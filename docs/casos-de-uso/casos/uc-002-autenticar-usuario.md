## UC-002 — Autenticar usuário

**Objetivo**

Permitir que o usuário acesse o sistema utilizando suas credenciais.

**Ator principal**

Usuário.

**Pré-condições**

O usuário deverá possuir cadastro válido.

**Gatilho**

O usuário solicita acesso ao sistema.

**Fluxo principal**

1. O sistema apresenta a tela de autenticação.
2. O usuário informa e-mail e senha.
3. O sistema valida as credenciais.
4. O sistema autentica o usuário.
5. O sistema carrega as informações pertencentes ao usuário.
6. O sistema apresenta a aplicação.

**Fluxos alternativos e exceções**

* Se as credenciais forem inválidas, o sistema deverá informar que o acesso não foi autenticado.
* O sistema não deverá revelar informações que permitam descobrir qual credencial está incorreta.
* Na Versão 1, a autenticação será realizada exclusivamente por e-mail e senha.

**Pós-condições**

O usuário estará autenticado e seus dados estarão disponíveis para utilização.

**Requisitos relacionados**

RF-002.

**Regras de negócio relacionadas**

Regras do módulo Usuários referentes à autenticação, carregamento do histórico e isolamento dos dados.
