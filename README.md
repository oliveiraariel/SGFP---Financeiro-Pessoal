# SGFP — Sistema de Gestão Financeira Pessoal

[Português (Brasil)](README.md) | [English](README.en.md)

O **SGFP — Sistema de Gestão Financeira Pessoal** é um projeto acadêmico voltado ao planejamento, organização e acompanhamento das finanças pessoais.

O sistema está sendo desenvolvido de forma incremental, com documentação prévia das regras de negócio, requisitos, casos de uso, domínio, modelagem de dados, arquitetura, implementação e testes.

Esta árvore representa a versão documental reorganizada do projeto, preparada tanto para navegação humana quanto para futura utilização por agentes e orchestrators.

## Baseline V1 simplificada — 14/09/2026

A V1 vigente adota **uma única Conta Financeira por usuário**, criada automaticamente como **Minha Conta**. O saldo é derivado dos lançamentos dessa conta. Transferências e Patrimônio Total saíram da V1. Backup manual é local em ZIP. Configurações incluem reset do perfil financeiro e exclusão definitiva do login, ambos com dupla confirmação.

Consulte `docs/governanca/baseline-v1-simplificada-2026-09-14.md`.


## Objetivo do Projeto

O SGFP tem como objetivo permitir que o usuário organize sua vida financeira por meio do controle de:

* Conta Financeira única;
* compromissos de entrada e saída;
* categorias;
* recorrências;
* lançamentos financeiros;
* planejamento mensal;
* configurações pessoais.

A primeira versão será uma aplicação Web em PHP sobre WordPress, utilizando API REST própria do SGFP.

## Características da Versão 1

Entre as principais características previstas para a V1 estão:

* cadastro e autenticação de usuário;
* gerenciamento da Conta Financeira única;
* registro manual das informações financeiras;
* controle de compromissos financeiros;
* categorização;
* recorrências mensais;
* efetivação de compromissos em lançamentos;
* tratamento básico de cartão de crédito e parcelamentos;
* Dashboard financeiro;
* tema claro e escuro;
* backup local em ZIP e restauração por arquivo;
* reset do perfil financeiro;
* exclusão definitiva da conta de acesso.

Não haverá integração bancária automática na Versão 1.

O catálogo funcional vigente vai de RF-001 a RF-023. A V1 possui **19 requisitos funcionais ativos**. RF-012, RF-013, RF-014 (Transferências) e RF-019 (PIN) permanecem preservados como futuros/inativos.

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

As Etapas 9 — Arquitetura da Aplicação e 10 — Desenvolvimento da API foram concluídas originalmente sobre a baseline anterior. Em 14/09/2026, a documentação foi revisada para a baseline simplificada e o backend da Etapa 10 foi posteriormente reconciliado e validado na branch `feat/stage-9-10-backend`, documentado no Draft PR #12. A Etapa 11 — Interface Web está em andamento na branch `feat/stage-11-web-interface` e ainda requer reconciliação com a nova baseline e com o backend atualizado. A Etapa 12 permanece futura para consolidação formal dos testes.

## Estrutura do Repositório

```text
SGFP---Financeiro-Pessoal/
├── README.md
├── AGENTS.md
├── ORCHESTRATOR.md
├── HANDOFF.md
├── project-manifest.yaml
│
├── docs/
│   ├── projeto/
│   ├── requisitos/
│   ├── casos-de-uso/
│   ├── dominio/
│   ├── modelagem-dados/
│   ├── arquitetura/
│   ├── api/
│   ├── interface-web/
│   ├── testes/
│   └── governanca/
│
└── src/                         # pacote do plugin WordPress SGFP
    ├── sgfp.php                # arquivo principal do plugin
    ├── composer.json
    ├── composer.lock
    ├── phpunit.xml.dist
    └── src/                    # código-fonte PHP carregado por PSR-4
        ├── Activation.php
        ├── Deactivation.php
        ├── Plugin.php
        ├── Application/
        ├── Domain/
        ├── Infrastructure/
        ├── REST/
        └── Tests/
```

O diretório `src/` na raiz do repositório contém o pacote do plugin WordPress. O ponto de entrada reconhecido pelo WordPress é `src/sgfp.php`; o diretório interno `src/src/` contém o código-fonte PHP organizado por responsabilidades.

A descrição das responsabilidades de cada diretório e camada está em `docs/arquitetura/01-arquitetura-da-aplicacao.md`.

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
* `docs/governanca/baseline-v1-simplificada-2026-09-14.md`
* `docs/governanca/PROMPT-RETOMADA.md`
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

## Tecnologias e Ferramentas

| Finalidade | Tecnologia / Ferramenta |
| --- | --- |
| Linguagem principal | PHP 8.1+ |
| Plataforma | WordPress |
| API | WordPress REST API (`sgfp/v1`) |
| Banco de dados | MySQL / MariaDB (InnoDB) |
| Dependências | Composer |
| Autoload | PSR-4 |
| Testes automatizados | PHPUnit |
| Análise estática | PHPStan |
| Padrões de código | PHP_CodeSniffer (PHPCS) |
| Controle de versão | Git / GitHub |
| Ambiente de desenvolvimento | Linux Mint + VS Code |

O papel de cada tecnologia na arquitetura e no processo de desenvolvimento está detalhado em `docs/arquitetura/01-arquitetura-da-aplicacao.md`.
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