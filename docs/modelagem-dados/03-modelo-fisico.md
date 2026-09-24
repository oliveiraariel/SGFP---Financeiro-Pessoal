# Etapa 8 — Modelo Físico

**Status:** baseline revisada em 14/09/2026.

## Resultado

O Modelo Físico da V1 passa a utilizar cinco tabelas financeiras:

- `CONTA_FINANCEIRA`;
- `CATEGORIA`;
- `RECORRENCIA`;
- `COMPROMISSO_FINANCEIRO`;
- `LANCAMENTO_FINANCEIRO`.

Decisões:
- usuários/autenticação permanecem em `wp_users`;
- `CONTA_FINANCEIRA.FK_ID_USUARIO` é único: exatamente uma conta por usuário na aplicação;
- não existem `PAPEL`, Conta Principal ou Conta Secundária;
- saldo é derivado dos lançamentos ativos;
- Categoria é opcional para Compromisso;
- Transferência foi removida da V1;
- Compromisso possui natureza Entrada/Saída;
- desfazimento preserva histórico dos lançamentos;
- reset/exclusão exigem remoção explícita dos registros em ordem segura pela aplicação.

A tabela técnica `sgfp_token_restauracao` pode continuar existindo como suporte de restauração; não é entidade financeira.

## Artefato
- [Modelo Físico MySQL/MariaDB](artefatos/modelo-fisico/sgfp-modelo-fisico-mysql.sql)

## Reconciliação de implementação

A API havia sido construída inicialmente sobre a baseline anterior de seis tabelas. A Etapa 10 foi posteriormente reconciliada e validada para a baseline simplificada da V1, conforme o estado de governança vigente do projeto. Este documento permanece como referência do Modelo Físico ativo da V1.
