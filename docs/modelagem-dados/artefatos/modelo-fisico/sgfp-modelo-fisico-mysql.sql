-- ============================================================
-- SGFP - MODELO FISICO V7
-- MYSQL / MARIADB + WORDPRESS
-- Revisao: 05/09/2026
-- ============================================================
--
-- BASELINE FUNCIONAL CONSIDERADA
--
-- - Catalogo preservado: RF-001 a RF-021.
-- - V1 ativa: 20 requisitos funcionais.
-- - RF-019 (PIN): adiado para versao futura.
--
-- DIRETRIZES DE INTEGRACAO COM WORDPRESS
--
-- 1. USUARIO, E-MAIL, SENHA, HASH, SESSAO E RECUPERACAO DE
--    SENHA SAO RESPONSABILIDADES DO WORDPRESS.
--
-- 2. NAO EXISTE TABELA USUARIO PROPRIA DO SGFP NA V1.
--    FK_ID_USUARIO REPRESENTA wp_users.ID (BIGINT UNSIGNED).
--
-- 3. ESTE MODELO DECLARA FOREIGN KEYS PARA wp_users(ID),
--    ASSUMINDO O PREFIXO PADRAO "wp_".
--    NA IMPLEMENTACAO REAL DO PLUGIN, O NOME DA TABELA DE
--    USUARIOS DEVE SER OBTIDO PELO WORDPRESS (EX.: $wpdb->users)
--    E O DDL AJUSTADO CASO O PREFIXO DA INSTALACAO SEJA OUTRO.
--
-- 4. AS FOREIGN KEYS PARA wp_users(ID) EXIGEM QUE A TABELA
--    DE USUARIOS E AS TABELAS DO SGFP UTILIZEM ENGINE
--    COMPATIVEL COM INTEGRIDADE REFERENCIAL (INNODB).
--    NAO FOI DEFINIDO ON DELETE CASCADE: A EXCLUSAO DE USUARIO
--    DEVE SER TRATADA EXPLICITAMENTE PELA APLICACAO.
--
-- 5. O BACKEND DEVE SEMPRE OBTER FK_ID_USUARIO DA SESSAO DO
--    WORDPRESS. O CLIENTE NAO DEVE INFORMAR UM FK_ID_USUARIO
--    ARBITRARIO PARA OPERACOES PRIVADAS.
--
-- 6. O TEMA CLARO/ESCURO SERA PREFERENCIA DO USUARIO NO
--    WORDPRESS (wp_usermeta). NAO HA TABELA SGFP PARA TEMA.
--
-- 7. O PIN NAO FAZ PARTE DA V1. NAO EXISTEM TABELAS,
--    COLUNAS, TOKENS OU MECANISMOS DE PERSISTENCIA DE PIN
--    NESTE MODELO.
--
-- 8. BACKUP E RESTAURACAO SAO PROCESSOS DA APLICACAO.
--    NAO E NECESSARIA TABELA PROPRIA NA V1 APENAS PARA
--    REPRESENTAR A COPIA DE SEGURANCA.
--
-- 9. AS CATEGORIAS INICIAIS NAO SAO GLOBAIS. SAO CRIADAS
--    INDIVIDUALMENTE PARA CADA NOVO USUARIO E DEPOIS PODEM
--    SER RENOMEADAS, EXCLUIDAS OU COMPLEMENTADAS.
--
-- 10. CONVENCAO DIDATICA DE NOMENCLATURA:
--     TODA COLUNA QUE ATUA COMO CHAVE ESTRANGEIRA RECEBE
--     O PREFIXO FK_, POR EXEMPLO: FK_ID_USUARIO,
--     FK_ID_CATEGORIA, FK_ID_CONTA E FK_ID_COMPROMISSO.
-- ============================================================


-- ============================================================
-- REMOCAO DAS TABELAS
-- ORDEM INVERSA DAS DEPENDENCIAS
-- ============================================================

