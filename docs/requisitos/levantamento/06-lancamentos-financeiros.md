# MÓDULO 6 – LANÇAMENTOS FINANCEIROS

**Documento:** Levantamento de Requisitos

**Versão:** 1.0

**Objetivo**

Definir como o sistema registrará as movimentações financeiras efetivamente realizadas, estabelecendo as regras para entradas, saídas, efetivação, alteração, exclusão, datas e impacto sobre o saldo das contas.

**Regras de Negócio**

**RN-001**

Um lançamento financeiro representa uma movimentação financeira efetivamente realizada.

**RN-002**

Um compromisso financeiro não será considerado efetivado enquanto o usuário não confirmar que a movimentação ocorreu.

**RN-003**

A efetivação de uma Saída deverá diminuir o saldo da conta principal.

**RN-004**

A efetivação de uma Entrada deverá aumentar o saldo da conta principal.

**RN-005**

Um lançamento financeiro deverá possuir um nome que identifique a movimentação.

**RN-006**

Um lançamento financeiro deverá possuir um valor monetário.

**RN-007**

Um lançamento financeiro deverá possuir uma data de efetivação.

**RN-008**

O lançamento financeiro poderá possuir uma descrição complementar, sendo esse campo opcional.

**RN-009**

Ao efetivar um compromisso financeiro, o sistema deverá permitir que o usuário informe a data em que a movimentação financeira realmente ocorreu.

**RN-010**

A data atual será apresentada como padrão no momento da efetivação.

O usuário poderá confirmar a data apresentada ou informar uma data diferente.

**RN-011**

Após a efetivação de um compromisso financeiro, a movimentação correspondente ficará protegida contra alterações diretas.

**RN-012**

Para modificar um compromisso que já tenha sido efetivado, o usuário deverá primeiro desfazer sua efetivação.

**RN-013**

Ao desfazer uma efetivação, o valor correspondente deixará de produzir efeito sobre o saldo da conta principal.

**RN-014**

Após o desfazimento da efetivação, o compromisso voltará a ficar disponível para alteração.

**RN-015**

Quando o usuário alterar o valor de um compromisso recorrente após desfazer sua efetivação, deverá informar se a alteração será aplicada somente ao mês em questão ou também aos compromissos subsequentes.

**RN-016**

Após realizar a alteração, o usuário poderá efetivar novamente o compromisso, fazendo com que o novo valor produza efeito sobre o saldo da conta principal.

**RN-017**

Compromissos classificados como Entrada e Saída seguirão as mesmas regras de efetivação, alteração, desfazimento e recorrência.

**RN-018**

A efetivação de um compromisso classificado como Entrada aumentará o saldo da conta principal.

**RN-019**

A efetivação de um compromisso classificado como Saída diminuirá o saldo da conta principal.

**RN-020**

Compromissos classificados como Entrada poderão possuir recorrência sem término definido ou com quantidade determinada de meses.

**RN-021**

O usuário poderá desfazer a efetivação de uma Entrada ou Saída seguindo o mesmo procedimento.

No caso de uma Entrada, desfazer a efetivação fará com que o valor correspondente deixe de compor o saldo da conta principal.

**RN-022**

Todo lançamento financeiro originado pela efetivação de um compromisso deverá manter vínculo com o compromisso que lhe deu origem.

Esse vínculo permitirá ao sistema identificar qual compromisso foi efetivado e realizar o processo de desfazimento da efetivação.

**RN-023**

A conta principal será criada com saldo padrão de R$ 0,00.

**RN-024**

O usuário poderá informar o saldo real existente em sua conta principal no momento em que iniciar a utilização do sistema, caso esse saldo seja diferente de R$ 0,00.

O saldo informado será registrado pelo sistema por meio de um lançamento de entrada, mantendo o saldo da conta baseado nos lançamentos registrados.

**RN-025**

O lançamento de Entrada que representa o valor inicial da conta principal será utilizado como ponto de partida para os cálculos das movimentações financeiras posteriores.

**RN-026**

O saldo da conta principal poderá assumir valores positivos, zero ou negativos.

**RN-027**

As entradas e saídas efetivadas serão aplicadas ao saldo a partir do lançamento de Entrada que representa o valor inicial da conta principal, quando este existir.

**RN-028**

O usuário poderá criar um compromisso financeiro no mês corrente mesmo que a movimentação não tenha sido previamente planejada.

