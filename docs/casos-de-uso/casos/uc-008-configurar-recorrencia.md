## UC-008 — Configurar recorrência

**Objetivo**

Permitir que o usuário configure uma operação como recorrente.

**Ator principal**

Usuário.

**Pré-condições**

O usuário deverá estar cadastrando ou administrando uma operação que permita recorrência.

**Gatilho**

O usuário opta por utilizar recorrência.

**Fluxo principal**

1. O usuário escolhe utilizar recorrência.
2. O sistema solicita o início da recorrência.
3. O usuário define mês corrente ou mês seguinte.
4. O usuário define recorrência sem término ou quantidade determinada de meses.
5. O sistema valida a configuração.
6. O sistema registra a recorrência.

**Fluxos alternativos e exceções**

* A periodicidade da V1 será exclusivamente mensal.
* Uma recorrência sem término permanecerá ativa até seu encerramento.
* Uma recorrência com duração determinada será encerrada após o cumprimento da quantidade definida.

**Pós-condições**

A operação estará configurada segundo a recorrência definida.

**Requisitos relacionados**

RF-008, RF-014, RF-016.

**Regras de negócio relacionadas**

Regras do módulo Recorrências.
