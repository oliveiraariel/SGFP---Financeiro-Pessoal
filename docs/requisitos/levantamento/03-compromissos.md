# Módulo 3 — Compromissos Financeiros

## Objetivo

Definir como o sistema representará os compromissos financeiros do usuário, estabelecendo as regras para seu cadastro, manutenção, edição, exclusão e relacionamento com as futuras movimentações financeiras.

## Regras de Negócio

### RN-001

Um compromisso financeiro representa uma obrigação financeira ou uma previsão de entrada de recursos.

Os compromissos financeiros existirão independentemente de já terem sido pagos ou recebidos.

### RN-002

Todo compromisso financeiro deverá possuir um nome que o identifique.

Exemplos:

- Salário
- Internet
- Energia Elétrica
- Mercado
- Combustível
- Cartão de Crédito
- Academia

### RN-003

Todo compromisso financeiro deverá possuir um valor monetário.

### RN-004

Todo compromisso financeiro deverá possuir uma natureza financeira:

- Entrada;
- Saída.

Nos compromissos financeiros padrão, a natureza será definida pelo usuário no momento do cadastro.

Nas transferências, a natureza será determinada pelo sentido da operação em relação à Conta Principal:

- Conta Principal → Conta Secundária: Saída;
- Conta Secundária → Conta Principal: Entrada.

### RN-005

Haverá uma única conta definida como principal pelo usuário.

Todos os compromissos financeiros incidirão sobre a conta principal.

As contas secundárias não receberão compromissos financeiros diretamente.

As contas secundárias somente receberão movimentações de entrada e saída provenientes da conta principal.

### RN-006

Enquanto um compromisso financeiro não estiver pago, ele poderá ser alterado livremente.

Ao alterar um compromisso recorrente, o sistema deverá perguntar se a alteração será aplicada:

- Apenas ao mês atual.
- Ao mês atual e aos meses seguintes.

### RN-007

Compromissos financeiros já pagos não poderão ser alterados diretamente.

Para realizar qualquer alteração, o usuário deverá primeiro desfazer o pagamento.

Ao desfazer o pagamento, a movimentação financeira correspondente será desfeita e o saldo da conta principal será recalculado.

Após isso, o compromisso voltará ao estado pendente e poderá ser editado normalmente.

### RN-008

Enquanto um compromisso financeiro estiver pendente, ele poderá ser removido.

Ao remover um compromisso recorrente, o sistema deverá perguntar se a remoção será aplicada:

- Apenas ao mês atual.
- Ao mês atual e aos meses seguintes.

### RN-009

Caso um compromisso financeiro já esteja pago, sua exclusão somente será permitida após o usuário desfazer o pagamento.

### RN-010

Caso um compromisso financeiro possua valor igual a R$ 0,00, ele continuará existindo no sistema, porém não produzirá impacto financeiro naquele mês.

Essa funcionalidade permitirá representar situações em que determinado compromisso permaneça ativo, mas não gere cobrança em um período específico.

## Decisões Tomadas

- Todo compromisso financeiro possuirá obrigatoriamente um nome.
- Todo compromisso financeiro possuirá obrigatoriamente um valor.
- Todo compromisso financeiro será classificado como Entrada ou Saída.
- Nos compromissos padrão, a natureza será definida pelo usuário.
- Nas transferências, a natureza será determinada pelo sentido da operação em relação à Conta Principal: Principal → Secundária = Saída; Secundária → Principal = Entrada.
- Haverá uma única conta principal.
- Todos os compromissos financeiros incidirão sobre a conta principal.
- Contas secundárias não receberão compromissos financeiros diretamente.
- Contas secundárias somente receberão movimentações de entrada e saída provenientes da conta principal.
- Compromissos pendentes poderão ser alterados ou removidos.
- Compromissos pagos ficarão protegidos contra alterações e exclusões.
- Para alterar ou remover um compromisso pago será necessário desfazer previamente o pagamento.
- Alterações e exclusões de compromissos recorrentes poderão ser aplicadas apenas ao mês atual ou também aos meses seguintes.
- O sistema permitirá compromissos com valor igual a R$ 0,00 quando isso representar corretamente a situação financeira do usuário.

## Funcionalidades da Versão 1

- Cadastro de compromissos financeiros.
- Edição de compromissos.
- Exclusão de compromissos.
- Classificação entre Entrada e Saída.
- Alteração apenas do mês atual ou dos meses seguintes para compromissos recorrentes.
- Exclusão apenas do mês atual ou dos meses seguintes para compromissos recorrentes.
- Proteção contra alteração de compromissos já pagos.
- Possibilidade de desfazer um pagamento para permitir alterações.
- Recalcular automaticamente o saldo da conta principal após desfazer um pagamento.

## Funcionalidades Previstas para Versões Futuras

Nenhuma funcionalidade adicional foi definida até o momento.

**Data de Revisão**

13 / 08 / 2026
