# MAPA DO DOMÍNIO

## Sistema de Gestão Financeira Pessoal — SGFP

**Sigla:** SGFP
**Documento:** Mapa do Domínio
**Versão:** 1.4
**Etapa:** Etapa 5 — Mapa do Domínio
**Data da revisão:** 04/09/2026

---

## 1. Objetivo do Documento

Este documento apresenta o Mapa do Domínio do Sistema de Gestão Financeira Pessoal — SGFP.

Seu objetivo é identificar, organizar e relacionar os principais conceitos necessários para compreender o funcionamento do sistema, utilizando como base as informações consolidadas no Documento de Visão, no Levantamento de Requisitos, na Especificação de Requisitos de Software e nos Casos de Uso.

O Mapa do Domínio funciona como uma etapa de transição entre a compreensão dos requisitos e a Modelagem Conceitual.

A terminologia consolidada neste documento deverá orientar os modelos seguintes, sempre respeitando as regras de negócio, requisitos e casos de uso vigentes.

Nesta etapa **não são definidos**:

- entidades definitivas do modelo de dados;
- cardinalidades;
- chaves;
- tabelas;
- colunas;
- tipos de dados;
- estratégias de persistência;
- estruturas físicas de banco de dados;
- classes, métodos ou decisões de arquitetura.

A presença de um conceito neste documento não determina automaticamente que ele deverá se tornar uma entidade na Modelagem Conceitual.

---

## 2. Escopo e Fontes de Verdade

### 2.1 Fontes principais

Este mapa foi consolidado a partir das seguintes fontes do projeto:

- `docs/projeto/documento-de-visao.md`;
- `docs/governanca/relatorio-de-consolidacao.md`;
- `docs/requisitos/levantamento/01-usuarios.md` a `14-integracoes.md`;
- `docs/requisitos/srs/`;
- Casos de Uso `UC-001` a `UC-022`, em `docs/casos-de-uso/casos/`.

As regras de negócio permanecem sob autoridade dos documentos de Levantamento de Requisitos.
Os requisitos funcionais e não funcionais permanecem sob autoridade do SRS.
Os Casos de Uso permanecem como fonte das interações entre usuário e sistema.

Este Mapa do Domínio **organiza e relaciona esses conceitos**, sem substituir as fontes anteriores.

### 2.2 Fora do escopo da Versão 1

Não fazem parte do escopo atual do domínio da V1:

- integrações bancárias;
- compras individuais de cartão;
- controle de limite de crédito;
- relatórios avançados ou independentes;
- anexos de imagens;
- sincronização em nuvem;
- aplicativo móvel;
- demais funcionalidades futuras ainda não incorporadas à V1.

---

## 3. Fronteira do Domínio

O SGFP registra manualmente o planejamento e a realização das finanças pessoais do usuário.

### 3.1 Planejamento

O planejamento financeiro é representado principalmente por **Compromissos Financeiros**.

Um compromisso representa uma obrigação ou expectativa financeira antes de sua realização.

### 3.2 Realização

A realização financeira é representada por **Lançamentos Financeiros**.

Um lançamento representa uma movimentação que efetivamente ocorreu e produz efeito financeiro sobre uma conta.

### 3.3 Transferências

As **Transferências** representam movimentações de valores entre contas pertencentes ao mesmo usuário.

Como apenas redistribuem recursos entre contas próprias, não alteram o patrimônio total.

### 3.4 Informações derivadas

**Saldo**, **Patrimônio Total**, **Histórico Financeiro** e informações apresentadas no **Dashboard** são obtidos a partir de dados financeiros existentes.

Esses elementos não representam valores financeiros independentes.

---

## 4. Núcleo do Domínio

O núcleo financeiro do SGFP está organizado principalmente em torno dos seguintes conceitos:

- Usuário;
- Conta Financeira;
- Compromisso Financeiro;
- Categoria;
- Recorrência;
- Lançamento Financeiro;
- Transferência.

