# Módulo 1 — Usuários

## Objetivo

Permitir que cada pessoa utilize o SGFP de forma independente, com identidade e autenticação fornecidas pelo WordPress e dados financeiros isolados por usuário.

## Regras de Negócio

### RN-001 — Usuário Individual
Cada usuário possuirá seu próprio conjunto de dados financeiros. A aplicação Web poderá possuir múltiplos usuários cadastrados, sem compartilhamento de dados financeiros entre eles na V1.

### RN-002 — Autenticação
O acesso dependerá de login por e-mail e senha. Após logout ou expiração da sessão, será necessária nova autenticação. O PIN não participa da autenticação da V1.

### RN-003 — Dados do Usuário
Após o login, o SGFP carregará os dados pertencentes ao usuário autenticado: sua Conta Financeira única, compromissos, lançamentos, categorias, recorrências e configurações.

### RN-004 — Segurança das Senhas
As senhas nunca serão armazenadas em texto puro. A V1 utilizará os mecanismos do WordPress para identidade, armazenamento seguro e autenticação.

### RN-005 — Isolamento dos Dados
Toda operação financeira será executada no contexto do usuário autenticado. Um usuário não poderá acessar os dados de outro.

### RN-006 — Cadastro e Provisionamento Inicial
Na primeira utilização, a pessoa deverá realizar seu cadastro. Após a criação válida da identidade WordPress, o SGFP deverá provisionar automaticamente:
- exatamente uma Conta Financeira com nome inicial **Minha Conta**;
- o conjunto inicial de categorias;
- as configurações padrão necessárias.

O usuário não deverá entrar no gestor em estado válido sem sua conta única provisionada.

### RN-007 — Recuperação de Senha
A recuperação utilizará o mecanismo do WordPress e o e-mail cadastrado. A senha anterior nunca será revelada.

### RN-008 — Proteção por PIN — Versão Futura
O PIN não faz parte da V1. Em versão futura poderá funcionar como bloqueio rápido durante sessão autenticada, sem substituir o login principal.

### RN-009 — Alteração de Senha
O usuário poderá alterar sua senha pelos mecanismos disponibilizados pela aplicação e pelo WordPress.

### RN-010 — Exclusão da Conta de Acesso
O usuário autenticado poderá excluir definitivamente sua conta de acesso. A operação removerá os dados SGFP e a identidade/login correspondente no WordPress, após duas etapas de confirmação, sendo a última a digitação exata de **EXCLUIR CONTA**.

A exclusão é distinta do reset do perfil financeiro e encerra o acesso ao portal.

## Funcionalidades da Versão 1
- Cadastro de usuário.
- Provisionamento automático da Conta Financeira única e das categorias padrão.
- Login e logout.
- Recuperação e alteração de senha.
- Isolamento dos dados.
- Exclusão definitiva da conta de acesso com dupla confirmação.

## Funcionalidades Futuras
- PIN opcional.
- Provedores externos de login.
- Sincronização em nuvem.

## Decisões Tomadas
- WordPress fornece identidade, autenticação, sessão e credenciais.
- Cada usuário possui exatamente uma Conta Financeira na V1.
- O nome inicial da conta é **Minha Conta**.
- A conta e as categorias padrão são criadas no provisionamento inicial.
- A exclusão da conta de acesso remove dados SGFP e login WordPress após dupla confirmação.

**Data de Revisão:** 14/09/2026
