# SGFP — Prompt de Retomada em Nova Sessão

Use este prompt ao abrir um novo chat, nova sessão ou novo agente com acesso ao projeto SGFP.

**Última atualização:** 05/09/2026

## Prompt

```text
Estamos continuando o projeto SGFP.

Antes de qualquer alteração, leia obrigatoriamente:

1. AGENTS.md
2. ORCHESTRATOR.md
3. project-manifest.yaml
4. docs/governanca/continuidade-de-contexto.md

Se a tarefa envolver Arquitetura ou etapas técnicas posteriores, leia também:

5. docs/dominio/01-mapa-de-dominio.md
6. docs/modelagem-dados/01-modelagem-conceitual-mer.md
7. docs/modelagem-dados/02-modelo-entidade-relacionamento-der.md
8. docs/modelagem-dados/03-modelo-fisico.md

Se a tarefa envolver processo, sequência de etapas, gates ou transição entre etapas, consulte também:

9. docs/projeto/plano-de-desenvolvimento.md

Se a tarefa envolver preparação técnica, Arquitetura, API, Interface Web ou Testes, consulte também, quando necessário:

10. docs/projeto/roteiro-tecnico-de-implementacao.md

O roteiro técnico é apenas um documento auxiliar. Ele não substitui fontes canônicas, não cria novas etapas, não autoriza alterar a modelagem validada sem análise de impacto e não permite antecipar API, Interface Web ou Testes formais antes dos respectivos gates.

Não faça alterações ainda.

Primeiro confirme o estado real do projeto.

Se houver acesso direto ao repositório Git, verifique no mínimo:

- branch atual;
- git status;
- git diff --stat;
- git diff --name-status;
- git diff --check.

Depois compare o estado real do repositório com project-manifest.yaml, docs/governanca/continuidade-de-contexto.md e, quando pertinente, com o Plano de Desenvolvimento.

Se não houver acesso direto ao Git, use somente os documentos fornecidos e deixe explícito que o estado real do working tree não pôde ser confirmado.

Confirme explicitamente:

1. se o catálogo funcional preservado continua sendo RF-001 a RF-021;
2. se a baseline ativa da V1 continua com 20 RFs e RF-019 permanece futuro;
3. se os RNFs utilizam o padrão RNF-001 a RNF-020;
4. se docs/dominio/01-mapa-de-dominio.md corresponde à Etapa 5 validada;
5. se as Etapas 6 — MER, 7 — DER e 8 — Modelo Físico permanecem concluídas e validadas;
6. se os artefatos oficiais de modelagem estão em docs/modelagem-dados/artefatos/;
7. se a próxima etapa autorizada permanece sendo Etapa 9 — Arquitetura da Aplicação;
8. se as Etapas 10 a 12 continuam condicionadas aos respectivos gates;
9. se ISSUE-007 permanece resolvida;
10. se ISSUE-008 continua aberta, sem bloquear o início da Etapa 9, mas devendo ser concluída antes do fechamento final da rastreabilidade de testes;
11. se existe qualquer divergência entre manifesto, continuidade, Plano, fontes canônicas e, quando disponível, estado real do Git.

Considere como estado atual:

- a proteção básica das operações e dos recursos da API contra acesso não autorizado faz parte da V1;
- mecanismos técnicos concretos de proteção da API pertencem à Arquitetura e ao Desenvolvimento da API;
- a cópia de segurança ordinária da V1 é criada manualmente e enviada ao e-mail cadastrado;
- antes de uma restauração confirmada, o sistema deverá gerar e preservar em condição recuperável uma cópia automática do estado imediatamente anterior;
- se essa preservação falhar, a restauração será cancelada;
- a cópia automática pré-restauração é uma proteção pontual e não caracteriza backup automático periódico ou contínuo;
- ISSUE-007 está resolvida por decisão humana, permanecendo apenas o mecanismo técnico para as etapas posteriores;
- ISSUE-008 permanece aberta como pendência de rastreabilidade;
- a associação de Categoria ao Compromisso Financeiro é opcional na V1;
- um compromisso pode ser cadastrado e permanecer sem categoria;
- a V1 utilizará PHP sobre WordPress;
- o backend específico do SGFP será implementado em plugin próprio;
- a solução utilizará a infraestrutura REST do WordPress;
- a implementação relacional permanece compatível com MySQL ou MariaDB;
- sistema operacional, editor e extensões de desenvolvimento não são restrições do SGFP, salvo exigência formal posterior;
- ferramentas como Composer, clientes HTTP, PHPUnit e utilitários de qualidade podem ser adotadas quando forem úteis e quando a etapa correspondente estiver autorizada;
- a presença dessas ferramentas no roteiro técnico não as transforma em requisitos do produto;
- MER, DER e Modelo Físico da V1 estão concluídos e validados; os artefatos oficiais estão em `docs/modelagem-dados/` e `docs/modelagem-dados/artefatos/`.
- a natureza de uma transferência é determinada em relação à Conta Principal: Principal → Secundária = Saída; Secundária → Principal = Entrada.

Não invente decisões de negócio.
Não altere arquivos no primeiro turno.
Não execute git add, commit, push, merge, rebase destrutivo ou troca de branch antes de apresentar o diagnóstico e receber autorização quando necessária.

Para a Etapa 9:

- use o MER, o DER e o Modelo Físico validados como entradas técnicas;
- consulte regras de negócio, requisitos e Casos de Uso sempre que uma decisão arquitetural depender do comportamento do sistema;
- mantenha WordPress, PHP, plugin próprio, WordPress REST API e MySQL/MariaDB como restrições tecnológicas já consolidadas;
- defina explicitamente responsabilidades, dependências, acesso a dados, estratégia de criação/evolução das tabelas e integração com identidade/autenticação do WordPress;
- não transforme automaticamente tabela, entidade conceitual e classe PHP na mesma representação;
- não altere a modelagem validada apenas para acomodar preferência de implementação;
- considere ISSUE-007 como resolvida;
- mantenha ISSUE-008 visível como pendência não bloqueadora;
- não inicie a implementação da API antes de validar a arquitetura correspondente;

Para tarefas técnicas futuras:

- siga sempre os gates oficiais do Plano de Desenvolvimento;
- use o roteiro técnico apenas para orientar como executar a etapa já autorizada;
- não trate preferência de ferramenta como restrição do sistema;
- explique o problema, o conceito e a finalidade de uma ferramenta antes de introduzi-la;
- prefira implementação incremental e verificável;
- permita testes durante o desenvolvimento quando úteis;
- preserve a Etapa 12 como consolidação formal da estratégia, casos, evidências, rastreabilidade e resultados de teste.

Se houver divergência entre documentos, aplique a autoridade definida em ORCHESTRATOR.md e project-manifest.yaml e utilize a fonte canônica correspondente ao assunto.

Não resolva conflitos de negócio silenciosamente.
```

