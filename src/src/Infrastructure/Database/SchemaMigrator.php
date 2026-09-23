<?php

declare(strict_types=1);

namespace SGFP\Infrastructure\Database;

use RuntimeException;

final class SchemaMigrator
{
    private const OPTION_NAME = 'sgfp_schema_version';

    private const MIGRATIONS = [
        '1.0.0' => 'migrateTo100',
        '1.1.0' => 'migrateTo110',
        '1.2.0' => 'migrateTo120',
        '1.3.0' => 'migrateTo130',
    ];

    public static function migrate(): void
    {
        $current = get_option(self::OPTION_NAME, '0.0.0');

        foreach (self::MIGRATIONS as $version => $method) {
            if (version_compare($current, $version, '>=')) {
                continue;
            }

            self::{$method}($version);
            update_option(self::OPTION_NAME, $version, false);
            $current = $version;
        }
    }

    private static function migrateTo100(string $version): void
    {
        global $wpdb;

        $previousHideErrors = $wpdb->hide_errors();

        try {
            foreach (self::splitStatements(Schema::getCreateTablesSql($wpdb->get_charset_collate())) as $statement) {
                self::run($statement, $version);
            }
        } finally {
            if ($previousHideErrors === true) {
                $wpdb->show_errors();
            }
        }
    }

    private static function migrateTo110(string $version): void
    {
        global $wpdb;

        $table = TableNames::restorationToken();
        $users = $wpdb->users;
        $charset = $wpdb->get_charset_collate();

        self::run("CREATE TABLE IF NOT EXISTS {$table} (
            id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            user_id BIGINT UNSIGNED NOT NULL,
            token_hash CHAR(64) CHARACTER SET ascii COLLATE ascii_bin NOT NULL,
            metadata LONGTEXT NOT NULL,
            expires_at DATETIME NOT NULL,
            claimed_at DATETIME NULL,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            CONSTRAINT fk_sgfp_token_user FOREIGN KEY (user_id) REFERENCES {$users}(ID),
            UNIQUE KEY uq_sgfp_token_hash (token_hash),
            KEY idx_sgfp_token_user_expiry (user_id, expires_at)
        ) ENGINE=InnoDB {$charset}", $version);

        $engine = $wpdb->get_var($wpdb->prepare(
            'SELECT ENGINE FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = %s',
            $table
        ));

        if (strtoupper((string) $engine) !== 'INNODB') {
            throw new RuntimeException(sprintf('Migration %s requires InnoDB for %s.', $version, $table));
        }
    }

