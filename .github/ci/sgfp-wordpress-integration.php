<?php

use SGFP\Infrastructure\Database\SchemaMigrator;
use SGFP\Infrastructure\Database\TableNames;

function sgfp_fail(string $message): never {
    throw new RuntimeException('[INTEGRATION] ' . $message);
}

function sgfp_assert(bool $condition, string $message): void {
    if (!$condition) {
        sgfp_fail($message);
    }
}

function sgfp_sql(string $sql): void {
    global $wpdb;
    $result = $wpdb->query($sql);
    if ($result === false) {
        sgfp_fail('SQL failed: ' . $wpdb->last_error . ' | ' . $sql);
    }
}

function sgfp_table_exists(string $table): bool {
    global $wpdb;
    return $wpdb->get_var($wpdb->prepare('SHOW TABLES LIKE %s', $table)) === $table;
}

function sgfp_column_exists(string $table, string $column): bool {
    global $wpdb;
    return (int) $wpdb->get_var($wpdb->prepare(
        'SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME=%s AND COLUMN_NAME=%s',
        $table,
        $column
    )) > 0;
}

function sgfp_index_exists(string $table, string $index): bool {
    global $wpdb;
    return (int) $wpdb->get_var($wpdb->prepare(
        'SELECT COUNT(*) FROM information_schema.STATISTICS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME=%s AND INDEX_NAME=%s',
        $table,
        $index
    )) > 0;
}

function sgfp_count(string $table, string $column, int $userId): int {
    global $wpdb;
    return (int) $wpdb->get_var($wpdb->prepare(
        "SELECT COUNT(*) FROM {$table} WHERE {$column}=%d",
        $userId
    ));
}

function sgfp_create_admin(string $suffix): int {
    $login = 'sgfp_' . $suffix . '_' . strtolower(wp_generate_password(6, false, false));
    $userId = wp_insert_user([
        'user_login' => $login,
        'user_pass' => 'Sgfp!Integration123',
        'user_email' => $login . '@example.test',
        'role' => 'administrator',
    ]);
    if (is_wp_error($userId)) {
        sgfp_fail('Unable to create test user: ' . $userId->get_error_message());
    }
    return (int) $userId;
}

function sgfp_rest(string $method, string $route, array $params = [], ?array $fileParams = null): WP_REST_Response {
    $request = new WP_REST_Request($method, $route);
    if ($params !== []) {
        $request->set_body_params($params);
    }
    if ($fileParams !== null) {
        $request->set_file_params($fileParams);
    }
    $response = rest_do_request($request);
    if ($response instanceof WP_Error) {
        sgfp_fail('REST error at ' . $route . ': ' . $response->get_error_message());
    }
    return $response;
}

function sgfp_assert_clean_v8_schema(): void {
    global $wpdb;

    $financial = [
        TableNames::account(),
        TableNames::category(),
        TableNames::recurrence(),
        TableNames::commitment(),
        TableNames::entry(),
    ];

    foreach ($financial as $table) {
        sgfp_assert(sgfp_table_exists($table), 'Missing V8 table ' . $table);
    }

    sgfp_assert(sgfp_table_exists(TableNames::restorationToken()), 'Missing restoration token table');
    sgfp_assert(!sgfp_table_exists(TableNames::prefix() . 'transferencia'), 'Legacy transfer table still exists');
    sgfp_assert(!sgfp_column_exists(TableNames::account(), 'papel'), 'Legacy account papel column still exists');
    sgfp_assert(!sgfp_column_exists(TableNames::account(), 'id_usuario_principal'), 'Legacy account principal generated column still exists');
    sgfp_assert(!sgfp_column_exists(TableNames::commitment(), 'tipo'), 'Legacy commitment tipo column still exists');
    sgfp_assert(sgfp_index_exists(TableNames::account(), 'uq_conta_usuario'), 'Single-account unique index is missing');
    sgfp_assert(get_option('sgfp_schema_version') === '1.2.0', 'Schema version is not 1.2.0');

    $engineFailures = (int) $wpdb->get_var(
        "SELECT COUNT(*) FROM information_schema.TABLES
         WHERE TABLE_SCHEMA=DATABASE()
           AND TABLE_NAME IN ('" . implode("','", array_map('esc_sql', [...$financial, TableNames::restorationToken()])) . "')
           AND UPPER(ENGINE) <> 'INNODB'"
    );
    sgfp_assert($engineFailures === 0, 'One or more SGFP tables are not InnoDB');
}

