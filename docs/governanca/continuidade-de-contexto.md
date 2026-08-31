# SGFP — Continuidade de Contexto

## 1. Finalidade

Este documento permite retomar o trabalho do SGFP em uma nova sessão, novo chat, novo agente ou após interrupção de execução sem depender do histórico da conversa anterior.

Ele funciona como **handoff operacional do estado corrente**.

Este documento não substitui as fontes canônicas do projeto e não deve ser utilizado para criar ou alterar regras de negócio por conta própria.

## 2. Ordem de leitura para retomada

Ao retomar o projeto, leia nesta ordem:

1. `AGENTS.md`
2. `ORCHESTRATOR.md`
3. `project-manifest.yaml`
4. `docs/governanca/continuidade-de-contexto.md`
5. `docs/dominio/01-mapa-de-dominio.md` para tarefas da Etapa 6 em diante
6. somente os documentos canônicos e derivados necessários para a tarefa atual

Em caso de divergência:

- regras de negócio e requisitos aprovados prevalecem conforme a autoridade definida em `ORCHESTRATOR.md` e `project-manifest.yaml`;
- o Mapa do Domínio organiza os conceitos aprovados da Etapa 5, mas não substitui regras de negócio, requisitos ou Casos de Uso;
- este documento descreve o estado operacional de continuidade;
- o estado real do Git deve ser confirmado antes de qualquer alteração.

## 3. Estado atual do projeto

Catálogo funcional preservado:

- `RF-001` a `RF-021`.

Baseline ativa da Versão 1:

- 20 requisitos funcionais ativos: `RF-001` a `RF-018`, `RF-020` e `RF-021`;
- `RF-019` — proteção por PIN — adiado para versão futura;
- `UC-018` e `CA-019.1` a `CA-019.5` preservados como artefatos futuros, sem renumeração.

Última etapa concluída e validada:

- **Etapa 5 — Mapa do Domínio**

Artefato da Etapa 5:

- `docs/dominio/01-mapa-de-dominio.md`

Etapa atual:

- **Etapa 6 — Modelagem Conceitual (MER)**

Situação:
- o Mapa do Domínio foi revisado para permanecer estritamente no escopo da Etapa 5;
- a baseline documental foi atualizada para retirar o PIN da V1;
- WordPress + PHP constituem restrição tecnológica conhecida para a aplicação Web;
- o backend específico do SGFP será implementado em plugin próprio e a solução utilizará API REST;
- identidade, autenticação e sessão da V1 serão fornecidas pelo WordPress com e-mail e senha;
- cardinalidades, entidades definitivas, chaves, tabelas, nulabilidade, persistência, especializações formais e decisões físicas pertencem às etapas correspondentes;
- estruturas exclusivas do PIN futuro não deverão ser modeladas na V1;
- os RNFs estão padronizados como `RNF-001` a `RNF-020`;
- a rastreabilidade direta das 182 regras indexadas para RFs/RNFs está registrada em `docs/requisitos/srs/rastreabilidade-regras-requisitos.csv`;
- permanece aberta, sem bloquear a Etapa 6, a decisão sobre o significado de "preservar o estado anterior" durante a restauração de backup; essa decisão deverá ser tomada antes da Arquitetura da Aplicação;
- Etapas 7 a 12 permanecem condicionadas aos respectivos gates.

## 4. Decisões consolidadas relevantes para a modelagem

### 4.1 Baseline funcional

O catálogo preserva exatamente os identificadores `RF-001` a `RF-021`.

Na V1 estão ativos `RF-001` a `RF-018`, `RF-020` e `RF-021`.

`RF-019` permanece registrado como requisito de versão futura, com `UC-018` e `CA-019.1` a `CA-019.5` igualmente futuros.

Não renumerar os identificadores posteriores e não criar RF oficial acima de `RF-021` sem decisão humana explícita e atualização coordenada dos artefatos dependentes.

### 4.2 Contas e saldo

- contas são criadas sem saldo armazenado;
- saldo é derivado das movimentações financeiras;
- não existe atributo independente de “saldo inicial” na Conta Financeira;
- quando necessário, o valor existente na Conta Principal no início da utilização é representado por um Lançamento Financeiro de Entrada;
- o valor destinado inicialmente a uma Conta Secundária chega por Transferência com a Conta Principal;
- exclusão de contas financeiras não pertence à V1;
- não criar regras de exclusão, arquivamento ou inativação de contas sem decisão humana explícita e atualização das fontes normativas.

### 4.3 Patrimônio

- Patrimônio Total é derivado dos saldos das contas;
- Transferências entre contas do próprio usuário não alteram o patrimônio total.

### 4.4 Compromisso e lançamento

- Compromisso Financeiro representa planejamento, obrigação ou expectativa;
- Lançamento Financeiro representa movimentação efetivamente realizada;
- a efetivação faz a transição entre planejamento e realização;
- o desfazimento remove o efeito financeiro conforme as regras vigentes;
- estratégias de persistência do desfazimento pertencem às etapas posteriores.

### 4.5 Categoria

- cada usuário recebe um conjunto inicial de categorias criado já vinculado ao próprio usuário;
- essas categorias iniciais são registros do usuário e podem ser renomeadas ou removidas, e novas categorias podem ser adicionadas;
- categoria é obrigatória no momento do cadastro do compromisso;
- remover uma categoria não exclui os compromissos anteriormente associados;
- a forma de representar essa situação no modelo de dados será decidida na etapa apropriada.

