# SGFP — Handoff de Continuidade

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
