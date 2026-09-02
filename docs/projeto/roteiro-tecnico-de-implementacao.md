# SGFP — Roteiro Técnico de Implementação

**Sistema de Gestão Financeira Pessoal**

**Versão do documento:** 1.0

**Data da última atualização:** 02/09/2026

**Classificação:** documento técnico de apoio

**Autoridade:** subordinado ao Plano de Desenvolvimento, às fontes canônicas do projeto e aos gates oficiais

---

## 1. Finalidade

Este documento detalha **como conduzir tecnicamente as etapas posteriores de arquitetura e implementação do SGFP**, com foco em orientação prática, aprendizado progressivo, validação incremental e uso consciente de ferramentas.

Ele não cria uma nova sequência oficial de fases e não substitui:

- o Plano de Desenvolvimento;
- o Documento de Visão;
- o Levantamento de Requisitos;
- o SRS;
- os Casos de Uso;
- o Mapa do Domínio;
- o MER;
- o DER;
- o Modelo Físico;
- a Arquitetura;
- os documentos de Testes.

Quando houver divergência entre este roteiro e uma fonte canônica, **prevalece a fonte oficial correspondente ao assunto**.

Este roteiro também não transforma preferências de ambiente ou ferramentas substituíveis em restrições do produto.

---

## 2. Estado atual e condição de uso

No estado atual do projeto:

- a Etapa 5 — Mapa do Domínio está concluída e validada;
- a Etapa 6 — Modelagem Conceitual (MER) está em andamento;
- DER, Modelo Físico, Arquitetura, API, Interface Web e Testes formais ainda dependem dos respectivos gates.

Portanto, este documento descreve **o caminho técnico a ser seguido quando cada etapa correspondente estiver autorizada**.

Sua existência não significa que a implementação já tenha começado.

---

## 3. Princípios de condução técnica

Durante as etapas posteriores, o trabalho deverá seguir estes princípios:

1. Respeitar os gates oficiais do projeto.
2. Não transformar hipótese técnica em decisão de negócio.
3. Não antecipar arquitetura antes da Etapa 9.
4. Não tratar modelo físico preliminar como fonte superior ao MER e ao DER validados.
5. Introduzir ferramentas somente quando houver um problema concreto que justifique seu uso.
6. Preferir soluções simples, compreensíveis e suficientemente robustas para o escopo da V1.
7. Evitar abstrações criadas apenas porque são comuns em outros projetos.
8. Não assumir automaticamente que tabela, entidade de domínio e classe PHP sejam a mesma coisa.
9. Implementar de forma incremental e verificável.
10. Testar ao longo do desenvolvimento, sem esperar exclusivamente pela Etapa 12.
11. Revisar e validar cada mudança antes de registrá-la no versionamento.
12. Manter rastreabilidade entre necessidade, implementação e verificação.

---

## 4. Restrições oficiais e ferramentas recomendadas

### 4.1 Restrições tecnológicas já estabelecidas

Para a Versão 1, o projeto estabelece:

- aplicação Web;
- backend em PHP;
- WordPress como plataforma;
- backend específico do SGFP implementado em plugin próprio;
- utilização da infraestrutura REST do WordPress;
- banco relacional compatível com WordPress, atualmente MySQL ou MariaDB;
- identidade, autenticação e sessão providas pelo WordPress;
- API protegida contra acesso não autorizado conforme requisitos e arquitetura futura.

Esses elementos constituem restrições ou direções oficiais do produto.

### 4.2 Elementos que não são restrições do produto

Não fazem parte das restrições do SGFP, salvo decisão formal posterior:

- sistema operacional do desenvolvedor;
- editor ou IDE;
- extensão do editor;
- cliente HTTP específico;
- ferramenta específica de formatação;
- ferramenta específica de análise estática.

Esses itens podem ser escolhidos conforme adequação ao ambiente de desenvolvimento.

### 4.3 Ferramentas preferenciais ou candidatas

Algumas ferramentas são fortemente adequadas ao ecossistema e podem ser adotadas quando houver necessidade real:

- **Composer** — dependências, autoload e organização por namespaces;
- **PSR-4** — estratégia preferencial de autoload quando compatível com a arquitetura definida;
- **PHPUnit** — ferramenta preferencial para testes automatizados PHP;
- **cliente HTTP** — Bruno, Postman, Insomnia ou equivalente para testar a API independentemente da interface;
- **PHPStan** — candidato para análise estática;
- **PHP-CS-Fixer ou PHP_CodeSniffer** — candidatos para padronização e qualidade de código;
- **Git** e o repositório remoto adotado pelo projeto — versionamento e rastreabilidade técnica.

A adoção concreta dessas ferramentas deverá ser confirmada no momento em que o problema correspondente existir.

---

## 5. Relação entre este roteiro e as etapas oficiais

Este roteiro não possui fases próprias numeradas. Ele detalha atividades técnicas dentro das etapas oficiais.

| Etapa oficial | Foco técnico principal |
| --- | --- |
| Etapa 6 — MER | Consolidar entidades, atributos, relacionamentos e cardinalidades em nível conceitual |
| Etapa 7 — DER | Refinar o modelo e deixá-lo apto à transformação relacional |
| Etapa 8 — Modelo Físico | Definir e validar a estrutura SQL e materializá-la de forma controlada |
| Etapa 9 — Arquitetura | Definir organização do plugin, componentes, dependências, acesso a dados, REST e integração com WordPress |
| Etapa 10 — API | Implementar funcionalidades do backend de forma incremental e testável |
| Etapa 11 — Interface Web | Construir a interface sobre contratos de API suficientemente estáveis |
| Etapa 12 — Testes | Consolidar estratégia, casos, evidências, rastreabilidade e resultados de teste |

---

## 6. Etapa 8 — Do modelo físico ao banco materializado

Quando a Etapa 8 estiver autorizada, deverá ser mantida a diferença entre:

**Modelo físico**
Definição das tabelas, colunas, tipos, chaves, restrições, índices e demais elementos físicos.

**Banco materializado**
Estrutura efetivamente criada e executada no SGBD.

Fluxo esperado:

```text
MER validado
    ↓
DER validado
    ↓
Modelo físico
    ↓
Revisão estrutural
    ↓
Validação SQL
    ↓
Execução controlada em MySQL/MariaDB
    ↓
Banco materializado para testes
```

Antes de considerar o modelo físico concluído, deverão ser verificados, conforme aplicável:

- tabelas;
- colunas;
- tipos;
- chaves primárias;
- chaves estrangeiras;
- restrições UNIQUE;
- CHECKs suportados e adequados;
- índices;
- integridade referencial;
- regras estruturais;
- compatibilidade com a plataforma WordPress;
- coerência com MER e DER validados.

Qualquer SQL produzido antes dessa etapa deverá ser tratado como **material preliminar de apoio**, sujeito a revisão.

---

## 7. Etapa 9 — Arquitetura da aplicação

A Etapa 9 deverá responder principalmente:

> Como o software será organizado para cumprir as responsabilidades já compreendidas no domínio e nos requisitos?

### 7.1 Pontos que deverão ser definidos

Entre os pontos a decidir estão:

- estrutura do plugin SGFP;
- ponto de entrada do plugin;
- organização das dependências;
- autoload e namespaces;
- responsabilidades dos componentes;
- dependências entre componentes;
- organização das rotas REST;
- acesso e persistência de dados;
- integração com usuários, autenticação e sessão do WordPress;
- autorização das operações;
- estratégia de criação e evolução das tabelas próprias do plugin;
- versionamento do esquema quando necessário;
- tratamento de erros;
- fronteiras entre backend e interface.

### 7.2 Componentes arquiteturais previstos

O Plano de Desenvolvimento já prevê a análise de componentes como:

- Controllers;
- Services;
- Repositories;
- API REST;
- representações de conceitos e regras de domínio em código quando justificadas.

Uma direção arquitetural possível é:

```text
Requisição HTTP
    ↓
WordPress REST API
    ↓
Rota SGFP
    ↓
Controller
    ↓
Service
    ↓
Repository
    ↓
Banco
    ↓
Resposta
```

Regras ou objetos de domínio poderão participar do fluxo conforme a necessidade real.

Esse desenho **não obriga** a criação antecipada de uma pasta ou classe para cada elemento citado. A estrutura final deverá ser definida na própria Etapa 9.

### 7.3 Composer

Composer deverá ser considerado a ferramenta preferencial para:

- dependências PHP;
- autoload;
- namespaces;
- organização do código;
- PSR-4 quando adequado.

