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
6. `docs/projeto/plano-de-desenvolvimento.md` quando a tarefa envolver processo, sequência de etapas ou gates
7. `docs/projeto/roteiro-tecnico-de-implementacao.md` somente quando a tarefa envolver preparação técnica, Modelo Físico, Arquitetura, API, Interface Web, testes ou planejamento das etapas técnicas posteriores
8. somente os documentos canônicos e derivados necessários para a tarefa atual

Em caso de divergência:

- regras de negócio e requisitos aprovados prevalecem conforme a autoridade definida em `ORCHESTRATOR.md` e `project-manifest.yaml`;
- o Mapa do Domínio organiza os conceitos aprovados da Etapa 5, mas não substitui regras de negócio, requisitos ou Casos de Uso;
- o Plano de Desenvolvimento permanece a autoridade sobre processo, sequência de etapas e gates;
- o roteiro técnico de implementação é documento auxiliar e não substitui fontes canônicas, decisões de arquitetura, modelagem validada ou gates oficiais;
- este documento descreve o estado operacional de continuidade;
- o estado real do Git deve ser confirmado antes de qualquer alteração.

## 3. Estado atual do projeto

Catálogo funcional preservado:

- `RF-001` a `RF-021`.

Baseline ativa da Versão 1:

- 20 requisitos funcionais ativos: `RF-001` a `RF-018`, `RF-020` e `RF-021`;
- `RF-019` — proteção por PIN — adiado para versão futura;
- `UC-018` e `CA-019.1` a `CA-019.5` preservados como artefatos futuros, sem renumeração;
- requisitos não funcionais padronizados como `RNF-001` a `RNF-020`.

Última etapa concluída e validada:

- **Etapa 5 — Mapa do Domínio**

Artefato da Etapa 5:

- `docs/dominio/01-mapa-de-dominio.md`

Etapa atual:

- **Etapa 6 — Modelagem Conceitual (MER)**

Situação:
- o Mapa do Domínio foi revisado para permanecer estritamente no escopo da Etapa 5;
- o Plano de Desenvolvimento foi ampliado para detalhar melhor a transição entre modelagem, arquitetura, implementação e testes, sem alterar a sequência oficial das 12 etapas;
- `docs/projeto/roteiro-tecnico-de-implementacao.md` foi definido como guia auxiliar para as etapas técnicas posteriores; ele não constitui nova etapa, não cria uma segunda numeração de fases e não autoriza antecipar gates;
- a baseline documental foi atualizada para retirar o PIN da V1;
- WordPress + PHP constituem restrição tecnológica conhecida para a aplicação Web;
- o backend específico do SGFP será implementado em plugin próprio e a solução utilizará a infraestrutura REST do WordPress;
- a implementação relacional da V1 permanece compatível com MySQL ou MariaDB;
- sistema operacional, editor de código e extensão de IDE não constituem restrições do SGFP, salvo exigência formal posterior;
- Composer é ferramenta preferencial para dependências e autoload PHP quando a etapa de implementação correspondente estiver autorizada;
- cliente HTTP para testes da API, PHPUnit e ferramentas de qualidade de código são opções preferenciais ou substituíveis conforme a necessidade, e não requisitos funcionais do produto;
- identidade, autenticação e sessão da V1 serão fornecidas pelo WordPress com e-mail e senha;
- a proteção básica das operações e dos recursos da API contra acesso não autorizado faz parte da V1; mecanismos técnicos concretos pertencem às Etapas 9 e 10;
- a cópia de segurança ordinária da V1 é criada manualmente, enviada ao e-mail cadastrado e pode ser posteriormente fornecida pelo usuário como arquivo para restauração;
- antes de uma restauração confirmada, o sistema deverá gerar e preservar em condição recuperável uma cópia automática do estado imediatamente anterior; se essa preservação falhar, a restauração será cancelada e os dados atuais permanecerão inalterados;
- a cópia pré-restauração é compatível com o mesmo processo de restauração das cópias manuais, deve ser identificável por sua origem e terá tentativa de envio por e-mail; a falha isolada do envio não bloqueia a restauração quando a preservação recuperável já foi concluída;
- backup automático periódico ou contínuo permanece fora da V1;
- `CA-021.8` registra explicitamente o envio da cópia manual por e-mail e `CA-021.9` a `CA-021.11` consolidam a proteção pré-restauração;
- `ISSUE-007` está resolvida: o significado operacional de preservar o estado imediatamente anterior foi definido como cópia de segurança automática pré-restauração; o mecanismo técnico permanece para as etapas posteriores;
- `ISSUE-008` permanece aberta: a matriz direta Regra de Negócio → Requisito ainda não foi materializada; não bloqueia a Etapa 6, mas deverá ser concluída antes do fechamento da rastreabilidade da Etapa 12;
- cardinalidades, entidades definitivas, chaves, tabelas, nulabilidade, persistência, especializações formais e decisões físicas pertencem às etapas correspondentes;
- SQLs, modelos físicos preliminares ou estruturas de banco produzidas como apoio não devem ser tratados como Modelo Físico oficial antes da validação do MER, do DER e da abertura da Etapa 8;
- estruturas exclusivas do PIN futuro não deverão ser modeladas na V1;
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

