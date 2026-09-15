# REQUISITOS FUNCIONAIS

**Documento:** Especificação de Requisitos de Software (ERS)  
**Versão:** 3.0  
**Baseline:** catálogo RF-001 a RF-023; 19 requisitos ativos na V1; RF-012, RF-013, RF-014 e RF-019 preservados como futuros/inativos.

## 1. Usuários e credenciais

### RF-001 — Cadastrar usuário
O sistema deverá permitir o cadastro inicial e, após a criação válida da identidade WordPress, provisionar automaticamente a Conta Financeira única **Minha Conta** e as categorias padrão.

### RF-002 — Autenticar usuário
O sistema deverá permitir autenticação por e-mail e senha, respeitando o isolamento dos dados.

### RF-003 — Gerenciar senha
O sistema deverá permitir alteração e recuperação de senha pelos mecanismos do WordPress.

## 2. Conta e saldo

### RF-004 — Gerenciar Conta Financeira única
O sistema deverá permitir visualizar e renomear a única Conta Financeira do usuário. A conta é criada automaticamente no cadastro; a V1 não permite contas adicionais nem exclusão isolada da conta.

### RF-005 — Consultar saldo da conta
O sistema deverá calcular e apresentar o saldo da Conta Financeira a partir dos Lançamentos Financeiros ativos, sem armazenar saldo como atributo independente. Patrimônio Total não integra a V1.

## 3. Compromissos financeiros

### RF-006 — Gerenciar compromissos financeiros
O sistema deverá permitir cadastrar, consultar, alterar e excluir compromissos conforme estado e regras aplicáveis. Categoria é opcional e pode ser criada no fluxo.

### RF-007 — Efetivar e desfazer compromissos financeiros
O sistema deverá efetivar/desfazer compromissos registrando ou revertendo o Lançamento Financeiro e seu efeito sobre o saldo da Conta Financeira única.

### RF-008 — Gerenciar compromissos recorrentes
O sistema deverá permitir recorrência mensal conforme as regras vigentes.

## 4. Categorias e lançamentos

### RF-009 — Gerenciar categorias financeiras
O sistema deverá provisionar categorias padrão por usuário e permitir criar, visualizar, renomear e excluir categorias próprias, sem afetar outros usuários.

### RF-010 — Registrar lançamentos financeiros
O sistema deverá registrar os lançamentos resultantes das efetivações e, quando informado, o valor inicial da Conta Financeira por lançamento de origem `SALDO_INICIAL`.

### RF-011 — Consultar movimentações financeiras
O sistema deverá permitir consultar lançamentos e movimentações do usuário e período selecionados.

## 5. Identificadores preservados para versão futura — Transferências

### RF-012 — Gerenciar transferências entre contas — Versão Futura
Fora do escopo ativo da V1 por existir somente uma Conta Financeira por usuário.

### RF-013 — Efetivar e desfazer transferências — Versão Futura
Fora do escopo ativo da V1.

### RF-014 — Gerenciar transferências recorrentes — Versão Futura
Fora do escopo ativo da V1.

## 6. Casos específicos de compromissos

### RF-015 — Gerenciar compromissos de cartão de crédito
O pagamento de fatura poderá ser representado por compromisso de Saída.

### RF-016 — Gerenciar compromissos parcelados
Parcelamentos poderão ser representados por compromissos recorrentes.

## 7. Consulta financeira

### RF-017 — Consultar o Dashboard financeiro
O sistema deverá apresentar situação financeira do período, incluindo saldo, valores previstos e compromissos relacionados, sem Patrimônio Total separado.

### RF-018 — Navegar entre períodos financeiros
O sistema deverá permitir navegar entre meses e consultar/registrar informações conforme as regras aplicáveis.

## 8. Configurações e segurança

### RF-019 — Gerenciar proteção por PIN — Versão Futura
Preservado apenas para rastreabilidade histórica. Não integra a V1.

### RF-020 — Gerenciar tema da aplicação
O sistema deverá permitir selecionar e aplicar tema Claro/Escuro.

### RF-021 — Gerenciar cópias de segurança e restauração
O sistema deverá gerar backup manual em arquivo ZIP para download local, validar e restaurar backup por arquivo, preservar cópia pré-restauração recuperável e não depender de e-mail para entrega de backup.

### RF-022 — Resetar perfil financeiro
O sistema deverá permitir apagar todos os dados SGFP do usuário e reprovisionar o estado inicial sem excluir sua identidade/login WordPress, mediante dupla confirmação e digitação exata de **RESETAR PERFIL**.

### RF-023 — Excluir conta de acesso
O sistema deverá permitir remover definitivamente todos os dados SGFP e a identidade/login WordPress do usuário, mediante dupla confirmação e digitação exata de **EXCLUIR CONTA**.

## Lista Consolidada

| Código | Requisito | Status |
| --- | --- | --- |
| RF-001 | Cadastrar usuário | V1 |
| RF-002 | Autenticar usuário | V1 |
| RF-003 | Gerenciar senha | V1 |
| RF-004 | Gerenciar Conta Financeira única | V1 |
| RF-005 | Consultar saldo da conta | V1 |
| RF-006 | Gerenciar compromissos financeiros | V1 |
| RF-007 | Efetivar e desfazer compromissos | V1 |
| RF-008 | Gerenciar compromissos recorrentes | V1 |
| RF-009 | Gerenciar categorias | V1 |
| RF-010 | Registrar lançamentos | V1 |
| RF-011 | Consultar movimentações | V1 |
| RF-012 | Gerenciar transferências | Futuro |
| RF-013 | Efetivar/desfazer transferências | Futuro |
| RF-014 | Transferências recorrentes | Futuro |
| RF-015 | Compromissos de cartão | V1 |
| RF-016 | Compromissos parcelados | V1 |
| RF-017 | Dashboard | V1 |
| RF-018 | Navegar entre períodos | V1 |
| RF-019 | Proteção por PIN | Futuro |
| RF-020 | Tema | V1 |
| RF-021 | Backup/restauração | V1 |
| RF-022 | Resetar perfil financeiro | V1 |
| RF-023 | Excluir conta de acesso | V1 |

**Data de Revisão:** 14/09/2026