Antes de sua utilização efetiva, deverão ser compreendidos:

- `composer.json`;
- `composer.lock`;
- `vendor/`;
- autoload;
- namespaces;
- PSR-4;
- impacto do Composer no carregamento das classes do plugin.

---

## 8. Estratégia de tabelas no WordPress

A existência de um arquivo SQL não resolve, sozinha, a manutenção do banco pelo plugin.

Na Etapa 9 deverá ser definida uma estratégia adequada para que o plugin possa, quando necessário:

- detectar sua instalação;
- criar as tabelas próprias do SGFP;
- utilizar corretamente o prefixo de tabelas do WordPress;
- registrar uma versão do esquema;
- aplicar futuras alterações estruturais de forma controlada;
- preservar integridade e compatibilidade com o ambiente WordPress.

O SQL do Modelo Físico será uma referência para essa estratégia, mas não deverá ser copiado mecanicamente sem considerar o funcionamento real do WordPress.

---

## 9. Etapa 10 — Implementação da API

A implementação deverá começar somente após a arquitetura correspondente estar definida.

### 9.1 Desenvolvimento incremental

Sempre que apropriado, o backend deverá ser desenvolvido por **funcionalidades de ponta a ponta**.

Em vez de criar todos os componentes de uma camada antes das demais, deverá ser preferido um fluxo semelhante a:

```text
Caso de Uso / necessidade real
    ↓
Rota REST
    ↓
Controller
    ↓
Service
    ↓
Regras e conceitos necessários
    ↓
Repository
    ↓
Banco
    ↓
Resposta HTTP
    ↓
Teste
```

Depois de validar uma primeira funcionalidade, a mesma estrutura poderá ser reutilizada ou refinada nas seguintes.

Essa abordagem reduz o risco de criar muitas abstrações sem uso real.

### 9.2 Seleção da primeira funcionalidade

A primeira funcionalidade deverá ser escolhida considerando:

- requisitos;
- Casos de Uso;
- dependências;
- complexidade;
- modelo de dados;
- valor didático;
- capacidade de percorrer todo o fluxo do backend.

Nenhuma funcionalidade específica deverá ser fixada neste roteiro antes dessa análise.

---

## 10. WordPress REST API

Quando a API começar a ser implementada, deverão ser compreendidos e aplicados, conforme necessidade:

- HTTP;
- URI;
- JSON;
- request e response;
- headers;
- métodos HTTP;
- códigos de status;
- registro de rotas REST;
- autenticação;
- autorização;
- permission callbacks;
- validação de entrada;
- tratamento de erros.

As rotas deverão derivar de requisitos e Casos de Uso reais.

Não deverão ser criados endpoints apenas para preencher uma estrutura arquitetural.

---

## 11. Responsabilidades esperadas dos componentes

### 11.1 Controller

Quando adotado, deverá concentrar principalmente responsabilidades de fronteira HTTP, como:

- receber a chamada;
- interpretar a requisição;
- tratar aspectos de entrada relacionados ao protocolo;
- acionar a operação adequada da aplicação;
- produzir a resposta HTTP.

Regras de negócio complexas não deverão ser concentradas no Controller.

### 11.2 Service

Quando adotado, deverá coordenar operações da aplicação e Casos de Uso.

Poderá:

- aplicar ou coordenar regras;
- utilizar objetos ou serviços de domínio;
- acionar persistência;
- organizar transações e fluxos de aplicação quando necessário.

Um Service não deverá existir apenas para repetir o Controller.

### 11.3 Repository

Quando adotado, deverá encapsular o acesso e a persistência de dados de maneira coerente com a arquitetura.

Seu objetivo principal é evitar SQL e detalhes de persistência espalhados por toda a aplicação.

Repository não deverá virar uma abstração genérica sem responsabilidade clara.

### 11.4 Domínio em código

Conceitos e regras relevantes poderão receber representações próprias em código quando isso melhorar clareza, integridade ou testabilidade.

Não deverá ser assumida a equivalência automática:

```text
Tabela = Entidade de Domínio = Classe PHP
```

Cada representação deverá existir porque resolve uma responsabilidade concreta.

---

## 12. Testes manuais da API

A API deverá poder ser testada independentemente da Interface Web.

Para isso poderá ser utilizado um cliente HTTP, como:

- Bruno;
- Postman;
- Insomnia;
- ferramenta equivalente.

