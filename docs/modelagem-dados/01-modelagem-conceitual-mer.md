# Etapa 6 — Modelagem Conceitual (MER)

**Status:** baseline revisada em 14/09/2026.

## Entidades da V1

1. **Usuário** — identidade fornecida por WordPress; usado para propriedade/cardinalidade.
2. **Conta Financeira** — exatamente uma por usuário.
3. **Categoria** — pertence ao usuário e pode classificar compromissos.
4. **Recorrência** — configura repetição mensal.
5. **Compromisso Financeiro** — previsão/obrigação de Entrada ou Saída.
6. **Lançamento Financeiro** — movimento realizado que produz efeito sobre a Conta Financeira.

## Relações e cardinalidades

- Usuário 1 — 1 Conta Financeira.
- Usuário 1 — 0..N Categoria.
- Usuário 1 — 0..N Recorrência.
- Usuário 1 — 0..N Compromisso Financeiro.
- Usuário 1 — 0..N Lançamento Financeiro.
- Categoria 0..1 — 0..N Compromisso Financeiro.
- Recorrência 0..1 — 0..N Compromisso Financeiro.
- Compromisso Financeiro 0..1 — 0..N Lançamento Financeiro.
- Conta Financeira 1 — 0..N Lançamento Financeiro.

## Invariantes

- A conta é provisionada automaticamente como **Minha Conta**.
- O saldo não é entidade nem atributo armazenado; é derivado dos lançamentos ativos da conta.
- Categoria é opcional no Compromisso.
- Criar Compromisso não altera saldo.
- Efetivar Compromisso origina Lançamento.
- Desfazer efetivação remove o efeito financeiro preservando histórico.
- Transferência não integra a V1.
- Patrimônio Total não integra a V1.

## Artefatos visuais anteriores

Arquivos gráficos/editáveis anteriores que exibam Transferência ou múltiplas contas são históricos da baseline anterior e não prevalecem sobre este documento até serem regenerados.

## Histórico
- 14/09/2026: retirada de Transferência e múltiplas contas; relação Usuário–Conta alterada para 1:1.