Também existem conceitos e informações relevantes para compreender o comportamento do sistema:

- Natureza Financeira;
- Período Financeiro;
- Efetivação;
- Desfazimento da Efetivação;
- Saldo;
- Patrimônio Total;
- Preferências do Usuário;
- Recuperação de Acesso;
- Cópia de Segurança;
- Restauração.

Esses elementos possuem responsabilidades diferentes e não deverão ser tratados automaticamente da mesma forma nas etapas posteriores.

---

## 5. Conceitos e Responsabilidades

### 5.1 Usuário

**Definição** — Pessoa que utiliza o SGFP para realizar o próprio controle financeiro.

**Responsabilidade** — Representa o proprietário das informações financeiras e das configurações associadas ao uso do sistema.

**Observações** — A aplicação Web poderá possuir múltiplos usuários cadastrados. Cada usuário permanece proprietário de suas próprias informações financeiras, configurações e categorias, que deverão ser isoladas dos dados pertencentes a outros usuários.

**Rastreabilidade** — `USR-RN-001` a `USR-RN-009` · `UC-001` a `UC-004`

---

### 5.2 Conta Financeira

**Definição** — Local financeiro utilizado pelo usuário para manter e movimentar valores.

**Responsabilidade** — Representa onde as movimentações financeiras produzem seus efeitos.

**Observações** —

- o usuário pode possuir múltiplas contas;
- existe uma Conta Principal e podem existir contas secundárias;
- a conta possui nome definido pelo usuário;
- o saldo da conta é derivado das movimentações financeiras registradas;
- a conta não possui saldo inicial armazenado como atributo independente.

**Rastreabilidade** — `CTA-RN-001` a `CTA-RN-010` · `UC-005` a `UC-006`

#### Conta Principal

Representa o papel da conta utilizada como referência principal do controle financeiro.

Quando for necessário representar o valor financeiro existente no início da utilização do sistema, esse valor é registrado por meio de um **Lançamento Financeiro de Entrada** associado à Conta Principal.

#### Conta Secundária

Representa uma conta adicional pertencente ao usuário.

Para composição inicial de valor, uma Conta Secundária não recebe uma Entrada direta destinada a criar um “saldo inicial”. O valor deve chegar por meio de **Transferência** com a Conta Principal, conforme as regras vigentes.

---

### 5.3 Saldo

**Definição** — Posição financeira de uma Conta Financeira em determinado momento.

**Responsabilidade** — Expressa o resultado das movimentações financeiras efetivamente registradas na conta.

**Observações** —

- Entradas aumentam o saldo;
- Saídas reduzem o saldo;
- Transferências produzem efeitos nas contas envolvidas;
- o saldo é derivado das movimentações e não constitui um valor financeiro independente.

**Rastreabilidade** — `CTA-RN-005` e `CTA-RN-006` · regras correspondentes de Lançamentos e Transferências

---

### 5.4 Compromisso Financeiro

**Definição** — Previsão de Entrada ou Saída registrada pelo usuário antes da realização financeira.

**Responsabilidade** — Representa uma obrigação ou expectativa financeira planejada.

**Observações** —

- possui natureza financeira;
- pode estar associado a uma categoria, de forma opcional, podendo ser cadastrado e permanecer sem categoria;
- está associado a um período financeiro;
- pode possuir valor igual a zero;
- enquanto pendente, pode ser alterado ou removido conforme as regras do sistema;
- sua efetivação produz a movimentação financeira correspondente;
- após efetivado, exige desfazimento da efetivação antes de alterações ou exclusões permitidas pelas regras vigentes.

**Rastreabilidade** — `CMP-RN-001` a `CMP-RN-010` · regras relacionadas de Lançamentos Financeiros

---

### 5.5 Natureza Financeira

**Definição** — Classificação que indica o efeito financeiro esperado de um compromisso ou movimentação.

