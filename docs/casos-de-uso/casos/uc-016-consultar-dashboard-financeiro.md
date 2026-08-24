## UC-016 — Consultar Dashboard financeiro

**Objetivo**

Permitir que o usuário acompanhe a situação financeira consolidada de um período.

**Ator principal**

Usuário.

**Pré-condições**

O usuário deverá estar autenticado.

**Gatilho**

O usuário acessa o Dashboard.

**Fluxo principal**

1. O sistema identifica o mês e ano correntes ou o período previamente selecionado.
2. O sistema calcula o saldo inicial do período.
3. O sistema calcula as entradas previstas.
4. O sistema calcula as saídas previstas.
5. O sistema calcula o saldo final previsto.
6. O sistema apresenta os compromissos que compõem os valores.
7. O usuário poderá acessar o detalhamento de um compromisso.

**Fluxos alternativos**

* O usuário poderá navegar para outro mês.
* Alterações, inclusões, exclusões, efetivações ou desfazimentos que afetem o período deverão ser refletidos no Dashboard.

**Pós-condições**

Nenhum dado financeiro próprio do Dashboard será criado ou alterado.

**Requisitos relacionados**

RF-021, RF-022.

**Regras de negócio relacionadas**

Regras do módulo Dashboard.