DROP TABLE IF EXISTS LANCAMENTO_FINANCEIRO;
DROP TABLE IF EXISTS TRANSFERENCIA;
DROP TABLE IF EXISTS COMPROMISSO_FINANCEIRO;
DROP TABLE IF EXISTS RECORRENCIA;
DROP TABLE IF EXISTS CATEGORIA;
DROP TABLE IF EXISTS CONTA_FINANCEIRA;

-- ============================================================
-- CONTA FINANCEIRA
-- ============================================================
--
-- - A conta nao armazena saldo.
-- - O saldo e sempre derivado dos lancamentos ativos.
-- - Um usuario pode ter varias contas, mas no maximo uma
--   conta com papel PRINCIPAL.
-- - A V1 nao contempla exclusao de contas.
-- ============================================================

CREATE TABLE CONTA_FINANCEIRA(
    ID_CONTA BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    FK_ID_USUARIO BIGINT UNSIGNED NOT NULL,
    NOME VARCHAR(120) NOT NULL,
    PAPEL VARCHAR(12) NOT NULL,
    CRIADA_EM DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,

    /*
       Coluna tecnica gerada para garantir no maximo uma
       conta PRINCIPAL por usuario.

       PRINCIPAL  -> FK_ID_USUARIO
       SECUNDARIA -> NULL

       UNIQUE permite varios NULL, mas somente uma ocorrencia
       do mesmo FK_ID_USUARIO nao nulo.
    */
    ID_USUARIO_PRINCIPAL BIGINT UNSIGNED
        GENERATED ALWAYS AS (
            CASE
                WHEN PAPEL = 'PRINCIPAL' THEN FK_ID_USUARIO
                ELSE NULL
            END
        ) STORED,

    CONSTRAINT FK_CONTA_USUARIO
        FOREIGN KEY(FK_ID_USUARIO)
        REFERENCES wp_users(ID),

    CONSTRAINT CK_CONTA_PAPEL
        CHECK(PAPEL IN ('PRINCIPAL', 'SECUNDARIA')),

    CONSTRAINT UQ_CONTA_PRINCIPAL_USUARIO
        UNIQUE(ID_USUARIO_PRINCIPAL),

    /*
       Chave alternativa usada pelas FKs compostas para
       garantir pertencimento ao mesmo usuario.
    */
    CONSTRAINT UQ_CONTA_ID_USUARIO
        UNIQUE(ID_CONTA, FK_ID_USUARIO),

    KEY IDX_CONTA_USUARIO(FK_ID_USUARIO)
) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_unicode_ci;


-- ============================================================
-- CATEGORIA
-- ============================================================
--
-- A associacao de Categoria ao Compromisso Financeiro e opcional.
-- FK_ID_CATEGORIA pode permanecer NULL desde o cadastro.
-- Excluir uma categoria nao exclui compromissos anteriormente associados.
--
-- A operacao de exclusao deve ocorrer em transacao:
-- 1. desvincular a categoria dos compromissos do usuario;
-- 2. excluir a categoria.
-- ============================================================

CREATE TABLE CATEGORIA(
    ID_CATEGORIA BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    FK_ID_USUARIO BIGINT UNSIGNED NOT NULL,
    NOME VARCHAR(100) NOT NULL,
    CRIADA_EM DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT FK_CATEGORIA_USUARIO
        FOREIGN KEY(FK_ID_USUARIO)
        REFERENCES wp_users(ID),

    CONSTRAINT UQ_CATEGORIA_NOME_USUARIO
        UNIQUE(FK_ID_USUARIO, NOME),

    CONSTRAINT UQ_CATEGORIA_ID_USUARIO
        UNIQUE(ID_CATEGORIA, FK_ID_USUARIO)
) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_unicode_ci;


