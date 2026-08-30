# Módulo 1 — Usuários

## Objetivo

Permitir que cada pessoa utilize o sistema de forma independente, garantindo que seus dados financeiros sejam armazenados de maneira segura e acessados somente mediante autenticação.

## Regras de Negócio

### RN-001 — Usuário Individual

Cada usuário possuirá seu próprio conjunto de dados financeiros.

A aplicação Web poderá possuir múltiplos usuários cadastrados.

Não haverá compartilhamento de dados financeiros entre diferentes usuários na Versão 1.

### RN-002 — Autenticação

O acesso ao sistema dependerá da realização de login utilizando e-mail e senha cadastrados pelo próprio usuário.

Após a autenticação, o sistema carregará automaticamente as informações pertencentes àquele usuário.

Após o logout ou a expiração da sessão, será necessário realizar novamente a autenticação utilizando e-mail e senha.

A proteção por PIN não fará parte da autenticação da Versão 1.

### RN-003 — Histórico do Usuário

Após o login, o usuário deverá visualizar seu histórico financeiro e as informações pertencentes à sua conta.

Entre as informações carregadas estão:

- contas financeiras;
- compromissos financeiros;
- lançamentos;
- categorias;
- recorrências;
- transferências;
- saldos derivados das movimentações;
- configurações pessoais.

### RN-004 — Segurança das Senhas

As senhas nunca deverão ser armazenadas em texto puro.

A Versão 1 utilizará os mecanismos de autenticação e armazenamento seguro de credenciais disponibilizados pelo WordPress.

### RN-005 — Isolamento dos Dados

Os dados pertencentes a um usuário não poderão ser acessados por outro usuário.

Cada operação sobre dados financeiros deverá ser executada no contexto do usuário autenticado.

### RN-006 — Cadastro Inicial

Na primeira utilização da aplicação Web, a pessoa deverá realizar seu cadastro antes de acessar o SGFP.

O cadastro permitirá posteriormente a autenticação utilizando e-mail e senha.

Após a criação do usuário, os dados iniciais previstos pelo SGFP, incluindo o conjunto inicial de categorias, deverão ser criados já vinculados ao usuário correspondente.

### RN-007 — Recuperação de Senha

O sistema deverá permitir a recuperação da senha utilizando o endereço de e-mail cadastrado.

A Versão 1 utilizará o mecanismo de recuperação de senha disponibilizado pelo WordPress.

A recuperação não deverá revelar a senha anterior.

### RN-008 — Proteção por PIN — Versão Futura

A proteção por PIN não fará parte da Versão 1.

Em versão futura, o PIN poderá ser ativado voluntariamente pelo usuário como mecanismo de bloqueio rápido do SGFP durante uma sessão já autenticada.

O PIN não substituirá a autenticação principal por e-mail e senha.

O desbloqueio por PIN será aplicável somente quando o SGFP tiver sido previamente bloqueado.

Em caso de esquecimento ou bloqueio do PIN, a redefinição deverá exigir nova confirmação da senha da conta, sem mecanismo próprio de recuperação do PIN por e-mail.

O bloqueio automático por inatividade poderá ser avaliado juntamente com essa funcionalidade futura.

### RN-009 — Alteração de Senha

O usuário poderá alterar sua senha por meio dos mecanismos disponibilizados pela aplicação e pelo WordPress.

As regras técnicas de confirmação e proteção da alteração serão definidas na arquitetura e implementação.

## Funcionalidades da Versão 1

- Cadastro de usuário.
- Login utilizando e-mail e senha.
- Logout.
- Recuperação de senha.
- Alteração de senha.
- Armazenamento seguro das senhas por meio dos mecanismos do WordPress.
- Carregamento automático dos dados financeiros do usuário autenticado.
- Isolamento dos dados entre usuários.

## Funcionalidades Previstas para Versões Futuras

- Proteção opcional por PIN para bloqueio rápido do SGFP durante sessão autenticada.
- Bloqueio manual do SGFP e desbloqueio por PIN.
- Limite de tentativas de PIN.
- Redefinição do PIN mediante confirmação da senha da conta.
- Bloqueio automático da aplicação após período de inatividade.
- Login utilizando provedores externos, como Google.
- Sincronização dos dados em nuvem.
- Backup automático em serviços externos.

## Decisões Tomadas

- A aplicação Web poderá possuir múltiplos usuários cadastrados.
- Cada usuário possuirá dados financeiros próprios e isolados.
- A Versão 1 utilizará autenticação por e-mail e senha.
- O WordPress será utilizado para identidade, autenticação, sessão, armazenamento seguro e recuperação de senha.
- O PIN não fará parte da Versão 1.
- O PIN permanecerá planejado para versão futura como mecanismo opcional de bloqueio rápido durante uma sessão já autenticada.
- O PIN futuro não substituirá a autenticação principal por e-mail e senha.
- Não será desenvolvido mecanismo próprio de recuperação de PIN por e-mail ou token.
- Após logout ou expiração da sessão, será necessário autenticar novamente utilizando e-mail e senha.
- O histórico financeiro será carregado no contexto do usuário autenticado.
- Os dados iniciais do SGFP que pertençam ao usuário deverão ser criados já vinculados a ele.
- A autenticação utilizando provedores externos permanecerá prevista para versão futura.

**Data de Revisão**

30 / 08 / 2026
