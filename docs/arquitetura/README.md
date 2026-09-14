# Etapa 9 — Arquitetura da Aplicação

Definir a organização técnica da aplicação, seus componentes, responsabilidades, dependências, integração com WordPress, estratégia de persistência e contratos da API.

**Status:** gate original da Etapa 9 aprovado em 11/09/2026; arquitetura documental **revisada em 14/09/2026** para a baseline simplificada.

As Etapas 6 — MER, 7 — DER e 8 — Modelo Físico foram revisadas em 14/09/2026 e constituem as entradas técnicas vigentes para a arquitetura.

A Arquitetura considera as restrições consolidadas da V1 — PHP sobre WordPress, plugin próprio, infraestrutura REST do WordPress e MySQL/MariaDB — sem transformar preferências de implementação em novas regras de negócio.

## Artefato

- [Arquitetura da Aplicação V1](01-arquitetura-da-aplicacao.md) — arquitetura V1 revisada: conta única, compromissos/lançamentos, recorrência, backup ZIP local, restauração, reset/exclusão e segurança.

## Condição para avanço

O gate original da Etapa 9 foi aprovado em 11/09/2026. A Etapa 10 foi implementada sobre a baseline anterior e agora requer reconciliação com a revisão de 14/09/2026. A Etapa 11 — Interface Web está em andamento e também requer essa reconciliação.

A pendência `ISSUE-008` continua não bloqueadora para a Etapa 9, mas deverá ser resolvida antes do fechamento integral da rastreabilidade na Etapa 12.
