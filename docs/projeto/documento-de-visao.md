# **Documento de Visão**

## Sistema de Gestão Financeira Pessoal

**Versão do documento:** 1.6

**Data da última atualização:** 05/09/2026

### 1. Apresentação

O projeto consiste no desenvolvimento de um Sistema de Gestão Financeira Pessoal com o objetivo de auxiliar usuários no planejamento, organização e acompanhamento de suas finanças.

O sistema não possui integração com instituições bancárias ou outros serviços externos na Versão 1. Todas as movimentações financeiras serão registradas manualmente pelo usuário, permitindo controle sobre compromissos financeiros, lançamentos financeiros, transferências entre contas e evolução do patrimônio financeiro.

O foco principal do projeto é oferecer uma ferramenta simples de utilizar, porém suficientemente organizada para representar a realidade financeira do usuário.

O sistema será desenvolvido seguindo os princípios da Engenharia de Software, priorizando o entendimento completo do domínio do problema antes da implementação do banco de dados e do código-fonte.

### 2. Objetivo Geral

Desenvolver uma aplicação capaz de auxiliar pessoas na organização de suas finanças pessoais, permitindo controlar compromissos financeiros, lançamentos financeiros, contas financeiras, transferências, recorrências e patrimônio, oferecendo uma visão clara da situação financeira atual e do planejamento dos meses seguintes.

### 3. Público-Alvo

O sistema destina-se a pessoas que desejam controlar suas finanças pessoais de forma organizada.

A aplicação Web poderá ser utilizada por múltiplos usuários cadastrados.

Cada usuário possuirá seus próprios dados financeiros, configurações e categorias, mantidos de forma isolada dos dados dos demais usuários.

Não haverá compartilhamento de dados financeiros entre usuários na Versão 1.

### 4. Escopo da Primeira Versão

A primeira versão do sistema contemplará as funcionalidades essenciais para o gerenciamento financeiro pessoal.

Serão desenvolvidos:

- cadastro de usuário;
- autenticação por e-mail e senha;
- gerenciamento de contas financeiras;
- gerenciamento de compromissos financeiros;
- controle de receitas;
- controle de despesas;
- categorias personalizáveis;
- lançamentos recorrentes;
- efetivação de compromissos por meio de lançamentos financeiros;
- transferências entre contas;
- controle básico de cartão de crédito;
- controle de parcelamentos;
- dashboard inicial;
- recuperação de senha por e-mail;
- criação manual de cópia de segurança;
- restauração de cópia de segurança;
- preservação do estado imediatamente anterior por meio de cópia de segurança automática pré-restauração, criada antes da substituição dos dados atuais;
- API REST;
- interface Web.

### 5. Funcionalidades Previstas para Versões Futuras

As funcionalidades abaixo não fazem parte da primeira versão, porém já fazem parte da visão de evolução do projeto.

Entre elas destacam-se:

- proteção opcional por PIN para bloqueio rápido do SGFP durante uma sessão já autenticada;
- bloqueio automático da aplicação após período de inatividade;
- autenticação utilizando conta Google;
- sincronização dos dados em nuvem;
- aplicativo para dispositivos móveis;
- dashboards avançados;
- relatórios gerenciais mais completos;
- integrações com serviços externos;
- backup automático periódico ou contínuo;
- anexação de arquivos de imagem aos compromissos;
- outras funcionalidades que venham a ser identificadas como necessárias após a utilização da Versão 1.

### 6. Plataforma

A primeira versão será desenvolvida como uma aplicação Web utilizando PHP sobre WordPress.

O WordPress será utilizado como plataforma da aplicação, fornecendo a infraestrutura Web, o gerenciamento de usuários e autenticação, as sessões e o suporte à API REST.

O backend específico do SGFP será desenvolvido em um plugin próprio, responsável pelas regras de negócio, pelos endpoints da aplicação e pelo acesso aos dados do domínio.

A persistência relacional da Versão 1 deverá utilizar solução compatível com MySQL ou MariaDB, em conformidade com as restrições tecnológicas consolidadas do projeto.

A interface Web consumirá a API REST própria do SGFP.

Essa arquitetura permitirá que futuras aplicações, como um aplicativo Android ou iOS, utilizem a mesma API e as mesmas regras de negócio, desenvolvendo apenas uma nova interface e o mecanismo de autenticação adequado ao novo cliente.

### 7. Filosofia do Sistema

O sistema não pretende reproduzir o comportamento de um extrato bancário oficial.

Seu objetivo é funcionar como uma ferramenta de planejamento e organização financeira.

O **compromisso financeiro** representa uma obrigação financeira ou uma previsão de entrada definida pelo usuário.

O **lançamento financeiro** representa a movimentação financeira efetivamente realizada.

Por esse motivo, durante o mês corrente, os compromissos e lançamentos poderão ser ajustados pelo usuário conforme sua necessidade, respeitando as regras definidas pelo sistema.

As alterações realizadas em compromissos recorrentes deverão preservar o histórico financeiro, permitindo que o usuário escolha se a modificação será aplicada apenas ao mês atual ou também aos meses seguintes.

Essa filosofia busca equilibrar praticidade, flexibilidade e consistência das informações.

### 8. Visão Geral do Funcionamento

Cada usuário poderá possuir uma ou mais contas financeiras.

