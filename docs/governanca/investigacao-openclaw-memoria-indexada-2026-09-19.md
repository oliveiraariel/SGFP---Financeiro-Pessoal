# Investigação: indisponibilidade da memória indexada do OpenClaw — 19/09/2026

## 1. Resumo executivo

Nesta sessão, `memory_search` para o agente `sgfp` retornou **corpus de memória indisponível**, com o erro `index chunking implementation changed` e a orientação `openclaw memory status --index --agent sgfp`. O status read-only permitido confirmou que a busca vetorial está pausada porque o índice persistido declara `chunkingVersion: 3`, enquanto o runtime atual valida a versão `4`. O erro confirmado é, portanto, uma incompatibilidade de identidade/contrato de chunking; ele não é o erro AMEP `application/json payload is invalid`.

Não foi executado `openclaw memory status --index`, não houve reconstrução, re-embedding, mudança de runtime/configuração ou correção de código. Portanto, este relatório não afirma a causa-raiz final, nem que os dados foram apagados, nem que um rebuild é necessário. A indisponibilidade observada afeta a recuperação semântica; não implica, por si só, perda de transcrições ou de arquivos de memória.

## 2. Camadas que não devem ser confundidas

| Camada | O que representa | O que a falha permite concluir |
|---|---|---|
| Transcrição/contexto | Eventos e histórico das sessões; o contexto que uma conversa já recebeu | Pode continuar persistido e não é automaticamente apagado pela falha do índice |
| Memória em arquivos | Arquivos JSON/JSONL e registros duráveis de memória/conhecimento | Pode permanecer legível ou servir a fallback lexical, mas isso não prova busca semântica disponível |
| Corpus/índice semântico | Fontes, chunks, metadados de proveniência/recall e embeddings usados por `memory_search` | A consulta semântica não conseguiu validar/usar o índice por incompatibilidade de chunking |

Uma transcrição persistida não é um índice; um arquivo de memória não é um embedding; e a existência física de tabelas ou chunks não prova que a versão atual do consumidor aceite seu formato.

## 3. Evidência local observada

- O runtime local é OpenClaw `2026.9.4 (3a9d69d)`, Node `v24.19.0` e Python `3.12.3`.
- A chamada de busca do agente `sgfp` produziu a mensagem de indisponibilidade e o erro literal `index chunking implementation changed`.
- A orientação retornada foi `openclaw memory status --index --agent sgfp`; esse comando de reindexação **não foi executado**. Foi executado somente `openclaw memory status --agent sgfp`, sem `--index`.
- O status read-only retornou: OpenAI `text-embedding-3-small`, `Sources: 11/31 files`, `26 chunks`, `Dirty: yes`, banco `~/.openclaw/agents/sgfp/agent/openclaw-agent.sqlite`, `Vector search: paused until memory is rebuilt`, `Vector dims: 1536`, FTS `ready`, cache de embeddings com `22 entries` e store vetorial `indexed (unprobed)`.
- A leitura read-only do SQLite confirmou as tabelas de índice e os contadores `memory_index_sources=11`, `memory_index_chunks=26`, `memory_index_state.revision=37`, `memory_index_chunk_provenance=26`, `memory_index_chunk_recall_metadata=26` e `memory_embedding_cache=22`. Esses números demonstram registros locais não vazios; não demonstram compatibilidade, frescor, integridade ou consultabilidade.
- O registro `memory_index_meta_v1` contém `model=text-embedding-3-small`, `provider=openai`, `chunkTokens=400`, `chunkOverlap=80`, `chunkingVersion=3`, `provenanceVersion=1`, `vectorDims=1536` e `ftsTokenizer=unicode61`. O `memory_vector_rebuild_v1` está `clean`; isso não contradiz o status `Dirty: yes`, pois são metadados/indicadores distintos e o significado operacional de cada estado não foi inferido além do que o CLI exibiu.
- Também foram observados arquivos de transcrição/memória locais e o registro em `/home/ariel/.openclaw/workspace/memory/.dreams/session-corpus/2026-09-13.txt` contendo a indisponibilidade e a recomendação acima.
- O pacote instalado é `/home/ariel/.nvm/versions/node/v24.19.0/lib/node_modules/openclaw`, versão `2026.9.4`, com schema de agente `19`; o runtime é Node `v24.19.0` e Python `3.12.3`.
- No código instalado, `dist/extensions/memory-core/manager-runtime.js:522` valida `meta.chunkingVersion !== 4` e produz exatamente `index chunking implementation changed`, classificando a identidade como incompatibilidade do proprietário `openclaw` com código `chunking_version`. O mesmo módulo grava `chunkingVersion: 4` ao construir novos metadados (`:2610`).
- Não foi localizado, nesta inspeção, o produtor exato que gravou a versão 3, a release que a produziu ou um contrato histórico separado. A evidência permite fechar a causa imediata da indisponibilidade no consumidor atual, mas não a autoria histórica da divergência.

Os contadores e arquivos são evidência de armazenamento, não declaração de saúde. O estado `1` não foi interpretado como “saudável”, pois seu significado semântico não foi confirmado pelo comando oficial.

## 4. Diagnóstico e cadeia técnica provável

O fluxo relevante é: (1) `memory_search` seleciona o corpus/index do agente; (2) o runtime carrega fontes, chunks e metadados; (3) `refreshIndexIdentityDirty` compara identidade, incluindo a versão de chunking; (4) ao observar `meta.chunkingVersion=3` contra o contrato atual `4`, classifica `owner=openclaw`, `code=chunking_version`; (5) a busca vetorial é pausada até reconstrução explícita. O modelo/provider e as dimensões observados coincidem, mas isso não elimina a incompatibilidade de chunking.