Na V1 existem duas naturezas:

- **Entrada** — quando efetivada, aumenta o saldo;
- **Saída** — quando efetivada, reduz o saldo.

**Responsabilidade** — Classificar o efeito financeiro esperado.

Entrada e Saída são classificações do domínio e não conceitos independentes com identidade própria.

---

### 5.6 Categoria

**Definição** — Elemento utilizado para organizar os Compromissos Financeiros.

**Responsabilidade** — Classificar e agrupar compromissos para facilitar a organização e visualização.

**Observações** —

- a categoria é opcional no momento do cadastro do compromisso;
- pode ser criada, renomeada e removida;
- pode ser criada durante o cadastro de um compromisso;
- não produz efeito direto sobre saldo ou patrimônio;
- a remoção de uma categoria não exclui os compromissos anteriormente associados.

**Observações adicionais** — No cadastro de cada usuário, o sistema disponibiliza um conjunto inicial de categorias criado já vinculado a esse usuário. Essas categorias são sugestões e, após a criação, podem ser renomeadas ou removidas pelo próprio usuário; novas categorias também podem ser adicionadas. Não existe um conjunto global compartilhado cuja alteração afete outros usuários.

**Rastreabilidade** — `CAT-RN-001` a `CAT-RN-009` · `UC-011`

---

### 5.7 Recorrência

**Definição** — Comportamento de repetição mensal associado a compromissos financeiros.

**Responsabilidade** — Controlar a continuidade temporal de compromissos ao longo dos períodos.

**Observações** —

- na V1, a periodicidade é mensal;
- pode iniciar no mês corrente ou no mês seguinte;
- pode possuir duração determinada ou não possuir término definido;
- alterações e cancelamentos podem afetar somente o período atual ou também períodos seguintes, conforme as regras vigentes;
- períodos anteriores devem permanecer preservados.

A forma de representação da Recorrência nos modelos posteriores não é definida neste documento.

**Rastreabilidade** — `REC-RN-001` a `REC-RN-017` · `UC-008`

---

### 5.8 Efetivação

**Definição** — Confirmação de que a movimentação prevista por um Compromisso Financeiro realmente ocorreu.

**Responsabilidade** — Marcar a passagem entre planejamento e realização financeira.

Conceitualmente:

**Compromisso Pendente → Efetivação → Lançamento Financeiro → Efeito Financeiro**

A Efetivação representa um comportamento ou transição do domínio. Este documento não determina sua representação nos modelos de dados.

**Rastreabilidade** — regras de Lançamentos Financeiros · `UC-009` e `UC-010`

---

### 5.9 Lançamento Financeiro

**Definição** — Movimentação financeira efetivamente realizada.

**Responsabilidade** — Registrar a ocorrência financeira e produzir efeito sobre a Conta Financeira relacionada.

**Princípio central do SGFP** —

**Compromisso Financeiro = obrigação ou previsão.**
**Lançamento Financeiro = movimentação efetivamente realizada.**

**Observações** —

- possui nome, valor e data de efetivação;
- pode possuir descrição complementar;
- quando originado pela efetivação de um compromisso, mantém vínculo com sua origem conforme as regras vigentes;
- participa da formação do histórico financeiro;
- compõe o cálculo do saldo da conta.

**Rastreabilidade** — `LAN-RN-001` a `LAN-RN-040` · `UC-009` a `UC-010`

---

### 5.10 Desfazimento da Efetivação

**Definição** — Reversão da condição de uma movimentação anteriormente efetivada.

**Responsabilidade** — Retirar o efeito financeiro da movimentação e permitir o retorno do compromisso à condição prevista pelas regras do sistema.

O desfazimento deve preservar a consistência entre Compromisso Financeiro, Lançamento Financeiro e Saldo.

Este documento não define a estratégia de armazenamento ou implementação necessária para preservar o histórico dessa operação.