### 4.6 Recorrência

- recorrência é mensal na V1;
- alterações e cancelamentos preservam períodos anteriores;
- a estratégia de representação e eventual materialização de ocorrências pertence à Modelagem Conceitual e etapas posteriores.

### 4.7 Transferência

- movimenta valores entre contas do mesmo usuário;
- origem e destino devem ser contas diferentes;
- as regras atuais relacionam transferências entre Conta Principal e Conta Secundária;
- Transferência não altera Patrimônio Total;
- o Mapa do Domínio não decide se Transferência será especialização, composição ou entidade independente.

## 5. Papel do Mapa do Domínio

`docs/dominio/01-mapa-de-dominio.md` é a entrada formal para a Etapa 6.

Ele contém:

- conceitos;
- responsabilidades;
- relações conceituais;
- informações derivadas;
- elementos de apoio;
- limites de escopo;
- rastreabilidade;
- pontos que deverão ser decididos nas etapas posteriores.

Ele **não define**:

- cardinalidades;
- entidades definitivas;
- PK/FK;
- tabelas e colunas;
- tipos de dados;
- nulabilidade;
- índices;
- estratégias de persistência;
- arquitetura;
- implementação.

## 6. Gate da Etapa 6 — Modelagem Conceitual (MER)

A Etapa 6 pode iniciar quando:

1. `docs/dominio/01-mapa-de-dominio.md` estiver presente no repositório na versão validada;
2. `git diff --check` não apresentar erro;
3. não houver divergência bloqueante entre Mapa do Domínio, regras de negócio, SRS e Casos de Uso;
4. o estado do Git for conhecido e adequado ao trabalho.

A aprovação do gate não deve ser inferida apenas pelo resultado de `git diff --check`; a consistência documental e o conteúdo da versão efetivamente versionada também devem ser confirmados.

Durante o MER:

- analisar candidatos do Mapa do Domínio, sem presumir que todos serão entidades;
- definir conceitos com identidade própria somente quando justificado;
- definir relacionamentos e cardinalidades na Etapa 6;
- manter dados derivados como derivados quando aplicável;
- registrar ambiguidades sem promover suposições a regra;
- consultar a fonte canônica sempre que uma decisão depender de regra de negócio.

## 7. Gates posteriores

- **Etapa 7 — DER:** somente após validação do MER.
- **Etapa 8 — Modelo Físico:** somente após validação do DER.
- **Etapa 9 — Arquitetura:** somente após compreensão e modelagem compatíveis com os gates anteriores.
- **Etapa 10 — API:** somente após arquitetura correspondente.
- **Etapa 11 — Interface Web:** conforme arquitetura e contratos aprovados.
- **Etapa 12 — Testes:** evolui conforme critérios, implementação e estratégia vigente.

A existência de arquivos ou diretórios reservados para etapas futuras não significa que essas etapas foram iniciadas.

## 8. Estado do Git na retomada

Não assumir branch, working tree limpo ou existência de commit apenas com base neste documento.

Confirmar sempre:

```bash
git branch --show-current
git status
git diff --stat
git diff --name-status
git diff --check
```

Se existirem arquivos preparados para commit, revisar o diff antes de executar `git add`.

Não executar `push`, `merge`, rebase destrutivo ou alteração da `main` sem compreender o estado atual e a estratégia de integração.

## 9. Próxima ação recomendada

Após confirmar que a Etapa 5 e os arquivos de governança atualizados estão corretamente versionados:

1. abrir uma nova unidade de trabalho para **Etapa 6 — Modelagem Conceitual (MER)**;
2. ler `docs/dominio/01-mapa-de-dominio.md`;
3. carregar somente as regras de negócio, requisitos e Casos de Uso necessários aos conceitos analisados;
4. produzir `docs/modelagem-dados/01-modelagem-conceitual-mer.md`;
5. validar o MER antes de qualquer trabalho de DER.

## 10. Arquivos-base para contexto de novas sessões

Para uma retomada eficiente, fornecer preferencialmente:

- `AGENTS.md`;
- `ORCHESTRATOR.md`;
- `project-manifest.yaml`;
- `docs/governanca/PROMPT-RETOMADA.md`;
- `docs/governanca/continuidade-de-contexto.md`.

Para tarefas da Etapa 6 em diante, incluir também:

- `docs/dominio/01-mapa-de-dominio.md`.

Depois, adicionar somente os documentos de negócio necessários à tarefa.

## 11. Documentos históricos que não devem ser usados como estado corrente

`docs/governanca/relatorio-de-consolidacao.md` deve continuar preservando o histórico da reconciliação dos `ISSUE-001` a `ISSUE-004`.

Ele é fonte de governança e proveniência, não um handoff operacional corrente.

Não reescrever o histórico somente para refletir a etapa atual.

## 12. Atualização deste documento

Atualizar este arquivo quando houver:

- conclusão ou início formal de etapa;
- mudança de gate;
- novo blocker;
- decisão humana com impacto transversal;
- alteração relevante de baseline;
- commit/merge que mude o ponto de retomada;
- interrupção do trabalho em estado que precise ser retomado.

Registrar apenas o necessário para retomada e manter a próxima ação explícita.

Última atualização operacional: **2026-08-30**.
