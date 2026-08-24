# Módulo 1 — Usuários

## Objetivo

Permitir que cada pessoa utilize o sistema de forma independente, garantindo que seus dados financeiros sejam armazenados de maneira segura e acessados somente mediante autenticação.

## Regras de Negócio

### RN-001 — Usuário Individual

Cada usuário possuirá seu próprio conjunto de dados financeiros.

Não haverá compartilhamento de dados financeiros entre diferentes usuários.

### RN-002 — Autenticação

O acesso ao sistema dependerá da realização de login utilizando e-mail e senha cadastrados pelo próprio usuário.

Após a autenticação, o sistema carregará automaticamente todas as informações pertencentes àquele usuário.

O PIN não fará parte da autenticação inicial.

Após o logout ou a expiração da sessão, será necessário realizar novamente a autenticação utilizando e-mail e senha.

### RN-003 — Histórico do Usuário

Após o login, o usuário deverá visualizar todo o seu histórico financeiro.

Entre as informações carregadas estão:

- contas financeiras;
- lançamentos;
- categorias;
- recorrências;
- transferências;
- saldo das contas;
- configurações pessoais.

### RN-004 — Segurança das Senhas

As senhas nunca deverão ser armazenadas em texto puro.

O sistema deverá utilizar algoritmos de hash para armazenamento seguro das credenciais.

### RN-005 — Isolamento dos Dados

Os dados pertencentes a um usuário não poderão ser acessados por outro usuário.

Cada usuário possuirá seu próprio conjunto de informações financeiras.

### RN-006 — Cadastro Inicial

Na primeira utilização da versão Web do sistema, o usuário deverá realizar seu cadastro antes de acessar a aplicação.

O cadastro permitirá posteriormente a autenticação utilizando e-mail e senha.

### RN-007 — Recuperação de Senha e PIN

O sistema deverá permitir que o usuário recupere sua senha ou PIN utilizando o endereço de e-mail cadastrado.

A recuperação será realizada por meio de um link enviado ao e-mail cadastrado.

O link de recuperação terá validade de 24 horas e poderá ser utilizado uma única vez.

A recuperação não deverá revelar a senha ou o PIN anterior.

Durante o processo de recuperação, o usuário deverá criar uma nova senha ou um novo PIN.

### RN-008 — PIN

O PIN será opcional.

O PIN não substituirá o e-mail e a senha na autenticação inicial.

Quando habilitado, o PIN funcionará como mecanismo de desbloqueio rápido de uma sessão previamente autenticada.

O usuário poderá bloquear manualmente a aplicação.

Para desbloquear a aplicação, será necessário informar o PIN.

O bloqueio automático por inatividade não fará parte da Versão 1.

### RN-009 — Alteração de Senha e PIN

O usuário poderá alterar sua senha e seu PIN diretamente pela aplicação.

A alteração da senha ou do PIN exigirá confirmação obrigatória.

As confirmações de alteração de senha e PIN não poderão ser desabilitadas pelas configurações gerais de confirmação de edição.

## Funcionalidades da Versão 1

- Cadastro de usuário.
- Login utilizando e-mail e senha.
- Recuperação de senha por e-mail.
- Recuperação de PIN por e-mail.
- Alteração de senha diretamente pela aplicação.
- Alteração de PIN diretamente pela aplicação.
- Utilização opcional de PIN para desbloqueio rápido.
- Bloqueio manual da aplicação.
- Desbloqueio da aplicação utilizando PIN.
- Armazenamento seguro das senhas utilizando hash.
- Carregamento automático do histórico financeiro após o login.

## Funcionalidades Previstas para Versões Futuras

- Login utilizando conta Google.
- Sincronização dos dados em nuvem.
- Backup automático em serviços externos.
- Bloqueio automático da aplicação após período de inatividade.

## Decisões Tomadas

- Cada instalação será utilizada por um único usuário.
- Não haverá compartilhamento de dados.
- O sistema exigirá autenticação por e-mail e senha.
- O PIN será opcional.
- O PIN não fará parte da autenticação inicial.
- O PIN funcionará como mecanismo de desbloqueio rápido de uma sessão previamente autenticada.
- O usuário poderá bloquear manualmente a aplicação.
- O desbloqueio da aplicação poderá ser realizado utilizando o PIN.
- Após o logout ou a expiração da sessão, será necessário realizar novamente a autenticação utilizando e-mail e senha.
- O bloqueio automático por inatividade não fará parte da Versão 1.
- O histórico financeiro será carregado automaticamente após o login.
- As senhas serão armazenadas utilizando hash.
- A senha poderá ser alterada diretamente pela aplicação.
- O PIN poderá ser alterado diretamente pela aplicação.
- A alteração de senha e PIN exigirá confirmação obrigatória.
- A recuperação de senha será realizada por e-mail.
- A recuperação de PIN será realizada por e-mail.
- O link de recuperação terá validade de 24 horas.
- O link de recuperação poderá ser utilizado uma única vez.
- A recuperação não revelará a credencial anterior.
- A recuperação permitirá a criação de uma nova senha ou PIN.
- A autenticação utilizando conta Google ficará prevista para uma versão futura do sistema.

**Data de Revisão**

13 / 08 / 2026