function sgfp_drop_financial_tables(): void {
    global $wpdb;
    $tables = [
        TableNames::entry(),
        TableNames::prefix() . 'transferencia',
        TableNames::commitment(),
        TableNames::recurrence(),
        TableNames::category(),
        TableNames::account(),
    ];
    sgfp_sql('SET FOREIGN_KEY_CHECKS=0');
    foreach ($tables as $table) {
        sgfp_sql("DROP TABLE IF EXISTS {$table}");
    }
    sgfp_sql('SET FOREIGN_KEY_CHECKS=1');
}

function sgfp_create_legacy_v7_schema(): void {
    global $wpdb;

    sgfp_drop_financial_tables();

    $users = $wpdb->users;
    $charset = $wpdb->get_charset_collate();
    $account = TableNames::account();
    $category = TableNames::category();
    $recurrence = TableNames::recurrence();
    $commitment = TableNames::commitment();
    $entry = TableNames::entry();
    $transfer = TableNames::prefix() . 'transferencia';

    sgfp_sql("CREATE TABLE {$account} (
        id_conta BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        fk_id_usuario BIGINT UNSIGNED NOT NULL,
        nome VARCHAR(120) NOT NULL,
        papel VARCHAR(10) NOT NULL,
        criada_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        id_usuario_principal BIGINT UNSIGNED GENERATED ALWAYS AS (
            CASE WHEN papel='PRINCIPAL' THEN fk_id_usuario ELSE NULL END
        ) STORED,
        CONSTRAINT fk_conta_usuario FOREIGN KEY (fk_id_usuario) REFERENCES {$users}(ID),
        CONSTRAINT ck_conta_papel CHECK (papel IN ('PRINCIPAL','SECUNDARIA')),
        CONSTRAINT uq_conta_principal_usuario UNIQUE (id_usuario_principal),
        CONSTRAINT uq_conta_id_usuario UNIQUE (id_conta, fk_id_usuario)
    ) {$charset}");

    sgfp_sql("CREATE TABLE {$category} (
        id_categoria BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        fk_id_usuario BIGINT UNSIGNED NOT NULL,
        nome VARCHAR(100) NOT NULL,
        criada_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        CONSTRAINT fk_categoria_usuario FOREIGN KEY (fk_id_usuario) REFERENCES {$users}(ID),
        CONSTRAINT uq_categoria_id_usuario UNIQUE (id_categoria, fk_id_usuario)
    ) {$charset}");

    sgfp_sql("CREATE TABLE {$recurrence} (
        id_recorrencia BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        fk_id_usuario BIGINT UNSIGNED NOT NULL,
        inicio_mes DATE NOT NULL,
        quantidade_meses SMALLINT UNSIGNED NULL,
        encerrada_no_mes DATE NULL,
        criada_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        CONSTRAINT fk_recorrencia_usuario FOREIGN KEY (fk_id_usuario) REFERENCES {$users}(ID),
        CONSTRAINT uq_recorrencia_id_usuario UNIQUE (id_recorrencia, fk_id_usuario)
    ) {$charset}");

    sgfp_sql("CREATE TABLE {$commitment} (
        id_compromisso BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        fk_id_usuario BIGINT UNSIGNED NOT NULL,
        fk_id_categoria BIGINT UNSIGNED NULL,
        fk_id_recorrencia BIGINT UNSIGNED NULL,
        nome VARCHAR(180) NOT NULL,
        valor DECIMAL(14,2) NOT NULL,
        tipo VARCHAR(15) NOT NULL DEFAULT 'PADRAO',
        natureza VARCHAR(7) NOT NULL,
        mes_referencia DATE NOT NULL,
        status VARCHAR(12) NOT NULL DEFAULT 'PENDENTE',
        criado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        CONSTRAINT fk_compromisso_usuario FOREIGN KEY (fk_id_usuario) REFERENCES {$users}(ID),
        CONSTRAINT ck_compromisso_tipo CHECK (tipo IN ('PADRAO','TRANSFERENCIA')),
        CONSTRAINT uq_compromisso_id_usuario UNIQUE (id_compromisso, fk_id_usuario)
    ) {$charset}");

    sgfp_sql("CREATE TABLE {$entry} (
        id_lancamento BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        fk_id_usuario BIGINT UNSIGNED NOT NULL,
        fk_id_compromisso BIGINT UNSIGNED NULL,
        fk_id_conta BIGINT UNSIGNED NOT NULL,
        origem VARCHAR(15) NOT NULL,
        nome VARCHAR(180) NOT NULL,
        valor DECIMAL(14,2) NOT NULL,
        tipo_efeito VARCHAR(7) NOT NULL,
        data_efetivacao DATETIME NOT NULL,
        descricao VARCHAR(500) NULL,
        estado VARCHAR(10) NOT NULL DEFAULT 'ATIVO',
        criado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        desfeito_em DATETIME NULL,
        id_compromisso_ativo BIGINT UNSIGNED GENERATED ALWAYS AS (
            CASE WHEN origem='COMPROMISSO' AND estado='ATIVO' THEN fk_id_compromisso ELSE NULL END
        ) STORED,
        id_conta_compromisso_ativa VARCHAR(80) GENERATED ALWAYS AS (
            CASE WHEN origem='COMPROMISSO' AND estado='ATIVO'
                 THEN CONCAT(fk_id_conta, ':', fk_id_compromisso) ELSE NULL END
        ) STORED,
        id_conta_saldo_inicial_ativo BIGINT UNSIGNED GENERATED ALWAYS AS (
            CASE WHEN origem='SALDO_INICIAL' AND estado='ATIVO' THEN fk_id_conta ELSE NULL END
        ) STORED,
        CONSTRAINT fk_lancamento_usuario FOREIGN KEY (fk_id_usuario) REFERENCES {$users}(ID),
        CONSTRAINT fk_lancamento_compromisso FOREIGN KEY (fk_id_compromisso, fk_id_usuario)
            REFERENCES {$commitment}(id_compromisso, fk_id_usuario),
        CONSTRAINT fk_lancamento_conta FOREIGN KEY (fk_id_conta, fk_id_usuario)
            REFERENCES {$account}(id_conta, fk_id_usuario),
        CONSTRAINT uq_lancamento_efeito_ativo UNIQUE (id_conta_compromisso_ativa),
        CONSTRAINT uq_lancamento_saldo_inicial_ativo UNIQUE (id_conta_saldo_inicial_ativo)
    ) {$charset}");

    sgfp_sql("CREATE TABLE {$transfer} (
        id_transferencia BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        fk_id_usuario BIGINT UNSIGNED NOT NULL,
        fk_id_compromisso BIGINT UNSIGNED NOT NULL,
        fk_id_conta_origem BIGINT UNSIGNED NOT NULL,
        fk_id_conta_destino BIGINT UNSIGNED NOT NULL
    ) {$charset}");

    update_option('sgfp_schema_version', '1.1.0', false);
}

