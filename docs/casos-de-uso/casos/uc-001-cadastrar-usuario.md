## UC-001 — Cadastrar usuário

**Objetivo**

Permitir que uma pessoa crie seu acesso inicial ao SGFP.

**Ator principal**

Usuário.

**Pré-condições**

Nenhuma autenticação é necessária.

**Gatilho**

O usuário solicita o cadastro inicial.

**Fluxo principal**

1. O usuário acessa a opção de cadastro.
2. O sistema solicita os dados necessários para o cadastro.
3. O usuário informa os dados solicitados.
4. O sistema valida as informações.
5. O sistema cria o cadastro do usuário.
6. O sistema cria os dados iniciais previstos para o novo usuário, incluindo seu conjunto inicial de categorias, já vinculados ao usuário correspondente.
7. O sistema disponibiliza o acesso mediante as credenciais cadastradas.

**Fluxos alternativos e exceções**

* Se os dados obrigatórios forem inválidos, o sistema deverá informar o problema e permitir a correção.
* Se o e-mail já estiver cadastrado, o sistema deverá impedir a criação de um novo acesso utilizando o mesmo identificador e informar a situação.

**Pós-condições**

O usuário estará cadastrado, terá seus dados iniciais provisionados e estará apto a realizar a autenticação.

**Requisitos relacionados**

RF-001.

**Regras de negócio relacionadas**

Regras do módulo Usuários referentes a cadastro e identificação do usuário.
