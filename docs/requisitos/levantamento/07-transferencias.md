# MÓDULO 7 — TRANSFERÊNCIAS

**Status:** fora do escopo ativo da V1 a partir de 14/09/2026.

## Decisão vigente

A V1 simplificada possui **exatamente uma Conta Financeira por usuário**. Consequentemente, não existe par de contas de origem/destino capaz de sustentar uma transferência interna.

As regras históricas `TRF-RN-001` a `TRF-RN-024` ficam preservadas apenas para rastreabilidade e possível retomada em versão futura. Elas **não constituem regras ativas da V1**.

## Efeito documental

- `RF-012`, `RF-013` e `RF-014` ficam preservados como requisitos futuros/inativos.
- `UC-013` fica preservado como Caso de Uso futuro/inativo.
- A entidade/tabela `TRANSFERENCIA` sai do MER, DER e Modelo Físico ativos da V1.
- O frontend e a API não deverão expor transferências após a reconciliação da implementação.
- Compromissos financeiros da V1 possuem apenas natureza Entrada ou Saída e incidem sobre a Conta Financeira única.

## Condição para retorno

Transferências só poderão voltar ao escopo quando uma versão futura reintroduzir múltiplas contas e definir novamente suas regras.

**Data de Revisão:** 14/09/2026