-- ============================================================
-- RECORRENCIA
-- ============================================================
--
-- A V1 possui apenas recorrencia mensal.
-- INICIO_MES e ENCERRADA_NO_MES usam sempre o primeiro dia
-- do mes como representacao fisica do periodo mes/ano.
--
-- QUANTIDADE_MESES NULL = recorrencia sem termino definido.
-- ENCERRADA_NO_MES       = encerramento antecipado/manual.
--
-- A estrategia de materializacao das ocorrencias futuras e
-- responsabilidade da aplicacao e deve preservar os meses
-- anteriores.
-- ============================================================

CREATE TABLE RECORRENCIA(
    ID_RECORRENCIA BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    FK_ID_USUARIO BIGINT UNSIGNED NOT NULL,

    INICIO_MES DATE NOT NULL,
    QUANTIDADE_MESES SMALLINT UNSIGNED,
    ENCERRADA_NO_MES DATE,

    CRIADA_EM DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT FK_RECORRENCIA_USUARIO
        FOREIGN KEY(FK_ID_USUARIO)
        REFERENCES wp_users(ID),

    CONSTRAINT CK_RECORRENCIA_INICIO_MES
        CHECK(DAY(INICIO_MES) = 1),

    CONSTRAINT CK_RECORRENCIA_QUANTIDADE
        CHECK(
            QUANTIDADE_MESES IS NULL
            OR QUANTIDADE_MESES > 0
        ),

    CONSTRAINT CK_RECORRENCIA_ENCERRAMENTO_MES
        CHECK(
            ENCERRADA_NO_MES IS NULL
            OR DAY(ENCERRADA_NO_MES) = 1
        ),

    CONSTRAINT CK_RECORRENCIA_ENCERRAMENTO_DATA
        CHECK(
            ENCERRADA_NO_MES IS NULL
            OR ENCERRADA_NO_MES >= INICIO_MES
        ),

    CONSTRAINT UQ_RECORRENCIA_ID_USUARIO
        UNIQUE(ID_RECORRENCIA, FK_ID_USUARIO),

    KEY IDX_RECORRENCIA_USUARIO(FK_ID_USUARIO)
) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_unicode_ci;


-- ============================================================
-- COMPROMISSO FINANCEIRO
-- ============================================================
--
-- TIPO = PADRAO
--   - representa previsao de ENTRADA ou SAIDA;
--   - NATUREZA e obrigatoria.
--
-- TIPO = TRANSFERENCIA
--   - utiliza o mesmo ciclo de compromisso/efetivacao;
--   - nao representa uma entrada ou saida patrimonial isolada;
--   - NATUREZA e obrigatoria e representa o efeito da transferencia
--     sob a perspectiva da Conta Principal:
--       PRINCIPAL  -> SECUNDARIA = SAIDA
--       SECUNDARIA -> PRINCIPAL  = ENTRADA
--   - os efeitos SAIDA/ENTRADA aparecem nos dois lancamentos
--     criados quando a transferencia e efetivada.
--
-- STATUS e mantido por simplicidade de consulta. A aplicacao
-- deve atualizar compromisso e lancamentos na mesma transacao
-- para impedir divergencia de estado.
-- ============================================================

