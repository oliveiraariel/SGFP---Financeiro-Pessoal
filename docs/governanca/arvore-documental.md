# Árvore Documental Canônica

**Atualizada em:** 16/09/2026

A lista abaixo representa os principais artefatos documentais canônicos da baseline vigente. Arquivos históricos permanecem preservados no repositório, mas não prevalecem sobre estes artefatos.

```text
AGENTS.md
ORCHESTRATOR.md
README.md
project-manifest.yaml

docs/README.md

docs/projeto/
  documento-de-visao.md
  plano-de-desenvolvimento.md
  roteiro-tecnico-de-implementacao.md

docs/requisitos/levantamento/
  README.md
  01-usuarios.md
  02-contas.md
  03-compromissos.md
  04-categorias.md
  05-recorrencias.md
  06-lancamentos-financeiros.md
  07-transferencias.md              # preservado para versão futura
  08-casos-especificos-de-compromissos.md
  09-dashboard.md
  10-configuracoes.md
  11-relatorios.md
  12-arquivos.md
  13-seguranca.md
  14-integracoes.md
  regras-de-negocio-index.csv

docs/requisitos/srs/
  README.md
  01-introducao.md
  02-descricao-geral.md
  03-requisitos-funcionais.md
  04-requisitos-nao-funcionais.md
  05-regras-de-negocio-referencia.md
  06-restricoes-e-dependencias.md
  07-criterios-de-aceitacao.md
  08-rastreabilidade.md
  requisitos-index.csv

docs/casos-de-uso/
  README.md
  catalogo.csv
  contexto-e-atores.md
  rastreabilidade-e-referencias-originais.md
  casos/
    uc-001 ... uc-024
    uc-013-gerenciar-transferencias.md      # futuro/inativo
    uc-018-gerenciar-protecao-por-pin.md   # futuro/inativo
    uc-022-consultar-patrimonio-total.md   # futuro/inativo
    uc-023-resetar-perfil-financeiro.md
    uc-024-excluir-conta-acesso.md

docs/dominio/
  README.md
  01-mapa-de-dominio.md

docs/modelagem-dados/
  01-modelagem-conceitual-mer.md
  02-modelo-entidade-relacionamento-der.md
  03-modelo-fisico.md
  artefatos/modelo-fisico/sgfp-modelo-fisico-mysql.sql

docs/arquitetura/
  README.md
  01-arquitetura-da-aplicacao.md

docs/api/
  README.md

docs/interface-web/
  README.md

docs/testes/
  README.md

docs/governanca/
  baseline-v1-simplificada-2026-09-14.md
  decisao-identidade-acesso-v1-2026-09-16.md
  PROMPT-RETOMADA.md
  continuidade-de-contexto.md
  arvore-documental.md
  inventario-fonte.csv
  proveniencia.csv
  relatorio-de-consolidacao.md
```

## Precedência

Para estado vigente, a leitura deve começar por:

1. `project-manifest.yaml`;
2. `docs/governanca/baseline-v1-simplificada-2026-09-14.md`;
3. `docs/governanca/decisao-identidade-acesso-v1-2026-09-16.md` para identidade, cadastro, capability e estados de entrada;
4. `ORCHESTRATOR.md`;
5. fontes normativas da etapa afetada.

`HANDOFF.md`, o relatório de consolidação, proveniência e inventários preservam histórico e devem ser interpretados dentro desse contexto.
