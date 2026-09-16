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

1. O sistema direciona o usuário ao fluxo nativo de autenticação do WordPress usado como entrada do SGFP.
2. O usuário informa e-mail e senha conforme a regra da V1.
3. O WordPress valida as credenciais e estabelece a sessão.
4. O SGFP identifica o usuário por `wp_users.ID` e verifica provisionamento/autorização.
5. Se necessário, o provisionamento idempotente de reparo pode ser executado sem duplicar conta ou dados iniciais.
6. O usuário autorizado retorna ao gestor SGFP; usuário não provisionado/não autorizado não recebe o manager completo.

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
