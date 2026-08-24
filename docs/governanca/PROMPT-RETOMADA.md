# SGFP — Prompt de Retomada em Nova Sessão

Use este prompt ao abrir um novo chat, nova sessão ou novo agente com acesso ao repositório SGFP.

## Prompt

```text
Estamos continuando o projeto SGFP.

Antes de qualquer alteração, leia obrigatoriamente:

1. AGENTS.md
2. ORCHESTRATOR.md
3. project-manifest.yaml
4. docs/governanca/continuidade-de-contexto.md

Se a Skill $project-orchestrator estiver disponível, use-a para coordenar a retomada e qualquer trabalho subsequente que exija decomposição, validação, integração ou replanejamento.

Não faça alterações ainda.

Primeiro confirme o estado real do Git com, no mínimo:

- branch atual;
- git status;
- git diff --stat;
- git diff --name-status;
- git diff --check.

Depois compare o estado real do repositório com docs/governanca/continuidade-de-contexto.md.

Informe:

1. onde o projeto está;
2. qual trabalho está em andamento;
3. qual é a próxima ação obrigatória;
4. quais blockers ou validações ainda estão pendentes;
5. se existe qualquer divergência entre o estado do Git, o manifesto e o documento de continuidade.

Não invente decisões de negócio.
Não altere arquivos.
Não execute git add, commit, push, merge ou troca de branch até apresentar esse diagnóstico.

Se houver divergência entre o documento de continuidade e as fontes oficiais, preserve o estado real do Git e aplique a autoridade definida em ORCHESTRATOR.md e project-manifest.yaml. Não resolva a divergência silenciosamente.
```

## Uso recomendado

### OpenClaw

Na raiz do repositório, copie apenas o bloco `Prompt` acima para a nova sessão do agente `sgfp`.

### ChatGPT ou outro assistente sem acesso direto ao diretório local

Se o novo chat não tiver acesso ao repositório local, forneça os arquivos necessários ou conecte-o ao repositório antes de pedir a retomada.

O prompt não substitui acesso às fontes.

## Regra de segurança operacional

O primeiro turno de retomada deve ser **somente leitura e diagnóstico**.

Alterações só começam após confirmação explícita do estado corrente.
