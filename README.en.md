# SGFP — Personal Financial Management System

[Português (Brasil)](README.md) | **English**

**SGFP — Sistema de Gestão Financeira Pessoal** is an academic web application for personal financial organization and tracking.

The project is developed incrementally, with prior documentation of business rules, requirements, use cases, domain concepts, data modeling, architecture, implementation, and tests.

## Current V1 baseline

The active V1 baseline adopts:

- exactly one Financial Account per user;
- automatic provisioning of the account named **Minha Conta** ("My Account");
- balance derived from active Financial Entries rather than stored directly;
- financial commitments for income and expenses;
- categories;
- monthly recurrence;
- settlement of commitments into financial entries;
- local ZIP backup and file-based restore;
- financial profile reset while preserving the WordPress login;
- definitive access/account deletion;
- WordPress authentication by email and password.

Transfers and Total Net Worth are outside the active V1 scope. Optional PIN protection is also postponed to a future version.

The canonical baseline is documented in:

`docs/governanca/baseline-v1-simplificada-2026-09-14.md`

## Technology

The V1 is a web application built on:

- WordPress;
- PHP;
- a dedicated SGFP plugin;
- WordPress REST infrastructure with SGFP REST endpoints;
- MySQL or MariaDB.

## Development stages

The project follows these stages:

1. Vision Document
2. Requirements Elicitation
3. Requirements Specification
4. Use Cases
5. Domain Map
6. Conceptual Data Modeling
7. Entity-Relationship Model
8. Physical Data Model
9. Application Architecture
10. API Development
11. Web Interface Development
12. Tests

Stages 9 and 10 have already been reconciled with the simplified V1 baseline. Stage 11 — Web Interface Development — is currently in progress and still needs final alignment with the reconciled backend.

## Repository structure

```text
docs/projeto/
docs/requisitos/
docs/casos-de-uso/
docs/dominio/
docs/modelagem-dados/
docs/arquitetura/
docs/api/
docs/interface-web/
docs/testes/
docs/governanca/
```

## Source of truth

`project-manifest.yaml` identifies the canonical project sources.

`ORCHESTRATOR.md` defines repository guidance for agents and orchestration systems.

Agents must not silently resolve conflicting requirements, business rules, or scope decisions.

## Academic context

The project is developed in a FATEC academic context and involves software engineering, systems analysis, data modeling, databases, software architecture, API development, web development, and software testing.

## Portuguese documentation

The full project documentation is maintained primarily in Portuguese because the project is academic and its domain documentation is produced for a Brazilian context.

For the complete project overview, requirements, governance, and current development status, see [README.md](README.md).
