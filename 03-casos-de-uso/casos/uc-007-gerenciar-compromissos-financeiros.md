## UC-007 — Gerenciar compromissos financeiros

**Objetivo**

Permitir que o usuário cadastre e mantenha compromissos financeiros.

**Ator principal**

Usuário.

**Pré-condições**

O usuário deverá estar autenticado.

**Gatilho**

O usuário acessa a área de compromissos ou inicia o cadastro de um novo compromisso.

**Fluxo principal**

1. O usuário solicita o cadastro de um compromisso.
2. O sistema solicita nome, valor, natureza e categoria.
3. O usuário informa os dados.
4. O usuário seleciona uma categoria.
5. Caso necessário, o usuário cria uma nova categoria durante o cadastro.
6. O sistema valida as informações.
7. O sistema registra o compromisso.
8. O compromisso fica disponível para consulta e posterior efetivação.

**Fluxos alternativos e exceções**

* O compromisso poderá ser Entrada ou Saída.
* A categoria será obrigatória.
* Um compromisso poderá possuir valor igual a R$ 0,00.
* Compromissos pendentes poderão ser alterados.
* Compromissos pendentes poderão ser removidos.
* Compromissos já efetivados não poderão ser alterados ou excluídos diretamente.

**Pós-condições**

O compromisso estará registrado no período correspondente.

**Requisitos relacionados**

RF-008.

**Regras de negócio relacionadas**

Regras do módulo Compromissos e do módulo Categorias.
