# PROMPTS OPENCLAW — SGFP

Este arquivo consolida os prompts operacionais recomendados para trabalhar no projeto **SGFP — Sistema de Gestão Financeira Pessoal** através do OpenClaw, usando o **Adaptive AI Orchestrator** como porta de entrada e as skills especializadas somente quando forem necessárias.

Este arquivo não substitui a governança do SGFP. `project-manifest.yaml`, `ORCHESTRATOR.md` e as fontes canônicas indicadas pelo próprio repositório continuam sendo a autoridade para estado, gates, regras e decisões.

## Regra simples

```text
INICIAR UMA SESSÃO SÉRIA
→ Prompt Mestre

CONTINUAR A MESMA LINHA DE TRABALHO
→ Prompt de Continuação

ENCERRAR A SESSÃO DE FORMA ORGANIZADA
→ Prompt de Parada + Handoff
```

Não é necessário colar os três prompts juntos.

---

# 1. PROMPT MESTRE — INICIAR SESSÃO

Use este prompt ao iniciar uma sessão séria de trabalho no SGFP, especialmente para arquitetura, backend, API, frontend, testes, revisão ou evolução documental ligada ao desenvolvimento.

Preencha principalmente **OBJETIVO DESTA SESSÃO** e, quando houver, **MATERIAL ADICIONAL**.

```text
Quero trabalhar profissionalmente no projeto SGFP.

Use o `adaptive-orchestrator-bridge` como porta de entrada para o
Adaptive AI Orchestrator em modo multiagente de projeto.

Use o agente/workspace OpenClaw `sgfp` como owner do projeto quando ele estiver
disponível e corretamente configurado para este repositório. Não substitua esse
owner por `main` sem verificar o ambiente ou existir motivo explícito.

Use `engineering-lifecycle` como capacidade de engenharia e selecione apenas as
skills necessárias por Work Unit. O Adaptive, e não a skill, deve manter a
autoridade sobre Work Graph, dependências, ready frontier, workers, concorrência,
fan-in, avaliação e replanning.

O repositório possui documentação robusta e governança própria.
Não substitua documentação existente por suposições.

Antes de agir:
1. confirme o estado real do Git e do projeto;
2. leia primeiro os documentos de governança e roteamento definidos pelo próprio
   repositório, na ordem exigida por `ORCHESTRATOR.md`/`project-manifest.yaml`;
3. localize somente as fontes canônicas necessárias à tarefa;
4. determine a etapa e o gate atualmente autorizados;
5. diferencie fatos, inferências, conflitos e dúvidas realmente bloqueantes;
6. determine o próximo trabalho seguro;
7. construa/revise um Work Graph acíclico com dependências reais;
8. selecione as capacidades/skills mínimas necessárias por Work Unit.

PROJETO
SGFP — Sistema de Gestão Financeira Pessoal

REPOSITÓRIO
oliveiraariel/SGFP---Financeiro-Pessoal

OBJETIVO DESTA SESSÃO
[ESCREVER AQUI]

MATERIAL ADICIONAL
[URLs, screenshots, documentos ou referências, se houver]

GATES E ORDEM
Não presuma que Backend e Frontend formam uma fila rígida, mas também não pule
gates oficiais.

No estado documentado em 08/09/2026, a Etapa 8 está validada e a próxima etapa
autorizada é a Etapa 9 — Arquitetura da Aplicação. As Etapas 10 — API e 11 —
Interface Web continuam condicionadas aos gates correspondentes. Sempre confirme
essa situação nas fontes atuais antes de agir, pois este arquivo pode ficar mais
antigo que o `project-manifest.yaml`.

Enquanto a Arquitetura correspondente ainda não estiver suficientemente definida
e validada, não antecipe implementação da API ou decisões de interface que dela
dependam.

Quando contratos/interfaces/decisões compartilhadas estiverem suficientemente
estáveis e os gates permitirem, exponha o paralelismo real do projeto. A ready
frontier pode conter, por exemplo:
- backend + backend;
- frontend + frontend;
- backend + frontend consumidor do mesmo contrato;
- implementação + testes/review/security;
- pesquisas ou análises independentes.

Um rótulo de camada não constitui dependência por si só. Adicione bloqueio apenas
quando houver pré-requisito real.

SCHEDULING CONTÍNUO
O número de workers deve seguir a ready frontier útil e a economicidade de
tokens/contexto. Não crie um pool fixo nem maximize agentes por si só. Use 1, 2,
3, 4, 6 ou mais workers somente quando houver trabalho útil, independente e
compatível com o limite configurado.

Quando um worker terminar, avalie/finalize seu resultado imediatamente. Se o
resultado for aceito e desbloquear nova Work Unit, recalcule a ready frontier e,
havendo slot livre e segurança de recursos, despache o novo worker sem esperar
workers independentes ainda ativos. `dispatch generation` é registro operacional,
não uma barreira de sincronização.

FAN-IN
Resultados de workers não devem circular informalmente entre agentes como fonte
de verdade. O Adaptive recebe, avalia e somente então propaga o contexto de
resultados aceitos. Quando ramos paralelos convergirem, crie Work Unit explícita
de integração, testes, síntese, revisão ou outra verificação adequada.

GIT, WORKSPACE E ESCRITA PARALELA
Respeite integralmente a política multiagente do SGFP: trabalho paralelo com
escrita deve usar branch/worktree isolado e ownership explícito. Não faça escrita
multiagente diretamente na `main`.

O Adaptive v0.4 também exige `write_paths` literais, precisos e relativos ao
repositório para Work Units que solicitam `filesystem.write`. Escopos absolutos,
com `..`, glob, amplos, desconhecidos ou sobrepostos não são seams seguros.

Se a execução atual NÃO conseguir comprovar branch/worktree isolado para cada
worker escritor, não enfraqueça a governança do SGFP: serialize as Work Units que
escrevem no checkout, embora trabalho read-only independente possa continuar em
paralelo. Não alegue isolamento que não exista.

AUTORIDADE
Você pode executar mudanças não destrutivas necessárias ao objetivo, dentro do
projeto, da etapa autorizada e dos side effects efetivamente concedidos.

Se o objetivo autoriza edição do repositório, permita somente o efeito necessário
`filesystem.write`. Isso não autoriza automaticamente deploy, publicação,
credenciais, operações externas, alteração de escopo ou ações destrutivas.

Não está autorizado sem nova confirmação a:
- mudar regra de negócio;
- mudar escopo da V1;
- resolver silenciosamente conflito entre fontes canônicas;
- antecipar etapa bloqueada por gate;
- substituir arquitetura aprovada sem análise de impacto;
- apagar trabalho útil ou reescrever histórico destrutivamente;
- expor credenciais/dados sensíveis;
- publicar/deploy;
- efetuar mudanças externas irreversíveis.

QUALIDADE
Runtime completion não prova correção semântica. Use Work Units de testes,
integração, code review, security review, accessibility/UI review ou outras
evidências quando o risco/escopo exigir.

Não use todas as skills por padrão. Selecione a menor combinação capaz de
produzir evidência suficiente. Não microfragmente o trabalho apenas para aumentar
o número de workers.

Ao completar cada resultado significativo:
- avalie e finalize;
- atualize dependências/frontier;
- valide;
- revise;
- atualize artefatos diretamente afetados quando necessário;
- preserve rastreabilidade.

Se chegar a uma decisão humana obrigatória, pare somente a parte afetada e
continue trabalho independente quando seguro. Explique:
1. qual decisão é necessária;
2. quais opções existem;
3. impacto de cada uma;
4. sua recomendação.

Ao finalizar a sessão, use `project-handoff` e deixe o próximo passo inequívoco,
incluindo Work Units concluídas/bloqueadas, dispatch generations relevantes,
`max_parallelism_observed` quando disponível, fan-in realizado, evidências,
blockers e próxima ready frontier.
```

