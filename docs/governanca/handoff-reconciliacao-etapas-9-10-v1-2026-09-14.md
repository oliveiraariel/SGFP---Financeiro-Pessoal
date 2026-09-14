# SGFP — Handoff Técnico da Reconciliação das Etapas 9–10 com a V1 Simplificada

**Data:** 14/09/2026  
**Branch:** `feat/stage-9-10-backend`  
**Status:** reconciliação implementada e validada em ambiente automatizado  
**Finalidade:** permitir que OpenClaw, Adaptive ou outro agente retome o backend sem inferir a baseline antiga a partir de documentos históricos ou commits anteriores.

---

## 1. Regra de leitura deste documento

Este handoff descreve **o estado técnico atual da branch `feat/stage-9-10-backend`** após a simplificação da V1.

Ele deve ser lido em conjunto com a baseline documental atualizada no Draft PR #11:

- branch documental: `docs/v1-single-account-local-backup-reset-delete`;
- PR #11: **WIP: Simplify V1 to single account, local backup and account lifecycle**.

Enquanto o PR #11 não for integrado, alguns documentos antigos ainda presentes nesta branch podem conter a baseline anterior. Em caso de divergência entre esses documentos antigos e este handoff, **não assumir que a baseline antiga continua vigente**. Para regras de negócio e escopo da V1 simplificada, consultar o PR #11 e seus documentos atualizados.

Este handoff não autoriza inventar novas regras de negócio.

---

## 2. Objetivo da reconciliação

A implementação das Etapas 9–10 havia sido concluída sobre uma baseline anterior que possuía:

- múltiplas contas por usuário;
- Conta Principal e Conta Secundária;
- Transferências ativas;
- Patrimônio Total;
- backup baseado em gzip/e-mail;
- ausência dos fluxos de reset de perfil e exclusão do login.

Em 14/09/2026 a V1 foi simplificada. O backend foi então reconciliado para refletir a nova baseline.

---

## 3. Baseline funcional atual da V1

Catálogo preservado:

- `RF-001` a `RF-023`.

Requisitos ativos na V1:

- **19 RFs ativos**.

Futuros/inativos:

- `RF-012`, `RF-013`, `RF-014` — Transferências;
- `RF-019` — proteção por PIN.

Novos requisitos ativos:

- `RF-022` — Resetar perfil financeiro;
- `RF-023` — Excluir conta de acesso.

Casos de uso futuros/inativos:

- `UC-013` — Transferências;
- `UC-018` — PIN;
- `UC-022` — Patrimônio Total.

Novos casos de uso ativos:

- `UC-023` — Resetar perfil financeiro;
- `UC-024` — Excluir conta de acesso.

---

## 4. Decisões estruturais vigentes

### 4.1 Conta Financeira única

Cada usuário possui exatamente uma Conta Financeira.

A aplicação provisiona automaticamente:

`Minha Conta`

no cadastro do usuário WordPress.

A conta:

- pode ser renomeada;
- não possui papel Principal/Secundária;
- não pode ser criada livremente em quantidade adicional;
- não armazena saldo;
- não é excluída isoladamente na V1.

### 4.2 Saldo

Saldo é informação derivada dos `LANCAMENTO_FINANCEIRO` ativos.

Não existe coluna persistida `SALDO`.

Saldo inicial é representado por:

- `ORIGEM = SALDO_INICIAL`;
- `TIPO_EFEITO = ENTRADA`.

O valor do saldo inicial pode ser positivo, zero ou negativo conforme a baseline atual.

### 4.3 Compromisso e lançamento

Criar um `COMPROMISSO_FINANCEIRO` não altera saldo.

Somente a efetivação produz `LANCAMENTO_FINANCEIRO`.

Todo compromisso ativo da V1 representa:

- `ENTRADA`; ou
- `SAIDA`.

O campo/tipo específico de Transferência foi removido.

### 4.4 Transferências

Transferências não fazem parte da V1 ativa.

Foram removidos do fluxo ativo:

- rotas REST;
- controllers;
- services;
- modelo ativo;
- repository ativo;
- tabela física `TRANSFERENCIA`.

Identificadores e documentação histórica/futura podem continuar existindo para rastreabilidade.

### 4.5 Patrimônio Total

Patrimônio Total / `net-worth` não integra a V1 ativa.

A rota `/net-worth` e o service correspondente foram removidos.

### 4.6 Backup e restauração

Backup manual:

- é gerado sob demanda;
- é entregue localmente como arquivo ZIP;
- não depende de e-mail;
- contém `manifest.json` e `payload.bin`;
- o payload permanece protegido com Sodium;
- possui hash SHA-256.

Formato atual:

- schema lógico: `sgfp-backup`;
- versão: `2`;
- contêiner: `sgfp-backup-zip`.

Restauração:

- é integral, sem merge;
- valida o ZIP;
- valida integridade/proteção;
- usa token de restauração;
- exige confirmação;
- gera obrigatoriamente uma cópia pré-restauração recuperável;
- cancela a operação se a cópia pré-restauração não puder ser preservada;
- devolve a cópia pré-restauração como ZIP local baixável.

