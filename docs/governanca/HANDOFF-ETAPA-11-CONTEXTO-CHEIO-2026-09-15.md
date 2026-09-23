# Handoff de contexto completo — SGFP Etapa 11 + recuperação Adaptive

**Data:** 2026-09-15  
**Escopo deste documento:** reconstrução de estado somente leitura. Nenhuma implementação, correção de código, nova orquestração, aplicação de stash, commit ou merge foi realizada nesta sessão.

## 1. Estado atual e proveniência

| Repositório | Caminho | Branch | HEAD observado | Estado |
|---|---|---|---|---|
| Adaptive | `/home/ariel/Área de trabalho/VSCode/Git/adaptive-ai-orchestrator` | `main` | `52c2097e2e86be7b422b853d07f2fe15b04e6e14` | limpo |
| Bridge | `/home/ariel/Área de trabalho/VSCode/Git/ariel-agent-skills` | `main` | `abceac4aef23e744a1d9413f0763f5c91e600d03` | somente `__pycache__` não rastreado preservado |
| SGFP | `/home/ariel/Área de trabalho/VSCode/Git/SGFP-orchestrator-core` | `feat/stage-11-web-interface` | `4ff7168832dff76a659bba9c886a9e19371b960a` histórico/base | WIP não commitado válido |

Python Adaptive esperado/validado: `/home/ariel/Área de trabalho/VSCode/Git/adaptive-ai-orchestrator/.venv/bin/python`.

O HEAD SGFP continua no histórico porque as mudanças da Etapa 11 foram preservadas como WIP, não porque tenham sido descartadas. Nenhum resultado produzido em `master@406130e` é autoritativo para implementação.

## 2. Preservação obrigatória

- Stash SGFP: `stash@{0}: On feat/stage-11-web-interface: backup-wip-antes-reconciliacao-stage11-2026-09-15`.
- Backup externo: `/home/ariel/backup-sgfp-frontend-2026-09-15` (presente).
- Stash Adaptive: `stash@{0}: On main: backup-wip-before-planner-contract-sync-2026-09-15`.
- Stash Bridge: `stash@{0}: On fix/adaptive-worktree-runtime-resolution: safety-wip-bridge-runtime-preflight-2026-09-15`.
- Preservar também `.adaptive/`, result stores, manifests, observability e relatórios existentes.

Não usar `git reset --hard`, `git clean`, `git stash pop` ou `git stash apply` global. Não apagar o backup externo, stashes ou `__pycache__` preservado.

## 3. Orquestrações reconstruídas

### Orquestração 1 — `0a264877c1de46b39a48060a7cfdb828`

Execução inicial da Etapa 11. Planner produziu 8 WUs, paralelismo máximo 1, `replan_count=0`. Todas foram operacionalmente `COMPLETED/ACCEPTED` pelo executor, porém a revisão/fan-in (`u8`) retornou **REQUEST CHANGES**. Não houve WU bloqueada no estado terminal do run. O resultado não autorizou merge.

| WU | Escopo | Resultado |
|---|---|---|
| u1 | descoberta, baseline e proveniência | ACCEPTED; `.../u1/54e3cc1999ec46cb87378d8b04b067d4/manifest.json` |
| u2 | análise de contratos REST | ACCEPTED; `.../u2/41d05e75f1b847eda114479caec52b05/manifest.json` |
| u3 | conta, saldo inicial e effectuation | ACCEPTED; `.../u3/64bbfd.../manifest.json` |
| u4 | consistência de mês | ACCEPTED; `.../u4/b3d1.../manifest.json` |
| u5 | categorias, ZIP e valores textuais | ACCEPTED; `.../u5/9d4.../manifest.json` |
| u6 | `app.js`, `app.css`, `Frontend.php` | ACCEPTED; `.../u6/bfaae.../manifest.json` |
| u7 | verificação local | ACCEPTED; PHP lint, PHPUnit 43/106, Node e diff check; WP/MySQL indisponíveis |
| u8 | revisão/fan-in | execução aceita, veredicto REQUEST CHANGES; `.../u8/d320.../manifest.json` |

