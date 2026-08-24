# REQUISITOS FUNCIONAIS

**Documento:** Especificação de Requisitos de Software (ERS)

**Versão:** 1.0

## 1. Contas Financeiras

### **RF01 — Gerenciar contas financeiras**

O sistema deverá permitir ao usuário gerenciar suas contas financeiras, incluindo sua criação, edição, visualização e exclusão, respeitando as regras definidas para a conta principal e as contas secundárias.

### **RF02 — Gerenciar o saldo das contas**

O sistema deverá calcular e apresentar o saldo das contas com base nas movimentações financeiras efetivadas, considerando as regras aplicáveis a entradas, saídas e transferências.

### **RF03 — Gerenciar o saldo inicial da conta principal**

O sistema deverá permitir ao usuário informar o saldo existente na conta principal no início da utilização do sistema, utilizando esse valor como ponto de partida para os cálculos financeiros posteriores.

## 2. Compromissos Financeiros

### **RF04 — Gerenciar compromissos financeiros**

O sistema deverá permitir ao usuário cadastrar, visualizar, alterar e excluir compromissos financeiros, respeitando as regras aplicáveis ao seu estado e às demais características do compromisso.

### **RF05 — Gerenciar a efetivação de compromissos financeiros**

O sistema deverá permitir ao usuário efetivar e desfazer a efetivação de compromissos financeiros, registrando a movimentação correspondente e aplicando ou retirando seu efeito sobre o saldo da conta envolvida.

### **RF06 — Gerenciar compromissos recorrentes**

O sistema deverá permitir ao usuário configurar e administrar compromissos financeiros recorrentes, respeitando as regras definidas para início, duração, alteração, exclusão e encerramento das recorrências.

## 3. Categorias

### **RF07 — Gerenciar categorias financeiras**

O sistema deverá permitir ao usuário criar, visualizar, alterar e excluir categorias, bem como associá-las aos compromissos financeiros para fins de organização e agrupamento.

## 4. Lançamentos Financeiros

### **RF08 — Registrar lançamentos financeiros**

O sistema deverá registrar os lançamentos financeiros resultantes da efetivação dos compromissos, mantendo as informações necessárias para identificar e controlar a movimentação realizada.

### **RF09 — Atualizar os saldos a partir dos lançamentos**

O sistema deverá atualizar automaticamente os saldos das contas de acordo com os lançamentos financeiros efetivados e com o desfazimento de suas respectivas efetivações.

## 5. Transferências

### **RF10 — Gerenciar transferências entre contas**

O sistema deverá permitir ao usuário criar, visualizar, alterar, efetivar, desfazer e excluir transferências entre suas contas, respeitando as regras aplicáveis às contas de origem e destino.

### **RF11 — Registrar os efeitos das transferências**

O sistema deverá registrar os efeitos de uma transferência nas contas de origem e destino, mantendo os respectivos históricos consistentes e sem alterar o patrimônio total do usuário.

### **RF12 — Gerenciar transferências recorrentes**

O sistema deverá permitir ao usuário configurar e administrar transferências recorrentes, utilizando as regras definidas para recorrência.

## 6. Casos Específicos de Compromissos Financeiros

### **RF13 — Gerenciar compromissos de cartão de crédito**

O sistema deverá permitir ao usuário representar o pagamento de faturas de cartão de crédito por meio de compromissos financeiros de Saída, respeitando as regras definidas para esses compromissos.

### **RF14 — Gerenciar compromissos parcelados**

O sistema deverá permitir ao usuário representar parcelamentos por meio de compromissos financeiros recorrentes, respeitando as regras gerais de recorrência e efetivação.

## 7. Dashboard

### **RF15 — Consultar o Dashboard financeiro**

O sistema deverá apresentar ao usuário uma visão consolidada da situação financeira do mês selecionado, incluindo os valores previstos e os compromissos que os compõem.

### **RF16 — Navegar entre períodos financeiros**

O sistema deverá permitir ao usuário navegar entre diferentes meses para consultar os respectivos dados e situações financeiras.

## 8. Configurações

### **RF17 — Gerenciar proteção por PIN**

O sistema deverá permitir ao usuário configurar e administrar a proteção de acesso por PIN, incluindo sua ativação, alteração, desativação e recuperação conforme as regras estabelecidas.

### **RF18 — Gerenciar o tema da aplicação**

O sistema deverá permitir ao usuário selecionar e aplicar o tema de apresentação da aplicação.

### **RF19 — Gerenciar cópias de segurança**

O sistema deverá permitir ao usuário criar e restaurar cópias de segurança dos dados do sistema, respeitando as regras definidas para preservação, proteção e restauração dos dados.

## Lista Consolidada

| Código | Requisito Funcional |
| --- | --- |
| **RF01** | Gerenciar contas financeiras |
| **RF02** | Gerenciar o saldo das contas |
| **RF03** | Gerenciar o saldo inicial da conta principal |
| **RF04** | Gerenciar compromissos financeiros |
| **RF05** | Gerenciar a efetivação de compromissos financeiros |
| **RF06** | Gerenciar compromissos recorrentes |
| **RF07** | Gerenciar categorias financeiras |
| **RF08** | Registrar lançamentos financeiros |
| **RF09** | Atualizar os saldos a partir dos lançamentos |
| **RF10** | Gerenciar transferências entre contas |
| **RF11** | Registrar os efeitos das transferências |
| **RF12** | Gerenciar transferências recorrentes |
| **RF13** | Gerenciar compromissos de cartão de crédito |
| **RF14** | Gerenciar compromissos parcelados |
| **RF15** | Consultar o Dashboard financeiro |
| **RF16** | Navegar entre períodos financeiros |
| **RF17** | Gerenciar proteção por PIN |
| **RF18** | Gerenciar o tema da aplicação |
| **RF19** | Gerenciar cópias de segurança |