## Exemplos de objetivo

### Arquitetura — objetivo recomendado agora

```text
OBJETIVO DESTA SESSÃO

Confirmar o estado oficial do SGFP e iniciar/continuar a Etapa 9 — Arquitetura da
Aplicação, usando as fontes canônicas e a modelagem validada. Estruture o trabalho
em Work Units, explore paralelismo seguro para análises independentes e não inicie
implementação da API ou da Interface Web antes dos respectivos gates.
```

### Backend / API — usar quando o gate estiver liberado

```text
OBJETIVO DESTA SESSÃO

Implementar a próxima unidade autorizada do backend/API do SGFP, respeitando a
arquitetura já validada, requisitos, casos de uso, modelo de dados, governança e
critérios de aceitação. Explore paralelismo backend/backend, backend+testes e,
quando houver contrato estável e gate compatível, backend+frontend.
```

### Frontend / WordPress — usar quando o gate estiver liberado

```text
OBJETIVO DESTA SESSÃO

Trabalhar na próxima unidade autorizada da Interface Web do SGFP em WordPress,
preservando regras de negócio, arquitetura e contratos aprovados. Use
`web-frontend-design` quando necessário e explore frontend/frontend ou
frontend+backend em paralelo somente quando as dependências e o isolamento de
workspace exigido pelo projeto permitirem.
```

---

# 2. PROMPT CURTO — CONTINUAR

Use quando quiser que o OpenClaw continue a partir do trabalho já feito, sem reenviar o Prompt Mestre inteiro.

