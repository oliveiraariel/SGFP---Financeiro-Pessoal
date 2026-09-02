# ORCHESTRATOR.md — Contrato Operacional para Agentes

**Projeto:** SGFP — Sistema de Gestão Financeira Pessoal  
**Versão deste documento:** 2.3
**Status:** Governança operacional vigente  
**Última atualização:** 02/09/2026

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

O catálogo funcional preserva 21 identificadores de `RF-001` a `RF-021`.

A baseline ativa da V1 possui 20 requisitos funcionais. `RF-019 — Gerenciar proteção por PIN` foi adiado para versão futura, sem renumeração dos requisitos posteriores. `UC-018` e `CA-019.1` a `CA-019.5` permanecem igualmente preservados como artefatos futuros.

A **Etapa 5 — Mapa do Domínio** foi concluída e validada no artefato:

- `docs/dominio/01-mapa-de-dominio.md`

O Mapa do Domínio organiza os conceitos, responsabilidades e relações conceituais do SGFP sem antecipar cardinalidades, entidades definitivas, estruturas lógicas, estruturas físicas, arquitetura ou implementação.

A etapa corrente é:

**Etapa 6 — Modelagem Conceitual (MER)**

A Etapa 6 deverá utilizar a baseline documental atualizada e não deverá modelar conceitos, entidades, atributos ou estruturas destinados exclusivamente ao PIN futuro.

As pendências e decisões de governança relevantes ao estado atual permanecem registradas no `project-manifest.yaml` e nos documentos de continuidade. Em especial:

- `ISSUE-001` a `ISSUE-007` estão resolvidas conforme a baseline vigente;
- `ISSUE-008` permanece aberta como pendência de rastreabilidade, não bloqueia a Etapa 6 e deverá ser concluída antes do fechamento formal da rastreabilidade de testes na Etapa 12.

Até a validação do MER:

- não iniciar DER;
- não iniciar Modelo Físico;
- não promover decisões de persistência;
- não definir arquitetura final;
- não iniciar implementação.

### Restrição tecnológica vigente

A Versão 1 será uma aplicação Web em PHP sobre WordPress. O WordPress fornecerá identidade, autenticação, sessão e infraestrutura REST; o backend específico do SGFP será implementado em plugin próprio. MySQL ou MariaDB será utilizado como banco relacional compatível com a plataforma.

Essa decisão tecnológica não autoriza antecipar detalhes de arquitetura que pertencem à Etapa 9.

## 4. Ordem mínima de leitura

Antes de executar qualquer tarefa, o agente coordenador deverá ler:

1. `project-manifest.yaml`
2. `docs/governanca/continuidade-de-contexto.md`
3. `docs/governanca/relatorio-de-consolidacao.md`
4. `docs/projeto/plano-de-desenvolvimento.md`
5. `docs/projeto/documento-de-visao.md`

Depois, deverá carregar somente os documentos necessários à tarefa:

6. `docs/requisitos/levantamento/README.md` e módulos de negócio envolvidos;
7. `docs/requisitos/srs/README.md` e seções do SRS envolvidas;
8. `docs/casos-de-uso/README.md` e Casos de Uso relacionados;
9. `docs/dominio/01-mapa-de-dominio.md` para qualquer tarefa da Etapa 6 em diante;
10. artefatos das etapas posteriores somente quando a etapa correspondente já tiver sido iniciada e forem relevantes;
11. `docs/projeto/roteiro-tecnico-de-implementacao.md` quando a tarefa envolver preparação técnica, Arquitetura, API, Interface, testes durante a implementação, ferramentas de desenvolvimento ou qualidade de código.

O roteiro técnico é um **documento auxiliar**. Ele detalha práticas, ferramentas e sequência didática, mas permanece subordinado ao Plano de Desenvolvimento, às fontes canônicas, aos gates oficiais e às decisões de arquitetura aprovadas.

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
| Orientação técnica e didática de implementação | `docs/projeto/roteiro-tecnico-de-implementacao.md` — apoio subordinado, sem autoridade para alterar fontes canônicas |
| Proveniência, inventário e consolidação | `docs/governanca/` |

`project-manifest.yaml` e `ORCHESTRATOR.md` são fontes de **governança e roteamento**, não fontes de regras de negócio.

