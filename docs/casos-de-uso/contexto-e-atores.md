# ESPECIFICAÇÃO DE CASOS DE USO

## Sistema de Gestão Financeira Pessoal

**Sigla:** SGFP
**Documento:** Casos de Uso
**Versão:** 1.1
**Etapa:** 4 — Casos de Uso
**Data:** 23/08/2026

## 1. Objetivo do Documento

Este documento especifica os casos de uso do SGFP, descrevendo como os atores interagem com o sistema para alcançar objetivos relacionados à gestão financeira pessoal.

Os casos de uso utilizam como fontes de referência:

* o Documento de Visão;
* o Levantamento de Requisitos;
* a ERS — Especificação de Requisitos de Software;
* as regras de negócio consolidadas na Etapa 2.

Os casos de uso não definem a implementação técnica do sistema nem determinam, por si só, a estrutura das entidades do domínio ou do banco de dados.

## 2. Conceitos Utilizados

### 2.1 Ator

Ator representa um papel desempenhado por uma pessoa ou sistema externo que interage com o SGFP.

### 2.2 Caso de Uso

Caso de uso representa um objetivo que um ator busca alcançar por meio do sistema.

Um caso de uso poderá envolver diversos requisitos funcionais e diversas entidades do domínio.

A relação entre requisito funcional e caso de uso não será necessariamente de um para um.

### 2.3 Fluxo Principal

Representa a sequência normal de interações necessária para atingir o objetivo do caso de uso.

### 2.4 Fluxos Alternativos

Representam variações válidas do fluxo principal.

### 2.5 Exceções

Representam situações em que o objetivo não pode ser concluído conforme o fluxo principal.

## 3. Atores

### ACT-01 — Usuário

Pessoa que utiliza o SGFP para realizar seu próprio controle financeiro.

O usuário será o ator principal da Versão 1.

### ACT-02 — Serviço de E-mail

Serviço responsável pela entrega das comunicações utilizadas no mecanismo de recuperação de senha.

Este ator representa uma dependência de comunicação necessária a determinadas funcionalidades e não constitui uma integração financeira externa.

## 4. Visão Geral dos Casos de Uso

| Código | Caso de Uso | Ator principal | Objetivo |
|---|---|---|---|
| UC-001 | Cadastrar usuário | Usuário | Criar o acesso inicial ao sistema |
| UC-002 | Autenticar usuário | Usuário | Acessar o sistema mediante credenciais |
| UC-003 | Alterar senha | Usuário | Modificar a senha atual |
| UC-004 | Recuperar senha | Usuário | Recuperar o acesso mediante e-mail |
| UC-005 | Gerenciar contas financeiras | Usuário | Criar, consultar e renomear contas |
| UC-006 | Informar saldo inicial | Usuário | Registrar o saldo real inicial da conta principal |
| UC-007 | Gerenciar compromissos financeiros | Usuário | Cadastrar, consultar, alterar e excluir compromissos |
| UC-008 | Configurar recorrência | Usuário | Definir a recorrência mensal de uma operação |
| UC-009 | Efetivar compromisso financeiro | Usuário | Confirmar que a movimentação ocorreu |
| UC-010 | Desfazer efetivação | Usuário | Reverter a efetivação de um compromisso |
| UC-011 | Gerenciar categorias | Usuário | Criar, alterar, excluir e associar categorias |
| UC-012 | Consultar movimentações financeiras | Usuário | Consultar lançamentos e histórico financeiro |
| UC-013 | Gerenciar transferências | Usuário | Criar e administrar transferências entre contas |
| UC-014 | Gerenciar cartão de crédito | Usuário | Controlar compromissos referentes a faturas |
| UC-015 | Gerenciar parcelamentos | Usuário | Representar e controlar compromissos parcelados |
| UC-016 | Consultar Dashboard financeiro | Usuário | Visualizar a situação financeira do período |
| UC-017 | Navegar entre períodos financeiros | Usuário | Consultar diferentes meses |
| UC-018 | Gerenciar proteção por PIN — **Versão futura** | Usuário | Configurar e utilizar a proteção opcional por PIN em versão futura |
| UC-019 | Gerenciar tema da aplicação | Usuário | Alterar a apresentação visual |
| UC-020 | Criar cópia de segurança | Usuário | Gerar uma cópia dos dados |
| UC-021 | Restaurar cópia de segurança | Usuário | Restaurar o estado a partir de uma cópia |
| UC-022 | Consultar patrimônio total | Usuário | Visualizar o patrimônio total calculado |

## 5. Regras Gerais para os Casos de Uso

### 5.1 Autenticação

Os casos de uso que alteram ou consultam dados financeiros deverão pressupor que o usuário esteja autenticado, salvo quando o próprio caso de uso tratar da autenticação ou recuperação de acesso.

### 5.2 Dados do Usuário

Cada operação deverá respeitar o isolamento dos dados pertencentes ao usuário autenticado.

### 5.3 Regras de Negócio

As regras de negócio não serão reproduzidas integralmente neste documento.

Quando um fluxo depender de uma regra específica, será feita referência ao módulo correspondente do Levantamento de Requisitos.

### 5.4 Estado Financeiro

As operações que alterarem compromissos, efetivações, lançamentos ou transferências deverão produzir os efeitos financeiros definidos pelas regras de negócio.

## 6. Especificação dos Casos de Uso
