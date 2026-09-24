# PROMPT DE RETOMADA — SGFP

Use este documento ao iniciar uma nova sessão/agente no SGFP.

## 1. Leitura obrigatória

Leia, nesta ordem:

1. `HANDOFF.md`;
2. `AGENTS.md`;
3. `ORCHESTRATOR.md`;
4. `project-manifest.yaml`;
5. `docs/governanca/baseline-v1-simplificada-2026-09-14.md`;
6. `docs/governanca/status-implementacao-v1-2026-09-14.md`;
7. `docs/governanca/continuidade-de-contexto.md`;
8. `docs/requisitos/srs/03-requisitos-funcionais.md`;
9. `docs/requisitos/srs/07-criterios-de-aceitacao.md`;
10. `docs/casos-de-uso/catalogo.csv`;
11. `docs/dominio/01-mapa-de-dominio.md`;
12. `docs/modelagem-dados/01-modelagem-conceitual-mer.md`;
13. `docs/modelagem-dados/02-modelo-entidade-relacionamento-der.md`;
14. `docs/modelagem-dados/03-modelo-fisico.md`;
15. `docs/arquitetura/01-arquitetura-da-aplicacao.md`;
16. `docs/api/README.md`;
17. `docs/interface-web/README.md`.

Se algum arquivo não existir no caminho esperado, informe o fato e não invente conteúdo substituto.

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

- Etapas 5–9 possuem documentação revisada para a baseline simplificada.
- A Etapa 10 está concluída, reconciliada e validada.
- A Etapa 11 está em andamento na branch `feat/stage-11-web-interface`.
- A reconciliação funcional da Etapa 11 com conta única, backup local, reset/exclusão e backend atualizado foi incorporada; ainda devem ser confirmadas as validações integradas/ambientais e de navegador necessárias ao fechamento formal.
- Resíduos de múltiplas contas, Transferências, Patrimônio ou backup por e-mail devem ser tratados como legado/histórico, nunca como fonte para reverter a baseline.
- `ISSUE-008` de rastreabilidade permanece pendente antes do fechamento da Etapa 12.

## 4. Validação obrigatória do estado real

Antes de editar qualquer arquivo, compare o que os documentos dizem com o estado real do repositório e do runtime disponível.

Confirme, no mínimo:

- repositório correto;
- branch atual;
- HEAD local e remoto;
- `git status` e WIP existente;
- diferenças local/remoto;
- processos/orquestrações relevantes quando a tarefa depender deles;
- se o `HANDOFF.md` ainda descreve o estado real ou representa apenas um checkpoint histórico.

Não execute `reset`, `clean`, checkout destrutivo, stash, rebase destrutivo, merge ou descarte de WIP para “alinhar” o ambiente.

Se houver divergência entre o handoff e o estado real, preserve o estado real, descreva a divergência e só então defina a próxima ação.

## 5. Regras de operação

- Não invente regras de negócio.
- Em conflito, a baseline vigente e as fontes normativas atualizadas prevalecem sobre implementação ou documentação histórica.
- Preserve alterações locais preexistentes; não use reset/clean/stash destrutivo sem autorização.
- No primeiro turno, faça retomada ativa em modo leitura: leia, inspecione, compare e informe o diagnóstico; não apenas aguarde novos logs.
- Quando usar Adaptive, respeite governança, bridge e decomposição multiagente.
- Se Adaptive falhar, não use fallback direto sem autorização humana explícita.

## 6. Resposta esperada na retomada

Informe:
- repositório/branch/HEAD;
- estado do working tree;
- baseline carregada;
- comparação entre `HANDOFF.md`, documentação vigente e estado real;
- divergências relevantes;
- próximo passo seguro e concreto.

Não trate documentos históricos como estado vigente.