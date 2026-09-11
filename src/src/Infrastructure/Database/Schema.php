<?php

declare(strict_types=1);

namespace SGFP\Infrastructure\Database;

final class Schema
{
    public static function createTables(): void
    {
        global $wpdb;

        $charset = $wpdb->get_charset_collate();

        $account = TableNames::account();
        $category = TableNames::category();
        $recurrence = TableNames::recurrence();
        $commitment = TableNames::commitment();
        $entry = TableNames::entry();
        $transfer = TableNames::transfer();

        $sql = "
        CREATE TABLE {$account} (
            id_conta BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            fk_id_usuario BIGINT UNSIGNED NOT NULL,
            nome VARCHAR(120) NOT NULL,
            papel VARCHAR(12) NOT NULL,
            criada_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            principal_hash BINARY(16) AS (CASE WHEN papel = 'PRINCIPAL' THEN UNHEX(MD5(CONCAT(fk_id_usuario, ':', papel))) END) STORED UNIQUE,
            CONSTRAINT chk_papel CHECK (papel IN ('PRINCIPAL', 'SECUNDARIA'))
        ) {$charset};

        CREATE TABLE {$category} (
            id_categoria BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            fk_id_usuario BIGINT UNSIGNED NOT NULL,
            nome VARCHAR(120) NOT NULL,
            tipo VARCHAR(12) NOT NULL,
            criada_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            CONSTRAINT chk_tipo CHECK (tipo IN ('RECEITA', 'DESPESA')),
            UNIQUE KEY uk_usuario_nome_tipo (fk_id_usuario, nome, tipo)
        ) {$charset};

        CREATE TABLE {$recurrence} (
            id_recorrencia BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            fk_id_compromisso BIGINT UNSIGNED NOT NULL,
            dia_vencimento TINYINT UNSIGNED NOT NULL,
            mes_inicial TINYINT UNSIGNED NOT NULL,
            ano_inicial SMALLINT UNSIGNED NOT NULL,
            mes_final TINYINT UNSIGNED DEFAULT NULL,
            ano_final SMALLINT UNSIGNED DEFAULT NULL,
            criada_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            CONSTRAINT chk_dia CHECK (dia_vencimento BETWEEN 1 AND 31),
            CONSTRAINT chk_mes_inicial CHECK (mes_inicial BETWEEN 1 AND 12),
            CONSTRAINT chk_ano_inicial CHECK (ano_inicial >= 2000),
            CONSTRAINT chk_mes_final CHECK (mes_final IS NULL OR mes_final BETWEEN 1 AND 12),
            CONSTRAINT chk_ano_final CHECK (ano_final IS NULL OR ano_final >= 2000)
        ) {$charset};

        CREATE TABLE {$commitment} (
            id_compromisso BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            fk_id_usuario BIGINT UNSIGNED NOT NULL,
            fk_id_conta BIGINT UNSIGNED NOT NULL,
            fk_id_categoria BIGINT UNSIGNED DEFAULT NULL,
            descricao VARCHAR(255) NOT NULL,
            valor DECIMAL(15,2) NOT NULL,
            tipo VARCHAR(12) NOT NULL,
            status VARCHAR(12) NOT NULL DEFAULT 'PENDENTE',
            data_vencimento DATE NOT NULL,
            efetivado_em DATETIME DEFAULT NULL,
            criado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            CONSTRAINT chk_tipo_compromisso CHECK (tipo IN ('RECEITA', 'DESPESA')),
            CONSTRAINT chk_status CHECK (status IN ('PENDENTE', 'EFETIVADO', 'CANCELADO')),
            KEY idx_usuario_vencimento (fk_id_usuario, data_vencimento)
        ) {$charset};

        CREATE TABLE {$entry} (
            id_lancamento BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            fk_id_usuario BIGINT UNSIGNED NOT NULL,
            fk_id_conta BIGINT UNSIGNED NOT NULL,
            fk_id_compromisso BIGINT UNSIGNED DEFAULT NULL,
            fk_id_transferencia BIGINT UNSIGNED DEFAULT NULL,
            descricao VARCHAR(255) NOT NULL,
            valor DECIMAL(15,2) NOT NULL,
            tipo VARCHAR(12) NOT NULL,
            data_competencia DATE NOT NULL,
            efetivado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            criado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            CONSTRAINT chk_tipo_lancamento CHECK (tipo IN ('RECEITA', 'DESPESA')),
            KEY idx_usuario_conta_competencia (fk_id_usuario, fk_id_conta, data_competencia)
        ) {$charset};

        CREATE TABLE {$transfer} (
            id_transferencia BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            fk_id_usuario BIGINT UNSIGNED NOT NULL,
            fk_id_conta_origem BIGINT UNSIGNED NOT NULL,
            fk_id_conta_destino BIGINT UNSIGNED NOT NULL,
            valor DECIMAL(15,2) NOT NULL,
            data_transferencia DATE NOT NULL,
            efetivada_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            criada_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            CONSTRAINT chk_contas_diferentes CHECK (fk_id_conta_origem <> fk_id_conta_destino),
            KEY idx_usuario_data (fk_id_usuario, data_transferencia)
        ) {$charset};
        ";

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        dbDelta($sql);
    }
}
