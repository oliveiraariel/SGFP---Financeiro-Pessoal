## UC-015 — Gerenciar parcelamentos

**Objetivo**

Permitir que o usuário represente uma obrigação parcelada utilizando compromissos recorrentes.

**Ator principal**

Usuário.

**Pré-condições**

O usuário deverá estar autenticado.

**Gatilho**

O usuário deseja registrar uma operação parcelada.

**Fluxo principal**

1. O usuário cria um compromisso financeiro.
2. O usuário define nome e valor.
3. O usuário configura recorrência.
4. O usuário define a quantidade de meses desejada.
5. O sistema registra a recorrência.
6. Os compromissos correspondentes ficam disponíveis nos períodos aplicáveis.

**Fluxos alternativos e exceções**

* O usuário poderá configurar recorrência sem término definido, conforme as regras existentes.
* Alterações, efetivações e exclusões seguirão as regras gerais dos compromissos e recorrências.

**Pós-condições**

O parcelamento estará representado pelo compromisso recorrente.

**Requisitos relacionados**

RF-016, RF-008.

**Regras de negócio relacionadas**

Regras dos módulos Casos Específicos de Compromissos Financeiros e Recorrências.
