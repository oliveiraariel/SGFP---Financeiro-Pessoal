# Orientação para Orchestrator / Agentes

## Ordem de leitura recomendada

1. `project-manifest.yaml`
2. `90-governanca/relatorio-de-consolidacao.md`
3. `01-projeto/plano-de-desenvolvimento.md`
4. `01-projeto/documento-de-visao.md`
5. `02-requisitos/levantamento/README.md` e módulos relacionados à tarefa
6. `02-requisitos/srs/README.md` e seções do SRS
7. `03-casos-de-uso/README.md` e casos de uso relevantes
8. documentos das etapas posteriores, quando existirem

## Precedência documental atual

- O **Plano de Desenvolvimento v1.4** define a organização vigente das etapas e documentos.
- O **Levantamento de Requisitos** é a fonte das regras de negócio.
- `02-requisitos/srs/03-requisitos-funcionais.md` é o catálogo formal atual de RFs e contém **19 RFs**.
- `02-requisitos/srs/04-requisitos-nao-funcionais.md` é o catálogo formal atual de RNFs.
- Critérios de Aceitação, Rastreabilidade e Casos de Uso possuem uma divergência conhecida: utilizam um catálogo alternativo de 25 RFs. Não inferir que esses identificadores estão aprovados como catálogo oficial.
- Conteúdo em `98-historico/` é histórico e não deve substituir documentação canônica.
- Conteúdo em `99-fonte-original/` é o export bruto do Notion e deve ser consultado apenas para auditoria, recuperação ou verificação de proveniência.

## Regra para conflitos

Quando documentos canônicos divergirem e o `relatorio-de-consolidacao.md` não registrar uma resolução explícita, não escolher silenciosamente uma versão. Registrar a divergência e solicitar decisão antes de alterar regras de negócio.

## Identificadores

As regras de negócio possuem IDs repetidos por módulo (`RN-001`, `RN-002` etc.). Para referências globais automatizadas, utilizar o campo `rule_uid` em `02-requisitos/levantamento/regras-de-negocio-index.csv`.