CREATE TABLE COMPROMISSO_FINANCEIRO(
    ID_COMPROMISSO BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

    FK_ID_USUARIO BIGINT UNSIGNED NOT NULL,
    FK_ID_CATEGORIA BIGINT UNSIGNED,
    FK_ID_RECORRENCIA BIGINT UNSIGNED,

    NOME VARCHAR(180) NOT NULL,
    VALOR DECIMAL(14,2) NOT NULL,

    TIPO VARCHAR(15) NOT NULL,
    NATUREZA VARCHAR(7) NOT NULL,

    MES_REFERENCIA DATE NOT NULL,
    STATUS VARCHAR(12) NOT NULL DEFAULT 'PENDENTE',

    CRIADO_EM DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT FK_COMPROMISSO_USUARIO
        FOREIGN KEY(FK_ID_USUARIO)
        REFERENCES wp_users(ID),

    CONSTRAINT FK_COMPROMISSO_CATEGORIA
        FOREIGN KEY(FK_ID_CATEGORIA, FK_ID_USUARIO)
        REFERENCES CATEGORIA(ID_CATEGORIA, FK_ID_USUARIO),

    CONSTRAINT FK_COMPROMISSO_RECORRENCIA
        FOREIGN KEY(FK_ID_RECORRENCIA, FK_ID_USUARIO)
        REFERENCES RECORRENCIA(ID_RECORRENCIA, FK_ID_USUARIO),

    /* Valor zero e permitido pelas regras da V1. */
    CONSTRAINT CK_COMPROMISSO_VALOR
        CHECK(VALOR >= 0),

    CONSTRAINT CK_COMPROMISSO_TIPO
        CHECK(TIPO IN ('PADRAO', 'TRANSFERENCIA')),

    CONSTRAINT CK_COMPROMISSO_NATUREZA
        CHECK(NATUREZA IN ('ENTRADA', 'SAIDA')),

    CONSTRAINT CK_COMPROMISSO_MES
        CHECK(DAY(MES_REFERENCIA) = 1),

    CONSTRAINT CK_COMPROMISSO_STATUS
        CHECK(
            STATUS IN (
                'PENDENTE',
                'EFETIVADO',
                'EXCLUIDO'
            )
        ),

    /*
       Uma recorrencia materializada possui no maximo uma
       ocorrencia de compromisso para cada mes.
       FK_ID_RECORRENCIA NULL permite varios nao recorrentes.
    */
    CONSTRAINT UQ_COMPROMISSO_RECORRENCIA_MES
        UNIQUE(FK_ID_RECORRENCIA, MES_REFERENCIA),

    CONSTRAINT UQ_COMPROMISSO_ID_USUARIO
        UNIQUE(ID_COMPROMISSO, FK_ID_USUARIO),

    KEY IDX_COMP_USUARIO_MES(FK_ID_USUARIO, MES_REFERENCIA),
    KEY IDX_COMP_CATEGORIA_USUARIO(FK_ID_CATEGORIA, FK_ID_USUARIO),
    KEY IDX_COMP_RECORRENCIA_USUARIO(FK_ID_RECORRENCIA, FK_ID_USUARIO),
    KEY IDX_COMP_STATUS_USUARIO(FK_ID_USUARIO, STATUS)
) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_unicode_ci;


-- ============================================================
-- TRANSFERENCIA
-- ============================================================
--
-- A transferencia especializa os dados de um compromisso do
-- TIPO = TRANSFERENCIA.
--
-- A camada de Service deve validar, antes de persistir:
-- - o compromisso e do tipo TRANSFERENCIA;
-- - uma das contas e PRINCIPAL e a outra e SECUNDARIA;
-- - nao existem transferencias diretas SECUNDARIA -> SECUNDARIA;
-- - a NATUREZA corresponde ao fluxo visto pela Conta Principal:
--       PRINCIPAL  -> SECUNDARIA = SAIDA;
--       SECUNDARIA -> PRINCIPAL  = ENTRADA.
--
-- As FKs compostas garantem que compromisso, origem e destino
-- pertencem ao mesmo usuario.
-- ============================================================

