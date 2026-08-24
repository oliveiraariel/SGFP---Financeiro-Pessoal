## UC-009 — Efetivar compromisso financeiro

**Objetivo**

Permitir que o usuário confirme que uma movimentação prevista realmente ocorreu.

**Ator principal**

Usuário.

**Pré-condições**

O compromisso deverá estar registrado e ainda não efetivado.

**Gatilho**

O usuário solicita a efetivação do compromisso.

**Fluxo principal**

1. O usuário seleciona um compromisso pendente.
2. O sistema apresenta os dados da efetivação.
3. O sistema apresenta a data atual como padrão.
4. O usuário confirma a data ou informa outra data.
5. O usuário confirma a efetivação.
6. O sistema registra o lançamento correspondente.
7. O sistema aplica o efeito financeiro à conta apropriada.
8. O sistema atualiza os saldos e as visões dependentes.

**Fluxos alternativos e exceções**

* Uma Entrada deverá aumentar o saldo.
* Uma Saída deverá diminuir o saldo.
* O usuário poderá informar uma data diferente da data atual.
* Um compromisso recorrente deverá obedecer às regras de aplicação da alteração do período.

**Pós-condições**

O compromisso estará efetivado e a movimentação correspondente estará registrada.

**Requisitos relacionados**

RF-007, RF-010.

**Regras de negócio relacionadas**

Regras dos módulos Compromissos e Lançamentos Financeiros.
