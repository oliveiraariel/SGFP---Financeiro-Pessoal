# SGFP — Handoff de Continuidade

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
