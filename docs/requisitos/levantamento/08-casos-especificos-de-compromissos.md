# **MÓDULO 8 – CASOS ESPECÍFICOS DE COMPROMISSOS FINANCEIROS**

**Documento:** Levantamento de Requisitos

**Versão:** 1.0

## **Objetivo**

Registrar regras específicas para situações que podem ocorrer dentro dos compromissos financeiros, sem criar mecanismos ou entidades independentes para cada situação.

Na Versão 1, Cartão de Crédito e Parcelamentos serão tratados como formas específicas de utilização dos compromissos financeiros e das regras de recorrência já estabelecidas nos módulos anteriores.

## **8.1 Cartão de Crédito**

### **Regras de Negócio**

**RN-001**

O cartão de crédito não será tratado como uma conta secundária.

**RN-002**

O pagamento de uma fatura de cartão de crédito será representado como um compromisso financeiro de Saída que produzirá efeito sobre o saldo da conta principal quando for efetivado.

**RN-003**

O usuário poderá cadastrar um compromisso denominado Cartão de Crédito sem recorrência.

**RN-004**

O usuário poderá cadastrar um compromisso denominado Cartão de Crédito com recorrência.

**RN-005**

Quando o compromisso de Cartão de Crédito for recorrente, o usuário poderá alterar o valor de cada mês conforme o valor real da fatura.

**RN-006**

As alterações de valor do compromisso recorrente de Cartão de Crédito seguirão as mesmas regras definidas para os demais compromissos recorrentes.

**RN-007**

Ao alterar o valor do Cartão de Crédito, o sistema deverá perguntar se a alteração será aplicada somente ao mês em questão ou ao mês em questão e aos meses subsequentes.

**RN-008**

O compromisso de Cartão de Crédito seguirá as mesmas regras de efetivação, desfazimento, alteração e exclusão definidas para os demais compromissos financeiros.

**RN-009**

O usuário será responsável por definir em qual mês deseja registrar o compromisso de pagamento da fatura do cartão de crédito.

**RN-010**

O sistema não exigirá o cadastro da data de fechamento ou da data de vencimento do cartão de crédito na Versão 1.

**RN-011**

Quando a fatura for efetivada, o valor será considerado na atualização do saldo da conta principal.

**RN-012**

O sistema não realizará, na Versão 1, o controle individual das compras realizadas no cartão de crédito, da composição da fatura ou do limite disponível.

**RN-013**

O usuário poderá utilizar uma recorrência para representar o compromisso mensal do cartão de crédito, mesmo que o valor da fatura seja alterado posteriormente em cada mês.

**RN-014**

Quando o usuário alterar o valor de uma fatura recorrente de cartão de crédito, poderá escolher se a alteração será aplicada somente ao mês em questão ou também aos meses subsequentes.

## **8.2 Parcelamentos**

### **Regras de Negócio**

**RN-015**

Um parcelamento não será tratado como uma entidade financeira independente na Versão 1.

**RN-016**

Parcelamentos realizados fora do cartão de crédito serão representados como compromissos financeiros.

**RN-017**

O usuário poderá definir o nome do compromisso de acordo com a natureza da operação.

Exemplos:

- Empréstimo
- Compra parcelada
- Pagamento parcelado a outra pessoa
- Compra parcelada em estabelecimento

**RN-018**

O usuário poderá utilizar a recorrência para representar um compromisso parcelado.

**RN-019**

O usuário poderá definir a quantidade de meses de duração do parcelamento, como 3, 6, 9 ou qualquer outra quantidade desejada.

**RN-020**

O usuário poderá configurar um compromisso parcelado com recorrência sem término definido, caso deseje utilizar essa configuração.

**RN-021**

Os compromissos parcelados seguirão as mesmas regras de recorrência definidas no Módulo 5 – Recorrência.

**RN-022**

As alterações de valor de um compromisso parcelado seguirão as mesmas regras estabelecidas para os demais compromissos recorrentes.

**RN-023**

Quando houver alteração de valor em um compromisso parcelado, o sistema deverá perguntar se a alteração será aplicada somente ao mês em questão ou ao mês em questão e aos meses subsequentes.

**RN-024**

Os compromissos parcelados seguirão as mesmas regras de efetivação, desfazimento, alteração e exclusão definidas para os demais compromissos financeiros.

## **Decisões Tomadas**

- Cartão de Crédito não será uma conta secundária.
- Cartão de Crédito será tratado como um compromisso financeiro de Saída.
- O usuário poderá registrar a fatura no mês que considerar adequado para sua organização financeira.
- O sistema não exigirá informações sobre data de fechamento ou vencimento do cartão na Versão 1.
- O sistema não controlará individualmente as compras realizadas no cartão.
- O sistema não controlará o limite disponível do cartão.
- O sistema não controlará a composição da fatura.
- O compromisso de Cartão de Crédito poderá ser recorrente.
- O valor da recorrência poderá ser alterado conforme o valor real da fatura de cada mês.
- As alterações seguirão as mesmas regras gerais dos compromissos recorrentes.
- Parcelamentos não serão tratados como uma entidade independente.
- Um parcelamento será representado por um compromisso financeiro com recorrência.
- O usuário definirá o nome do compromisso.
- O usuário definirá o valor do compromisso.
- O usuário poderá definir a quantidade de meses da recorrência.
- A recorrência poderá também não possuir término definido.
- As regras de alteração, efetivação, desfazimento e exclusão serão as mesmas já definidas para os demais compromissos financeiros.
- Cartão de Crédito e Parcelamentos são casos específicos de utilização dos compromissos financeiros e não representam, na Versão 1, entidades independentes do domínio.

## **Funcionalidades da Versão 1**

- Cadastro de compromisso de pagamento de Cartão de Crédito.
- Cadastro de compromisso de Cartão de Crédito com ou sem recorrência.
- Alteração do valor da fatura conforme o valor real de cada mês.
- Aplicação da alteração somente ao mês em questão ou aos meses subsequentes.
- Efetivação do pagamento da fatura.
- Desfazimento da efetivação seguindo as regras gerais do sistema.
- Cadastro de compromissos parcelados.
- Definição da quantidade de meses de um parcelamento.
- Utilização de recorrência para representar parcelamentos.
- Possibilidade de recorrência sem término definido.
- Alteração de valores dos compromissos parcelados.
- Aplicação das alterações somente ao mês em questão ou aos meses subsequentes.
- Efetivação dos compromissos parcelados.
- Desfazimento da efetivação.
- Exclusão seguindo as regras gerais dos compromissos financeiros.

## **Funcionalidades Previstas para Versões Futuras**

- Cadastro de data de fechamento da fatura do cartão.
- Cadastro de data de vencimento da fatura.
- Controle de limite de crédito.
- Registro individual das compras realizadas no cartão.
- Controle de compras parceladas realizadas no cartão.
- Composição automática da fatura.
- Controle detalhado de parcelas originadas de compras no cartão.
- Outras funcionalidades específicas de gerenciamento de cartão de crédito ou parcelamentos que venham a ser necessárias em versões futuras.

## **Observação sobre a Modelagem**

Cartão de Crédito e Parcelamentos são apresentados neste módulo para documentar suas regras de negócio e seu comportamento dentro do sistema.

Na etapa de modelagem do sistema, não será assumido que cada módulo ou caso específico deverá necessariamente corresponder a uma entidade independente.

A modelagem deverá ser realizada posteriormente a partir dos conceitos e comportamentos efetivamente identificados no domínio do SGFP.

## **Questões para Etapas Posteriores**

Nenhuma.

## **Data de Revisão**

**13/08/2026**
