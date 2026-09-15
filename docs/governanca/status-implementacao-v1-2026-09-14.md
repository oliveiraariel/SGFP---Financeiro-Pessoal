# SGFP — Status de Implementação da V1 simplificada

**Data de referência:** 14/09/2026  
**Objetivo:** fornecer a agentes e ao OpenClaw uma visão única da relação entre baseline documental, backend reconciliado e frontend em andamento.

## 1. Baseline normativa

A V1 vigente está definida em:

`docs/governanca/baseline-v1-simplificada-2026-09-14.md`

Resumo:

- uma Conta Financeira por usuário;
- provisionamento automático de **Minha Conta**;
- saldo derivado de Lançamentos Financeiros ativos;
- Transferências fora da V1;
- Patrimônio Total fora da V1;
- backup manual local em ZIP;
- restauração integral por ZIP com snapshot pré-restauração;
- Resetar perfil financeiro com `RESETAR PERFIL`;
- Excluir conta de acesso com `EXCLUIR CONTA`;
- 19 RFs ativos no catálogo RF-001..RF-023.

## 2. Trilhas GitHub

### PR #11 — documentação

Branch:

`docs/v1-single-account-local-backup-reset-delete`

Finalidade:

- formalizar e harmonizar a nova baseline;
- atualizar requisitos, UCs, domínio, modelagem, arquitetura e governança;
- substituir referências normativas incompatíveis com a V1 simplificada;
- manter histórico antigo explicitamente classificado como histórico.

### PR #12 — backend Etapas 9–10

Branch:

`feat/stage-9-10-backend`

Base:

`feat/stage-11-web-interface`

Finalidade:

- reconciliar a implementação da API/backend com a baseline do PR #11;
- manter a branch 9–10 aberta para ajustes legítimos encontrados durante a Etapa 11.

Documento técnico detalhado nessa branch:

`docs/governanca/handoff-reconciliacao-etapas-9-10-v1-2026-09-14.md`

## 3. Estado da Etapa 10

A Etapa 10 foi reconciliada tecnicamente com a V1 simplificada.

Implementado:

- conta única;
- provisionamento automático;
- Modelo Físico V8;
- migração de schema 1.2.0;
- retirada de Transferências;
- retirada de net-worth;
- backup ZIP V2;
- restauração ZIP;
- snapshot pré-restauração;
- reset do perfil;
- exclusão da identidade WordPress.

Validação executada:

- Composer install: PASS;
- PHP lint: PASS;
- PHPUnit: **40 testes / 92 assertions / 0 erros**;
- WordPress + MySQL 8:
  - `[PASS] clean-v8`;
  - `[PASS] migrate-v7-to-v8`;
  - `[PASS] lifecycle`;
  - `[PASS] backup-restore`.

Defeitos reais encontrados pelo gate e corrigidos:

- ciclo de vida do `ZipArchive`;
- imports ausentes dos services de recorrência em `Routes.php`.

## 4. Estado da Etapa 11

A Etapa 11 está em andamento na branch:

`feat/stage-11-web-interface`

Ela ainda deve ser reconciliada com:

1. a baseline documental do PR #11;
2. o backend atualizado do PR #12.

Alvos principais da reconciliação:

- remover fluxos de múltiplas contas;
- remover Principal/Secundária;
- remover Transferências;
- remover Patrimônio Total;
- adaptar Dashboard à conta única;
- usar backup ZIP local;
- implementar UI de restauração compatível;
- implementar UI de Resetar perfil;
- implementar UI de Excluir conta;
- alinhar chamadas REST aos contratos atuais.

## 5. Relação operacional entre 9–10 e 11

A branch `feat/stage-9-10-backend` permanece aberta deliberadamente.

Se a Etapa 11 revelar necessidade legítima de backend:

1. registrar a necessidade;
2. verificar se é requisito já previsto ou nova decisão humana necessária;
3. alterar a linha 9–10;
4. testar;
5. documentar;
6. reintegrar a alteração à Etapa 11.

Não corrigir silenciosamente o frontend criando contratos divergentes da API.

## 6. Working tree local

Há histórico recente de trabalho local modificado/não rastreado na Etapa 11.

Antes de:

- pull;
- merge;
- rebase;
- troca de branch;
- aplicação de PR;

o OpenClaw deve confirmar:

`git status`

e preservar o WIP existente.

É proibido usar `reset --hard`, `clean` ou outro procedimento destrutivo para “facilitar” a integração sem autorização humana explícita.

## 7. Ordem recomendada de leitura para OpenClaw

Ao retomar o projeto:

1. `AGENTS.md`;
2. `ORCHESTRATOR.md`;
3. `project-manifest.yaml`;
4. `docs/governanca/baseline-v1-simplificada-2026-09-14.md`;
5. este documento;
6. Draft PR #11;
7. Draft PR #12;
8. handoff técnico da branch 9–10;
9. documentos da Etapa 11 relevantes à tarefa;
10. código somente após confirmar branch/HEAD/working tree.

## 8. Próximo objetivo

O próximo objetivo é **reconciliar a Etapa 11/frontend**, preservando o trabalho local existente.

Não é objetivo:

- reintroduzir múltiplas contas;
- reintroduzir Transferências;
- reintroduzir Patrimônio Total;
- voltar a backup por e-mail;
- redesenhar o backend sem necessidade concreta.

## 9. Pendências conhecidas

- sincronizar `composer.lock` com a declaração de `ext-zip` em `composer.json`;
- validar opcionalmente MariaDB em gate específico;
- validar ambiente local Linux Mint/WordPress/Apache/PHP;
- executar testes visuais/browser/UX da Etapa 11;
- resolver `ISSUE-008` antes do fechamento formal da rastreabilidade da Etapa 12.

Este documento registra estado operacional, não substitui requisitos ou regras de negócio canônicas.
