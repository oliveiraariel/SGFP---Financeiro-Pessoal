# **Plano de Desenvolvimento do Projeto**

## **Sistema de Gestão Financeira Pessoal**

**Versão do documento:** 1.5

**Data da última atualização:** 30/08/2026

**Instituição e graduação:**

**Disciplinas envolvidas:**

**Professores:**

**Equipe no projeto**:

FATEC – Banco de Dados

Laboratório de Banco de Dados 3

Linguagem de Programação 2

Paulo Sérgio

Joaquim Padilha

Mariana Bodario

Alexandra Bittes

Ariel Oliveira

## **1. Objetivo do Plano**

Este documento define a metodologia de desenvolvimento que será adotada pela equipe durante todo o projeto.

Seu objetivo é orientar a ordem das atividades, padronizar o processo de desenvolvimento e garantir que as decisões sejam tomadas no momento adequado.

O projeto será conduzido seguindo os princípios da Engenharia de Software, priorizando o entendimento completo do problema antes da implementação da solução.

## **2. Princípios do Projeto**

Durante todo o desenvolvimento serão adotados os seguintes princípios:

1. Compreender o problema antes de propor soluções.
2. Documentar as decisões antes da implementação.
3. Evitar iniciar a programação sem uma modelagem consistente.
4. Construir o banco de dados somente após compreender completamente o domínio do sistema.
5. Desenvolver cada etapa somente quando a anterior estiver concluída e validada.
6. Priorizar simplicidade, clareza e facilidade de manutenção.
7. Evitar a criação de entidades, mecanismos ou funcionalidades desnecessárias.
8. Reutilizar mecanismos existentes quando forem suficientes para representar uma situação do domínio.
9. Não transformar automaticamente módulos ou funcionalidades em entidades do domínio.
10. Separar regras de negócio, funcionalidades, dados, entidades, interface, configuração, arquitetura e implementação.
11. Preservar o histórico financeiro quando as regras do sistema exigirem alterações em períodos futuros.
12. Preservar o estado anterior do sistema durante processos de restauração de dados.
13. Considerar a possibilidade de evolução futura sem introduzir complexidade desnecessária na Versão 1.

## **3. Metodologia de Desenvolvimento**

O desenvolvimento seguirá uma sequência de etapas, na qual cada etapa utilizará como base os resultados das etapas anteriores.

### **3.1 Documentos do Projeto**

Além das etapas de desenvolvimento, o projeto manterá uma estrutura documental organizada para registrar as principais informações produzidas durante o processo.

Os documentos principais do projeto serão:

1. **Documento de Visão**

    Registra o propósito do sistema, problema, público alvo, objetivos, escopo e visão geral do produto.

2. **Levantamento de Requisitos**

    Registra as necessidades identificadas, regras de negócio, decisões, funcionalidades e demais informações levantadas durante a análise do problema.

3. **SRS – Especificação de Requisitos de Software**

    Consolida os requisitos de software do sistema, incluindo requisitos funcionais, requisitos não funcionais, restrições aplicáveis e referências às regras de negócio.

4. **Casos de Uso**

    Registra as interações entre os atores e o sistema para alcançar os objetivos definidos.

5. **Modelagem do Domínio e Dados**

    Registra a evolução da compreensão do domínio até a representação conceitual, lógica e física dos dados.

6. **Arquitetura de Software**

    Registra a organização técnica da aplicação, seus componentes, camadas, responsabilidades e principais decisões arquiteturais.

7. **Testes**

    Registra a estratégia de testes, casos de teste, cenários, resultados e evidências de validação do sistema.

O **Plano de Desenvolvimento** e o **Documento de Visão** são documentos de caráter preliminar e de orientação do projeto. Os sete documentos acima representam os principais documentos produzidos e evoluídos ao longo do desenvolvimento.

### **3.2 Relação entre Etapas e Documentos**

Os documentos não serão necessariamente produzidos integralmente em uma única etapa.

Cada etapa poderá produzir, alimentar ou revisar documentos existentes.

A relação principal será:

| Etapa | Atividade | Documento ou artefato principal |
| --- | --- | --- |
| 1 | Documento de Visão | Documento de Visão |
| 2 | Levantamento de Requisitos | Levantamento de Requisitos |
| 3 | Especificação de Requisitos | SRS |
| 4 | Casos de Uso | Casos de Uso |
| 5 | Mapa do Domínio | Modelagem do Domínio e Dados |
| 6 | Modelagem Conceitual | Modelagem do Domínio e Dados |
| 7 | Modelo Entidade Relacionamento | Modelagem do Domínio e Dados |
| 8 | Modelo Físico | Modelagem do Domínio e Dados |
| 9 | Arquitetura da Aplicação | Arquitetura de Software |
| 10 | Desenvolvimento da API | Arquitetura de Software e artefatos da API |
| 11 | Desenvolvimento da Interface Web | Arquitetura de Software e artefatos da interface |
| 12 | Testes | Documento de Testes |

Os documentos poderão receber atualizações quando decisões posteriores produzirem impactos sobre informações já documentadas.

### **3.3 Rastreabilidade entre as Etapas**

Os artefatos produzidos durante o desenvolvimento deverão manter, sempre que aplicável, relação com as informações que lhes deram origem.

A rastreabilidade será construída progressivamente, considerando principalmente:

**Regra de Negócio → Requisito → Caso de Uso → Implementação → Teste**

A Matriz de Rastreabilidade será desenvolvida progressivamente durante o projeto.

Inicialmente poderá relacionar os requisitos funcionais aos respectivos casos de uso.

Posteriormente poderá incorporar as relações entre requisitos, regras de negócio, casos de uso e testes.

O objetivo é permitir identificar a origem de um requisito, sua representação no sistema e a forma como seu atendimento será verificado.

## **4. Etapas do Desenvolvimento**

### **Etapa 1: Documento de Visão**

**Objetivo**

Definir claramente o propósito do sistema, seu público alvo, objetivos, escopo inicial e visão geral.

O Documento de Visão poderá receber revisões caso decisões posteriores revelem a necessidade de ajustes em seu conteúdo.

### **Etapa 2: Levantamento de Requisitos**

**Objetivo**

Descobrir como o sistema deverá funcionar.

Nesta etapa não serão discutidos banco de dados, linguagens de programação, APIs ou implementação.

As discussões serão focadas nas necessidades do usuário, regras de negócio, comportamentos esperados e funcionalidades necessárias ao sistema.

O levantamento será realizado por módulos e subetapas de negócio.

Cada módulo ou subetapa deverá ser analisado e validado antes de ser considerado concluído.

Cada módulo ou subetapa poderá conter:

1. Objetivo.
2. Regras de negócio.
3. Decisões tomadas.
4. Funcionalidades da Versão 1.
5. Funcionalidades previstas para versões futuras.
6. Questões pendentes ou destinadas a etapas posteriores, quando necessário.

### **Etapa 3: Especificação de Requisitos de Software (SRS)**

**Objetivo**

Transformar o levantamento realizado em uma especificação formal dos requisitos de software.

O SRS conterá, conforme aplicável:

1. Requisitos funcionais.
2. Requisitos não funcionais.
3. Escopo do software.
4. Restrições aplicáveis.
5. Interfaces ou integrações relevantes.
6. Referências às regras de negócio consolidadas na Etapa 2.

As regras de negócio não serão reproduzidas integralmente nesta etapa, evitando duplicação de conteúdo.

Os requisitos funcionais serão identificados individualmente pelo padrão:

**RF 001, RF 002, RF 003...**

Os requisitos não funcionais serão identificados individualmente pelo padrão:

**RNF 001, RNF 002, RNF 003...**

A rastreabilidade será construída progressivamente após a definição dos requisitos e dos casos de uso, podendo posteriormente incorporar os testes.

### **Etapa 4: Casos de Uso**

**Objetivo**

Documentar as interações entre os atores e o sistema, considerando os requisitos e regras de negócio previamente definidos.

Os casos de uso deverão representar os objetivos que os usuários pretendem alcançar por meio do sistema e as principais interações necessárias para alcançá-los.

### **Etapa 5: Mapa do Domínio**

**Objetivo**

