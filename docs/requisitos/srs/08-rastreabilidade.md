# RASTREABILIDADE DOS REQUISITOS

## Sistema de Gestão Financeira Pessoal

**Sigla:** SGFP

**Documento:** Especificação de Requisitos de Software (ERS)

**Versão:** 2.2

**Baseline:** catálogo preservado com RF-001 a RF-021; 20 requisitos ativos na V1 e RF-019 adiado para versão futura

**Subetapa:** Etapa 3 — Especificação de Requisitos

## 1. Objetivo

Estabelecer a relação entre regras de negócio, requisitos funcionais, Casos de Uso e critérios de aceitação. A ligação com implementação, casos de teste e resultados será acrescentada nas etapas correspondentes.

## 2. Estrutura

A cadeia-alvo de rastreabilidade do projeto é:

**Regra de Negócio → Requisito → Caso de Uso → Critério de Aceitação → Caso de Teste → Resultado**

As regras de negócio permanecem no Levantamento de Requisitos e não são duplicadas neste documento.

A rastreabilidade atualmente materializada de forma completa nesta matriz começa em **Requisito → Caso de Uso → Critério de Aceitação**. A ligação individual **Regra de Negócio → Requisito** ainda deverá ser consolidada em operação específica de rastreabilidade, utilizando os identificadores globais existentes em `docs/requisitos/levantamento/regras-de-negocio-index.csv`.

## 3. Matriz de Rastreabilidade dos Requisitos Funcionais

| Requisito | Caso(s) de Uso | Critérios de Aceitação |
| --- | --- | --- |
| RF-001 | UC-001 | CA-001.1 a CA-001.4 |
| RF-002 | UC-002 | CA-002.1 a CA-002.4 |
| RF-003 | UC-003, UC-004 | CA-003.1 a CA-003.5 |
| RF-004 | UC-005 | CA-004.1 a CA-004.7 |
| RF-005 | UC-005, UC-006, UC-022 | CA-005.1 a CA-005.8 |
| RF-006 | UC-007, UC-011 | CA-006.1 a CA-006.9 |
| RF-007 | UC-009, UC-010 | CA-007.1 a CA-007.8 |
| RF-008 | UC-008, UC-015 | CA-008.1 a CA-008.7 |
| RF-009 | UC-007, UC-011 | CA-009.1 a CA-009.5 |
| RF-010 | UC-006, UC-009, UC-010, UC-012, UC-017 | CA-010.1 a CA-010.9 |
| RF-011 | UC-012, UC-017 | CA-011.1 a CA-011.4 |
| RF-012 | UC-013 | CA-012.1 a CA-012.7 |
| RF-013 | UC-013 | CA-013.1 a CA-013.7 |
| RF-014 | UC-008 | CA-014.1 a CA-014.3 |
| RF-015 | UC-014 | CA-015.1 a CA-015.5 |
| RF-016 | UC-008, UC-015 | CA-016.1 a CA-016.4 |
| RF-017 | UC-016 | CA-017.1 a CA-017.6 |
| RF-018 | UC-012, UC-016, UC-017 | CA-018.1 a CA-018.5 |
| RF-019 — **Versão futura** | UC-018 — **Versão futura** | CA-019.1 a CA-019.5 — **Versão futura** |
| RF-020 | UC-019 | CA-020.1 a CA-020.3 |
| RF-021 | UC-020, UC-021 | CA-021.1 a CA-021.8 |

## 4. Rastreabilidade dos Requisitos Não Funcionais

Os RNFs não possuem necessariamente um Caso de Uso próprio. Sua verificação será realizada por critérios de aceitação e, quando aplicável, por testes técnicos, inspeção, análise ou demonstração.

Os identificadores dos RNFs são padronizados no formato `RNF-001` a `RNF-020` em todos os artefatos normativos e derivados.

| Requisito | Critérios de Aceitação |
| --- | --- |
| RNF-001 | CA-NF-001.1 a CA-NF-001.2 |
| RNF-002 | CA-NF-002.1 a CA-NF-002.2 |
| RNF-003 | CA-NF-003.1 |
| RNF-004 | CA-NF-004.1 a CA-NF-004.2 |
| RNF-005 | CA-NF-005.1 |
| RNF-006 | CA-NF-006.1 a CA-NF-006.2 |
| RNF-007 | CA-NF-007.1 |
| RNF-008 | CA-NF-008.1 |
| RNF-009 | CA-NF-009.1 |
| RNF-010 | CA-NF-010.1 |
| RNF-011 | CA-NF-011.1 |
| RNF-012 | CA-NF-012.1 a CA-NF-012.2 |
| RNF-013 | CA-NF-013.1 a CA-NF-013.2 |
| RNF-014 | CA-NF-014.1 |
| RNF-015 | CA-NF-015.1 |
| RNF-016 | CA-NF-016.1 |
| RNF-017 | CA-NF-017.1 a CA-NF-017.2 |
| RNF-018 | CA-NF-018.1 |
| RNF-019 | CA-NF-019.1 a CA-NF-019.2 |
| RNF-020 | CA-NF-020.1 |

