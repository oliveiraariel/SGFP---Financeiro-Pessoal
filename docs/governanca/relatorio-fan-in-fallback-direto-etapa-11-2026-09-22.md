# Fan-in e review do fallback direto — Etapa 11 — 22/09/2026

**Horário de vinculação:** 2026-09-22T15:09:54-03:00  
**Branch / HEAD:** `feat/stage-11-web-interface` / `4ff7168832dff76a659bba9c886a9e19371b960a`  
**Estado Git:** working tree intencionalmente dirty; WIP preexistente preservado; sem reset, clean, stash, checkout destrutivo, commit ou merge.  
**Autoridade:** fallback direto controlado autorizado pelo responsável humano em 22/09/2026.  
**Execuções Adaptive preservadas:** `5ad5177f76bb4bc79ffbb9a648c3497f` e `44b9873d70d04f5f8b9471b8c912193e` confirmadas `PAUSED + QUIESCENT`, `active_execution_count=0`; `25661d19a7f84ff390bbab47adf505e0` não foi retomada.

## 1. Escopo

Fan-in do estado atual para os sete relatos `DEF-001..DEF-007`, com prioridade para:

1. contrato REST público em `Routes.php` e correlation ID;
2. correção do mapeamento canônico dos defeitos;
3. verificação da interface de movimentos e dos saldos da Visão Geral;
4. regressões locais, lint e higiene Git;
5. decisão explícita sobre o gate ambiental e o empacotamento.

Este review foi realizado pela sessão owner que executou o fallback. Portanto, ele é uma revisão de estado atual com eixos de especificação e padrões, mas **não substitui revisão independente por outro revisor** nem os testes ambientais.

## 2. Correções incorporadas no fallback

- `PATCH /account` e `POST /account/initial-balance` usam `PublicError::fromCode(404, 'NOT_FOUND')`; o filtro REST preserva o envelope canônico e associa o correlation ID da requisição ao corpo/header.
- Catches genéricos dos controllers alcançados agora vinculam `$e` antes de chamar `PublicError::response`, evitando novo erro fatal no caminho de exceção.
- A tela de Lançamentos desempacota `{items: [...]}` por `list(d)` antes de mapear linhas; a origem continua sendo somente Lançamentos ativos.
- A Visão Geral compõe `dashboard(d) + agenda(...)`, tornando visíveis saldo de abertura, atual e final previsto.
- O registro `DEF-001..DEF-007` foi realinhado literalmente aos sete relatos originais.

## 3. Review por especificação

| Defeito | Evidência local atual | Veredito local | Evidência ainda ausente |
|---|---|---|---|
| DEF-001 — CRUD mostra `INTERNAL_ERROR` | CRUD/DTO/services + `PublicErrorTest` + `RoutesCompositionTest`; envelopes legados removidos; catches corrigidos | **Aceito localmente** | REST autenticado e log correlacionado em WordPress/MySQL |
| DEF-002 — texto preto nos inputs | CSS explícito por tema + teste de contraste estrutural | **Aceito localmente** | estilos computados/autofill em browser |
| DEF-003 — máscara `dd/mm/aaaa` | máscara, conversão civil e round-trip protegidos por teste | **Aceito localmente** | digitação/colagem/teclado móvel em browser |
| DEF-004 — `d.map` / apenas efetivados | `list(d).map`; service/adapter consultam apenas `ATIVO` | **Aceito localmente** | pendente/efetivado/desfeito em banco e REST reais |
| DEF-005 — saldos inicial/final | dashboard renderizado junto da agenda; services testam abertura/atual/previsto | **Aceito localmente** | cenários mensais em banco/browser reais |
| DEF-006 — ZIP de backup | contêiner, limite, headers e digest inspecionados; teste rejeita entrada extra | **Aceito localmente** | download, `unzip -t` e restore real com snapshot |
| DEF-007 — tema confiável | API/localStorage/bootstrap/rollback e `ThemeServiceTest` | **Aceito localmente** | recarga, falha de rede e isolamento de usuários em browser/WordPress |

## 4. Review por padrões

- **Contrato REST:** sem envelope string conhecido nas duas closures de conta; erros passam por allowlist pública e detalhes internos não atravessam a fronteira.
- **Observabilidade:** correlation ID seguro é materializado no envelope; o filtro adiciona `X-Correlation-ID` e normaliza também `WP_Error` anterior ao controller.
- **Arquitetura:** correções permaneceram na fronteira REST/frontend/teste; nenhuma regra de negócio, tabela ou gate foi redefinido.
- **Coleções:** o cliente aceita tanto array quanto envelope `{items}` pelo helper existente, sem duplicar regra no backend.
- **Saldo:** continua derivado; nenhuma persistência/cache autoritativo foi introduzido.
- **WIP/Git:** nenhuma alteração preexistente foi descartada ou reclassificada.