Organizar e validar os principais conceitos do domínio do problema, distinguindo aqueles que representam conceitos relevantes do domínio daqueles que representam apenas atributos, regras, comportamentos, configurações ou formas de apresentação.

Essa etapa servirá como ponte entre a especificação dos requisitos e a modelagem conceitual.

Ainda não serão desenhadas entidades nem relacionamentos.

Será construída apenas uma representação organizada dos conceitos que compõem o sistema.

### **Etapa 6: Modelagem Conceitual (MER)**

**Objetivo**

Identificar entidades, atributos e relacionamentos com base nas regras de negócio levantadas anteriormente e nos conceitos validados no Mapa do Domínio.

Nesta etapa será utilizada uma ferramenta de modelagem, como o brModelo.

### **Etapa 7: Modelo Entidade Relacionamento (DER)**

**Objetivo**

Refinar a modelagem conceitual, definindo cardinalidades e relacionamentos completos.

### **Etapa 8: Modelo Físico**

**Objetivo**

Transformar o DER em um banco de dados relacional.

Nesta etapa serão definidos:

1. Tabelas.
2. Chaves primárias.
3. Chaves estrangeiras.
4. Restrições.
5. Índices.
6. Demais elementos necessários à implementação física do banco de dados.

### **Etapa 9: Arquitetura da Aplicação**

**Objetivo**

Definir a organização interna do projeto.

A primeira versão será desenvolvida como uma aplicação Web utilizando PHP sobre WordPress e uma arquitetura baseada em API REST.

O WordPress será utilizado como plataforma da aplicação, fornecendo infraestrutura Web, gerenciamento de usuários e autenticação, sessões e suporte à API REST. O backend específico do SGFP será implementado em plugin próprio.

A arquitetura deverá permitir que futuras aplicações, como um aplicativo mobile, utilizem a mesma API e as regras de negócio implementadas no sistema.

Serão estabelecidos:

1. Estrutura das pastas.
2. Camadas da aplicação.
3. Arquitetura da API.
4. Responsabilidades das camadas.
5. Padrão Repository.
6. Services.
7. Controllers.
8. Demais componentes necessários à arquitetura.

A definição da arquitetura deverá ocorrer somente após a compreensão e modelagem do domínio.

### **Etapa 10: Desenvolvimento da API**

**Objetivo**

Implementar as regras de negócio da aplicação e disponibilizar os recursos necessários por meio da API.

A implementação deverá seguir as decisões estabelecidas nas etapas anteriores.

### **Etapa 11: Desenvolvimento da Interface Web**

**Objetivo**

Construir a interface do usuário utilizando a API desenvolvida anteriormente.

A interface deverá utilizar os recursos disponibilizados pela API, evitando duplicação das regras de negócio entre a interface e o backend.

### **Etapa 12: Testes**

**Objetivo**

Validar o funcionamento completo do sistema e verificar o atendimento aos requisitos definidos.

Serão realizados testes relacionados a:

1. Regras de negócio.
2. Requisitos funcionais.
3. Banco de dados.
4. API.
5. Interface.
6. Integração entre os componentes.

Os resultados dos testes serão utilizados para alimentar a rastreabilidade dos requisitos e identificar eventuais necessidades de correção.

## **5. Organização do Levantamento de Requisitos**

O levantamento de requisitos foi realizado por módulos e subetapas de negócio.

A organização dos módulos não determina automaticamente as entidades que serão utilizadas posteriormente na modelagem.

Um módulo ou subetapa existe para organizar o levantamento e a documentação das regras de negócio e funcionalidades do sistema.

A conclusão de um módulo ocorreu somente após as dúvidas relevantes terem sido analisadas e as decisões necessárias terem sido tomadas.

**A organização consolidada da Etapa 2 é:**

### **1. Usuários**

Objetivo relacionado a cadastro, autenticação, isolamento dos dados e recuperação de senha.

### **2. Contas**

Objetivo relacionado ao gerenciamento das contas financeiras, movimentações, saldos, transferências e patrimônio.

### **3. Compromissos**

Objetivo relacionado ao cadastro, manutenção, edição, exclusão e comportamento dos compromissos financeiros.

