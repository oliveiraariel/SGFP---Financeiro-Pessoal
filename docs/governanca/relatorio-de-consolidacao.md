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

## 3. Inconsistências detectadas

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

### 3.3 Exclusão de conta — decisão de negócio pendente

RF01 declara criação, edição, visualização **e exclusão** de contas financeiras. O módulo de Contas não define regra de exclusão, e o Caso de Uso de contas foi posteriormente limitado a criação, consulta e renomeação. A regra de exclusão de contas precisa ser confirmada antes da implementação.

### 3.4 Categoria opcional versus obrigatória — consolidado pela fonte mais recente

O módulo original de Categorias dizia que a associação era opcional. O Plano de Desenvolvimento v1.4, mais recente, determina que **a categoria é obrigatória** e que o usuário pode criar uma categoria no cadastro quando necessário.

Na árvore canônica, o módulo de Categorias foi atualizado para refletir a decisão mais recente. O conteúdo original permanece no export bruto.

### 3.5 Página Arquivos contém conteúdo de Configurações — corrigido estruturalmente

A tarefa `Arquivos` do export contém uma versão revisada do Módulo Configurações. Como esse conteúdo possui decisões mais recentes de backup, ele foi utilizado como fonte canônica de `10-configuracoes.md`.

A subetapa `12-arquivos.md` foi reconstruída apenas a partir do Plano de Desenvolvimento v1.4, que registra que anexos de imagem não pertencem à V1.

### 3.6 Saldo inicial — escopo a revisar

O módulo de Contas registra o saldo inicial por lançamento de entrada de uma conta; o SRS atual possui RF03 especificamente para o saldo inicial da **conta principal**. Recomenda-se confirmar se contas secundárias também podem receber saldo inicial por lançamento ou se essa ação fica restrita à conta principal.

### 3.7 Descrição Geral do SRS estava em formato de rascunho — corrigido

A página exportada continha frases de orientação e comentários conversacionais em vez de texto definitivo. A versão canônica foi finalizada exclusivamente com informações já presentes no Documento de Visão, Levantamento e SRS.

### 3.8 Documento de Visão continha status desatualizado — corrigido

A seção de situação atual ainda indicava fase anterior do projeto. Foi atualizada para refletir o controle exportado: Etapas 2, 3 e 4 concluídas e próxima etapa prevista como Mapa do Domínio.

## 4. Ordem recomendada antes de submissão a um orchestrator

1. Resolver o catálogo definitivo de RFs.
2. Realinhar Casos de Uso, Critérios de Aceitação e Rastreabilidade ao catálogo escolhido.
3. Confirmar a regra de exclusão de contas.
4. Confirmar o escopo do saldo inicial em contas secundárias.
5. Depois disso, congelar a Etapa 3/SRS como baseline e iniciar o Mapa do Domínio.
