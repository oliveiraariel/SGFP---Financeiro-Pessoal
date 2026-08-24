## UC-014 — Gerenciar cartão de crédito

**Objetivo**

Permitir que o usuário registre o pagamento de faturas de cartão de crédito utilizando compromissos financeiros.

**Ator principal**

Usuário.

**Pré-condições**

O usuário deverá estar autenticado.

**Gatilho**

O usuário deseja registrar uma fatura de cartão de crédito.

**Fluxo principal**

1. O usuário cria um compromisso de Saída destinado ao pagamento do cartão.
2. O usuário define o mês correspondente.
3. O usuário poderá configurar recorrência.
4. O sistema registra o compromisso.
5. O usuário poderá alterar o valor de cada mês conforme o valor real da fatura.
6. O usuário poderá efetivar o compromisso quando realizar o pagamento.

**Fluxos alternativos e exceções**

* O sistema não controlará compras individuais do cartão na V1.
* O sistema não calculará automaticamente a composição da fatura.
* O sistema não controlará limite de crédito.
* O sistema não exigirá datas de fechamento ou vencimento na V1.

**Pós-condições**

A fatura estará representada como compromisso financeiro.

**Requisitos relacionados**

RF-015.

**Regras de negócio relacionadas**

Regras do módulo Casos Específicos de Compromissos Financeiros.
