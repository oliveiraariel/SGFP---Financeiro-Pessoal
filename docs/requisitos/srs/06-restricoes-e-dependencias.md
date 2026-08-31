# RESTRIÇÕES E DEPENDÊNCIAS

## Sistema de Gestão Financeira Pessoal

**Sigla:** SGFP

**Documento:** Especificação de Requisitos de Software (ERS)

**Versão:** 1.1

**Subetapa:** Etapa 3 — Especificação de Requisitos

**Data:** 23/08/2026

## 1. Objetivo

Esta seção registra as principais restrições e dependências identificadas para a Versão 1 do SGFP.

As restrições representam condições que limitam ou orientam o desenvolvimento da solução.

As dependências representam recursos, serviços, ambientes ou condições externas dos quais o funcionamento do sistema dependerá.

Os itens aqui registrados não deverão antecipar decisões que pertençam às etapas posteriores de arquitetura, modelagem ou implementação, salvo quando já houver decisão consolidada no projeto.

## 2. Restrições

### RE-001 — Escopo da Versão 1

A Versão 1 deverá respeitar o escopo definido no Documento de Visão e no Levantamento de Requisitos.

Funcionalidades planejadas para versões futuras não deverão ser incorporadas à Versão 1 sem decisão posterior de alteração de escopo.

### RE-002 — Inserção Manual de Dados Financeiros

Na Versão 1, as informações financeiras serão inseridas manualmente pelo usuário.

Não haverá integração automática com instituições financeiras para obtenção de movimentações.

### RE-003 — Ausência de Integrações Financeiras Externas

A Versão 1 não possuirá integração com bancos, instituições financeiras ou outros serviços externos destinados à obtenção ou sincronização automática de dados financeiros.

A API REST própria da aplicação não será considerada uma integração externa.

### RE-004 — Periodicidade das Recorrências

Na Versão 1, as recorrências serão exclusivamente mensais.

Não serão contempladas recorrências semanais, quinzenais, diárias ou com outros intervalos.

### RE-005 — Conta Principal

O sistema deverá possuir uma única conta definida como principal.

Os compromissos financeiros normais incidirão sobre essa conta.

### RE-006 — Contas Secundárias

As contas classificadas como secundárias serão utilizadas para movimentações decorrentes de transferências.

As regras específicas de movimentação entre conta principal e contas secundárias deverão ser respeitadas.

### RE-007 — Transferências

As transferências ocorrerão somente entre contas pertencentes ao mesmo usuário.

Na Versão 1, as transferências deverão respeitar as regras definidas para conta principal e contas secundárias, não sendo tratadas como movimentações externas.

### RE-008 — Cartão de Crédito na Versão 1

O sistema não realizará, na Versão 1, controle individual de compras realizadas no cartão de crédito, composição automática de fatura, controle de limite ou controle específico de fechamento e vencimento.

O pagamento da fatura será representado por compromisso financeiro de Saída.

### RE-009 — Parcelamentos na Versão 1

Parcelamentos não serão tratados como entidade financeira independente.

Serão representados por meio dos mecanismos de compromissos financeiros e recorrências já definidos.

### RE-010 — Dashboard

O Dashboard não possuirá dados financeiros independentes.

Seus valores deverão ser obtidos a partir dos dados existentes no sistema.

### RE-011 — Relatórios

Na Versão 1, não serão definidos ou implementados relatórios financeiros específicos.

### RE-012 — Backup Automático

A Versão 1 não realizará backup automático.

A criação da cópia de segurança será realizada manualmente pelo usuário.

### RE-013 — Autenticação por Provedores Externos

A Versão 1 utilizará autenticação por e-mail e senha.

Login utilizando Google, Apple/iCloud ou outros provedores externos permanecerá fora do escopo da versão inicial.

### RE-014 — Sincronização e Armazenamento Externo

A Versão 1 não realizará sincronização em nuvem nem utilizará serviços externos de armazenamento como parte obrigatória da funcionalidade financeira.

### RE-015 — Anexação de Arquivos

A anexação de imagens ou outros arquivos aos compromissos não fará parte do escopo da Versão 1.

### RE-016 — Plataforma da Versão 1

A primeira versão será desenvolvida como aplicação Web utilizando PHP sobre WordPress.

A solução utilizará uma API REST própria do SGFP apoiada na infraestrutura REST do WordPress, conforme estabelecido no Documento de Visão e no Plano de Desenvolvimento.

O backend específico do SGFP será implementado em plugin próprio.

### RE-017 — Separação entre Domínio e Implementação

As definições desta ERS não deverão determinar automaticamente entidades, classes, tabelas ou outros componentes de implementação.

Essas decisões serão realizadas nas etapas posteriores de modelagem e arquitetura.

### RE-018 — Critérios Técnicos Ainda Não Definidos

A Versão 1 ainda não possui valores quantitativos formalmente definidos para aspectos como:

- tempo máximo de resposta;
- disponibilidade mínima;
- capacidade máxima de usuários;
- capacidade máxima de dados;
- navegadores e versões suportados;
- critérios técnicos detalhados de responsividade.

Esses critérios deverão ser definidos posteriormente quando houver base técnica suficiente para estabelecê-los.

## 3. Dependências