```text
Continue a partir do estado atual usando o `adaptive-orchestrator-bridge` em modo
multiagente de projeto e o owner `sgfp` quando disponível.

Não refaça discovery já concluído sem necessidade. Consulte o último handoff e as
fontes canônicas que ele referencia. Reconfirme gate/etapa se houver qualquer
chance de mudança desde a sessão anterior.

Reavalie o Work Graph e a ready frontier. Se múltiplas Work Units independentes
estiverem prontas, use somente o paralelismo útil e permitido.

Quando um resultado aceito liberar novo trabalho, recalcule a frontier e ocupe um
slot livre imediatamente sem esperar workers independentes ainda ativos.

Respeite a política do SGFP de branch/worktree isolado para trabalho paralelo com
escrita. Se o isolamento não estiver comprovado na execução atual, serialize
writers; não enfraqueça a governança para obter paralelismo artificial.

Faça fan-in explícito quando ramos convergirem e propague downstream somente
resultados aceitos.

Prossiga dentro da etapa/gates e side effects autorizados. Pare somente diante de
blocker real, decisão humana necessária, conflito canônico, operação não
autorizada ou conclusão do escopo atual.
```

---

# 3. PROMPT CURTO — PARAR E FAZER HANDOFF

Use quando quiser encerrar a sessão de maneira controlada.

```text
Encerre esta sessão de forma controlada.

A partir deste pedido, não despache novas Work Units e não reabasteça slots
liberados. Permita apenas que workers já ativos terminem ou atinjam
blocker/timeout controlado; recolha e avalie/finalize seus resultados. Não abandone
worker ativo silenciosamente.

Se houver sinal de replanning ainda não executado, preserve-o como pendência no
handoff em vez de iniciar novo ciclo de trabalho.

Use `project-handoff` e entregue:
- objetivo desta sessão;
- etapa/gate e estado atual do Work Graph;
- Work Units concluídas, bloqueadas, revision-required ou não iniciadas;
- dispatch generations relevantes e `max_parallelism_observed`;
- último fan-out/fan-in e resultados aceitos relevantes;
- branches/worktrees/ownership utilizados, quando houver;
- arquivos, commits, PRs ou artefatos modificados;
- testes, verificações e revisões executados;
- decisões tomadas e respectivas fontes;
- blockers e sinais de replan pendentes;
- riscos ou incertezas restantes;
- próxima ready frontier/trabalho executável;
- skills/capacidades recomendadas para a próxima sessão.

Não copie documentos grandes; referencie as fontes canônicas por caminho.
Não exponha credenciais ou dados sensíveis.
```

---

# Como escolher qual prompt usar

| Situação | Prompt |
|---|---|
| Início de uma nova sessão séria | **Prompt Mestre** |
| Continuação da mesma linha de trabalho | **Prompt de Continuação** |
| Encerrar sem perder contexto | **Prompt de Parada + Handoff** |
| Pergunta simples e conceitual | Nenhum destes é necessário |
| Pequena análise visual isolada | Pode usar diretamente `web-frontend-design`, se fizer sentido |
| Trabalho real de projeto | Preferir **Bridge multiagente → Adaptive → engineering-lifecycle → skills por Work Unit** |

---

# Princípio operacional

```text
Usuário
  ↓
OpenClaw / owner sgfp
  ↓
adaptive-orchestrator-bridge --multi-agent
  ↓
Adaptive AI Orchestrator
  ↓
Work Graph + ready frontier
  ↓
workers lógicos + skills mínimas
  ↓
execução lateral continuamente reabastecida quando segura
  ↓
avaliação / fan-in / replanning limitado
  ↓
validação / rastreabilidade / handoff
```

O usuário define **objetivo e autoridade**. O Adaptive coordena. As skills fornecem
capacidades especializadas por Work Unit. Não tente usar todas as skills nem um
número fixo de agentes manualmente.

---

# Fonte de verdade do projeto

Antes de qualquer trabalho relevante, o OpenClaw/Adaptive deve respeitar as fontes de governança e roteamento do SGFP, especialmente:

- `project-manifest.yaml`;
- `ORCHESTRATOR.md`;
- `docs/governanca/continuidade-de-contexto.md`;
- documentação canônica indicada pelo próprio repositório;
- handoff mais recente, quando houver.

Na baseline consultada em 08/09/2026, o `project-manifest.yaml` registra `stage_9_ready_to_start`, Etapa 8 como última concluída e Etapa 9 — Arquitetura da Aplicação como próxima etapa. Ele também exige trabalho paralelo isolado por branch/worktree, ownership explícito e proíbe writes multiagente diretamente na `main`.

Este arquivo apenas padroniza como iniciar, continuar e encerrar sessões no OpenClaw; se ficar desatualizado, prevalecem as fontes de governança/canônicas atuais.
