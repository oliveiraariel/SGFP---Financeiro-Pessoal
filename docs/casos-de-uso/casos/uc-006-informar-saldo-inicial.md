## UC-006 — Informar saldo inicial

**Objetivo**  
Representar a posição financeira inicial por Lançamento na Conta Financeira única.

**Pré-condições**  
Usuário autenticado e Conta Financeira existente.

**Fluxo principal**
1. O usuário informa o valor real inicial.
2. O sistema valida o valor.
3. O sistema registra Lançamento de origem `SALDO_INICIAL`.
4. O saldo passa a ser derivado desse e dos demais lançamentos ativos.

**Alternativas**
- O valor pode ser positivo, zero ou negativo.
- Sem valor inicial, o saldo permanece R$ 0,00 até haver lançamentos.

**Pós-condições**  
Não existe atributo independente de saldo inicial.

**Requisitos relacionados**  
RF-005, RF-010.
