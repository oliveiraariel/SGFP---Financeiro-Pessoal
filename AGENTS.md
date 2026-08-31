# SGFP — Instruções para Agentes

Antes de qualquer tarefa neste repositório:

1. Leia `ORCHESTRATOR.md`.
2. Leia `project-manifest.yaml`.
3. Leia `docs/governanca/continuidade-de-contexto.md`.
4. Consulte somente os documentos necessários em `docs/`.
5. Para tarefas de modelagem, use `docs/dominio/01-mapa-de-dominio.md` como entrada da Etapa 5 já validada, sem tratá-lo como substituto das regras de negócio ou requisitos.
6. Respeite os gates definidos no projeto.
7. Não resolva silenciosamente conflitos de negócio.
8. Não antecipe MER, DER, Modelo Físico, arquitetura ou implementação além da etapa autorizada.
9. Preserve identificadores e rastreabilidade.
10. Não altere regras de negócio sem base documental ou decisão humana.
11. Para alterações relevantes, utilize branch ou worktree isolada.
12. Antes de concluir, valide consistência documental e Git.

## Estado resumido

- Catálogo funcional preservado: `RF-001` a `RF-021`.
- Baseline ativa da V1: 20 requisitos funcionais; `RF-019` (proteção por PIN) está adiado para versão futura e não deve provocar renumeração dos itens posteriores.
- `UC-018` e `CA-019.1` a `CA-019.5` permanecem igualmente preservados como artefatos futuros.
- Requisitos não funcionais padronizados como `RNF-001` a `RNF-020`.
- Etapa 5 — Mapa do Domínio: concluída e validada.
- Etapa atual: Etapa 6 — Modelagem Conceitual (MER).
- Restrição tecnológica consolidada para a V1: aplicação Web em PHP sobre WordPress, com backend específico em plugin próprio e API REST.
- A proteção básica das operações e recursos da API contra acesso não autorizado faz parte da V1; mecanismos técnicos concretos pertencem às etapas de Arquitetura e Desenvolvimento da API.
- A cópia de segurança da V1 é criada manualmente, enviada ao e-mail cadastrado e restaurada posteriormente por meio do arquivo fornecido pelo usuário.
- `ISSUE-007`: permanece pendente a decisão humana sobre o significado operacional de preservar o estado imediatamente anterior durante uma restauração; não bloqueia o MER, mas deve ser resolvida antes da Arquitetura.
- `ISSUE-008`: a matriz direta Regra de Negócio → Requisito ainda não foi materializada; não bloqueia o MER, mas deverá ser concluída antes do fechamento da rastreabilidade de testes.
- DER, Modelo Físico, Arquitetura detalhada e Implementação permanecem condicionados aos respectivos gates.

As regras operacionais completas deste projeto estão em `ORCHESTRATOR.md`.
