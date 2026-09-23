# SGFP — Checkpoint autoritativo de continuidade — 2026-09-22

## CURRENT AUTHORITATIVE HANDOFF — Etapa 11 frontend WIP — 2026-09-22 (America/Sao_Paulo)

- **Estado:** Projeto SGFP Etapa 11 Frontend em andamento. Branch `feat/stage-11-web-interface`; HEAD `4ff7168832dff76a659bba9c886a9e19371b960a`. O working tree WIP do usuário deve ser preservado: não executar reset, clean, checkout destrutivo, stash ou commit.
- **Checks locais mais recentes:** PHPUnit PASS (`168 testes / 525 assertions / 0 falhas / 0 erros / 0 skipped`); PHP lint PASS (`128` arquivos sob `src/src`); `node --check src/assets/app.js` PASS; `composer validate --no-check-publish` PASS; `git diff --check` PASS. Também foram executados o round-trip de `BackupProtector` sem `SGFP_BACKUP_KEY` usando uma salt WordPress simulada e o probe do hook `rest_pre_serve_request` que emite bytes binários somente no sucesso. Resta somente a confirmação ambiental/integrada: executar a matriz REST/browser em WordPress + MySQL/MariaDB, incluindo confirmação visual/acessibilidade responsiva; essa confirmação não é executável neste host.
- **Artefato para teste atual:** [`release/sgfp-etapa-11-test-20260922T213740Z.zip`](release/sgfp-etapa-11-test-20260922T213740Z.zip), `123034` bytes, SHA-256 `af6014c8a4183bd0f8527fed58b56759a904574889bbaca40e7008c9141f7ef9`. É derivado do WIP consolidado até DEF-016, do fluxo de entrada sem landing e inclui DEF-013, DEF-014, DEF-015 e o aviso de saldo inicial do primeiro acesso. Os ZIPs anteriores foram preservados sem alteração.
- **Execuções:** orquestração histórica/terminal PARTIAL `25661d19a7f84ff390bbab47adf505e0` (44 WUs, 35 concluídas, 13 `PRACTICAL_TEST_READY`, 9 bloqueadas no checkpoint antigo) não deve ser retomada. Execução frontend `4d392786f6f24046b7fe747f603865b4` concluiu `WU-COMP-02`, `WU-COMP-03`, `WU-REC-02`, `WU-VG-02`, `WU-VG-03`, `WU-VG-04` e `WU-VG-06`; não repetir.
- **Execuções de defeitos preservadas:** `44b9873d70d04f5f8b9471b8c912193e` está `PAUSED + QUIESCENT`, `active_execution_count=0`, `mutation_authority_released=true`, `pending_replan=false`, `replan_count=5`, 11 WUs, sem workers. A sucessora pausada `5ad5177f76bb4bc79ffbb9a648c3497f` também está `PAUSED + QUIESCENT`, `active_execution_count=0`, com apenas `WU-RECOVERY-READONLY-20260922` concluída e sete WUs inacabadas. Nenhuma delas deve ser retomada ou duplicada sem nova decisão governada.
- **Aceitas/concluídas:** `WU-ETAPA11-AUDIT-WIP-CONTRACT`, `WU-ETAPA11-DECISION-CONTRACT-RECONCILIATION`, `WU-ETAPA11-DEF001-COMMITMENT-CRUD`, `WU-ETAPA11-DEF004-MOVEMENTS-CONTRACT`, `WU-ETAPA11-DEF005-DASHBOARD-BALANCES`, `WU-ETAPA11-DEF006-BACKUP-ZIP`, `WU-ETAPA11-DEFECT-REGISTER`, `WU-ETAPA11-FRONTEND-DEF002-003-004-005-007` e `WU-ETAPA11-REST-ERROR-CONTRACT-NORMALIZATION`.
- **Inacabadas no Adaptive (não retomar automaticamente):** em `44b...`, `WU-ETAPA11-FANIN-REVIEW-ALL-DEFECTS` e `WU-ETAPA11-REST-ERROR-CONTRACT-HEAD-RECONCILIATION`; em `5ad...`, as sete WUs de correção/validação/release. O fallback direto abaixo é evidência separada e não altera esses checkpoints.
- **Contrato REST corrigido localmente:** as duas closures de conta usam `PublicError::fromCode(404, 'NOT_FOUND')`; os catches inesperados dos controllers alcançados vinculam `$e`; testes cobrem envelope, mensagem pública e correlation ID. O achado HIGH anterior está remediado no WIP atual, mas REST hospedado ainda não foi retestado.
- **Registro documental corrigido:** [`docs/governanca/defect-register-etapa-11.md`](docs/governanca/defect-register-etapa-11.md) agora mapeia fielmente: #1 CRUD Compromissos `INTERNAL_ERROR`; #2 texto dos inputs preto; #3 máscara automática `dd/mm/aaaa`; #4 lançamentos `d.map` e apenas efetivados; #5 saldos inicial/final da Visão Geral; #6 ZIP de backup; #7 troca de tema confiável.
- **Reteste manual — DEF-002 corrigido:** o owner esclareceu que o defeito está no **tema escuro**: campos brancos exibiam caracteres cinza-claro. O registro anterior inverteu o tema afetado. O fallback direto agora força fundo branco e preto explícito (`color`, `caret-color` e `-webkit-text-fill-color` `#000`) nos inputs, selects/opções, campo monetário e seletor de Configurações do tema escuro; tema claro continua preto. Retestar a instalação que contenha este WIP antes de considerar o defeito fechado.
- **DEF-008 — exclusão de pendente:** a agenda omitia a ação de excluir embora `DELETE /commitments/{id}` e `DeleteCommitmentService` já existissem e aceitassem apenas estado `PENDENTE`. O fallback adicionou `Excluir` com método `DELETE` somente ao item não efetivado; item efetivado continua sem exclusão, exigindo desfazer a efetivação antes. Regressões focadas e suíte completa PASS (`157/476`); reteste REST/browser pendente.
- **DEF-009 — resetar perfil:** a UI solicitava a frase, mas não chamava `POST /profile-reset/validation` nem enviava o token obrigatório para `POST /profile-reset`; `ResetProfileService` rejeitava corretamente a operação. O fallback agora obtém o token ao iniciar o reset, o conserva em campo oculto e o transmite somente após a frase exata. Regressão focada, serviço e suíte completa PASS (`158/479`); reteste REST/browser pendente.
- **DEF-010 — Visão Geral:** o Dashboard já calculava em centavos o saldo final do mês anterior (`opening_balance`), os movimentos efetivados no mês (`realized_*`), o saldo final realizado (`current_balance`) e o previsto (`expected_closing_balance`). A UI foi recomposta para mostrar Previsto primeiro (`Mês anterior`, entradas/saídas/saldo previstos) e Realizado depois (entradas/saídas realizadas e `Saldo final`), sem mudar os cálculos canônicos. Regressões e suíte completa PASS (`158/481`); reteste browser pendente.
- **DEF-011 — densidade dos cartões do Dashboard:** as caixas de texto de Previsto/Realizado tinham padding vertical excessivo. A regra específica reduz somente o padding vertical de `16px` para `9px` e o gap interno de `8px` para `5px`; fontes, cores, valores e reflow foram preservados. Regressão e suíte completa PASS (`159/483`); reteste browser pendente.
- **DEF-012 — download de backup:** o backend já fornecia o ZIP binário e os headers de download, porém o cliente clicava uma âncora destacada do DOM e não liberava a object URL. O fallback usa agora o filename em `Content-Disposition` quando disponível, anexa a âncora temporária ao DOM, aciona o download, remove a âncora e revoga a URL. Regressão focada e suíte completa PASS (`160/489`); reteste REST/browser com dados reais pendente.
- **Fluxo de entrada sem landing intermediária:** o ponto que renderizava “Entre para acessar o gestor financeiro” era `Frontend::render()`. O fallback direto registrado cria o hook público `template_redirect` com prioridade `0`: visitante anônimo na página singular que contém `[sgfp_app]` recebe `wp_safe_redirect(wp_login_url(get_permalink()))` e a execução é encerrada; autenticado não redireciona e renderiza o frontend atual. Admin, AJAX, cron, JSON/REST, páginas não singulares e páginas sem shortcode são explicitamente ignorados. A renderização anônima passa a ser vazia como salvaguarda, sem landing. Regressões em `AuthFrontendTest` e suíte completa PASS (`163/498`); reteste WordPress/browser pendente.
- **DEF-013 — seletor global de mês:** no tema escuro, o input `month` do cabeçalho permanecia com caracteres cinza-claro porque `.sgfp-month` não recebia o override aplicado aos campos de formulário. O fallback torna o controle branco com texto, cursor e preenchimento WebKit pretos, além de `color-scheme:light` para o controle nativo. Regressão e suíte completa PASS (`164/500`); reteste browser pendente.
- **DEF-014 — tema inicial escuro:** `ThemeService` devolvia `light` sem meta de preferência, e os fallbacks do cliente/backup repetiam esse valor. O padrão canônico passou a `dark`; o tema claro é preservado somente quando está explícito na preferência existente ou na restauração. Regressões e suíte completa PASS (`165/505`); reteste WordPress/browser de novo usuário pendente.
- **DEF-015 — backup não gerado:** a ausência de `SGFP_BACKUP_KEY` fazia `BackupProtector` abortar a criação; além disso, uma resposta REST comum seria serializada pelo WordPress como JSON, alterando os bytes do ZIP. O fallback conserva a chave explícita quando presente e, sem ela, usa a salt `auth` privada do WordPress; o sucesso de `POST /sgfp/v1/backups` é servido no hook final `rest_pre_serve_request`, após status/headers, para ignorar somente o encoder JSON. Falhas continuam no envelope público JSON e a UI passou a mostrar esse envelope no lugar da mensagem genérica. Regressões, round-trip do fallback e probe de streaming PASS; suíte completa (`167/515`) PASS. Reteste WordPress/browser sem variável de ambiente pendente.
- **DEF-016 — saldo inicial no primeiro acesso:** o contrato `GET /account` já expunha `initial_balance_configured`, mas o frontend não orientava o perfil novo. Após a primeira carga, ele consulta esse estado e mostra uma mensagem única por conta/navegador somente se não houver saldo inicial; o botão abre **Minha Conta**. Nesse fluxo o saldo é obrigatório, o mês/ano atual é pré-preenchido e somente leitura, e a submissão rejeita período diferente antes de chamar `POST /account/initial-balance`. A conta passa a configurada pela própria regra existente ao salvar o lançamento inicial. Regressão e suíte completa (`168/525`) PASS; reteste WordPress/browser pendente.
- **Defeitos adicionais confirmados e corrigidos no fallback:** DEF-004 ainda usava `d.map(...)` sobre `{items}`; agora usa `list(d).map(...)`. DEF-005 calculava dashboard, mas renderizava somente a agenda; agora compõe `dashboard(d) + agenda(...)`. Ambos possuem regressões focadas.
- **Fan-in/review atual:** [`docs/governanca/relatorio-fan-in-fallback-direto-etapa-11-2026-09-22.md`](docs/governanca/relatorio-fan-in-fallback-direto-etapa-11-2026-09-22.md) registra `APPROVE WITH NOTES` somente para a matriz local. A revisão foi feita pelo owner do fallback e não substitui review independente nem ambiente real.
- **Gate ambiental / release:** BLOQUEADO. Este host não possui `wp`, MySQL/MariaDB, Docker/Podman, servidor WordPress, portas web/banco ativas ou harness E2E. Zero cenários ambientais foram executados. O ZIP de teste acima foi produzido por autorização explícita, mas não deve ser tratado como release aprovada.
- **Verificação do novo ZIP:** produção montada em árvore temporária isolada com `composer install --no-dev --optimize-autoloader`; raiz única `sgfp/`; `src/Plugin.php` presente sem `src/src/Plugin.php`; `src/Tests/`, `vendor/bin/`, `composer.json` e `composer.lock` ausentes. `unzip -t`, lint de `109` PHPs empacotados, `class_exists('SGFP\\Plugin')`, bootstrap isolado de `SGFP\Plugin::boot()` e `git diff --check` PASS.
- **Próxima ação segura:** instalar o ZIP atual e testar em WordPress/browser: DEF-016 em usuário novo (aviso único → **Minha Conta** → saldo obrigatório no mês atual → conta configurada); DEF-013 no seletor global de mês; DEF-014 no primeiro login; e DEF-015 com **`SGFP_BACKUP_KEY` ausente** (`POST /backups` deve retornar `200`, headers de download e bytes iniciando em `PK`, seguido de `unzip -t`, SHA e restauração). Não retomar `25661`, não repetir WUs aceitas e preservar todo o WIP.

### Fallback direto controlado — 2026-09-22