Exemplos:

- Conta Corrente;
- Carteira;
- Poupança;
- Investimentos.

Cada conta possuirá seu próprio saldo.

O patrimônio total do usuário corresponderá à soma dos saldos de todas as contas.

Transferências entre contas pertencentes ao usuário modificarão apenas os saldos das contas envolvidas, sem alterar o patrimônio total.

Os compromissos financeiros representarão obrigações ou previsões financeiras.

Quando um compromisso for efetivado, será registrado um lançamento financeiro correspondente à movimentação efetivamente realizada.

As receitas efetivadas aumentarão o saldo da conta correspondente.

As despesas somente reduzirão o saldo quando forem efetivadas e registradas como movimentações financeiras realizadas.

Cada compromisso possuirá informações que representarão sua situação durante o período.

### 9. Organização Financeira

Os compromissos financeiros poderão ser classificados em categorias.

No cadastro de cada usuário, o sistema disponibilizará um conjunto inicial de categorias para facilitar o primeiro uso.

Essas categorias serão criadas já vinculadas ao respectivo usuário e, a partir desse momento, poderão ser renomeadas ou removidas por ele, assim como novas categorias poderão ser adicionadas.

A categoria será opcional no cadastro de um compromisso financeiro.

Caso nenhuma das categorias disponíveis seja adequada ao compromisso, o usuário poderá utilizar o botão de adição disponível junto às categorias para criar uma nova categoria.

O usuário poderá:

- criar novas categorias;
- renomear categorias existentes;
- remover categorias que não desejar utilizar;
- organizar seus compromissos conforme sua própria necessidade.

As categorias funcionarão como agrupadores de compromissos financeiros.

Na visão principal do sistema será apresentado o valor total de cada categoria.

Ao expandir uma categoria, o usuário poderá visualizar os compromissos individuais que a compõem.

### 10. Lançamentos Recorrentes

O sistema permitirá que determinados compromissos financeiros sejam definidos como recorrentes.

Na Versão 1, a recorrência será exclusivamente mensal.

Uma recorrência poderá possuir duração determinada ou não possuir término definido.

Os compromissos recorrentes serão disponibilizados nos meses seguintes conforme as regras definidas para sua recorrência.

Ao editar um compromisso recorrente, o sistema permitirá ao usuário escolher entre:

- aplicar a alteração apenas ao mês atual;
- aplicar a alteração ao mês atual e aos meses seguintes.

Da mesma forma, ao excluir um compromisso recorrente, o sistema perguntará se a exclusão deverá afetar apenas o mês atual ou também os meses seguintes.

Essa abordagem preservará o histórico financeiro e proporcionará maior flexibilidade ao usuário.

### 11. Metodologia de Desenvolvimento

O desenvolvimento seguirá uma metodologia baseada em etapas.

Antes da implementação da API serão realizados:

- levantamento completo dos requisitos;
- especificação formal do sistema;
- definição das regras de negócio;
- elaboração dos casos de uso;
- construção do mapa do domínio;
- modelagem conceitual (MER);
- modelo entidade-relacionamento (DER);
- modelo físico do banco de dados;
- definição da arquitetura da aplicação.

Somente após a conclusão e validação das etapas correspondentes será iniciado o desenvolvimento da API. A interface Web será desenvolvida quando os recursos necessários do backend estiverem disponíveis, conforme os gates definidos no Plano de Desenvolvimento.

Verificações e testes poderão acompanhar a implementação sempre que forem úteis para validar incrementos do sistema. A Etapa 12 permanecerá responsável pela consolidação formal da estratégia de testes, casos de teste, evidências, rastreabilidade e resultados.

### 12. Situação Atual do Projeto

As etapas de levantamento e especificação de requisitos, Casos de Uso, Mapa do Domínio e modelagem de dados foram concluídas.

As **Etapas 6 — Modelagem Conceitual (MER), 7 — Modelo Entidade-Relacionamento (DER) e 8 — Modelo Físico** estão concluídas e validadas. A próxima etapa autorizada é a **Etapa 9 — Arquitetura da Aplicação**.

A decisão de utilizar PHP sobre WordPress na Versão 1, com backend específico em plugin próprio, infraestrutura REST do WordPress e persistência relacional compatível com MySQL ou MariaDB, deverá orientar a definição da Arquitetura. A proteção opcional por PIN permanece adiada para uma versão futura.

A preservação do estado anterior durante uma restauração foi consolidada como cópia de segurança automática pré-restauração: após validação da cópia escolhida e confirmação do usuário, o estado atual deverá ser preservado em condição recuperável antes de qualquer substituição. O mecanismo técnico dessa preservação será definido na etapa apropriada.

A documentação continuará passível de revisão quando forem identificadas inconsistências ou impactos decorrentes das etapas posteriores.


### 13. Objetivo Acadêmico

Além do desenvolvimento de uma aplicação funcional, este projeto possui caráter acadêmico.

Seu propósito é aplicar, de forma integrada, os conhecimentos de Engenharia de Software, Análise de Sistemas, Modelagem de Dados, Banco de Dados, Arquitetura de Software e Desenvolvimento de APIs.

Todo o processo será documentado para que as decisões de projeto possam ser compreendidas, justificadas e reutilizadas em futuros desenvolvimentos.
