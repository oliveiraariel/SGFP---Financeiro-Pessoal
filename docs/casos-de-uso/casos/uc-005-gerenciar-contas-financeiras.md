## UC-005 — Gerenciar contas financeiras

**Objetivo**

Permitir que o usuário mantenha suas contas financeiras.

**Ator principal**

Usuário.

**Pré-condições**

O usuário deverá estar autenticado.

**Gatilho**

O usuário acessa a área de contas.

**Fluxo principal**

1. O usuário acessa as contas financeiras.
2. O sistema apresenta as contas existentes.
3. O usuário poderá criar uma nova conta.
4. O sistema registra a conta sem saldo inicial próprio.
5. O usuário poderá alterar o nome de uma conta.
6. O sistema atualiza o nome sem modificar o histórico financeiro associado.
7. O sistema atualiza a apresentação das contas.

**Fluxos alternativos e exceções**

* O usuário poderá possuir múltiplas contas.
* Deverá existir apenas uma conta principal.
* As contas secundárias obedecerão às regras específicas de movimentação definidas no levantamento.

**Pós-condições**

As contas existentes estarão atualizadas de acordo com a operação realizada.

**Requisitos relacionados**

RF-004, RF-005.

**Regras de negócio relacionadas**

Regras do módulo Contas referentes a criação, nomenclatura, conta principal, contas secundárias e saldo.