O conceito de compromisso financeiro constitui o mecanismo central utilizado para representar obrigações financeiras e previsões de entrada.

### **4. Categorias**

Objetivo relacionado à organização dos compromissos financeiros por categorias.

A categoria é obrigatória para os compromissos financeiros.

Caso nenhuma categoria disponível seja adequada, o usuário poderá criar uma nova categoria por meio do botão de adição disponível junto às categorias.

As categorias não representam movimentações financeiras independentes.

### **5. Recorrências**

Objetivo relacionado ao comportamento de compromissos financeiros que se repetem mensalmente.

Na Versão 1, a recorrência será exclusivamente mensal, podendo possuir duração determinada ou não possuir término definido.

### **6. Lançamentos Financeiros**

Objetivo relacionado às movimentações financeiras efetivamente realizadas e ao mecanismo de efetivação dos compromissos financeiros.

O lançamento financeiro representa a movimentação efetivamente realizada, enquanto o compromisso representa a obrigação ou previsão anterior à efetivação.

### **7. Transferências**

Objetivo relacionado à movimentação de valores entre contas pertencentes ao usuário.

As transferências utilizarão o mesmo mecanismo de compromisso e efetivação utilizado nas demais movimentações financeiras.

### **8. Casos Específicos de Compromissos Financeiros**

Objetivo relacionado ao tratamento de situações específicas que podem ocorrer dentro dos compromissos financeiros.

Na Versão 1, Cartão de Crédito e Parcelamentos serão tratados como formas específicas de utilização dos compromissos financeiros e das regras de recorrência já estabelecidas.

Não será assumido que esses casos correspondam automaticamente a entidades independentes do domínio.

### **9. Dashboard**

Objetivo relacionado à apresentação consolidada da situação financeira do período selecionado.

O Dashboard representa uma visão dos dados existentes no sistema e não possui dados financeiros independentes.

O Dashboard não será tratado como uma entidade do domínio.

### **10. Configurações**

Objetivo relacionado às preferências, proteção e preservação dos dados disponibilizadas ao usuário.

Na Versão 1, permanecem como definições consolidadas o tema da aplicação, o backup e a restauração de dados. A proteção opcional por PIN foi retirada do escopo da Versão 1 e permanece registrada como funcionalidade prevista para versão futura.

### **11. Relatórios**

Objetivo relacionado ao levantamento das necessidades futuras de relatórios financeiros.

Na Versão 1, não serão definidos ou implementados relatórios financeiros específicos.

O Dashboard será a principal visão consolidada da situação financeira na Versão 1.

### **12. Arquivos**

Subetapa destinada à análise da utilização, armazenamento ou associação de arquivos no sistema.

Na Versão 1, a anexação de arquivos de imagem aos compromissos não fará parte do escopo, permanecendo como funcionalidade prevista para versão futura.

### **13. Segurança**

Subetapa destinada à análise das regras e necessidades de segurança do sistema.

Para a Versão 1, foram consolidadas, entre outras, as seguintes diretrizes:

1. Autenticação do usuário por e-mail e senha.
2. Armazenamento seguro das senhas por meio dos mecanismos de hash fornecidos pela plataforma de autenticação adotada.
3. Alteração de senha diretamente pela aplicação utilizando os mecanismos disponibilizados pelo WordPress.
4. Recuperação de senha por meio do endereço de e-mail cadastrado, utilizando o mecanismo de recuperação disponibilizado pelo WordPress.
5. Isolamento dos dados financeiros pertencentes a usuários diferentes.
6. Proteção das operações e dos recursos da API contra acesso não autorizado.

A proteção opcional por PIN não fará parte da Versão 1. Para uma versão futura, o PIN será tratado como mecanismo de bloqueio rápido do SGFP durante uma sessão já autenticada, sem substituir a autenticação principal por e-mail e senha.

### **14. Integrações**

Subetapa destinada à análise de integrações externas e funcionalidades relacionadas.

Na Versão 1, não haverá integração com instituições bancárias ou outros serviços externos.