### DEP-001 — Ambiente de Execução Web

O funcionamento da aplicação dependerá de um ambiente capaz de executar PHP e WordPress e de disponibilizar a aplicação Web definida para a Versão 1.

Os detalhes técnicos de instalação, servidor Web, versões suportadas, configuração e disponibilização serão definidos na etapa de Arquitetura da Aplicação.

### DEP-002 — Banco de Dados

O sistema dependerá de banco de dados relacional compatível com a plataforma WordPress, utilizando MySQL ou MariaDB na implementação da Versão 1.

A estrutura definitiva das tabelas próprias do SGFP, chaves, restrições, índices e demais decisões físicas será definida nas etapas de Modelagem e Arquitetura.

Os dados de identidade e autenticação que forem responsabilidade do WordPress não deverão ser duplicados desnecessariamente nas tabelas próprias do SGFP.

### DEP-003 — Serviço de E-mail

A recuperação de senha e o envio das cópias de segurança criadas manualmente dependerão da disponibilidade de um meio de envio de e-mail ao endereço cadastrado pelo usuário.

A Versão 1 utilizará o fluxo de recuperação de senha fornecido pelo WordPress e enviará ao e-mail cadastrado as cópias de segurança solicitadas pelo usuário. O serviço ou a tecnologia de entrega de e-mail utilizado no ambiente será definido posteriormente.

A proteção por PIN foi adiada para versão futura e não possuirá mecanismo próprio de recuperação por e-mail.

### DEP-004 — API REST

A interface Web dependerá da API REST própria da aplicação para acesso às funcionalidades e aos dados disponibilizados pelo sistema.

A API será desenvolvida antes da implementação da interface Web, conforme o Plano de Desenvolvimento.

### DEP-005 — Infraestrutura de Disponibilização

A utilização do sistema dependerá da infraestrutura necessária para disponibilizar a aplicação Web e seus componentes.

A definição da infraestrutura pertence à etapa de Arquitetura e não será antecipada nesta especificação.

## 4. Dependências que Não Constituem Integrações Externas de Negócio

As seguintes dependências não caracterizam, por si só, integrações financeiras externas:

- API REST própria do SGFP;
- banco de dados utilizado pela própria aplicação;
- serviço utilizado para envio de e-mails de recuperação de senha e das cópias de segurança solicitadas;
- infraestrutura necessária para execução e disponibilização do sistema.

A existência dessas dependências não altera a decisão de que a Versão 1 não possuirá integração com instituições bancárias ou outros serviços externos de negócio.

## 5. Relação com Outros Documentos

As restrições e dependências desta seção deverão ser interpretadas em conjunto com:

- Documento de Visão;
- Levantamento de Requisitos;
- Requisitos Funcionais;
- Requisitos Não Funcionais;
- Plano de Desenvolvimento do Projeto.

As regras de negócio não serão reproduzidas nesta seção.

Quando uma restrição depender de uma regra específica, esta deverá ser consultada no Levantamento de Requisitos.

## 6. Impacto nas Etapas Posteriores

As restrições e dependências identificadas nesta seção deverão ser consideradas nas etapas seguintes:

### Casos de Uso

As restrições que afetarem a interação do usuário deverão ser refletidas nos fluxos, pré-condições e exceções dos casos de uso.

### Modelagem do Domínio

As restrições de negócio deverão ser consideradas na análise dos conceitos e responsabilidades do domínio.

### Modelagem de Dados

As restrições de integridade e persistência identificadas posteriormente deverão ser traduzidas em estruturas e mecanismos apropriados.

### Arquitetura

As dependências técnicas, limitações de infraestrutura e requisitos de qualidade ainda não quantificados deverão ser refinados durante a definição da arquitetura.

### Implementação

A implementação deverá respeitar as restrições consolidadas nas etapas anteriores.

### Testes

As restrições deverão ser utilizadas como base para a definição dos cenários de teste correspondentes.

## 7. Situação da Subetapa

Esta subetapa consolida as restrições e dependências conhecidas na Versão 1 sem antecipar decisões técnicas ainda não definidas.

Novas restrições ou dependências poderão ser registradas posteriormente caso decisões tomadas nas etapas seguintes produzam impacto sobre os requisitos do sistema.

## 8. Histórico de Atualização

### Versão 1.1 — 30/08/2026

Atualização para refletir a utilização de PHP sobre WordPress na Versão 1, o banco relacional MySQL/MariaDB compatível com a plataforma, a delegação de identidade/autenticação ao WordPress e o adiamento da proteção por PIN para versão futura. A dependência de e-mail da V1 passa a se aplicar à recuperação de senha e ao envio das cópias de segurança solicitadas, e não à recuperação de PIN.

### Versão 1.0 — 23/08/2026

Primeira consolidação da subetapa **Restrições e Dependências** da Etapa 3 — Especificação de Requisitos.

Foram registrados:

- restrições de escopo da Versão 1;
- limitações funcionais já decididas;
- restrições relacionadas a recorrência, contas, transferências e casos específicos;
- restrições relacionadas a integrações externas;
- dependências de infraestrutura, banco de dados, API e serviço de e-mail;
- critérios técnicos que ainda permanecem pendentes de definição.