## 5. Estado exato revisado

SHA-256 das superfícies principais no instante do review:

| Arquivo | SHA-256 |
|---|---|
| `src/src/REST/Routes.php` | `f03201294eae0d7898f7906f19ac9d41f07e6cf036d719b7ea3f9bb81f3c2106` |
| `src/src/REST/PublicError.php` | `cb53238e57f65e830ff42168fb650c2b751d1100306dc3509f86f6cf96af7ad1` |
| `src/src/REST/Controllers/AccountController.php` | `f3392fea3677382e41024bb3e672d481d78fb25af835ebf0cf61594b078f9498` |
| `src/src/REST/Controllers/BackupController.php` | `2d3393a999165a8e099a7b556f96545a8fa13c081641d0f44f3c9f6ac1815034` |
| `src/src/REST/Controllers/RestoreController.php` | `9518af2f2603033c770ba58be2d32232438740710d0add63b87a24923219b477` |
| `src/src/REST/Controllers/ProfileController.php` | `37e2afc903f4f3a6b2a6c65b951e7175e5ea7f701ff4ae58e34754abd9f74ff3` |
| `src/src/REST/Controllers/RecurrenceController.php` | `ba2a8a4ce2ddaf9645184008e33be54caaac4c8f8e09705d8e356b8e83aae54c` |
| `src/assets/app.js` | `65aad6364f3ff8c0ffe01a38e73d28c86b4d50ffa59f5917f25e00ca89e3b97e` |
| `src/assets/app.css` | `b3cc6222aecc7e7f6e1f885d36c031aced7d89a4bff4ec16dfcaf9a356bec134` |
| `src/src/Tests/Unit/PublicErrorTest.php` | `d127f312da4b969861602f17d61bacdbb612ec678720980f0d62acb99137be1b` |
| `src/src/Tests/Unit/RoutesCompositionTest.php` | `36c4ff26d02fda721b9e9644f28a713813088ba51a4ff7c0c39c8865a4ba2fdd` |
| `src/src/Tests/Unit/CommitmentRowActionsTest.php` | `12269de8d7df820e8c64b3244cc728eee82662f1dc34780fd00e5e6ce906ca47` |

## 6. Validações executadas

- PHPUnit completo: **PASS — 156 testes / 469 asserções / 0 falhas / 0 erros / 0 skipped**.
- PHPUnit focado (`PublicError`, rotas, frontend/movimentos/saldos, reporting): **PASS — 43 testes / 210 asserções** na rodada combinada; rodadas menores anteriores também passaram.
- PHP lint: **PASS — 128 arquivos** sob `src/src`.
- `node --check src/assets/app.js`: **PASS**.
- `composer validate --no-check-publish`: **PASS**.
- `git diff --check`: **PASS**.

## 7. Gate ambiental

**Não executado por indisponibilidade do ambiente neste host.** Foram verificados:

- `wp`, `mysql`, `mariadb`, `docker` e `podman`: indisponíveis;
- nenhum processo WordPress/Apache/Nginx/PHP-FPM/MySQL/MariaDB correlato;
- nenhuma porta web/banco relevante ativa;
- nenhum `wp-config.php` localizado em `/var/www` ou `/srv`;
- nenhum harness de integração ou browser versionado neste checkout.

Isso representa **zero cenários ambientais executados**, não um passe.

## 8. Veredito

**APPROVE WITH NOTES para a matriz local dos sete defeitos.** Não restou finding local bloqueante nas superfícies revisadas após as correções e a suíte completa.

**RELEASE/PACKAGE GATE: BLOQUEADO.** A instrução humana condiciona o ZIP à aprovação e à validação ambiental; como WordPress/MySQL/MariaDB/REST/browser não foram executados, nenhum novo ZIP foi produzido. O artefato anterior permanece stale e não deve ser usado como prova destas correções.

## 9. Próxima ação

Disponibilizar um ambiente WordPress single-site com MySQL ou MariaDB, instalar o plugin construído a partir deste mesmo WIP, executar a matriz REST/browser `DEF-001..DEF-007` e repetir o review no estado exato testado. Somente depois, se todos os gates passarem, aplicar o procedimento `sgfp-plugin-packaging` e gerar um ZIP com nome, tamanho e SHA-256 novos.
