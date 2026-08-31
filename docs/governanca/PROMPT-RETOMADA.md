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

Se a tarefa envolver a Etapa 6 ou qualquer modelagem posterior, leia também:

5. docs/dominio/01-mapa-de-dominio.md

Não faça alterações ainda.

Primeiro confirme o estado real do Git com, no mínimo:

- branch atual;
- git status;
- git diff --stat;
- git diff --name-status;
- git diff --check.

Depois compare o estado real do repositório com project-manifest.yaml e docs/governanca/continuidade-de-contexto.md.

Confirme explicitamente:

1. se a baseline funcional oficial continua sendo RF-001 a RF-021, com 20 RFs ativos e RF-019 futuro;
2. se os RNFs utilizam o padrão RNF-001 a RNF-020;
3. se docs/dominio/01-mapa-de-dominio.md existe e corresponde à Etapa 5 validada;
4. se a Etapa 5 está concluída;
5. se a etapa atual é Etapa 6 — Modelagem Conceitual (MER);
6. quais arquivos ou validações estão pendentes no working tree;
7. se ISSUE-007 permanece resolvida e se ISSUE-008 continua aberta, indicando o gate afetado pela pendência remanescente;
8. se existe qualquer divergência entre Git, manifesto, continuidade e fontes canônicas.

Considere como estado atual:
- a proteção básica das operações e dos recursos da API contra acesso não autorizado faz parte da V1;
- mecanismos técnicos concretos de proteção da API pertencem à Arquitetura e ao Desenvolvimento da API;
- a cópia de segurança ordinária da V1 é criada manualmente e enviada ao e-mail cadastrado; antes de uma restauração confirmada, o sistema deverá gerar e preservar em condição recuperável uma cópia automática do estado imediatamente anterior, cancelando a restauração se essa preservação falhar;
- a cópia automática pré-restauração é uma proteção pontual e não caracteriza backup automático periódico ou contínuo;
- ISSUE-007: resolvida por decisão humana com a definição da cópia de segurança pré-restauração, permanecendo apenas o mecanismo técnico para as etapas posteriores;
- ISSUE-008: a matriz direta Regra de Negócio → Requisito ainda deverá ser materializada antes do fechamento final da rastreabilidade de testes.

Não invente decisões de negócio.
Não altere arquivos no primeiro turno.
Não execute git add, commit, push, merge ou troca de branch antes de apresentar o diagnóstico.

Para a Etapa 6:
- use o Mapa do Domínio como entrada conceitual;
- consulte regras de negócio, requisitos e Casos de Uso quando necessário;
- considere somente requisitos ativos da V1 ao definir o MER atual;
- não crie entidade, atributo ou persistência para o PIN, pois RF-019 está adiado para versão futura;
- não trate os candidatos do Mapa do Domínio como entidades já decididas;
- defina no MER apenas o que pertence à Modelagem Conceitual;
- trate WordPress como restrição tecnológica conhecida, sem antecipar detalhes que pertencem à Arquitetura;
- considere ISSUE-007 como resolvida e mantenha ISSUE-008 visível sem tentar resolvê-la por inferência durante o MER;
- não antecipe DER, Modelo Físico, Arquitetura ou Implementação.

Se houver divergência entre documentos, aplique a autoridade definida em ORCHESTRATOR.md e project-manifest.yaml e não resolva conflitos de negócio silenciosamente.
```

## Uso recomendado

### Agente com acesso direto ao repositório

Execute o prompt na raiz do repositório e permita somente leitura e diagnóstico no primeiro turno.

### ChatGPT ou outro assistente sem acesso direto ao diretório local

Forneça, no mínimo:

- `AGENTS.md`;
- `ORCHESTRATOR.md`;
- `project-manifest.yaml`;
- `docs/governanca/continuidade-de-contexto.md`;
- `docs/dominio/01-mapa-de-dominio.md` para tarefas da Etapa 6 em diante;
- apenas as regras, requisitos e Casos de Uso necessários à tarefa.

Não é necessário enviar o repositório inteiro quando a tarefa puder ser executada com contexto menor e suficiente.

## Regra de segurança operacional

O primeiro turno de retomada deve ser **somente leitura e diagnóstico**.

Alterações só começam após confirmação explícita do estado corrente.