**Rastreabilidade** — regras de Lançamentos Financeiros · `UC-009` e `UC-010`

---

### 5.11 Transferência

**Definição** — Movimentação de valores entre contas pertencentes ao mesmo usuário.

**Responsabilidade** — Redistribuir recursos entre contas próprias sem alterar o Patrimônio Total.

**Observações** —

- possui Conta de Origem e Conta de Destino;
- origem e destino devem ser contas diferentes;
- na V1, as regras vigentes relacionam transferências entre Conta Principal e Conta Secundária;
- enquanto pendente, não produz efeito financeiro;
- quando efetivada, reduz o saldo da conta de origem e aumenta o saldo da conta de destino;
- insuficiência de saldo não impede a operação na V1, conforme as regras vigentes;
- uma transferência efetivada deve ser desfeita antes de alterações ou exclusões permitidas.

Este documento não determina se Transferência será representada como especialização, composição ou entidade independente nos modelos posteriores.

**Rastreabilidade** — `TRF-RN-001` a `TRF-RN-024` · `UC-013`

---

### 5.12 Patrimônio Total

**Definição** — Posição financeira consolidada do usuário considerando os saldos de suas contas.

Conceitualmente:

**Patrimônio Total = soma dos saldos das Contas Financeiras**

**Responsabilidade** — Apresentar a situação financeira consolidada.

Transferências entre contas do próprio usuário não alteram o patrimônio total.

O Patrimônio Total é uma informação derivada e não possui, neste documento, definição de persistência própria.

---

### 5.13 Período Financeiro

**Definição** — Contexto temporal utilizado para organizar e consultar informações financeiras.

**Responsabilidade** — Organizar compromissos, recorrências, movimentações e previsões no tempo.

Na V1, a principal referência é mês e ano.

O usuário pode consultar períodos anteriores, o período atual e períodos futuros.

Este documento não determina se Período Financeiro será representado como entidade própria nos modelos posteriores.

---

## 6. Casos Específicos do Domínio

### 6.1 Cartão de Crédito

Na V1, Cartão de Crédito é tratado por meio dos mecanismos existentes de Compromisso Financeiro.

O pagamento de uma fatura é representado como um compromisso de Saída, podendo utilizar Recorrência conforme as regras aplicáveis.

A V1 não contempla:

- registro individual das compras do cartão;
- composição automática da fatura;
- controle de limite de crédito;
- regras próprias de fechamento de fatura;
- regras próprias de vencimento do cartão.

Cartão de Crédito não é tratado neste mapa como conceito independente do núcleo do domínio.

**Rastreabilidade** — Casos de Uso e regras específicas de compromissos de cartão vigentes na V1.

---

### 6.2 Parcelamento

Na V1, Parcelamento utiliza os conceitos de Compromisso Financeiro e Recorrência.

Conceitualmente:

**Parcelamento → Compromissos distribuídos em períodos sucessivos por meio das regras de recorrência aplicáveis.**

Alterações, exclusões e efetivações seguem as regras vigentes de Compromissos e Recorrência.

Parcelamento não é tratado neste mapa como conceito independente do núcleo do domínio.

**Rastreabilidade** — Casos de Uso e regras específicas de compromissos parcelados vigentes na V1.

---

## 7. Elementos de Apoio ao Sistema

Os elementos abaixo são necessários ao funcionamento do SGFP, mas não pertencem ao núcleo financeiro do domínio.

### 7.1 Preferências do Usuário

**Definição** — Opções pessoais associadas ao uso do sistema.

Na V1 incluem, entre outras configurações previstas:

- tema Claro ou Escuro.

A proteção opcional por PIN foi retirada do escopo da V1 e permanece registrada apenas como funcionalidade futura.

Este documento não define como essas preferências serão estruturadas ou armazenadas.

**Rastreabilidade** — regras vigentes de Configurações para tema · `UC-019`; `CFG-RN-001` a `CFG-RN-007` e `UC-018` permanecem preservados para versão futura