`docs/dominio/01-mapa-de-dominio.md` é a fonte consolidada dos **conceitos do domínio aprovados na Etapa 5**. Ele orienta a Modelagem Conceitual, mas não substitui regras de negócio, requisitos ou Casos de Uso e não autoriza antecipar decisões próprias de MER, DER, Modelo Físico ou Arquitetura.

O relatório de consolidação registra problemas e decisões de reorganização, mas não deve ser utilizado para substituir silenciosamente conteúdo normativo.

`docs/projeto/roteiro-tecnico-de-implementacao.md` não é fonte normativa de negócio, requisito, modelagem ou arquitetura aprovada. Quando houver divergência entre o roteiro e uma fonte canônica ou decisão de etapa já validada, prevalece a fonte oficial do projeto.

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

## 7. Estado das pendências documentais

A baseline funcional vigente registra:

- `ISSUE-001`: resolvida — catálogo reconciliado de 21 identificadores funcionais (`RF-001` a `RF-021`), com 20 RFs ativos na V1 e `RF-019` preservado como futuro;
- `ISSUE-002`: resolvida — cadastro, autenticação e senha formalizados como `RF-001`, `RF-002` e `RF-003`;
- `ISSUE-003`: resolvida — exclusão de contas fora do escopo da V1;
- `ISSUE-004`: resolvida — valor inicial da Conta Principal representado por Entrada; Conta Secundária recebe valor por Transferência; saldo não é armazenado;
- `ISSUE-005`: resolvida — proteção por PIN retirada do escopo ativo da V1, com identificadores preservados para versão futura;
- `ISSUE-006`: resolvida — PHP sobre WordPress, plugin próprio, infraestrutura REST e MySQL/MariaDB consolidados como restrição tecnológica da V1;
- `ISSUE-007`: resolvida — preservação do estado anterior definida como cópia de segurança automática pré-restauração, permanecendo o mecanismo técnico para a etapa apropriada;
- `ISSUE-008`: aberta — a matriz direta Regra de Negócio → Requisito ainda deverá ser materializada. Não bloqueia a Etapa 6, mas bloqueia o fechamento final da rastreabilidade da Etapa 12.

Os status, resoluções e evidências permanecem registrados em `project-manifest.yaml`, `docs/governanca/continuidade-de-contexto.md` e `docs/governanca/relatorio-de-consolidacao.md`.

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

- a Etapa 5 somente é considerada concluída com `docs/dominio/01-mapa-de-dominio.md` validado;
- a Modelagem Conceitual (MER) pode iniciar somente após a validação do Mapa do Domínio;
- não criar DER antes da validação do MER;
- não criar Modelo Físico antes da validação do DER;
- a execução controlada do Modelo Físico em MySQL/MariaDB para validar sintaxe e integridade pode ocorrer na Etapa 8, sem caracterizar início da implementação da aplicação;
- não definir a arquitetura final antes da conclusão e validação da modelagem de dados aplicável;
- não iniciar implementação da API antes da arquitetura correspondente estar definida;
- preparação de ambiente, estudo de ferramentas ou elaboração de roteiro técnico não altera por si só o estado oficial da etapa;
- não duplicar regras de negócio na interface Web;
- testes e verificações técnicas podem acompanhar a implementação quando úteis, mas a Etapa 12 permanece responsável pela consolidação formal da estratégia, evidências, rastreabilidade e resultados;
- não considerar uma funcionalidade ou etapa concluída sem validação compatível com seu gate.

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
- critérios para considerar a tarefa concluída;
- quando houver tecnologia ou ferramenta envolvida, se ela representa restrição obrigatória, decisão de arquitetura ou ferramenta de apoio substituível.

Durante a execução:

1. realizar a menor alteração suficiente;
2. preservar identificadores existentes;
3. evitar renumeração em massa sem decisão explícita;
4. não misturar alteração semântica com reformatação extensa;
5. atualizar índices e rastreabilidade quando a fonte correspondente mudar;
6. utilizar links relativos entre documentos;
7. evitar duplicação de regras;
8. registrar decisões na fonte apropriada, não apenas na resposta do agente;
9. quando introduzir ferramenta, dependência, padrão ou abstração nova, explicitar o problema que ela resolve e verificar se sua adoção é realmente necessária naquele momento;
10. em tarefas de implementação com objetivo didático, preferir a progressão **Problema → Conceito → Decisão → Implementação → Teste → Validação → Versionamento**, conforme detalhado no roteiro técnico.

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