function sgfp_seed_v7(int $userId, bool $balancedTransfer): array {
    global $wpdb;

    $account = TableNames::account();
    $commitment = TableNames::commitment();
    $entry = TableNames::entry();
    $transfer = TableNames::prefix() . 'transferencia';

    sgfp_assert($wpdb->insert($account, [
        'fk_id_usuario' => $userId,
        'nome' => 'Conta Principal Antiga',
        'papel' => 'PRINCIPAL',
    ], ['%d','%s','%s']) === 1, 'Could not insert principal legacy account');
    $principalId = (int) $wpdb->insert_id;

    sgfp_assert($wpdb->insert($account, [
        'fk_id_usuario' => $userId,
        'nome' => 'Conta Secundária Antiga',
        'papel' => 'SECUNDARIA',
    ], ['%d','%s','%s']) === 1, 'Could not insert secondary legacy account');
    $secondaryId = (int) $wpdb->insert_id;

    sgfp_assert($wpdb->insert($commitment, [
        'fk_id_usuario' => $userId,
        'nome' => 'Despesa antiga',
        'valor' => 20,
        'tipo' => 'PADRAO',
        'natureza' => 'SAIDA',
        'mes_referencia' => '2026-09-01',
        'status' => 'EFETIVADO',
    ], ['%d','%s','%f','%s','%s','%s','%s']) === 1, 'Could not insert standard legacy commitment');
    $standardCommitmentId = (int) $wpdb->insert_id;

    sgfp_assert($wpdb->insert($commitment, [
        'fk_id_usuario' => $userId,
        'nome' => 'Transferência antiga',
        'valor' => 30,
        'tipo' => 'TRANSFERENCIA',
        'natureza' => 'SAIDA',
        'mes_referencia' => '2026-09-01',
        'status' => 'EFETIVADO',
    ], ['%d','%s','%f','%s','%s','%s','%s']) === 1, 'Could not insert transfer legacy commitment');
    $transferCommitmentId = (int) $wpdb->insert_id;

    $rows = [
        [null, $principalId, 'SALDO_INICIAL', 'Saldo inicial', 100, 'ENTRADA'],
        [$standardCommitmentId, $secondaryId, 'COMPROMISSO', 'Despesa antiga', 20, 'SAIDA'],
        [$transferCommitmentId, $principalId, 'COMPROMISSO', 'Transferência saída', 30, 'SAIDA'],
    ];

    if ($balancedTransfer) {
        $rows[] = [$transferCommitmentId, $secondaryId, 'COMPROMISSO', 'Transferência entrada', 30, 'ENTRADA'];
    }

    foreach ($rows as [$commitmentId, $accountId, $origin, $name, $amount, $effect]) {
        sgfp_assert($wpdb->insert($entry, [
            'fk_id_usuario' => $userId,
            'fk_id_compromisso' => $commitmentId,
            'fk_id_conta' => $accountId,
            'origem' => $origin,
            'nome' => $name,
            'valor' => $amount,
            'tipo_efeito' => $effect,
            'data_efetivacao' => '2026-09-15 12:00:00',
            'estado' => 'ATIVO',
        ], ['%d','%d','%d','%s','%s','%f','%s','%s','%s']) === 1, 'Could not insert legacy entry');
    }

    sgfp_assert($wpdb->insert($transfer, [
        'fk_id_usuario' => $userId,
        'fk_id_compromisso' => $transferCommitmentId,
        'fk_id_conta_origem' => $principalId,
        'fk_id_conta_destino' => $secondaryId,
    ], ['%d','%d','%d','%d']) === 1, 'Could not insert legacy transfer row');

    return [$principalId, $secondaryId];
}