A escolha é substituível e não faz parte da arquitetura do produto.

O objetivo é separar claramente duas perguntas:

**O backend funciona corretamente?**

**A interface utiliza corretamente o backend?**

Quando a ferramenta escolhida permitir, requisições úteis poderão ser versionadas junto ao projeto.

---

## 13. Testes automatizados PHP

Testes automatizados deverão ser introduzidos quando existirem componentes reais que justifiquem sua verificação.

**PHPUnit** deverá ser considerado a ferramenta preferencial para testes PHP.

Os testes poderão priorizar:

- regras de negócio;
- Services;
- objetos de domínio;
- casos de erro;
- invariantes;
- componentes isoláveis;
- integrações relevantes quando necessário.

Não deverão ser criados testes artificiais apenas para aumentar quantidade ou cobertura sem valor técnico.

---

## 14. Qualidade e análise do código

Ferramentas de qualidade deverão ser introduzidas gradualmente, de acordo com o código existente.

Necessidades típicas incluem:

- padronização de estilo;
- formatação automática;
- detecção de problemas estáticos;
- identificação de inconsistências antes da execução.

Ferramentas possíveis incluem:

- PHP-CS-Fixer;
- PHP_CodeSniffer;
- PHPStan;
- alternativas equivalentes adequadas ao projeto.

Não é necessário adotar simultaneamente todas as ferramentas disponíveis.

A configuração deverá ser suficientemente rigorosa para melhorar o projeto sem criar burocracia desnecessária.

---

## 15. Etapa 11 — Interface Web

A interface deverá ser construída sobre operações de backend suficientemente estáveis.

Deverá:

- utilizar os recursos disponibilizados pela API;
- respeitar autenticação e autorização definidas;
- evitar duplicar regras de negócio já existentes no backend;
- apresentar erros de forma adequada;
- manter separação entre comportamento visual e regras do domínio.

A escolha de tecnologias adicionais de frontend deverá ocorrer somente quando necessária e deverá respeitar as restrições acadêmicas e arquiteturais vigentes.

---

## 16. Etapa 12 — Consolidação dos testes

A Etapa 12 não representa o primeiro momento em que testes ocorrerão.

Ela deverá consolidar formalmente:

- estratégia de testes;
- Casos de Teste;
- cenários;
- evidências;
- resultados;
- validação dos requisitos;
- rastreabilidade;
- regressões;
- correções necessárias.

A cadeia de rastreabilidade deverá permitir relacionar, quando aplicável:

```text
Regra de Negócio
    ↓
Requisito
    ↓
Caso de Uso
    ↓
Critério de Aceitação
    ↓
Modelagem / Arquitetura aplicável
    ↓
Implementação
    ↓
Caso de Teste
    ↓
Resultado
```

---

## 17. Método didático de implementação

Quando um conceito, ferramenta ou mecanismo novo for introduzido, deverá ser preferida a sequência:

```text
PROBLEMA
    ↓
por que precisa ser resolvido
    ↓
CONCEITO
    ↓
como a solução funciona
    ↓
DECISÃO
    ↓
por que foi escolhida
    ↓
IMPLEMENTAÇÃO
    ↓
TESTE
    ↓
VALIDAÇÃO
    ↓
VERSIONAMENTO
```

Antes de executar comandos ou criar estruturas novas, deverá ficar claro:

- o que será feito;
- por que será feito;
- qual problema será resolvido;
- como a nova parte se relaciona com o restante do sistema;
- como será verificado se funcionou.

---

## 18. Tratamento de comandos

Quando forem necessários comandos de PHP, Composer, banco de dados, WordPress, Git ou ferramentas auxiliares:

1. explicar brevemente o objetivo;
2. fornecer os comandos necessários;
3. executar ou solicitar verificação do resultado quando aplicável;
4. interpretar a saída;
5. não presumir sucesso sem evidência;
6. interromper a progressão da parte afetada quando houver erro relevante não compreendido.

---

## 19. Alterações no repositório

Antes de modificar código existente:

- inspecionar a estrutura atual;
- localizar os arquivos relevantes;
- identificar padrões já adotados;
- evitar duplicação;
- confirmar dependências;
- verificar o estado do Git.

Depois da alteração:

- revisar o diff;
- executar testes e verificações pertinentes;
- confirmar que não houve alteração acidental de escopo;
- verificar `git status` e, quando aplicável, `git diff --check`;
- explicar o que mudou;
- somente então preparar o commit.

Commits deverão ser pequenos e semanticamente coerentes sempre que possível.

---

## 20. Marcos técnicos futuros

Os marcos abaixo são referências para as etapas posteriores, não indicam o estado atual do projeto.

### Marco A — Modelo de dados implementável

```text
MER validado
    ↓
DER validado
    ↓
Modelo Físico validado
    ↓
Banco materializado em ambiente de teste
```

### Marco B — Fundação arquitetural

```text
Arquitetura definida
    ↓
Plugin mínimo reconhecido pelo WordPress
    ↓
Autoload/dependências configurados quando aplicável
    ↓
Estratégia de tabelas definida
    ↓
Infraestrutura REST preparada
```

### Marco C — Primeira funcionalidade de ponta a ponta

```text
Requisito / Caso de Uso
    ↓
Rota REST
    ↓
Controller
    ↓
Service
    ↓
Persistência
    ↓
Resposta
    ↓
Teste independente da interface
```

### Marco D — Aplicação integrada

```text
Backend suficientemente estável
    ↓
Interface Web
    ↓
Integração
    ↓
Testes formais
    ↓
Estabilização
    ↓
SGFP V1 funcional
```

---

## 21. Regras para não limitar desnecessariamente o projeto

Este roteiro deverá ser revisado sempre que uma recomendação técnica correr o risco de ser tratada como obrigação sem justificativa.

Em particular:

- não vincular o projeto a um sistema operacional específico;
- não vincular o projeto a uma IDE específica;
- não exigir um cliente HTTP específico;
- não proibir frameworks ou bibliotecas por princípio geral; qualquer adoção adicional deverá ser justificada e compatível com WordPress e com a arquitetura aprovada;
- não tornar ferramenta de qualidade parte do produto;
- não congelar estrutura de diretórios antes da Etapa 9;
- não criar classes ou camadas apenas para reproduzir um desenho teórico;
- não tratar uma solução preliminar de banco como decisão conceitual.

Restrições reais deverão vir das fontes oficiais, de exigências acadêmicas, de decisões arquiteturais aprovadas ou de limitações técnicas demonstradas.

---

## 22. Regra de manutenção deste documento

Atualizar este roteiro quando houver:

- decisão arquitetural aprovada que altere a direção técnica;
- adoção definitiva de ferramenta relevante;
- mudança no processo de implementação;
- alteração do fluxo de desenvolvimento ou teste;
- descoberta de incompatibilidade com WordPress, PHP ou banco adotado;
- mudança de gate que torne uma orientação futura executável.

Não atualizar este documento para registrar regras de negócio específicas. Essas alterações pertencem às respectivas fontes canônicas.

---

## 23. Histórico de atualização

### Versão 1.0 — 02/09/2026

Criação do roteiro técnico a partir das diretrizes preliminares de transição para implementação, reconciliadas com o Plano de Desenvolvimento e com o estado oficial do projeto.

Principais decisões desta versão:

1. Manutenção das 12 etapas oficiais sem criação de uma segunda sequência de fases.
2. Registro explícito de que a Etapa 6 permanece em andamento e que a implementação ainda não foi iniciada.
3. Remoção de sistema operacional, editor e extensão de IDE como restrições do produto.
4. Preservação de PHP, WordPress, plugin próprio, infraestrutura REST e MySQL/MariaDB conforme documentação oficial.
5. Composer registrado como ferramenta preferencial, não como requisito funcional.
6. Controllers, Services e Repositories tratados como componentes previstos para definição arquitetural, sem estrutura de código congelada antecipadamente.
7. Domain tratado como representação possível de conceitos e regras, sem equivalência automática entre tabela, entidade e classe.
8. Cliente HTTP, PHPUnit e ferramentas de qualidade tratados como ferramentas preferenciais ou substituíveis.
9. Desenvolvimento por funcionalidades de ponta a ponta registrado como estratégia recomendada de implementação incremental.
10. Separação entre testes contínuos durante implementação e consolidação formal da Etapa 12.
11. Modelo físico preliminar tratado apenas como material de apoio até validação de MER e DER.
12. Inclusão de método didático, validação incremental e versionamento como práticas transversais.
