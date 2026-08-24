# ORCHESTRATOR.md — Contrato Operacional para Agentes

**Projeto:** SGFP — Sistema de Gestão Financeira Pessoal  
**Versão deste documento:** 2.0  
**Status:** Governança operacional vigente  
**Última atualização:** 24/08/2026

## 1. Finalidade

Este arquivo define como um orchestrator, agente autônomo ou conjunto de agentes deve trabalhar neste repositório.

Seu objetivo é garantir que qualquer automação:

- respeite as decisões já aprovadas;
- consulte as fontes corretas antes de agir;
- preserve a rastreabilidade entre artefatos;
- não transforme hipóteses em decisões;
- não antecipe etapas do desenvolvimento;
- produza alterações pequenas, verificáveis e auditáveis;
- solicite decisão humana quando houver ambiguidade real de negócio.

Este documento é um **contrato operacional**. Ele não substitui Documento de Visão, Plano de Desenvolvimento, Levantamento de Requisitos, SRS, Casos de Uso ou documentos técnicos posteriores.

## 2. Princípio fundamental

Todo trabalho deverá seguir, sempre que aplicável, a sequência:

**Compreender → Localizar a fonte → Verificar consistência → Identificar impactos → Executar → Validar → Atualizar rastreabilidade → Relatar**

Nenhum agente deverá utilizar preferência técnica, convenção de mercado ou inferência própria para alterar uma decisão de negócio já documentada.

A autoridade final sobre:

- ambiguidades de negócio;
- mudança de escopo;
- inclusão ou remoção de funcionalidades da V1;
- conflitos entre decisões canônicas;
- decisões arquiteturais de alto impacto;

é humana.

## 3. Estado atual do projeto

O projeto possui artefatos produzidos até a **Etapa 4 — Casos de Uso**. A baseline funcional da V1 foi reconciliada em 21 requisitos funcionais identificados de `RF-001` a `RF-021`.

A próxima etapa planejada é:

**Etapa 5 — Mapa do Domínio**

A entrada efetiva na Etapa 5 ainda não foi iniciada. O gate está **apto para reavaliação/liberação posterior**, após a reconciliação documental registrada em:

- `project-manifest.yaml`;
- `docs/governanca/relatorio-de-consolidacao.md`.

Os bloqueadores `ISSUE-001` a `ISSUE-004` foram resolvidos documentalmente nesta baseline. A abertura efetiva da Etapa 5 continua sendo uma ação posterior; até ela ocorrer, agentes não devem iniciar modelagem do domínio, modelagem de dados ou implementação como se essa etapa estivesse em execução.

## 4. Ordem mínima de leitura

Antes de executar qualquer tarefa, o agente coordenador deverá ler:

1. `project-manifest.yaml`
2. `docs/governanca/relatorio-de-consolidacao.md`
3. `docs/projeto/plano-de-desenvolvimento.md`
4. `docs/projeto/documento-de-visao.md`

Depois, deverá carregar somente os documentos necessários à tarefa:

5. `docs/requisitos/levantamento/README.md` e módulos de negócio envolvidos;
6. `docs/requisitos/srs/README.md` e seções do SRS envolvidas;
7. `docs/casos-de-uso/README.md` e Casos de Uso relacionados;
8. artefatos das etapas posteriores, quando já existirem e forem relevantes.

Não é necessário carregar todo o repositório para cada tarefa. O contexto deve ser suficiente para garantir correção, sem leitura indiscriminada.

## 5. Autoridade documental por assunto

A autoridade de cada documento depende do tipo de informação.

| Assunto | Fonte principal |
|---|---|
| Processo, sequência de etapas e gates | `docs/projeto/plano-de-desenvolvimento.md` |
| Visão, propósito e escopo geral do produto | `docs/projeto/documento-de-visao.md` |
| Regras de negócio e decisões do domínio | `docs/requisitos/levantamento/` |
| Requisitos funcionais e não funcionais | `docs/requisitos/srs/` |
| Interações ator–sistema | `docs/casos-de-uso/` |
| Conceitos do domínio | `docs/dominio/` |
| Modelagem conceitual, DER e modelo físico | `docs/modelagem-dados/` |
| Decisões de arquitetura | `docs/arquitetura/` |
| Implementação da API | `docs/api/` |
| Implementação da interface Web | `docs/interface-web/` |
| Estratégia, casos e resultados de teste | `docs/testes/` |
| Proveniência, inventário e consolidação | `docs/governanca/` |