## Uso recomendado

### Agente com acesso direto ao repositório

Execute o prompt na raiz do repositório e permita somente leitura e diagnóstico no primeiro turno.

O agente deve confirmar o estado real do Git antes de assumir que um arquivo, branch, commit ou alteração existe.

### ChatGPT ou outro assistente sem acesso direto ao diretório local

Forneça, no mínimo:

- `AGENTS.md`;
- `ORCHESTRATOR.md`;
- `project-manifest.yaml`;
- `docs/governanca/continuidade-de-contexto.md`;
- `docs/dominio/01-mapa-de-dominio.md` para tarefas que dependam dos conceitos do domínio;
- `docs/modelagem-dados/01-modelagem-conceitual-mer.md`, `02-modelo-entidade-relacionamento-der.md` e `03-modelo-fisico.md` para a Etapa 9 e tarefas técnicas que dependam da modelagem;
- `docs/projeto/plano-de-desenvolvimento.md` quando a tarefa envolver processo, gates ou transição entre etapas;
- `docs/projeto/roteiro-tecnico-de-implementacao.md` quando a tarefa envolver Arquitetura, API, Interface Web, testes ou preparação técnica;
- apenas as regras, requisitos, Casos de Uso e artefatos técnicos necessários à tarefa.

Não é necessário enviar o repositório inteiro quando a tarefa puder ser executada com contexto menor e suficiente.

Se não houver acesso ao Git, o assistente não deve afirmar que o working tree está limpo, que um commit existe ou que um arquivo já foi incorporado ao repositório.

## Regra de segurança operacional

O primeiro turno de retomada deve ser **somente leitura e diagnóstico**.

Alterações só começam após confirmação explícita do estado corrente e da tarefa autorizada.

A existência do roteiro técnico de implementação não altera essa regra e não significa que o projeto já tenha chegado à implementação.
