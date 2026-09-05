# SGFP — Instruções para Agentes

Antes de qualquer tarefa neste repositório:

1. Leia `ORCHESTRATOR.md`.
2. Leia `project-manifest.yaml`.
3. Leia `docs/governanca/continuidade-de-contexto.md`.
4. Consulte somente os documentos necessários em `docs/`.
5. Para tarefas de modelagem, use `docs/dominio/01-mapa-de-dominio.md` como entrada da Etapa 5 já validada, sem tratá-lo como substituto das regras de negócio, requisitos ou Casos de Uso.
6. Para tarefas que envolvam processo, sequência de etapas ou gates, consulte `docs/projeto/plano-de-desenvolvimento.md`.
7. Para preparação técnica ou tarefas das Etapas 8 a 12, consulte `docs/projeto/roteiro-tecnico-de-implementacao.md` somente como guia auxiliar; ele não substitui fontes canônicas, decisões da etapa correspondente nem autoriza antecipar gates.
8. Respeite os gates definidos no projeto.
9. Não resolva silenciosamente conflitos de negócio.
10. Não antecipe MER, DER, Modelo Físico, arquitetura ou implementação além da etapa autorizada.
11. Preserve identificadores e rastreabilidade.
12. Não altere regras de negócio sem base documental ou decisão humana.
13. Não transforme ferramentas, sistema operacional, editor ou extensões de desenvolvimento em restrições do produto sem decisão formal.
14. Trate `docs/modelagem-dados/` e seus artefatos validados como referência oficial das Etapas 6 a 8; SQLs, modelos ou estruturas auxiliares não devem substituir esses artefatos.
15. Para alterações relevantes, utilize branch ou worktree isolada.
16. Antes de concluir, valide consistência documental e Git.

## Estado resumido

- Catálogo funcional preservado: `RF-001` a `RF-021`.
- Baseline ativa da V1: 20 requisitos funcionais; `RF-019` (proteção por PIN) está adiado para versão futura e não deve provocar renumeração dos itens posteriores.
- `UC-018` e `CA-019.1` a `CA-019.5` permanecem igualmente preservados como artefatos futuros.
- Requisitos não funcionais padronizados como `RNF-001` a `RNF-020`.
- Etapa 5 — Mapa do Domínio: concluída e validada.
- Etapas 6 — Modelagem Conceitual (MER), 7 — DER e 8 — Modelo Físico: concluídas e validadas.
- Próxima etapa autorizada: Etapa 9 — Arquitetura da Aplicação, pronta para iniciar.
- A associação de Categoria ao Compromisso Financeiro é opcional na V1; o compromisso pode ser cadastrado e permanecer sem categoria.
- Restrição tecnológica consolidada para a V1: aplicação Web em PHP sobre WordPress, com backend específico em plugin próprio, infraestrutura REST do WordPress e banco relacional compatível com MySQL ou MariaDB.
- Sistema operacional, editor e extensões de desenvolvimento não constituem restrições do SGFP, salvo exigência formal posterior.
- Ferramentas como Composer, clientes HTTP para API, PHPUnit e utilitários de qualidade podem ser adotadas conforme a necessidade e a etapa autorizada; sua presença no roteiro técnico não as transforma em requisitos do produto.
- A proteção básica das operações e recursos da API contra acesso não autorizado faz parte da V1; mecanismos técnicos concretos pertencem às etapas de Arquitetura e Desenvolvimento da API.
- A cópia de segurança ordinária da V1 é criada manualmente e enviada ao e-mail cadastrado. Antes de uma restauração confirmada, o SGFP deverá gerar e preservar em condição recuperável uma cópia automática do estado imediatamente anterior; se essa preservação falhar, a restauração será cancelada. Essa proteção pontual não caracteriza backup automático periódico ou contínuo.
- `ISSUE-007`: resolvida pela decisão de cópia de segurança automática pré-restauração, sem antecipar o mecanismo técnico de preservação.
- `ISSUE-008`: a matriz direta Regra de Negócio → Requisito ainda não foi materializada; não bloqueia o início da Etapa 9, mas deverá ser concluída antes do fechamento da rastreabilidade de testes.
- MER, DER e Modelo Físico estão validados; a Arquitetura é a próxima etapa autorizada e a implementação da API continua condicionada à conclusão da Etapa 9.
- Testes e verificações podem acompanhar o desenvolvimento quando a etapa correspondente estiver autorizada; a Etapa 12 permanece responsável pela consolidação formal da estratégia, casos, evidências, rastreabilidade e resultados de teste.

As regras operacionais completas deste projeto estão em `ORCHESTRATOR.md`.