**RN-029**

Um compromisso criado no mês corrente seguirá as mesmas regras de efetivação dos demais compromissos.

**RN-030**

Uma movimentação financeira somente afetará o saldo após a efetivação do compromisso correspondente.

**RN-031**

Um compromisso financeiro efetivado não poderá ser excluído diretamente.

**RN-032**

Para excluir um compromisso efetivado no mês corrente, o usuário deverá primeiro desfazer sua efetivação.

**RN-033**

Ao desfazer a efetivação, o valor correspondente deixará de produzir efeito sobre o saldo da conta principal, retornando o saldo ao estado anterior à efetivação.

**RN-034**

Após o desfazimento da efetivação, o compromisso poderá ser excluído.

**RN-035**

Quando o compromisso excluído for recorrente, o sistema deverá perguntar se a exclusão será aplicada somente ao mês em questão ou ao mês em questão e aos meses subsequentes.

**RN-036**

A exclusão de um compromisso em determinado mês não modificará os compromissos dos meses anteriores.

**RN-037**

O sistema deverá permitir que o usuário registre compromissos financeiros em meses anteriores ao início da utilização da aplicação.

**RN-038**

O usuário poderá navegar para meses anteriores e posteriores ao mês corrente para consultar e cadastrar informações financeiras.

**RN-039**

O usuário será responsável por determinar a partir de qual período deseja iniciar o registro de seu histórico financeiro.

**RN-040**

O sistema não exigirá que o usuário cadastre seu histórico financeiro anterior ao início da utilização da aplicação.

O preenchimento de períodos anteriores será opcional e dependerá do interesse do usuário em reconstruir seu histórico financeiro.

**Decisões Tomadas**

- O lançamento financeiro representa uma movimentação efetivamente realizada.
- O compromisso financeiro somente produzirá efeito sobre o saldo após sua efetivação.
- Entradas aumentam o saldo.
- Saídas diminuem o saldo.
- Entradas e saídas possuem o mesmo comportamento de efetivação.
- O lançamento possuirá nome, valor e data de efetivação.
- A descrição será opcional.
- A data atual será apresentada como padrão no momento da efetivação.
- O usuário poderá informar uma data diferente da data atual.
- Um compromisso efetivado ficará protegido contra alterações diretas.
- Para corrigir um compromisso efetivado, será necessário desfazer sua efetivação.
- Ao desfazer a efetivação, o efeito financeiro será retirado do saldo.
- Depois de desfazer a efetivação, o compromisso poderá ser alterado ou excluído.
- Um compromisso efetivado não poderá ser excluído diretamente.
- Compromissos recorrentes seguirão as regras definidas no Módulo 5 – Recorrência.
- O lançamento financeiro manterá vínculo com o compromisso que lhe deu origem.
- O sistema começará com saldo padrão de R$ 0,00.
- O usuário poderá informar seu saldo real no início da utilização.
- O saldo real inicial informado pelo usuário será registrado por meio de um lançamento de entrada.
- O saldo poderá ser positivo, zero ou negativo.
- O usuário poderá criar compromissos no mês corrente mesmo que não tenham sido previamente planejados.
- O usuário poderá navegar para meses anteriores e registrar seu histórico financeiro caso deseje.
- O preenchimento do histórico anterior será opcional.

**Funcionalidades da Versão 1**

- Registro de compromissos que posteriormente serão efetivados.
- Efetivação de compromissos de Entrada e Saída.
- Registro da data de efetivação.
- Utilização da data atual como padrão para a efetivação.
- Alteração manual da data de efetivação.
- Atualização automática do saldo após a efetivação.
- Desfazimento da efetivação.
- Retorno do saldo ao estado anterior após o desfazimento da efetivação.
- Proteção de compromissos já efetivados.
- Alteração de compromissos após o desfazimento da efetivação.
- Exclusão de compromissos após o desfazimento da efetivação.
- Cadastro de compromissos não planejados previamente.
- Navegação para meses anteriores e posteriores.
- Registro opcional do histórico financeiro anterior ao início da utilização do sistema.
- Registro do saldo real inicial por meio de um lançamento de entrada.

**Funcionalidades Previstas para Versões Futuras**

Nenhuma funcionalidade adicional foi definida até o momento.

**Data de Revisão**

13/08/2026
