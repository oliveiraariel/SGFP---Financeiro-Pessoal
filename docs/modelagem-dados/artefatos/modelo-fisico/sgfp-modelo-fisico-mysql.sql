-- ============================================================
-- SGFP - MODELO FISICO V8
-- MYSQL / MARIADB + WORDPRESS
-- Revisao: 14/09/2026
-- Baseline: V1 simplificada
-- ============================================================
--
-- BASELINE FUNCIONAL CONSIDERADA
--
-- - Catalogo vigente: RF-001 a RF-023.
-- - V1 ativa: 19 requisitos funcionais.
-- - RF-012, RF-013 e RF-014 (TRANSFERENCIAS): versao futura.
-- - RF-019 (PIN): versao futura.
-- - RF-022: RESETAR PERFIL FINANCEIRO.
-- - RF-023: EXCLUIR CONTA DE ACESSO.
--
-- PRINCIPAIS DECISOES DA V1
--
-- 1. CADA USUARIO POSSUI EXATAMENTE UMA CONTA FINANCEIRA.
--    A APLICACAO A PROVISIONA AUTOMATICAMENTE COM O NOME
--    INICIAL "Minha Conta".
--
-- 2. NAO EXISTEM CONTA PRINCIPAL, CONTA SECUNDARIA, PAPEL DE
--    CONTA OU TROCA DE CONTA PRINCIPAL NA V1.
--
-- 3. SALDO NAO E ARMAZENADO NA CONTA. ELE E SEMPRE DERIVADO
--    DOS LANCAMENTOS FINANCEIROS ATIVOS DA CONTA.
--
-- 4. TRANSFERENCIAS E PATRIMONIO TOTAL NAO INTEGRAM A V1.
--
-- 5. BACKUP/RESTAURACAO SAO PROCESSOS DA APLICACAO.
--    O BACKUP MANUAL E ENTREGUE LOCALMENTE EM ARQUIVO ZIP.
--    NAO HA TABELA FINANCEIRA ESPECIFICA PARA BACKUP.
--
-- 6. RESET DO PERFIL E EXCLUSAO DA CONTA DE ACESSO SAO
--    OPERACOES DA APLICACAO. O RESET MANTEM wp_users; A
--    EXCLUSAO DE ACESSO REMOVE OS DADOS SGFP E O LOGIN.
-- ============================================================
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
--    ASSUMINDO O PREFIXO PADRAO "wp_" SOMENTE PARA FINS
--    DIDATICOS. NA IMPLEMENTACAO REAL DO PLUGIN, A TABELA
--    DE USUARIOS DEVE SER OBTIDA PELO WORDPRESS, POR EXEMPLO
--    POR $wpdb->users.
--
-- 4. AS FOREIGN KEYS PARA wp_users(ID) EXIGEM ENGINE
--    COMPATIVEL COM INTEGRIDADE REFERENCIAL (INNODB).
--
-- 5. NAO FOI DEFINIDO ON DELETE CASCADE PARA wp_users.
--    RESET E EXCLUSAO DE ACESSO DEVEM SER ORQUESTRADOS
--    EXPLICITAMENTE PELA APLICACAO, EM ORDEM SEGURA.
--
-- 6. O BACKEND DEVE SEMPRE OBTER FK_ID_USUARIO DA SESSAO DO
--    WORDPRESS. O CLIENTE NAO DEVE INFORMAR FK_ID_USUARIO
--    ARBITRARIO COMO AUTORIDADE PARA OPERACOES PRIVADAS.
--
-- 7. O TEMA CLARO/ESCURO E PREFERENCIA DO USUARIO NO
--    WORDPRESS (wp_usermeta). NAO HA TABELA SGFP PARA TEMA.
--
-- 8. O PIN NAO FAZ PARTE DA V1.
--
-- 9. AS CATEGORIAS INICIAIS NAO SAO GLOBAIS. SAO CRIADAS
--    INDIVIDUALMENTE PARA CADA NOVO USUARIO E DEPOIS PODEM
--    SER RENOMEADAS, EXCLUIDAS OU COMPLEMENTADAS.
--
-- 10. CONVENCAO DIDATICA DE NOMENCLATURA:
--     TODA COLUNA QUE ATUA COMO CHAVE ESTRANGEIRA RECEBE
--     O PREFIXO FK_.
-- ============================================================


-- ============================================================
-- REMOCAO DAS TABELAS
-- ORDEM INVERSA DAS DEPENDENCIAS
-- ============================================================

DROP TABLE IF EXISTS LANCAMENTO_FINANCEIRO;

-- Limpeza de legado da V7. TRANSFERENCIA nao sera recriada.
DROP TABLE IF EXISTS TRANSFERENCIA;

DROP TABLE IF EXISTS COMPROMISSO_FINANCEIRO;
DROP TABLE IF EXISTS RECORRENCIA;
DROP TABLE IF EXISTS CATEGORIA;
DROP TABLE IF EXISTS CONTA_FINANCEIRA;