O erro ocorre antes de uma recuperação semântica confiável: o consumidor atual rejeita a identidade do índice porque a representação persistida foi construída/registrada com chunking versão 3, enquanto o código atual exige versão 4. Esta é a melhor causa tecnicamente sustentada pelo status e pelo código local. Ela não prova qual release, processo ou migração produziu a divergência, nem prova corrupção ou apagamento.

### Hipóteses não comprovadas

- **Upgrade/migração:** runtime alterado sem migração do índice.
- **Embedding/provider:** mudança de modelo, provider, dimensão ou backend exigindo re-embedding.
- **Concorrência:** escrita/reindexação simultânea deixando metadados e chunks em revisões distintas.
- **Corrupção:** truncamento ou inconsistência de arquivos/SQLite.

Nenhuma dessas hipóteses foi demonstrada por diagnóstico oficial, checksum, stack trace ou reprodução. O erro AMEP `application/json payload is invalid` pertence a outra camada, de validação/entrega de mensagem JSON. Não há evidência correlacionada por `execution_id`, manifesto, hash ou stack trace ligando-o ao chunking; ele não deve ser listado como dor, causa ou explicação desta indisponibilidade.

## 5. Dados que podem continuar recuperáveis

Podem permanecer recuperáveis transcrições e eventos persistidos, arquivos JSON/JSONL de memória, fontes/chunks armazenados no SQLite e caches/índices anteriores preservados. Isso é possibilidade, não garantia pela API atual. A falha não autoriza concluir apagamento nem iniciar reconstrução.

## 6. Solução permanente proposta (sem execução nesta unidade)

1. Correlacionar a falha com agente, `execution_id`, versão, caminho do índice, revisão SQLite (`37`), `corpus_revision` se exposta e consumidor; registrar que a primeira interface de baixo custo já confirmou `chunkingVersion 3 → 4`.
2. Definir contrato versionado para `document_id`, `chunk_id`, offsets, tokenizer, tamanho/overlap, modelo, dimensão, provider, `index_version`, tombstones e hashes.
3. Gerar índice em staging, com manifest, checksum e revisão; usar lock/writer único e troca atômica após validação.
4. Preservar índice anterior e corpus-fonte até validação pós-troca; permitir rollback por referência.
5. Tornar migração/rebuild idempotente, sem duplicar chunks ou cobrar embeddings novamente sem justificativa.
6. Separar fallback lexical/leitura de transcrição da consulta semântica, sinalizando frescor, cardinalidade e motivo.
7. Medir embeddings, provider/modelo, latência, armazenamento e custo antes da promoção, com limite de custo e privacidade.

| Fase | Saída verificável |
|---|---|
| A — correlação | Caso reproduzível e componente responsável |
| B — inventário | Matriz de runtime, caminhos, versões e contrato |
| C — contrato | Especificação aprovada de corpus/chunks/embeddings |
| D — experimento | Rebuild em cópia/dry-run com recall, integridade, custo e latência |
| E — implementação | Writer idempotente com lock, staging, atomicidade e rollback |
| F — promoção | Mudança gradual, métricas, alerta, fallback e runbook |

## 7. Critérios, testes e runbook

Aceitar somente se payloads válidos/ inválidos forem classificados sem corromper o corpus; cada chunk tiver identidade, revisão e proveniência; rebuild interrompido puder continuar ou reverter; escritores concorrentes não perderem atualizações; o índice anterior permanecer consultável durante a troca; transcrições forem recuperáveis independentemente do índice; e custo/tempo de embedding forem medidos antes da promoção.

Runbook: congelar alteração; preservar logs, manifests, corpus e índice anterior; coletar correlação; verificar espaço, provider e custo; executar dry-run em cópia; conferir hashes/cardinalidade; promover por troca atômica; testar retomada; monitorar; manter rollback. Em falha, reverter a referência e preservar o diagnóstico. Não apagar transcrições nem iniciar re-embedding automático.

## 8. Limites, não-objetivos e decisões pendentes

Esta revisão executou somente `openclaw memory status --agent sgfp` e leituras locais read-only do código/configuração/metadados. Não executou `openclaw memory status --index`, não reconstruiu índice, não fez embedding e não alterou OpenClaw, código, configuração, skills ou Git. Não confirma se haverá custo de embedding; apenas registra que ele deve ser medido antes de qualquer rebuild.

Antes de implementar, decisão humana deve definir necessidade de recuperação semântica, proprietário do índice, provider/modelo, limites de custo, retenção/privacidade e suficiência do fallback lexical. Chunking/embeddings são mudança arquitetural do runtime de memória, não correção inferível do SGFP.

## Proveniência

- Envelope AMEP: `.adaptive/messages/msg_8a2ad99ad75b496cbe8f43cb138f85c5/manifest.json`; SHA-256 `80bca592e70da1062ba977c34b9f012fd12d0cd084726ab09529a3f8e1c16d71` verificado contra a referência recebida.
- Payload: `payload.json`; SHA-256 verificado contra o manifesto.
- Payload SHA-256 `5769fe1257e2f7f14e8abbaf4d28e3dd3e3211bb86b8494bdd23df68ca6090f6`; inspeções desta unidade foram somente leitura; nenhum comando com `--index` foi executado.
