## UC-017 — Navegar entre períodos financeiros

**Objetivo**

Permitir que o usuário consulte e opere sobre diferentes meses.

**Ator principal**

Usuário.

**Pré-condições**

O usuário deverá estar autenticado.

**Gatilho**

O usuário solicita alteração do período visualizado.

**Fluxo principal**

1. O usuário seleciona um mês e ano.
2. O sistema carrega os dados correspondentes.
3. O sistema atualiza as informações apresentadas.
4. O usuário poderá consultar ou cadastrar informações naquele período conforme as funcionalidades disponíveis.

**Fluxos alternativos**

* O usuário poderá navegar para períodos anteriores.
* O usuário poderá navegar para períodos posteriores.
* O usuário poderá registrar histórico em períodos anteriores ao início da utilização do sistema.

**Pós-condições**

O período selecionado estará disponível para consulta e operação.

**Requisitos relacionados**

RF-015, RF-016, RF-022.

**Regras de negócio relacionadas**

Regras dos módulos Lançamentos Financeiros, Recorrências e Dashboard.