---

### 7.2 Recuperação de Acesso

**Definição** — Mecanismo que permite ao usuário recuperar o acesso por meio da redefinição de senha.

**Responsabilidade** — Possibilitar recuperação controlada das credenciais.

Na V1, a recuperação de senha utiliza o mecanismo disponibilizado pelo WordPress e o e-mail cadastrado do usuário.

Este documento não define estrutura técnica de tokens, algoritmos criptográficos ou mecanismo de persistência.

**Rastreabilidade** — regras de Usuário e Configurações · `UC-004` e casos relacionados

---

### 7.3 Serviço de E-mail

**Definição** — Serviço externo utilizado para entrega das comunicações necessárias à recuperação de acesso e para entrega da cópia de segurança criada manualmente pelo usuário.

**Responsabilidade** — Viabilizar o envio das mensagens previstas na recuperação de senha e a entrega da cópia de segurança ao endereço de e-mail cadastrado.

Não constitui integração financeira externa.

---

### 7.4 Cópia de Segurança

**Definição** — Cópia dos dados do sistema produzida para preservação e posterior recuperação.

**Responsabilidade** — Apoiar a proteção dos dados do usuário.

Na V1, a criação ordinária da cópia é manual e a cópia gerada é enviada ao endereço de e-mail cadastrado do usuário.

Existe uma única geração automática específica na V1: após a validação da cópia escolhida e a confirmação de uma restauração, o sistema deverá gerar uma cópia do estado imediatamente anterior antes de substituir os dados atuais. Essa cópia deverá permanecer preservada em condição recuperável, ser compatível com o mesmo processo de restauração das cópias manuais e ser identificável como de origem pré-restauração.

Essa proteção pontual não caracteriza backup automático periódico ou contínuo.

Este documento não define formato, mecanismo técnico de geração, armazenamento ou proteção da cópia.

**Rastreabilidade** — regras de Backup e Restauração · `UC-020` e `UC-021`

---

### 7.5 Restauração

**Definição** — Processo de recuperação dos dados a partir de uma cópia de segurança válida.

**Responsabilidade** — Recuperar um estado previamente preservado conforme as regras vigentes.

Na V1, o usuário fornece ao sistema o arquivo correspondente à cópia de segurança que deseja restaurar.

Após a validação da cópia e a confirmação do usuário, o estado imediatamente anterior deverá ser preservado em uma cópia de segurança recuperável antes da substituição integral dos dados atuais. Se essa preservação não puder ser concluída com sucesso, a restauração não deverá prosseguir e os dados atuais deverão permanecer inalterados.

A restauração deve respeitar as regras de confirmação, validação, substituição integral dos dados, ausência de mesclagem e preservação de integridade definidas no projeto.

Este documento não define a estratégia técnica utilizada para gerar, armazenar ou disponibilizar a cópia pré-restauração.

**Rastreabilidade** — regras de Backup e Restauração · `UC-020` e `UC-021`

---

## 8. Informações e Visões Derivadas

Os elementos desta seção são relevantes para o usuário, mas são obtidos a partir de conceitos e informações já existentes no domínio.

### 8.1 Histórico Financeiro

Representa a consulta das movimentações financeiras efetivamente realizadas ao longo do tempo.

**Classificação:** visão derivada.

### 8.2 Entradas Previstas

Representam valores previstos a partir de Compromissos Financeiros de natureza Entrada.

**Classificação:** informação derivada.

### 8.3 Saídas Previstas

Representam valores previstos a partir de Compromissos Financeiros de natureza Saída.

**Classificação:** informação derivada.

### 8.4 Saldo de Abertura do Período

Representa a posição financeira da conta no início de um período consultado, obtida a partir das movimentações anteriores.

Não corresponde a um “saldo inicial” armazenado na Conta Financeira.

**Classificação:** informação derivada.

