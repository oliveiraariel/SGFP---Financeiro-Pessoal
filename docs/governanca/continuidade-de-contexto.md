# SGFP — Continuidade de Contexto

## 1. Finalidade

Este documento permite retomar o trabalho do SGFP em uma nova sessão, novo chat, novo agente ou após interrupção de execução sem depender do histórico da conversa anterior.

Ele funciona como **handoff operacional do estado corrente**.

Este documento **não substitui** as fontes canônicas do projeto e não deve ser usado para criar ou alterar regras de negócio por conta própria.

## 2. Ordem de autoridade e leitura

Ao retomar o projeto, leia nesta ordem:

1. `AGENTS.md`
2. `ORCHESTRATOR.md`
3. `project-manifest.yaml`
4. `docs/governanca/continuidade-de-contexto.md`
5. somente os documentos canônicos e derivados necessários para a tarefa atual

Em caso de divergência:

- regras de negócio e requisitos aprovados prevalecem conforme a autoridade definida em `ORCHESTRATOR.md` e `project-manifest.yaml`;
- este documento descreve o **estado operacional de continuidade**, não cria uma nova fonte de verdade;
- o estado real do Git deve ser confirmado antes de qualquer alteração.

## 3. Estado atual do projeto

Última etapa formalmente concluída:

- **Etapa 4 — Casos de Uso**

Próxima etapa planejada:

- **Etapa 5 — Mapa do Domínio**

Situação:

- a Etapa 5 ainda **não foi iniciada**;
- a baseline documental pré-Etapa 5 está em reconciliação;
- a reconciliação dos requisitos foi executada no working tree, mas ainda precisa de auditoria independente antes de ser aceita e versionada.

## 4. Estado atual do Git

Branch de trabalho esperada:

```text
docs/reconciliacao-baseline-rf-v1
```

Estado esperado:

- existem alterações documentais no working tree;
- as alterações da reconciliação ainda não devem estar staged;
- não deve existir commit da reconciliação;
- não deve existir push da reconciliação;
- a `main` não deve ser alterada durante essa revisão.

Antes de continuar, confirmar obrigatoriamente:

```bash
git status
git branch --show-current
git diff --stat
git diff --name-status
git diff --check
```

Se o estado real do Git divergir deste documento, **não assumir qual é o estado correto**. Investigar primeiro.

## 5. Trabalho em andamento

Objetivo da branch atual:

> Reconciliar a baseline funcional da V1 antes da Etapa 5, resolvendo `ISSUE-001` a `ISSUE-004`, corrigindo rastreabilidade e consolidando um catálogo funcional único.

O agente `sgfp`, orientado por `AGENTS.md`, `ORCHESTRATOR.md`, `project-manifest.yaml` e pela Skill `project-orchestrator`, executou uma reconciliação documental no working tree.

Resultado informado pelo executor:

```text
PASSOU
```

Esse resultado **ainda não foi aceito como definitivo**.

Falta uma auditoria independente do diff e da consistência documental.

## 6. Baseline funcional aprovada para a reconciliação

A baseline funcional proposta e aprovada para reconciliação contém exatamente 21 requisitos:

| ID | Requisito Funcional |
|---|---|
| RF-001 | Cadastrar usuário |
| RF-002 | Autenticar usuário |
| RF-003 | Gerenciar senha |
| RF-004 | Gerenciar contas financeiras |
| RF-005 | Consultar saldos das contas e patrimônio total |
| RF-006 | Gerenciar compromissos financeiros |
| RF-007 | Efetivar e desfazer compromissos financeiros |
| RF-008 | Gerenciar compromissos recorrentes |
| RF-009 | Gerenciar categorias financeiras |
| RF-010 | Registrar lançamentos financeiros |
| RF-011 | Consultar movimentações financeiras |
| RF-012 | Gerenciar transferências entre contas |
| RF-013 | Efetivar e desfazer transferências |
| RF-014 | Gerenciar transferências recorrentes |
| RF-015 | Gerenciar compromissos de cartão de crédito |
| RF-016 | Gerenciar compromissos parcelados |
| RF-017 | Consultar o Dashboard financeiro |
| RF-018 | Navegar entre períodos financeiros |
| RF-019 | Gerenciar proteção por PIN |
| RF-020 | Gerenciar tema da aplicação |
| RF-021 | Gerenciar cópias de segurança e restauração |

Após validação e versionamento, esses identificadores devem formar a baseline funcional oficial da V1.

## 7. Decisões consolidadas nesta reconciliação

### 7.1 Usuário, autenticação e senha

- cadastro de usuário pertence à V1;
- autenticação por e-mail e senha pertence à V1;
- gerenciamento de senha pertence à V1;
- essas capacidades são formalizadas como `RF-001`, `RF-002` e `RF-003`.

### 7.2 Contas financeiras

- exclusão de contas financeiras não pertence à V1;
- não criar silenciosamente regras de exclusão, arquivamento ou inativação;
- contas secundárias recebem movimentações somente por transferências com a conta principal.

### 7.3 Saldo, valor inicial e patrimônio

