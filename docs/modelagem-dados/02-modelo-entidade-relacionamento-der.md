# Etapa 7 — Modelo Entidade-Relacionamento (DER)

**Status:** baseline textual revisada em 14/09/2026.

## Estruturas relacionais da V1

- `CONTA_FINANCEIRA`
- `CATEGORIA`
- `RECORRENCIA`
- `COMPROMISSO_FINANCEIRO`
- `LANCAMENTO_FINANCEIRO`

`TRANSFERENCIA` não integra mais o DER ativo da V1.

## Relações principais

- `CONTA_FINANCEIRA.FK_ID_USUARIO` referencia `wp_users(ID)` e é **UNIQUE**, garantindo no máximo uma conta por usuário; o provisionamento garante a existência da conta.
- Categoria, Recorrência, Compromisso e Lançamento permanecem escopados por usuário.
- Compromisso pode referenciar Categoria e Recorrência.
- Lançamento referencia obrigatoriamente Conta Financeira e opcionalmente Compromisso.
- Saldo é consulta derivada sobre Lançamentos ativos.

## Simplificações

Foram removidos do modelo ativo:
- `PAPEL`;
- `ID_USUARIO_PRINCIPAL`;
- distinção PRINCIPAL/SECUNDARIA;
- especialização/tabela Transferência;
- `TIPO=TRANSFERENCIA` no Compromisso;
- Patrimônio Total como informação própria da V1.

## Artefatos gráficos

O DER gráfico anterior permanece como histórico até ser regenerado. Em divergência, este documento e o Modelo Físico revisado prevalecem.