- **Autorização humana:** executar diretamente o trabalho pendente após a troca temporária do modelo LLM.
- **Evidência de elegibilidade:** `5ad...` e `44b...` confirmadas `PAUSED + QUIESCENT`, zero execuções ativas e autoridade de mutação liberada; nenhum Worker foi duplicado.
- **Arquivos alterados pelo fallback:** `src/src/REST/Routes.php`; `src/src/REST/Controllers/{Account,Backup,Restore,Profile,Recurrence}Controller.php`; `src/assets/app.js`; `src/src/Tests/Unit/{PublicErrorTest,RoutesCompositionTest,CommitmentRowActionsTest}.php`; `docs/governanca/defect-register-etapa-11.md`; `docs/governanca/relatorio-fan-in-fallback-direto-etapa-11-2026-09-22.md`; este `HANDOFF.md`.
- **Validações:** PHPUnit completo 156/469 PASS; lint PHP 128 arquivos PASS; JS check PASS; Composer validate PASS; diff check PASS.
- **Resultado:** correções locais, fan-in e empacotamento de teste concluídos; falta somente a confirmação ambiental/integrada dos fluxos REST/browser para eventual promoção a release.
- **Verificações do ZIP de teste:** raiz única; `src/Plugin.php` presente sem `src/src/Plugin.php`; `src/Tests/` e `vendor/bin/` ausentes; lint PHP, `class_exists('SGFP\\Plugin')` e `unzip -t` PASS.
- **Aditamento DEF-015:** fallback direto autorizado foi usado para corrigir a criação/entrega do backup sem retomar qualquer execução Adaptive: `src/src/Application/Backup/BackupProtector.php`, `src/src/REST/Routes.php`, `src/assets/app.js`, `src/src/Tests/Unit/{BackupArchiveTest,CommitmentRowActionsTest,RoutesCompositionTest}.php`, `docs/governanca/defect-register-etapa-11.md` e este `HANDOFF.md`. Evidência local atual: `167/515` PHPUnit, lint/JS/Composer/diff PASS, round-trip pela salt e probe binário REST PASS. Artefato consolidado atual: `release/sgfp-etapa-11-test-20260922T210847Z.zip`.
- **Aditamento DEF-016:** fallback direto autorizado foi usado para incluir o aviso único de saldo inicial por perfil novo em `src/assets/{app.js,app.css}` e a regressão em `src/src/Tests/Unit/CommitmentRowActionsTest.php`; a documentação e este handoff também foram atualizados. Evidência local atual: `168/525` PHPUnit, lint/JS/Composer/diff PASS.
- **Empacotamento após DEF-016:** por solicitação explícita do owner, o WIP atual foi empacotado em `release/sgfp-etapa-11-test-20260922T213740Z.zip` (`123034` bytes; SHA-256 `af6014c8a4183bd0f8527fed58b56759a904574889bbaca40e7008c9141f7ef9`). A árvore isolada usa dependências de produção e passou `unzip -t`, exclusões, lint de 109 PHPs, autoload, bootstrap e `git diff --check`.
- **Estado de entrega:** o ZIP consolidado foi entregue ao owner para teste manual; nenhuma nova execução Adaptive foi retomada, nenhum WIP foi descartado e não há confirmação ambiental registrada ainda.

### Prompt recomendado para a próxima janela

> Retomar do `HANDOFF.md`; preservar WIP; não retomar `25661` nem duplicar WUs aceitas; manter `5ad...`/`44b...` pausadas; instalar o ZIP atual `release/sgfp-etapa-11-test-20260922T213740Z.zip` e, em ambiente WordPress/MySQL/MariaDB, testar a matriz REST/browser `DEF-001..DEF-016`, incluindo fluxo anônimo → login → retorno, tema escuro no primeiro login, aviso de saldo inicial e download/restauração sem `SGFP_BACKUP_KEY`; repetir review no estado efetivamente testado antes de qualquer promoção.

## HISTORICAL / NON-CURRENT — retomada governada e reconciliação — 2026-09-22

### 1. Estado do Adaptive

