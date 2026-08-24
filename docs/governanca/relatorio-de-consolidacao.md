# Relatório de Consolidação e Inconsistências

## 1. Escopo da reorganização

Foram analisados **83 arquivos** do export do Notion: **73 Markdown e 10 CSV**. A nova árvore separa documentação vigente, artefatos por etapa, material histórico e o export bruto de origem.

Nenhum arquivo original foi apagado; o export completo foi preservado em `99-fonte-original/notion-export/`.

## 2. Decisões estruturais aplicadas

- A numeração vigente segue o Plano de Desenvolvimento v1.4: Documento de Visão e Plano como documentos de orientação; Etapa 2 para Levantamento de Requisitos; Etapa 3 para SRS; Etapa 4 para Casos de Uso; Etapas 5–12 para domínio, dados, arquitetura, implementação e testes.
- **Casos de Uso foi removido conceitualmente do SRS** e mantido apenas como Etapa 4.
- O arquivo consolidado de Casos de Uso foi dividido em arquivos individuais por UC para facilitar navegação humana, importação no Notion e consumo automatizado.
- UUIDs e nomes técnicos do export do Notion não são usados nos arquivos canônicos.
- O export bruto permanece disponível apenas como fonte histórica.

## 3. Inconsistências detectadas no inventário inicial

Esta seção preserva o diagnóstico histórico realizado antes da reconciliação. Os itens abaixo não representam pendências atuais; o estado e a resolução vigentes estão registrados na seção 5 e no manifesto do projeto.

### 3.1 Catálogo de RFs divergente — alta prioridade

O arquivo vigente `03-requisitos-funcionais.md` contém **19 RFs (RF01–RF19)**.

Entretanto:

- Critérios de Aceitação referencia **25 RFs**;
- Rastreabilidade referencia **25 RFs**;
- Casos de Uso foi produzido utilizando um catálogo alternativo de **25 RFs**.

Esses três artefatos foram preservados, mas marcados como **revisão necessária**. Antes de avançar para modelagem, recomenda-se decidir se o catálogo oficial continuará com 19 RFs ou se será ampliado e renumerado.

### 3.2 Cadastro, autenticação e senha não possuem RF explícito — alta prioridade

O Levantamento de Requisitos e o Documento de Visão incluem cadastro de usuário, login, recuperação e alteração de credenciais na V1. O catálogo atual de 19 RFs, porém, inicia em Contas Financeiras e não possui RF específico para essas capacidades.

O catálogo alternativo de 25 RFs usado nos Casos de Uso introduziu RFs para usuário, autenticação e senha, mas essa alteração não foi incorporada ao catálogo oficial.

### 3.3 Exclusão de conta — pendência registrada no inventário inicial

RF01 declara criação, edição, visualização **e exclusão** de contas financeiras. O módulo de Contas não define regra de exclusão, e o Caso de Uso de contas foi posteriormente limitado a criação, consulta e renomeação. A regra de exclusão de contas precisava ser confirmada antes da implementação; a decisão aplicada na reconciliação foi registrar essa operação como fora do escopo da V1.

### 3.4 Categoria opcional versus obrigatória — consolidado pela fonte mais recente

O módulo original de Categorias dizia que a associação era opcional. O Plano de Desenvolvimento v1.4, mais recente, determina que **a categoria é obrigatória** e que o usuário pode criar uma categoria no cadastro quando necessário.

Na árvore canônica, o módulo de Categorias foi atualizado para refletir a decisão mais recente. O conteúdo original permanece no export bruto.

### 3.5 Página Arquivos contém conteúdo de Configurações — corrigido estruturalmente

A tarefa `Arquivos` do export contém uma versão revisada do Módulo Configurações. Como esse conteúdo possui decisões mais recentes de backup, ele foi utilizado como fonte canônica de `10-configuracoes.md`.

A subetapa `12-arquivos.md` foi reconstruída apenas a partir do Plano de Desenvolvimento v1.4, que registra que anexos de imagem não pertencem à V1.

### 3.6 Saldo inicial — escopo registrado no inventário inicial

O módulo de Contas registra o valor inicial por lançamento de Entrada da **conta principal**; o SRS anterior possuía RF03 especificamente para esse conceito. A reconciliação confirmou que contas secundárias não recebem Entrada direta para composição inicial: seu valor chega por transferência com a conta principal.

### 3.7 Descrição Geral do SRS estava em formato de rascunho — corrigido

A página exportada continha frases de orientação e comentários conversacionais em vez de texto definitivo. A versão canônica foi finalizada exclusivamente com informações já presentes no Documento de Visão, Levantamento e SRS.

### 3.8 Documento de Visão continha status desatualizado — corrigido

A seção de situação atual ainda indicava fase anterior do projeto. Foi atualizada para refletir o controle exportado: Etapas 2, 3 e 4 concluídas e próxima etapa prevista como Mapa do Domínio.

## 4. Ordem histórica recomendada antes da reconciliação

Esta seção preserva a sequência de ações recomendada no diagnóstico inicial. As ações abaixo já foram executadas pela reconciliação registrada na seção 5 e não representam pendências atuais.