### 4.7 Resetar perfil financeiro

Frase exata:

`RESETAR PERFIL`

Requer:

- confirmação booleana;
- frase exata em maiúsculas.

O reset:

- remove os dados SGFP do usuário;
- preserva `wp_users`;
- remove preferências/tokens SGFP;
- reprovisiona `Minha Conta`;
- reprovisiona as categorias padrão.

Endpoint atual:

`POST /sgfp/v1/profile-reset`

### 4.8 Excluir conta de acesso

Frase exata:

`EXCLUIR CONTA`

Requer:

- confirmação booleana;
- frase exata em maiúsculas.

A operação:

1. remove os dados SGFP;
2. remove a identidade WordPress;
3. limpa a autenticação;
4. não deve reportar sucesso se o `wp_user` permanecer ativo.

Endpoint atual:

`DELETE /sgfp/v1/account-access`

---

## 5. Modelo físico V8 implementado

Tabelas financeiras ativas:

1. `CONTA_FINANCEIRA`
2. `CATEGORIA`
3. `RECORRENCIA`
4. `COMPROMISSO_FINANCEIRO`
5. `LANCAMENTO_FINANCEIRO`

Tabela técnica adicional:

- token de restauração.

Removidos do modelo V1:

- `TRANSFERENCIA`;
- `PAPEL`;
- `ID_USUARIO_PRINCIPAL`;
- `TIPO` específico de compromisso.

Restrição principal:

`CONTA_FINANCEIRA.FK_ID_USUARIO` possui unicidade.

Portanto, o banco garante no máximo uma conta por usuário; a existência da conta é garantida pelo provisionamento da aplicação.

---

## 6. Migração V7 → V8

Migração adicionada:

`1.2.0`

A migração não destrói silenciosamente os dados legados.

Comportamento esperado:

1. identifica a conta de destino do usuário;
2. preserva preferencialmente a antiga Conta Principal;
3. remove efeitos de compromissos de Transferência quando seu efeito ativo é líquido zero;
4. redireciona os lançamentos financeiros remanescentes para a conta única;
5. remove contas excedentes;
6. remove estruturas físicas da V7;
7. cria a restrição de conta única.

Proteções:

- uma Transferência antiga com efeito ativo líquido diferente de zero bloqueia a migração;
- múltiplos saldos iniciais ativos para o mesmo usuário bloqueiam a migração;
- em caso de bloqueio, a versão do schema não deve avançar silenciosamente.

---

## 7. Commits principais da reconciliação

Ponto em que a Alteração 5 já estava concluída:

- `d3f922c4da412b7b6f498e192cf1369b1029f3c6` — restauração ZIP V2 sem Transferências/e-mail.

Alterações posteriores principais:

- `3c39dfa0` — migração do domínio e banco para V8;
- `910b9138` — remoção das últimas referências V7;
- `59d8a5d7` — reset de perfil;
- `7de5d2cd` — correção da cobertura de teste do reset;
- `28ab01af` — exclusão da conta de acesso/identidade WordPress;
- `6d57fcfa` — correção do ciclo de vida do `ZipArchive`;
- `84b7cbe8` — correção dos imports dos services de recorrência em `Routes.php`.

Os commits de criação/remoção de workflows temporários de CI são apenas instrumentação de validação e não representam funcionalidade de produto.

---

## 8. Evidências de validação

### 8.1 Lint e testes unitários

Ambiente GitHub Actions:

- PHP 8.1;
- Composer;
- extensões Sodium e ZIP.

Resultado:

- PHP lint: **PASS**;
- PHPUnit: **40 testes / 92 assertions / 0 falhas / 0 erros**.

### 8.2 Gate de integração WordPress + MySQL

Foi criado um ambiente efêmero com:

- WordPress real;
- MySQL 8 real;
- PHP 8.1;
- plugin SGFP ativado;
- dados fictícios.

Os quatro cenários passaram:

#### A. Instalação limpa V8

`[PASS] clean-v8`

Validou:

- criação das cinco tabelas financeiras;
- ausência da tabela Transferência;
- ausência de `PAPEL`, `ID_USUARIO_PRINCIPAL` e `TIPO`;
- `UNIQUE(FK_ID_USUARIO)`;
- InnoDB;
- schema `1.2.0`;
- `user_register` criando exatamente uma `Minha Conta`;
- 13 categorias padrão.

#### B. Migração V7 → V8

`[PASS] migrate-v7-to-v8`

Validou:

- consolidação de duas contas em uma;
- preservação da antiga Conta Principal como alvo;
- preservação do saldo agregado;
- remoção da Transferência;
- bloqueio de uma migração propositalmente inconsistente.

#### C. Ciclo de usuário

`[PASS] lifecycle`

Validou:

- criação automática de conta;
- reset mantendo o `wp_user`;
- reprovisionamento;
- limpeza de dados/preferências/tokens SGFP;
- exclusão final removendo efetivamente o `wp_user`.

#### D. Backup/restauração

`[PASS] backup-restore`

Validou:

- saldo inicial;
- compromisso;
- efetivação;
- geração do ZIP;
- hash;
- manifest/payload;
- alteração de dados após o backup;
- validação do ZIP;
- snapshot pré-restauração;
- restauração integral;
- retorno ao saldo esperado;
- restauração do tema.

---

## 9. Defeitos encontrados pelos testes de integração

Os testes reais encontraram defeitos que lint/unitários não detectaram.

### 9.1 Ciclo de vida do ZipArchive

Problema:

- acesso/fechamento de objeto ZIP já invalidado podia gerar `ValueError`.

Correção:

- controle explícito do estado aberto/fechado do `ZipArchive`.

Commit:

- `6d57fcfa`.

### 9.2 Imports de recorrência em Routes.php

Problema:

`Routes.php` instanciava services de recorrência sem importar:

- `MaterializeRecurrenceOccurrenceService`;
- `SettleRecurrenceOccurrenceService`;
- `UndoRecurrenceOccurrenceSettlementService`.

O erro só apareceu quando WordPress inicializou as rotas REST reais.

Correção:

- três imports adicionados.

Commit:

- `84b7cbe8`.

Após as correções, os quatro cenários do gate passaram.

---

## 10. O que foi temporário e já foi removido

Foram criados apenas para validação:

- workflow de GitHub Actions para lint/PHPUnit;
- workflow de integração WordPress/MySQL;
- script de cenários de integração.

Esses arquivos foram removidos depois dos testes.

Eles não fazem parte da infraestrutura permanente do projeto neste momento.

---

## 11. Estado da branch 9–10

A branch `feat/stage-9-10-backend` deve ser considerada:

**reconciliada estruturalmente com a V1 simplificada e validada por lint, testes unitários e testes de integração WordPress/MySQL automatizados.**

Ela permanecerá aberta deliberadamente.

Motivo:

durante a Etapa 11 podem surgir necessidades legítimas de ajustes na API/backend. Essas alterações deverão ser feitas nesta branch ou em uma branch explicitamente derivada dela e depois reintegradas de maneira controlada à Etapa 11.

Não considerar esta branch “congelada” apenas porque o gate atual passou.

---

## 12. Relação com a Etapa 11

Branch da interface:

`feat/stage-11-web-interface`

A Etapa 11 possui trabalho local/preexistente que ainda precisa ser preservado e reconciliado.

Regra operacional:

1. não sobrescrever o working tree local;
2. preservar qualquer WIP antes de merges/pulls;
3. usar a V1 simplificada como baseline;
4. usar o backend reconciliado da 9–10 como contrato técnico;
5. adaptar o frontend antigo para:
   - conta única;
   - ausência de Transferências;
   - ausência de Patrimônio Total;
   - backup ZIP local;
   - reset de perfil;
   - exclusão da conta de acesso.

Quando a Etapa 11 descobrir necessidade de alteração legítima no backend:

1. registrar a necessidade;
2. alterar/testar na linha 9–10;
3. validar;
4. reintegrar a mudança à Etapa 11.

---

## 13. O que ainda NÃO foi validado

Os testes automatizados realizados não substituem todos os testes locais.

Ainda é apropriado validar posteriormente:

- execução no Linux Mint do proprietário;
- instalação local específica do WordPress;
- MariaDB, caso se deseje um gate específico além do MySQL 8;
- Apache/PHP do ambiente local;
- integração com o frontend WIP real;
- testes visuais/browser;
- UX;
- fluxo completo usuário → interface → REST → banco no ambiente local.

Esses testes são especialmente apropriados para OpenClaw quando houver acesso ao computador do projeto.

---

## 14. Pendência técnica menor

`composer.json` passou a declarar `ext-zip`.

Durante o CI foi emitido aviso de que `composer.lock` ainda não estava sincronizado com a alteração recente do `composer.json`.

Isso não impediu Composer, lint, PHPUnit nem os testes de integração, mas deve ser regularizado antes de considerar o housekeeping da branch encerrado.

---

## 15. Instrução de retomada para OpenClaw

Ao iniciar uma nova sessão:

1. confirmar branch, HEAD e estado do working tree;
2. ler `AGENTS.md`;
3. ler `ORCHESTRATOR.md`;
4. ler `project-manifest.yaml`;
5. ler este handoff;
6. consultar o Draft PR #11 e a branch `docs/v1-single-account-local-backup-reset-delete` para a baseline documental atual;
7. verificar o Draft PR específico da reconciliação 9–10;
8. somente depois carregar os arquivos da tarefa concreta.

**Atenção:** alguns arquivos de governança presentes diretamente na branch 9–10 ainda refletem a baseline antiga até a integração do PR documental #11. Não usar esses trechos antigos para reintroduzir Transferências, múltiplas contas, Patrimônio Total ou backup por e-mail.

---

## 16. Próximo objetivo

O objetivo seguinte não é redesenhar novamente o backend.

O próximo objetivo é:

**usar a V1 documental simplificada + backend 9–10 reconciliado como base para continuar e reconciliar a Etapa 11 — Interface Web.**

O backend permanece disponível para correções adicionais que sejam descobertas durante essa integração.

Não executar merge para `main` apenas por este handoff. A integração deve seguir a governança Git do projeto e preservar o trabalho local existente.