Gaps apontados: CRUD de compromissos incompleto, consistência de mês/envelopes, autorização/ownership, fluxos destrutivos, acessibilidade e integração real.

### Orquestração 2 — `4b33e0b417294eda90a570ddf71ae250`

Passagem corretiva. Planner produziu 12 WUs, paralelismo máximo 2, `replan_count=0`, status `PARTIAL`, `ok=false`; circuit breaker em workers parciais. Aceitos: decisão/contrato, backup/destructive flows, acessibilidade e pesquisa de harness. Bloqueados/returned após duas tentativas: `backend-commitments`, `backend-routes-account-contract`, `frontend-api-reconciliation`. Permaneceram WAITING: revisão web, fan-in, integração/E2E, segurança e verificação.

Result refs relevantes: `backend-commitments/53eaf...` e `9ecb...`; `backend-routes-account-contract/b4e8...` e `29f68...`; `backend-backup-restore-and-destructive-flows/d0fba...`; `integration-harness-research/f063...`; `frontend-api/db9f...` e `1a1c...`; `frontend-accessibility-behavior/637f...`.

O primeiro worker de decisão reportou `master@406130e`; esse resultado é **proveniência inválida** e não deve ser reutilizado.

### Orquestração 3 — `fdff7555767441a8877aefbe9c0ddfa0`

Retomada delta, sem reiniciar WUs aceitas. Planner produziu 12 WUs, paralelismo máximo 1, `replan_count=0`, status `PARTIAL`, `ok=false`, `requires_human_decision=true`, stop reason `circuit-breaker:max-attempts:worker-partial`.

| WU | Role | Attempt/Wave | Estado | Verdict | Dependências | Resultado reutilizável | Motivo |
|---|---|---|---|---|---|---|---|
| decision-contract | decisão/contrato | retry | COMPLETED | ACCEPTED | baseline | sim, segunda tentativa | primeira evidência `master@406130e` inválida; revalidada no SGFP correto |
| backend-account-rest | backend REST | 1 | COMPLETED | ACCEPTED | decisão | sim | conta singular, saldo derivado, saldo inicial; 3 testes/7 assertions |
| backend-destructive-backup | backend destrutivo/backup | 1 | COMPLETED | ACCEPTED | decisão | sim | dashboard/mês, RFC3339 e remoção de `/settle`; 4 testes/9 assertions |
| backend-commitments | backend/API | 2 tentativas | BLOCKED/RETURNED | REQUEST CHANGES | contrato | parcial, não suficiente | GET coleção/item e PATCH adicionados; DELETE não implementado por falta de wiring/repository; float persiste |
| frontend-api | integração frontend/API | 2 tentativas | BLOCKED/RETURNED | REQUEST CHANGES | contratos backend | parcial | rotas/mês/decimal/recorrências ajustados; envelopes, validações e integração real pendentes |
| frontend-accessibility | acessibilidade | WAITING | não iniciado | — | frontend-api | não | predecessor bloqueado |
| security-review | segurança | WAITING | não iniciado | — | backend | não | predecessor/gate não liberado |
| verification-local | verificação | WAITING | não iniciado | — | implementações | não | predecessor bloqueado |
| web-ui-review | revisão web | WAITING | não iniciado | — | frontend | não | gate não liberado |
| integration-e2e | integração/E2E | WAITING | não iniciado | — | backend/frontend/ambiente | não | ambiente e predecessores |
| code-review | revisão de código | WAITING | não iniciado | — | diff completo | não | aguardava implementação |
| final-fan-in | consolidação | WAITING | não iniciado | — | todos os gates | não | não havia fan-in aprovável |

`backend-commitments` e `frontend-api` seguiram `partial → retry → partial → circuit breaker → BLOCKED`; as WUs dependentes permaneceram WAITING. Nenhuma WU gerada foi considerada silenciosamente concluída.

## 4. WIP SGFP atual

O `git status --short` atual contém os seguintes arquivos:

