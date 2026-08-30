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

1. O usuário solicita a recuperação.
2. O sistema solicita o endereço de e-mail cadastrado.
3. O usuário informa o e-mail.
4. O sistema verifica a solicitação.
5. O sistema aciona o mecanismo de recuperação de senha disponibilizado pelo WordPress.
6. O WordPress encaminha a comunicação de recuperação para o e-mail cadastrado.
7. O usuário segue o fluxo de recuperação apresentado pela plataforma.
8. O sistema permite a definição de uma nova senha conforme o mecanismo do WordPress.
9. O usuário informa a nova senha.
10. O sistema confirma a operação.

**Fluxos alternativos e exceções**

* O sistema não deverá revelar a senha anterior.

**Pós-condições**

A senha anterior será substituída pela nova senha criada pelo usuário.

**Requisitos relacionados**

RF-003.

**Regras de negócio relacionadas**

Regras do módulo Usuários referentes à recuperação de senha.