1. Resolver o catálogo definitivo de RFs.
2. Realinhar Casos de Uso, Critérios de Aceitação e Rastreabilidade ao catálogo escolhido.
3. Registrar a decisão sobre exclusão de contas e saldo inicial na baseline reconciliada.
4. Depois disso, submeter a baseline à reavaliação/liberação do gate da Etapa 5.

## 5. Reconciliação da baseline funcional V1

### 5.1 Decisão aplicada

A baseline funcional definitiva da V1 foi estabelecida com **21 Requisitos Funcionais**, identificados de `RF-001` a `RF-021`. Esses identificadores são oficiais e não devem ser renumerados sem decisão formal posterior.

Cadastro, autenticação e senha foram formalizados como RF-001, RF-002 e RF-003 porque já pertenciam ao escopo documentado da V1. Saldos e patrimônio foram consolidados em RF-005; atualização de saldos não permaneceu como RF independente; criação de categoria durante o cadastro e registro de informações de períodos anteriores foram mantidos como fluxos/capacidades relacionadas; consulta de movimentações permaneceu independente em RF-011. Backup e restauração foram consolidados em RF-021.

Exclusão de contas financeiras foi registrada como fora do escopo da V1. Não há saldo inicial armazenado como atributo: o valor inicial da conta principal é representado por lançamento de Entrada, enquanto o valor de uma conta secundária chega por transferência com a conta principal. Saldos e patrimônio são derivados dos movimentos.

### 5.2 Tabela de migração e proveniência

| Identificador anterior | Identificador definitivo | Tipo de mudança | Justificativa |
|---|---|---|---|
| RF-001 derivado | RF-001 | preservação | Cadastro de usuário já pertencia à V1. |
| RF-002 derivado | RF-002 | preservação | Autenticação já pertencia à V1. |
| RF-003 derivado | RF-003 | preservação | Alteração e recuperação de senha já pertenciam à V1. |
| RF-004 derivado / RF01 formal | RF-004 | renumeração e correção de escopo | Gerenciamento de contas permanece; exclusão foi retirada da V1. |
| RF-005 e RF-007 derivados / RF02 formal | RF-005 | consolidação | Saldos e patrimônio total são consultados a partir dos movimentos. |
| RF-006 derivado / RF03 formal | RF-010 e regras de contas | absorção | Valor inicial não é RF autônomo nem atributo; principal usa Entrada. |
| RF-008 derivado / RF04 formal | RF-006 | renumeração | Gerenciamento de compromissos permanece. |
| RF-009 derivado / RF05 formal | RF-007 | consolidação funcional | Efetivação e desfazimento passam a formar um único RF. |
| RF-012 derivado / RF06 formal | RF-008 | renumeração | Recorrência de compromissos permanece. |
| RF-010 e RF-011 derivados / RF07 formal | RF-009 | consolidação e absorção de fluxo | Categoria durante o cadastro é fluxo relacionado, não RF autônomo. |
| RF-013 derivado / RF08 formal | RF-010 | renumeração | Registro de lançamentos permanece e absorve valor histórico aplicável. |
| RF-014 derivado | RF-011 | formalização | Consulta de movimentações permanece independente. |
| RF-015 derivado | RF-010 e RF-018 | absorção | Registro histórico depende de lançamentos e navegação entre períodos. |
| RF-016 derivado / RF10 formal | RF-012 | renumeração | Gerenciamento de transferências permanece. |
| RF-017 derivado / RF11 formal | RF-013 | consolidação funcional | Efetivação e desfazimento dos efeitos da transferência formam um RF. |
| RF-018 derivado / RF12 formal | RF-014 | renumeração | Recorrência de transferências permanece. |
| RF-019 derivado / RF13 formal | RF-015 | renumeração | Compromissos de cartão permanecem. |
| RF-020 derivado / RF14 formal | RF-016 | renumeração | Parcelamentos permanecem. |
| RF-021 derivado / RF15 formal | RF-017 | renumeração | Dashboard permanece. |
| RF-022 derivado / RF16 formal | RF-018 | renumeração e absorção | Navegação absorve o acesso aos períodos aplicáveis. |
| RF-023 derivado / RF17 formal | RF-019 | renumeração | Proteção por PIN permanece. |
| RF-024 derivado / RF18 formal | RF-020 | renumeração | Tema permanece. |
| RF-025 derivado / RF19 formal | RF-021 | consolidação | Cópias de segurança e restauração ficam em um único RF. |

### 5.3 Resolução dos issues

- **ISSUE-001 — resolvido:** baseline adotada com 21 RFs e derivados realinhados.
- **ISSUE-002 — resolvido:** RF-001, RF-002 e RF-003 formalizam capacidades de usuário já presentes na V1.
- **ISSUE-003 — resolvido:** exclusão de contas financeiras foi excluída da V1 sem criação de regra de arquivamento ou inativação.
- **ISSUE-004 — resolvido:** valor inicial da principal usa Entrada; secundária recebe por transferência; não há saldo inicial armazenado; saldos e patrimônio são derivados.

Os documentos históricos não foram alterados para apagar a divergência original. O gate da Etapa 5 está apto para reavaliação/liberação posterior, mas a etapa não foi iniciada por esta reconciliação.