CREATE TABLE TRANSFERENCIA(
    FK_ID_COMPROMISSO BIGINT UNSIGNED PRIMARY KEY,
    FK_ID_USUARIO BIGINT UNSIGNED NOT NULL,

    FK_ID_CONTA_ORIGEM BIGINT UNSIGNED NOT NULL,
    FK_ID_CONTA_DESTINO BIGINT UNSIGNED NOT NULL,

    CONSTRAINT FK_TRANSFERENCIA_USUARIO
        FOREIGN KEY(FK_ID_USUARIO)
        REFERENCES wp_users(ID),

    CONSTRAINT FK_TRANSFERENCIA_COMPROMISSO
        FOREIGN KEY(FK_ID_COMPROMISSO, FK_ID_USUARIO)
        REFERENCES COMPROMISSO_FINANCEIRO(
            ID_COMPROMISSO,
            FK_ID_USUARIO
        ),

    CONSTRAINT FK_TRANSFERENCIA_ORIGEM
        FOREIGN KEY(FK_ID_CONTA_ORIGEM, FK_ID_USUARIO)
        REFERENCES CONTA_FINANCEIRA(
            ID_CONTA,
            FK_ID_USUARIO
        ),

    CONSTRAINT FK_TRANSFERENCIA_DESTINO
        FOREIGN KEY(FK_ID_CONTA_DESTINO, FK_ID_USUARIO)
        REFERENCES CONTA_FINANCEIRA(
            ID_CONTA,
            FK_ID_USUARIO
        ),

    CONSTRAINT CK_TRANSFERENCIA_CONTAS_DIFERENTES
        CHECK(FK_ID_CONTA_ORIGEM <> FK_ID_CONTA_DESTINO),

    KEY IDX_TRANSFERENCIA_USUARIO(FK_ID_USUARIO),
    KEY IDX_TRANSFERENCIA_ORIGEM_USUARIO(
        FK_ID_CONTA_ORIGEM,
        FK_ID_USUARIO
    ),
    KEY IDX_TRANSFERENCIA_DESTINO_USUARIO(
        FK_ID_CONTA_DESTINO,
        FK_ID_USUARIO
    )
) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_unicode_ci;


-- ============================================================
-- LANCAMENTO FINANCEIRO
-- ============================================================
--
-- Um lancamento representa uma movimentacao REALIZADA.
--
-- ORIGEM = COMPROMISSO
--   - lancamento criado pela efetivacao de um compromisso;
--   - ID_COMPROMISSO e obrigatorio;
--   - VALOR deve ser >= 0, pois o sentido e dado por
--     TIPO_EFEITO (ENTRADA/SAIDA).
--
-- ORIGEM = SALDO_INICIAL
--   - lancamento direto da conta principal usado apenas para
--     representar o valor existente no inicio da utilizacao;
--   - ID_COMPROMISSO permanece NULL;
--   - TIPO_EFEITO obrigatoriamente ENTRADA;
--   - VALOR pode ser positivo, zero ou negativo, conforme
--     UC-006 e as regras de Contas/Lancamentos.
--
-- Transferencia efetivada:
--   - gera DOIS lancamentos com o mesmo ID_COMPROMISSO;
--   - SAIDA na origem;
--   - ENTRADA no destino.
--
-- O desfazimento preserva o registro historico, alterando
-- ESTADO para DESFEITO e preenchendo DESFEITO_EM.
-- ============================================================