function sgfp_scenario_clean(): void {
    sgfp_assert_clean_v8_schema();

    $userId = sgfp_create_admin('clean');
    sgfp_assert(sgfp_count(TableNames::account(), 'fk_id_usuario', $userId) === 1, 'Provisioning did not create exactly one account');

    global $wpdb;
    $name = (string) $wpdb->get_var($wpdb->prepare(
        'SELECT nome FROM ' . TableNames::account() . ' WHERE fk_id_usuario=%d',
        $userId
    ));
    sgfp_assert($name === 'Minha Conta', 'Provisioned account is not Minha Conta');
    sgfp_assert(sgfp_count(TableNames::category(), 'fk_id_usuario', $userId) === 13, 'Default categories were not provisioned');

    echo "[PASS] clean-v8\n";
}

function sgfp_scenario_migration(): void {
    global $wpdb;

    $userId = sgfp_create_admin('migration');

    sgfp_create_legacy_v7_schema();
    [$principalId] = sgfp_seed_v7($userId, true);

    SchemaMigrator::migrate();

    sgfp_assert_clean_v8_schema();
    sgfp_assert(sgfp_count(TableNames::account(), 'fk_id_usuario', $userId) === 1, 'V7 migration did not collapse accounts');

    $remainingId = (int) $wpdb->get_var($wpdb->prepare(
        'SELECT id_conta FROM ' . TableNames::account() . ' WHERE fk_id_usuario=%d',
        $userId
    ));
    sgfp_assert($remainingId === $principalId, 'V7 migration did not preserve the principal account as target');

    $balance = (float) $wpdb->get_var($wpdb->prepare(
        "SELECT COALESCE(SUM(CASE WHEN tipo_efeito='ENTRADA' THEN valor ELSE -valor END),0)
         FROM " . TableNames::entry() . "
         WHERE fk_id_usuario=%d AND estado='ATIVO'",
        $userId
    ));
    sgfp_assert(abs($balance - 80.0) < 0.0001, 'V7 migration did not preserve aggregate balance');
    sgfp_assert((int) $wpdb->get_var(
        "SELECT COUNT(*) FROM " . TableNames::commitment() . " WHERE nome='Transferência antiga'"
    ) === 0, 'Legacy transfer commitment survived migration');

    sgfp_create_legacy_v7_schema();
    sgfp_seed_v7($userId, false);

    $blocked = false;
    try {
        SchemaMigrator::migrate();
    } catch (RuntimeException $e) {
        $blocked = str_contains($e->getMessage(), 'non-zero active net effect');
    }

    sgfp_assert($blocked, 'Unsafe unbalanced V7 transfer was not blocked');
    sgfp_assert(get_option('sgfp_schema_version') === '1.1.0', 'Blocked migration incorrectly advanced schema version');
    sgfp_assert(sgfp_count(TableNames::account(), 'fk_id_usuario', $userId) === 2, 'Blocked migration mutated legacy accounts');

    echo "[PASS] migrate-v7-to-v8\n";
}

