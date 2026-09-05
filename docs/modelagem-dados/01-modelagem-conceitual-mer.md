# Etapa 6 — Modelagem Conceitual (MER)

## Objetivo

Representar conceitualmente os dados do SGFP a partir das regras de negócio, requisitos, Casos de Uso e conceitos validados no Mapa do Domínio.

**Status:** concluída e validada em 05/09/2026.

## Resultado

O MER consolidou as entidades e os relacionamentos necessários à Versão 1, incluindo Usuário, Conta Financeira, Categoria, Recorrência, Compromisso Financeiro, Transferência e Lançamento Financeiro.

Entre as decisões refletidas no modelo:

- a associação de Categoria ao Compromisso Financeiro é opcional;
- Transferência é uma especialização de Compromisso Financeiro;
- saldos são derivados dos lançamentos financeiros;
- a modelagem considera somente o escopo ativo da V1, sem estrutura específica para o PIN futuro;
- detalhes de persistência e integração física com o WordPress não pertencem ao MER.

## Artefatos

- [MER — representação visual](artefatos/mer/sgfp-mer-conceitual.png)
- [MER — arquivo editável do brModelo](artefatos/mer/sgfp-mer-conceitual.brM3)

Os artefatos acima constituem a representação validada da Etapa 6 e servem de entrada para as etapas posteriores de modelagem de dados.
