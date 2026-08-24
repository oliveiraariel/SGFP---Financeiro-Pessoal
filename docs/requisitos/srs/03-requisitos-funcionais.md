# REQUISITOS FUNCIONAIS

**Documento:** Especificação de Requisitos de Software (ERS)

**Versão:** 2.0

**Baseline funcional:** V1 reconciliada

## 1. Usuários e credenciais

### **RF-001 — Cadastrar usuário**

O sistema deverá permitir o cadastro inicial do usuário, conforme as regras do módulo Usuários.

### **RF-002 — Autenticar usuário**

O sistema deverá permitir a autenticação do usuário por e-mail e senha, respeitando o isolamento dos seus dados.

### **RF-003 — Gerenciar senha**

O sistema deverá permitir a alteração e a recuperação da senha conforme as regras do módulo Usuários.

## 2. Contas, saldos e patrimônio

### **RF-004 — Gerenciar contas financeiras**

O sistema deverá permitir criar, visualizar e renomear contas financeiras, respeitando as regras da conta principal e das contas secundárias. A exclusão de contas financeiras não pertence à V1.

### **RF-005 — Consultar saldos das contas e patrimônio total**

O sistema deverá calcular e apresentar os saldos das contas e o patrimônio total a partir dos movimentos financeiros registrados, sem manter saldo inicial como atributo independente. Transferências entre contas próprias não deverão alterar o patrimônio total.

## 3. Compromissos financeiros

### **RF-006 — Gerenciar compromissos financeiros**

O sistema deverá permitir cadastrar, consultar, alterar e excluir compromissos financeiros conforme seu estado e as regras de negócio aplicáveis. A criação de categoria durante o cadastro é um fluxo relacionado a este requisito e a RF-009.

### **RF-007 — Efetivar e desfazer compromissos financeiros**

O sistema deverá permitir efetivar e desfazer a efetivação de compromissos financeiros, registrando ou revertendo o lançamento e seus efeitos sobre o saldo da conta envolvida.

### **RF-008 — Gerenciar compromissos recorrentes**

O sistema deverá permitir configurar e administrar compromissos financeiros recorrentes, respeitando as regras definidas para início, duração, alteração, exclusão e encerramento das recorrências.

## 4. Categorias e lançamentos

### **RF-009 — Gerenciar categorias financeiras**

O sistema deverá permitir criar, visualizar, alterar e excluir categorias, bem como associá-las aos compromissos financeiros. A criação de categoria durante o cadastro de um compromisso deverá permanecer disponível como fluxo relacionado.

### **RF-010 — Registrar lançamentos financeiros**

O sistema deverá registrar os lançamentos financeiros resultantes das movimentações efetivadas, incluindo o registro do valor inicial da conta principal por meio de um lançamento de Entrada quando aplicável. Informações financeiras de períodos anteriores deverão ser registradas por este mecanismo, conforme as regras existentes.

### **RF-011 — Consultar movimentações financeiras**

O sistema deverá permitir consultar os lançamentos e demais movimentações financeiras registradas, respeitando o usuário e o período selecionado.

## 5. Transferências

### **RF-012 — Gerenciar transferências entre contas**

O sistema deverá permitir criar, visualizar e alterar transferências entre contas pertencentes ao usuário, bem como excluir transferências não efetivadas, respeitando as regras aplicáveis.

### **RF-013 — Efetivar e desfazer transferências**

O sistema deverá permitir efetivar e desfazer transferências, aplicando ou revertendo os efeitos nos saldos das contas de origem e destino e mantendo seus históricos consistentes, sem alterar o patrimônio total.

### **RF-014 — Gerenciar transferências recorrentes**

O sistema deverá permitir configurar e administrar transferências recorrentes conforme as regras de recorrência.

## 6. Casos específicos de compromissos

### **RF-015 — Gerenciar compromissos de cartão de crédito**

O sistema deverá permitir representar o pagamento de faturas de cartão de crédito por meio de compromissos de Saída, respeitando as regras definidas para esses compromissos.

### **RF-016 — Gerenciar compromissos parcelados**

O sistema deverá permitir representar parcelamentos por meio de compromissos recorrentes, respeitando as regras gerais de recorrência e efetivação.

## 7. Consulta financeira

### **RF-017 — Consultar o Dashboard financeiro**

O sistema deverá apresentar uma visão consolidada da situação financeira do período selecionado, incluindo valores previstos e os compromissos que os compõem.

### **RF-018 — Navegar entre períodos financeiros**

O sistema deverá permitir navegar entre diferentes meses e consultar ou registrar informações nos períodos aplicáveis, inclusive períodos anteriores, conforme as regras existentes.

## 8. Configurações e segurança

### **RF-019 — Gerenciar proteção por PIN**

O sistema deverá permitir configurar e administrar a proteção por PIN, incluindo ativação, alteração, desativação e recuperação conforme as regras estabelecidas.

### **RF-020 — Gerenciar tema da aplicação**

O sistema deverá permitir selecionar e aplicar o tema de apresentação da aplicação.

### **RF-021 — Gerenciar cópias de segurança e restauração**

O sistema deverá permitir criar e restaurar cópias de segurança dos dados, respeitando as regras de preservação, proteção e restauração. Não ficam definidos neste requisito tecnologia, formato físico, armazenamento externo ou infraestrutura.

## Lista Consolidada

| Código | Requisito Funcional |
| --- | --- |
| **RF-001** | Cadastrar usuário |
| **RF-002** | Autenticar usuário |
| **RF-003** | Gerenciar senha |
| **RF-004** | Gerenciar contas financeiras |
| **RF-005** | Consultar saldos das contas e patrimônio total |
| **RF-006** | Gerenciar compromissos financeiros |
| **RF-007** | Efetivar e desfazer compromissos financeiros |
| **RF-008** | Gerenciar compromissos recorrentes |
| **RF-009** | Gerenciar categorias financeiras |
| **RF-010** | Registrar lançamentos financeiros |
| **RF-011** | Consultar movimentações financeiras |
| **RF-012** | Gerenciar transferências entre contas |
| **RF-013** | Efetivar e desfazer transferências |
| **RF-014** | Gerenciar transferências recorrentes |
| **RF-015** | Gerenciar compromissos de cartão de crédito |
| **RF-016** | Gerenciar compromissos parcelados |
| **RF-017** | Consultar o Dashboard financeiro |
| **RF-018** | Navegar entre períodos financeiros |
| **RF-019** | Gerenciar proteção por PIN |
| **RF-020** | Gerenciar tema da aplicação |
| **RF-021** | Gerenciar cópias de segurança e restauração |