A API desenvolvida pelo próprio sistema não constitui uma integração externa e será utilizada como parte da arquitetura da aplicação, permitindo futuramente o desenvolvimento de outras interfaces, como uma aplicação mobile.

## **6. Critérios para Avançar de Etapa**

A equipe somente deverá iniciar uma nova etapa quando a etapa anterior estiver suficientemente definida e validada para permitir o avanço.

Exemplos:

1. Não iniciar a modelagem antes da conclusão do levantamento de requisitos.
2. Não iniciar o banco de dados antes da conclusão do MER e do DER.
3. Não iniciar a implementação da API antes da definição da arquitetura.
4. Não iniciar o desenvolvimento da interface antes da disponibilidade dos recursos necessários da API.

A existência de uma ideia, hipótese ou funcionalidade planejada para uma etapa futura não deverá antecipar a execução dessa etapa.

Quando uma questão puder ser registrada para análise posterior sem impedir a continuidade da etapa atual, ela deverá ser documentada como questão pendente ou funcionalidade futura.

## **7. Responsabilidade da Equipe**

Durante este projeto, todas as decisões deverão priorizar:

1. Simplicidade.
2. Clareza.
3. Organização.
4. Facilidade de manutenção.
5. Coerência com o domínio.
6. Possibilidade de evolução.
7. Aprendizado da equipe.

As decisões deverão ser tomadas de maneira incremental, evitando antecipar soluções técnicas antes que o problema esteja suficientemente compreendido.

O objetivo não é apenas entregar um software funcional, mas compreender cada decisão tomada ao longo do desenvolvimento, formando uma base sólida em Engenharia de Software, análise de requisitos, modelagem de domínio, modelagem de dados, banco de dados, arquitetura de sistemas e desenvolvimento de APIs.

## **8. Princípio de Evolução do Sistema**

O sistema deverá ser desenvolvido considerando a possibilidade de evolução futura, porém sem introduzir complexidade antecipada.

Uma funcionalidade somente deverá fazer parte da Versão 1 quando houver necessidade identificada e justificativa suficiente para sua inclusão.

Funcionalidades que não sejam necessárias para o objetivo fundamental da aplicação na Versão 1 não deverão fazer parte do desenvolvimento atual.

Essas funcionalidades poderão ser registradas como:

**Fora do escopo da Versão 1**

ou:

**Funcionalidade prevista para versão futura.**

Entre as funcionalidades previstas para versões futuras estão:

1. Proteção opcional por PIN para bloqueio rápido do SGFP durante uma sessão já autenticada.
2. Bloqueio automático da aplicação após período de inatividade.
3. Login com Google.
4. Sincronização em nuvem.
5. Aplicativo mobile.
6. Dashboards avançados.
7. Relatórios financeiros adicionais.
8. Integrações externas.
9. Backup automático.
10. Anexação de arquivos de imagem aos compromissos.
11. Outras funcionalidades que venham a ser identificadas como necessárias após a utilização da Versão 1.

As funcionalidades previstas para versões futuras não constituem etapas deste desenvolvimento e não deverão antecipar decisões ou introduzir complexidade na Versão 1.

Quando uma funcionalidade já possuir identificadores de rastreabilidade antes de ser adiada, seus identificadores deverão ser preservados. Assim, RF-019 e UC-018 relacionados à proteção por PIN permanecerão registrados como itens de versão futura, sem renumeração dos requisitos e casos de uso posteriores.

Não deverão ser criados requisitos, entidades, estruturas ou mecanismos apenas para preparar funcionalidades futuras que ainda não façam parte do escopo atual.

## **9. Princípio de Separação das Decisões**

Durante o desenvolvimento, deverão ser diferenciados os seguintes conceitos:

### **Regra de Negócio**

Determina o comportamento que o sistema deve obrigatoriamente respeitar.

### **Funcionalidade**

Determina aquilo que o sistema deverá permitir que o usuário faça.

### **Dado**

Representa uma informação necessária ao funcionamento do sistema.

### **Entidade**

Representa um conceito relevante do domínio que possua identidade e responsabilidade própria.

### **Interface / UX**

Determina como informações e funcionalidades serão apresentadas ao usuário.

### **Configuração**

