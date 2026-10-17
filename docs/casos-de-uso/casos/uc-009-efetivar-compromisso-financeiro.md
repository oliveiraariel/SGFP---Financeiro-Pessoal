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
3. O sistema apresenta como data financeira padrão a data cadastrada do compromisso ou da ocorrência.
4. O usuário confirma a data financeira ou informa outra data em que a movimentação ocorreu.
5. O usuário confirma a efetivação.
6. O sistema registra o lançamento correspondente.
7. O sistema aplica o efeito financeiro à conta apropriada.
8. O sistema atualiza os saldos e as visões dependentes.

**Fluxos alternativos e exceções**

* Uma Entrada deverá aumentar o saldo.
* Uma Saída deverá diminuir o saldo.
* O usuário poderá informar uma data financeira diferente da data apresentada como padrão.
* A data financeira do lançamento não deverá ser substituída pelo timestamp operacional de criação, atualização ou efetivação; este último é metadado técnico do processamento.
* Em uma ocorrência recorrente, a data cadastrada da ocorrência será a data financeira padrão, respeitando a competência/período da ocorrência e as regras de aplicação da alteração do período.
* A efetivação deverá ser idempotente: repetir a mesma solicitação para um compromisso ou ocorrência já efetivado não poderá criar outro lançamento nem duplicar o efeito sobre o saldo.

**Pós-condições**

O compromisso estará efetivado e a movimentação correspondente estará registrada.

O lançamento deverá manter a data financeira informada (ou a data cadastrada usada como padrão) e o vínculo com o compromisso ou ocorrência de origem. O timestamp operacional do processamento será mantido separadamente para auditoria técnica.

**Requisitos relacionados**

RF-007, RF-010.

**Regras de negócio relacionadas**

Regras dos módulos Compromissos e Lançamentos Financeiros.