- saldo não é um atributo financeiro independente mantido manualmente;
- saldo é derivado das movimentações;
- patrimônio é derivado dos saldos das contas;
- valor inicial da conta principal é representado por lançamento de Entrada;
- valor destinado inicialmente a uma conta secundária deve chegar por transferência com a principal;
- transferência não altera o patrimônio total;
- o conceito antigo de "saldo inicial" não deve persistir como estado armazenado independente.

### 7.4 Atualização de saldo

A atualização de saldos não é RF autônomo.

É comportamento de consistência relacionado principalmente a:

- `RF-005`;
- `RF-007`;
- `RF-013`;
- critérios de aceitação e regras de integridade financeira correspondentes.

### 7.5 Categorias

Criar categoria durante o cadastro de compromisso não é RF independente.

É fluxo/capacidade relacionada a:

- `RF-006`;
- `RF-009`.

### 7.6 Períodos anteriores

Registrar informações financeiras de períodos anteriores não é RF independente.

A capacidade deve ser representada pelas regras e requisitos apropriados, especialmente:

- `RF-010`;
- `RF-018`.

### 7.7 Movimentações

Consultar movimentações financeiras permanece capacidade funcional independente:

```text
RF-011 — Consultar movimentações financeiras
```

### 7.8 Backup

`RF-021` contempla cópias de segurança e restauração conforme as regras existentes.

Nenhuma tecnologia de armazenamento, formato físico, provedor externo ou mecanismo de infraestrutura deve ser escolhido nesta etapa.

## 8. Issues tratados pela reconciliação

A reconciliação pretende resolver, preservando proveniência:

- `ISSUE-001` — divergência entre catálogo formal de 19 RFs e artefatos derivados com 25 RFs;
- `ISSUE-002` — cadastro, autenticação e senha ausentes do catálogo formal anterior;
- `ISSUE-003` — exclusão de contas mencionada sem regra de negócio correspondente;
- `ISSUE-004` — inconsistência sobre valor inicial e contas secundárias.

Os issues não devem simplesmente desaparecer. Histórico, resolução e justificativa devem permanecer registrados conforme os mecanismos de governança do projeto.

## 9. Próxima ação obrigatória

Antes de qualquer commit, executar uma **auditoria independente** da reconciliação.

Primeiro:

```bash
git status
git diff --stat
git diff --name-status
git diff --check
```

Depois revisar criticamente o diff completo e confirmar:

1. existem exatamente `RF-001` a `RF-021` como baseline oficial;
2. não existe RF oficial além de `RF-021`;
3. cada identificador possui significado único;
4. cadastro, autenticação e senha estão corretamente cobertos;
5. exclusão de contas não aparece como funcionalidade da V1;
6. saldo inicial não é tratado como atributo independente;
7. principal e secundárias respeitam as regras de movimentação;
8. patrimônio permanece derivado;
9. atualização de saldo não aparece como RF autônomo;
10. Casos de Uso possuem referências corretas;
11. Critérios de Aceitação possuem referências corretas;
12. índices CSV correspondem aos documentos;
13. rastreabilidade RF → UC → Critério é consistente onde aplicável;
14. referências antigas permanecem somente quando forem histórico/proveniência válida;
15. `project-manifest.yaml` e `ORCHESTRATOR.md` representam corretamente o estado reconciliado;
16. nenhuma etapa de domínio, modelagem, arquitetura ou implementação foi iniciada antecipadamente;
17. `git diff --check` passa.

Se qualquer falha relevante for encontrada:

```text
corrigir
→ revalidar
→ auditar novamente
```

Somente após a auditoria independente resultar em **PASSOU** deve ser feito commit.

## 10. Ações proibidas neste ponto

Até a auditoria independente ser concluída:

- não executar `git add`;
- não executar commit;
- não executar push;
- não fazer merge;
- não iniciar o Mapa do Domínio;
- não alterar a `main`;
- não inventar novas regras de negócio.

## 11. Quando a reconciliação passar

Depois da aprovação humana da auditoria:

```bash
git status
git diff --check
git add -A
git commit -m "docs: reconcilia baseline funcional da v1"
git push -u origin docs/reconciliacao-baseline-rf-v1
git status
```

Depois disso, revisar a integração via Pull Request antes de incorporar à `main`.

## 12. Regra para retomada em nova sessão

Ao iniciar uma nova sessão, o primeiro passo deve ser:

> Confirmar o estado do Git e comparar com este documento antes de executar qualquer alteração.

Não confiar apenas em memória de conversa, resumo de agente ou relatório anterior.

## 13. Atualização deste documento

Atualizar este arquivo sempre que houver uma mudança relevante de continuidade, por exemplo:

- troca de branch ou Work Unit principal;
- conclusão de uma auditoria;
- commit/merge importante;
- alteração do gate da próxima etapa;
- mudança de blocker;
- início de nova etapa;
- interrupção do trabalho em ponto que precise ser retomado depois.

Ao atualizar:

- registrar apenas o estado necessário para retomada;
- não duplicar documentação canônica desnecessariamente;
- remover instruções transitórias que já perderam validade;
- preservar decisões relevantes por referência às fontes oficiais;
- manter a próxima ação explícita.

Última atualização operacional: **2026-08-24**.
