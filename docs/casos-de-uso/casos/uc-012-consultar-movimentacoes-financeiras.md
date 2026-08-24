## UC-012 — Consultar movimentações financeiras

**Objetivo**

Permitir que o usuário consulte os lançamentos e demais registros financeiros efetivamente realizados.

**Ator principal**

Usuário.

**Pré-condições**

O usuário deverá estar autenticado.

**Gatilho**

O usuário solicita a consulta do histórico financeiro.

**Fluxo principal**

1. O usuário acessa o histórico.
2. O sistema apresenta as movimentações do período selecionado.
3. O usuário poderá navegar entre períodos.
4. O sistema apresenta os registros correspondentes.
5. O usuário poderá consultar o detalhamento de uma movimentação.

**Fluxos alternativos**

* O usuário poderá consultar períodos anteriores mesmo antes do início da utilização do sistema, caso tenha registrado informações nesses períodos.

**Pós-condições**

Nenhum dado será alterado pela consulta.

**Requisitos relacionados**

RF-014, RF-015, RF-022.

**Regras de negócio relacionadas**

Regras dos módulos Lançamentos Financeiros e Dashboard.
