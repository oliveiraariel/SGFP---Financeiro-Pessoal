# MÓDULO 4 – CATEGORIAS

**Documento:** Levantamento de Requisitos

**Versão:** 1.0

**Objetivo**

Definir como os compromissos financeiros poderão ser organizados em grupos, facilitando a visualização, o acompanhamento e a organização financeira do usuário.

**Regras de Negócio**

**RN-001**

O sistema disponibilizará um conjunto inicial de categorias para facilitar a utilização da aplicação.

Essas categorias serão apenas sugestões e poderão ser livremente alteradas pelo usuário.

**RN-002**

A associação entre um compromisso financeiro e uma categoria será obrigatória no momento do cadastro.

**RN-003**

Uma categoria poderá ser removida pelo usuário a qualquer momento.

Ao remover uma categoria, os compromissos financeiros associados a ela não serão excluídos.

Eles apenas deixarão de pertencer a qualquer categoria.

**RN-004**

Uma categoria poderá ser renomeada a qualquer momento.

A alteração do nome da categoria não modificará os compromissos vinculados a ela, apenas a forma como serão agrupados e apresentados ao usuário.

**RN-005**

As categorias terão apenas a função de organizar compromissos financeiros.

Elas não representarão movimentações financeiras e não influenciarão diretamente os cálculos de saldo ou patrimônio.

**RN-006**

Quando um compromisso estiver associado a uma categoria, ele poderá ser apresentado agrupado na tela principal.

Ao expandir a categoria, o usuário poderá visualizar todos os compromissos que a compõem.

Compromissos que não estiverem associados a nenhuma categoria poderão ser exibidos individualmente.

**RN-007**

Ao cadastrar um compromisso financeiro, o sistema deverá apresentar as categorias disponíveis para seleção, sendo obrigatória a escolha de uma categoria para concluir o cadastro.

**RN-008**

Caso nenhuma das categorias disponíveis seja adequada ao compromisso que está sendo cadastrado, o usuário poderá criar uma nova categoria diretamente durante o cadastro do compromisso, por meio de uma opção de adição.

**RN-009**

A categoria criada durante o cadastro do compromisso deverá ficar disponível para seleção e poderá ser associada ao compromisso que originou sua criação.

**Decisões Tomadas**

- O sistema disponibilizará categorias iniciais apenas como sugestão.
- O usuário poderá criar novas categorias.
- O usuário poderá renomear categorias existentes.
- O usuário poderá remover categorias.
- A utilização de categorias será obrigatória para os compromissos financeiros.
- A exclusão de uma categoria nunca removerá os compromissos financeiros vinculados.
- O agrupamento de compromissos servirá apenas para melhorar a organização e a visualização das informações.

**Funcionalidades da Versão 1**

- Cadastro de categorias.
- Edição de categorias.
- Exclusão de categorias.
- Associação de compromissos às categorias.
- Criação de uma nova categoria durante o cadastro de um compromisso.
- Agrupamento dos compromissos na tela principal.
- Visualização expandida dos compromissos pertencentes a uma categoria.

**Funcionalidades Previstas para Versões Futuras**

- Ícones personalizados para categorias.
- Cores personalizadas para categorias.
- Ordenação manual das categorias.
- Estatísticas financeiras por categoria.

**Data de Revisão**

13/08/2026
