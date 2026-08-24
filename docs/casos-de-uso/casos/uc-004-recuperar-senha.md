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
5. O sistema gera um link de recuperação.
6. O sistema envia o link ao e-mail cadastrado.
7. O usuário acessa o link.
8. O sistema valida a validade e a utilização do link.
9. O sistema permite a criação de uma nova senha.
10. O usuário informa a nova senha.
11. O sistema confirma a operação.

**Fluxos alternativos e exceções**

* O link deverá possuir validade limitada conforme a regra definida no Levantamento de Requisitos.
* O link deverá ser de utilização única.
* O sistema não deverá revelar a senha anterior.
* Um link expirado ou já utilizado não deverá permitir a criação de nova senha.

**Pós-condições**

A senha anterior será substituída pela nova senha criada pelo usuário.

**Requisitos relacionados**

RF-003.

**Regras de negócio relacionadas**

Regras do módulo Usuários referentes à recuperação de senha.
