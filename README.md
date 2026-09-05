# SGFP — Sistema de Gestão Financeira Pessoal

O **SGFP — Sistema de Gestão Financeira Pessoal** é um projeto acadêmico voltado ao planejamento, organização e acompanhamento das finanças pessoais.

O sistema está sendo desenvolvido de forma incremental, com documentação prévia das regras de negócio, requisitos, casos de uso, domínio, modelagem de dados, arquitetura, implementação e testes.

Esta árvore representa a versão documental reorganizada do projeto, preparada tanto para navegação humana quanto para futura utilização por agentes e orchestrators.

## Objetivo do Projeto

O SGFP tem como objetivo permitir que o usuário organize sua vida financeira por meio do controle de:

* contas financeiras;
* compromissos de entrada e saída;
* categorias;
* recorrências;
* lançamentos financeiros;
* transferências;
* patrimônio;
* planejamento mensal;
* configurações pessoais.

A primeira versão será uma aplicação Web em PHP sobre WordPress, utilizando API REST própria do SGFP.

## Características da Versão 1

Entre as principais características previstas para a V1 estão:

* cadastro e autenticação de usuário;
* gerenciamento de contas financeiras;
* registro manual das informações financeiras;
* controle de compromissos financeiros;
* categorização;
* recorrências mensais;
* efetivação de compromissos em lançamentos;
* transferências entre contas;
* tratamento básico de cartão de crédito e parcelamentos;
* Dashboard financeiro;
* tema claro e escuro;
* criação e restauração manual de cópias de segurança.

Não haverá integração bancária automática na Versão 1.

O catálogo funcional preserva os identificadores RF-001 a RF-021. A V1 possui **20 requisitos funcionais ativos**; RF-019 — proteção por PIN — está adiado para versão futura, sem renumeração dos requisitos posteriores.

## Metodologia de Desenvolvimento

O projeto segue uma sequência incremental de desenvolvimento:

1. Documento de Visão
2. Levantamento de Requisitos
3. Especificação de Requisitos
4. Casos de Uso
5. Mapa do Domínio
6. Modelagem Conceitual
7. Modelo Entidade-Relacionamento
8. Modelo Físico
9. Arquitetura da Aplicação
10. Desenvolvimento da API
11. Desenvolvimento da Interface Web
12. Testes

Cada etapa deve utilizar como entrada os artefatos já validados nas etapas anteriores.

## Status Atual

As etapas de levantamento e especificação de requisitos já possuem documentação consolidada.

Os Casos de Uso também foram organizados individualmente para facilitar manutenção, rastreabilidade e consumo por agentes.

As Etapas 5 — Mapa do Domínio, 6 — Modelagem Conceitual (MER), 7 — Modelo Entidade-Relacionamento (DER) e 8 — Modelo Físico foram concluídas e validadas.

A próxima etapa autorizada é a **Etapa 9 — Arquitetura da Aplicação**, pronta para iniciar.

## Estrutura do Repositório

```text
docs/projeto/
docs/requisitos/
docs/casos-de-uso/
docs/dominio/
docs/modelagem-dados/
docs/arquitetura/
docs/api/
docs/interface-web/
docs/testes/
docs/governanca/
```

### `docs/projeto/`

Contém os documentos de orientação geral do projeto:

* Documento de Visão;
* Plano de Desenvolvimento.

### `docs/requisitos/`

Contém:

* levantamento de requisitos por módulo;
* regras de negócio;
* Especificação de Requisitos de Software;
* requisitos funcionais;
* requisitos não funcionais;
* restrições;
* critérios de aceitação;
* rastreabilidade.

### `docs/casos-de-uso/`

Contém:

* contexto e atores;
* catálogo de Casos de Uso;
* Casos de Uso individualizados;
* referências de rastreabilidade.

### `docs/dominio/`

Reservado ao Mapa do Domínio.

### `docs/modelagem-dados/`

Contém a documentação e os artefatos validados da modelagem de dados:

* Modelagem Conceitual (MER);
* DER / Modelo Relacional;
* Modelo Físico;
* arquivos visuais e editáveis mantidos em `docs/modelagem-dados/artefatos/`.

### `docs/arquitetura/`

Reservado para a documentação da arquitetura da aplicação.

### `docs/api/`

Reservado para desenvolvimento e documentação da API.

### `docs/interface-web/`

Reservado para desenvolvimento da interface Web.

### `docs/testes/`

Reservado para estratégia, casos e resultados de testes.

### `docs/governanca/`

Contém os documentos relacionados à reorganização, proveniência e controle documental do projeto.

## Documentação Principal

Os principais pontos de entrada para compreender o SGFP são:

* `docs/projeto/documento-de-visao.md`
* `docs/projeto/plano-de-desenvolvimento.md`
* `docs/requisitos/levantamento/README.md`
* `docs/requisitos/srs/README.md`
* `docs/casos-de-uso/README.md`
* `docs/modelagem-dados/01-modelagem-conceitual-mer.md`
* `docs/modelagem-dados/02-modelo-entidade-relacionamento-der.md`
* `docs/modelagem-dados/03-modelo-fisico.md`
* `docs/governanca/relatorio-de-consolidacao.md`

## Fonte de Verdade

O arquivo:

```text
project-manifest.yaml
```

identifica as fontes canônicas do projeto e os problemas documentais conhecidos.

O arquivo:

```text
ORCHESTRATOR.md
```

define orientações para utilização do repositório por agentes e sistemas de orquestração.

Agentes não devem resolver silenciosamente conflitos de requisitos, regras de negócio ou escopo.

## Pendências Conhecidas

O histórico de divergências e reconciliações do projeto está registrado em:

```text
docs/governanca/relatorio-de-consolidacao.md
```

A divergência histórica do catálogo de requisitos funcionais já foi reconciliada.

Permanece aberta a `ISSUE-008`, referente à materialização da matriz direta Regra de Negócio → Requisito. Essa pendência não bloqueia o início da Arquitetura, mas deverá ser concluída antes do fechamento final da rastreabilidade de testes.

## Tecnologias

A arquitetura técnica será consolidada na etapa correspondente.

Já estão definidos como direcionamentos da Versão 1:

* aplicação Web;
* WordPress como plataforma da V1;
* PHP;
* plugin próprio do SGFP para o backend;
* API REST própria do SGFP sobre a infraestrutura REST do WordPress;
* MySQL ou MariaDB como banco de dados relacional;
* autenticação da V1 por e-mail e senha utilizando os mecanismos do WordPress.

A proteção opcional por PIN foi adiada para versão futura. Ela não integra a autenticação da V1 e, quando implementada, servirá apenas para bloqueio rápido da aplicação durante uma sessão já autenticada.

## Contexto Acadêmico

Projeto desenvolvido no contexto do curso de **Banco de Dados da FATEC**, envolvendo conhecimentos de:

* Engenharia de Software;
* Análise de Sistemas;
* Modelagem de Dados;
* Banco de Dados;
* Arquitetura de Software;
* Desenvolvimento de APIs;
* Desenvolvimento Web;
* Testes de Software.

## Evolução

O repositório deverá evoluir mantendo:

* rastreabilidade entre requisitos, casos de uso, implementação e testes;
* preservação das decisões de negócio;
* separação entre documentação normativa, histórica e técnica;
* simplicidade arquitetural;
* controle de mudanças por Git.