-- ============================================================
-- CONTA FINANCEIRA
-- ============================================================
--
-- - Cada usuario possui exatamente UMA conta na V1.
-- - A aplicacao cria essa conta automaticamente no
--   provisionamento inicial do usuario.
-- - Nome inicial: "Minha Conta".
-- - O usuario pode renomear a conta.
-- - A conta nao armazena saldo.
-- - O saldo e sempre derivado dos lancamentos ATIVOS.
-- - A conta nao pode ser excluida isoladamente na V1.
--
-- O UNIQUE(FK_ID_USUARIO) garante NO MAXIMO uma conta por
-- usuario no banco. A existencia obrigatoria da conta e uma
-- invariavel de provisionamento da aplicacao.
-- ============================================================

CREATE TABLE CONTA_FINANCEIRA(
    ID_CONTA BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    FK_ID_USUARIO BIGINT UNSIGNED NOT NULL,
    NOME VARCHAR(120) NOT NULL DEFAULT 'Minha Conta',
    CRIADA_EM DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT FK_CONTA_USUARIO
        FOREIGN KEY(FK_ID_USUARIO)
        REFERENCES wp_users(ID),

    CONSTRAINT UQ_CONTA_USUARIO
        UNIQUE(FK_ID_USUARIO),

    /*
       Chave alternativa usada pelas FKs compostas para
       garantir pertencimento ao mesmo usuario.
    */
    CONSTRAINT UQ_CONTA_ID_USUARIO
        UNIQUE(ID_CONTA, FK_ID_USUARIO)
) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_unicode_ci;


-- ============================================================
-- CATEGORIA
-- ============================================================
--
-- - Categorias pertencem ao usuario, nao a conta.
-- - Categorias iniciais sao provisionadas por usuario.
-- - A associacao ao Compromisso Financeiro e opcional.
-- - FK_ID_CATEGORIA pode permanecer NULL desde o cadastro.
-- - Excluir categoria nao exclui compromissos associados.
--
-- A exclusao deve ocorrer na aplicacao, em transacao:
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
        UNIQUE(ID_CATEGORIA, FK_ID_USUARIO),

    KEY IDX_CATEGORIA_USUARIO(FK_ID_USUARIO)
) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_unicode_ci;


-- ============================================================
-- RECORRENCIA
-- ============================================================
--
-- - A V1 possui apenas recorrencia mensal.
-- - INICIO_MES e ENCERRADA_NO_MES usam sempre o primeiro dia
--   do mes como representacao fisica do periodo mes/ano.
-- - QUANTIDADE_MESES NULL = recorrencia sem termino definido.
-- - ENCERRADA_NO_MES = encerramento antecipado/manual.
-- - A estrategia de materializacao das ocorrencias futuras e
--   responsabilidade da aplicacao.
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
-- - Todo compromisso da V1 representa previsao de ENTRADA
--   ou SAIDA.
-- - NATUREZA e obrigatoria.
-- - Nao existe TIPO=TRANSFERENCIA na V1.
-- - Criar compromisso NAO altera o saldo.
-- - O efeito financeiro ocorre somente quando o compromisso
--   e efetivado e origina um LANCAMENTO_FINANCEIRO.
-- - STATUS e mantido por simplicidade de consulta.
-- - Compromisso efetivado deve ser desfeito antes de editar
--   ou excluir, conforme as regras da aplicacao.
-- ============================================================