**Backend REST/services/domínio:**
`CreateBackupService.php`, `CreateCommitmentService.php`, `GetDashboardService.php`, `ListMovementsService.php`, `MaterializeRecurrenceOccurrenceService.php`, `SettleCommitmentService.php`, `UndoCommitmentSettlementService.php`, `UndoRecurrenceOccurrenceSettlementService.php`, `AccountController.php`, `BackupController.php`, `CategoryController.php`, `CommitmentController.php`, `ReportingController.php`, `CreateCommitmentRequest.php`, `SetInitialBalanceRequest.php`, `Routes.php`.

**Novos arquivos backend:** `ListCommitmentsService.php`, `UpdateCommitmentService.php`, `UpdateCommitmentRequest.php`.

**Testes:** `CreateCommitmentServiceTest.php` modificado.

**Frontend/documentação não rastreados:** `src/assets/` (inclui `app.js` e `app.css`), `src/src/Frontend/`, `docs/governanca/HANDOFF-RETOMADA-ETAPA-11.md` e `docs/governanca/relatorio-fan-in-etapa-11-2026-09-15.md`.

Classificação: backend REST/services/DTOs = contratos de conta, compromissos, categorias, dashboard, movimentos, effectuation, backup e meses; frontend = reconciliação parcial da shell V1; teste = cobertura pontual; documentação = artefatos de continuidade/fan-in. O WIP é recuperável, mas não está integralmente aceito: somente os resultados explicitamente aceitos acima podem ser reutilizados sem nova verificação.

## 5. Baseline V1 autoritativa

- Exatamente uma conta por usuário, criada automaticamente como **Minha Conta**.
- Sem contas múltiplas, Principal/Secundária, `AccountRole`, transferências ou patrimônio líquido.
- Saldo derivado exclusivamente de lançamentos financeiros ativos; não há saldo armazenado autoritativo.
- Compromisso criado nasce pendente e não altera saldo; effectuation cria lançamento ativo; undo desfaz o efeito e restaura o saldo.
- Categoria é opcional; categorias-base são provisionadas de modo idempotente e categoria customizada não pode desaparecer.
- Backup manual local em ZIP; restore validado com snapshot pré-restauração recuperável.
- `RESETAR PERFIL` apaga dados SGFP, preserva login WordPress e reprovisiona estado inicial.
- `EXCLUIR CONTA` remove dados SGFP antes da identidade WordPress; falha posterior deve ser explícita e retryável.

Fonte normativa principal: `docs/governanca/baseline-v1-simplificada-2026-09-14.md`; complementar: `docs/governanca/status-implementacao-v1-2026-09-14.md`, `docs/governanca/continuidade-de-contexto.md`, `docs/governanca/HANDOFF-RETOMADA-ETAPA-11.md`, arquitetura/API, Routes, controllers, services, domínio, migrations e testes.

## 6. Pendências técnicas verificadas

1. **Commitment CRUD:** listagem/item/PATCH parcialmente adicionados; DELETE e wiring/repository ainda pendentes; contrato final frontend/backend não provado.
2. **Envelopes REST:** respostas ainda heterogêneas e há divergências entre frontend e API.
3. **Conta/saldo:** caminho de saldo derivado foi implementado localmente, mas ownership real e ponta a ponta ainda não foram provados.
4. **Decimal:** fronteira REST melhorada, porém domínio/persistência ainda contêm float; precisão exata ponta a ponta pendente.
5. **Autorização/ownership:** capability, isolamento entre usuários, IDs inválidos e idempotência precisam de testes reais/abrangentes.
6. **Reset/delete:** fluxos parciais; confirmação, transação, reprovisionamento, falha de remoção WP e retry precisam validação completa.
7. **Backup/restore:** resposta ZIP foi melhorada; validação, snapshot, transação, invariantes e restore completo não foram provados E2E.
8. **Frontend/API:** `app.js` foi parcialmente reconciliado, mas mapping completo, envelopes, erros e operações destrutivas ainda exigem auditoria.
9. **Anti-regression:** scan final de código ativo ainda pendente.

## 7. Ambiente e bloqueios

Trabalho tecnicamente executável sem WordPress deve continuar: contratos, DTOs, serviços, testes unitários, ownership simulado, decimal, ZIP/restore determinístico, mapping JS, lint e documentação.

