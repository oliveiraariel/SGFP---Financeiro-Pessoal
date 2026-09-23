# Status real — Etapa 11 — Interface Web V1

**Auditado em:** 2026-09-16 — passe de fechamento local  
**Checkout:** `feat/stage-11-web-interface` em `4ff7168832dff76a659bba9c886a9e19371b960a`, com WIP local autoritativo.  
**Preservação:** `stash@{0}: backup-wip-antes-reconciliacao-stage11-2026-09-15` e `/home/ariel/backup-sgfp-frontend-2026-09-15` confirmados presentes.

Este documento descreve o código e os testes observados no checkout, não substitui a baseline V1 nem torna E2E não executado em evidência de aprovação.

| Área | Estado | Evidência no código | Arquivos | Testes existentes | Falta fazer |
|---|---|---|---|---|---|
| Navegação | PARTIAL | sete itens V1 e `aria-current`; painel modal funcional | `Frontend.php`, `app.js` | sintaxe JS | editor é apenas pré-visualização; falta navegador/a11y |
| Visão Geral | IMPLEMENTED BUT UNVERIFIED | `GET /dashboard?month=`, cartões | `app.js`, `ReportingController.php` | `ReportingServicesTest` | prova REST/browser |
| Lançamentos | IMPLEMENTED BUT UNVERIFIED | `GET /movements?month=`, envelope `items` | `app.js`, `ReportingController.php` | indireta | prova de consistência visual com saldo |
| Compromissos | PARTIAL | GET item/coleção, POST, PATCH, DELETE lógico, efetivar/desfazer; PATCH rejeita estado `EFETIVADO` | `Routes.php`, controller/services/repository, `app.js` | create, settle, undo, delete, ownership/update | contrato REST/ownership integrado e browser |
| Recorrências | IMPLEMENTED BUT UNVERIFIED | ocorrência é carregada e renderizada; ações efetivar/desfazer usam a resposta | `Routes.php`, `RecurrenceController.php`, `app.js` | `RecurrenceOccurrenceServicesTest` | UX/browser e prova REST |
| Minha Conta | IMPLEMENTED BUT UNVERIFIED | GET/PATCH `/account`, saldo derivado, saldo inicial | `AccountController.php`, `app.js` | saldo inicial/reporting | ownership e integração WP |
| Categorias | IMPLEMENTED BUT UNVERIFIED | API CRUD, UI listar/criar/editar/excluir e quick-add no compromisso | controller/services/repository, `app.js` | create/seed; ownership coberto por serviço | browser/a11y e prova REST |
| Configurações | PARTIAL | tema, backup, restore, reset/delete conectados | `app.js` | tema | confirmação em duas etapas e browser |
| Backup | IMPLEMENTED BUT UNVERIFIED | resposta ZIP binária com headers | `BackupController.php` | backup/restore unitários | download real WP |
| Restore | IMPLEMENTED BUT UNVERIFIED | validar → token → confirmar | routes/controller/app | planner/revalidate/decoder | upload/snapshot E2E |
| Reset | IMPLEMENTED BUT UNVERIFIED | frase e transação/reprovisionamento | `ProfileController.php`, service | `ResetProfileServiceTest` | fluxo WP/nonce real |
| Delete account | IMPLEMENTED BUT UNVERIFIED | purge transacional antes de apagar identidade | `ProfileController.php`, service | `DeleteAccountServiceTest` | fluxo WP, retry e sessão pós-delete |
| Auth/WordPress | PARTIAL | nonce, `same-origin`, capability, shortcode/enqueue | `Frontend.php`, controllers, `Plugin.php` | unitários | testes de nonce/capability reais |
| Provisionamento | IMPLEMENTED BUT UNVERIFIED | `user_register` e fallback idempotente no login | `Plugin.php`, provision service | `ProvisionUserServiceTest`, seed | E2E WordPress |
| REST contracts | PARTIAL | rotas V1 presentes; respostas heterogêneas | `Routes.php`, controllers | unitários parciais | envelopes consistentes/correlation id/contract probes |
| Ownership/autorização | PARTIAL | `current_user_can`, `WpUserContext`, repositórios filtram usuário | routes/services/repositories | parcial por mocks | testes cross-user e IDs inválidos |
| Decimal | PARTIAL | borda REST exige string decimal; validação de zero não depende de conversão; domínio, cálculos e persistência ainda usam `float`/`%f` | DTOs, modelos, serviços, repositórios | validações unitárias parciais | migração monetária exata ponta a ponta |
| Error envelopes | PARTIAL | cliente aceita formas variadas; API emite várias formas | `app.js`, controllers | nenhum contrato | padronizar sucesso/erro |
| Loading/empty/errors | IMPLEMENTED BUT UNVERIFIED | loading, empty state, retry e status em formulários | `app.js`, CSS | sintaxe JS | browser/a11y |
| CSS/responsividade | IMPLEMENTED BUT UNVERIFIED | breakpoints 760/520/430 e tabela rolável | `app.css` | nenhum | revisão visual/browser |

