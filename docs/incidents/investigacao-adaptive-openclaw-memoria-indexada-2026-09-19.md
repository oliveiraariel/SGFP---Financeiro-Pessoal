# Investigação — Adaptive, OpenClaw e memória indexada

**Data:** 19/09/2026  
**Correlação:** `d2371ffdf3bf4016844035d4a1f3115e`  
**Escopo:** consolidação somente leitura das auditorias `wu-adaptive-audit`, `wu-index-audit` e `wu-correlation`.

## 1. Dor e impacto

Foi observado erro de indisponibilidade do corpus de memória semântica do OpenClaw, associado nos registros a incompatibilidade de versão de chunking (`3` persistida versus `4` exigida pelo runtime). A busca vetorial ficou pausada, enquanto a busca textual permaneceu disponível.

O impacto confirmado é a perda ou redução da recuperação semântica indexada. Não há evidência suficiente para afirmar perda de dados, corrupção do banco ou causalidade do Adaptive.

## 2. Método e evidências

Foram correlacionados manifests AMEP/Result Store, resultados persistidos, checkpoint, liveness, código e documentação do Adaptive, além dos artefatos locais relacionados a memória, índice, chunking e logs. As três dependências publicadas estavam completas e seus hashes foram verificados:

| Lote | Execução | Resultado | SHA-256 verificado |
|---|---|---|---|
| `wu-adaptive-audit` | `8d5e061a58484b4ebd64a7472404f735` | `4706` bytes | `85628ee33101b3152029f744ef6b521498cd1819ffd17313e881cff6826ab3d1` |
| `wu-index-audit` | `b620991271f94a038a0f2ea2b6307c84` | `3856` bytes | `9dc97e7589af0e866bfe41a9053d9226d08fb78ea6a4d62486c8b44ef2b9abe5` |
| `wu-correlation` | `9db0dacc028841e58d438997ef74113f` | `2136` bytes | `0ae8dbe83b6d8b81ecf026c4debbed7ddbb7e9b2c78db40eccdf2e245110ae01` |

Também foram verificadas a integridade do manifesto AMEP de entrada e do payload antes da consolidação. Nenhum comando de memória, indexação, embedding, reindexação ou mutação de código/configuração/índice foi executado.

## 3. Achados por componente

### Adaptive AI Orchestrator

- Não foram encontradas chamadas a `openclaw memory`, `memory_search`, `memory_get`, SQLite de memória do agente, embeddings, reindexação ou injeção semântica em `src/`, `tests/`, `scripts/`, `bootstrap/` e documentação operacional examinada.
- O Adaptive usa artefatos próprios (`.adaptive`: AMEP, checkpoints, liveness, Result Store e logs), distintos do índice semântico OpenClaw.
- O `FileMessageStore` separa payload do controle; o `FileResultStore` valida contenção de caminho, tamanho e SHA-256 e publica manifesto por último.
- Estado de projeto e catálogos permanecem em adaptadores em memória; estado operacional durável usa arquivos JSON/texto com escrita temporária e substituição atômica.
- Não foram encontrados transações de banco, locks entre processos ou leases para sincronizar todos esses arquivos/adaptadores.

### OpenClaw / índice semântico

- Logs persistidos registram `chunkingVersion: 3` contra requisito `4` do runtime e a mensagem de corpus indisponível.
- A causa imediata mais provável é incompatibilidade de formato/metadados entre índice persistido e runtime atual.
- Essa causa está baseada em evidência histórica persistida: não houve reconfirmação por comando de status do índice nesta investigação.

### Estado operacional da execução

O lote de correlação encontrou checkpoint em `EXECUTION`, `desired_state=RUNNING`, `terminal=false`, geração de dispatch `1` e paralelismo máximo observado `3`, com heartbeats recentes. Em um momento intermediário, os três lotes apareciam `RUNNING` e `wu-report` `PLANNED`; posteriormente os três resultados foram publicados e aceitos. Essa diferença é uma divergência temporal de observabilidade, não evidência de falha semântica.

## 4. Causalidade e confiança