A divergência histórica entre os catálogos de 19 e 25 RFs foi reconciliada em um catálogo preservado de 21 identificadores. A V1 atual possui 20 RFs ativos e mantém RF-019 como requisito futuro; o histórico da migração permanece na governança.

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

## 16. Política para arquitetura, implementação e ferramentas

Até a Etapa 9, decisões técnicas não devem ser promovidas a arquitetura definitiva sem justificativa e sem o gate correspondente.

Direcionamentos já definidos para a V1:

- aplicação Web;
- PHP;
- WordPress como plataforma Web;
- backend específico do SGFP em plugin próprio;
- infraestrutura WordPress REST API com endpoints próprios do SGFP;
- identidade, autenticação e sessão fornecidas pelo WordPress;
- banco de dados relacional MySQL ou MariaDB.

O Plano de Desenvolvimento já prevê que a Etapa 9 defina estrutura de pastas, responsabilidades, arquitetura da API e a utilização de Controllers, Services e Repositories. A forma concreta dessas estruturas deverá ser decidida na própria Arquitetura, sem criar diretórios ou classes vazias antecipadamente.

Representações de domínio no código deverão existir quando houver responsabilidade real de negócio. Não assumir equivalência automática entre **tabela**, **entidade conceitual** e **classe de domínio**, nem exigir uma camada ou diretório `Domain/` apenas por convenção.

Ferramentas de desenvolvimento deverão ser classificadas como apoio e não como restrições do produto, salvo exigência formal. Em particular:

- sistema operacional, editor e extensão de IDE não constituem restrições do SGFP por si só;
- Composer é ferramenta preferencial para dependências e autoload PHP quando sua adoção for pertinente;
- clientes HTTP como Bruno, Postman, Insomnia ou equivalentes podem ser usados para testar a API independentemente da interface;
- PHPUnit é ferramenta preferencial para testes automatizados PHP quando houver componentes reais a testar;
- ferramentas como PHPStan, PHP-CS-Fixer e PHP_CodeSniffer podem ser adotadas progressivamente para análise estática, formatação ou padronização, conforme necessidade.

Durante a Etapa 10, preferir implementação incremental de funcionalidades de ponta a ponta quando isso favorecer compreensão, validação e redução de risco. Essa estratégia não substitui os gates oficiais nem impede outra sequência quando houver dependência técnica justificada.

Testes unitários, de integração, banco e API podem acompanhar as etapas de implementação. A Etapa 12 permanece responsável pela consolidação formal da estratégia, casos, evidências, rastreabilidade e resultados.

`docs/projeto/roteiro-tecnico-de-implementacao.md` detalha essas práticas de forma didática e operacional. Ele deve ser consultado como apoio e nunca utilizado para sobrepor regras de negócio, requisitos, modelagem, arquitetura aprovada ou gates do Plano de Desenvolvimento.

Não criar `src/`, camadas, frameworks, dependências ou abstrações apenas para “preparar” uma implementação futura antes do gate correspondente.

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

## 21. Histórico de atualização

### Versão 2.3 — 02/09/2026

- integração do novo roteiro técnico de implementação como documento auxiliar subordinado às fontes canônicas;
- atualização do estado das `ISSUE-001` a `ISSUE-008` conforme a governança vigente;
- distinção explícita entre restrições do produto, decisões arquiteturais e ferramentas de desenvolvimento substituíveis;
- consolidação de WordPress, plugin próprio, WordPress REST API e MySQL/MariaDB como direcionamentos tecnológicos oficiais da V1;
- registro de Composer, cliente HTTP, PHPUnit e ferramentas de qualidade como recursos preferenciais ou opcionais introduzidos conforme necessidade;
- reforço da implementação incremental, dos testes contínuos e da consolidação formal de testes na Etapa 12;
- preservação integral dos gates: o projeto continua na Etapa 6 e a implementação não foi iniciada.

## 22. Regra final

Quando houver dúvida entre:

**avançar rapidamente**

e

**preservar a coerência do domínio, dos requisitos e da rastreabilidade**,

o orchestrator deverá preservar a coerência e expor a decisão necessária ao responsável humano.
