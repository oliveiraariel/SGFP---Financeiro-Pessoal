# MÓDULO 4 – CATEGORIAS

**Documento:** Levantamento de Requisitos

**Versão:** 1.2

**Objetivo**

Definir como os compromissos financeiros poderão ser organizados em grupos, facilitando a visualização, o acompanhamento e a organização financeira do usuário.

**Regras de Negócio**

**RN-001**

No cadastro de cada usuário, o sistema disponibilizará um conjunto inicial de categorias para facilitar a utilização da aplicação.

Essas categorias serão criadas como registros próprios já vinculados ao usuário correspondente.

As categorias iniciais serão apenas sugestões e, após sua criação, poderão ser livremente renomeadas ou removidas pelo próprio usuário, assim como novas categorias poderão ser adicionadas.

Alterações realizadas por um usuário em suas categorias não deverão afetar as categorias de outros usuários.

**RN-002**

A associação entre um compromisso financeiro e uma categoria será opcional. O compromisso poderá ser cadastrado sem categoria.

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

Ao cadastrar um compromisso financeiro, o sistema deverá apresentar as categorias disponíveis para seleção, mas a escolha de uma categoria será opcional e sua ausência não impedirá a conclusão do cadastro.

**RN-008**

Caso nenhuma das categorias disponíveis seja adequada ao compromisso que está sendo cadastrado, o usuário poderá criar uma nova categoria diretamente durante o cadastro do compromisso, por meio de uma opção de adição.

**RN-009**

A categoria criada durante o cadastro do compromisso deverá ficar disponível para seleção e poderá ser associada ao compromisso que originou sua criação.

**Decisões Tomadas**

- O sistema disponibilizará categorias iniciais apenas como sugestão.
- O conjunto inicial será criado individualmente para cada novo usuário e ficará vinculado a ele desde sua criação.
- Não haverá um conjunto global compartilhado de categorias cuja alteração por um usuário afete os demais.
- O usuário poderá criar novas categorias.
- O usuário poderá renomear categorias existentes.
- O usuário poderá remover categorias.
- A utilização de categorias será opcional para os compromissos financeiros.
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

04/09/2026