`project-manifest.yaml` e `ORCHESTRATOR.md` são fontes de **governança e roteamento**, não fontes de regras de negócio.

O relatório de consolidação registra problemas e decisões de reorganização, mas não deve ser utilizado para substituir silenciosamente conteúdo normativo.

## 6. Regra de precedência e conflitos

Não existe uma regra global do tipo “o arquivo mais recente sempre vence”.

Quando duas fontes canônicas divergirem:

1. identificar os arquivos e os identificadores envolvidos;
2. classificar o problema como:
   - contradição;
   - omissão;
   - artefato derivado desatualizado;
   - decisão técnica ainda não definida;
   - possível mudança de escopo;
3. verificar se existe resolução explícita já incorporada a uma fonte canônica;
4. se houver resolução inequívoca, utilizá-la e atualizar os artefatos derivados afetados;
5. se permanecer ambiguidade de negócio, interromper apenas a parte afetada e solicitar decisão humana;
6. continuar trabalhos independentes que não dependam da decisão, quando isso for seguro;
7. após a decisão, atualizar no mesmo conjunto de mudanças todos os artefatos diretamente afetados.

É proibido “resolver” conflito apenas porque uma alternativa parece mais comum, elegante ou tecnicamente conveniente.

## 7. Estado dos bloqueadores documentais

Os quatro bloqueadores registrados foram resolvidos na baseline funcional V1:

- `ISSUE-001`: adotado catálogo definitivo de 21 RFs (`RF-001` a `RF-021`) e realinhados os derivados;
- `ISSUE-002`: formalizados cadastro, autenticação e senha como `RF-001`, `RF-002` e `RF-003`;
- `ISSUE-003`: exclusão de contas registrada como fora do escopo da V1;
- `ISSUE-004`: valor inicial da principal representado por Entrada, valor de secundária por transferência, sem saldo inicial armazenado.

Os status, resoluções e evidências permanecem registrados em `project-manifest.yaml` e `docs/governanca/relatorio-de-consolidacao.md`. O gate da Etapa 5 está tecnicamente apto para reavaliação, mas a etapa não foi iniciada.

## 8. Etapas e diretórios

O Plano de Desenvolvimento define 12 etapas. A árvore do repositório agrupa algumas delas por finalidade documental.

| Etapa | Conteúdo | Diretório principal |
|---|---|---|
| 1 | Documento de Visão | `docs/projeto/` |
| 2 | Levantamento de Requisitos | `docs/requisitos/levantamento/` |
| 3 | SRS | `docs/requisitos/srs/` |
| 4 | Casos de Uso | `docs/casos-de-uso/` |
| 5 | Mapa do Domínio | `docs/dominio/` |
| 6 | Modelagem Conceitual | `docs/modelagem-dados/` |
| 7 | DER | `docs/modelagem-dados/` |
| 8 | Modelo Físico | `docs/modelagem-dados/` |
| 9 | Arquitetura | `docs/arquitetura/` |
| 10 | API | `docs/api/` |
| 11 | Interface Web | `docs/interface-web/` |
| 12 | Testes | `docs/testes/` |

A existência de um diretório reservado para uma etapa futura não significa que a etapa esteja iniciada.

## 9. Gates de desenvolvimento

Os agentes devem respeitar a sequência estabelecida no Plano de Desenvolvimento.

Regras essenciais:

- não iniciar o Mapa do Domínio enquanto os bloqueadores de requisitos que afetem a modelagem não forem resolvidos;
- não criar MER, DER ou modelo físico antes da validação do Mapa do Domínio;
- não definir a arquitetura final antes da compreensão e modelagem do domínio;
- não iniciar implementação da API antes da arquitetura correspondente estar definida;
- não duplicar regras de negócio na interface Web;
- não considerar uma funcionalidade concluída sem validação compatível com a etapa.

Uma pendência que não bloqueie a etapa pode permanecer registrada. Uma pendência que altere o significado do domínio, requisito ou comportamento necessário deve impedir o avanço da parte afetada.

## 10. Responsabilidades recomendadas em ambiente multiagente