    private static function migrateTo120(string $version): void
    {
        global $wpdb;

        $account = TableNames::account();
        $category = TableNames::category();
        $recurrence = TableNames::recurrence();
        $commitment = TableNames::commitment();
        $entry = TableNames::entry();
        $legacyTransfer = TableNames::prefix() . 'transferencia';

        $legacyAccount = self::columnExists($account, 'papel');
        $legacyCommitment = self::columnExists($commitment, 'tipo');

        if (!$legacyAccount && !$legacyCommitment) {
            if (self::tableExists($legacyTransfer)) {
                self::run("DROP TABLE {$legacyTransfer}", $version);
            }
            return;
        }

        if (!$legacyAccount || !$legacyCommitment) {
            throw new RuntimeException("Migration {$version} found a partially migrated legacy schema.");
        }

        self::assertLegacyDataCanBeCollapsed($commitment, $entry);

        self::run("INSERT INTO {$account} (fk_id_usuario, nome, papel)
            SELECT users_with_data.user_id, 'Minha Conta', 'PRINCIPAL'
            FROM (
                SELECT fk_id_usuario AS user_id FROM {$category}
                UNION
                SELECT fk_id_usuario AS user_id FROM {$recurrence}
                UNION
                SELECT fk_id_usuario AS user_id FROM {$commitment}
            ) users_with_data
            LEFT JOIN {$account} a ON a.fk_id_usuario = users_with_data.user_id
            WHERE a.id_conta IS NULL", $version);

        $temporaryMap = $wpdb->prefix . 'sgfp_v8_account_map_tmp';
        self::run("DROP TEMPORARY TABLE IF EXISTS {$temporaryMap}", $version);
        self::run("CREATE TEMPORARY TABLE {$temporaryMap} AS
            SELECT fk_id_usuario AS user_id,
                   COALESCE(
                       MAX(CASE WHEN papel='PRINCIPAL' THEN id_conta ELSE NULL END),
                       MIN(id_conta)
                   ) AS target_account_id
            FROM {$account}
            GROUP BY fk_id_usuario", $version);
        self::run("ALTER TABLE {$temporaryMap} ADD PRIMARY KEY (user_id)", $version);

        self::run("DELETE e FROM {$entry} e
            INNER JOIN {$commitment} c
              ON c.id_compromisso=e.fk_id_compromisso
             AND c.fk_id_usuario=e.fk_id_usuario
            WHERE c.tipo='TRANSFERENCIA'", $version);

        if (self::tableExists($legacyTransfer)) {
            self::run("DROP TABLE {$legacyTransfer}", $version);
        }

        self::run("DELETE FROM {$commitment} WHERE tipo='TRANSFERENCIA'", $version);

        self::run("UPDATE {$entry} e
            INNER JOIN {$temporaryMap} m ON m.user_id=e.fk_id_usuario
            SET e.fk_id_conta=m.target_account_id
            WHERE e.fk_id_conta<>m.target_account_id", $version);

        self::run("DELETE a FROM {$account} a
            INNER JOIN {$temporaryMap} m ON m.user_id=a.fk_id_usuario
            WHERE a.id_conta<>m.target_account_id", $version);

        self::run("DROP TEMPORARY TABLE IF EXISTS {$temporaryMap}", $version);

        self::dropCheckIfExists($account, 'ck_conta_papel', $version);
        self::dropIndexIfExists($account, 'uq_conta_principal_usuario', $version);
        self::dropColumnIfExists($account, 'id_usuario_principal', $version);
        self::dropColumnIfExists($account, 'papel', $version);

        if (!self::indexExists($account, 'uq_conta_usuario')) {
            self::run("ALTER TABLE {$account} ADD CONSTRAINT uq_conta_usuario UNIQUE (fk_id_usuario)", $version);
        }

        self::dropCheckIfExists($commitment, 'ck_compromisso_tipo', $version);
        self::dropColumnIfExists($commitment, 'tipo', $version);

        self::dropIndexIfExists($entry, 'uq_lancamento_efeito_ativo', $version);
        self::dropColumnIfExists($entry, 'id_conta_compromisso_ativa', $version);

        if (!self::indexExists($entry, 'uq_lancamento_compromisso_ativo')) {
            self::run("ALTER TABLE {$entry}
                ADD CONSTRAINT uq_lancamento_compromisso_ativo UNIQUE (id_compromisso_ativo)", $version);
        }
    }

    private static function migrateTo130(string $version): void
    {
        // Compromissos now preserve the daily due date; only recurrence
        // boundaries remain month-based.
        self::dropCheckIfExists(TableNames::commitment(), 'ck_compromisso_mes', $version);
    }

    private static function assertLegacyDataCanBeCollapsed(string $commitment, string $entry): void
    {
        global $wpdb;

        $unbalancedTransfers = (int) $wpdb->get_var(
            "SELECT COUNT(*) FROM (
                SELECT c.id_compromisso,
                       COALESCE(SUM(CASE
                           WHEN e.estado='ATIVO' AND e.tipo_efeito='ENTRADA' THEN e.valor
                           WHEN e.estado='ATIVO' AND e.tipo_efeito='SAIDA' THEN -e.valor
                           ELSE 0
                       END), 0) AS net_effect
                FROM {$commitment} c
                LEFT JOIN {$entry} e
                  ON e.fk_id_compromisso=c.id_compromisso
                 AND e.fk_id_usuario=c.fk_id_usuario
                WHERE c.tipo='TRANSFERENCIA'
                GROUP BY c.id_compromisso
                HAVING ABS(net_effect) > 0.000001
            ) legacy_transfer_check"
        );

        if ($unbalancedTransfers > 0) {
            throw new RuntimeException(
                'V8 migration blocked: a legacy transfer has a non-zero active net effect.'
            );
        }

        $duplicateInitialBalances = (int) $wpdb->get_var(
            "SELECT COUNT(*) FROM (
                SELECT fk_id_usuario
                FROM {$entry}
                WHERE origem='SALDO_INICIAL' AND estado='ATIVO'
                GROUP BY fk_id_usuario
                HAVING COUNT(*) > 1
            ) legacy_initial_balance_check"
        );

        if ($duplicateInitialBalances > 0) {
            throw new RuntimeException(
                'V8 migration blocked: a user has more than one active initial balance.'
            );
        }
    }

    private static function tableExists(string $table): bool
    {
        global $wpdb;
        return $wpdb->get_var($wpdb->prepare('SHOW TABLES LIKE %s', $table)) === $table;
    }

    private static function columnExists(string $table, string $column): bool
    {
        global $wpdb;
        return (int) $wpdb->get_var($wpdb->prepare(
            'SELECT COUNT(*) FROM information_schema.COLUMNS'
            . ' WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME=%s AND COLUMN_NAME=%s',
            $table,
            $column
        )) > 0;
    }

    private static function indexExists(string $table, string $index): bool
    {
        global $wpdb;
        return (int) $wpdb->get_var($wpdb->prepare(
            'SELECT COUNT(*) FROM information_schema.STATISTICS'
            . ' WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME=%s AND INDEX_NAME=%s',
            $table,
            $index
        )) > 0;
    }

    private static function checkExists(string $table, string $constraint): bool
    {
        global $wpdb;
        return (int) $wpdb->get_var($wpdb->prepare(
            'SELECT COUNT(*) FROM information_schema.TABLE_CONSTRAINTS'
            . " WHERE CONSTRAINT_SCHEMA=DATABASE() AND TABLE_NAME=%s"
            . " AND CONSTRAINT_NAME=%s AND CONSTRAINT_TYPE='CHECK'",
            $table,
            $constraint
        )) > 0;
    }

    private static function dropCheckIfExists(string $table, string $constraint, string $version): void
    {
        global $wpdb;

        if (!self::checkExists($table, $constraint)) {
            return;
        }

        if ($wpdb->query("ALTER TABLE {$table} DROP CHECK {$constraint}") !== false) {
            return;
        }

        if ($wpdb->query("ALTER TABLE {$table} DROP CONSTRAINT {$constraint}") === false) {
            throw new RuntimeException(
                sprintf('Migration %s failed dropping check %s: %s', $version, $constraint, $wpdb->last_error)
            );
        }
    }

    private static function dropIndexIfExists(string $table, string $index, string $version): void
    {
        if (self::indexExists($table, $index)) {
            self::run("ALTER TABLE {$table} DROP INDEX {$index}", $version);
        }
    }

    private static function dropColumnIfExists(string $table, string $column, string $version): void
    {
        if (self::columnExists($table, $column)) {
            self::run("ALTER TABLE {$table} DROP COLUMN {$column}", $version);
        }
    }

    private static function run(string $sql, string $version): void
    {
        global $wpdb;

        if ($wpdb->query($sql) === false) {
            throw new RuntimeException(sprintf(
                'Migration %s failed: %s',
                $version,
                $wpdb->last_error
            ));
        }
    }

    /** @return list<string> */
    private static function splitStatements(string $sql): array
    {
        $statements = [];
        foreach (explode(';', $sql) as $statement) {
            $statement = trim($statement);
            if ($statement !== '') {
                $statements[] = $statement;
            }
        }
        return $statements;
    }
}