### 8.5 Saldo Final Previsto

Representa uma projeção financeira do período, formada a partir da posição financeira de abertura e dos compromissos previstos para o período.

**Classificação:** informação derivada.

### 8.6 Valores por Categoria

Representam consolidações dos Compromissos Financeiros associados às Categorias.

**Classificação:** informação derivada.

### 8.7 Dashboard

Representa uma visão consolidada de informações financeiras já existentes.

Pode apresentar, conforme os requisitos vigentes:

- posição financeira do período;
- entradas previstas;
- saídas previstas;
- saldo final previsto;
- compromissos;
- categorias;
- patrimônio;
- informações do período selecionado.

O Dashboard não possui dados financeiros independentes.

**Classificação:** visão derivada e elemento de interface.

---

## 9. Elementos Externos ao Domínio Financeiro

### 9.1 API REST

A API REST representa uma decisão de arquitetura e não um conceito do domínio financeiro.

Ela é apenas reconhecida como parte do contexto do sistema e não é modelada neste documento.

### 9.2 Interface Web

A Interface Web representa a forma de interação do usuário com o sistema.

Ela não é um conceito do domínio financeiro e não é modelada neste documento.

---

## 10. Relações Conceituais Principais

As relações abaixo expressam vínculos existentes entre conceitos do domínio, **sem definir cardinalidades ou estruturas de dados**.

| Origem | Relação conceitual | Destino |
|---|---|---|
| Usuário | possui e administra | Conta Financeira |
| Usuário | registra e administra | Compromisso Financeiro |
| Usuário | organiza | Categoria |
| Usuário | utiliza | Preferências do Usuário |
| Categoria | classifica | Compromisso Financeiro |
| Compromisso Financeiro | pode possuir | Recorrência |
| Compromisso Financeiro | pode ser submetido a | Efetivação |
| Efetivação | produz | Lançamento Financeiro |
| Lançamento Financeiro | produz efeito sobre | Conta Financeira |
| Conta Financeira | possui posição financeira expressa por | Saldo |
| Contas Financeiras | compõem | Patrimônio Total |
| Transferência | movimenta valores entre | Conta Financeira de Origem e Conta Financeira de Destino |
| Período Financeiro | organiza temporalmente | Compromissos e Movimentações |

As cardinalidades, identificadores e formas definitivas de representação dessas relações pertencem à **Etapa 6 — Modelagem Conceitual (MER)** e não são determinadas neste documento.

---

## 11. Mapa Conceitual Consolidado

```text
                              USUÁRIO
                                 │
                  possui, registra e organiza
                                 │
          ┌──────────────────────┼──────────────────────┐
          │                      │                      │
          ▼                      ▼                      ▼
  CONTA FINANCEIRA      COMPROMISSO FINANCEIRO      CATEGORIA
          │                      │                      ▲
          │                      └──── organizado por ──┘
          │
          │             pode possuir RECORRÊNCIA
          │
          │             pode ser EFETIVADO
          │                      │
          │                      ▼
          │             LANÇAMENTO FINANCEIRO
          │                      │
          └──────────── produz efeito ────────────────┐
                                                      ▼
                                               SALDO DA CONTA
                                                      │
                                               soma dos saldos
                                                      │
                                                      ▼
                                              PATRIMÔNIO TOTAL


                          TRANSFERÊNCIA
                               │
                   movimenta valores entre
                               │
                ┌──────────────┴──────────────┐
                ▼                             ▼
        CONTA DE ORIGEM              CONTA DE DESTINO

             redistribui valores sem alterar
                    o PATRIMÔNIO TOTAL
```

Este mapa representa relações de negócio e não uma estrutura de banco de dados.

---

## 12. Classificação Consolidada

