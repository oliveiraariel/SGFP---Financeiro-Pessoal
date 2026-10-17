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

1. O usuário acessa o login nativo WordPress pela entrada do SGFP.
2. Informa credenciais na tela WordPress.
3. O WordPress valida credenciais e estabelece a sessão.
4. O SGFP verifica identidade `wp_users.ID`, `_sgfp_provisioned` e `use_sgfp`.
5. O hook `wp_login` pode executar reparo idempotente de provisionamento.
6. Somente o usuário autorizado recebe o gestor e seus dados.

**Fluxos alternativos e exceções**

* Se as credenciais forem inválidas, o sistema deverá informar que o acesso não foi autenticado.
* O sistema não deverá revelar informações que permitam descobrir qual credencial está incorreta.
* A V1 exige login exclusivamente por e-mail e senha. **Pendência de conformidade:** a implementação 1.0.15 usa `wp-login.php` e não foi identificado filtro próprio para impedir nome de usuário.

**Pós-condições**

O usuário autenticado, provisionado e com `use_sgfp` recebe acesso aos dados financeiros. A identidade WordPress pode existir antes do provisionamento SGFP.

**Requisitos relacionados**

RF-002.

**Regras de negócio relacionadas**

Regras do módulo Usuários referentes à autenticação, carregamento do histórico e isolamento dos dados.
