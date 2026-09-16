## UC-004 — Recuperar senha

**Objetivo**

Permitir que o usuário recupere a senha esquecida utilizando o e-mail cadastrado.

**Atores**

Usuário; Serviço de E-mail.

**Pré-condições**

O usuário deverá possuir e-mail cadastrado.

**Gatilho**

O usuário solicita recuperação de senha.

**Fluxo principal**

1. O usuário acessa o fluxo nativo de recuperação de senha do WordPress disponibilizado pela entrada do SGFP.
2. O WordPress solicita o endereço de e-mail cadastrado.
3. O usuário informa o e-mail.
4. O sistema verifica a solicitação.
5. O sistema aciona o mecanismo de recuperação de senha disponibilizado pelo WordPress.
6. O WordPress encaminha a comunicação de recuperação para o e-mail cadastrado.
7. O usuário segue o fluxo de recuperação apresentado pela plataforma.
8. O sistema permite a definição de uma nova senha conforme o mecanismo do WordPress.
9. O usuário informa a nova senha.
10. O fluxo WordPress confirma a operação e permite retornar à entrada/autenticação do SGFP.

**Fluxos alternativos e exceções**

* O sistema não deverá revelar a senha anterior.
* Se o mecanismo de recuperação do WordPress considerar o link ou a chave de recuperação expirado, inválido ou já utilizado, a definição de nova senha não deverá ser permitida.

**Pós-condições**

A senha anterior será substituída pela nova senha criada pelo usuário.

**Requisitos relacionados**

RF-003.

**Regras de negócio relacionadas**

Regras do módulo Usuários referentes à recuperação de senha.
