# Etapa 5 — Mapa do Domínio SGFP

**Status:** baseline revisada e validada para a V1 simplificada em 14/09/2026.

## 1. Finalidade

Representar os conceitos e relações de negócio da V1 sem confundir informação derivada, comportamento ou mecanismo técnico com entidade persistente.

## 2. Núcleo do domínio

### 2.1 Usuário
Pessoa autenticada no SGFP. A identidade, senha e sessão são fornecidas pelo WordPress.

Cada Usuário possui seus próprios dados financeiros isolados.

### 2.2 Conta Financeira
Representa a única posição financeira controlada pelo usuário na V1.

Regras:
- existe **exatamente uma** Conta Financeira por Usuário;
- é criada automaticamente no cadastro/provisionamento;
- nome inicial: **Minha Conta**;
- pode ser renomeada;
- não possui papel Principal/Secundária;
- não pode ser excluída isoladamente.

### 2.3 Categoria
Organiza Compromissos Financeiros.

A associação Categoria–Compromisso é opcional. Categorias padrão são criadas no provisionamento e o usuário pode criar, renomear e excluir suas próprias categorias.

### 2.4 Recorrência
Configura a repetição mensal de Compromissos Financeiros.

### 2.5 Compromisso Financeiro
Representa uma previsão ou obrigação de **Entrada** ou **Saída**.

Criar um Compromisso **não altera o saldo**. O Compromisso produz efeito financeiro somente quando efetivado.

### 2.6 Lançamento Financeiro
Representa uma movimentação realizada.

Pode ter origem:
- `COMPROMISSO`, criado pela efetivação;
- `SALDO_INICIAL`, para representar a posição financeira existente no início da utilização.

Somente Lançamentos ativos produzem efeito sobre o saldo.

### 2.7 Saldo
Informação derivada da Conta Financeira.

**Saldo = soma dos efeitos dos Lançamentos Financeiros ativos da conta.**

Saldo não é entidade nem atributo persistido separadamente.

## 3. Comportamentos centrais

### Efetivação
`Compromisso pendente → efetivação → Lançamento ativo → efeito no saldo`.

### Desfazimento
O lançamento ativo é marcado como desfeito e deixa de participar do saldo; o histórico é preservado e o Compromisso volta ao estado compatível com edição conforme regras vigentes.

### Provisionamento inicial
`Cadastro WordPress → Minha Conta → categorias padrão → acesso ao gestor`.

O SGFP não deve considerar concluído o provisionamento de um usuário sem sua Conta Financeira única.

## 4. Apoio e configurações

### Preferências
Incluem tema e demais preferências SGFP. Reset retorna preferências aos padrões.

### Cópia de Segurança
Backup manual dos dados SGFP entregue ao usuário como ZIP baixado localmente. O conteúdo é versionado, validável e protegido.

### Restauração
Substitui integralmente os dados SGFP por um backup válido. Não há merge. Antes da substituição, uma cópia pré-restauração recuperável é obrigatória.

### Resetar Perfil Financeiro
Apaga os dados SGFP, preserva a identidade/login WordPress e reprovisiona Minha Conta + categorias padrão. Requer dupla confirmação e `RESETAR PERFIL`.

### Excluir Conta de Acesso
Apaga os dados SGFP e remove a identidade/login WordPress. Requer dupla confirmação e `EXCLUIR CONTA`.

## 5. Relações conceituais

| Origem | Relação | Destino |
| --- | --- | --- |
| Usuário | possui exatamente uma | Conta Financeira |
| Usuário | organiza | Categoria |
| Usuário | registra | Compromisso Financeiro |
| Usuário | configura | Recorrência |
| Categoria | classifica opcionalmente | Compromisso Financeiro |
| Recorrência | configura | Compromisso Financeiro |
| Compromisso Financeiro | quando efetivado origina | Lançamento Financeiro |
| Conta Financeira | registra | Lançamento Financeiro |
| Lançamento Financeiro ativo | compõe | Saldo da Conta |

## 6. Mapa consolidado

```text
                         USUÁRIO
                            │
                 possui exatamente 1
                            ▼
                    CONTA FINANCEIRA
                            │
                            │ registra
                            ▼
                 LANÇAMENTO FINANCEIRO
                            │
                     efeitos ativos
                            ▼
                     SALDO DERIVADO

USUÁRIO ── organiza ──> CATEGORIA
   │                       │
   └── registra ──> COMPROMISSO <── classifica opcionalmente
                         │
                  pode ter RECORRÊNCIA
                         │
                     efetivação
                         ▼
                 LANÇAMENTO FINANCEIRO
```

## 7. Fora do escopo ativo da V1

- múltiplas contas;
- Conta Principal / Conta Secundária;
- Transferências entre contas;
- Patrimônio Total agregado;
- PIN;
- integração bancária automática;
- armazenamento externo obrigatório de backup.

Transferência e Patrimônio permanecem registrados em artefatos históricos/identificadores futuros para rastreabilidade, mas não integram o domínio ativo da V1.

## 8. Elementos que não são entidades por si só

Não devem se tornar entidades automaticamente:
- Saldo;
- Dashboard;
- Efetivação;
- Desfazimento;
- Entrada/Saída;
- Backup;
- Restauração;
- Reset;
- Exclusão de acesso;
- API REST;
- Interface Web.

## 9. Síntese

A progressão financeira da V1 é:

**Usuário → Conta única / Categorias → Compromissos → Efetivação → Lançamentos → Saldo derivado**

A decisão de 14/09/2026 substitui a baseline anterior de múltiplas contas, Transferências e Patrimônio.