CREATE TABLE COMPROMISSO_FINANCEIRO(
    ID_COMPROMISSO BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

    FK_ID_USUARIO BIGINT UNSIGNED NOT NULL,
    FK_ID_CATEGORIA BIGINT UNSIGNED,
    FK_ID_RECORRENCIA BIGINT UNSIGNED,

    NOME VARCHAR(180) NOT NULL,
    VALOR DECIMAL(14,2) NOT NULL,
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

    /* Valor zero continua permitido pelas regras da V1. */
    CONSTRAINT CK_COMPROMISSO_VALOR
        CHECK(VALOR >= 0),

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
-- LANCAMENTO FINANCEIRO
-- ============================================================
--
-- Um lancamento representa uma movimentacao REALIZADA.
--
-- ORIGEM = COMPROMISSO
--   - criado pela efetivacao de um compromisso;
--   - FK_ID_COMPROMISSO e obrigatorio;
--   - VALOR deve ser >= 0;
--   - TIPO_EFEITO define ENTRADA ou SAIDA.
--
-- ORIGEM = SALDO_INICIAL
--   - representa o valor existente na Conta Financeira no
--     inicio da utilizacao;
--   - FK_ID_COMPROMISSO permanece NULL;
--   - TIPO_EFEITO obrigatoriamente ENTRADA;
--   - VALOR pode ser positivo, zero ou negativo, conforme
--     UC-006.
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
       Impede mais de um efeito ATIVO do mesmo compromisso.
       Registros DESFEITOS continuam preservados no historico.
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

    CONSTRAINT UQ_LANCAMENTO_COMPROMISSO_ATIVO
        UNIQUE(ID_COMPROMISSO_ATIVO),

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
-- PROVISIONAMENTO INICIAL
-- - WordPress cria a identidade do usuario;
-- - SGFP cria exatamente uma Conta Financeira "Minha Conta";
-- - SGFP cria as categorias padrao do usuario;
-- - o provisionamento somente e considerado concluido quando
--   o estado inicial obrigatorio estiver consistente.
--
-- CONTA
-- - existe exatamente uma conta por usuario na V1;
-- - a conta pode ser renomeada;
-- - nao pode ser criada conta adicional;
-- - nao pode ser excluida isoladamente;
-- - saldo = soma dos lancamentos ATIVOS da conta.
--
-- CATEGORIA
-- - a associacao ao compromisso e opcional;
-- - FK_ID_CATEGORIA pode permanecer NULL;
-- - ao excluir categoria, desvincular compromissos e excluir
--   a categoria na mesma unidade transacional.
--
-- COMPROMISSO
-- - todo compromisso possui NATUREZA ENTRADA ou SAIDA;
-- - criar compromisso nao altera saldo;
-- - efetivacao cria um LANCAMENTO_FINANCEIRO ATIVO;
-- - compromisso efetivado deve ser desfeito antes de editar
--   ou excluir;
-- - STATUS e lancamento devem ser atualizados de forma
--   consistente pela camada de Service.
--
-- SALDO INICIAL
-- - usa ORIGEM = SALDO_INICIAL e TIPO_EFEITO = ENTRADA;
-- - pode ser positivo, zero ou negativo;
-- - pertence a unica Conta Financeira do usuario;
-- - no maximo um saldo inicial ATIVO por conta.
--
-- RECORRENCIA
-- - periodicidade mensal;
-- - alteracao/exclusao pode atingir apenas o mes atual ou
--   o mes atual e os seguintes;
-- - periodos anteriores permanecem preservados;
-- - recorrencia encerrada nao e reativada.
--
-- BACKUP / RESTAURACAO
-- - backup manual local em ZIP e processo da aplicacao;
-- - nenhuma tabela financeira adicional e necessaria;
-- - restauracao e integral, sem merge;
-- - antes da substituicao, a aplicacao deve gerar uma copia
--   pre-restauracao recuperavel.
--
-- RESETAR PERFIL FINANCEIRO
-- - exige dupla confirmacao e a frase exata RESETAR PERFIL;
-- - preserva a identidade/login WordPress;
-- - remove os dados SGFP em ordem segura;
-- - reprovisiona Minha Conta e categorias padrao;
-- - restaura preferencias SGFP aos valores padrao.
--
-- EXCLUIR CONTA DE ACESSO
-- - exige dupla confirmacao e a frase exata EXCLUIR CONTA;
-- - remove os dados SGFP em ordem segura;
-- - depois remove a identidade/login correspondente no
--   WordPress;
-- - nao deve ser reportada como concluida se o login ainda
--   permanecer ativo.
--
-- ORDEM SUGERIDA PARA LIMPEZA DOS DADOS SGFP DE UM USUARIO
-- 1. LANCAMENTO_FINANCEIRO;
-- 2. COMPROMISSO_FINANCEIRO;
-- 3. RECORRENCIA;
-- 4. CATEGORIA;
-- 5. CONTA_FINANCEIRA.
--
-- USUARIO / WORDPRESS
-- - FK_ID_USUARIO vem da autenticacao WordPress;
-- - toda consulta e mutacao privada deve ser filtrada pelo
--   usuario autenticado;
-- - nao aceitar FK_ID_USUARIO do cliente como autoridade.
-- ============================================================


-- ============================================================
-- CONSULTA CONCEITUAL DE SALDO
-- ============================================================
--
-- SELECT COALESCE(SUM(
--     CASE
--         WHEN TIPO_EFEITO = 'ENTRADA' THEN VALOR
--         WHEN TIPO_EFEITO = 'SAIDA'   THEN -VALOR
--     END
-- ), 0) AS SALDO
-- FROM LANCAMENTO_FINANCEIRO
-- WHERE FK_ID_USUARIO = <USUARIO_WORDPRESS_AUTENTICADO>
--   AND FK_ID_CONTA = <CONTA_UNICA>
--   AND ESTADO = 'ATIVO';
--
-- O mesmo calculo aceita saldo inicial negativo, pois o
-- lancamento SALDO_INICIAL e do tipo ENTRADA e seu VALOR pode
-- ser negativo conforme UC-006.
--
-- PATRIMONIO TOTAL NAO INTEGRA A V1.
-- TRANSFERENCIAS NAO INTEGRAM A V1.
-- ============================================================