function sgfp_scenario_lifecycle(): void {
    global $wpdb;

    $userId = sgfp_create_admin('reset');
    wp_set_current_user($userId);

    sgfp_assert(sgfp_count(TableNames::account(), 'fk_id_usuario', $userId) === 1, 'Lifecycle user was not provisioned');

    sgfp_assert($wpdb->insert(TableNames::category(), [
        'fk_id_usuario' => $userId,
        'nome' => 'Categoria Temporária',
    ], ['%d','%s']) === 1, 'Could not create temporary category');

    update_user_meta($userId, 'sgfp_theme', 'dark');

    $tokenHash = hash('sha256', 'lifecycle-token');
    sgfp_assert($wpdb->insert(TableNames::restorationToken(), [
        'user_id' => $userId,
        'token_hash' => $tokenHash,
        'metadata' => '{}',
        'expires_at' => gmdate('Y-m-d H:i:s', time() + 3600),
    ], ['%d','%s','%s','%s']) === 1, 'Could not create temporary restoration token');

    $reset = sgfp_rest('POST', '/sgfp/v1/profile-reset', [
        'confirmation' => true,
        'phrase' => 'RESETAR PERFIL',
    ]);

    sgfp_assert($reset->get_status() === 200, 'Profile reset returned HTTP ' . $reset->get_status());
    sgfp_assert(get_userdata($userId) instanceof WP_User, 'Profile reset deleted WordPress identity');
    sgfp_assert(sgfp_count(TableNames::account(), 'fk_id_usuario', $userId) === 1, 'Profile reset did not reprovision one account');
    sgfp_assert(sgfp_count(TableNames::category(), 'fk_id_usuario', $userId) === 13, 'Profile reset did not reprovision default categories');
    sgfp_assert((int) $wpdb->get_var($wpdb->prepare(
        'SELECT COUNT(*) FROM ' . TableNames::category() . ' WHERE fk_id_usuario=%d AND nome=%s',
        $userId,
        'Categoria Temporária'
    )) === 0, 'Profile reset kept temporary category');
    sgfp_assert(get_user_meta($userId, 'sgfp_theme', true) === '', 'Profile reset kept SGFP theme preference');
    sgfp_assert(sgfp_count(TableNames::restorationToken(), 'user_id', $userId) === 0, 'Profile reset kept restoration tokens');

    $deleteUserId = sgfp_create_admin('delete');
    wp_set_current_user($deleteUserId);

    $delete = sgfp_rest('DELETE', '/sgfp/v1/account-access', [
        'confirmation' => true,
        'phrase' => 'EXCLUIR CONTA',
    ]);

    sgfp_assert($delete->get_status() === 200, 'Account deletion returned HTTP ' . $delete->get_status());
    sgfp_assert(get_userdata($deleteUserId) === false, 'WordPress identity still exists after account deletion');
    sgfp_assert(sgfp_count(TableNames::account(), 'fk_id_usuario', $deleteUserId) === 0, 'Account deletion kept financial account');
    sgfp_assert(sgfp_count(TableNames::category(), 'fk_id_usuario', $deleteUserId) === 0, 'Account deletion kept categories');

    echo "[PASS] lifecycle\n";
}

