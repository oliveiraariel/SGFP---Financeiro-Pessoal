## UC-013 — Gerenciar transferências

**Objetivo**

Permitir que o usuário movimente valores entre suas próprias contas.

**Ator principal**

Usuário.

**Pré-condições**

O usuário deverá possuir pelo menos duas contas válidas para a transferência.

**Gatilho**

O usuário solicita uma nova transferência.

**Fluxo principal**

1. O usuário seleciona a conta de origem.
2. O usuário seleciona a conta de destino.
3. O usuário informa o valor.
4. O sistema valida as contas e o valor.
5. O sistema registra a transferência.
6. Enquanto pendente, a transferência não altera os saldos.
7. O usuário poderá efetivar a transferência.
8. O sistema retira o valor da origem e adiciona o valor ao destino.
9. O sistema registra os efeitos nos históricos das duas contas.

**Fluxos alternativos e exceções**

* Conta de origem e conta de destino deverão ser diferentes.
* Na V1, a conta de origem ou destino deverá respeitar as regras definidas para conta principal e conta secundária.
* A insuficiência de saldo não deverá bloquear a transferência.
* Uma transferência efetivada deverá ser desfeita antes de ser alterada ou excluída.
* A transferência não deverá alterar o patrimônio total do usuário.

**Pós-condições**

A transferência estará registrada com seus efeitos financeiros consistentes quando efetivada.

**Requisitos relacionados**

RF-012, RF-013.

**Regras de negócio relacionadas**

Regras do módulo Transferências e do módulo Contas.
