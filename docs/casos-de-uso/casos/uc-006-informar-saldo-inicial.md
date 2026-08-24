## UC-006 — Informar saldo inicial

**Objetivo**

Permitir que o usuário registre o valor financeiro existente na conta principal por meio de um lançamento de Entrada no início da utilização do sistema.

**Ator principal**

Usuário.

**Pré-condições**

O usuário deverá possuir uma conta principal.

**Gatilho**

O usuário solicita o registro do saldo inicial.

**Fluxo principal**

1. O usuário informa o valor financeiro real existente na conta principal.
2. O sistema valida o valor.
3. O sistema registra um lançamento de Entrada na conta principal para representar o valor informado.
4. O sistema calcula os saldos posteriores a partir dos movimentos registrados.

**Fluxos alternativos e exceções**

* O valor informado poderá ser positivo, zero ou negativo, conforme as regras de negócio.
* Este caso de uso representa somente o valor inicial da conta principal. Para uma conta secundária, não deverá ser registrada Entrada direta; o valor deverá ser obtido por transferência com a conta principal, conforme UC-013.
* Caso o usuário não queira reconstruir o saldo inicial, o sistema deverá manter a condição padrão definida para a V1.

**Pós-condições**

A conta principal terá o lançamento de Entrada correspondente, sem armazenar saldo inicial como atributo independente.

**Requisitos relacionados**

RF-010, RF-005.

**Regras de negócio relacionadas**

Regras dos módulos Contas e Lançamentos Financeiros referentes ao saldo inicial.
