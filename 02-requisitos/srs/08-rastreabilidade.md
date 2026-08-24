> **Status de consolidação: REVISÃO NECESSÁRIA.**  
> Este arquivo foi produzido com um catálogo alternativo de **25 requisitos funcionais**, enquanto o catálogo atualmente consolidado em `03-requisitos-funcionais.md` possui **19 RFs**. O conteúdo foi preservado para não perder trabalho, mas seus vínculos RF/UC/CA não devem ser tratados como definitivos até a realinhação dos identificadores.

# RASTREABILIDADE DOS REQUISITOS

## Sistema de Gestão Financeira Pessoal

**Sigla:** SGFP

**Documento:** Especificação de Requisitos de Software (ERS)

**Versão:** 1.0

**Subetapa:** Etapa 3 — Especificação de Requisitos

**Data:** 23/08/2026

## 1. Objetivo

Estabelecer a relação entre os requisitos especificados no SRS, os casos de uso e os critérios de aceitação correspondentes.

A rastreabilidade permitirá acompanhar a origem e a evolução de cada requisito até sua futura verificação nos testes.

A matriz será ampliada nas etapas de implementação e testes, quando os requisitos puderem ser relacionados aos componentes implementados e aos casos de teste executados.

## 2. Estrutura da Rastreabilidade

A estrutura adotada para o projeto será:

**Regra de Negócio → Requisito → Caso de Uso → Critério de Aceitação → Caso de Teste → Resultado**

As Regras de Negócio permanecem no Levantamento de Requisitos e não serão duplicadas neste documento.

Os casos de teste e seus resultados serão vinculados posteriormente, durante a Etapa 12 — Testes.

## 3. Matriz de Rastreabilidade dos Requisitos Funcionais

| Requisito | Caso de Uso | Critérios de Aceitação |
| --- | --- | --- |
| RF-001 | UC-001 | CA-001.1 a CA-001.4 |
| RF-002 | UC-002 | CA-002.1 a CA-002.4 |
| RF-003 | UC-003, UC-004 | CA-003.1 a CA-003.5 |
| RF-004 | UC-005 | CA-004.1 a CA-004.6 |
| RF-005 | UC-005, UC-009, UC-010, UC-013 | CA-005.1 a CA-005.6 |
| RF-006 | UC-006 | CA-006.1 a CA-006.3 |
| RF-007 | UC-022 | CA-007.1 a CA-007.3 |
| RF-008 | UC-007 | CA-008.1 a CA-008.8 |
| RF-009 | UC-009, UC-010 | CA-009.1 a CA-009.8 |
| RF-010 | UC-011 | CA-010.1 a CA-010.6 |
| RF-011 | UC-007, UC-011 | CA-011.1 a CA-011.2 |
| RF-012 | UC-008 | CA-012.1 a CA-012.8 |
| RF-013 | UC-009 | CA-013.1 a CA-013.5 |
| RF-014 | UC-012 | CA-014.1 a CA-014.4 |
| RF-015 | UC-012, UC-017 | CA-015.1 a CA-015.3 |
| RF-016 | UC-013 | CA-016.1 a CA-016.7 |
| RF-017 | UC-013 | CA-017.1 a CA-017.7 |
| RF-018 | UC-008, UC-013 | CA-018.1 a CA-018.4 |
| RF-019 | UC-014 | CA-019.1 a CA-019.5 |
| RF-020 | UC-015 | CA-020.1 a CA-020.4 |
| RF-021 | UC-016 | CA-021.1 a CA-021.8 |
| RF-022 | UC-016, UC-017 | CA-022.1 a CA-022.5 |
| RF-023 | UC-018 | CA-023.1 a CA-023.9 |
| RF-024 | UC-019 | CA-024.1 a CA-024.3 |
| RF-025 | UC-020, UC-021 | CA-025.1 a CA-025.8 |

## 4. Rastreabilidade dos Requisitos Não Funcionais

Os RNFs não possuem necessariamente um Caso de Uso próprio. Sua verificação será realizada por critérios de aceitação e, quando aplicável, por testes técnicos, inspeção, análise ou demonstração.

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

As regras de negócio permanecem no documento **Levantamento de Requisitos — Etapa 2**.

Quando uma regra de negócio for relevante para um requisito, sua referência deverá ser mantida na documentação correspondente, sem duplicar seu conteúdo.

A relação conceitual será:

**RN → RF/RNF → UC ou CA**

Essa relação poderá ser ampliada na matriz de rastreabilidade quando os identificadores definitivos das regras de negócio estiverem consolidados.

## 6. Rastreabilidade Futura para Implementação e Testes

Durante as etapas posteriores serão acrescentadas, conforme aplicável:

**Requisito → Componente/Implementação → Caso de Teste → Resultado**

A matriz não deverá registrar componentes ou testes inexistentes apenas para preencher campos antecipadamente.

## 7. Verificações de Completude

Na conclusão desta subetapa:

- todos os 25 RFs possuem ao menos um Caso de Uso relacionado;
- todos os 25 RFs possuem critérios de aceitação relacionados;
- todos os 20 RNFs possuem critérios de aceitação relacionados;
- os RNFs não dependem de correspondência obrigatória com Casos de Uso;
- a relação com as Regras de Negócio permanece externa ao SRS, com referência ao documento de origem;
- os casos de teste ainda não estão vinculados, pois pertencem à Etapa 12 — Testes.

## 8. Controle de Alterações

Qualquer alteração significativa em requisito, caso de uso ou critério de aceitação deverá ser refletida nesta matriz.

A rastreabilidade deverá ser revisada sempre que ocorrer:

- criação de requisito;
- alteração de requisito;
- fusão ou divisão de requisito;
- alteração de caso de uso;
- alteração de critério de aceitação;
- alteração de escopo.

## 9. Conclusão da Subetapa

A rastreabilidade inicial da ERS está estabelecida entre:

**Requisitos Funcionais → Casos de Uso → Critérios de Aceitação**

e entre:

**Requisitos Não Funcionais → Critérios de Aceitação**

A ligação com casos de teste, implementação e resultados será realizada progressivamente nas etapas posteriores.

## 10. Histórico de Atualização

### Versão 1.0 — 23/08/2026

Primeira consolidação da subetapa **Rastreabilidade dos Requisitos** da Etapa 3 — Especificação de Requisitos.

Foram estabelecidas:

- a estrutura de rastreabilidade do projeto;
- a matriz RF → UC → Critérios de Aceitação;
- a matriz RNF → Critérios de Aceitação;
- a relação conceitual com as Regras de Negócio;
- a preparação para vínculo futuro com implementação e testes.
