# PROMPTS OPENCLAW — SGFP

Este arquivo consolida os prompts operacionais recomendados para trabalhar no projeto **SGFP — Sistema de Gestão Financeira Pessoal** através do OpenClaw, usando o **Adaptive AI Orchestrator** como porta de entrada e as skills especializadas somente quando forem necessárias.

## Regra simples

Use este fluxo:

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

Preencha principalmente a seção **OBJETIVO DESTA SESSÃO** e, quando houver, **MATERIAL ADICIONAL**.

```text
Quero trabalhar profissionalmente no projeto SGFP.

Use o `adaptive-orchestrator-bridge` como porta de entrada para o
Adaptive AI Orchestrator.

Use `engineering-lifecycle` para determinar o fluxo de engenharia e
selecionar apenas as skills necessárias ao objetivo desta sessão.

O repositório possui documentação robusta e governança própria.
Não substitua documentação existente por suposições.

Antes de agir:

1. descubra o estado atual do projeto;
2. leia primeiro os documentos de governança e roteamento definidos
   pelo próprio repositório;
3. localize apenas as fontes canônicas necessárias à tarefa;
4. determine a etapa/gate atual;
5. identifique fatos, inferências e dúvidas reais separadamente;
6. determine o próximo trabalho seguro;
7. selecione as capacidades necessárias.

PROJETO
SGFP — Sistema de Gestão Financeira Pessoal

REPOSITÓRIO
oliveiraariel/SGFP---Financeiro-Pessoal

OBJETIVO DESTA SESSÃO
[ESCREVER AQUI]

MATERIAL ADICIONAL
[URLs, screenshots, documentos ou referências, se houver]

AUTORIDADE
Você pode executar mudanças não destrutivas necessárias ao objetivo,
dentro do projeto e da etapa autorizada.

Não está autorizado sem nova confirmação a:
- mudar regra de negócio;
- mudar escopo da V1;
- ignorar conflito documental;
- apagar trabalho;
- expor credenciais;
- publicar/deploy;
- efetuar mudanças externas irreversíveis.

Não use todas as skills por padrão. Selecione a menor combinação capaz
de produzir evidência suficiente.

Ao completar cada ciclo significativo:
- valide;
- revise;
- atualize artefatos afetados quando necessário;
- preserve rastreabilidade.

Se chegar a uma decisão humana obrigatória, pare somente a parte
afetada e explique:
1. qual decisão é necessária;
2. quais opções existem;
3. impacto de cada uma;
4. sua recomendação.

Ao finalizar a sessão, use `project-handoff` e deixe o próximo passo
inequívoco.
```

## Exemplos de objetivo

### Arquitetura

```text
OBJETIVO DESTA SESSÃO

Iniciar ou continuar a Etapa 9 — Arquitetura da Aplicação do SGFP,
utilizando as fontes canônicas existentes e sem iniciar implementação
da API antes que a arquitetura correspondente esteja suficientemente
definida e validada.
```

### Backend / API

```text
OBJETIVO DESTA SESSÃO

Implementar a próxima unidade autorizada do backend/API do SGFP,
respeitando arquitetura, requisitos, casos de uso, modelo de dados,
governança e critérios de aceitação já aprovados.
```

### Frontend / WordPress

```text
OBJETIVO DESTA SESSÃO

Trabalhar na interface Web do SGFP em WordPress, preservando regras de
negócio e arquitetura existentes, usando `web-frontend-design` quando
necessário para UI, responsividade, acessibilidade e integração visual.
```

---

# 2. PROMPT CURTO — CONTINUAR

Use quando quiser que o OpenClaw continue a partir do trabalho já feito, sem reenviar o Prompt Mestre inteiro.

```text
Continue a partir do estado atual.

Não refaça discovery já concluído sem necessidade.
Consulte o último handoff e as fontes canônicas que ele referencia.

Use o Adaptive AI Orchestrator e reavalie o próximo trabalho seguro da
frontier, selecionando somente as skills necessárias.

Prossiga dentro da etapa e dos gates autorizados.

Pare somente diante de blocker real, decisão humana necessária ou
conclusão do escopo atual.
```

---

# 3. PROMPT CURTO — PARAR E FAZER HANDOFF

Use quando quiser encerrar a sessão de maneira controlada.

```text
Pare ao concluir a unidade atômica de trabalho atualmente em andamento.

Não inicie uma nova unidade.

Use `project-handoff` e me entregue:

- objetivo desta sessão;
- estado atual;
- trabalho concluído;
- arquivos, commits ou artefatos modificados;
- testes e revisões executados;
- decisões tomadas e respectivas fontes;
- blockers;
- riscos ou incertezas restantes;
- próximo trabalho executável;
- skills recomendadas para a próxima sessão.

Não copie documentos grandes para o handoff; referencie as fontes
canônicas por caminho.
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
| Trabalho real de projeto | Preferir **Adaptive → engineering-lifecycle → skills necessárias** |

---

# Princípio operacional

O usuário define **o objetivo**.

O **Adaptive AI Orchestrator** organiza o trabalho.

O `engineering-lifecycle` ajuda a selecionar o fluxo de engenharia.

As skills especializadas executam somente as capacidades necessárias.

```text
Usuário
  ↓
OpenClaw
  ↓
adaptive-orchestrator-bridge
  ↓
Adaptive AI Orchestrator
  ↓
engineering-lifecycle
  ↓
skills necessárias
  ↓
execução / validação / handoff
```

Não tente usar todas as skills manualmente em toda tarefa.

---

# Fonte de verdade do projeto

Antes de qualquer trabalho relevante, o OpenClaw/Adaptive deve respeitar as fontes de governança e roteamento já existentes no SGFP, especialmente:

- `project-manifest.yaml`
- `ORCHESTRATOR.md`
- documentação canônica indicada pelo próprio repositório
- handoff mais recente, quando houver

Este arquivo **não substitui a governança do projeto**. Ele apenas padroniza como iniciar, continuar e encerrar sessões no OpenClaw.
