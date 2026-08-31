# **REQUISITOS NÃO FUNCIONAIS**

**Documento:** Especificação de Requisitos de Software (ERS)

**Versão:** 1.1

## **RNF-001 — Segurança**

O sistema deverá proteger o acesso às funcionalidades e aos dados dos usuários, impedindo acesso ou utilização não autorizados.

## **RNF-002 — Proteção das Credenciais**

As credenciais de acesso dos usuários deverão ser armazenadas de forma segura, não sendo mantidas em formato que permita sua recuperação direta.

## **RNF-003 — Isolamento dos Dados dos Usuários**

Os dados pertencentes a um usuário não poderão ser acessados ou manipulados por outro usuário.

## **RNF-004 — Proteção dos Dados**

Os dados pessoais e financeiros armazenados pelo sistema deverão receber proteção adequada contra acesso, alteração ou exposição não autorizados.

## **RNF-005 — Integridade dos Dados Financeiros**

O sistema deverá preservar a consistência dos dados financeiros, evitando informações contraditórias entre compromissos, lançamentos, contas, saldos e demais registros relacionados.

## **RNF-006 — Consistência das Operações**

Operações que produzam alterações relacionadas entre diferentes registros deverão manter esses registros consistentes entre si.

Essa exigência é especialmente relevante para efetivações, desfazimentos de efetivação e transferências.

## **RNF-007 — Preservação do Histórico Financeiro**

O sistema deverá preservar os registros financeiros já realizados, evitando que alterações posteriores modifiquem indevidamente informações pertencentes a períodos anteriores.

## **RNF-008 — Confiabilidade dos Cálculos Financeiros**

Os cálculos relacionados a saldos, entradas, saídas, transferências e demais valores financeiros deverão produzir resultados consistentes a partir dos dados registrados no sistema.

## **RNF-009 — Integridade dos Valores Monetários**

O sistema deverá tratar os valores monetários de forma a evitar erros de precisão que possam comprometer os cálculos financeiros.

## **RNF-010 — Desempenho**

O sistema deverá apresentar desempenho adequado à utilização das funcionalidades previstas, evitando tempos de resposta que prejudiquem a experiência do usuário.

Os critérios quantitativos de desempenho serão definidos posteriormente durante a especificação técnica e arquitetura do sistema.

## **RNF-011 — Disponibilidade**

O sistema deverá permanecer disponível para utilização dos usuários dentro das condições operacionais definidas para sua infraestrutura.

A meta quantitativa de disponibilidade será definida posteriormente durante a especificação técnica e arquitetura do sistema.

## **RNF-012 — Usabilidade**

O sistema deverá apresentar uma interface compreensível e consistente, permitindo que o usuário realize as operações previstas sem necessidade de conhecimento técnico sobre o funcionamento interno da aplicação.

## **RNF-013 — Responsividade**

A interface deverá adaptar sua apresentação aos diferentes tamanhos de tela suportados pelo sistema, proporcionando utilização adequada em computadores, tablets e dispositivos móveis.

Os critérios técnicos de responsividade serão definidos posteriormente durante a especificação da interface.

## **RNF-014 — Compatibilidade**

O sistema deverá funcionar adequadamente nos navegadores e ambientes definidos como suportados para a Versão 1.

A definição dos navegadores, versões e ambientes suportados será realizada posteriormente.

## **RNF-015 — Manutenibilidade**

O sistema deverá possuir estrutura que permita sua manutenção, correção de problemas e evolução futura sem alterações desnecessariamente amplas nas funcionalidades existentes.

Os critérios técnicos de implementação relacionados à manutenibilidade serão definidos posteriormente na etapa de arquitetura e desenvolvimento.

## **RNF-016 — Escalabilidade**

O sistema deverá possuir estrutura que permita sua evolução conforme o crescimento da quantidade de usuários, dados e funcionalidades.

Os critérios quantitativos de capacidade e crescimento serão definidos posteriormente, de acordo com as necessidades identificadas para a aplicação.

## **RNF-017 — Recuperação de Dados**

O sistema deverá permitir a recuperação dos dados a partir de uma cópia de segurança válida, preservando a consistência do estado restaurado.

## **RNF-018 — Proteção das Cópias de Segurança**

As cópias de segurança deverão possuir proteção adequada contra acesso ou utilização não autorizados.

A forma técnica de proteção será definida posteriormente na etapa de arquitetura e implementação.

## **RNF-019 — Privacidade**

O sistema deverá tratar os dados pessoais e financeiros dos usuários de acordo com os requisitos de privacidade aplicáveis ao sistema.

Os requisitos legais e técnicos específicos serão detalhados posteriormente nas etapas apropriadas do projeto.

## **RNF-020 — Evolutividade**

A estrutura do sistema deverá permitir a incorporação futura de novas funcionalidades sem comprometer desnecessariamente as funcionalidades existentes.

## **Pontos a serem definidos posteriormente**

Os seguintes critérios permanecem em aberto e serão definidos nas etapas técnicas apropriadas:

- Tempo máximo de resposta.
- Meta de disponibilidade.
- Navegadores suportados.
- Versões dos navegadores suportados.
- Dispositivos e ambientes suportados.
- Critérios técnicos de responsividade.
- Capacidade esperada de usuários.
- Capacidade esperada de dados.
- Critérios técnicos de escalabilidade.
- Mecanismos técnicos de segurança.
- Mecanismos técnicos de proteção das cópias de segurança.

**Observação:** A ausência de valores quantitativos nesta etapa é intencional. Os requisitos já identificam as características esperadas do sistema, enquanto os critérios técnicos e métricas que dependem da arquitetura, infraestrutura ou implementação serão definidos posteriormente.

## **Histórico de atualização**

### **Versão 1.1 — 30/08/2026**

Padronização dos identificadores dos requisitos não funcionais no formato `RNF-001` a `RNF-020`, alinhando o documento canônico aos Critérios de Aceitação e à matriz de Rastreabilidade, sem alteração do conteúdo normativo dos requisitos.
