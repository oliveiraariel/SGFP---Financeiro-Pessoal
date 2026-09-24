# SGFP — Instruções para Agentes

Antes de qualquer tarefa neste repositório:

1. Leia `ORCHESTRATOR.md`.
2. Leia `project-manifest.yaml`.
3. Leia `docs/governanca/baseline-v1-simplificada-2026-09-14.md`.
4. Leia `docs/governanca/status-implementacao-v1-2026-09-14.md`.
5. Leia `docs/governanca/continuidade-de-contexto.md`.
6. Consulte somente os documentos necessários em `docs/`.
7. Para tarefas de modelagem, use `docs/dominio/01-mapa-de-dominio.md` como entrada da Etapa 5 já validada, sem tratá-lo como substituto das regras de negócio, requisitos ou Casos de Uso.
8. Para tarefas que envolvam processo, sequência de etapas ou gates, consulte `docs/projeto/plano-de-desenvolvimento.md`.
9. Para preparação técnica ou tarefas das Etapas 8 a 12, consulte `docs/projeto/roteiro-tecnico-de-implementacao.md` somente como guia auxiliar; ele não substitui fontes canônicas, decisões da etapa correspondente nem autoriza antecipar gates.
10. Respeite os gates definidos no projeto.
11. Não resolva silenciosamente conflitos de negócio.
12. Não antecipe MER, DER, Modelo Físico, arquitetura ou implementação além da etapa autorizada.
13. Preserve identificadores e rastreabilidade.
14. Não altere regras de negócio sem base documental ou decisão humana.
15. Não transforme ferramentas, sistema operacional, editor ou extensões de desenvolvimento em restrições do produto sem decisão formal.
16. Trate `docs/modelagem-dados/` e seus artefatos validados como referência oficial das Etapas 6 a 8; SQLs, modelos ou estruturas auxiliares não devem substituir esses artefatos.
17. Para alterações relevantes, utilize branch ou worktree isolada.
18. Antes de concluir, valide consistência documental e Git.

## Roteamento obrigatório de execução

Toda tarefa de desenvolvimento, análise técnica, revisão, teste ou inspeção
solicitada ao agente `sgfp` deve, por padrão, passar pelo
`adaptive-orchestrator-bridge` antes de execução direta.

O fluxo preferencial é:

```text
agente sgfp → adaptive-orchestrator-bridge → Adaptive AI Orchestrator → Work Unit/worker → avaliação
```

Para uma tarefa pequena, use uma Work Unit única; para trabalho decomponível,
use o modo multiagente. Não execute diretamente na sessão owner apenas porque a
tarefa é read-only ou parece simples.

### Fallback direto controlado

A execução direta pela sessão owner nunca deve ocorrer silenciosamente.

Se o fluxo Adaptive estiver comprovadamente indisponível, órfão, stale ou
inconsistente, o owner poderá executar diretamente **somente após autorização
humana explícita** para aquela tarefa.

Exemplos de condição elegível:

- bridge indisponível;
- Work Unit órfã ou stale;
- estado `RUNNING` sem worker/subagente realmente ativo;
- sessão worker encerrada sem relatório final;
- falha repetida de dispatch/retomada;
- divergência comprovada entre estado persistido e estado real.

Antes do fallback, quando possível, o owner deve verificar o estado real da
bridge/orquestração, confirmar ausência de worker ativo, evitar duplicação de
execução e registrar o motivo concreto.

Com autorização explícita, o fallback deve:

- limitar-se ao escopo autorizado;
- preservar gates, regras de negócio e WIP;
- não antecipar etapas;
- executar as validações normalmente exigidas;
- registrar no relatório final ou `HANDOFF.md` o motivo, a evidência, os
  arquivos alterados e as validações realizadas.

Após a exceção, novas tarefas voltam ao roteamento padrão pelo Adaptive.

### Circuit breaker

Se duas tentativas consecutivas da mesma tarefa terminarem sem worker ativo e
sem relatório final, não iniciar automaticamente uma terceira Work Unit
equivalente.

Nessa situação, informar o bloqueio e solicitar decisão humana entre recuperação
do Adaptive, nova execução governada após correção ou fallback direto
controlado.

Fallback direto sem autorização humana explícita continua proibido.

Consulte `ORCHESTRATOR.md`, seção **2.1 — Roteamento pelo Adaptive e
degradação controlada**, para as regras completas.

## Estado resumido

- Baseline funcional vigente: `RF-001` a `RF-023`; 19 requisitos ativos na V1.
- `RF-012`, `RF-013` e `RF-014` (Transferências) estão fora da V1 e preservados para versão futura.
- `RF-019` (PIN) permanece futuro.
- Cada usuário possui exatamente uma Conta Financeira, criada automaticamente como **Minha Conta**.
- Saldo é derivado dos Lançamentos Financeiros ativos da conta; não existe saldo armazenado.
- Patrimônio Total não integra a V1.
- Backup manual é local, entregue por download em arquivo ZIP; backup não depende de e-mail.
- Restauração permanece integral e protegida por cópia pré-restauração recuperável.
- A V1 inclui **Resetar perfil financeiro** (mantém login) e **Excluir conta de acesso** (remove dados SGFP + login WordPress), ambas com dupla confirmação por frase em caixa alta.
- A associação de Categoria ao Compromisso Financeiro é opcional.
- Plataforma: PHP sobre WordPress, plugin próprio, WordPress REST API e MySQL/MariaDB.
- Etapa 10 está concluída, reconciliada e validada para a baseline simplificada da V1.
- Etapa 11 está em andamento na branch de interface; a reconciliação funcional foi incorporada e permanecem as validações integradas/ambientais e o fechamento formal da etapa.
- Fonte normativa da simplificação: `docs/governanca/baseline-v1-simplificada-2026-09-14.md`.

As regras operacionais completas deste projeto estão em `ORCHESTRATOR.md`.