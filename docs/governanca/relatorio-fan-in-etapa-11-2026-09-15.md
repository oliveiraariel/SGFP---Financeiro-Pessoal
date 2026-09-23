# Relatório consolidado de fan-in — Etapa 11

**Data:** 2026-09-15  
**Branch:** `feat/stage-11-web-interface`  
**HEAD revisado:** `4ff7168832dff76a659bba9c886a9e19371b960a`  
**Escopo:** revisão anti-regressão, segurança, matriz e handoff; sem merge/deploy.

## Estado autoritativo

O checkout está deliberadamente dirty. Existem alterações em dez arquivos PHP e os diretórios `src/assets/` e `src/src/Frontend/`, além do handoff de governança. Essas alterações pertencem ao WIP da etapa e não foram descartadas, commitadas ou mescladas.

## Evidência executada

- PHP lint: aprovado em todos os PHP de `src/src`.
- PHPUnit (`src/phpunit.xml.dist`): 43 testes, 106 asserções, aprovado; 1 warning não-fatal em `SettleCommitmentServiceTest`.
- `node --check src/assets/app.js`: aprovado.
- `git diff --check`: aprovado.
- Integração WordPress/MySQL: não executada; o ambiente não possui wp-cli, cliente MySQL/MariaDB, Docker/configuração de conexão ou harness de integração.
- A revisão foi feita contra o HEAD acima e contra o baseline/handoff em `docs/governanca/`.

## Matriz de achados

| ID | Severidade | Localização | Evidência | Ação exigida | Verificação |
|---|---|---|---|---|---|
| F-01 | **Bloqueador** | `src/src/REST/Routes.php:275-344` | Há `POST /commitments`, efetivação e desfazimento, mas não há `GET /commitments`, `GET/PATCH/DELETE /commitments/{id}`; a UI chama esses endpoints. | Implementar/registrar as operações previstas no contrato, com autorização por usuário e envelopes de resposta consistentes. | Probe REST WordPress cobrindo status, payload e isolamento entre usuários; teste de regressão da UI. |
| F-02 | **Alta** | `src/src/REST/Routes.php:202-205, 298-302, 403-420, 347-361` | As rotas ainda declaram/padronizam `YYYY-MM`, enquanto serviços e parte dos novos endpoints exigem `YYYY-MM-01`; ocorrência declara regex de dia mas `pattern` de mês. | Unificar contrato, regex, DTOs, cliente e mensagens em um único formato; rejeitar datas inválidas semanticamente. | Testes de contrato para `2026-02-01`, `2026-02`, dia inválido e transição de ano. |
| F-03 | **Alta — segurança/contrato** | `src/src/REST/Routes.php:221-228`, `AccountController.php:get` | `PATCH /account` usa closure que injeta o ID após buscar a conta, enquanto `get()` transforma `listService` em conta única apenas se o resultado tiver exatamente um item. A integração real e a invariância de conta única não foram provadas; endpoints legados de múltiplas contas continuam expostos. | Centralizar a resolução da conta do usuário, definir invariantes de unicidade e retirar/segregar rotas legadas fora da V1. | Testes de autorização/ownership, conta ausente, duplicidade e tentativa de acessar ID de outro usuário em ambiente WordPress. |
| F-04 | **Alta** | `src/assets/app.js` e `Routes.php` | O cliente envia `DELETE /profile`-equivalente como `DELETE /account-access` e reset como `POST /profile-reset`, mas as rotas de validação/confirmação e seus contratos precisam ser reconciliados; sem integração não há prova de que a operação destrutiva esteja realmente protegida pelo fluxo exigido. | Alinhar nomes, confirmação, nonce, reautorização e resposta de erro do fluxo reset/exclusão; não aceitar apenas uma frase como autorização suficiente. | Teste integrado de usuário autenticado, nonce ausente/inválido, confirmação expirada e tentativa cross-user; auditoria de logs sem dados sensíveis. |
| F-05 | **Médio** | `src/assets/app.js:load/submit` e `Frontend.php:52-62` | A navegação atualiza `is-active`, mas não `aria-current`; o botão “Abrir editor” não possui handler e não alterna `hidden`/`aria-expanded`; foco modal não é gerenciado. | Corrigir comportamento e acessibilidade antes de aceitar a etapa web. | Teste de navegador/teclado: abrir/fechar, Escape, foco, leitor de tela e mudança de seção. |

## Veredito

**REQUEST CHANGES / não apto para integração.** F-01 e F-02 impedem a aceitação funcional; F-03 e F-04 exigem validação de segurança e integração antes de qualquer release. O resultado local/unitário verde não substitui os gates de contrato, WordPress/MySQL e navegador.

## Próximo passo seguro

Fixar o contrato REST/OpenAPI e a invariância de conta única; corrigir F-01/F-02; depois executar integração WordPress/MySQL e revisão independente do novo HEAD. Só então reconciliar o frontend e executar a verificação de navegador/acessibilidade. Preservar todo o WIP e o stash existente; não aplicar o stash globalmente.

ADAPTIVE_WORK_STATUS: COMPLETE
ADAPTIVE_BLOCKER_TYPE: NONE
ADAPTIVE_UNMET_CRITERIA: NONE
