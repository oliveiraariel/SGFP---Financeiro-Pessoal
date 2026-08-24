# Módulo 2 — Contas

## Objetivo do Módulo

Definir como o sistema representará as contas financeiras do usuário, estabelecendo regras para criação, movimentação, saldo, transferências e patrimônio.

## Regras de Negócio

### RN-001

Na primeira utilização do sistema não existirão contas financeiras cadastradas.

### RN-002

O patrimônio inicial do usuário será igual a R$ 0,00.

### RN-003

As contas financeiras serão criadas manualmente pelo usuário.

### RN-004

Todas as movimentações financeiras serão registradas manualmente pelo usuário.

### RN-005

Ao criar uma conta, ela será criada sem saldo.

Caso o usuário deseje informar um valor inicial, deverá registrar um lançamento de entrada representando o saldo inicial da conta.

### RN-006

O saldo de uma conta será sempre calculado a partir dos lançamentos registrados nela.

O sistema não armazenará saldo inicial como atributo da conta.

### RN-007

O usuário poderá criar quantas contas financeiras desejar.

### RN-008

Cada conta possuirá apenas um nome definido pelo usuário.

O sistema não exigirá informações como banco, agência, número da conta ou tipo da conta.

### RN-009

O nome de uma conta poderá ser alterado a qualquer momento.

Essa alteração não modificará os lançamentos já existentes nem o histórico financeiro da conta.

### RN-010

O sistema será utilizado como ferramenta de controle financeiro pessoal.

As informações cadastrais da instituição financeira não serão obrigatórias para o funcionamento da aplicação.

## Funcionalidades da Versão 1

- Criar contas financeiras.
- Renomear contas existentes.
- Manter múltiplas contas financeiras.
- Registrar movimentações em cada conta.
- Calcular automaticamente o saldo de cada conta.
- Calcular automaticamente o patrimônio total do usuário.
- Registrar o saldo inicial por meio de um lançamento de entrada.

## Funcionalidades Previstas para Versões Futuras

- Cadastro opcional de informações bancárias, como instituição financeira, agência e número da conta.
- Associação de logotipos das instituições financeiras.
- Integração com serviços bancários, caso seja definida em futuras versões do projeto.

## Decisões Tomadas

- O sistema iniciará sem contas financeiras cadastradas.
- O usuário criará manualmente todas as suas contas.
- O saldo inicial será registrado como um lançamento de entrada.
- O saldo de cada conta será sempre calculado automaticamente a partir dos lançamentos registrados.
- O usuário poderá criar quantas contas desejar.
- O nome das contas poderá ser alterado sem comprometer o histórico financeiro.
- O sistema não dependerá de informações bancárias para realizar o controle financeiro.

## Questões Pendentes

Nenhuma.

**Data de Revisão**

13 / 08 / 2026
