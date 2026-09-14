# Etapa 10 — Desenvolvimento da API

**Status histórico:** a primeira implementação foi concluída sobre a baseline anterior.  
**Status atual:** **reconciliada com a V1 simplificada de 14/09/2026 e validada em ambiente automatizado**.

A reconciliação está implementada na branch:

`feat/stage-9-10-backend`

e documentada no:

**Draft PR #12 — WIP: Reconcile Stage 9–10 backend with simplified V1**

## Estado funcional atual

O backend implementa:

- uma Conta Financeira por usuário;
- provisionamento automático de **Minha Conta**;
- saldo derivado dos Lançamentos ativos;
- ausência de Principal/Secundária;
- Transferências fora da V1 ativa;
- Patrimônio Total/net-worth fora da V1;
- Modelo Físico V8 com cinco tabelas financeiras;
- migração de schema `1.2.0` para a V8;
- backup protegido entregue localmente em ZIP;
- restauração ZIP V2 com snapshot pré-restauração obrigatório;
- RF-022 — Resetar perfil financeiro;
- RF-023 — Excluir conta de acesso.

## Validações executadas

### PHP / unidade

- Composer install: PASS;
- PHP lint: PASS;
- PHPUnit: **40 testes / 92 assertions / 0 falhas / 0 erros**.

### Integração WordPress + MySQL

Ambiente efêmero real em GitHub Actions:

- `[PASS] clean-v8`;
- `[PASS] migrate-v7-to-v8`;
- `[PASS] lifecycle`;
- `[PASS] backup-restore`.

Os testes confirmaram instalação limpa, migração V7→V8, provisionamento, reset, exclusão do usuário WordPress, backup/restauração e preservação de invariantes relevantes.

## Defeitos encontrados e corrigidos

A validação de integração encontrou dois defeitos que lint/testes unitários não haviam revelado:

- ciclo de vida inválido de `ZipArchive`, corrigido no commit `6d57fcfa`;
- imports ausentes dos services de recorrência em `Routes.php`, corrigidos no commit `84b7cbe8`.

## Continuidade

A branch 9–10 permanece aberta de propósito.

Durante a Etapa 11, se a integração do frontend revelar necessidade legítima de ajuste na API/backend:

1. registrar a necessidade;
2. corrigir na linha 9–10;
3. validar;
4. reintegrar à Etapa 11 de forma controlada.

A Etapa 11 ainda precisa ser reconciliada com a baseline simplificada e com os contratos atuais da API.

## Fonte normativa e trilha de implementação

- baseline normativa: `docs/governanca/baseline-v1-simplificada-2026-09-14.md`;
- documentação da mudança: Draft PR #11;
- implementação backend: Draft PR #12;
- detalhes técnicos de continuidade: handoff técnico registrado na branch 9–10.

A implementação não substitui regras de negócio, requisitos ou casos de uso como fonte normativa. Quando houver divergência, diagnosticar e aplicar a precedência definida por `ORCHESTRATOR.md` e `project-manifest.yaml`.

## Pendência menor de housekeeping

`composer.json` declara `ext-zip`. O gate automatizado registrou aviso de que `composer.lock` precisa ser sincronizado com essa alteração. O aviso não impediu Composer, lint, PHPUnit ou os testes de integração, mas deve ser regularizado antes do fechamento de housekeeping da branch.