Representa uma preferência ou comportamento configurável pelo usuário.

### **Arquitetura**

Determina como o software será organizado internamente.

### **Implementação**

Determina como uma decisão será efetivamente programada.

Essas categorias não deverão ser confundidas durante o levantamento ou a modelagem.

## **10. Regra para Modelagem**

Os módulos e subetapas definidos no levantamento de requisitos não determinarão automaticamente as entidades do banco de dados.

Quando a etapa de modelagem for iniciada, cada conceito deverá ser analisado considerando:

1. Identidade.
2. Atributos.
3. Responsabilidades.
4. Relacionamentos.
5. Cardinalidade.
6. Dependências.
7. Regras de integridade.
8. Dados derivados.
9. Necessidade real de persistência.

Conceitos que representem apenas categorias, comportamentos, configurações, visualizações ou formas de utilização de outras entidades não deverão ser transformados automaticamente em entidades independentes.

Cartão de Crédito, Parcelamentos e Dashboard já possuem decisões explícitas nesse sentido no levantamento consolidado.

## **11. Regra para Arquitetura**

A arquitetura deverá ser definida após a compreensão e modelagem do domínio.

Primeiro deverá ser identificado:

**O que o domínio precisa fazer?**

Posteriormente deverá ser definido:

**Como o software será organizado para cumprir essas responsabilidades?**

A arquitetura deverá servir ao domínio e às necessidades do sistema, evitando a introdução de padrões, abstrações ou tecnologias que não possuam justificativa para o escopo do projeto.

## **12. Regra para Implementação**

Quando a implementação for iniciada, o desenvolvimento deverá seguir, sempre que possível, a sequência:

**Conceito → Responsabilidade → Decisão → Estrutura → Implementação → Teste**

O objetivo é compreender a responsabilidade de cada componente antes de sua implementação.

A implementação não deverá antecipar decisões que pertençam às etapas anteriores.

## **13. Objetivo Acadêmico**

O projeto possui, além do objetivo de construir uma aplicação funcional, um objetivo acadêmico.

O desenvolvimento deverá permitir que a equipe compreenda progressivamente:

1. Análise de requisitos.
2. Regras de negócio.
3. Modelagem de domínio.
4. Modelagem conceitual.
5. Modelagem de dados.
6. Arquitetura de software.
7. Desenvolvimento de APIs.
8. Desenvolvimento de interfaces.
9. Testes.
10. Evolução de software.

Todo o processo deverá ser documentado de maneira que as decisões possam ser compreendidas, justificadas e utilizadas como referência para futuros desenvolvimentos.

## **14. Histórico de Atualização**

### **Versão 1.4: 17/08/2026**

Atualização do Plano de Desenvolvimento para formalizar a estrutura documental do projeto e sua relação com as etapas de desenvolvimento.

Principais atualizações:

1. Inclusão da estrutura dos principais documentos do projeto.
2. Definição dos sete documentos principais produzidos ao longo do desenvolvimento.
3. Distinção entre documentos preliminares, etapas de desenvolvimento e artefatos técnicos.
4. Definição da relação entre cada etapa e os documentos que ela produz ou contribui para produzir.
5. Formalização da rastreabilidade entre regras de negócio, requisitos, casos de uso, implementação e testes.
6. Ajuste da definição da Matriz de Rastreabilidade para que seja construída progressivamente ao longo do desenvolvimento.
7. Manutenção do Levantamento de Requisitos como documento de origem das regras de negócio, decisões e informações levantadas durante a análise.
8. Manutenção do SRS como documento de especificação dos requisitos de software, evitando a duplicação integral das regras de negócio.
9. Manutenção da separação entre requisitos, regras de negócio, casos de uso, domínio, dados, arquitetura e implementação.
10. Manutenção das etapas de modelagem do domínio, modelagem conceitual, DER e modelo físico.
11. Manutenção das etapas de arquitetura, desenvolvimento da API, desenvolvimento da interface e testes.
12. Manutenção dos princípios de simplicidade, evolução controlada e não antecipação de complexidade.
13. Atualização da estrutura do documento para refletir a organização atualmente adotada no projeto.
