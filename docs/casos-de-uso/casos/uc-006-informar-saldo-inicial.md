## UC-006 — Informar saldo inicial

**Objetivo**

Permitir que o usuário registre o saldo real existente na conta principal no início da utilização do sistema.

**Ator principal**

Usuário.

**Pré-condições**

O usuário deverá possuir uma conta principal.

**Gatilho**

O usuário solicita o registro do saldo inicial.

**Fluxo principal**

1. O usuário informa o saldo real existente.
2. O sistema valida o valor.
3. O sistema registra o valor conforme o mecanismo definido para inicialização financeira.
4. O sistema passa a utilizar o valor como ponto de partida para os cálculos posteriores.

**Fluxos alternativos e exceções**

* O saldo informado poderá ser positivo, zero ou negativo, conforme as regras de negócio.
* Caso o usuário não queira reconstruir o saldo inicial, o sistema deverá manter a condição padrão definida para a V1.

**Pós-condições**

A conta principal possuirá uma referência inicial para os cálculos financeiros posteriores.

**Requisitos relacionados**

RF-006.

**Regras de negócio relacionadas**

Regras dos módulos Contas e Lançamentos Financeiros referentes ao saldo inicial.
