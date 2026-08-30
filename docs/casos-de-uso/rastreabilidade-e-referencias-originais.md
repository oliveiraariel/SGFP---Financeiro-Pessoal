## 7. Relação entre Requisitos e Casos de Uso

A relação inicial entre requisitos funcionais e casos de uso será mantida como uma relação de rastreabilidade, sem exigir correspondência de um para um.

| Requisito | Caso(s) de Uso principal(is) |
|---|---|
| RF-001 | UC-001 |
| RF-002 | UC-002 |
| RF-003 | UC-003, UC-004 |
| RF-004 | UC-005 |
| RF-005 | UC-005, UC-006, UC-022 |
| RF-006 | UC-007, UC-011 |
| RF-007 | UC-009, UC-010 |
| RF-008 | UC-008, UC-015 |
| RF-009 | UC-007, UC-011 |
| RF-010 | UC-006, UC-009, UC-010, UC-012, UC-017 |
| RF-011 | UC-012, UC-017 |
| RF-012 | UC-013 |
| RF-013 | UC-013 |
| RF-014 | UC-008 |
| RF-015 | UC-014 |
| RF-016 | UC-008, UC-015 |
| RF-017 | UC-016 |
| RF-018 | UC-012, UC-016, UC-017 |
| RF-019 — **Versão futura** | UC-018 — **Versão futura** |
| RF-020 | UC-019 |
| RF-021 | UC-020, UC-021 |

Os vínculos preservam os identificadores RF-001 a RF-021. Na Versão 1 permanecem ativos 20 requisitos; RF-019 e UC-018 foram adiados para versão futura sem renumeração dos identificadores posteriores. A existência histórica do catálogo alternativo de 25 RFs permanece registrada no relatório de consolidação e na proveniência da governança.

## 8. Critérios para a Próxima Etapa

Após a validação desta etapa, os casos de uso deverão servir de entrada para:

* refinamento da rastreabilidade dos requisitos;
* análise dos conceitos e responsabilidades do domínio;
* construção do Mapa do Domínio;
* identificação posterior dos elementos necessários à modelagem.

Nenhum caso de uso deverá ser interpretado como definição de entidade, tabela, classe ou método.

## 9. Referências Técnicas

A modelagem dos casos de uso e a utilização dos conceitos de ator, caso de uso e relacionamentos seguem como referência a especificação oficial do **OMG Unified Modeling Language (UML) 2.5.1**. A especificação UML é a referência normativa para a representação dos elementos e relacionamentos utilizados na modelagem UML.

A organização dos requisitos e sua relação com os casos de uso considera também os princípios de engenharia de requisitos da **ISO/IEC/IEEE 29148:2018**, que define processos, itens de informação e orientações para engenharia de requisitos. A norma permanece vigente e foi revisada e confirmada em 2024.

Referências:

* OMG. *Unified Modeling Language (UML), Version 2.5.1*. https://www.omg.org/spec/UML/2.5.1/
* ISO. *ISO/IEC/IEEE 29148:2018 — Systems and software engineering — Life cycle processes — Requirements engineering*. https://www.iso.org/standard/72089.html

## 10. Histórico de Atualização

### Versão 1.0 — 23/08/2026

Primeira consolidação dos Casos de Uso do SGFP.

Principais conteúdos:

* definição dos atores;
* definição dos objetivos dos casos de uso;
* identificação dos casos de uso da Versão 1;
* especificação dos fluxos principais;
* identificação de fluxos alternativos e exceções;
* identificação de pré-condições e pós-condições;
* relação inicial entre requisitos funcionais e casos de uso;
* preparação para a etapa de definição e modelagem do domínio.

### Nota de consistência

A revisão dos Casos de Uso não introduziu exclusão de contas como capacidade do sistema, pois essa funcionalidade não foi definida no Levantamento de Requisitos do módulo Contas. O caso de uso permanece limitado a criação, consulta e renomeação de contas.
