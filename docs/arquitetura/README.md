# Etapa 9 — Arquitetura da Aplicação

Definir a organização técnica da aplicação, seus componentes, responsabilidades, dependências, integração com WordPress, estratégia de persistência e contratos da API.

**Status:** baseline validada em 11/09/2026; gate integral da Etapa 9 aprovado.

As Etapas 6 — MER, 7 — DER e 8 — Modelo Físico estão concluídas e validadas e constituem entradas técnicas para esta etapa.

A Arquitetura considera as restrições consolidadas da V1 — PHP sobre WordPress, plugin próprio, infraestrutura REST do WordPress e MySQL/MariaDB — sem transformar preferências de implementação em novas regras de negócio.

## Artefato

- [Arquitetura da Aplicação V1](01-arquitetura-da-aplicacao.md) — ciclo de vida do plugin, fronteiras de confiança, componentes, dependências, contratos REST, integração WordPress, persistência, concorrência, recorrência sem escrita em consultas, transferências, cálculos derivados, backup/restauração, observabilidade, revisões funcional e de segurança, riscos e decisões humanas registradas (`DEC-001` a `DEC-005`).

## Condição para avanço

O gate integral da Etapa 9 foi aprovado em 11/09/2026. A Etapa 10 — Desenvolvimento da API foi concluída e validada no commit `362c4b947bbfceb171c75c9a71b943d50d1cfe14`. A Etapa 11 — Interface Web permanece não iniciada.

A pendência `ISSUE-008` continua não bloqueadora para a Etapa 9, mas deverá ser resolvida antes do fechamento integral da rastreabilidade na Etapa 12.