CREATE TABLE LANCAMENTO_FINANCEIRO(
    ID_LANCAMENTO BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

    FK_ID_USUARIO BIGINT UNSIGNED NOT NULL,
    FK_ID_COMPROMISSO BIGINT UNSIGNED,
    FK_ID_CONTA BIGINT UNSIGNED NOT NULL,

    ORIGEM VARCHAR(15) NOT NULL,

    NOME VARCHAR(180) NOT NULL,
    VALOR DECIMAL(14,2) NOT NULL,
    TIPO_EFEITO VARCHAR(7) NOT NULL,

    DATA_EFETIVACAO DATETIME NOT NULL,
    DESCRICAO VARCHAR(500),

    ESTADO VARCHAR(10) NOT NULL DEFAULT 'ATIVO',
    CRIADO_EM DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    DESFEITO_EM DATETIME,

    /*
       Colunas tecnicas para impedir duplicacao de um efeito
       ATIVO do mesmo compromisso na mesma conta, preservando
       quantos registros DESFEITOS forem necessarios.
    */
    ID_COMPROMISSO_ATIVO BIGINT UNSIGNED
        GENERATED ALWAYS AS (
            CASE
                WHEN ORIGEM = 'COMPROMISSO'
                     AND ESTADO = 'ATIVO'
                THEN FK_ID_COMPROMISSO
                ELSE NULL
            END
        ) STORED,

    ID_CONTA_COMPROMISSO_ATIVA BIGINT UNSIGNED
        GENERATED ALWAYS AS (
            CASE
                WHEN ORIGEM = 'COMPROMISSO'
                     AND ESTADO = 'ATIVO'
                THEN FK_ID_CONTA
                ELSE NULL
            END
        ) STORED,

    /*
       Permite no maximo um saldo inicial ATIVO por conta.
       Um saldo inicial desfeito deixa de participar da
       restricao e pode ser substituido por novo registro.
    */
    ID_CONTA_SALDO_INICIAL_ATIVO BIGINT UNSIGNED
        GENERATED ALWAYS AS (
            CASE
                WHEN ORIGEM = 'SALDO_INICIAL'
                     AND ESTADO = 'ATIVO'
                THEN FK_ID_CONTA
                ELSE NULL
            END
        ) STORED,

    CONSTRAINT FK_LANCAMENTO_USUARIO
        FOREIGN KEY(FK_ID_USUARIO)
        REFERENCES wp_users(ID),

    CONSTRAINT FK_LANCAMENTO_COMPROMISSO
        FOREIGN KEY(FK_ID_COMPROMISSO, FK_ID_USUARIO)
        REFERENCES COMPROMISSO_FINANCEIRO(
            ID_COMPROMISSO,
            FK_ID_USUARIO
        ),

    CONSTRAINT FK_LANCAMENTO_CONTA
        FOREIGN KEY(FK_ID_CONTA, FK_ID_USUARIO)
        REFERENCES CONTA_FINANCEIRA(
            ID_CONTA,
            FK_ID_USUARIO
        ),

    CONSTRAINT CK_LANCAMENTO_ORIGEM
        CHECK(ORIGEM IN ('COMPROMISSO', 'SALDO_INICIAL')),

    CONSTRAINT CK_LANCAMENTO_ORIGEM_COMPROMISSO
        CHECK(
            (
                ORIGEM = 'COMPROMISSO'
                AND FK_ID_COMPROMISSO IS NOT NULL
                AND VALOR >= 0
            )
            OR
            (
                ORIGEM = 'SALDO_INICIAL'
                AND FK_ID_COMPROMISSO IS NULL
            )
        ),

    CONSTRAINT CK_LANCAMENTO_TIPO_EFEITO
        CHECK(TIPO_EFEITO IN ('ENTRADA', 'SAIDA')),

    CONSTRAINT CK_LANCAMENTO_SALDO_INICIAL
        CHECK(
            ORIGEM <> 'SALDO_INICIAL'
            OR TIPO_EFEITO = 'ENTRADA'
        ),

    CONSTRAINT CK_LANCAMENTO_ESTADO
        CHECK(ESTADO IN ('ATIVO', 'DESFEITO')),

    CONSTRAINT CK_LANCAMENTO_DESFAZIMENTO
        CHECK(
            (
                ESTADO = 'ATIVO'
                AND DESFEITO_EM IS NULL
            )
            OR
            (
                ESTADO = 'DESFEITO'
                AND DESFEITO_EM IS NOT NULL
            )
        ),

    CONSTRAINT UQ_LANCAMENTO_EFEITO_ATIVO
        UNIQUE(
            ID_COMPROMISSO_ATIVO,
            ID_CONTA_COMPROMISSO_ATIVA
        ),

    CONSTRAINT UQ_LANCAMENTO_SALDO_INICIAL_ATIVO
        UNIQUE(ID_CONTA_SALDO_INICIAL_ATIVO),

    KEY IDX_LANC_USUARIO_DATA(FK_ID_USUARIO, DATA_EFETIVACAO),
    KEY IDX_LANC_COMPROMISSO_USUARIO(
        FK_ID_COMPROMISSO,
        FK_ID_USUARIO
    ),
    KEY IDX_LANC_CONTA_USUARIO(FK_ID_CONTA, FK_ID_USUARIO),
    KEY IDX_LANC_ESTADO_USUARIO(FK_ID_USUARIO, ESTADO)
) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_unicode_ci;