| Conceito / elemento | Classificação no domínio |
|---|---|
| Usuário | Conceito relevante do domínio |
| Conta Financeira | Conceito central |
| Compromisso Financeiro | Conceito central |
| Categoria | Conceito organizacional |
| Recorrência | Conceito/comportamento relevante |
| Lançamento Financeiro | Conceito central |
| Transferência | Conceito central |
| Natureza Financeira | Classificação financeira |
| Efetivação | Comportamento/transição |
| Desfazimento da Efetivação | Comportamento/transição |
| Período Financeiro | Conceito temporal |
| Saldo | Informação financeira derivada |
| Patrimônio Total | Informação financeira derivada |
| Histórico Financeiro | Visão derivada |
| Entradas Previstas | Informação derivada |
| Saídas Previstas | Informação derivada |
| Saldo de Abertura do Período | Informação derivada |
| Saldo Final Previsto | Informação derivada |
| Cartão de Crédito | Caso específico tratado por conceitos existentes |
| Parcelamento | Caso específico tratado por conceitos existentes |
| Preferências do Usuário | Elemento de apoio/configuração |
| Recuperação de Acesso | Mecanismo de apoio e segurança |
| Serviço de E-mail | Sistema externo de apoio |
| Cópia de Segurança | Mecanismo de preservação de dados |
| Restauração | Mecanismo de recuperação de dados |
| Dashboard | Visão derivada/interface |
| API REST | Arquitetura, fora do domínio financeiro |
| Interface Web | Interface, fora do domínio financeiro |

---

## 13. Pontos Registrados para Validação nas Etapas Posteriores

Este Mapa do Domínio identifica os pontos abaixo sem antecipar decisões de Modelagem Conceitual, DER, Modelo Físico ou Arquitetura.

### 13.1 Usuários e isolamento das informações

A aplicação Web poderá possuir múltiplos usuários cadastrados e exige isolamento das informações pertencentes a cada usuário.

O Mapa do Domínio mantém o conceito **Usuário** como proprietário das informações. A forma física de representação da identidade, inclusive sua relação futura com `wp_users`, pertence às etapas posteriores.

### 13.2 Remoção de Categoria

A categoria é opcional no momento do cadastro de um compromisso, e sua remoção posterior não deve excluir os compromissos anteriormente associados.

O comportamento está registrado como regra do domínio. A forma de representação dessa situação será definida na Modelagem Conceitual e etapas posteriores.

### 13.3 Desfazimento da Efetivação

O domínio exige que o desfazimento retire o efeito financeiro da movimentação e preserve a consistência das informações.

A estratégia usada para representar histórico, estados ou registros técnicos não é definida neste documento.

### 13.4 Recorrências e períodos futuros

As regras exigem que compromissos recorrentes estejam disponíveis para planejamento de períodos subsequentes, preservando períodos anteriores.

A estratégia de representação e eventual geração de ocorrências pertence às etapas posteriores.

### 13.5 PIN — versão futura

A proteção por PIN foi retirada do escopo ativo da Versão 1.

`RF-019`, `UC-018` e os respectivos critérios permanecem preservados apenas para versão futura. Nenhum conceito, entidade, atributo ou mecanismo de persistência exclusivo do PIN deverá ser levado ao MER da V1.

Quando implementado futuramente, o PIN será uma proteção secundária para bloqueio rápido do SGFP durante uma sessão já autenticada e não substituirá a autenticação principal por e-mail e senha.

### 13.6 Restauração — preservação do estado anterior

O projeto determina que os dados atuais sejam substituídos pelos dados da cópia selecionada e que não haja mesclagem na Versão 1.

A preservação do estado imediatamente anterior está definida como a geração e preservação recuperável de uma cópia de segurança pré-restauração, realizada depois da validação da cópia escolhida e da confirmação do usuário e antes da substituição dos dados atuais.

A restauração não poderá prosseguir se essa preservação falhar. A decisão resolve a ambiguidade de negócio sem definir o mecanismo técnico de armazenamento, transação ou infraestrutura, que permanece para as etapas posteriores.