## 5. Rastreabilidade com as Regras de Negócio

As regras de negócio permanecem nos módulos da Etapa 2 e possuem identificadores globais catalogados em `docs/requisitos/levantamento/regras-de-negocio-index.csv`.

O índice global permite localizar de forma inequívoca regras como `CTA-RN-005`, `LAN-RN-022` ou `CAT-RN-003`, mas ainda não possui uma coluna que materialize, regra por regra, os requisitos relacionados.

Portanto, a cadeia **Regra de Negócio → Requisito** está conceitualmente definida e possui identificadores estáveis dos dois lados, mas sua matriz direta ainda não foi concluída.

Essa lacuna deverá ser tratada em uma operação específica de rastreabilidade, com revisão individual das regras para evitar vínculos genéricos ou incorretos. Não deverão ser atribuídos requisitos a uma regra apenas pelo módulo em que ela aparece.

A pendência **não bloqueia a Etapa 6 — Modelagem Conceitual (MER)**, pois as regras de negócio canônicas e os requisitos permanecem disponíveis e coerentes para consulta. Entretanto, a ligação direta deverá estar concluída antes de considerar a cadeia de rastreabilidade integralmente fechada e antes da consolidação final dos casos de teste da Etapa 12.

Exemplo já consolidado semanticamente: o registro do valor inicial da conta principal por Entrada utiliza regras dos módulos Contas e Lançamentos Financeiros e sustenta RF-005 e RF-010; contas secundárias recebem o valor correspondente por transferência com a conta principal, sem saldo inicial armazenado como atributo independente.

## 6. Proveniência da reconciliação

Os artefatos derivados anteriores utilizaram um catálogo de 25 RFs. A migração para o catálogo reconciliado de 21 RFs está registrada em `docs/governanca/proveniencia.csv` e no relatório de consolidação.

Os identificadores RF-001 a RF-021 permanecem oficiais e não deverão ser renumerados sem decisão formal posterior.

A decisão de 30/08/2026 retirou o RF-019 — Gerenciar proteção por PIN do escopo ativo da Versão 1 e o preservou como requisito de versão futura. Por consequência, UC-018 e CA-019.1 a CA-019.5 também permanecem preservados como artefatos futuros, sem deslocamento dos identificadores posteriores.

## 7. Rastreabilidade futura

Nas etapas posteriores serão acrescentadas, conforme aplicável:

**Requisito → Componente/Implementação → Caso de Teste → Resultado**

A matriz não deverá registrar componentes ou testes inexistentes apenas para preencher campos antecipadamente.

A ligação direta **Regra de Negócio → Requisito** deverá ser concluída em operação própria antes de a cadeia completa ser declarada encerrada.

## 8. Verificações de completude

- o catálogo preserva 21 identificadores funcionais, RF-001 a RF-021;
- 20 requisitos funcionais permanecem ativos na Versão 1;
- RF-019 está adiado para versão futura, com UC-018 e CA-019.1 a CA-019.5 igualmente preservados como futuros;
- todos os requisitos funcionais ativos da V1 possuem ao menos um Caso de Uso relacionado;
- todos os requisitos funcionais ativos da V1 possuem critérios de aceitação relacionados;
- RF-021 possui CA-021.1 a CA-021.8, incluindo a entrega da cópia de segurança por e-mail;
- os 20 RNFs possuem critérios de aceitação relacionados;
- os RNFs utilizam o padrão de identificador `RNF-001` a `RNF-020`;
- os RNFs não dependem de correspondência obrigatória com Casos de Uso;
- a matriz direta Regra de Negócio → Requisito ainda está pendente e não deve ser tratada como concluída;
- os casos de teste ainda não estão vinculados, pois pertencem à Etapa 12 — Testes;
- não há referência normativa a RF-022, RF-023, RF-024 ou RF-025.

## 9. Histórico de atualização

### Versão 2.2 — 30/08/2026

Saneamento após auditoria documental: alinhou RF-021 a CA-021.1 até CA-021.8, explicitou a padronização dos RNFs e registrou com precisão que a matriz direta Regra de Negócio → Requisito ainda não foi materializada, evitando declarar como completa uma cadeia ainda parcial.

### Versão 2.1 — 30/08/2026

Atualizou o escopo funcional da Versão 1 após a decisão de adiar a proteção por PIN. O RF-019, UC-018 e CA-019.1 a CA-019.5 foram preservados como identificadores de versão futura, sem renumeração dos artefatos posteriores. A V1 passa a possuir 20 requisitos funcionais ativos dentro de um catálogo histórico de 21 identificadores.

### Versão 2.0 — baseline reconciliada

Reconciliou o catálogo formal anterior de 19 RFs e os artefatos derivados de 25 RFs, adotando 21 requisitos funcionais com identificadores RF-001 a RF-021. Foram atualizadas as relações com Casos de Uso e Critérios de Aceitação, preservando a proveniência da divergência anterior.

### Versão 1.0 — 23/08/2026

Primeira consolidação da subetapa de Rastreabilidade dos Requisitos, originalmente produzida com o catálogo derivado de 25 RFs.