Última investigação indicou PHP 8.3.6, Composer 2.7.1, PHPUnit 10.5.64, PHPStan 1.12.34, sodium/zip/PDO disponíveis; WordPress, WP-CLI, Docker/Podman e clientes/servidor MySQL/MariaDB não disponíveis. Browser automation não configurada e harness E2E não criado. Isso é blocker ambiental concreto apenas para E2E WordPress/MySQL/browser, não justificativa para interromper trabalho local independente. Não instalar componentes via sudo sem nova autorização.

## 8. Hipótese Adaptive: parada e recuperação

Tratar como hipótese a investigar, **não como implementação aprovada**:

- `max_attempts_per_work_unit` conhecido: 2.
- `REVISION_REQUIRED` que atinge o limite pode virar `BLOCKED`.
- `BLOCKED` deixa de participar de `_unfinished()`, dependentes ficam WAITING e o scheduler termina `PARTIAL` sem active/ready.
- O circuit breaker deveria interromper a estratégia/worker atual, não o objetivo inteiro. Estratégias distintas, recovery supervisor ou decomposição devem preceder BLOCKED terminal.
- Worker vivo (heartbeat/status/progresso) deve ser considerado ALIVE; silêncio exige reconcile same-run, confirmação, cancel best-effort, fence/revoke e replacement.
- Após estratégia A esgotada, estratégia B deve receber objetivo original, WIP, resultados e feedback, podendo trocar skill/model/provider.
- BLOCKED terminal deve ficar reservado a decisão humana, autoridade/credencial, dependência externa real, produção, ambiente insuperável ou orçamento global esgotado após estratégias distintas.

### Fencing e liveness propostas

Usar geração/autoridade por WU: generation 1 revogada torna resultado `STALE_EXECUTION`; generation 2 passa a authoritative. Futuramente, worktrees isoladas por worker/generation reduzem conflitos de escrita. Estados atuais conhecidos: `SUBMITTED`, `RUNNING`, `SUSPECT`, `COMPLETED`, `FAILED`, `CANCELLED`; evolução proposta: `SUSPECT → reconcile → confirmar silêncio → fence/cancel → replacement`.

## 9. Conhecimento e integrações já existentes

O Adaptive main já contém conhecimento promovido: `prove-runtime-provenance-before-dependency-repair`, `use-explicit-plan-only-for-planner-validation`, `trace-machine-result-before-retrying`, `separate-control-plane-from-result-plane`, `reconcile-same-run-before-redispatch`, `validate-transport-with-staged-payloads`, `recover-authenticated-run-before-redispatch`, `resolve-blockers-before-implementation` e `simplify-after-structured-planning-failure`.

Integrações relevantes: Adaptive PR #43 (structured planner contract), Bridge PR #15 (runtime preflight), Adaptive PR #44 (plan-only) e Adaptive PR #45 (knowledge promotion).

## 10. Próxima sessão recomendada

1. Inspecionar primeiro os manifests/result stores de `fdff7555767441a8877aefbe9c0ddfa0` e confirmar a cadeia `RETURNED → retry → circuit breaker → BLOCKED` para `backend-commitments` e `frontend-api`.
2. Comparar o comportamento observado com a política de recovery acima; não redispatchar cegamente nem reiniciar a Etapa 11 inteira.
3. Corrigir/testar o mecanismo Adaptive de strategy exhaustion, replacement, recovery e fencing, se a investigação confirmar a hipótese.
4. Reproduzir o caso em testes do Adaptive.
5. Validar recovery controlado.
6. Só então retomar WUs técnicas restantes no checkout SGFP correto, começando por contratos REST/CRUD e depois gates de segurança, verificação, UI, integração e fan-in.

## 11. Não repetir

Não usar evidência `master@406130e`; não aplicar stash inteiro; não restaurar frontend antigo; não reintroduzir funcionalidades removidas da V1; não executar fallback direto sem autorização humana explícita; não declarar WU concluída sem teste/evidência; não tratar ausência de E2E ambiental como bloqueio de todo o trabalho; não declarar Stage 11 pronta para merge enquanto fan-in não for APPROVE.

**Estado de encerramento desta sessão:** handoff criado; código funcional não alterado; nenhum commit criado; nenhum stash aplicado; nenhuma nova orquestração iniciada.