- **Checkout/runtime:** `/home/ariel/Área de trabalho/VSCode/Git/adaptive-ai-orchestrator`, `main` / `0f427b3981ef7bd6671d5fe1295ab890f6ed7cf9`; runtime efetivo `.venv/bin/python` desse checkout.
- **Mudança global incorporada:** [PR #76](https://github.com/oliveiraariel/adaptive-ai-orchestrator/pull/76), merge `bb41439286adcd8d94033fce16853adc3218d628`, adicionou o control-plane durável `ACTIVE → QUIESCENT`, a CLI `reconcile-work-unit`, decisão auditável `PRACTICAL_TEST_READY`, preservação de `RETURNED`, verificação de manifesto e liberação de dependências.
- **Compatibilidade legada incorporada:** [PR #77](https://github.com/oliveiraariel/adaptive-ai-orchestrator/pull/77), merge `0f427b3981ef7bd6671d5fe1295ab890f6ed7cf9`, separa a identidade lógica de execução Gateway da identidade de publicação do Result Store. A reconciliação valida caminho canônico, manifesto, protocolo, bytes e SHA-256 sem exigir que as duas identidades históricas sejam literalmente iguais.
- **Políticas:** heartbeat não é lease; `PAUSED + active_executions=[]` libera autoridade administrativa como `QUIESCENT`. `PRACTICAL_TEST_READY` satisfaz dependências como `COMPLETED`, mas permanece explicitamente distinguível de aceite normal no audit trail. O watchdog continua a tratar heartbeat isolado como não-progresso.
- **Testes Adaptive:** PR #76: `603 passed`; PR #77: `604 passed`; ambos CI GitHub verdes. Também executados `compileall -q src` e `git diff --check` (PASS). Não há hardcode por SGFP, orchestration ID ou WU no Adaptive.
- **WIP Adaptive:** `docs/architecture/RESULT-STORE-TERMINAL-RECONCILIATION.md` permanece não rastreado e não foi alterado.

### 2. Estado da orquestração SGFP

- **Orchestration ID:** `25661d19a7f84ff390bbab47adf505e0`; Work Graph original preservado, **44 WUs únicas**; migração `sgfp-explicit-wu-normalization-v1` permanece aplicada uma única vez.
- Antes da retomada, `pause-project` oficial registrou `QUIESCENT`, `desired_state=PAUSED`, `active_executions=[]` e `mutation_authority_released=true`; nenhum Result Store, record histórico ou Work Graph foi editado manualmente.
- Após a reconciliação, `resume-project` oficial está **RUNNING** com `controller_control_plane_state=ACTIVE`, `desired_state=RUNNING`, `terminal=false`, `pending_replan=false` e exatamente **um Worker ativo**: `WU-COMP-02`, wave 113, attempt 1, execution `openclaw:gateway:orchestrator:project:25661d19a7f84ff390bbab47adf505e0:wave:113:WU-COMP-02:attempt:PLANNED:strategy:1:attempt-number:1`.
- Política de continuação persistida no resume: `recovery_loop_mode=DISABLED`, `acceptance_mode=PRACTICAL_TEST`. Falhas materiais continuam sem promoção; retornos apenas avaliativos/visuais não devem iniciar Recovery Loop.

### 3. Reconciliações auditáveis

As 13 decisões abaixo são `RETURNED histórico → PRACTICAL_TEST_READY`; cada uma preserva `execution_id`, `result_ref`, manifest, lineage e razão no campo `reconciliation_decisions`. Todas tinham Worker `COMPLETED`, `ADAPTIVE_WORK_STATUS: COMPLETE`, blocker `NONE`, nenhum critério não atendido e manifest final íntegro.

`WU-DATA-01`, `WU-COMP-04`, `WU-REC-01`, `WU-REC-03`, `WU-REC-04`, `WU-VG-01`, `WU-VG-07`, `WU-VG-08`, `WU-VG-12`, `WU-VG-13`, `WU-VG-15`, `WU-VG-16`, `WU-VG-17`.

`WU-DATA-01` desbloqueou `WU-COMP-02`, `WU-COMP-03`, `WU-REC-02`, `WU-VG-02`, `WU-VG-03` e `WU-VG-06`. `WU-REC-04` já satisfeita permanece pré-requisito de `WU-VG-04`; esta ainda aguarda `WU-REC-02`. `WU-VG-07`, anteriormente `BLOCKED` apenas por parada administrativa do strategist, foi reconciliada pelo seu `RETURNED` histórico completo e íntegro.

**Não reconciliada:** `AGENDA` continua `REVISION_REQUIRED/RETURNED`: Worker `PARTIAL`, blocker `ENVIRONMENT`, critério objetivo pendente — matriz completa VG-01..20 e validação WordPress/MySQL/MariaDB/HTTP/browser-UX. `VERIFY-STAGE11` continua dependente de `AGENDA`.

### 4. Trabalho restante e fronteira

- Em execução: `WU-COMP-02`.
- Prontas e independentes atrás da mesma fronteira de dados: `WU-COMP-03`, `WU-REC-02`, `WU-VG-02`, `WU-VG-03`, `WU-VG-06` (a serialização atual é causada por `write_paths` conflitantes; a capacidade máxima configurada não implica paralelismo seguro).
- Depois de `WU-REC-02`: `WU-VG-04` torna-se acionável.
- Fan-in pendente: `AGENDA`; portanto `VERIFY-STAGE11` continua não iniciada por dependência real.
- Contagem no instante da retomada: 22 aceitas normais + 13 `PRACTICAL_TEST_READY` = 35 satisfazendo grafo; 1 `REVISION_REQUIRED` material (`AGENDA`); 8 WUs planejadas/downstream, das quais uma está agora em execução.

### 5. SGFP working tree — preservar integralmente

- Branch/HEAD: `feat/stage-11-web-interface` / `4ff7168832dff76a659bba9c886a9e19371b960a`; `origin/feat/stage-11-web-interface` coincide no momento da auditoria inicial.
- WIP existente: 46 arquivos rastreados modificados, 29 entradas não rastreadas, 0 indexados **antes desta atualização de handoff**. Esta atualização de `HANDOFF.md` é a única mutação direta no checkout SGFP nesta sessão.
- Proibido: `reset`, `clean`, stash, checkout/rebase destrutivo ou recriação do Work Graph/migração.

### 6. Pendências e próxima sessão

- **Código/execução:** observar o mesmo controller e a mesma orquestração; não criar sucessora. O scheduler deve continuar a dispatchar WUs acionáveis após cada resultado prático.
- **Teste ambiental/visual:** `AGENDA` e, depois, `VERIFY-STAGE11` exigem a validação material WordPress/MySQL/browser descrita acima; `PRACTICAL_TEST_READY` não substitui esse gate.
- **Blocker real atual:** somente a lacuna objetiva de `AGENDA`; não há blocker de control-plane, Result Store ou Worker na fronteira atual.

### NEXT SESSION

1. Consultar `project-status` da mesma ID e processos/liveness; não inferir estado por conversa.
2. Se ainda `RUNNING`, observar o controller já existente e não emitir segundo `resume-project`.
3. Se `PAUSED + QUIESCENT`, usar somente `reconcile-work-unit`/`resume-project` oficiais; nunca editar o checkpoint ou Result Store.
4. Preservar todos os `RETURNED` históricos e o WIP SGFP. Atualizar este bloco somente com estados canônicos e testes realmente executados.

---

## HISTORICAL / NON-CURRENT — encerramento operacional da orquestração SGFP — 2026-09-21

### Escopo deste handoff

- **Orquestração única (preservada):** `25661d19a7f84ff390bbab47adf505e0`. Não foi criada substituta, não houve `resume-project`, nova migração, redispatch, reinício de controller/supervisor/Worker ou alteração funcional do SGFP nesta reconciliação.
- **Divergência central:** o checkpoint persistido permanece não terminal (`desired_state=RUNNING`, `terminal=false`, `phase=EXECUTION`), mas a execução operacional está parada há longo período: não há controller, supervisor ou Worker como processo ativo. O `RUNNING` de `WU-VG-13` e `active_executions=1` são registros persistidos obsoletos, não prova de execução em curso.
- **Fontes consultadas:** checkpoint, Work Graph/records/attempts, Result Stores/manifests, evidence lineage, liveness e leases, Git/WIP dos dois repositórios. Busca de memória foi somente contextual e não foi usada como evidência de aceite.

### Adaptive AI Orchestrator

- Repositório: `/home/ariel/Área de trabalho/VSCode/Git/adaptive-ai-orchestrator`.
- Branch/HEAD: `main` / `762dc965fb50aaba1cba87cc875760e88aa11085`; o checkout local coincide com `main` no momento da leitura.
- Runtime efetivo: `.venv/bin/python`, importando `adaptive_orchestrator` de `.../adaptive-ai-orchestrator/src/adaptive_orchestrator/__init__.py` (checkout local; não runtime global).
- WIP a preservar: `?? docs/architecture/RESULT-STORE-TERMINAL-RECONCILIATION.md`.
- Correções estruturais presentes no HEAD: PR #69 (`7539414`, migração/normalização), PR #70 (`b0c0bcd`, supervisor/recovery), PR #71 (`346b8ff`, recovery progressivo), PR #72 (`7a3f522`, dispatch independente com recovery pendente), além de `8af801b` (throughput/stall tolerance) e `762dc965` (defaults/functional intent). Nenhuma delas foi exercitada ou reiniciada nesta sessão.

### SGFP Git e WIP

- Branch/HEAD: `feat/stage-11-web-interface` / `4ff7168832dff76a659bba9c886a9e19371b960a`.
- Index: **0** arquivos indexados. Working tree: **46** arquivos rastreados modificados e **29 entradas não rastreadas** no `git status` (inclui diretórios com muitos arquivos e este `HANDOFF.md`); o inventário completo permanece em `git status --porcelain=v1` no checkout e todo o WIP deve ser preservado.
- Principais superfícies WIP: `src/src/`, `src/assets/`, `docs/`, `release/`, novos serviços/DTOs/testes e artefato `work-graph-migration-sgfp-25661-dry-run.json`. Não usar `reset`, `clean`, `stash`, checkout sobrescrevente ou troca de branch para “limpar”.

### Estado persistido e liveness

| Campo | Valor persistido/observado |
|---|---|
| status derivado | `RUNNING` (checkpoint: `desired_state=RUNNING`, `terminal=false`) |
| desired_state / terminal / phase | `RUNNING` / `false` / `EXECUTION` |
| pending_replan / replan_count | `true` / `37` |
| dispatch_generation / waves | `69` / 69 dispatch records |
| concorrência | máximo observado `1`; não há `concurrency_mode`/`max_concurrency` materializados no checkpoint |
| active_executions persistidas | 1: `WU-VG-13`, wave 69; stale operacionalmente |
| controller conhecido | liveness `ACTIVE/RUNNING`, sequência 278, início `2026-09-21 17:56:05 BRT`, último heartbeat `2026-09-21 19:05:21 BRT`; PID não foi persistido nesse artefato e nenhum processo correspondente existe agora |
| supervisor/guardian conhecido | lease PID `2209520`, adquirida `2026-09-21 16:39:47 BRT`, expirada `16:41:47 BRT`; PID ausente |
| último Worker registrado | `WU-VG-13`, execution `openclaw:gateway:orchestrator:project:25661d19a7f84ff390bbab47adf505e0:wave:69:WU-VG-13:attempt:PLANNED:strategy:1:attempt-number:1`; último progresso `19:04:59 BRT`, heartbeat `19:05:29 BRT` |
| liveness atual | **0 controllers ativos, 0 supervisors ativos, 0 Workers ativos** (checagem de processos e PIDs/lease) |

**RUN operacional histórica.** A última janela observável começou com o controller em `2026-09-21 17:56:05 BRT`. A última atividade funcional registrada foi o progresso de `WU-VG-13` às `19:04:59 BRT` (último heartbeat de Worker às `19:05:29 BRT`; controller às `19:05:21 BRT`). Foram produzidos 69 dispatch records, 68 records com execution ID, 75 records de avaliação e trabalho registrado em 28 WUs. Estado final da run: 18 aceitas, 1 em revisão, 9 em recovery, 15 planejadas e 1 `RUNNING` apenas no checkpoint. Não há evento formal de desligamento: **RUN operacional encerrada por ausência prolongada de liveness; checkpoint pode ter permanecido não terminal.**

### Work Graph atual

- **44 `work_unit_id` únicos** — não há WUs adicionais criadas por waves/retries/recovery. A migração `sgfp-explicit-wu-normalization-v1` continua registrada uma única vez; não refazê-la.
- 39 dependências persistidas; a dependência histórica `AGENDA -> VERIFY-STAGE11` permanece. Há 67 `manifest.json`, 69 `result.txt` e 1 `result.txt.tmp` sob `.adaptive/runs/25661d19a7f84ff390bbab47adf505e0/`; publicação de Result Store não equivale a aceite.
- `A/E` abaixo significa **tentativas persistidas / records de execution**. `RS` indica existência de Result Store para a WU; `EL` indica evidence lineage aceita. A última execution é identificada pela última wave; os IDs completos e manifests ficam em `.adaptive/runs/25661d19a7f84ff390bbab47adf505e0/<WU>/` e nos `records` do checkpoint.

| WU — descrição curta | Estado persistido → categoria operacional | Dependências | A/E; última execution/wave | RS/evidência/observação |
|---|---|---|---|---|
| F01-RECON — reconciliação F-01 | COMPLETED → CONCLUÍDA/ACEITA | — | 1/1; W1 | RS; output ref aceito |
| MC-ACCOUNT — conta única | COMPLETED → CONCLUÍDA/ACEITA | F01-RECON | 1/1; W2 | RS; output ref aceito |
| CFG-SETTINGS — tema sem mutação | COMPLETED → CONCLUÍDA/ACEITA | MC-ACCOUNT | 1/1; W3 | RS; output ref aceito |
| DATA-DATE — data completa legada | COMPLETED → CONCLUÍDA/ACEITA | CFG-SETTINGS | 2/2; W5 | RS; output ref aceito |
| REC-RULES — regras legadas de recorrência | COMPLETED → CONCLUÍDA/ACEITA | DATA-DATE | 1/3; W8 | RS; output ref aceito |
| COMP-FORM — formulário legado | COMPLETED → CONCLUÍDA/ACEITA | REC-RULES | 1/1; W9 | RS; output ref aceito |
| AGENDA-RECOVERY-GATE — gate histórico | COMPLETED → CONCLUÍDA/ACEITA | COMP-FORM | 1/1; W14 | RS; output ref aceito |
| WU-MC-01 — título da conta | COMPLETED → CONCLUÍDA/ACEITA | — | 0/1; W18 | sem RS próprio; EL aceita pela migração |
| WU-MC-02 — saldo inicial bloqueado | COMPLETED → CONCLUÍDA/ACEITA | — | 0/1; W18 | sem RS próprio; EL aceita pela migração |
| WU-MC-03 — mês inicial bloqueado | COMPLETED → CONCLUÍDA/ACEITA | — | 0/1; W18 | sem RS próprio; EL aceita pela migração |
| WU-MC-04 — editar saldo/mês | COMPLETED → CONCLUÍDA/ACEITA | — | 0/1; W18 | sem RS próprio; EL aceita pela migração |
| WU-CFG-01 — entrada em Configurações | COMPLETED → CONCLUÍDA/ACEITA | — | 0/1; W18 | sem RS próprio; EL aceita pela migração |
| WU-COMP-01 — contraste do valor | COMPLETED → CONCLUÍDA/ACEITA | — | 0/1; W18 | sem RS próprio; EL aceita pela migração |
| WU-REC-05 — recorrência limitada | COMPLETED → CONCLUÍDA/ACEITA | — | 1/1; W39 | RS; output ref aceito |
| WU-VG-05 — linha por compromisso | COMPLETED → CONCLUÍDA/ACEITA | — | 1/1; W48 | RS; output ref aceito |
| WU-VG-09 — coluna valor | COMPLETED → CONCLUÍDA/ACEITA | — | 1/1; W57 | RS; output ref aceito |
| WU-VG-10 — coluna status | COMPLETED → CONCLUÍDA/ACEITA | — | 1/3; W60 | RS; output ref aceito |
| WU-VG-11 — coluna ação | COMPLETED → CONCLUÍDA/ACEITA | — | 2/4; W64 | RS; output ref aceito |
| AGENDA — fan-in histórico | REVISION_REQUIRED → RETORNOU PARA REVISÃO | COMP-FORM, AGENDA-RECOVERY-GATE, WU-VG-01..20 | 0/8; W18 | RS sem output ref; revisão após estratégias esgotadas/worker partial |
| WU-DATA-01 — contrato Data do Compromisso | RECOVERY_REQUIRED → EM RECOVERY | — | 2/9; W26 | 8 manifests/RS; **não aceita**; estratégias esgotadas/evaluation returned; bloqueia COMP-02/03, REC-02 e VG-02/03/04/06 |
| WU-COMP-04 — remover lista da aba | RECOVERY_REQUIRED → EM RECOVERY | — | 2/4; W43 | RS; estratégias esgotadas/evaluation returned |
| WU-REC-01 — remover NEXT | RECOVERY_REQUIRED → EM RECOVERY | — | 2/4; W30 | RS; estratégias esgotadas/evaluation returned |
| WU-REC-03 — sem recorrência | RECOVERY_REQUIRED → EM RECOVERY | — | 2/4; W34 | RS; estratégias esgotadas/evaluation returned |
| WU-REC-04 — indeterminada | RECOVERY_REQUIRED → EM RECOVERY | — | 2/4; W38 | RS; estratégias esgotadas/evaluation returned |
| WU-VG-01 — agenda mensal | RECOVERY_REQUIRED → EM RECOVERY | — | 2/4; W47 | RS; estratégias esgotadas/evaluation returned |
| WU-VG-07 — coluna descrição | RECOVERY_REQUIRED → EM RECOVERY | — | 2/4; W52 | RS; estratégias esgotadas/evaluation returned |
| WU-VG-08 — coluna categoria | RECOVERY_REQUIRED → EM RECOVERY | — | 2/4; W56 | RS; estratégias esgotadas/evaluation returned |
| WU-VG-12 — efetivar/desefetivar | RECOVERY_REQUIRED → EM RECOVERY | — | 2/4; W68 | RS; estratégias esgotadas/evaluation returned |
| WU-VG-13 — Ver Lançamentos | RUNNING → BLOQUEADA/SUSPENSA operacionalmente | — | 0/0; W69 ativo só no checkpoint | `result.txt` sem manifest; Worker inexistente, execution stale |
| WU-VG-14 — remover Gerenciar | PLANNED → READY MAS NÃO CONCLUÍDA | — | 0/0; — | nunca executou; independente |
| WU-VG-15 — preservar Adicionar | PLANNED → READY MAS NÃO CONCLUÍDA | — | 0/0; — | nunca executou; independente |
| WU-VG-16 — abrir criação | PLANNED → READY MAS NÃO CONCLUÍDA | — | 0/0; — | nunca executou; independente |
| WU-VG-17 — coluna Edição | PLANNED → READY MAS NÃO CONCLUÍDA | — | 0/0; — | nunca executou; independente |
| WU-VG-18 — botão Editar | PLANNED → READY MAS NÃO CONCLUÍDA | — | 0/0; — | nunca executou; independente |
| WU-VG-19 — hidratar edição | PLANNED → READY MAS NÃO CONCLUÍDA | — | 0/0; — | nunca executou; independente |
| WU-VG-20 — editar efetivado | PLANNED → READY MAS NÃO CONCLUÍDA | — | 0/0; — | nunca executou; independente |
| WU-COMP-02 — substituir mês por data | PLANNED → AGUARDANDO DEPENDÊNCIA | WU-DATA-01 | 0/0; — | nunca executou |
| WU-COMP-03 — pré-preencher data | PLANNED → AGUARDANDO DEPENDÊNCIA | WU-DATA-01 | 0/0; — | nunca executou |
| WU-REC-02 — início pela data | PLANNED → AGUARDANDO DEPENDÊNCIA | WU-DATA-01 | 0/0; — | nunca executou |
| WU-VG-02 — mês pela data | PLANNED → AGUARDANDO DEPENDÊNCIA | WU-DATA-01 | 0/0; — | nunca executou |
| WU-VG-03 — ordem cronológica | PLANNED → AGUARDANDO DEPENDÊNCIA | WU-DATA-01 | 0/0; — | nunca executou |
| WU-VG-04 — recorrências aplicáveis | PLANNED → AGUARDANDO DEPENDÊNCIA | WU-DATA-01, WU-REC-02, WU-REC-04, WU-REC-05 | 0/0; — | nunca executou |
| WU-VG-06 — vencimento | PLANNED → AGUARDANDO DEPENDÊNCIA | WU-DATA-01 | 0/0; — | nunca executou |
| VERIFY-STAGE11 — fan-in final | PLANNED → AGUARDANDO DEPENDÊNCIA | AGENDA | 0/0; — | nunca executou |

**Contagem operacional por categoria:** CONCLUÍDA/ACEITA **18**; RETORNOU PARA REVISÃO **1**; EM RECOVERY **9**; BLOQUEADA/SUSPENSA **1** (estado persistido `RUNNING`, porém sem Worker); READY MAS NÃO CONCLUÍDA **7**; AGUARDANDO DEPENDÊNCIA **8**; CANCELADA **0**. Total: **44**.

### Próxima janela

Nenhuma ação de recuperação é autorizada por este handoff. A nova janela deve começar somente com reconciliação read-only deste checkpoint e liveness; qualquer decisão de reativar a mesma orquestração exige instrução humana explícita. Não criar substituta e não inferir terminalidade pela ausência de liveness.

---

## HISTORICAL / NON-CURRENT — conteúdo anterior preservado abaixo

## Encerramento desta sessão

- A continuação funcional do SGFP está **suspensa**. Nesta sessão não houve `resume-project`, despacho de Worker, alteração funcional do SGFP, reset, clean ou stash.
- A retomada futura deve usar exclusivamente a orquestração existente `25661d19a7f84ff390bbab47adf505e0`, depois de reconciliação somente leitura do estado pós-incidente.

## Adaptive AI Orchestrator

- Repositório local: `/home/ariel/Área de trabalho/VSCode/Git/adaptive-ai-orchestrator`.
- Branch/HEAD confirmado após `git fetch origin` e `git merge --ff-only origin/main`: `main` em `b0c0bcd77b3e3fc91adf6ee257d76cc1673bb34e` (PR #70, `fix: prevent supervisor exit and recovery replan livelock`).
- PR #69 (Work Graph Migration / Normalization) já está incorporado em `main`.
- O runtime permanece no checkout local: `.venv/bin/python` importa `adaptive_orchestrator` de `/home/ariel/Área de trabalho/VSCode/Git/adaptive-ai-orchestrator/src/adaptive_orchestrator/__init__.py`; CLI `.venv/bin/adaptive-orchestrator` disponível.
- WIP pré-existente foi preservado integralmente: `docs/architecture/RESULT-STORE-TERMINAL-RECONCILIATION.md` continua não rastreado; nenhuma limpeza, stash ou sobrescrita foi aplicada.
- Validação oficial registrada para o PR #70: `578 passed, 0 skipped, 0 failed` (não reexecutada nesta sessão).

### Correção incorporada pelo PR #70

- O supervisor `--watch` permanece vivo se uma retomada automática falhar e mantém lease de cooldown antes da nova tentativa.
- Recovery replan sem progresso estrutural real é rejeitado; os replans são limitados por recovery epoch e um epoch sem progresso faz yield governado, não terminal.
- `RECOVERY_REQUIRED` e `pending_replan=true` são preservados durante esse yield; falhas transitórias de provider do Recovery Strategist/Planner também fazem yield não terminal, sem classificação artificial como `BLOCKED`.
- A análise estratégica seguinte recebe feedback das falhas anteriores.
- Eventos de observabilidade disponíveis: `orchestration_recovery_yielded` e `recovery_strategy_failed`.

## Orquestração SGFP persistida

- **ID:** `25661d19a7f84ff390bbab47adf505e0`.
- **Migração aplicada:** `sgfp-explicit-wu-normalization-v1`.
- **Spec digest:** `2c3d7dec6b9ed4e02f9632a88a82081365f093b7ff5e1ca3beef7d5f6a29c1d4`.
- **Digests before/after:** `ff158823ce00f8747d35a9f680d68e16b8a25f923605c0fd8e8e035afdb59281` → `7203557f4d3cac764146355b9ca1f4883825d1ec048d446a065a3219b7eaef0d`.
- **Work Graph:** 44 WUs totais: 9 nós históricos preservados e 35 WUs explícitas materializadas; 30 dependências novas; nenhuma dependência histórica removida ou alterada. A dependência histórica `AGENDA -> VERIFY-STAGE11` permanece preservada.
- **WUs aceitas por evidence lineage:** `WU-MC-01`, `WU-MC-02`, `WU-MC-03`, `WU-MC-04`, `WU-CFG-01`, `WU-COMP-01`.

### Estado autoritativo final observado

- O checkpoint continua não terminal: `status=RUNNING`, `desired_state=RUNNING`, `terminal=false`, `active_execution_count=0`, `pending_replan=true`, `replan_count=21`, `dispatch_generation=22` e 44 WUs.
- `WU-DATA-01=RECOVERY_REQUIRED`; não há Worker funcional ativo nem dependência liberada a partir dela.
- O liveness do controller anterior está `TERMINAL/FAILED`; não há processo de controller/supervisor ativo. O artefato de Recovery Strategist existe apenas como `result.txt.tmp`, sem manifest final.
- Portanto, há divergência operacional a reconciliar: o checkpoint é não terminal, mas o controller anterior terminou em falha após recovery/replan livelock. Não usar este encerramento como autorização para retomar automaticamente.

### Análise do incidente preservada

- A lacuna `recurrence_start=CURRENT/NEXT` pertence principalmente às WUs de recorrência: `WU-REC-01` remove a opção de iniciar no mês seguinte e `WU-REC-02` inicia a recorrência pela Data do Compromisso.
- `WU-REC-02` já depende de `WU-DATA-01`; não inverter essa dependência nem criar ciclo.
- Essa lógica downstream não deve impedir artificialmente o aceite de `WU-DATA-01`: o escopo desta WU é tornar `commitment_date` uma data completa, persistida, retornada pela REST API e tecnicamente utilizável, inclusive como referência canônica posterior para recorrência.
- O incidente evidenciou replan repetido sem progresso estrutural, possível elevação contínua de `replan_count` e falhas de sessão/provider do Recovery Strategist/Planner que podiam encerrar o controller; esses são precisamente os caminhos corrigidos pelo PR #70.

## Git SGFP

- Branch: `feat/stage-11-web-interface`.
- HEAD conhecido: `4ff7168832dff76a659bba9c886a9e19371b960a`.
- O working tree contém WIP amplo, modificado e não rastreado, preservado sem `reset`, `clean` ou `stash`.

## Próximo passo seguro em nova sessão

1. Iniciar em modo somente leitura e confirmar Adaptive `main` em `b0c0bcd77b3e3fc91adf6ee257d76cc1673bb34e`, incluindo import pelo runtime `.venv` local.
2. Ler checkpoint, Work Graph, Result Store, este handoff e liveness da mesma orquestração `25661d19a7f84ff390bbab47adf505e0`.
3. Não criar nova orquestração, não refazer a migração das 44 WUs e não retomar automaticamente antes de reconciliar o estado pós-incidente.
4. Determinar se `WU-DATA-01` permanece `RECOVERY_REQUIRED/pending_replan`; só então decidir a retomada governada pelo supervisor/recovery corrigido do Adaptive.

Os checkpoints abaixo são históricos/não atuais; seu conteúdo foi preservado integralmente.

# HISTORICAL / NON-CURRENT — checkpoint anterior — 2026-09-20

- **Branch/HEAD:** `feat/stage-11-web-interface` / `4ff7168832dff76a659bba9c886a9e19371b960a`.
- **WIP a preservar:** working tree amplo e intencionalmente dirty (alterações em `src/`, `src/assets/`, `docs/`, `release/` e este arquivo); não usar `reset`, `clean`, `stash`, troca de branch, rebase, commit, merge ou deploy para “limpar” o checkout.
- **Etapa/gate:** Etapa 11 — Desenvolvimento da Interface Web — em andamento; o gate de aceite WordPress/browser ainda não está concluído.
- **Decisões V1 aplicáveis:** uma única conta `Minha Conta`; saldo derivado de lançamentos; sem Transferências, Patrimônio Total ou PIN; backup/restauração por ZIP local; reset com `RESETAR PERFIL`; exclusão de acesso com `EXCLUIR CONTA` — ver `docs/governanca/baseline-v1-simplificada-2026-09-14.md`.
- **Validações locais comprovadas:** correção da validação estrita do mês `YYYY-MM-01` no dashboard; PHPUnit **99 testes / 264 assertions PASS**; pacote `release/sgfp-etapa-11-runtime-20260919T164036Z-clean.zip` (**116311 bytes**, SHA-256 `a03823e92080e81d331e13011131e15ebcd5777176cd3a7965912773b47562a1`); packaging, vendor de produção isolado, raiz ZIP `sgfp/`, exclusão de testes/manifests Composer, lint, boot smoke, `unzip -t` e `git diff --check` passaram. Essas evidências são locais e não provam aceite ambiental.
- **Adaptive/orquestrações:** a orquestração de projeto `154192e6efc448e2af4b127b5c8ac2cd` está **PAUSED**; suas Work Units registradas têm resultados locais, mas a orquestração pausada não é conclusão do gate. Não tratar avaliações antigas, resultados parciais ou ausência de worker como aceite.
- **Limites e riscos:** WordPress/MySQL autenticado, paridade plugin/schema, provisionamento, chamadas REST e aceite browser/UX permanecem não verificados; o pacote limpo é somente candidato a reteste, não release.
- **Memória:** a busca de memória OpenClaw está indisponível por incompatibilidade de chunking; até correção controlada, este arquivo e os artefatos/repositório são a fonte de retomada.
- **Próxima ação segura:** sem descartar WIP, instalar/ativar exatamente o pacote limpo em WordPress de teste com MySQL/MariaDB, registrar paridade de código/schema, executar o reteste autenticado da interface e capturar respostas REST/logs/screenshots; depois atualizar este handoff somente com evidência observada.

Os checkpoints abaixo são históricos/não atuais; seu conteúdo foi preservado integralmente.

# HISTORICAL / NON-CURRENT — checkpoint anterior — 2026-09-19

- Checkout confirmado na branch `feat/stage-11-web-interface`, HEAD `4ff7168832dff76a659bba9c886a9e19371b960a`.
- Working tree permanece dirty intencional; as alterações WIP existentes foram preservadas.
- Etapa 11 (Interface Web) está em andamento.
- Orquestração terminal `daecfdbc36c54d24abe797d8a44d6c39`: correções locais para PATCH de Compromissos nullable, edição/ciclos efetivar-desfazer, sinais negativos e saldo atual/projetado. Validações executadas: 98 testes/262 assertions, lint PHP, `node --check` e `git diff --check`. WordPress/browser real ainda pendentes.
- Pacote local pronto para reteste: `release/sgfp-etapa-11-retest-20260919T031040Z-clean.zip`, 128542 bytes, SHA-256 `4ba1bb08926b53ea751e16a247b40e5af2cbcd27383d92b8a1f8a5fb4a25994c`, orquestração terminal `88cc9ec1e9d5451bb9c216fb5ed7ba41`. Foi somente validado localmente; não foi instalado, ativado ou enviado.
- Lacuna UX conhecida: a aba Minha Conta permite nome e saldo inicial, mas ainda precisa explicitar que a conta já existe com o padrão **Minha Conta** e saldo inicial **R$ 0,00**, além de obter teste de múltiplos PATCH/WordPress real quando aplicável.
- Próximo passo seguro: instalar este ZIP em WordPress de teste, ativar e validar os cenários reais sem descartar WIP.

# HISTORICAL / NON-CURRENT — CURRENT AUTHORITATIVE CHECKPOINT anterior — 18/09/2026 — revisão independente ainda requer correção

## Fan-in/revisão Adaptive — Etapa 11 / HEAD `4ff7168832dff76a659bba9c886a9e19371b960a`

- Resultados dependentes `WU-COMMITMENTS` e `WU-ACCOUNTING-UI` verificados por manifest e SHA-256.
- Validação local nesta revisão: PHPUnit **96 testes / 257 assertions PASS**; lint PHP PASS; `node --check src/assets/app.js` PASS; `git diff --check` PASS.
- Veredito: **REQUEST CHANGES / PARTIAL**.
- Bloqueador persistente: `src/src/REST/Routes.php` declara `category_id` do PATCH `/commitments/{id}` apenas como `integer`, enquanto atualização e frontend aceitam/enviam `null` para remover categoria. O teste existente cobre apenas o POST.
- Critério não atendido: contrato PATCH anulável, regressão de sanitização/contrato para `null` e nova revisão após a correção.
- Segurança/ownership: testes locais existentes passaram; não houve confirmação de vazamento sensível.
- Limitação ambiental: WordPress/MySQL autenticado e browser/UX reais não foram executados.

# HISTORICAL / NON-CURRENT — CURRENT AUTHORITATIVE CHECKPOINT anterior — 18/09/2026 — revisão independente requer correção

## Fan-in/revisão Adaptive — Etapa 11 / HEAD `4ff7168832dff76a659bba9c886a9e19371b960a`

- Resultados dependentes `WU-COMMITMENTS` e `WU-ACCOUNTING-UI` verificados por manifest e SHA-256; ambos completos.
- Verificações locais no HEAD atual: PHPUnit **96 testes / 257 assertions PASS**; lint PHP PASS; `node --check src/assets/app.js` PASS; `git diff --check` PASS.
- Veredito independente: **REQUEST CHANGES**.
- Bloqueador confirmado em `src/src/REST/Routes.php`: o PATCH `/commitments/{id}` ainda declara `category_id` somente como `integer`, embora `UpdateCommitmentService`/DTO aceitem `null` e o frontend envie `null` ao remover categoria. O sanitizador REST do WordPress pode rejeitar a requisição antes do controller.
- Critério não atendido: tornar o campo PATCH anulável, adicionar regressão específica de contrato/sanitização para `null` e executar nova revisão independente no HEAD corrigido.
- Segurança/ownership: testes de ownership, capability e erros públicos passaram; não foi confirmado vazamento sensível.
- Limitação ambiental: WordPress/MySQL autenticado e browser/UX reais permanecem não verificados.
- Nenhum código-fonte, pacote, deploy, commit, merge ou reset foi executado nesta revisão; WIP preservado. Esta atualização altera somente `HANDOFF.md`.

# HISTORICAL / NON-CURRENT — CURRENT AUTHORITATIVE CHECKPOINT anterior — 18/09/2026 — correção de contrato REST pendente de reteste ambiental

## Checkpoint governado — Etapa 11 / criação de Compromisso

- **Etapa/branch/HEAD:** Etapa 11 — Desenvolvimento da Interface Web; `feat/stage-11-web-interface` / `4ff7168832dff76a659bba9c886a9e19371b960a`.
- **Causa comprovada da falha:** o frontend envia `category_id`, `recurrence_months_count` e `recurrence_start` como `null`. A rota `POST /commitments` os declarava somente como `integer`/`string`; o sanitizador REST real do WordPress rejeitava o payload antes do controller, e o filtro de erro o expunha como `REQUEST_ERROR`.
- **Correção local já feita:** os três campos tornaram-se anuláveis no contrato da rota; `recurrence_start` passou a incluir `null` no enum. Não houve mudança de banco, serviço, frontend, pacote, deploy ou instalação.
- **Validações locais:** reprodução em memória contra o sanitizador real do WordPress 6.4; PHPUnit completo: **95 testes / 245 assertions PASS**; lint PHP PASS; `node --check src/assets/app.js` PASS; `git diff --check` PASS.
- **Limite ambiental:** não houve inserção/retorno real no WordPress porque não há site WordPress conectado nem runtime local. O reteste real permanece pendente após instalar esta fonte corrigida.
- **Adaptive/fallback:** `af500cf7f3e747faaae04e2f21c03a5f` é histórico de investigação ambiental bloqueada. A correção foi executada por fallback direto autorizado pelo usuário, como exceção porque o Adaptive estava terminal `BLOCKED` sem workers ativos.
- **WIP:** preservar todo o conteúdo e working tree existentes; não executar reset, clean, stash, checkout, rebase, commit, push, merge, deploy, instalação ou ZIP sem autorização explícita. Este checkpoint altera somente este `HANDOFF.md`.

O conteúdo histórico abaixo permanece preservado.

# HISTORICAL / NON-CURRENT — CURRENT AUTHORITATIVE CHECKPOINT anterior — 18/09/2026 — investigação ambiental pendente

## Checkpoint compacto para retomada

- **Branch/HEAD:** `feat/stage-11-web-interface` / `4ff7168832dff76a659bba9c886a9e19371b960a`.
- **WIP a preservar:** working tree amplo e intencionalmente dirty; não executar reset, clean, stash, troca de branch, rebase, commit, push, merge, deploy ou ZIP sem autorização explícita. Os ZIPs anteriores, incluindo `release/sgfp-stage11-commitments-fix-20260918-1626.zip` e outros, não devem ser tratados como correção comprovada do ambiente real.
- **Validação real em WordPress hospedado — 18/09/2026:** a criação de Compromisso falhou com o erro exibido `REQUEST_ERROR` (Nome `luz`, Valor `123,55`, Natureza `Saída`, sem Categoria, sem Recorrência, referência setembro/2026), conforme screenshot. A causa ainda não foi determinada.
- **Evidência histórica:** a última orquestração terminal de empacotamento foi `ffaa50ce00894750977f07b9c30a2690`; isso é evidência local/histórica, não prova de correção no ambiente hospedado.
- **Estado desta unidade:** investigação real, correção e novo ZIP continuam pendentes. Este checkpoint alterou somente este `HANDOFF.md`.
- **Próximo passo seguro:** reconciliar estado de plugin/versão/ativos no WordPress hospedado; capturar resposta REST/rede e logs; comparar contrato/payload; então corrigir, testar e empacotar por nova orquestração. Não redispatchar trabalho anterior apenas por este handoff.

Este checkpoint é o estado autoritativo mais recente. O histórico abaixo permanece preservado.

# HISTORICAL / NON-CURRENT — CURRENT AUTHORITATIVE CHECKPOINT anterior — 17/09/2026

## Unidade Adaptive — estado anônimo do shortcode — 2026-09-17

- Alterado `src/src/Frontend/Frontend.php`: usuário não autenticado agora recebe uma tela simples de acesso SGFP com o botão `Entrar`, gerado por `wp_login_url()` com retorno para a URL atual da página SGFP.
- Preservado o fluxo autenticado existente, incluindo a guarda de provisionamento/capability e a renderização do frontend.
- Atualizado `src/src/Tests/Unit/AuthFrontendTest.php` para verificar estado anônimo, botão de login, redirect para a página SGFP e ausência de cadastro público/recuperação adicional.
- Validações: `php -l` nos dois arquivos, PHPUnit `AuthFrontendTest.php`: **6 testes / 14 assertions / 0 falhas**, `git diff --check`: PASS.
- WIP preexistente do working tree foi preservado.

Este é o checkpoint autoritativo mais recente para uma nova sessão SGFP.

- **Branch:** `feat/stage-11-web-interface`
- **HEAD:** `4ff7168832dff76a659bba9c886a9e19371b960a`
- **Working tree:** contém WIP dirty intencional preservado; não descartar, resetar, limpar, trocar de branch ou rebasear sem autorização explícita.
- **Correção confirmada:** wiring fatal de `Routes` corrigido.
- **Baseline validada:** Composer válido; PHPUnit **67 testes / 161 assertions**; lint PHP; `node --check`; `git diff --check`; composição de rotas; smoke de boot do plugin.
- **Pacote limpo:** `dist/sgfp-stage11-rest-hotfix-v3-20260917.zip`, **120648 bytes**, SHA-256 `54261d0f59522a8c8c4e466aa13d30a3995c8a4329dd0c573c30d633416fdc82`.
- **Limite do pacote:** destinado **somente a teste ambiental WordPress**; não é aprovação de release e não foi instalado.

**Próxima ação segura:** abrir uma nova janela focada no frontend para explorar a funcionalidade do produto e validar o comportamento da interface em um ambiente WordPress real, preservando todo o WIP e sem repetir a descoberta já concluída.

O conteúdo histórico abaixo permanece preservado explicitamente como histórico e não substitui este checkpoint.

## Validação ambiental — 2026-09-17 — Work Unit `wu:256ce89a41974294b3fa40a9cd197a21`

- Branch/HEAD confirmados antes da inspeção: `feat/stage-11-web-interface` / `4ff7168832dff76a659bba9c886a9e19371b960a`.
- Working tree dirty intencional preservado integralmente; não houve reset, clean, troca de branch, rebase, stash ou alteração remota.
- Pacote destinado ao teste ambiental conferido: `dist/sgfp-stage11-rest-hotfix-v3-20260917.zip`, 120648 bytes, SHA-256 `54261d0f59522a8c8c4e466aa13d30a3995c8a4329dd0c573c30d633416fdc82`. O conteúdo contém a estrutura limpa do plugin, frontend, `src/` e `vendor/`.
- Bloqueio ambiental objetivo: não há `docker`/`podman`/`wp` disponíveis; não há `wp-config.php`, `.env` ou credenciais/sessão de teste no checkout; `127.0.0.1:80` e `localhost:80` recusaram conexão; não foi identificado endpoint WordPress local ativo.
- Resultado: autenticação WordPress, provisionamento de `Minha Conta`, shortcode/frontend e entrada no aplicativo **não foram executados**; nenhum êxito foi simulado.
- Próxima ação: disponibilizar um WordPress real acessível e uma sessão/credenciais de teste já configuradas, então repetir somente os gates ambientais aplicáveis com este ZIP, sem instalar em ambientes fora do escopo.

# HISTORICAL / NON-CURRENT — CURRENT OVERRIDE anterior — 14/09/2026

Este HANDOFF contém abaixo um registro técnico extenso da implementação da Etapa 10 sobre a **baseline anterior**. Ele deve ser preservado como evidência histórica, mas **não é mais a autoridade funcional atual**.

Antes de retomar código, leia `docs/governanca/baseline-v1-simplificada-2026-09-14.md`.

Mudanças vigentes:
- uma conta por usuário (`Minha Conta`);
- sem Transferências e sem Patrimônio Total na V1;
- saldo derivado dos lançamentos da conta única;
- backup ZIP local, sem e-mail;
- reset do perfil e exclusão do login com dupla confirmação;
- backend da Etapa 10 já reconciliado e validado na branch `feat/stage-9-10-backend` / Draft PR #12;
- frontend da Etapa 11 está em andamento e precisa ser reconciliado com a baseline e com o backend atualizado.

Para estado operacional atual, leia também `docs/governanca/status-implementacao-v1-2026-09-14.md`.

Não use detalhes históricos abaixo para reintroduzir capacidades removidas da V1.

---

# HANDOFF HISTÓRICO DA IMPLEMENTAÇÃO ANTERIOR

# SGFP — Handoff de Continuidade

## Estado corrente autoritativo — 2026-09-12

- Etapa 9 concluída e validada.
- Etapa 10 concluída no commit `362c4b947bbfceb171c75c9a71b943d50d1cfe14`; lint, testes manuais e verificações Git aprovados.
- PHPUnit e validação WordPress/MySQL/MariaDB real permanecem pendentes por limitação ambiental.
- Etapa 11 não iniciada.
- Etapa 12 permanece futura para consolidação formal dos testes.

Os registros abaixo preservam histórico de unidades anteriores e bloqueios já superados; não substituem o estado corrente acima.

## Unidade `wu:5d2caf8351ab4dc9a08a4168c9b75c00` — 2026-09-12

### Trabalho realizado

- Criada a tabela física dedicada `{$wpdb->prefix}sgfp_token_restauracao`, com prefixo `sgfp_`, `ENGINE=InnoDB`, FK para `wp_users`, ownership por `user_id`, hash SHA-256 em `CHAR(64)` binário, expiração, metadata e claim.
- Adicionada migração incremental/idempotente `1.1.0`, com verificação runtime da engine efetiva via `information_schema`.
- `ValidateBackupService` grava tokens nessa tabela; `WpRestorationTokenClaim` faz `SELECT ... FOR UPDATE` e update condicional na mesma tabela, mantendo o contrato da porta e o escopo do usuário.
- `RevalidateRestorationService` lê a tabela pelo novo store; a compatibilidade de construtor permanece somente para os testes unitários antigos, enquanto a composição REST usa exclusivamente o store SQL.

### Evidências

- Lint PHP completo: passou.
- `git diff --check`: passou.
- PHPUnit/static checks: Composer não está disponível neste ambiente; não foram executados.

### Limites preservados

Esta unidade não implementa transação integral de restauração, replacement/rollback, retenção/catalogação/e-mail ou Stage 11. `AGENTS.md` e `PROMPTS-OPENCLAW-SGFP.md` não foram alterados por esta unidade; alterações dirty preexistentes foram preservadas.

## Unidade `wu:9dda475005f247cf8ac2297c59dc9f1a` — 2026-09-12

### Resultado: BLOQUEADA — atomicidade do token não demonstrável

A investigação não autorizou implementação segura da restauração integral:

- A criação grava `sgfp_restore_validation_<sha256(token)>` em `wp_usermeta` por `update_user_meta()` (`ValidateBackupService`/`WpUserPreferenceRepository`). A confirmação lê e atualiza essa linha em `WpRestorationTokenClaim`, com `SELECT ... FOR UPDATE`, mas não inicia nem recebe um `TransactionManager` comum.
- O executor disponível (`RestoreFromImportPlanService`) inicia `START TRANSACTION`/`COMMIT`/`ROLLBACK` por `WpTransactionManager` na conexão global `$wpdb`, cobrindo somente a persistência SGFP atualmente contratada. Não há prova no código, na configuração ou no Modelo Físico V7 de que `wp_usermeta` use InnoDB na instalação efetiva e participe da mesma unidade transacional; `FOR UPDATE` isolado não cria essa garantia.
- O Modelo Físico V7 declara `ENGINE=InnoDB` para as seis tabelas SGFP e exige compatibilidade InnoDB para FKs com `wp_users`, mas trata `wp_usermeta` como responsabilidade WordPress e não define sua engine/DDL. A arquitetura canônica também registra que usermeta não substitui as tabelas financeiras e que cache deve ser invalidado somente após commit.
- `get_user_meta()`/`update_user_meta()` atravessam a camada WordPress e podem atualizar cache de objeto; não existe no fluxo de restauração uma invalidação pós-commit nem uma garantia de que o estado cacheado acompanhe rollback. Portanto, mesmo a hipótese de conexão compartilhada não bastaria sem contrato explícito de cache.
- A busca por `TRUNCATE`, `ALTER TABLE`, `DROP TABLE`, `CREATE TABLE`, `RENAME TABLE`, `LOCK TABLES` e equivalentes no código de restauração não encontrou statement de DDL/implicit commit. Os `CREATE TABLE` encontrados pertencem ao bootstrap/migração do schema, fora da restauração; isso remove um risco identificado, mas não resolve a fronteira `wp_usermeta`/SGFP.

Não foi criada token table, não houve alteração em `src/`, não foi iniciada Stage 11 e os arquivos dirty preexistentes foram preservados. Não é seguro implementar claim transacional, rollback conjunto ou os testes de sucesso/falha/concorrência solicitados enquanto a infraestrutura comum e o contrato de cache não forem demonstrados/definidos.

### Evidências

- Inspecionados `ValidateBackupService`, `WpRestorationTokenClaim`, `WpUserPreferenceRepository`, `WpTransactionManager`, `RestoreFromImportPlanService`, `Schema.php`, Modelo Físico V7 e arquitetura canônica.
- `git diff --check`: passou.

## Unidade `wu:35d8f2ff6ea0479486edddfaf60bf3ad` — 2026-09-12

### Trabalho realizado

- Criados `RestorationPersistence` e `RestoreFromImportPlanService`.
- O executor aceita somente `RestorationImportPlan` válido, delega a substituição user-scoped à porta explícita, executa verificação antes do commit e usa `TransactionManager::transactional`, garantindo rollback em exceções.
- A porta de persistência exige contagens, proprietário, tema, referências e invariantes verificados; a ordem física do Modelo V7 é validada pelo serviço.
- A integração no fluxo REST não foi feita: a claim atual em `usermeta` ainda não compartilha a fronteira transacional SQL do executor. Conectá-la agora permitiria consumir o token e depois falhar a restauração sem rollback da claim.

### Evidências

- Lint dos arquivos alterados: passou.
- `git diff --check`: passou.

### Limite preservado

- Nenhum arquivo protegido foi alterado nesta unidade; Stage 11, catálogo e retenção permanecem fora do escopo.

## Unidade `wu:c240d3184526476182c0f7bc8a00f829` — 2026-09-12

### Trabalho realizado

- A confirmação reabre o arquivo staged sob o `UserOperationLock` do usuário efetivo.
- Reautentica, descriptografa com AEAD, descompacta gzip, decodifica/valida o schema e gera o `RestorationImportPlan` determinístico antes do snapshot e da claim do token.
- Falhas de arquivo indisponível, adulterado, expirado, incompatível, malformado ou não decodificável ocorrem antes de snapshot/claim e não adicionam mutação de dados SGFP.
- O plano é retornado como preparação (`import_plan`); substituição, rollback, retenção, e-mail e Stage 11 permanecem fora do escopo.

### Evidências

- Lint PHP completo: passou.
- `php Tests/Manual/*.php`: passou.
- `git diff --check`: passou.
- PHPUnit não executado: Composer/extensão `mbstring` indisponíveis no ambiente.

### Limite preservado

O executor de substituição integral continua bloqueado pelos contratos de persistência/atomicidade já registrados nas unidades anteriores; nenhum arquivo protegido dirty foi alterado.

## Unidade `wu:262f51ad0ca94b8e9d6a860dd2cfb937` — 2026-09-12

### Resultado: BLOQUEADORES RECONCILIADOS DOCUMENTALMENTE — implementação ainda não autorizada nesta unidade

Esta unidade confrontou `HANDOFF.md`, `README.md`, `docs/governanca/continuidade-de-contexto.md`, UC-020/UC-021, o Modelo Físico V7, os contratos REST e a implementação atual. Não houve alteração em `src/`, Stage 11 não foi iniciada e `AGENTS.md`/`PROMPTS-OPENCLAW-SGFP.md` foram preservados.

As classificações usadas abaixo são: **A** regra/decisão de negócio; **B** contrato arquitetural ou de persistência; **C** detalhe técnico implementável sem nova regra; **D** operação, segurança ou disponibilidade.

### Contrato consolidado para a implementação posterior

1. A restauração só pode avançar com `confirmation === true`, capability e usuário atuais, token não expirado, staging íntegro e hash conferido. A confirmação falsa, ausente, malformada ou repetida não muta dados.
2. O `UserOperationLock` por usuário cobre revalidação, captura do `pre_restore`, substituição, verificação, commit/rollback e claim do token. Perda do lock/conexão cancela; não há continuação em outra conexão.
3. O `pre_restore` é produzido antes de qualquer exclusão, em visão consistente, protegido, persistido atomically e relido/verificado. Falha em captura, proteção, persistência, catálogo ou verificação impede o início da escrita.
4. A substituição é integral e user-scoped: apagar o conjunto atual e importar o staging em ordem de dependência dentro de uma única transação SQL. Não há merge. IDs lógicos do backup devem ser preservados ou devem existir remapeamento completo e explícito para todas as referências; `save` isolado não satisfaz esse contrato.
5. A verificação ocorre antes do commit: contagens, proprietário, referências, constraints, tema e invariantes do Modelo Físico V7. Qualquer falha faz rollback e deixa o estado anterior intacto.
6. O token é uma claim única e idempotentemente rejeitável. Para ser realmente atômico com a substituição, seu estado consumível e o catálogo do artefato precisam participar da mesma unidade transacional SQL (ou de um protocolo de compensação formalmente equivalente, que não está aprovado). `usermeta` isolado e arquivo fora da transação não podem ser tratados como atomicidade suficiente.
7. A retenção adotada é a de `DEC-004`: uma cópia `pre_restore`, preservada por 24 horas ou até a próxima tentativa de restauração, o que ocorrer primeiro; o catálogo deve impedir acesso cruzado e identificar usuário, origem, hash, expiração e localização. Falha de e-mail após preservação não desfaz a restauração.
8. Repetições concorrentes do mesmo token devem resultar em uma única restauração efetiva; as demais recebem rejeição estável. Repetições após sucesso não podem reaplicar a cópia. Uma nova tentativa, quando o token ainda for válido e a operação anterior tiver rollback completo, deve seguir o mesmo lock e revalidar o staging.
9. Autorização é sempre capability + usuário da sessão + escopo nas consultas/mutações; respostas não enumeram existência de outro usuário. Logs não carregam token, chave, payload ou dados financeiros.

### Matriz de bloqueadores

| BLOCKER | STATE | EXISTING DECISION | DECISION NEEDED | RESOLVED |
|---|---|---|---|---|
| Confirmação executável | Implementada apenas como contrato/revalidação; restauração ainda não executa | UC-021 exige confirmação; REST exige booleano verdadeiro | Implementar confirmação sem mutação antes dela | Sim — C |
| Snapshot pré-restauração | Captura existente, mas artefato/catalogação não compõem a transação SQL | UC-021, `ARQ-013`/`ARQ-014`, `DEC-004` exigem cópia recuperável antes da escrita | Porta de catálogo, persistência atômica, releitura e expiração | Sim — B/C |
| Substituição integral e referências | Planner existe; portas só têm `save`/consulta e não garantem delete/replace/remapeamento | UC-021 proíbe merge; Modelo V7 exige FKs e escopo por usuário | Operações bulk user-scoped e remapeamento determinístico em ordem de dependência | Sim — B/C |
| Transação/rollback e falha intermediária | `TransactionManager` cobre SQL; executor integral inexiste | `ARQ-007`, UC-021 e arquitetura exigem rollback e estado não misto | Uma transação de escrita, verificação antes do commit e rollback em toda exceção/conexão perdida | Sim — C |
| Retenção | Há decisão humana, mas a arquitetura contém texto antigo dizendo que não há política | `DEC-004`: 1 snapshot/24h ou até próxima tentativa | Aplicar essa política no catálogo/limpeza | Sim — A/D |
| Consumo/invalidação do token | Claim atual usa `usermeta` e está fora da transação SQL de restauração | Token é opaco, ligado ao usuário, expirável e de uso único | Mover claim/catalogação para fronteira transacional comum; consumir somente no commit lógico | Sim — B/C |
| Retry, concorrência e idempotência | Lock existe; substituição/claim único ainda não formam protocolo completo | `ARQ-014` exige lock; UC-021 não permite estado misto | Lock cobre o fluxo; claim única, rejeição estável e rollback completo | Sim — C/D |
| Integridade dos dados do usuário | Decoder/planner verificam payload; importação e verificação final não existem | V7, constraints e UC-021 exigem estado integral válido | Verificar contagens, referências, constraints, tema e proprietário antes do commit | Sim — C |
| Disponibilidade de recuperação/catálogo | Arquivo privado é gravado, mas não há catálogo transacional nem leitura de recuperação | `ARQ-013` exige storage recuperável e identificação `pre_restore` | Catálogo user-scoped transacional e rotina de expiração conforme `DEC-004` | Sim — B/C/D |
| Autorização e segurança | Rotas/captura têm capability e lock; fluxo integral ainda não existe | `ARQ-004`, `ARQ-015`, `UC-021` e staging privado | Repetir controles em toda operação e não expor artefatos/segredos | Sim — C/D |

Não foi identificada decisão de negócio nova. As lacunas restantes são contratos de persistência, transação, catálogo e execução, portanto podem ser adotadas sem inventar semântica de domínio. A unidade seguinte pode implementar somente esse contrato; não deve tratar `save` como substituição nem claim em `usermeta` como atomicidade da restauração.

## Unidade `wu:eea1589478744ce6983e01904d7b3505` — 2026-09-12

### Resultado: BLOQUEADA — checkpoint após retry

O retry foi encerrado sem alteração de código-fonte. A inspeção confirmou que a arquitetura canônica responde à ordem do fluxo e aos invariantes da restauração (lock por usuário, snapshot `pre_restore` antes da substituição, substituição integral, verificação e rollback), mas não define contratos executáveis adicionais nem código validado que os implemente:

- As portas dos seis repositórios continuam limitadas a `save` e consultas; não há operações user-scoped para apagar/substituir integralmente dados nem contrato de inserção que preserve IDs lógicos ou exponha remapeamento físico completo das referências.
- `TransactionManager` opera na conexão SQL, enquanto `RestorationTokenClaim` usa `usermeta` via WordPress; não existe fronteira transacional comum para garantir claim único atômico com a substituição.
- `BackupStore` persiste o snapshot como artefato fora da transação SQL; não há contrato de catálogo/retenção/verificação transacional que permita tratar artefato e mutações como uma unidade atômica.

Os documentos canônicos (`docs/arquitetura/01-arquitetura-da-aplicacao.md`, seções 9 e 13) determinam o comportamento esperado, mas não respondem essas escolhas de contrato/persistência. Implementar agora exigiria inventar semânticas não especificadas. Stage 11 não foi iniciada. Os arquivos sujos preexistentes `AGENTS.md`, `PROMPTS-OPENCLAW-SGFP.md`, `docs/api/README.md` e `docs/governanca/continuidade-de-contexto.md` foram preservados.

### Evidências

- `git diff --check`: passou.
- Nenhuma alteração especulativa em `src/` ou nos documentos canônicos.
- Checkpoint bloqueado registrado neste commit; o estado anterior válido permanece preservado.

## Unidade `wu:d8312afc40b04d56b3f0085e483c0c36` — 2026-09-12

### Resultado: BLOQUEADA — sem implementação segura

A unidade não implementou o executor de restauração porque a arquitetura persistente atual não fornece os fatos necessários para cumprir os invariantes solicitados sem adivinhação:

- As portas `AccountRepository`, `CategoryRepository`, `RecurrenceRepository`, `CommitmentRepository`, `TransferRepository` e `EntryRepository` só expõem `save`/consulta; não existe contrato para exclusão integral user-scoped ou substituição ordenada. Usar `save` sozinho faria merge e não substituiria os dados atuais.
- Os adaptadores usam IDs físicos autogerados em `insert`; não existe operação/contrato para inserir preservando ou retornar uma remapagem física completa para todas as referências lógicas durante a substituição.
- `RestorationTokenClaim` atualiza `user_meta` via WordPress, enquanto `TransactionManager` controla transação SQL em `$wpdb`. Não há fronteira transacional comum que permita consumir o token atomicamente com a substituição: consumir antes pode perder o token após rollback; consumir depois pode confirmar dados sem garantir claim único.
- O snapshot pré-restauração é persistido por `BackupStore` fora da transação SQL de substituição. Também não há contrato de retenção/verificação transacional que permita afirmar que o artefato recuperável e as mutações compartilham a mesma unidade atômica.

Implementar um executor exigiria escolher novos contratos e semânticas de persistência (deleção por usuário, inserção/remapeamento, armazenamento/claim transacional e ordem de snapshot), decisões não especificadas no repositório. Portanto, a unidade parou conforme a regra de não adivinhar fatos ausentes. Nenhum arquivo fora deste `HANDOFF.md` foi alterado; Stage 11 continua não iniciada.

## Unidade `wu:baf5b4b7acda42e9a33fcaf8868d4eb4` — 2026-09-12

### Trabalho realizado

- Criados `StagedBackup` (estrutura imutável) e `StagedBackupDecoder`.
- O decoder exige exatamente `metadata`, `theme` e as seis seções de registros; valida schema/version, proprietário, origem, tema, formato escalar dos registros, enums, IDs, referências internas e referências pendentes.
- Decodificação continua ocorrendo após autenticação, gzip e JSON; nenhuma substituição, persistência ou exclusão de registros foi adicionada.
- `CreateBackupService` e `CapturePreRestorationSnapshotService` passaram a emitir o formato canônico com metadados agrupados.
- `ValidateBackupService` agora só cria staging/token após obter a estrutura validada.
- Testes focados adicionados em `src/Tests/Unit/StagedBackupDecoderTest.php`.

### Evidências

- Lint PHP completo: passou.
- `php Tests/Manual/*.php`: passou.
- `git diff --check`: passou.
- PHPUnit não executado: Composer/extensão `mbstring` indisponíveis no ambiente.

## Unidade `wu:94e3f64adb834b1ab39d8759f0a66601` — 2026-09-12

### Trabalho realizado

- Criados `RestorationImportPlan` imutável e `RestorationImportPlanner`, aceitando somente `StagedBackup` validado e usuário efetivo.
- Ordem determinística: contas, categorias, recorrências, compromissos, transferências, lançamentos e tema; chaves lógicas e remapeamento explícito de referências.
- Referências nulas são preservadas; referências não resolvidas ou de outro usuário são rejeitadas. Nenhuma persistência foi executada.

### Evidências

- Lint PHP, testes manuais e `git diff --check`: executados ao final desta unidade.

### Limites preservados

- Não foram implementados substituição/persistência/exclusão de registros, commit/rollback, retenção/catalogação/e-mail, nem Stage 11.
- `AGENTS.md` e `PROMPTS-OPENCLAW-SGFP.md` não foram alterados por esta unidade; alterações preexistentes nesses arquivos foram preservadas.

**Data:** 2026-09-11  
**Branch ativa:** `feat/stage-9-10-backend`  
**Último commit:** a definir nesta unidade
**Etapa:** 10 — Desenvolvimento da API (em andamento)  
**Etapa 11:** NÃO iniciada.

## Estado resumido

A Etapa 10 está em andamento. As unidades de negócio principais já foram implementadas e commitadas; esta unidade implementa a criação manual de backup. PHPUnit continua não executável localmente por falta da extensão `mbstring`.

### Unidades concluídas (commits)

- `15bfdc8` — Alinhamento semântico ao Modelo Físico V7 (categoria, compromisso, lançamento, efetivação na Conta Principal).
- `c926978` — Testes unitários V7 de compromisso e efetivação.
- `bc9ee40` — Saldo Inicial (`POST /accounts/{id}/initial-balance`).
- `b23565e` — Transferências (criar, efetivar, desfazer).
- `e3ded57` — Desfazer efetivação de compromisso simples.
- `ec53834` — Recorrência: modelo, repositório e criação.
- `4e868bf` — Recorrência: materialização e efetivação de ocorrências.
- `920c4ff` — Dashboard e consultas (`GET /movements`, `/net-worth`, `/dashboard`).
- `b8d863b` — Tema (`GET/PUT /preferences/theme`).
- `9266dfa` — Preparação: `findAllByUser` nos repositórios para exportação.

## Unidade concluída nesta sessão

- O contrato `POST /restorations` agora exige `token` e `confirmation` booleano explícito; confirmação ausente, não booleana ou falsa é rejeitada sem mutação.
- Confirmação verdadeira revalida capability, usuário, expiração, staging e hash sob `UserOperationLock` e retorna `confirmation_accepted`, distinto de `ready_for_confirmation`; não cria snapshot, não inicia restauração e não consome o token.
- Teste unitário focado adicionado para rejeição sem mutação e aceitação após revalidação.

### Evidências desta unidade

- `find src/src -name '*.php' -print0 | xargs -0 -n1 php -l`: passou.
- `cd src && php Tests/Manual/*.php`: passou; todos os testes manuais passaram.
- `git diff --check`: passou.
- PHPUnit não executado: Composer não está instalado/disponível.

### Checkpoint e próxima unidade

- Commit desta unidade: será registrado após a validação final do diff.
- Gaps pendentes: snapshot pré-restauração recuperável, substituição integral, verificação/rollback, retenção e consumo único do token continuam deliberadamente não implementados.
- Próxima Work Unit: implementar a restauração transacional completa somente após preservar obrigatoriamente o snapshot pré-restauração, com rollback e consumo único.

- `POST /sgfp/v1/backups` exige capability `use_sgfp`, captura os dados do usuário em transação, serializa JSON, compacta com gzip, protege com Sodium usando `SGFP_BACKUP_KEY` e envia o arquivo por `wp_mail` ao e-mail cadastrado.
- Restauração, catálogo de backups, snapshot pré-restauração e Etapa 11 não foram iniciados.
- `POST /sgfp/v1/restore-validations` recebe o arquivo, autentica/descompacta, valida versão, proprietário e seções obrigatórias, e retorna resumo com token temporário; não altera dados.
- A validação agora preserva o arquivo em staging privado configurado por `SGFP_BACKUP_DIR`, com nome imprevisível, escrita atômica e confirmação por hash; falha de configuração ou persistência interrompe a validação.
- `POST /sgfp/v1/restorations` agora revalida token, expiração, staging e hash, retornando `ready_for_confirmation`; ainda não substitui dados nem consome o token.
- A captura do backup manual agora adquire e libera `UserOperationLock` por usuário, usando `GET_LOCK`/`RELEASE_LOCK` no adaptador WordPress.
- A preparação (`POST /restorations`) também revalida token, expiração e hash sob `UserOperationLock`; o token permanece disponível até a confirmação efetiva.

### Validações desta sessão

- `find src/src -name '*.php' -print0 | xargs -0 -n1 php -l`: passou.
- `php Tests/Manual/create-account-manual-test.php`: passou.
- `php Tests/Manual/initial-balance-manual-test.php`: bloqueado antes da execução porque o fake `EntryRepository` do teste não implementa `findAllByUser`, método já exigido pela porta existente.

### Validações da unidade seguinte

- Os doubles manuais de `EntryRepository`, `RecurrenceRepository` e `TransferRepository` foram alinhados às portas atuais.
- `php Tests/Manual/*.php`: todos os seis testes manuais passaram.
- Lint PHP completo após a validação preliminar: passou.
- Lint PHP e todos os testes manuais após o staging privado: passaram.
- Lint PHP e todos os testes manuais após a revalidação: passaram.
- Lint PHP e todos os testes manuais após a proteção por lock: passaram.
- Lint PHP e todos os testes manuais após o lock na preparação da restauração: passaram.
- `WpEntryRepository::findAllByUser` e `WpTransferRepository::findAllByUser` foram implementados com escopo obrigatório por usuário e mapeamento dos modelos existentes.
- Lint PHP completo e todos os seis testes manuais após esta unidade: passaram.

## O que falta

### Backup e Restauração (`RF-021`)

Requisitos canônicos (ver `docs/arquitetura/01-arquitetura-da-aplicacao.md`, seção 13):

- Backup manual (`POST /backups`):
  - Coletar todos os dados do usuário (contas, categorias, recorrências, compromissos, transferências, lançamentos, tema).
  - Serializar em JSON, compactar com gzip.
  - Cifrar/autenticar com Sodium (AEAD), chave via `SGFP_BACKUP_KEY` (fallback KMS/secret manager).
  - Gravar em diretório privado fora do document root.
  - Validar o arquivo antes de enviá-lo por e-mail (`wp_mail`).
  - Usar `UserOperationLock` + transação `REPEATABLE READ` para snapshot consistente.

- Restauração (`POST /restore-validations` e `POST /restorations`):
  - Receber arquivo em staging privado, validar tamanho/formato.
  - Decifrar/autenticar, validar versão, proprietário, schema, enums, referências e completude.
  - Retornar resumo + token opaco de uso único.
  - Ao confirmar: adquirir lock, gerar snapshot pré-restauração, validar, substituir integralmente os dados SGFP e o tema, verificar, commit.
  - Preservar snapshot pré-restauração por 24h.

### Revisão final da Etapa 10

- Revisar consistência dos contratos REST com a arquitetura.
- Revisar segurança (autorização, enumeração, erros estáveis).
- Resolver `ISSUE-008` (matriz Regra de Negócio → Requisito) antes do fechamento da rastreabilidade de testes.
- Consolidar testes formais na Etapa 12.

## Decisões pendentes / necessárias

1. **Chave de backup:** onde e como fornecer `SGFP_BACKUP_KEY` no ambiente de execução? (env var, secret manager?)
2. **Diretório de storage privado:** qual caminho físico fora do document root?
3. **Limites:** tamanho máximo do arquivo, limite do anexo de e-mail, política de retenção do snapshot pré-restauração.
4. **E-mail:** confirmar transporte WordPress (`wp_mail`) disponível e configurado.

## Notas técnicas para continuação

- Os repositórios já possuem `findAllByUser` nas entidades necessárias para exportação.
- `findAllByUser` está implementado em `WpTransferRepository` e `WpEntryRepository`, mantendo o escopo por usuário e as convenções de mapeamento existentes.
- Será necessário criar:
  - `Application/Ports/UserOperationLock`
  - `Infrastructure/WordPress/WpUserOperationLock` (usar `GET_LOCK`/`RELEASE_LOCK` do MySQL/MariaDB)
  - `Application/Backup/BackupCodec`, `BackupProtector`, `BackupStore`
  - `Application/Services/BackupService`
  - `Application/Services/RestoreValidationService`
  - `Application/Services/RestoreService`
  - `REST/Controllers/BackupController`
  - `REST/Controllers/RestoreController`
  - Rotas REST correspondentes.
- PHPUnit continua indisponível localmente por `mbstring`; os testes manuais cobrem as funcionalidades implementadas.

## Comando de verificação rápida

```bash
cd src
find src -name '*.php' -not -path '*/vendor/*' -print0 | xargs -0 -n1 php -l
php Tests/Manual/*.php
```

## Próxima ação recomendada

Próxima unidade pequena: definir o consumo único do token dentro de um serviço de restauração transacional, somente após a captura e preservação obrigatória do snapshot pré-restauração.

## Revisão final de encerramento da Stage 10 — 2026-09-12

**Resultado autoritativo:** BLOQUEADA — Stage 10 não fechada; Stage 11 não iniciada.

### Evidência

- Contratos REST confrontados com a arquitetura e os critérios `CA-021.1`–`CA-021.11`.
- Autorização/isolamento inspecionados: `permission_callback`/capability `use_sgfp`, contexto do usuário atual e repositórios com escopo por usuário.
- Backup manual inspecionado: lock por usuário, transação de captura, gzip, AEAD Sodium, limite/chave, staging privado, proprietário e hash.
- `git diff --check`: passou; lint PHP completo: passou; `php Tests/Manual/*.php`: seis testes passaram.
- PHPUnit não executado: `composer` não está instalado/disponível.

### Primeiro bloqueador concreto

`POST /restorations` chama apenas `RevalidateRestorationService::prepare()` e retorna `ready_for_confirmation`. Não existe confirmação executável nem serviço de restauração integral. Não há evidência de snapshot pré-restauração recuperável, substituição integral de dados/tema, verificação transacional, rollback, retenção de 24 horas ou consumo único do token. Isso deixa sem atendimento verificável `CA-021.4`–`CA-021.11`, especialmente `CA-021.6` e `CA-021.9`.

### Próxima ação

Implementar e testar somente o fluxo transacional de confirmação da restauração, com snapshot pré-restauração antes da substituição, retenção, rollback e consumo único do token. Depois repetir a revisão; não iniciar a Stage 11.

## Unidade `wu:db2503f175b44f72add0c010bc64342e` — 2026-09-12

### Trabalho realizado

- Integrado `CapturePreRestorationSnapshotService` ao caminho confirmado de `POST /restorations`.
- A captura ocorre depois da revalidação de capability, usuário, expiração, staging e hash, ainda sob o lock do usuário.
- Criada a variante `captureUnderLock`, evitando aquisição não reentrante do mesmo lock; a captura mantém transação, cifragem, persistência, releitura/verificação de hash e expiração de 24 horas.
- `confirmation_accepted` agora só é retornado se a captura concluir; qualquer falha propaga erro e impede sucesso.
- O snapshot retornado é identificado como `pre_restore` e permanece user-scoped.

### Evidências

- Lint PHP completo: passou.
- Testes manuais: passaram; todos os testes manuais passaram.
- PHPUnit: bloqueado porque a extensão `mbstring` não está disponível no ambiente.
- Arquivos `AGENTS.md` e `PROMPTS-OPENCLAW-SGFP.md` não foram alterados por esta unidade.

### Gaps deliberados

- Substituição integral, verificação/rollback, retenção ativa e consumo único do token permanecem fora do escopo desta unidade.
- Stage 11 não iniciada.

## Unidade `wu:a4e0c2c3128742d795fb0d4acbaa1e29` — 2026-09-12

### Trabalho realizado

- Criada a porta privada `BackupStore` e o adaptador `WpBackupStore`, com diretório `SGFP_BACKUP_DIR`, nome imprevisível, gravação atômica e confirmação por hash; falhas não retornam artefato utilizável.
- Criado `CapturePreRestorationSnapshotService`, reutilizável e user-scoped, que exige `use_sgfp`, captura contas, categorias, recorrências, compromissos, transferências, lançamentos e tema dentro de uma transação sob `UserOperationLock`.
- Snapshot marcado com origem `pre_restore`, comprimido e protegido por AEAD Sodium com `SGFP_BACKUP_KEY`, e persistido com expiração de 24 horas.
- Nenhuma rota, substituição de dados, consumo de token, envio de e-mail, retenção ativa ou Stage 11 foi iniciada.

### Evidências

- Lint PHP completo: passou.
- `php Tests/Manual/*.php`: passou; seis testes manuais passaram.
- `git diff --check`: passou.
- PHPUnit: não executado; Composer/extensão `mbstring` indisponíveis no ambiente, conforme histórico do repositório.

### Gaps e próximo passo

- O serviço ainda não está conectado ao fluxo de confirmação da restauração; isso permanece para a unidade de restauração transacional.
- Commit desta unidade: será registrado após a inspeção final do diff.
- Próximo passo: integrar a captura obrigatória antes da substituição integral, com rollback, verificação e consumo único do token.

## Unidade `wu:0dfaf47b138840c68cc7d8441aeaba15` — 2026-09-12

### Trabalho realizado

- Criada a porta `RestorationTokenClaim` e o adapter `WpRestorationTokenClaim`.
- O claim é user-scoped e bloqueia/atualiza atomicamente o metadata do token, rejeitando token inexistente, expirado ou já consumido.
- A confirmação só consome o token depois de capability, usuário, expiração, staging, hash e snapshot `pre_restore` terem sido validados/concluídos.
- O caminho REST foi conectado ao novo seam; não foram implementadas substituição de dados, rollback, retenção, e-mail ou Stage 11.

### Evidências

- Lint PHP completo: passou.
- `php Tests/Manual/*.php`: passou; todos os testes manuais passaram.
- `git diff --check`: passou.
- PHPUnit não executado: Composer/extensão `mbstring` indisponíveis no ambiente.

## Unidade `wu:d162246d00ea4854a23ce107dee465e6` — 2026-09-12

### Resultado: BLOQUEADA — integração transacional não pode ser afirmada com os contratos atuais

Não foi conectado o claim do token ao `RestoreFromImportPlanService` nem ativada a rota de restauração integral, porque a execução segura exigida não está disponível no runtime atual:

- não existe implementação concreta de `RestorationPersistence`; os seis repositórios existentes expõem somente `save`/consulta, sem substituição integral user-scoped, preservação de IDs lógicos ou remapeamento físico completo das referências;
- `WpRestorationTokenClaim` atualiza `wp_usermeta`, enquanto `WpTransactionManager` controla transações SQL das tabelas SGFP; nenhum contrato ou verificação de infraestrutura garante que `wp_usermeta` seja transacional e participe da mesma unidade atômica;
- o snapshot é persistido por `WpBackupStore` como artefato de filesystem, fora da transação SQL, sem catálogo transacional para garantir atomicidade conjunta.

Consumir o token antes da substituição poderia perder a claim após rollback; consumir depois poderia confirmar a substituição sem garantir claim única. Usar `save` para substituir faria merge e não satisfaria o contrato. Implementar adapters, catálogo ou remapeamento aqui exigiria inventar uma fronteira de persistência não aprovada. Conforme a instrução da unidade, a execução parou e nenhum arquivo protegido foi modificado; Stage 11, catálogo/retention, e-mail e regras adicionais permanecem fora do escopo.

### Evidências

- `git diff --check`: a executar após este registro.
- Nenhuma alteração em `src/`.
- Os arquivos protegidos dirty preexistentes (`AGENTS.md`, `PROMPTS-OPENCLAW-SGFP.md`, `docs/api/README.md` e `docs/governanca/continuidade-de-contexto.md`) foram preservados.

## Unidade `wu:ec348a5ab96c4e4fb6409fa80930dd3a` — 2026-09-12

### Trabalho realizado

- O `RestoreFromImportPlanService` agora exige `RestorationTokenClaim` e token válido.
- A claim ocorre dentro do mesmo callback de `TransactionManager::transactional`, antes de `replace`; falha de token, substituição ou verificação propaga exceção e aciona rollback da claim e dos dados.
- O contrato continua limitado ao plano validado e a dados user-scoped; nenhum SQL de DDL/implicit-commit foi introduzido.
- O lock permanece responsabilidade do chamador (`RevalidateRestorationService` mantém o `UserOperationLock` até o retorno), e os arquivos dirty protegidos foram preservados.

### Evidências

- `php -l src/src/Application/Services/RestoreFromImportPlanService.php`: passou.
- `git diff --check`: passou.
- PHPUnit não executado: Composer/extensão `mbstring` indisponíveis no ambiente.

### Limites

- A composição REST ainda não executa a substituição integral porque não existe implementação concreta de `RestorationPersistence`; catálogo, retenção, e-mail e Stage 11 permanecem fora do escopo.
## Unidade `wu:b2387be4f40a4106b2b4e0046f7c6acc` — 2026-09-12

### Resultado: BLOQUEADA — integração segura não demonstrável

- Criado `WpRestorationPersistence` com DML somente nas tabelas canônicas de `TableNames`, exclusão reversa user-scoped, importação em ordem de dependência, remapeamento lógico→físico, verificação de contagens/referências/ownership e aplicação do tema.
- A unidade não foi ligada ao fluxo REST nem recebeu checkpoint, porque o tema usa `wp_usermeta` por `update_user_meta()` e o claim usa o mesmo mecanismo, enquanto a transação do executor cobre somente SQL SGFP. O runtime não fornece contrato de transação/cache comum que prove rollback e atomicidade do conjunto.
- Conectar o adapter agora permitiria consumir/alterar metadata e dados SGFP sem garantia de unidade atômica; isso violaria o requisito de commit único. Não foi adicionado catálogo, retenção, e-mail, Stage 11 ou regra nova.

### Evidências

- `php -l src/src/Infrastructure/WordPress/WpRestorationPersistence.php`: passou.
- `git diff --check`: passou.
- PHPUnit não executado: Composer/extensão `mbstring` continuam indisponíveis no ambiente.
- Arquivos dirty protegidos (`AGENTS.md`, `PROMPTS-OPENCLAW-SGFP.md`, `docs/api/README.md`, `docs/governanca/continuidade-de-contexto.md`) preservados.
## Work Unit `wu:6fb1aa4aa6b441528e2300c94ae3e77e` — restauração transacional

- **Causa raiz:** a persistência usava `update_user_meta()`/`get_user_meta()` dentro da transação, sem garantir engine de `wp_usermeta`, sem preflight comum e sem propagação de falhas de `START`/`COMMIT`/`ROLLBACK`; o tema podia atravessar a fronteira SQL via cache/API.
- **Solução aplicada:** `WpRestorationPersistence` agora valida `InnoDB` via `information_schema.TABLES` para `wp_usermeta`, token e todas as tabelas SGFP antes de mutar; grava o tema `sgfp_theme` diretamente com `SELECT ... FOR UPDATE` + `INSERT/UPDATE`; mantém somente DML user-scoped. Claim de token e transaction manager propagam erros críticos. A invalidação de cache e a leitura SQL do tema ocorrem somente após commit.
- **Evidência:** `php -l` passou nos quatro arquivos alterados; `git diff --check` passou. PHPUnit não iniciou por ausência da extensão PHP `mbstring`. PHPStan executou e reportou apenas símbolos WordPress não disponíveis no bootstrap (`get_current_user_id`, `current_user_can`, `get_user_meta`, `update_user_meta`); o novo cache call foi tornado dinâmico.
- **Riscos residuais:** testes de sucesso/rollback/concurrency/engine incompatível/implicit commit não puderam ser demonstrados neste ambiente sem PHPUnit funcional e sem banco WordPress conectado. Nenhuma alternativa especulativa foi introduzida.

## Work Unit `wu:3a58023da79e42a29ae6a2b5604ea93a` — correção `login_redirect` e artefato de reteste

- **Causa:** o callback nativo `login_redirect` pode receber `WP_Error` como terceiro argumento durante `/wp-login.php`, mas `WordPressLogin::safeRedirect()` declarava somente `WP_User`, causando `TypeError` antes da validação do destino.
- **Correção:** o contrato agora aceita `WP_User|WP_Error`; ao receber `WP_Error`, retorna o fallback seguro do portal. A lógica existente para `WP_User` foi preservada.
- **Teste:** adicionado caso unitário específico para `WP_Error`; casos existentes de destino externo/interno preservados.
- **Arquivos funcionais:** `src/src/Frontend/WordPressLogin.php`, `src/src/Tests/Unit/AuthFrontendTest.php`.
- **Artefato:** ZIP limpo de reteste ambiental gerado em `dist/`, com vendor de produção isolado, root único, `src/Plugin.php` achatado e allowlist runtime; sem merge, deploy, instalação, commit ou limpeza do WIP.
- **Evidências:** PHPUnit focado `AuthFrontendTest`: 7 testes/15 assertions, PASS; PHPUnit completo: 67 testes/161 assertions, PASS; lint PHP da fonte e do ZIP: PASS; `unzip -t`: PASS; `class_exists('SGFP\\Plugin')`: PASS; `SGFP\\Plugin::boot()` com stubs mínimos de hooks WordPress: PASS; `git diff --check`: PASS; ZIP `dist/sgfp-login-redirect-wp-error-20260918-033453.zip`, 125069 bytes, SHA-256 `835441919549cba12a0aa4d82041305f7bc0c639dc14a68da530d39e141ea420`; branch `feat/stage-11-web-interface`, HEAD `4ff7168832dff76a659bba9c886a9e19371b960a`.
# HISTORICAL RECORD — 18/09/2026 — reconciliação técnica SGFP / Adaptive

## Resultado da reconciliação técnica SGFP / Adaptive

- **Branch/HEAD verificados:** `feat/stage-11-web-interface` / `4ff7168`.
- **Escopo desta unidade:** somente este `HANDOFF.md`; nenhum arquivo de produto foi alterado. O WIP existente do checkout foi preservado integralmente.
- **Estado SGFP:** `READY_FOR_ENVIRONMENTAL_RETEST`. As correções funcionais e de segurança aceitas nas unidades U6-R e U7-R-FIX permanecem respaldadas por Result Stores e manifests íntegros, incluindo PHPUnit completo reportado como aprovado nas respectivas unidades.
- **Gates ambientais ainda necessários:** WordPress + MySQL/MariaDB real, browser, acessibilidade, responsividade e fluxos reais de autenticação/provisionamento/uso. Este checkpoint não declara esses gates executados.

### Evidências Adaptive aceitas

- U6-R: `.adaptive/runs/348112dd40a840748b38c6739d2e2e3d/U6-R/d883e30e48554c3cb3b5b77ae8184d2e/result.txt` — `COMPLETE`, PHPUnit completo reportado: 89 testes / 202 assertions.
- U7-R-FIX: `.adaptive/runs/348112dd40a840748b38c6739d2e2e3d/U7-R-FIX/0d45eef456ff4180bc8f90500d594bda/result.txt` — `COMPLETE`, PHPUnit completo reportado: 90 testes / 221 assertions.
- Os manifests correspondentes declaram os Result Stores e foram conferidos junto aos `result.txt`; não há evidência aceita de corrupção, conflito ou alteração concorrente do checkout nesta unidade.

### Incidente / backlog operacional do Adaptive

Permanece registrado, sem bloquear a prontidão técnica do SGFP:

- ausência de leases/checkpoints históricos suficientes;
- dificuldade de reconciliar U6/U7 corretivas com as unidades originais;
- evento `worker_recovered` não suportado;
- impossibilidade de provar ausência histórica de worker órfão.

Esses pontos são dívida de observabilidade/reconciliação do Adaptive. Não foram corrigidos nesta unidade e não autorizam ZIP, merge, deploy, instalação remota ou recuperação adicional.

### Gates locais desta unidade

- PHPUnit: **PASS — 90 testes / 221 assertions / 0 falhas / 0 erros / 0 skipped**, via `src/vendor/bin/phpunit -c src/phpunit.xml.dist`.
- PHP lint: **PASS** nos PHPs alterados.
- `node --check` nos JavaScript alterados: **PASS**.
- `git diff --check`: **PASS**.

ADAPTIVE_WORK_STATUS: COMPLETE
ADAPTIVE_BLOCKER_TYPE: NONE
ADAPTIVE_UNMET_CRITERIA: environmental WordPress+MySQL/MariaDB, browser/a11y/responsiveness and real-flow gates remain pending; Adaptive historical operational debt recorded above
# HISTORICAL / NON-CURRENT — CURRENT AUTHORITATIVE CHECKPOINT anterior — 18/09/2026 — fan-in/revisão independente da Etapa 11

## Resultado da revisão `WU-FANIN-VERIFY-REVIEW`

- **Estado inspecionado:** branch `feat/stage-11-web-interface`, working tree dirty intencional preservado; revisão vinculada ao estado local observado em 18/09/2026.
- **Validações locais:** PHPUnit **96 testes / 257 assertions PASS**; lint PHP PASS; `node --check src/assets/app.js` PASS; `git diff --check` PASS.
- **Achado bloqueante para aceite:** `src/assets/app.js` envia `category_id: null` no PATCH de edição quando o compromisso fica sem categoria. O serviço/DTO suportam `null`, mas `src/src/REST/Routes.php` ainda declara `category_id` da rota `PATCH /commitments/{id}` somente como `integer`. No WordPress, o sanitizador pode rejeitar o payload antes do controller, impedindo a remoção de categoria.
- **Classificação:** `REQUEST CHANGES` / required revision — alinhar o contrato PATCH ao contrato de criação (tipo anulável) e adicionar teste de composição/sanitização para edição com `category_id: null`; depois executar nova revisão independente no HEAD corrigido.
- **Segurança:** testes de ownership e fronteira de capability passaram; não foram observados, nesta revisão, vazamentos de mensagens internas nos controllers revisados. A autorização real, REST autenticado e isolamento WordPress/MySQL permanecem não verificados neste ambiente.
- **Limites ambientais:** não há evidência local de execução em WordPress/MySQL hospedado nem de browser/UX real; não tratar os passes unitários como substitutos desses gates.

## Atualização da revisão independente — 18/09/2026

- O HEAD revisado permanece sem a correção necessária: `PATCH /commitments/{id}` ainda declara `category_id` como `integer`, enquanto o DTO/serviço e a interface aceitam `null` para remover a categoria.
- Evidências reexecutadas: dependências WU-COMMITMENTS e WU-ACCOUNTING-UI com hashes dos Result Stores conferentes; PHPUnit **96 testes / 257 assertions PASS**; lint PHP PASS; `node --check src/assets/app.js` PASS; `git diff --check` PASS.
- A composição de rotas cobre nulabilidade na criação, mas não há teste de sanitização/contrato para o PATCH anulável. Aceite permanece `REQUEST CHANGES`.
- Nenhum arquivo de implementação foi alterado nesta revisão; o WIP foi preservado. WordPress/MySQL, REST autenticado e browser/UX continuam não verificados.

## Atualização da revisão independente — 18/09/2026 — reteste após lotes dependentes

- **Estado revisado:** branch `feat/stage-11-web-interface`, HEAD `4ff7168`, working tree dirty intencional preservado.
- **Dependências aceitas:** `WU-COMMITMENTS`, `WU-ACCOUNTING-UI` e `WU-PATCH-CATEGORY-NULLABLE`; manifests e hashes dos Result Stores conferidos.
- **Validações locais:** PHPUnit **98 testes / 262 assertions PASS**; lint PHP completo PASS; `node --check src/assets/app.js` PASS; `git diff --check` PASS.
- **Revisão de especificação/padrões:** o contrato PATCH anulável agora está alinhado em `Routes.php`, com sanitização de `null` preservada e teste `RoutesComposition`; ciclos de efetivação/desfazer reutilizam lançamento; saldo atual/projetado e sinais de movimentos estão cobertos pelos testes reportados.
- **Segurança:** capability/ownership server-side, nonce REST, escaping de dados dinâmicos e fronteira de erro público foram inspecionados; testes de ownership e `PublicError` passaram. Nenhuma vulnerabilidade confirmada no escopo local.
- **UI:** estrutura semântica, labels, foco visível, reflow CSS, tabela com overflow local e redução de movimento foram inspecionados; `node --check` passou. Browser, leitores de tela e viewports reais não foram executados.
- **Resultado:** **APPROVE WITH NOTES / COMPLETE para a matriz local**. Permanecem gates ambientais: WordPress + MySQL/MariaDB real, REST autenticado, browser/a11y/responsividade e fluxos reais de login/provisionamento. Não houve package/deploy.
# HISTORICAL / NON-CURRENT — CURRENT AUTHORITATIVE CHECKPOINT anterior — 2026-09-19 — pacote limpo da correção YYYY-MM-01

- Empacotamento executado sobre a working tree intencionalmente dirty da branch `feat/stage-11-web-interface`, HEAD `4ff7168832dff76a659bba9c886a9e19371b960a`; o WIP preexistente foi preservado.
- Correção verificada: validação estrita do mês `YYYY-MM-01` no serviço de dashboard; evidência do fallback direto autorizado: PHPUnit **99 testes / 264 assertions PASS**.
- Artefato novo, limpo e pronto para reteste ambiental: `release/sgfp-etapa-11-runtime-20260919T164036Z-clean.zip` — **116311 bytes**, SHA-256 `a03823e92080e81d331e13011131e15ebcd5777176cd3a7965912773b47562a1`.
- Verificações do pacote: vendor de produção isolado (`composer install --no-dev --optimize-autoloader`), raiz ZIP única `sgfp/`, `src/Tests/` e manifests Composer ausentes, lint PHP PASS, `class_exists('SGFP\\Plugin')` PASS, smoke de `Plugin::boot()` PASS, integridade `unzip -t` PASS, `git diff --check` PASS.
- Orquestração de projeto existente pausada: `154192e6efc448e2af4b127b5c8ac2cd`.
- Este pacote é somente para reteste; não é release, não conclui a Etapa 11 e não foi instalado, ativado, publicado ou enviado.
- Lacunas restantes: validação real em WordPress/MySQL autenticado, ativação/provisionamento, fluxos REST completos e validação browser/UX permanecem pendentes.