| Afirmação | Classificação | Confiança | Limite |
|---|---|---:|---|
| O corpus semântico apresentou incompatibilidade de chunking | Observação persistida | Alta no sintoma | Falta reconfirmação viva |
| `3` versus `4` é a causa imediata | Inferência apoiada por log | Média-alta | Requer status/integridade do índice |
| Adaptive causou a incompatibilidade | Não demonstrada | Alta confiança na não demonstração | Auditoria não prova impossibilidade externa |
| Há corrupção ou perda de dados | Não estabelecida | — | Não afirmar sem exame do banco |

A classificação causal aceita é **observabilidade/estado de execução**, não causalidade direta demonstrada. O Adaptive pode transportar e registrar o erro, mas não há evidência de que mutou o índice, chamou o mecanismo de memória ou executou reindexação.

## 5. Limitações

- Não foi executado status/index do OpenClaw; portanto, o conteúdo atual, a liveness e a integridade do índice vivo permanecem não confirmados.
- Não foi inspecionado o SQLite/banco privado do agente nem executada migração ou reconstrução.
- A auditoria não cobre processos externos ao checkout nem estado global não exposto nos artefatos correlacionados.
- `pytest` não estava disponível no ambiente do lote Adaptive; não há declaração de suíte automatizada verde independente.
- O working tree do SGFP já continha alterações e arquivos não rastreados; nenhum foi alterado por esta consolidação fora do relatório autorizado.

## 6. Soluções por componente — não executadas

### Índice OpenClaw

1. Executar diagnóstico somente leitura do índice e comparar explicitamente versão de chunking, metadados, schema e integridade do banco.
2. Se incompatível, preparar rebuild/migração em staging ou cópia, preservando o índice anterior.
3. Gerar novo índice, validar contagens, consultas representativas e disponibilidade FTS/vetorial.
4. Fazer troca atômica somente após validação; manter o índice anterior para rollback.

### Adaptive

1. Não adicionar integração com memória sem decisão de arquitetura.
2. Se for necessária durabilidade concorrente, especificar e testar adaptador transacional/SQLite separado dos arquivos AMEP/Result Store.
3. Manter a separação entre plano de controle, plano de resultado e índice semântico.

## 7. Runbook de verificação futura

- Capturar status somente leitura do corpus/índice e registrar versão exigida e encontrada.
- Verificar existência, permissões, tamanho, checksum e integridade lógica do banco.
- Confirmar disponibilidade separada de FTS e busca vetorial.
- Comparar uma amostra fixa de documentos/chunks antes e depois do diagnóstico.
- Não iniciar rebuild em produção sem cópia, lock, janela operacional, critério de abortamento e plano de retorno.

## 8. Custo, privacidade e rollback

- O diagnóstico deve limitar-se a metadados, contagens, checksums e amostras mínimas; não exportar conteúdo privado para chat ou logs.
- Reindexação pode consumir CPU, memória, armazenamento e tempo proporcional ao corpus; medir antes de escolher janela e paralelismo.
- O rollback recomendado é manter o índice anterior intacto, construir o novo fora do caminho ativo e trocar uma referência de forma atômica. Em falha de validação, remover apenas o artefato novo e restaurar a referência anterior.
- Nenhuma solução de reparo foi executada nesta investigação.

## 9. Não-objetivos

- Implementar SQLite, memória semântica, embeddings ou chunking.
- Reindexar, migrar, apagar ou substituir o índice.
- Alterar código/configuração do Adaptive ou OpenClaw.
- Corrigir o working tree do SGFP ou modificar arquivos além deste relatório.
- Tratar logs de sessão como prova suficiente de liveness ou causalidade.

## 10. Conclusão

O problema está suficientemente caracterizado como incompatibilidade observada no índice semântico OpenClaw, com impacto na recuperação vetorial. A auditoria não sustenta atribuir causalidade ao Adaptive: seus artefatos são de transporte/coordenação e não há implementação encontrada de memória semântica ou reindexação. A próxima ação segura é uma verificação viva, somente leitura, do índice; qualquer rebuild ou migração deve ser uma tarefa separada e reversível.
