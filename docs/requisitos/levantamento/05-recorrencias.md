# MÓDULO 5 – RECORRÊNCIA

**Documento:** Levantamento de Requisitos

**Versão:** 1.0

**Objetivo**

Definir como o sistema tratará compromissos financeiros que se repetem mensalmente, permitindo ao usuário configurar, alterar e encerrar recorrências sem comprometer o histórico financeiro dos períodos anteriores.

**Regras de Negócio**

**RN-001**

Ao cadastrar um compromisso como recorrente, o usuário deverá informar se a recorrência terá início no mês corrente ou no mês seguinte.

**RN-002**

A recorrência poderá ser configurada sem término definido ou com uma quantidade determinada de meses.

**RN-003**

Quando uma recorrência possuir uma quantidade determinada de meses, ela será encerrada automaticamente após o cumprimento da quantidade de meses configurada.

**RN-004**

Quando uma recorrência não possuir término definido, ela permanecerá ativa até que o usuário decida encerrá-la.

**RN-005**

Os compromissos recorrentes terão periodicidade mensal na Versão 1 do sistema.

Não serão contempladas, nesta versão, recorrências semanais, quinzenais, diárias ou com outros intervalos.

**RN-006**

Os compromissos futuros decorrentes de uma recorrência deverão estar disponíveis para o planejamento financeiro dos meses subsequentes.

**RN-007**

Ao alterar o valor de um compromisso recorrente, o sistema deverá perguntar ao usuário se a alteração será aplicada somente ao mês em questão ou ao mês em questão e aos compromissos subsequentes.

**RN-008**

Alterações realizadas em um compromisso recorrente terão como ponto inicial o mês que estiver sendo visualizado pelo usuário.

Compromissos de meses anteriores permanecerão inalterados.

**RN-009**

Um compromisso recorrente que ainda não tenha sido pago poderá ter seu valor alterado normalmente.

**RN-010**

Um compromisso recorrente que já tenha sido pago não poderá ter seu valor alterado diretamente.

Para realizar a alteração, o usuário deverá primeiro desfazer o pagamento.

Ao desfazer o pagamento, a movimentação financeira correspondente será desfeita e o saldo da conta principal será recalculado.

**RN-011**

A alteração do valor de um compromisso recorrente para R$ 0,00 em determinado mês não encerrará sua recorrência.

Os compromissos dos meses subsequentes continuarão existindo normalmente.

**RN-012**

A exclusão de um compromisso recorrente somente no mês em questão não encerrará sua recorrência.

Os compromissos dos meses subsequentes continuarão sendo gerados normalmente.

**RN-013**

Quando o usuário optar por cancelar o compromisso do mês em questão e os compromissos subsequentes, a recorrência será encerrada.

**RN-014**

O encerramento de uma recorrência não modificará os compromissos dos períodos anteriores.

Os registros anteriores permanecerão preservados.

**RN-015**

Uma recorrência encerrada não será reativada.

Caso o usuário deseje estabelecer novamente o mesmo compromisso no futuro, deverá cadastrar um novo compromisso, podendo utilizar o mesmo nome, valor e configuração de recorrência.

**RN-016**

A alteração ou exclusão de um compromisso já pago somente poderá ocorrer após o usuário desfazer o pagamento correspondente.

**RN-017**

O mês em que o usuário estiver trabalhando será considerado o ponto de referência para alterações e cancelamentos dos compromissos recorrentes.

Dessa forma, o usuário poderá navegar para um mês futuro e realizar uma alteração a partir daquele mês, preservando os períodos anteriores.

**Decisões Tomadas**

- A recorrência será exclusivamente mensal na Versão 1.
- A recorrência poderá começar no mês corrente ou no mês seguinte.
- A recorrência poderá ser contínua, sem término definido.
- A recorrência poderá possuir uma quantidade determinada de meses.
- O usuário poderá alterar o valor somente do mês em questão ou daquele mês e dos subsequentes.
- O usuário poderá excluir somente o compromisso do mês em questão sem interromper a recorrência.
- O usuário poderá cancelar o compromisso do mês em questão e os subsequentes, encerrando a recorrência.
- Um compromisso pago não poderá ser alterado ou excluído diretamente.
- Para modificar ou excluir um compromisso pago, será necessário desfazer seu pagamento.
- Alterações realizadas em meses futuros não modificarão os meses anteriores.
- Uma recorrência encerrada será considerada finalizada.
- Para utilizar novamente o mesmo compromisso no futuro, o usuário deverá criar um novo compromisso recorrente.

**Funcionalidades da Versão 1**

- Configuração de compromisso recorrente.
- Definição do início da recorrência no mês corrente ou no mês seguinte.
- Configuração de recorrência sem término.
- Configuração de recorrência por quantidade determinada de meses.
- Geração dos compromissos recorrentes dos meses subsequentes.
- Alteração do valor de um compromisso recorrente.
- Aplicação da alteração somente ao mês em questão ou aos meses subsequentes.
- Exclusão pontual de um compromisso recorrente.
- Cancelamento do compromisso corrente e dos subsequentes.
- Encerramento de recorrências.
- Preservação dos compromissos dos meses anteriores.

**Funcionalidades Previstas para Versões Futuras**

Nenhuma funcionalidade adicional foi definida até o momento.

**Questões para Etapas Posteriores**

A quantidade de meses futuros que permanecerá previamente disponível para planejamento ainda será definida durante o levantamento das funcionalidades relacionadas ao planejamento e à navegação entre os meses.

**Data de Revisão**

13/08/2026
