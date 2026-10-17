# SGFP — Registro da versão estável 1.0.15

**Data:** 17/10/2026  
**Tipo:** registro de publicação e escopo de validação; não é homologação completa.

## Publicação

- Branch de desenvolvimento consolidada: `fix/dashboard-evolution-chart`.
- Commit que registra o código da versão estável: `cac410f0db52d3b5b1c57f77a77d09eb48d0abbd`.
- Commit seguinte que adiciona o harness visual: `c76ee1d1cefe460d6394ced3fd39c9e946830342`.
- `main` e tag `v1.0.15` apontavam para `c76ee1d1` no momento da publicação.
- Release oficial: [SGFP 1.0.15 — Versão Estável](https://github.com/oliveiraariel/SGFP---Financeiro-Pessoal/releases/tag/v1.0.15).
- Versão declarada em `src/sgfp.php`: `1.0.15`.

## Anexos da Release

| Arquivo | Tamanho | SHA-256 calculado localmente na preparação |
| --- | ---: | --- |
| `SGFP-Plugin-1.0.15.zip` | 143253 bytes | `d801139a8719355f7bd9c0890730241a0c21b91ff1d4f52fdb2d57712007751d` |
| `SGFP-Codigo-Fonte-1.0.15.zip` | 1011058 bytes | `e82692f692c4176582306081694dabd59e77b3f1fecb40eae27855ac750d76f0` |

O GitHub confirmou anexos com esses nomes e tamanhos. A comparação criptográfica dos ZIPs remotos com os hashes locais não foi repetida nesta auditoria.

## Verificações informadas

- O responsável confirmou instalação do plugin no WordPress e estabilidade nos cenários manuais verificados.
- Foram comparados 104 arquivos PHP, JavaScript e CSS entre o código local e o ZIP instalável, sem diferença encontrada; a integridade dos arquivos ZIP foi verificada.
- O repositório mantém testes em `src/src/Tests/` e o harness visual em `harness/chart/`.

**Limites:** o relato de estabilidade não comprova todos os requisitos nem a execução de todos os testes de integração, segurança, acessibilidade, E2E WordPress/MySQL ou encerramento acadêmico da Etapa 12.

## Conformidade documental pendente

- A restrição de login exclusivamente por e-mail não foi demonstrada no fluxo `wp-login.php` da v1.0.15.
- `POST /onboarding` não é rota implementada nessa versão.
- `sgfp_access` permanece referência legada, não credencial de acesso financeiro; a autorização usa `use_sgfp` com `_sgfp_provisioned`.

As decisões foram reconciliadas em [Identidade e Acesso V1](decisao-identidade-acesso-v1-2026-09-16.md). Arquivos de acompanhamento anteriores preservam seu valor histórico, mas não descrevem automaticamente esta release.

## Preservação

Não reescrever a tag `v1.0.15` nem alterar os ZIPs publicados. Atualizações exclusivamente documentais podem evoluir na `main` sem mudar o artefato estável. Alterações funcionais futuras exigem versionamento e validação próprios.
