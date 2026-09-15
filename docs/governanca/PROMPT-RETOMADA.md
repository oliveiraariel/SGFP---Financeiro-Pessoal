# PROMPT DE RETOMADA — SGFP

Use este documento ao iniciar uma nova sessão/agente no SGFP.

## 1. Leitura obrigatória

Leia, nesta ordem:

1. `AGENTS.md`;
2. `ORCHESTRATOR.md`;
3. `project-manifest.yaml`;
4. `docs/governanca/baseline-v1-simplificada-2026-09-14.md`;
5. `docs/governanca/status-implementacao-v1-2026-09-14.md`;
6. `docs/governanca/continuidade-de-contexto.md`;
7. `docs/requisitos/srs/03-requisitos-funcionais.md`;
8. `docs/requisitos/srs/07-criterios-de-aceitacao.md`;
9. `docs/casos-de-uso/catalogo.csv`;
10. `docs/dominio/01-mapa-de-dominio.md`;
11. `docs/modelagem-dados/01-modelagem-conceitual-mer.md`;
12. `docs/modelagem-dados/02-modelo-entidade-relacionamento-der.md`;
13. `docs/modelagem-dados/03-modelo-fisico.md`;
14. `docs/arquitetura/01-arquitetura-da-aplicacao.md`;
15. `docs/api/README.md`;
16. `docs/interface-web/README.md`;
17. `HANDOFF.md` quando houver trabalho técnico em continuidade.

## 2. Baseline vigente que deve ser confirmada

- RF-001 a RF-023 no catálogo.
- 19 requisitos ativos na V1.
- RF-012, RF-013 e RF-014 (Transferências) fora da V1 e preservados como futuros.
- RF-019 (PIN) futuro.
- exatamente uma Conta Financeira por usuário;
- conta criada automaticamente no cadastro como **Minha Conta**;
- saldo derivado dos Lançamentos ativos, nunca armazenado como atributo;
- Patrimônio Total fora da V1;
- categoria opcional no Compromisso;
- criar Compromisso não altera saldo;
- efetivar Compromisso cria Lançamento;
- desfazer efetivação remove o efeito preservando histórico;
- backup manual por **ZIP baixado localmente**;
- backup/restauração não dependem de e-mail;
- restauração integral, sem merge, protegida por cópia pré-restauração;
- Reset do perfil financeiro mantém o login e exige `RESETAR PERFIL`;
- Exclusão da conta de acesso remove dados + login WordPress e exige `EXCLUIR CONTA`;
- ambas as operações destrutivas exigem duas etapas de confirmação.

## 3. Estado de desenvolvimento

- Etapas 5–9 possuem documentação revisada para a baseline de 14/09/2026.
- A implementação da Etapa 10 foi reconciliada e validada na branch `feat/stage-9-10-backend`; acompanhar o Draft PR #12.
- A Etapa 11 está em andamento na branch `feat/stage-11-web-interface` e ainda precisa ser reconciliada.
- O frontend pode conter código de múltiplas contas, Transferências, Patrimônio ou backup por e-mail; isso é **legado a reconciliar**, não fonte para reverter a documentação.
- Se a Etapa 11 revelar necessidade legítima de ajuste no backend, registrar, corrigir/testar na linha 9–10 e reintegrar de forma controlada.
- `ISSUE-008` de rastreabilidade permanece pendente antes do fechamento da Etapa 12.

## 4. Regras de operação

- Não invente regras de negócio.
- Em conflito, a baseline de 14/09/2026 e as fontes normativas atualizadas prevalecem sobre implementação antiga.
- Preserve alterações locais preexistentes; não use reset/clean/stash destrutivo sem autorização.
- No primeiro turno, diagnostique antes de editar.
- Quando usar Adaptive, respeite governança, bridge e decomposição multiagente.
- Se Adaptive falhar, não use fallback direto sem autorização humana explícita.

## 5. Resposta esperada na retomada

Informe:
- repositório/branch/HEAD;
- estado do working tree;
- baseline carregada;
- divergências entre documentação, backend reconciliado e frontend em andamento;
- próximo passo seguro.

Não trate documentos históricos como estado vigente.
