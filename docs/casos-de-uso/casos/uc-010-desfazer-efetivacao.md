## UC-010 — Desfazer efetivação

**Objetivo**

Permitir que o usuário reverta a efetivação de um compromisso.

**Ator principal**

Usuário.

**Pré-condições**

O compromisso deverá estar efetivado.

**Gatilho**

O usuário solicita o desfazimento da efetivação.

**Fluxo principal**

1. O usuário seleciona o compromisso efetivado.
2. O sistema solicita confirmação do desfazimento.
3. O usuário confirma.
4. O sistema desfaz o efeito da movimentação.
5. O sistema atualiza o saldo.
6. O compromisso retorna ao estado em que poderá ser alterado ou excluído conforme as regras aplicáveis.

**Fluxos alternativos e exceções**

* O desfazimento de uma Entrada deverá retirar do saldo o valor anteriormente acrescentado.
* O desfazimento de uma Saída deverá retirar do saldo o valor anteriormente diminuído.
* O sistema deverá manter consistência entre o compromisso, o lançamento e o saldo.
* O desfazimento deverá localizar o lançamento pelo vínculo com o compromisso ou ocorrência efetivada, e não pela data financeira ou pelo timestamp operacional isoladamente.
* O desfazimento deverá ser idempotente: repetir a operação depois de o efeito já ter sido removido não poderá gerar novo ajuste nem alterar novamente o saldo.
* A data financeira originalmente registrada deverá permanecer associada ao histórico do lançamento desfeito; o timestamp operacional do desfazimento será um metadado técnico distinto.

**Pós-condições**

A efetivação deixará de produzir efeito financeiro.

O compromisso ou ocorrência ficará novamente disponível para alteração conforme as regras aplicáveis, sem perder a data financeira originalmente registrada nem o vínculo histórico com o lançamento desfeito.

**Requisitos relacionados**

RF-007, RF-010.

**Regras de negócio relacionadas**

Regras dos módulos Compromissos e Lançamentos Financeiros.
