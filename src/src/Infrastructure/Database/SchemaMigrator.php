<?php

declare(strict_types=1);

namespace SGFP\Infrastructure\Database;

use RuntimeException;

final class SchemaMigrator
{
    private const OPTION_NAME = 'sgfp_schema_version';

    /**
     * @var array<string, string>
     */
    private const MIGRATIONS = [
        '1.0.0' => 'migrateTo100',
        '1.1.0' => 'migrateTo110',
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

        require_once \ABSPATH . 'wp-admin/includes/upgrade.php';

        $charset = $wpdb->get_charset_collate();
        $sql = Schema::getCreateTablesSql($charset);

        $previousHideErrors = $wpdb->hide_errors();

        try {
            foreach (self::splitStatements($sql) as $statement) {
                $result = $wpdb->query($statement);

                if ($result === false) {
                    throw new RuntimeException(
                        sprintf(
                            'Migration %s failed: %s',
                            $version,
                            $wpdb->last_error
                        )
                    );
                }
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
        $sql = "CREATE TABLE IF NOT EXISTS {$table} (
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
        ) ENGINE=InnoDB {$charset}";
        if ($wpdb->query($sql) === false) {
            throw new RuntimeException(sprintf('Migration %s failed: %s', $version, $wpdb->last_error));
        }
        $engine = $wpdb->get_var($wpdb->prepare(
            'SELECT ENGINE FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = %s',
            $table
        ));
        if (strtoupper((string) $engine) !== 'INNODB') {
            throw new RuntimeException(sprintf('Migration %s requires InnoDB for %s.', $version, $table));
        }
    }

    /**
     * @return list<string>
     */
    private static function splitStatements(string $sql): array
    {
        $statements = [];

        foreach (explode(';', $sql) as $statement) {
            $statement = trim($statement);
            if ($statement === '') {
                continue;
            }
            $statements[] = $statement;
        }

        return $statements;
    }
}
