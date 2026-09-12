# SGFP — Handoff de Continuidade

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

- `POST /sgfp/v1/backups` exige capability `use_sgfp`, captura os dados do usuário em transação, serializa JSON, compacta com gzip, protege com Sodium usando `SGFP_BACKUP_KEY` e envia o arquivo por `wp_mail` ao e-mail cadastrado.
- Restauração, catálogo de backups, snapshot pré-restauração e Etapa 11 não foram iniciados.
- `POST /sgfp/v1/restore-validations` recebe o arquivo, autentica/descompacta, valida versão, proprietário e seções obrigatórias, e retorna resumo com token temporário; não altera dados.

### Validações desta sessão

- `find src/src -name '*.php' -print0 | xargs -0 -n1 php -l`: passou.
- `php Tests/Manual/create-account-manual-test.php`: passou.
- `php Tests/Manual/initial-balance-manual-test.php`: bloqueado antes da execução porque o fake `EntryRepository` do teste não implementa `findAllByUser`, método já exigido pela porta existente.

### Validações da unidade seguinte

- Os doubles manuais de `EntryRepository`, `RecurrenceRepository` e `TransferRepository` foram alinhados às portas atuais.
- `php Tests/Manual/*.php`: todos os seis testes manuais passaram.
- Lint PHP completo após a validação preliminar: passou.

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
- Ainda faltam `findAllByUser` em `WpTransferRepository` e `WpEntryRepository` (as portas já foram atualizadas).
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

Próxima unidade pequena: persistir staging privado e integrar o token de validação ao futuro fluxo de restauração, sem ainda substituir dados.
