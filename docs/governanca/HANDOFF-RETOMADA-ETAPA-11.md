# Handoff de retomada — Etapa 11 / Interface Web

**Data:** 2026-09-15  
**Estado autoritativo:** revisão fan-in concluída com REQUEST CHANGES; frontend/backend ainda não aptos para integração.
**Branch:** `feat/stage-11-web-interface`  
**HEAD auditado:** `4ff7168832dff76a659bba9c886a9e19371b960a` (veredito válido somente para este HEAD; qualquer alteração material exige nova revisão).

## 1. Guarda de estado e proteção

O checkout foi preservado sem restauração, aplicação de stash ou alteração destrutiva. A verificação do WIP executou lint/unit/JS/diff-check; a integração WordPress/MySQL e o navegador permanecem não verificados. O estado dirty observado contém alterações de backend, frontend e documentação:

```text
 M src/src/Application/Services/
 M src/src/REST/
?? src/assets/
?? src/src/Frontend/
?? docs/governanca/HANDOFF-RETOMADA-ETAPA-11.md
```

Backup externo do frontend: `/home/ariel/backup-sgfp-frontend-2026-09-15`.

O stash protegido permanece:

```text
stash@{0}: On feat/stage-11-web-interface: backup-wip-antes-reconciliacao-stage11-2026-09-15
```

É proibido aplicar, fazer `pop`, `drop` ou restaurar globalmente esse stash. A retomada deve preservar `src/assets/`, `src/src/Frontend/`, todos os arquivos WIP e o stash; qualquer reaproveitamento exige comparação seletiva contra a baseline.

## 2. Baseline V1 vigente

Fonte normativa: [`baseline-v1-simplificada-2026-09-14.md`](baseline-v1-simplificada-2026-09-14.md). A V1 usa exatamente uma **Minha Conta**, saldo derivado, compromissos, lançamentos, recorrências, categorias, dashboard, tema, reset de perfil, exclusão de conta e backup/restauração por ZIP local. Transferências, múltiplas contas/Principal/Secundária, Patrimônio Total, e-mail como entrega de backup e integrações externas estão fora da V1.

## 3. Auditoria aceita

**Orchestration ID:** `8f69a086d62c4232aac5fe9a6d968f0a`  
**Resultado:** aceito; quatro WUs concluídos (`baseline-and-api-facts`, `frontend-compatibility-audit`, `stash-differential-audit`, `gap-synthesis`). A auditoria foi estática/read-only: sem build, testes, navegador ou runtime.

### Comparação consolidada frontend/API

| View/ação | Endpoint usado | Estado V1 | Observação |
|---|---|---|---|
| Visão Geral | `GET /dashboard?month=` | Compatível | Dashboard previsto. |
| Lançamentos | `GET /movements?month=` | Compatível | Movimentos previstos. |
| Compromissos/listagem | `GET /commitments?month=` | Compatível com backend ausente | Frontend chama rota que o HEAD não registra. |
| Compromissos/criação | `POST /commitments` | Compatível com risco | Cliente converte dinheiro para `Number`, mas V1 exige string decimal. |
| Compromissos/efetivar | `POST /commitments/{id}/settle` | Bloqueado | Contrato define `/effectuation`. |
| Compromissos/desfazer | `POST /commitments/{id}/undo-effectuation` | Compatível | Rota prevista. |
| Compromissos/excluir | `DELETE /commitments/{id}` | Compatível com backend ausente | Rota prevista, não registrada no HEAD. |
| Recorrências | `GET /recurrences` | Legacy/indefinido | GET de coleção não está consolidado na V1. |
| Ocorrência | `GET /recurrences/{id}/occurrences/{month}` | Legacy/risco | Forma não consolidada; backend pode materializar em GET. |
| Transferências | `/transfers...` | Legacy | Fora da V1; view ainda navegável. |
| Contas/listagem | `GET /accounts` | Legacy/bloqueado | V1 exige `/account` singular. |
| Contas/criação | `POST /accounts` | Legacy/bloqueado | Contas adicionais fora da V1. |
| Contas/renomear | `PUT /accounts/{id}` | Bloqueado | V1 exige `PATCH /account`. |
| Patrimônio | `GET /net-worth` | Legacy | Fora da V1. |
| Saldo inicial | `POST /accounts/{id}/initial-balance` | Bloqueado | V1 exige `/account/initial-balance`. |
| Categorias | `GET/POST /categories` | Compatível | Rotas previstas. |
| Categoria editar/excluir | `PUT/DELETE /categories/{id}` | Parcial | V1 exige `PATCH` para edição. |
| Tema | `GET/PUT /preferences/theme` | Compatível | Valores `light`/`dark`. |
| Backup | `POST /backups` | Compatível com risco | V1 exige ZIP local; HEAD retorna base64 JSON. |
| Validação/restauração | `/restore-validations`, `/restorations` | Compatível | Sequência validar → confirmar prevista. |

Achados funcionais do shell: `+ Abrir editor` não possui handlers funcionais nem atualiza `aria-expanded`; a navegação não atualiza `is-active`/`aria-current`. Há ainda riscos de foco modal, `scope="col"`, tratamento de falha de tema e precisão monetária.

## 4. Gaps REST exatos antes da integração

1. Adicionar `GET /account` e retirar/substituir `GET /accounts`.
2. Adicionar `PATCH /account` e retirar/substituir `PUT/PATCH /accounts/{id}` e a closure singular concorrente.
3. Adicionar `POST /account/initial-balance` e retirar/substituir `POST /accounts/{id}/initial-balance`.
4. Adicionar `GET /commitments`, `GET/PATCH/DELETE /commitments/{id}`.
5. Adicionar ou renomear para `POST /commitments/{id}/effectuation` em relação a `/settle`.
6. Adicionar `POST /profile-reset-validations` e `POST /account-deletion-validations`.
7. Tornar a leitura de ocorrência não mutante e alinhar sua rota/contrato normativo.
8. Corrigir edição de categoria para `PATCH`.
9. Padronizar dinheiro como string decimal, mês como `YYYY-MM-01`, envelopes `{items,pagination}`, erros `{code,message,details,correlation_id}` e `correlation_id`.
10. Confirmar e alinhar backup como ZIP local, em vez de base64 JSON.

## 5. Próxima retomada segura

Fixar primeiro o contrato REST/OpenAPI e o modelo de conta única; depois alinhar backend; somente então reconciliar o frontend preservado e executar a verificação apropriada. Não importar o stash inteiro: testes manuais de dashboard, saldo inicial, ocorrência e desfazer efetivação podem ser avaliados seletivamente; transferências, múltiplas contas, patrimônio e remoções de reset/exclusão permanecem fora da V1.

## 6. Fan-in atual e gate

O relatório consolidado está em [`relatorio-fan-in-etapa-11-2026-09-15.md`](relatorio-fan-in-etapa-11-2026-09-15.md). O gate atual é **REQUEST CHANGES** pelos achados F-01/F-02 (contrato REST incompleto/inconsistente) e pelos gates de segurança/integração ainda não executados. A próxima ação segura é corrigir o contrato no escopo de implementação apropriado e fazer nova revisão independente no novo HEAD; não reutilizar este veredito após mudanças.

### Evidências

- `src/src/REST/Routes.php:173-455` e controllers/DTOs referenciados.
- `src/src/Frontend/Frontend.php:20-67`, `src/assets/app.js`, `src/assets/app.css`.
- `docs/arquitetura/01-arquitetura-da-aplicacao.md:258-303`.
- Resultados aceitos em `.adaptive/runs/8f69a086d62c4232aac5fe9a6d968f0a/`.
