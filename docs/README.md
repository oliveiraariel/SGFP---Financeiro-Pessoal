# Documentação do SGFP

Este diretório concentra os artefatos documentais do **SGFP — Sistema de Gestão Financeira Pessoal**.

A organização é semântica e independente da futura estrutura de código. A numeração e o estado das etapas permanecem definidos em `../project-manifest.yaml` e no Plano de Desenvolvimento.

## Estrutura

- `projeto/` — Documento de Visão e Plano de Desenvolvimento.
- `requisitos/` — Levantamento de Requisitos e SRS.
- `casos-de-uso/` — catálogo, atores, rastreabilidade e especificações de Casos de Uso.
- `dominio/` — Mapa do Domínio.
- `modelagem-dados/` — Modelagem Conceitual, DER e Modelo Físico.
- `arquitetura/` — documentação da Arquitetura da Aplicação.
- `api/` — documentação da API quando a etapa correspondente for iniciada.
- `interface-web/` — documentação da Interface Web.
- `testes/` — estratégia, casos e resultados de testes.
- `governanca/` — proveniência, inventário, consolidação e controle documental.

## Regra para futura implementação

Esta árvore contém **documentação**, não a arquitetura física do código.

Diretórios de implementação como `src/`, `app/`, `backend/`, `frontend/`, `database/`, `config/` ou equivalentes somente deverão ser criados quando a **Etapa 9 — Arquitetura da Aplicação** definir formalmente a estrutura técnica do software.

Até esse momento, a existência de documentação sobre API, interface ou banco de dados não deve ser interpretada como autorização para antecipar sua implementação.

## Governança

Antes de realizar alterações automatizadas ou multiagente, consultar:

1. `../ORCHESTRATOR.md`
2. `../project-manifest.yaml`
3. `governanca/relatorio-de-consolidacao.md`
