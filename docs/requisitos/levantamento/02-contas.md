# Módulo 2 — Contas

## Objetivo do Módulo

Definir a Conta Financeira única do usuário e sua relação com saldo e lançamentos na V1 simplificada.

## Regras de Negócio

### RN-001 — Conta Única
Cada usuário possuirá exatamente uma Conta Financeira na V1.

### RN-002 — Criação Automática
A conta será criada automaticamente no provisionamento inicial do usuário.

### RN-003 — Nome Inicial
A conta será criada com o nome **Minha Conta**.

### RN-004 — Renomeação
O usuário poderá renomear sua Conta Financeira sem alterar lançamentos ou histórico.

### RN-005 — Saldo Derivado
A conta não armazenará saldo como atributo. Seu saldo será calculado a partir dos Lançamentos Financeiros ativos associados a ela.

### RN-006 — Posição Inicial
Enquanto não houver lançamento com efeito financeiro, o saldo derivado será R$ 0,00.

Quando o usuário precisar representar o valor real existente no início da utilização, o SGFP registrará um Lançamento Financeiro de origem `SALDO_INICIAL` na Conta Financeira.

### RN-007 — Proibição de Contas Adicionais
A V1 não permitirá criar segunda conta, conta secundária ou alterar papel de conta.

### RN-008 — Sem Exclusão Isolada
A Conta Financeira não poderá ser excluída isoladamente. Ela será removida apenas no reset total dos dados SGFP ou na exclusão da conta de acesso, sendo recriada automaticamente após o reset do perfil financeiro.

### RN-009 — Transferências Fora da V1
Transferências entre contas não fazem parte do escopo ativo da V1, pois cada usuário possui apenas uma conta.

### RN-010 — Patrimônio Total Fora da V1
A V1 não apresentará Patrimônio Total como conceito separado. Com uma única conta, o valor seria redundante com o saldo da Conta Financeira.

## Funcionalidades da Versão 1
- Provisionar automaticamente uma conta por usuário.
- Visualizar a conta.
- Renomear a conta.
- Consultar o saldo derivado dos lançamentos.
- Registrar o valor inicial por Lançamento Financeiro quando necessário.

## Funcionalidades Previstas para Versões Futuras
- Múltiplas contas.
- Transferências entre contas.
- Patrimônio total agregado entre múltiplas contas.
- Dados bancários opcionais e integrações externas.

## Decisões Tomadas
- Relação Usuário–Conta é 1:1 na V1.
- Não existem papéis PRINCIPAL/SECUNDARIA.
- Saldo permanece derivado; não existe coluna/atributo independente de saldo.
- O valor inicial é um lançamento, não atributo da conta.

**Data de Revisão:** 14/09/2026
