# Documentação do SGFP

Este diretório concentra os artefatos documentais do **SGFP — Sistema de Gestão Financeira Pessoal**.

A numeração e o estado das etapas são governados por `project-manifest.yaml`, `ORCHESTRATOR.md` e pela baseline vigente.

## Baseline vigente

A decisão consolidada em **14/09/2026** está em:

- `governanca/baseline-v1-simplificada-2026-09-14.md`

Resumo:
- uma Conta Financeira por usuário, criada como **Minha Conta**;
- saldo derivado dos Lançamentos ativos;
- Transferências e Patrimônio Total fora da V1;
- backup local em ZIP;
- restauração integral com cópia pré-restauração;
- reset do perfil preservando login;
- exclusão da conta de acesso removendo dados + login.

## Estrutura

- `projeto/` — Documento de Visão, Plano e roteiro técnico.
- `requisitos/` — Levantamento de Requisitos e SRS.
- `casos-de-uso/` — catálogo, atores, rastreabilidade e UCs.
- `dominio/` — Mapa do Domínio vigente.
- `modelagem-dados/` — MER, DER, Modelo Físico e artefatos.
- `arquitetura/` — arquitetura da aplicação revisada.
- `api/` — documentação da API e reconciliação requerida.
- `interface-web/` — documentação da Etapa 11 em andamento.
- `testes/` — preparação e futura consolidação formal da Etapa 12.
- `governanca/` — baseline, continuidade, proveniência, inventário e controle documental.

## Estado de desenvolvimento

- Etapas 5–9: documentação revisada para a baseline de 14/09/2026.
- Etapa 10: implementação existente, construída sobre baseline anterior; requer migração/reconciliação.
- Etapa 11: em andamento na branch `feat/stage-11-web-interface`; requer reconciliação.
- Etapa 12: futura para consolidação formal dos testes.

A presença de código já implementado não torna a implementação antiga fonte normativa quando divergir da baseline vigente.

## Governança

Antes de alterações automatizadas ou multiagente, consultar:

1. `../AGENTS.md`
2. `../ORCHESTRATOR.md`
3. `../project-manifest.yaml`
4. `governanca/baseline-v1-simplificada-2026-09-14.md`
5. `governanca/PROMPT-RETOMADA.md`

Documentos históricos devem permanecer preservados, mas não prevalecem sobre a baseline vigente.
