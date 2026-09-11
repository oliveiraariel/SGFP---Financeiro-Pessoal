<?php

declare(strict_types=1);

namespace SGFP\Infrastructure\Database;

final class Schema
{
    public static function getCreateTablesSql(string $charset): string
    {
        global $wpdb;

        $usersTable = $wpdb->users;
        $account = TableNames::account();
        $category = TableNames::category();
        $recurrence = TableNames::recurrence();
        $commitment = TableNames::commitment();
        $transfer = TableNames::transfer();
        $entry = TableNames::entry();

        return <<<SQL
CREATE TABLE IF NOT EXISTS {$account} (
    id_conta BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    fk_id_usuario BIGINT UNSIGNED NOT NULL,
    nome VARCHAR(120) NOT NULL,
    papel VARCHAR(12) NOT NULL,
    criada_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    id_usuario_principal BIGINT UNSIGNED
        GENERATED ALWAYS AS (
            CASE WHEN papel = 'PRINCIPAL' THEN fk_id_usuario ELSE NULL END
        ) STORED,
    CONSTRAINT fk_conta_usuario
        FOREIGN KEY (fk_id_usuario) REFERENCES {$usersTable}(ID),
    CONSTRAINT ck_conta_papel
        CHECK (papel IN ('PRINCIPAL', 'SECUNDARIA')),
    CONSTRAINT uq_conta_principal_usuario
        UNIQUE (id_usuario_principal),
    CONSTRAINT uq_conta_id_usuario
        UNIQUE (id_conta, fk_id_usuario),
    KEY idx_conta_usuario (fk_id_usuario)
) {$charset};

CREATE TABLE IF NOT EXISTS {$category} (
    id_categoria BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    fk_id_usuario BIGINT UNSIGNED NOT NULL,
    nome VARCHAR(100) NOT NULL,
    criada_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_categoria_usuario
        FOREIGN KEY (fk_id_usuario) REFERENCES {$usersTable}(ID),
    CONSTRAINT uq_categoria_nome_usuario
        UNIQUE (fk_id_usuario, nome),
    CONSTRAINT uq_categoria_id_usuario
        UNIQUE (id_categoria, fk_id_usuario)
) {$charset};

CREATE TABLE IF NOT EXISTS {$recurrence} (
    id_recorrencia BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    fk_id_usuario BIGINT UNSIGNED NOT NULL,
    inicio_mes DATE NOT NULL,
    quantidade_meses SMALLINT UNSIGNED,
    encerrada_no_mes DATE,
    criada_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_recorrencia_usuario
        FOREIGN KEY (fk_id_usuario) REFERENCES {$usersTable}(ID),
    CONSTRAINT ck_recorrencia_inicio_mes
        CHECK (DAY(inicio_mes) = 1),
    CONSTRAINT ck_recorrencia_quantidade
        CHECK (quantidade_meses IS NULL OR quantidade_meses > 0),
    CONSTRAINT ck_recorrencia_encerramento_mes
        CHECK (encerrada_no_mes IS NULL OR DAY(encerrada_no_mes) = 1),
    CONSTRAINT ck_recorrencia_encerramento_data
        CHECK (encerrada_no_mes IS NULL OR encerrada_no_mes >= inicio_mes),
    CONSTRAINT uq_recorrencia_id_usuario
        UNIQUE (id_recorrencia, fk_id_usuario),
    KEY idx_recorrencia_usuario (fk_id_usuario)
) {$charset};

CREATE TABLE IF NOT EXISTS {$commitment} (
    id_compromisso BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    fk_id_usuario BIGINT UNSIGNED NOT NULL,
    fk_id_categoria BIGINT UNSIGNED,
    fk_id_recorrencia BIGINT UNSIGNED,
    nome VARCHAR(180) NOT NULL,
    valor DECIMAL(14,2) NOT NULL,
    tipo VARCHAR(15) NOT NULL,
    natureza VARCHAR(7) NOT NULL,
    mes_referencia DATE NOT NULL,
    status VARCHAR(12) NOT NULL DEFAULT 'PENDENTE',
    criado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_compromisso_usuario
        FOREIGN KEY (fk_id_usuario) REFERENCES {$usersTable}(ID),
    CONSTRAINT fk_compromisso_categoria
        FOREIGN KEY (fk_id_categoria, fk_id_usuario)
        REFERENCES {$category}(id_categoria, fk_id_usuario),
    CONSTRAINT fk_compromisso_recorrencia
        FOREIGN KEY (fk_id_recorrencia, fk_id_usuario)
        REFERENCES {$recurrence}(id_recorrencia, fk_id_usuario),
    CONSTRAINT ck_compromisso_valor
        CHECK (valor >= 0),
    CONSTRAINT ck_compromisso_tipo
        CHECK (tipo IN ('PADRAO', 'TRANSFERENCIA')),
    CONSTRAINT ck_compromisso_natureza
        CHECK (natureza IN ('ENTRADA', 'SAIDA')),
    CONSTRAINT ck_compromisso_mes
        CHECK (DAY(mes_referencia) = 1),
    CONSTRAINT ck_compromisso_status
        CHECK (status IN ('PENDENTE', 'EFETIVADO', 'EXCLUIDO')),
    CONSTRAINT uq_compromisso_recorrencia_mes
        UNIQUE (fk_id_recorrencia, mes_referencia),
    CONSTRAINT uq_compromisso_id_usuario
        UNIQUE (id_compromisso, fk_id_usuario),
    KEY idx_comp_usuario_mes (fk_id_usuario, mes_referencia),
    KEY idx_comp_categoria_usuario (fk_id_categoria, fk_id_usuario),
    KEY idx_comp_recorrencia_usuario (fk_id_recorrencia, fk_id_usuario),
    KEY idx_comp_status_usuario (fk_id_usuario, status)
) {$charset};

CREATE TABLE IF NOT EXISTS {$transfer} (
    fk_id_compromisso BIGINT UNSIGNED PRIMARY KEY,
    fk_id_usuario BIGINT UNSIGNED NOT NULL,
    fk_id_conta_origem BIGINT UNSIGNED NOT NULL,
    fk_id_conta_destino BIGINT UNSIGNED NOT NULL,
    CONSTRAINT fk_transferencia_usuario
        FOREIGN KEY (fk_id_usuario) REFERENCES {$usersTable}(ID),
    CONSTRAINT fk_transferencia_compromisso
        FOREIGN KEY (fk_id_compromisso, fk_id_usuario)
        REFERENCES {$commitment}(id_compromisso, fk_id_usuario),
    CONSTRAINT fk_transferencia_origem
        FOREIGN KEY (fk_id_conta_origem, fk_id_usuario)
        REFERENCES {$account}(id_conta, fk_id_usuario),
    CONSTRAINT fk_transferencia_destino
        FOREIGN KEY (fk_id_conta_destino, fk_id_usuario)
        REFERENCES {$account}(id_conta, fk_id_usuario),
    CONSTRAINT ck_transferencia_contas_diferentes
        CHECK (fk_id_conta_origem <> fk_id_conta_destino),
    KEY idx_transferencia_usuario (fk_id_usuario),
    KEY idx_transferencia_origem_usuario (fk_id_conta_origem, fk_id_usuario),
    KEY idx_transferencia_destino_usuario (fk_id_conta_destino, fk_id_usuario)
) {$charset};

CREATE TABLE IF NOT EXISTS {$entry} (
    id_lancamento BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    fk_id_usuario BIGINT UNSIGNED NOT NULL,
    fk_id_compromisso BIGINT UNSIGNED,
    fk_id_conta BIGINT UNSIGNED NOT NULL,
    origem VARCHAR(15) NOT NULL,
    nome VARCHAR(180) NOT NULL,
    valor DECIMAL(14,2) NOT NULL,
    tipo_efeito VARCHAR(7) NOT NULL,
    data_efetivacao DATETIME NOT NULL,
    descricao VARCHAR(500),
    estado VARCHAR(10) NOT NULL DEFAULT 'ATIVO',
    criado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    desfeito_em DATETIME,
    id_compromisso_ativo BIGINT UNSIGNED
        GENERATED ALWAYS AS (
            CASE
                WHEN origem = 'COMPROMISSO' AND estado = 'ATIVO'
                THEN fk_id_compromisso
                ELSE NULL
            END
        ) STORED,
    id_conta_compromisso_ativa BIGINT UNSIGNED
        GENERATED ALWAYS AS (
            CASE
                WHEN origem = 'COMPROMISSO' AND estado = 'ATIVO'
                THEN fk_id_conta
                ELSE NULL
            END
        ) STORED,
    id_conta_saldo_inicial_ativo BIGINT UNSIGNED
        GENERATED ALWAYS AS (
            CASE
                WHEN origem = 'SALDO_INICIAL' AND estado = 'ATIVO'
                THEN fk_id_conta
                ELSE NULL
            END
        ) STORED,
    CONSTRAINT fk_lancamento_usuario
        FOREIGN KEY (fk_id_usuario) REFERENCES {$usersTable}(ID),
    CONSTRAINT fk_lancamento_compromisso
        FOREIGN KEY (fk_id_compromisso, fk_id_usuario)
        REFERENCES {$commitment}(id_compromisso, fk_id_usuario),
    CONSTRAINT fk_lancamento_conta
        FOREIGN KEY (fk_id_conta, fk_id_usuario)
        REFERENCES {$account}(id_conta, fk_id_usuario),
    CONSTRAINT ck_lancamento_origem
        CHECK (origem IN ('COMPROMISSO', 'SALDO_INICIAL')),
    CONSTRAINT ck_lancamento_origem_compromisso
        CHECK (
            (
                origem = 'COMPROMISSO'
                AND fk_id_compromisso IS NOT NULL
                AND valor >= 0
            )
            OR
            (
                origem = 'SALDO_INICIAL'
                AND fk_id_compromisso IS NULL
            )
        ),
    CONSTRAINT ck_lancamento_tipo_efeito
        CHECK (tipo_efeito IN ('ENTRADA', 'SAIDA')),
    CONSTRAINT ck_lancamento_saldo_inicial
        CHECK (
            origem <> 'SALDO_INICIAL'
            OR tipo_efeito = 'ENTRADA'
        ),
    CONSTRAINT ck_lancamento_estado
        CHECK (estado IN ('ATIVO', 'DESFEITO')),
    CONSTRAINT ck_lancamento_desfazimento
        CHECK (
            (
                estado = 'ATIVO'
                AND desfeito_em IS NULL
            )
            OR
            (
                estado = 'DESFEITO'
                AND desfeito_em IS NOT NULL
            )
        ),
    CONSTRAINT uq_lancamento_efeito_ativo
        UNIQUE (id_compromisso_ativo, id_conta_compromisso_ativa),
    CONSTRAINT uq_lancamento_saldo_inicial_ativo
        UNIQUE (id_conta_saldo_inicial_ativo),
    KEY idx_lanc_usuario_data (fk_id_usuario, data_efetivacao),
    KEY idx_lanc_compromisso_usuario (fk_id_compromisso, fk_id_usuario),
    KEY idx_lanc_conta_usuario (fk_id_conta, fk_id_usuario),
    KEY idx_lanc_estado_usuario (fk_id_usuario, estado)
) {$charset};
SQL;
    }
}
