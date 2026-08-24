# **SUBETAPA – DASHBOARD**

**Documento:** Levantamento de Requisitos

**Versão:** 1.0

**Objetivo**

Definir a visão inicial do sistema, apresentando ao usuário um resumo da situação financeira do mês e o detalhamento dos compromissos que compõem os valores apresentados.

**Regras de Negócio**

**RN-001**

Ao acessar o sistema, o Dashboard deverá apresentar inicialmente o mês e ano correntes.

**RN-002**

O Dashboard deverá apresentar o saldo inicial do mês selecionado.

O saldo inicial corresponderá ao saldo final do mês anterior.

**RN-003**

O Dashboard deverá apresentar o total de entradas previstas para o mês selecionado.

**RN-004**

O Dashboard deverá apresentar o total de saídas previstas para o mês selecionado.

**RN-005**

O Dashboard deverá apresentar o saldo final previsto do mês.

O saldo final previsto será calculado considerando o saldo inicial, as entradas previstas e as saídas previstas.

**RN-006**

O saldo final previsto deverá ser calculado da seguinte forma:

**Saldo final previsto = Saldo inicial + Entradas previstas − Saídas previstas**

**RN-007**

Os valores apresentados no resumo deverão ser compostos pelos compromissos registrados para o mês selecionado.

**RN-008**

O Dashboard deverá apresentar o detalhamento dos compromissos que compõem os totais de entradas e saídas.

**RN-009**

As alterações realizadas nos compromissos deverão refletir nos valores apresentados pelo Dashboard.

**RN-010**

A inclusão de um novo compromisso deverá atualizar os valores apresentados no Dashboard.

**RN-011**

A exclusão de um compromisso deverá atualizar os valores apresentados no Dashboard.

**RN-012**

A efetivação ou o desfazimento da efetivação de um compromisso deverá refletir nos valores apresentados pelo Dashboard de acordo com o estado atualizado do compromisso.

**RN-013**

Alterações realizadas em compromissos recorrentes deverão refletir no Dashboard do mês correspondente, seguindo as regras de aplicação definidas para a recorrência.

**RN-014**

O usuário poderá navegar entre diferentes meses para consultar a situação financeira correspondente ao período selecionado.

**RN-015**

O Dashboard não permitirá que seus valores financeiros sejam alterados diretamente.

As alterações deverão ser realizadas nos compromissos, contas ou demais registros correspondentes, e o Dashboard deverá refletir automaticamente as alterações.

**RN-016**

O Dashboard deverá apresentar os compromissos de Entrada e Saída de forma individualizada, permitindo identificar quais registros compõem os valores apresentados no resumo.

**RN-017**

O Dashboard deverá permitir que o usuário acesse o detalhamento de um compromisso para realizar alterações conforme as regras definidas para o respectivo compromisso.

**RN-018**

Na versão inicial, o Dashboard não deverá exigir a utilização de gráficos para apresentar a situação financeira do usuário.

**RN-019**

Funcionalidades de análise gráfica por categoria e período poderão ser incorporadas em versões futuras.

**Decisões Tomadas**

- O Dashboard será iniciado no mês corrente.
- O Dashboard apresentará mês e ano.
- O saldo inicial será o saldo que encerrou o mês anterior.
- Serão apresentados os totais de entradas e saídas previstas.
- Será apresentado o saldo final previsto.
- O saldo final previsto considerará as movimentações previstas para o mês.
- Os valores serão detalhados individualmente pelos compromissos que os compõem.
- O Dashboard será atualizado conforme os dados do sistema forem modificados.
- O usuário poderá navegar entre os meses.
- O Dashboard será uma visão dos dados existentes no sistema e não possuirá dados financeiros independentes.
- O Dashboard não será tratado como uma entidade do domínio.
- No computador e tablet, o resumo e o detalhamento poderão ser apresentados lado a lado.
- No celular, o detalhamento poderá ser apresentado abaixo do resumo.
- Gráficos e análises históricas mais avançadas ficarão para versões futuras.

**Funcionalidades da Versão 1**

- Visualização do mês e ano correntes.
- Visualização do saldo inicial.
- Visualização do total de entradas previstas.
- Visualização do total de saídas previstas.
- Visualização do saldo final previsto.
- Detalhamento dos compromissos de entrada.
- Detalhamento dos compromissos de saída.
- Atualização automática dos valores conforme alterações nos dados.
- Navegação entre meses.
- Acesso aos compromissos a partir do detalhamento.

**Funcionalidades Previstas para Versões Futuras**

- Gráficos de evolução financeira.
- Seleção de categorias para análise.
- Seleção de períodos de 3, 6, 12, 24 meses ou outros períodos definidos posteriormente.
- Comparação da evolução dos gastos de uma determinada categoria ao longo dos meses.
- Outras ferramentas de análise financeira.

### **Observação sobre a RN-012**

O Dashboard deve mostrar a situação atual do mês, e não simplesmente somar tudo que foi originalmente cadastrado.

Isso é especialmente importante no seu exemplo: se uma saída prevista de R$ 500 for cancelada, alterada ou efetivada, **o resumo precisa acompanhar a nova realidade**.

**Data de Revisão**

**13/08/2026**