---

## 14. Conceitos a Serem Analisados na Modelagem Conceitual

A Etapa 6 deverá analisar, sem pressupor o resultado, os seguintes conceitos do núcleo do domínio:

1. Usuário;
2. Conta Financeira;
3. Compromisso Financeiro;
4. Categoria;
5. Recorrência;
6. Lançamento Financeiro;
7. Transferência.

Também deverão ser considerados, quando necessário:

- Período Financeiro;
- Preferências do Usuário;
- informações necessárias à recuperação de acesso;
- informações necessárias à cópia de segurança e restauração.

Esta lista representa **entrada para análise**, não uma lista de entidades já aprovadas.

A Modelagem Conceitual deverá decidir posteriormente, conforme sua própria finalidade:

- quais conceitos necessitam identidade própria;
- quais relações precisam ser formalizadas;
- quais conceitos necessitam persistência;
- quais informações permanecem derivadas;
- quais elementos pertencem apenas a comportamento, classificação ou apoio.

---

## 15. Conceitos que Não Devem se Tornar Entidades Automaticamente

Com base nas decisões atuais do projeto, não devem ser transformados automaticamente em entidades independentes apenas por aparecerem no sistema:

- Saldo;
- Patrimônio Total;
- Histórico Financeiro;
- Entradas Previstas;
- Saídas Previstas;
- Saldo de Abertura do Período;
- Saldo Final Previsto;
- Entrada;
- Saída;
- Efetivação;
- Desfazimento;
- Cartão de Crédito;
- Parcelamento;
- Dashboard;
- API REST;
- Interface Web.

Esses elementos representam informações derivadas, classificações, comportamentos, formas de utilização ou elementos externos ao núcleo financeiro.

A decisão definitiva sobre representação pertence às etapas posteriores.

---

## 16. Síntese do Domínio

A progressão conceitual central do SGFP pode ser compreendida da seguinte forma:

**Usuário → Contas / Categorias → Compromissos → Efetivação → Lançamentos → Saldos e Patrimônio derivados**

As **Recorrências** organizam a repetição temporal de Compromissos Financeiros.

As **Transferências** movimentam valores entre contas próprias, produzindo efeitos nas contas envolvidas sem alterar o Patrimônio Total.

O valor existente na Conta Principal no início da utilização do sistema é representado por um **Lançamento Financeiro de Entrada**, quando necessário. Uma Conta Secundária recebe valor inicial por **Transferência** com a Conta Principal. Não existe “Saldo Inicial” armazenado como atributo independente da conta.

Cartão de Crédito e Parcelamento utilizam mecanismos já existentes do domínio na V1.

Dashboard e Histórico Financeiro representam formas de consulta e apresentação de informações existentes.

Preferências, recuperação de acesso, cópia de segurança e restauração apoiam a utilização e proteção do sistema, sem constituírem o núcleo financeiro.

Com a validação deste Mapa do Domínio, os conceitos identificados tornam-se entrada para a **Etapa 6 — Modelagem Conceitual (MER)**, sem antecipar as decisões próprias dessa etapa.

---

## 17. Histórico de atualização

### Versão 1.3 — 30/08/2026

Alinhamento da Cópia de Segurança e da Restauração à decisão que resolveu a ISSUE-007: a V1 passa a reconhecer a cópia automática pré-restauração como proteção do estado imediatamente anterior, condicionando a substituição dos dados à preservação recuperável bem-sucedida, sem antecipar mecanismo técnico de implementação.

### Versão 1.2 — 30/08/2026

Correção de rastreabilidade do módulo Compromissos para o identificador global `CMP-RN-*` definido no índice de regras de negócio e alinhamento dos elementos de apoio Serviço de E-mail, Cópia de Segurança e Restauração às regras vigentes de backup. A atualização não altera o núcleo conceitual validado na Etapa 5 nem antecipa decisões do MER.