`ISSUE-007` está resolvida e não constitui mais bloqueio para a Arquitetura. A pendência `ISSUE-008` permanece **não bloqueadora da Etapa 6** e deverá ser concluída antes do fechamento final da rastreabilidade de testes.

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
- **Etapa 9 — Arquitetura:** somente após compreensão e modelagem compatíveis com os gates anteriores e após resolução de eventuais ambiguidades de negócio bloqueantes; `ISSUE-007` já se encontra resolvida.
- **Etapa 10 — API:** somente após arquitetura correspondente.
- **Etapa 11 — Interface Web:** conforme arquitetura e contratos aprovados.
- **Etapa 12 — Testes:** evolui conforme critérios, implementação e estratégia vigente; a cadeia direta Regra de Negócio → Requisito deverá estar concluída antes do fechamento final da rastreabilidade.

A existência de arquivos ou diretórios reservados para etapas futuras não significa que essas etapas foram iniciadas.

O roteiro técnico de implementação apenas detalha **como executar** atividades quando o gate correspondente estiver autorizado. Ele não altera a ordem das etapas.

Durante as Etapas 9 a 11, verificações e testes unitários, de integração, de banco ou de API podem acompanhar o desenvolvimento quando forem úteis. A Etapa 12 permanece responsável pela consolidação formal da estratégia, casos, evidências, rastreabilidade e resultados de teste.

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

1. continuar a unidade de trabalho da **Etapa 6 — Modelagem Conceitual (MER)**;
2. ler `docs/dominio/01-mapa-de-dominio.md`;
3. carregar somente as regras de negócio, requisitos e Casos de Uso necessários aos conceitos analisados;
4. produzir ou evoluir `docs/modelagem-dados/01-modelagem-conceitual-mer.md`;
5. validar o MER antes de qualquer trabalho de DER.

`ISSUE-007` deve permanecer registrada como resolvida. `ISSUE-008` deve permanecer visível, sem ser silenciosamente resolvida durante o MER.

O roteiro técnico de implementação deve permanecer como referência futura. Sua existência não altera a próxima ação atual: concluir e validar o MER antes de iniciar o DER.

## 10. Arquivos-base para contexto de novas sessões

Para uma retomada eficiente, fornecer preferencialmente:

- `AGENTS.md`;
- `ORCHESTRATOR.md`;
- `project-manifest.yaml`;
- `docs/governanca/PROMPT-RETOMADA.md`;
- `docs/governanca/continuidade-de-contexto.md`.

Para tarefas da Etapa 6 em diante, incluir também:

- `docs/dominio/01-mapa-de-dominio.md`.

Quando a tarefa envolver processo, gates ou transição entre etapas, incluir:

- `docs/projeto/plano-de-desenvolvimento.md`.

Quando a tarefa envolver preparação técnica ou as Etapas 8 a 12, incluir também, conforme necessário:

- `docs/projeto/roteiro-tecnico-de-implementacao.md`.

Depois, adicionar somente os documentos de negócio e técnicos necessários à tarefa.

## 11. Documentos históricos que não devem ser usados como estado corrente

`docs/governanca/relatorio-de-consolidacao.md` deve continuar preservando o histórico da reconciliação dos `ISSUE-001` a `ISSUE-004`.

Ele é fonte de governança e proveniência, não um handoff operacional corrente.

Não reescrever o histórico somente para refletir a etapa atual.

## 12. Atualização deste documento

Atualizar este arquivo quando houver:

- conclusão ou início formal de etapa;
- mudança de gate;
- novo blocker ou nova pendência operacional relevante;
- decisão humana com impacto transversal;
- alteração relevante de baseline;
- commit/merge que mude o ponto de retomada;
- interrupção do trabalho em estado que precise ser retomado.

Registrar apenas o necessário para retomada e manter a próxima ação explícita.

Última atualização operacional: **2026-09-02**.