## Mapa REST atual usado pelo frontend

| Tela | Ação | Método | Endpoint | Payload | Resposta esperada pelo cliente | Backend real | Estado |
|---|---|---|---|---|---|---|---|
| Visão Geral | carregar | GET | `/dashboard?month=YYYY-MM-01` | — | objeto de saldos | sim | parcial/E2E pendente |
| Lançamentos | listar | GET | `/movements?month=YYYY-MM-01` | — | `items[]` | sim | parcial/E2E pendente |
| Compromissos | listar/criar | GET/POST | `/commitments` | mês / dados com decimal string | `{items,pagination}` / item | sim | parcial |
| Compromissos | efetivar/desfazer/excluir | POST/POST/DELETE | `/{id}/effectuation`, `/undo-effectuation`, `/{id}` | — | item/status/204 | sim | parcial |
| Recorrências | abrir | GET | `/recurrences/{id}/occurrences/{month}` | — | item | sim | resposta ainda descartada pela UI |
| Minha Conta | carregar/editar/saldo inicial | GET/PATCH/POST | `/account`, `/account/initial-balance` | nome; decimal string | objeto conta/item | sim | parcial/E2E pendente |
| Categorias | listar/criar/excluir | GET/POST/DELETE | `/categories`, `/{id}` | nome | `{items,pagination}`/item/204 | sim | UI parcial |
| Configurações | tema/backup/restore/reset/delete | GET/PUT/POST/POST/POST/DELETE | preferências, backups, restore, profile, account-access | conforme ação | objeto, ZIP, token ou status | sim | parcial/E2E pendente |

## Anti-regressão

Não há chamadas runtime do frontend a `/accounts`, `/transfers` ou `/net-worth`. Ocorrências de `PRINCIPAL`, transferências e contas antigas em `SchemaMigrator.php` são migração histórica; não são funcionalidades V1 expostas.

## Passe de hardening local — 2026-09-16

| Item | Estado | Evidência |
|---|---|---|
| Decimal exato | PARTIAL | Ainda existem `float`, conversões `(float)`, `number_format` sobre valores numéricos e formatos `%f` em modelos, cálculos, saldo inicial, repositórios e restauração. Não foi declarado concluído. |
| Envelopes REST | PARTIAL | O frontend aceita `items`, objeto direto e múltiplas formas de `error`; controllers ainda emitem envelopes heterogêneos. Não foi introduzida abstração parcial sem contrato validado. |
| Security/ownership local | IMPLEMENTED BUT UNVERIFIED | Serviços filtram por usuário/capability e testes cobrem compromisso, categoria e conta cross-user; WordPress/REST real, recorrência, movimentos, backup e upload permanecem sem integração local completa. |
| Anti-regressão | DONE (local) | Busca runtime não encontrou chamadas V1 ativas para `/accounts`, `/transfers` ou `/net-worth`; ocorrências históricas permanecem em migração/documentação. |

### Testes adicionados neste passe

- `UpdateCommitmentServiceTest`: compromisso de outro usuário não é atualizado; compromisso efetivado retorna `409`.
- `OwnershipBoundaryTest`: categoria de outro usuário não pode ser renomeada/excluída; conta de outro usuário não pode receber saldo inicial.

### REST e segurança — pendências explícitas

As ações destrutivas existentes continuam exigindo confirmação textual exata e os serviços usam o `UserContext`/repositórios com `userId`. Ainda falta uma verificação integrada do nonce/capability WordPress e dos fluxos de upload, restore, backup e exclusão em ambiente WordPress real. A rota de exclusão exposta pelo código é `/account-access`; ela não foi renomeada para `/account-delete` sem decisão/contrato correspondente.

## Evidência local desta auditoria

- `composer validate`: aprovado.
- PHP lint de todos os arquivos PHP em `src/src`: aprovado.
- `node --check assets/app.js`: aprovado.
- PHPUnit após este passe: **50 testes, 121 asserções, 0 falhas, 0 erros, 1 warning** histórico (`SettleCommitmentServiceTest`). Este passe adicionou cinco cenários de ownership/estado.
- `git diff --check`: aprovado.

## Ordem de continuidade

1. Uniformizar envelopes REST e tratar precisão monetária como mudança transversal separada.
2. Ampliar testes locais de ownership para conta, categoria e ações destrutivas; validar restore/upload.
3. Executar revisão de segurança e anti-regressão formal.
4. Executar E2E WordPress/MySQL e browser quando o ambiente existir; então realizar novo fan-in independente.
