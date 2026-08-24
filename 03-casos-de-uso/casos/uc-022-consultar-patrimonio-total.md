## UC-022 — Consultar patrimônio total

**Objetivo**

Permitir que o usuário visualize o patrimônio total calculado a partir das contas e movimentações financeiras.

**Ator principal**

Usuário.

**Pré-condições**

O usuário deverá estar autenticado.

**Gatilho**

O usuário solicita a consulta do patrimônio.

**Fluxo principal**

1. O usuário acessa a informação de patrimônio.
2. O sistema considera os saldos das contas pertencentes ao usuário.
3. O sistema calcula o patrimônio total conforme as regras de negócio.
4. O sistema apresenta o resultado.

**Fluxos alternativos**

* O patrimônio deverá refletir as alterações ocorridas nas contas.
* Transferências entre contas próprias não deverão alterar o patrimônio total.

**Pós-condições**

Nenhum dado financeiro independente será criado pela consulta.

**Requisitos relacionados**

RF-007, RF-021.

**Regras de negócio relacionadas**

Regras dos módulos Contas, Lançamentos Financeiros e Transferências.