-- ============================================================
-- REGRAS QUE PERMANECEM NA CAMADA DE APLICACAO
-- ============================================================
--
-- CONTA
-- - a primeira utilizacao inicia sem contas;
-- - quando existir controle financeiro, o usuario define uma
--   unica conta principal;
-- - saldo = soma dos lancamentos ATIVOS da conta;
-- - a V1 nao permite exclusao de contas.
--
-- CATEGORIA
-- - a associacao ao compromisso e opcional desde o cadastro;
-- - FK_ID_CATEGORIA pode permanecer NULL;
-- - ao excluir categoria, desvincular compromissos e excluir
--   a categoria na mesma transacao.
--
-- COMPROMISSO
-- - compromisso PADRAO incide sobre a conta PRINCIPAL;
-- - compromisso efetivado deve ser desfeito antes de editar
--   ou excluir;
-- - STATUS e lancamentos devem ser atualizados em transacao.
--
-- TRANSFERENCIA
-- - compromisso relacionado deve ser TIPO = TRANSFERENCIA;
-- - origem/destino devem formar PRINCIPAL <-> SECUNDARIA;
-- - NATUREZA deve ser determinada pela perspectiva da Conta Principal:
--       PRINCIPAL  -> SECUNDARIA = SAIDA;
--       SECUNDARIA -> PRINCIPAL  = ENTRADA;
-- - efetivacao cria dois lancamentos atomicos:
--       SAIDA   na origem;
--       ENTRADA no destino;
-- - desfazimento desfaz os dois lancamentos atomicamente;
-- - saldo insuficiente nao bloqueia transferencia na V1.
--
-- SALDO INICIAL
-- - permitido apenas para a conta PRINCIPAL;
-- - usa ORIGEM = SALDO_INICIAL e TIPO_EFEITO = ENTRADA;
-- - pode ser positivo, zero ou negativo;
-- - conta secundaria recebe valor inicial somente por
--   transferencia com a conta principal.
--
-- RECORRENCIA
-- - periodicidade mensal;
-- - alteracao/exclusao pode atingir apenas o mes atual ou
--   o mes atual e os seguintes;
-- - periodos anteriores permanecem preservados;
-- - recorrencia encerrada nao e reativada.
--
-- USUARIO / WORDPRESS
-- - FK_ID_USUARIO vem da autenticacao WordPress;
-- - toda consulta e mutacao privada deve ser filtrada pelo
--   usuario autenticado;
-- - nao aceitar FK_ID_USUARIO do cliente como autoridade.
-- ============================================================


-- ============================================================
-- CONSULTAS CONCEITUAIS DE SALDO
-- ============================================================
--
-- Saldo de uma conta:
--
-- SELECT COALESCE(SUM(
--     CASE
--         WHEN TIPO_EFEITO = 'ENTRADA' THEN VALOR
--         WHEN TIPO_EFEITO = 'SAIDA'   THEN -VALOR
--     END
-- ), 0) AS SALDO
-- FROM LANCAMENTO_FINANCEIRO
-- WHERE FK_ID_USUARIO = <USUARIO_WORDPRESS_AUTENTICADO>
--   AND FK_ID_CONTA = <CONTA>
--   AND ESTADO = 'ATIVO';
--
-- O mesmo calculo aceita saldo inicial negativo, pois o
-- lançamento SALDO_INICIAL e do tipo ENTRADA e seu VALOR pode
-- ser negativo conforme UC-006.
--
-- Patrimonio total:
-- soma dos saldos de todas as contas do mesmo usuario.
-- Transferencias nao alteram o patrimonio porque produzem
-- uma SAIDA e uma ENTRADA de mesmo valor em contas proprias.
-- ============================================================
