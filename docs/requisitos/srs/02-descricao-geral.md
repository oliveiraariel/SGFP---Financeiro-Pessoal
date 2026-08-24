# 2. Descrição Geral do Sistema

## 2.1 Visão Geral

O SGFP — Sistema de Gestão Financeira Pessoal é uma aplicação destinada ao planejamento, registro e acompanhamento das finanças pessoais do usuário. A solução organiza informações financeiras registradas manualmente e distingue compromissos previstos de movimentações efetivamente realizadas.

## 2.2 Objetivo do Sistema

Permitir que o usuário organize contas, compromissos, categorias, recorrências, lançamentos e transferências, acompanhando a situação financeira de cada período e o impacto das movimentações nos saldos e no patrimônio.

## 2.3 Escopo da Versão 1

A Versão 1 contempla:

- contas financeiras;
- compromissos financeiros;
- categorias;
- recorrências mensais;
- lançamentos financeiros;
- transferências;
- casos específicos de compromissos, como cartão de crédito e parcelamentos;
- Dashboard;
- configurações;
- mecanismos de segurança relacionados às funcionalidades da V1;
- cópia e restauração manual de segurança;
- interface Web e API REST própria da aplicação.

## 2.4 Características Gerais de Funcionamento

As informações financeiras serão inseridas manualmente pelo usuário e organizadas por período. Compromissos representam obrigações ou previsões; lançamentos representam movimentações efetivamente realizadas. Alterações relevantes deverão refletir nos saldos e nas visões consolidadas de acordo com as regras de negócio.

## 2.5 Limitações e Fora de Escopo da Versão 1

Não fazem parte da primeira versão, entre outros itens registrados para evolução futura:

- integrações bancárias ou sincronização automática de dados financeiros;
- autenticação por provedores externos;
- backup automático e sincronização em nuvem;
- aplicativo mobile;
- relatórios financeiros específicos;
- controle detalhado de compras, limite e composição automática de faturas de cartão;
- anexação de arquivos aos compromissos.

## 2.6 Usuário do Sistema

A Versão 1 é direcionada ao usuário final responsável pelo próprio controle financeiro. Os dados financeiros de cada usuário deverão permanecer isolados e acessíveis somente mediante os mecanismos de autenticação definidos pelo sistema.

## 2.7 Limite desta Especificação

A ERS descreve comportamentos, requisitos e restrições do software. A definição formal das entidades do domínio, relacionamentos, modelo de dados e decisões arquiteturais pertence às etapas posteriores do projeto.
