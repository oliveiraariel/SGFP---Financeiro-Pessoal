## UC-011 — Gerenciar categorias

**Objetivo**

Permitir que o usuário mantenha as categorias utilizadas para organizar seus compromissos.

**Ator principal**

Usuário.

**Pré-condições**

O usuário deverá estar autenticado.

**Gatilho**

O usuário acessa o gerenciamento de categorias.

**Fluxo principal**

1. O usuário consulta as categorias existentes.
2. O usuário poderá criar uma categoria.
3. O usuário poderá renomear uma categoria.
4. O usuário poderá excluir uma categoria.
5. O sistema atualiza a organização dos compromissos conforme as alterações.

**Fluxos alternativos e exceções**

* A exclusão da categoria não deverá excluir os compromissos associados.
* O compromisso que perder sua categoria continuará existindo.
* A categoria poderá ser criada durante o cadastro de um compromisso.

**Pós-condições**

As categorias estarão atualizadas e os compromissos permanecerão preservados.

**Requisitos relacionados**

RF-007, RF-010, RF-011.

**Regras de negócio relacionadas**

Regras do módulo Categorias.
