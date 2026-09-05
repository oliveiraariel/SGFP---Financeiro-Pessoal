# **MÓDULO 7 – TRANSFERÊNCIAS**

**Documento:** Levantamento de Requisitos

**Versão:** 1.0

## **Objetivo**

Definir como o sistema realizará transferências de valores entre a conta principal e as contas secundárias pertencentes ao usuário, utilizando o mesmo mecanismo de compromisso e efetivação adotado nas demais movimentações financeiras.

## **Regras de Negócio**

**RN-001**

Uma transferência entre contas será tratada por meio do mesmo mecanismo de compromisso e efetivação utilizado nas demais movimentações financeiras.

**RN-002**

Uma transferência deverá possuir uma conta de origem, uma conta de destino e um valor.

**RN-003**

Enquanto uma transferência não estiver efetivada, ela não produzirá alteração nos saldos das contas envolvidas.

**RN-004**

Ao efetivar uma transferência, o valor será subtraído da conta de origem e adicionado à conta de destino.

**RN-005**

O desfazimento da efetivação de uma transferência deverá retornar os saldos das contas de origem e destino ao estado anterior à efetivação.

**RN-006**

Uma transferência entre contas pertencentes ao mesmo usuário não alterará seu patrimônio total, pois representará apenas a movimentação de recursos entre suas próprias contas.

**RN-007**

A conta de origem e a conta de destino deverão ser diferentes.

**RN-008**

A conta principal será a única conta que poderá realizar transferências com as contas secundárias.

**RN-009**

Será permitido realizar transferências da conta principal para uma conta secundária.

**RN-010**

Será permitido realizar transferências de uma conta secundária para a conta principal.

**RN-011**

Não será permitido realizar transferências diretamente entre duas contas secundárias.

**RN-012**

Uma conta poderá apresentar saldo negativo após a efetivação de uma transferência.

O sistema não bloqueará a transferência em razão de insuficiência de saldo.

**RN-013**

Uma transferência poderá ser configurada como recorrente e seguirá as mesmas regras de recorrência definidas para os demais compromissos financeiros.

**RN-014**

Uma transferência efetivada não poderá ser alterada diretamente.

Para alterá-la, o usuário deverá primeiro desfazer sua efetivação.

**RN-015**

Após o desfazimento da efetivação, a transferência poderá ser alterada seguindo as mesmas regras aplicáveis aos demais compromissos financeiros.

**RN-016**

Quando uma transferência recorrente tiver seu valor alterado, o usuário deverá escolher se a alteração será aplicada somente ao mês em questão ou ao mês em questão e aos meses subsequentes.

**RN-017**

Quando uma transferência efetivada tiver sua efetivação desfeita, os saldos das contas de origem e destino deverão retornar aos valores existentes antes da efetivação.

**RN-018**

Quando uma transferência recorrente for excluída, o sistema deverá perguntar se a exclusão será aplicada somente ao mês em questão ou ao mês em questão e aos meses subsequentes.

**RN-019**

Uma transferência efetivada não poderá ser excluída diretamente.

Para excluir uma transferência efetivada, o usuário deverá primeiro desfazer sua efetivação.

**RN-020**

Uma transferência poderá possuir recorrência sem término definido ou com quantidade determinada de meses.

**RN-021**

A periodicidade das transferências recorrentes será mensal na Versão 1 do sistema.

**RN-022**

Quando uma transferência for efetivada, o registro da operação deverá estar presente no histórico da conta de origem e no histórico da conta de destino.

**RN-023**

O registro da transferência deverá utilizar o mesmo nome da operação nas duas contas.

Na conta de origem, o valor será apresentado como saída.

Na conta de destino, o valor será apresentado como entrada.

A natureza financeira do compromisso de transferência será determinada pelo sentido da operação em relação à Conta Principal:

- Conta Principal → Conta Secundária: Saída;
- Conta Secundária → Conta Principal: Entrada.

Essa natureza decorre do fluxo da transferência e não será escolhida de forma independente pelo usuário.

**RN-024**

A alteração, efetivação ou desfazimento de uma transferência deverá manter os registros das contas de origem e destino consistentes entre si.

## **Decisões Tomadas**

- A transferência será tratada como um compromisso financeiro.
- A transferência utilizará o mecanismo de efetivação utilizado nas demais movimentações.
- Uma transferência não efetivada não altera os saldos.
- Uma transferência efetivada diminui o saldo da conta de origem e aumenta o saldo da conta de destino.
- Desfazer a efetivação desfaz os dois efeitos financeiros.
- A transferência não altera o patrimônio total do usuário.
- A origem e o destino deverão ser contas diferentes.
- Existirá apenas uma conta principal.
- A conta principal poderá transferir valores para contas secundárias.
- As contas secundárias poderão transferir valores para a conta principal.
- Não serão permitidas transferências diretamente entre contas secundárias.
- Uma conta poderá ficar negativa após uma transferência.
- Transferências poderão ser recorrentes.
- A recorrência seguirá as mesmas regras definidas no Módulo 5 – Recorrência.
- A transferência poderá ser recorrente sem término ou possuir quantidade determinada de meses.
- A periodicidade será mensal.
- Uma transferência efetivada ficará protegida contra alteração direta.
- Para alterar uma transferência efetivada, será necessário desfazer sua efetivação.
- Uma transferência efetivada também não poderá ser excluída diretamente.
- Para excluir uma transferência efetivada, será necessário desfazer sua efetivação.
- Alterações e exclusões de transferências recorrentes seguirão a regra de aplicação somente ao mês em questão ou ao mês em questão e aos subsequentes.
- A transferência aparecerá nos históricos das duas contas.
- O mesmo nome será utilizado nos dois registros.
- Na conta de origem, o valor será apresentado como saída.
- Na conta de destino, o valor será apresentado como entrada.
- A natureza do compromisso de transferência será determinada em relação à Conta Principal: Principal → Secundária = Saída; Secundária → Principal = Entrada.

## **Funcionalidades da Versão 1**

- Criação de transferência entre a conta principal e uma conta secundária.
- Definição da conta de origem.
- Definição da conta de destino.
- Definição do valor da transferência.
- Efetivação da transferência.
- Atualização simultânea dos saldos das contas envolvidas.
- Desfazimento da efetivação.
- Transferências recorrentes.
- Recorrência sem término definido.
- Recorrência com quantidade determinada de meses.
- Alteração de transferência antes da efetivação.
- Alteração de transferência após desfazer a efetivação.
- Aplicação de alterações somente ao mês em questão ou aos meses subsequentes.
- Exclusão de transferência não efetivada.
- Exclusão de transferência após desfazer a efetivação.
- Aplicação da exclusão somente ao mês em questão ou aos meses subsequentes.
- Registro da transferência no histórico da conta de origem.
- Registro da transferência no histórico da conta de destino.

## **Funcionalidades Previstas para Versões Futuras**

Nenhuma funcionalidade adicional foi definida até o momento.

## **Questões para Etapas Posteriores**

Nenhuma.

## **Data de Revisão**

**13/08/2026**