Os papéis abaixo representam responsabilidades. O orchestrator pode combinar ou instanciar esses papéis conforme a tarefa.

### Coordenador

Responsável por:

- interpretar o objetivo;
- identificar dependências;
- selecionar agentes;
- dividir tarefas;
- controlar gates;
- integrar resultados;
- evitar trabalhos conflitantes.

### Analista de Requisitos

Responsável por:

- regras de negócio;
- RFs e RNFs;
- critérios de aceitação;
- Casos de Uso;
- consistência e rastreabilidade.

Não deve decidir sozinho ambiguidades de negócio.

### Analista de Domínio

Responsável por:

- linguagem do domínio;
- conceitos;
- responsabilidades;
- distinção entre conceito, atributo, comportamento, configuração e apresentação.

Não deve transformar automaticamente módulos em entidades.

### Modelador de Dados

Responsável pelas Etapas 6, 7 e 8 após aprovação do Mapa do Domínio.

### Arquiteto de Software

Responsável por organizar tecnicamente a solução somente após os gates anteriores.

### Engenheiro Backend

Responsável pela API e regras de aplicação conforme arquitetura e requisitos aprovados.

### Engenheiro Frontend

Responsável pela interface Web, consumindo a API e evitando replicação das regras de negócio.

### QA / Testes

Responsável por transformar critérios verificáveis em cenários e casos de teste e manter evidências de validação.

### Revisor

Deve revisar o trabalho com independência sempre que possível, procurando:

- violação de requisito;
- regressão;
- quebra de rastreabilidade;
- decisão não autorizada;
- duplicação;
- inconsistência documental;
- implementação fora da etapa prevista.

## 11. Protocolo de execução de tarefas

Antes de alterar arquivos, o agente deverá determinar:

- objetivo da tarefa;
- etapa do projeto;
- fontes canônicas aplicáveis;
- arquivos afetados;
- bloqueadores conhecidos;
- critérios para considerar a tarefa concluída.

Durante a execução:

1. realizar a menor alteração suficiente;
2. preservar identificadores existentes;
3. evitar renumeração em massa sem decisão explícita;
4. não misturar alteração semântica com reformatação extensa;
5. atualizar índices e rastreabilidade quando a fonte correspondente mudar;
6. utilizar links relativos entre documentos;
7. evitar duplicação de regras;
8. registrar decisões na fonte apropriada, não apenas na resposta do agente.

Uma decisão tomada em conversa, issue, task ou execução de agente só passa a integrar a fonte de verdade quando for incorporada ao documento canônico correspondente e versionada no repositório.

## 12. Rastreabilidade

A rastreabilidade deverá evoluir progressivamente para a cadeia:

**Regra de Negócio → Requisito → Caso de Uso → Critério de Aceitação → Implementação → Caso de Teste → Resultado**

Nem todos os vínculos existem no estágio atual.

Ao alterar um elemento rastreável, o agente deverá procurar referências dependentes antes de concluir a tarefa.

Exemplos:

- alteração de RN pode afetar RF, UC, CA e testes;
- alteração de RF pode afetar UC, CA e rastreabilidade;
- alteração de UC pode afetar critérios, interface e testes;
- alteração de contrato da API pode afetar interface e testes.

A divergência histórica entre os catálogos de 19 e 25 RFs foi reconciliada na baseline definitiva de 21 RFs. A matriz atual somente deve utilizar `RF-001` a `RF-021`; o histórico da migração permanece na governança.

## 13. Identificadores

As regras de negócio possuem identificadores locais repetidos entre módulos, como `RN-001`.

Para referências globais automatizadas, utilizar:

`docs/requisitos/levantamento/regras-de-negocio-index.csv`

e seu campo:

`rule_uid`

Requisitos, Casos de Uso, Critérios de Aceitação e futuros Casos de Teste devem manter identificadores estáveis após aprovação.

Qualquer renumeração deve ser tratada como alteração de impacto amplo e exigir atualização das referências dependentes.

## 14. Política de alterações em Git

Em operação multiagente:

- `main` deve representar o estado integrado e revisado;
- tarefas paralelas devem utilizar branch ou worktree isolada;
- dois agentes não devem editar simultaneamente o mesmo arquivo sem coordenação;
- commits devem ser pequenos e semanticamente coerentes;
- utilizar Conventional Commits quando aplicável;
- não realizar `force-push`, rebase destrutivo ou reescrita de histórico sem autorização explícita;
- revisar `git diff` antes de concluir;
- não incluir segredos, tokens, credenciais, `.env` ou dados sensíveis no repositório.

Prefixos recomendados:

- `docs:` documentação;
- `feat:` funcionalidade;
- `fix:` correção;
- `refactor:` reorganização sem mudança funcional;
- `test:` testes;
- `chore:` manutenção e configuração;
- `build:` build e dependências;
- `ci:` automação de integração contínua.

O orchestrator não deve efetuar merge de mudanças com bloqueadores críticos não resolvidos apenas para manter o fluxo automático.

## 15. Concorrência e handoff entre agentes

Quando houver execução paralela:

- atribuir ownership claro por tarefa ou conjunto de arquivos;
- preferir worktrees independentes;
- registrar dependências entre tarefas;
- evitar que um agente altere arquivos de outro sem handoff;
- integrar somente após revisão de conflitos semânticos e de Git.

Todo handoff deve informar, no mínimo:

- objetivo;
- fontes consultadas;
- decisões já estabelecidas;
- arquivos modificados;
- validações realizadas;
- pendências;
- impacto esperado na rastreabilidade.

## 16. Política para arquitetura e implementação

Até a Etapa 9, decisões técnicas não devem ser promovidas a arquitetura definitiva sem justificativa.

Direcionamentos já definidos para a V1:

- aplicação Web;
- PHP;
- API REST própria;
- banco de dados relacional.

Estrutura definitiva de código, framework, organização de camadas, persistência, infraestrutura e demais detalhes devem ser decididos na etapa apropriada.

Não criar `src/`, camadas, frameworks ou abstrações apenas para “preparar” uma implementação futura antes do gate correspondente.

## 17. Segurança operacional

Agentes devem:

- utilizar o princípio do menor privilégio;
- evitar comandos destrutivos quando uma alternativa reversível existir;
- não apagar documentação histórica ou evidências de proveniência sem instrução explícita;
- não expor segredos em logs, commits ou documentação;
- validar entradas e efeitos antes de ações externas irreversíveis;
- solicitar confirmação humana para operações destrutivas ou de alto impacto quando não estiverem explicitamente autorizadas.

## 18. Definition of Done para uma tarefa

Uma tarefa somente pode ser declarada concluída quando:

- o objetivo solicitado foi atendido;
- o gate da etapa foi respeitado;
- as fontes corretas foram utilizadas;
- não foi criada decisão de negócio não autorizada;
- os arquivos estão no diretório adequado;
- identificadores e referências permanecem consistentes;
- índices ou rastreabilidade afetados foram atualizados;
- validações aplicáveis foram executadas;
- não existem erros conhecidos ocultados;
- bloqueadores remanescentes foram explicitamente relatados.

Para código, deverão ser acrescentadas as verificações técnicas definidas pela arquitetura e pela estratégia de testes vigente.

## 19. Formato mínimo do relatório de execução

Ao terminar uma tarefa, o agente deve informar de forma concisa:

1. **Resultado**
2. **Arquivos alterados**
3. **Fontes utilizadas**
4. **Validações executadas**
5. **Decisões ou pressupostos**
6. **Pendências/bloqueadores**
7. **Próxima ação recomendada**

Não declarar “concluído” quando uma condição necessária continuar bloqueada.

## 20. Edição Core e proveniência

Este repositório corresponde à edição **Core**, destinada ao trabalho ativo e ao consumo por agentes.

O export bruto do Notion e versões históricas não fazem parte desta árvore Core.

A proveniência disponível no repositório deve ser consultada em:

- `docs/governanca/inventario-fonte.csv`
- `docs/governanca/proveniencia.csv`
- `docs/governanca/relatorio-de-consolidacao.md`

Caso seja necessária auditoria sobre o material bruto original, deve-se utilizar o pacote de preservação completo mantido separadamente.

## 21. Regra final

Quando houver dúvida entre:

**avançar rapidamente**

e

**preservar a coerência do domínio, dos requisitos e da rastreabilidade**,

o orchestrator deverá preservar a coerência e expor a decisão necessária ao responsável humano.