function sgfp_scenario_backup_restore(): void {
    global $wpdb;

    $userId = sgfp_create_admin('backup');
    wp_set_current_user($userId);

    $accountId = (int) $wpdb->get_var($wpdb->prepare(
        'SELECT id_conta FROM ' . TableNames::account() . ' WHERE fk_id_usuario=%d',
        $userId
    ));
    sgfp_assert($accountId > 0, 'Backup user has no account');

    $initial = sgfp_rest('POST', '/sgfp/v1/accounts/' . $accountId . '/initial-balance', [
        'amount' => '1000.00',
        'name' => 'Saldo inicial',
        'effective_month' => '2026-09',
    ]);
    sgfp_assert($initial->get_status() === 201, 'Initial balance request failed');

    $category = sgfp_rest('POST', '/sgfp/v1/categories', ['name' => 'Teste Backup']);
    sgfp_assert($category->get_status() === 201, 'Category creation failed');
    $categoryId = (int) ($category->get_data()['id'] ?? 0);
    sgfp_assert($categoryId > 0, 'Category response has no id');

    $commitment = sgfp_rest('POST', '/sgfp/v1/commitments', [
        'category_id' => $categoryId,
        'name' => 'Conta teste',
        'amount' => 125,
        'nature' => 'SAIDA',
        'reference_month' => '2026-09',
    ]);
    sgfp_assert($commitment->get_status() === 201, 'Commitment creation failed');
    $commitmentId = (int) ($commitment->get_data()['id'] ?? 0);

    $settled = sgfp_rest('POST', '/sgfp/v1/commitments/' . $commitmentId . '/settle');
    sgfp_assert($settled->get_status() === 201, 'Commitment settlement failed');

    $theme = sgfp_rest('POST', '/sgfp/v1/preferences/theme', ['theme' => 'dark']);
    sgfp_assert(in_array($theme->get_status(), [200, 201], true), 'Theme update failed');

    $backup = sgfp_rest('POST', '/sgfp/v1/backups');
    sgfp_assert($backup->get_status() === 201, 'Backup creation failed');
    $backupData = $backup->get_data();

    $zip = base64_decode((string) ($backupData['content_base64'] ?? ''), true);
    sgfp_assert(is_string($zip) && $zip !== '', 'Backup ZIP payload is empty');
    sgfp_assert(hash('sha256', $zip) === ($backupData['sha256'] ?? ''), 'Backup ZIP hash mismatch');

    $packed = (new SGFP\Application\Backup\BackupArchive())->unpack($zip);
    $json = (new SGFP\Application\Backup\BackupProtector())->unprotect($packed['payload']);
    $payload = json_decode($json, true);
    sgfp_assert(is_array($payload), 'Backup payload is not valid JSON');
    sgfp_assert(($payload['metadata']['version'] ?? null) === 2, 'Backup payload version is not 2');
    sgfp_assert(count($payload['accounts'] ?? []) === 1, 'Backup payload does not contain exactly one account');

    $extra = sgfp_rest('POST', '/sgfp/v1/categories', ['name' => 'Depois do Backup']);
    sgfp_assert($extra->get_status() === 201, 'Could not mutate state after backup');
    $themeLight = sgfp_rest('POST', '/sgfp/v1/preferences/theme', ['theme' => 'light']);
    sgfp_assert(in_array($themeLight->get_status(), [200, 201], true), 'Could not mutate theme after backup');

    $tmp = tempnam(sys_get_temp_dir(), 'sgfp-upload-');
    sgfp_assert($tmp !== false && file_put_contents($tmp, $zip) === strlen($zip), 'Could not stage backup upload');

    $validation = sgfp_rest('POST', '/sgfp/v1/restore-validations', [], [
        'backup' => [
            'name' => 'sgfp-backup.zip',
            'type' => 'application/zip',
            'tmp_name' => $tmp,
            'error' => UPLOAD_ERR_OK,
            'size' => strlen($zip),
        ],
    ]);
    @unlink($tmp);

    sgfp_assert($validation->get_status() === 200, 'Backup validation failed with HTTP ' . $validation->get_status());
    $token = (string) ($validation->get_data()['token'] ?? '');
    sgfp_assert((bool) preg_match('/^[a-f0-9]{64}$/', $token), 'Backup validation token is invalid');

    $restore = sgfp_rest('POST', '/sgfp/v1/restorations', [
        'token' => $token,
        'confirmation' => true,
    ]);
    sgfp_assert($restore->get_status() === 200, 'Restoration failed with HTTP ' . $restore->get_status());

    $restoreData = $restore->get_data();
    sgfp_assert(($restoreData['status'] ?? null) === 'restored', 'Restoration did not report restored status');
    sgfp_assert(!empty($restoreData['snapshot']['content_base64']), 'Pre-restoration snapshot was not returned');
    sgfp_assert(($restoreData['snapshot']['content_type'] ?? null) === 'application/zip', 'Pre-restoration snapshot is not ZIP');

    sgfp_assert((int) $wpdb->get_var($wpdb->prepare(
        'SELECT COUNT(*) FROM ' . TableNames::category() . ' WHERE fk_id_usuario=%d AND nome=%s',
        $userId,
        'Depois do Backup'
    )) === 0, 'Restoration kept post-backup category');

    sgfp_assert(get_user_meta($userId, 'sgfp_theme', true) === 'dark', 'Restoration did not restore theme');

    $balance = (float) $wpdb->get_var($wpdb->prepare(
        "SELECT COALESCE(SUM(CASE WHEN tipo_efeito='ENTRADA' THEN valor ELSE -valor END),0)
         FROM " . TableNames::entry() . "
         WHERE fk_id_usuario=%d AND estado='ATIVO'",
        $userId
    ));
    sgfp_assert(abs($balance - 875.0) < 0.0001, 'Restoration did not restore expected balance');

    echo "[PASS] backup-restore\n";
}

$role = get_role('administrator');
if ($role !== null && !$role->has_cap('use_sgfp')) {
    $role->add_cap('use_sgfp');
}

$scenario = getenv('SGFP_INTEGRATION_SCENARIO') ?: '';

match ($scenario) {
    'clean-v8' => sgfp_scenario_clean(),
    'migrate-v7-to-v8' => sgfp_scenario_migration(),
    'lifecycle' => sgfp_scenario_lifecycle(),
    'backup-restore' => sgfp_scenario_backup_restore(),
    default => sgfp_fail('Unknown scenario: ' . $scenario),
};
