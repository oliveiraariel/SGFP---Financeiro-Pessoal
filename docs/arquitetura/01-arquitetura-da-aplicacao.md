# SGFP — Arquitetura da Aplicação V1

**Etapa:** 9 — Arquitetura da Aplicação

**Status:** baseline validada em 11/09/2026; gate integral da Etapa 9 aprovado; pronta para subsidiar a Etapa 10 — Desenvolvimento da API

**Baseline:** `RF-001` a `RF-018`, `RF-020` e `RF-021` ativos; `RF-019` futuro
**Data:** 10/09/2026

## 1. Finalidade, autoridade e limite

Este documento define uma arquitetura implementável para o backend da V1 do SGFP em um plugin PHP próprio para WordPress. Ele decide limites, dependências, integração, persistência e contratos públicos sem iniciar a Etapa 10 nem criar código ou frontend.

As regras de negócio, requisitos, Casos de Uso e o [Modelo Físico validado](../modelagem-dados/03-modelo-fisico.md) continuam sendo as fontes canônicas de seus assuntos. As escolhas `ARQ-*` são propostas da Etapa 9: tornam explícito o mecanismo técnico adotado, mas não alteram regra de negócio e somente se tornam baseline após validação humana.

## 2. Direcionadores e exclusões

### Direcionadores obrigatórios

- aplicação Web em PHP sobre WordPress;
- backend SGFP em plugin próprio e API apoiada na WordPress REST API;
- identidade, credenciais, autenticação, recuperação de senha e sessão sob responsabilidade do WordPress, com login da V1 exclusivamente por e-mail e senha;
- tabelas próprias InnoDB compatíveis com MySQL/MariaDB para os dados financeiros;
- isolamento por usuário em toda leitura e mutação privada;
- integridade transacional para efeitos financeiros relacionados;
- saldos, patrimônio e Dashboard sempre derivados;
- recorrência exclusivamente mensal e histórico anterior preservado;
- backup ordinário manual e proteção automática somente antes de restauração confirmada.

### Fora desta arquitetura V1

PIN, integração bancária, sincronização ou armazenamento externo obrigatório, relatórios específicos, anexos, aplicativo móvel, código do plugin, contrato OpenAPI final e interface Web. Não se define exclusão de usuário nem remoção automática dos dados na desinstalação.

## 3. Contexto e fronteiras de confiança

```text
Navegador
   │ HTTPS + cookie de sessão + nonce REST
   ▼
WordPress ── identidade, sessão, capabilities, REST e e-mail
   │
   ▼
Plugin SGFP ── casos de uso, autorização de recurso e regras financeiras
   │                         │
   ▼                         ▼
MySQL/MariaDB           storage privado de backup
```

Fronteiras:

1. todo dado do cliente é não confiável, inclusive identificadores e datas;
2. o usuário efetivo vem apenas da sessão WordPress;
3. capability autoriza o uso do SGFP; propriedade do recurso autoriza o acesso ao registro;
4. o banco reforça pertencimento e integridade, mas não substitui autorização;
5. e-mail é transporte externo falível, nunca armazenamento transacional;
6. arquivo recebido para restauração permanece não confiável até autenticação e validação completas.

## 4. Estilo, direção das dependências e organização

A V1 adota um **monólito modular em um único plugin**, organizado por capacidades. É uma escolha proporcional ao produto e à implantação WordPress; não há benefício demonstrado em processos ou serviços distribuídos.

```text
WordPress REST
      ↓
Controller / DTO HTTP
      ↓
Application Service (caso de uso)
      ↓
Políticas e valores de domínio necessários
      ↓
Portas de saída (repositories, transação, relógio, e-mail, backup, log)
      ↑
Adaptadores WordPress / MySQL-MariaDB / filesystem
```

Regras de dependência:

- Controllers não acessam `$wpdb` nem decidem invariantes;
- Services não recebem tipos `WP_REST_*` e coordenam autorização, transação e caso de uso;
- Repositories são específicos por responsabilidade, sempre recebem o usuário efetivo e não oferecem CRUD genérico irrestrito;
- infraestrutura implementa portas internas e pode depender do WordPress; o núcleo não depende de globais WordPress;
- objetos de domínio existem somente quando concentram invariantes ou cálculos reais; não há equivalência automática entre tabela, entidade e classe;
- integração entre capacidades ocorre por Services ou interfaces explícitas, nunca por chamada entre Controllers.

Organização lógica para a Etapa 10:

```text
sgfp.php
composer.json
src/
  Bootstrap/                 ativação, compatibilidade, composição e hooks
  Rest/                      rotas, schemas, Controllers e ErrorMapper
  Application/
    Accounts/               contas, saldo inicial, saldos e patrimônio
    Categories/             categorias e desvinculação
    Commitments/            compromissos, efetivação e desfazimento
    Recurrences/            projeção e comandos sobre ocorrências mensais
    Transfers/              transferências e seus dois efeitos
    Reporting/              movimentações e Dashboard
    Preferences/            tema do usuário
    Backup/                 exportação, validação e restauração
  Domain/                   políticas e Value Objects justificados
  Infrastructure/
    Persistence/            repositories, transações, locks e migrações
    WordPress/              usuário atual, capability, e-mail e usermeta
    Backup/                 codec, proteção e storage privado
    Observability/          correlação e eventos técnicos
tests/
```

O namespace raiz será `SGFP\`. Composer/PSR-4 será usado para autoload; o artefato instalável deverá incluir dependências de runtime. A estrutura é guia de responsabilidade, não autorização para gerar classes vazias.

### 4.1 Bootstrap e ciclo de vida

`sgfp.php` será um ponto de entrada mínimo: verifica o contexto WordPress, carrega o autoloader e registra callbacks de ciclo de vida. O `Bootstrap` compõe adaptadores em `plugins_loaded` e registra rotas somente em `rest_api_init`; incluir o arquivo principal não executa regra financeira.

- **ativação:** valida capacidades do ambiente, resolve nomes reais das tabelas, executa `SchemaMigrator` e cria options técnicas; falha impede uso e apresenta diagnóstico administrativo sem segredos;
- **carregamento/upgrade:** compara versão esperada e instalada, pois atualizar o plugin não dispara nova ativação; migração pendente usa o mesmo lock e, se falhar, as rotas de negócio permanecem fechadas;
- **desativação:** libera somente estado efêmero; não remove tabelas, options duráveis, categorias ou backups recuperáveis;
- **desinstalação:** preserva dados por padrão. Não haverá `DROP` automático enquanto uma política destrutiva e o consentimento administrativo não forem formalmente decididos.

Composer é ferramenta de construção e autoload, não requisito de execução no servidor. Ele foi escolhido em lugar de carregador próprio por fornecer mapa PSR-4 verificável e separar dependências de produção e desenvolvimento; o pacote instalável inclui apenas o necessário em runtime.

## 5. Componentes e estado sob responsabilidade

| Componente | Responsabilidade | Estado autoritativo |
| --- | --- | --- |
| `Bootstrap` | compor adaptadores, registrar hooks/rotas e verificar compatibilidade | nenhum estado financeiro |
| `RouteRegistry` | métodos, schemas e `permission_callback` | contrato HTTP registrado |
| Controller | request → comando/consulta; resultado → response | nenhum |
| Application Service | caso de uso, autorização de recurso e unidade transacional | transição em curso |
| Política/Value Object | período, dinheiro, estado e invariantes | valor imutável em memória |
| Repository específico | persistência e projeções escopadas ao usuário | tabelas oficiais |
| `TransactionManager` | conexão e `START TRANSACTION`/`COMMIT`/`ROLLBACK` | transação corrente |
| `UserOperationLock` | serializar mutações, captura de backup e restauração por usuário | lock nomeado no banco |
| `CurrentUserContext` | identidade autenticada do WordPress | `wp_users.ID` corrente |
| `BackupCodec/Protector/Store` | serializar, proteger, verificar e preservar | arquivo e catálogo privados |
| `Mailer` | solicitar envio pelo transporte WordPress | resultado de aceitação do transporte |
| `EventLogger` | evento estruturado e redigido | log operacional |

## 6. Integração com WordPress e autorização

### 6.1 Identidade, autenticação e sessão

- cadastro, identidade, armazenamento de credenciais, sessão e recuperação reutilizam as APIs e os mecanismos do WordPress; o SGFP não cria repositório, hash ou credencial paralelos;
- o formulário/handler de autenticação da V1 aceita **exclusivamente e-mail e senha**. Nome de usuário WordPress, PIN e provedores externos não são alternativas de login expostas ou aceitas pelo SGFP;
- o adaptador de identidade valida o formato do e-mail, resolve a identidade WordPress pelo e-mail e delega a verificação da senha ao WordPress. Falha de formato, e-mail inexistente, senha incorreta ou conta não autenticável produz para o cliente a mesma mensagem e o mesmo resultado de acesso não autenticado, sem indicar qual credencial falhou;
- a alteração de senha ocorre diretamente na aplicação para usuário autenticado e somente prossegue após o WordPress validar a **senha atual** e o usuário fornecer a **confirmação obrigatória da alteração**. Senha atual incorreta ou confirmação ausente não altera a credencial;
- valores de senha existem apenas durante a chamada ao adaptador WordPress: não entram em DTOs de domínio financeiro, tabelas SGFP, logs, eventos, mensagens de erro ou respostas. Somente TLS pode transportar esses valores;
- a interface Web usa cookie de sessão e nonce REST contra CSRF, sempre sobre HTTPS;
- cada rota possui `permission_callback`; operações financeiras exigem usuário autenticado, provisionamento confirmado e capability própria do SGFP, enquanto `onboarding` exige sessão e nonce válidos;
- `CurrentUserContext` obtém `wp_users.ID`; `FK_ID_USUARIO` nunca é aceito do payload, query ou path como autoridade;
- tema claro/escuro usa chave própria em `wp_usermeta`;
- um hook de cadastro chama provisionamento idempotente da capability `sgfp_access` e das categorias iniciais;
- um marcador próprio em `usermeta` só confirma o provisionamento depois das categorias. Ele permite distinguir falha de provisionamento do estado legítimo em que o usuário excluiu todas as categorias;
- usuários WordPress preexistentes ou incompletos usam `POST /onboarding`, que exige sessão/nonce, mas ainda não a capability SGFP; todas as demais rotas exigem `sgfp_access` e o marcador confirmado.

O cadastro WordPress e o provisionamento SGFP não formam uma única transação entre subsistemas. Falha no provisionamento não invalida silenciosamente o usuário: não concede acesso financeiro, é registrada e pode ser reparada pela repetição idempotente. `permission_callback` somente autoriza; não provisiona nem produz outros efeitos colaterais. A Etapa 10 deverá manter o handler de autenticação separado dos Services financeiros e verificar seu comportamento por interface pública, inclusive o erro uniforme e as condições da alteração de senha.

### 6.2 Autorização de recurso

Autenticação e capability são o primeiro gate. Cada operação privada resolve o recurso por `id + FK_ID_USUARIO`; todas as referências relacionadas — conta, categoria, recorrência e compromisso — são resolvidas de novo no mesmo escopo. Recurso inexistente e recurso de outro usuário retornam o mesmo `404`, reduzindo enumeração.

As FKs compostas do Modelo Físico são defesa adicional. Nem mesmo uma capability administrativa genérica do WordPress concede acesso financeiro cruzado. Backup e restauração operam somente sobre o usuário efetivo.

## 7. Contrato REST arquitetural

### 7.1 Convenções

- namespace registrado `sgfp/v1`; a URL usa a base REST descoberta na instalação (normalmente `/wp-json/sgfp/v1`), sem fixar `/wp-json` no cliente;
- JSON UTF-8 em `snake_case`, desacoplado dos nomes físicos;
- valores monetários como string decimal de duas casas, nunca `float`;
- data civil `YYYY-MM-DD`, mês `YYYY-MM-01` e instante ISO 8601 com fuso;
- coleções `{ "items": [...], "pagination": {...} }` e limites máximos de paginação definidos no OpenAPI da Etapa 10;
- `201` para criação, `200` para consulta/mutação com corpo e `204` para exclusão concluída;
- comandos explícitos para efetivar e desfazer;
- nenhuma consulta `GET` persiste, materializa ou modifica dados;
- identificador de usuário não integra contratos financeiros.

Erros possuem forma estável:

```json
{
  "code": "sgfp_conflict",
  "message": "A operação conflita com o estado atual do recurso.",
  "details": { "fields": {} },
  "correlation_id": "..."
}
```

Mapeamento: `400` formato inválido; `401` sem sessão; `403` capability insuficiente; `404` recurso ausente no escopo; `409` conflito de estado/concorrência; `413` arquivo acima do limite; `422` regra semântica; `503` provisionamento, migração ou dependência temporariamente indisponível; `500` falha inesperada. Respostas nunca expõem SQL, stack trace, caminhos ou dados internos.

### 7.2 Superfícies V1

| Capacidade | Superfície relativa a `/sgfp/v1` | Regra de contrato |
| --- | --- | --- |
| Identidade/senha | handler WordPress adaptado para login/alteração/recuperação; `POST /onboarding` somente para completar provisionamento | login somente por e-mail + senha; falha de autenticação uniforme; alteração exige senha atual + confirmação; nenhuma credencial é persistida pelo SGFP |
| Contas | `GET/POST /accounts`; `GET/PATCH /accounts/{id}` | o papel na criação depende da decisão humana registrada em 17.1; `PATCH` apenas renomeia; sem troca de papel ou `DELETE` na V1 |
| Saldo inicial | `POST /accounts/{id}/initial-balance` | somente Principal; cria o lançamento técnico previsto no Modelo Físico |
| Categorias | `GET/POST /categories`; `PATCH/DELETE /categories/{id}` | exclusão desvincula compromissos atomicamente |
| Compromissos simples | `GET/POST /commitments`; `GET/PATCH/DELETE /commitments/{id}` | efetivado exige desfazimento antes de editar/excluir |
| Efetivação simples | `POST /commitments/{id}/effectuation`; `POST /commitments/{id}/undo-effectuation` | comando idempotente sem duplicar efeito |
| Transferências simples | `GET/POST /transfers`; `GET/PATCH/DELETE /transfers/{id}` | natureza derivada; não aceita secundária–secundária |
| Efetivação de transferência | `POST /transfers/{id}/effectuation`; `POST /transfers/{id}/undo-effectuation` | resposta somente após os dois efeitos |
| Ocorrências recorrentes | `GET/PATCH/DELETE /recurrences/{id}/occurrences/{month}` | `GET` projeta sem gravar; mutações exigem `scope=occurrence|series_from_month` |
| Efetivação recorrente | `POST /recurrences/{id}/occurrences/{month}/effectuation`; `POST .../undo-effectuation` | comando materializa a ocorrência sob transação quando necessário |
| Consultas | `GET /movements`; `GET /net-worth`; `GET /dashboard` | somente leitura, filtrada por período/conta aplicável |
| Tema | `GET/PUT /preferences/theme` | apenas `light` ou `dark` |
| Backup | `POST /backups`; `GET /backups/pre-restore`; `GET /backups/pre-restore/{id}/file` | manual por e-mail; listagem/arquivo somente do usuário atual |
| Restauração | `POST /restore-validations`; `POST /restorations` | validar/resumir primeiro; confirmar por token depois |

Ao criar compromisso ou transferência recorrente, a resposta inclui `recurrence_id` e `month`. Consultas mensais retornam a mesma referência lógica para ocorrência já persistida ou apenas projetada. Cartão e parcelamento usam compromisso/recorrência; não ganham recurso próprio. Lançamentos não têm criação genérica: surgem de efetivação ou saldo inicial.

O OpenAPI da Etapa 10 detalhará campos, limites e exemplos sem mudar essas superfícies ou regras sem revisão arquitetural.

### 7.3 Representações mínimas

| Recurso | Campos públicos estáveis |
| --- | --- |
| conta | `id`, `name`, `role`, `created_at`; `balance` apenas como projeção |
| categoria | `id`, `name`, `created_at` |
| compromisso | `id`, `name`, `amount`, `nature`, `month`, `status`, `category_id` anulável e referência de recorrência anulável |
| recorrência | `id`, `starts_in`, `months_count` anulável, `ended_in` anulável; periodicidade não é escolhível na V1 |
| transferência | campos do compromisso mais `source_account_id` e `target_account_id`; `nature` é derivada e somente leitura |
| lançamento | `id`, `commitment_id` anulável, `account_id`, `origin`, `name`, `amount`, `effect`, `effective_at`, `description`, `state`, `undone_at` |
| Dashboard | `month`, `opening_balance`, `expected_inflows`, `expected_outflows`, `expected_closing_balance` e compromissos componentes |
| validação de restauração | token, expiração, origem/data da cópia e contagens; nunca caminho, chave ou conteúdo interno |

Campos derivados ou somente leitura não são aceitos como autoridade em comandos. Obrigatoriedade por operação, comprimentos e exemplos pertencem ao OpenAPI, subordinado a estas representações e ao Modelo Físico.

### 7.4 Fronteira com clientes

A interface V1 e qualquer cliente futuro consomem somente REST ou os fluxos nativos de identidade: não acessam banco, `$wpdb`, arquivos internos nem `admin-ajax` como API paralela. Cálculos, autorização, estados e invariantes permanecem no backend; o cliente apenas valida para usabilidade e nunca é autoridade.

O navegador V1 opera na mesma origem WordPress com cookie e `X-WP-Nonce`; CORS amplo não é habilitado. Um futuro mobile exigirá decisão separada sobre autenticação suportada pelo WordPress, revogação, CORS e armazenamento de credencial, sem alterar Services ou Repositories.

## 8. Persistência e evolução do esquema

### 8.1 Adaptação fiel

Permanecem as seis estruturas oficiais: `CONTA_FINANCEIRA`, `CATEGORIA`, `RECORRENCIA`, `COMPROMISSO_FINANCEIRO`, `TRANSFERENCIA` e `LANCAMENTO_FINANCEIRO`, com colunas, FKs compostas, `CHECK`, índices e colunas geradas do Modelo Físico.

Na instalação real, nomes usam `$wpdb->prefix` mais prefixo lógico `sgfp_`; a tabela de usuários é obtida por `$wpdb->users`. Um `TableNames` central produz somente identificadores conhecidos. Valores sempre usam consultas preparadas. Posts, postmeta e usermeta não substituem as tabelas financeiras; usermeta permanece apenas para preferência de tema.

Nomes físicos de constraints e índices recebem prefixo/hash determinístico, limitado ao tamanho aceito pelo banco, para não colidir com outra instalação no mesmo schema; sua semântica permanece idêntica ao Modelo Físico.

A ativação verifica InnoDB, `utf8mb4`, FKs, `CHECK` efetivamente aplicado e colunas geradas. Incompatibilidade impede ativação/upgrade com diagnóstico, em vez de degradar integridade silenciosamente.

### 8.2 Migração

`dbDelta()` não é autoridade exclusiva porque não garante todas as construções validadas. `SchemaMigrator` executa migrações incrementais, ordenadas, idempotentes e versionadas; a versão confirmada fica em option própria não autoload. Um lock nomeado impede migradores simultâneos.

DDL MySQL/MariaDB não é considerado rollback transacional confiável. Cada migração registra pré-condição, mudança, verificação pós-condição e recuperação operacional. A versão só avança depois da verificação; falha bloqueia uso de código incompatível. Desinstalação preserva dados por padrão.

## 9. Transações, locks e concorrência

`TransactionManager` mantém uma única conexão `$wpdb` por unidade de trabalho; todos os Repositories participantes a reutilizam. Exceção provoca rollback. Decisões dependentes de estado relêem registros com `SELECT ... FOR UPDATE`.

São atômicos:

- desvincular compromissos e excluir categoria;
- efetivar/desfazer compromisso e sincronizar `STATUS`/lançamento;
- criar/alterar compromisso de transferência e sua especialização;
- efetivar ou desfazer simultaneamente os dois efeitos de transferência;
- materializar e alterar ocorrência/recorrência;
- substituir integralmente tabelas SGFP e tema do usuário numa restauração.

Constraints únicas defendem contra repetição concorrente. Deadlock/timeout sempre reverte; nova tentativa curta só é permitida para comando comprovadamente idempotente.

Todo Service mutável, o backup manual e a restauração adquirem o mesmo `UserOperationLock` antes da primeira operação de banco relativa ao usuário. A chave é calculada no servidor a partir do usuário efetivo; o lock possui espera de aquisição limitada, pertence à conexão única da unidade de trabalho e é liberado explicitamente. Falha, timeout ou perda da conexão/posse do lock encerram a operação sem nova tentativa implícita e sem alteração parcial.

Para produzir um backup, depois de adquirir o lock, o `BackupService` inicia na mesma conexão uma transação somente leitura em `REPEATABLE READ` com snapshot consistente. Todas as consultas às seis tabelas InnoDB, filtradas pelo usuário, são consumidas pelo `BackupCodec` dentro dessa única visão; o tema SGFP também é lido enquanto o lock permanece retido. A transação de leitura só termina depois que o último registro lógico foi serializado no contêiner temporário protegido. Nenhum Repository do exportador pode abrir outra conexão ou executar uma leitura fora dessa transação.

Se uma mutação obtiver o lock primeiro, o backup começa apenas depois de seu commit e representa o estado posterior completo. Se o backup obtiver o lock primeiro, a mutação aguarda ou recebe conflito após o timeout e o contêiner representa integralmente o estado anterior. Assim, nenhuma cópia pode combinar registros de antes e depois da mesma mutação. A etapa de e-mail ocorre fora do lock e não altera o ponto temporal já capturado.

Na restauração, o mesmo lock permanece sob a mesma conexão desde a captura da cópia `pre_restore` até o commit ou rollback da substituição integral. A transação somente leitura da captura e a transação de escrita da restauração são distintas, mas não existe intervalo desbloqueado entre elas; portanto, nenhuma mutação pode ocorrer entre o estado preservado e o estado imediatamente substituído.

## 10. Fluxos financeiros críticos

### 10.1 Compromisso e lançamento

Compromisso padrão pendente incide sobre a Conta Principal. Efetivação bloqueia o compromisso, confirma `PENDENTE`, cria um lançamento `ATIVO` de mesma natureza e muda para `EFETIVADO` na mesma transação. Desfazimento bloqueia compromisso e efeito ativo, marca o lançamento `DESFEITO`, preenche `DESFEITO_EM` e retorna o compromisso a `PENDENTE`; o histórico não é apagado.

Saldo inicial é comando próprio: apenas Conta Principal, `ORIGEM=SALDO_INICIAL`, `TIPO_EFEITO=ENTRADA`, no máximo um ativo por conta e valor inclusive negativo ou zero. Conta Secundária recebe composição inicial somente por transferência.

### 10.2 Transferência

Criação persiste `COMPROMISSO_FINANCEIRO(TIPO=TRANSFERENCIA)` e `TRANSFERENCIA` na mesma transação. O Service exige contas distintas do mesmo usuário e par Principal–Secundária. A natureza é derivada: Principal → Secundária é `SAIDA`; Secundária → Principal é `ENTRADA`.

Efetivação gera atomicamente dois lançamentos ativos, de mesmo nome e valor: `SAIDA` na origem e `ENTRADA` no destino. Insuficiência de saldo não bloqueia. Desfazimento marca os dois efeitos como desfeitos e retorna o compromisso a pendente. Estado unilateral nunca é confirmado ao cliente.

## 11. Recorrência mensal sem efeitos em consultas

Adota-se **snapshot mensal com projeção de leitura e materialização por comando**:

1. criação persiste `RECORRENCIA` e a primeira ocorrência;
2. `recurrence_id + month` é a referência lógica estável da ocorrência;
3. consultas calculam em memória as ocorrências aplicáveis ausentes, respeitando início, quantidade e encerramento; não inserem registros;
4. antes de efetivar, alterar ou excluir uma ocorrência apenas projetada, o Service bloqueia a recorrência e materializa o snapshot alvo na mesma transação do comando;
5. a unique `(recorrência, mês)` torna a materialização idempotente sob concorrência;
6. alteração/exclusão apenas da ocorrência materializa também, quando aplicável, o mês sucessor com a configuração anterior ao ajuste, impedindo propagação da exceção;
7. alteração `series_from_month` preserva meses anteriores, atualiza snapshots pendentes existentes desde o pivô e torna o pivô a base das projeções futuras;
8. exclusão pontual usa `STATUS=EXCLUIDO`; cancelamento do pivô e seguintes registra `ENCERRADA_NO_MES` e exclui snapshots pendentes abrangidos;
9. quantidade determinada limita o último mês elegível; recorrência encerrada não reativa;
10. ocorrência efetivada precisa ser desfeita antes de edição/exclusão; comandos sobre a série falham sem alteração se houver ocorrência efetivada no intervalo afetado.

Para transferência recorrente, compromisso e especialização são projetados/materializados juntos. A estratégia atende planejamento futuro e `CA-011.4` sem cron, horizonte arbitrário ou escrita causada por `GET`.

## 12. Saldos, patrimônio e Dashboard

Repositories de consulta calculam com `DECIMAL`, considerando somente lançamentos `ATIVO`:

- saldo da conta = entradas menos saídas;
- saldo de abertura = efeitos anteriores ao primeiro instante do mês;
- patrimônio = soma dos saldos das contas do usuário;
- saldo final previsto = saldo de abertura + entradas previstas − saídas previstas;
- Dashboard = composição dessas projeções e compromissos do período.

Os dois efeitos de uma transferência se anulam no patrimônio. Não existe tabela ou cache autoritativo de saldo/Dashboard. Cache futuro, se comprovadamente necessário, será descartável, escopado ao usuário e invalidado por toda mutação relevante.

## 13. Backup manual e restauração protegida

### 13.1 Formato e proteção

O backup será um contêiner lógico versionado, produzido em streaming, contendo metadados, origem `manual|pre_restore`, usuário proprietário, tema e dados SGFP necessários. Referências internas não dependem dos IDs físicos. Credenciais, hashes de senha, cookies, nonces, logs e dados de outros usuários são excluídos.

`BackupProtector` usa criptografia autenticada com Sodium e chave exclusiva do SGFP fornecida por configuração protegida fora do banco e do arquivo. `BackupStore` grava em diretório privado fora do document root, com nome imprevisível, permissões mínimas e troca atômica. O arquivo só é preservado após ser reaberto, autenticado, decifrado e validado.

Essa escolha protege confidencialidade e integridade, mas vincula a restauração à disponibilidade da chave configurada. Custódia, cópia operacional e rotação da chave devem ser aprovadas no gate; sem esse procedimento, a implementação de `RF-021` não pode ser considerada recuperável.

O catálogo privado, em option não autoload, guarda apenas identificador opaco, usuário, origem, data, versão, hash e localização interna. Arquivo e catálogo precisam ser confirmados; falha remove o artefato incompleto. Rotas de recuperação expõem apenas cópias `pre_restore` do usuário atual e mantêm o arquivo cifrado.

### 13.2 Backup manual

O Service adquire o `UserOperationLock`, abre a transação somente leitura com snapshot consistente definida na seção 9 e exporta, por streaming, todos os dados do usuário dentro dessa visão. Depois de finalizar, reabrir, autenticar, decifrar e validar o contêiner temporário protegido, encerra a transação e libera o lock; somente então solicita envio ao e-mail de `wp_users`. Falha de leitura, serialização, proteção ou validação encerra a transação, descarta o temporário incompleto e não envia uma cópia parcial.

Sucesso do envio significa aceitação pelo transporte WordPress, não garantia de entrega externa. O temporário manual é removido após o fluxo; não há agendamento periódico. Retenção do lock não abrange o transporte de e-mail nem altera as decisões humanas ainda pendentes sobre limites de volume ou duração.

### 13.3 Restauração

1. receber arquivo em staging privado, com limites de bytes e registros e sem passar por uploads públicos;
2. validar por streaming e formato próprio, sem `unserialize`, inclusão de PHP ou extração de caminhos; autenticar/decriptar e verificar versão, proprietário, schema, enums, referências e completude, sem mutar estado;
3. retornar resumo e token opaco, curto, de uso único, ligado ao usuário, hash do arquivo e expiração;
4. após confirmação, adquirir o lock do usuário na conexão da unidade de trabalho e revalidar token/arquivo;
5. nessa conexão, abrir transação somente leitura com snapshot consistente, exportar o estado atual como `pre_restore` e encerrá-la somente após finalizar o contêiner lógico protegido;
6. sem liberar o lock, preservar o contêiner, reabrir, autenticar, decifrar e validar; qualquer falha cancela antes de tocar nos dados atuais;
7. ainda sob o mesmo lock, iniciar uma transação de escrita e substituir integralmente os dados SGFP e o tema do usuário, remapeando referências internas;
8. verificar contagens, integridade e estado importado antes do commit; falha provoca rollback;
9. após commit ou rollback, liberar o lock; no sucesso, invalidar caches WordPress e tentar enviar a cópia pré-restauração por e-mail. Falha isolada de e-mail é registrada e não reverte a restauração.

Não há mesclagem. Se a captura, proteção, persistência ou validação da cópia pré-restauração falhar, a transação de escrita não começa e o estado atual permanece inalterado. Se a substituição falhar, seu rollback preserva esse estado e a cópia `pre_restore` já validada pode permanecer como evidência recuperável. Perda do lock ou da conexão antes do commit causa cancelamento/rollback, nunca continuação em nova conexão.

A cópia pré-restauração permanece no storage privado mesmo se o e-mail falhar. Como não existe política canônica de retenção, nenhuma remoção automática é autorizada; o risco de crescimento do storage deve receber decisão humana antes da operação em produção.

## 14. Observabilidade e privacidade

Cada requisição recebe `correlation_id`. Eventos estruturados incluem horário UTC, operação, resultado, duração, usuário, tipo/ID interno do recurso e código de erro. Eventos prioritários: negação de acesso, rollback, efetivação/desfazimento, backup, restauração, migração e incompatibilidade.

Logs não contêm senha, nonce, cookie, chave, arquivo, payload integral, descrição livre ou valor financeiro. Detalhe técnico de falha inesperada fica somente no canal protegido do servidor; a API recebe mensagem genérica. Acesso e retenção dos logs seguem política operacional de privilégio mínimo, pois identificadores de usuário e recurso continuam sendo dados protegidos. Métricas mínimas: latência/erro por rota, rollbacks e duração/resultado de backup, restauração e migração.

## 15. Dependências, compatibilidade e seams de teste

### Runtime

- WordPress: REST, usuários, sessão, capability, usermeta e e-mail;
- PHP com JSON e Sodium;
- MySQL ou MariaDB com InnoDB e capacidades do Modelo Físico;
- HTTPS e storage privado gravável.

A topologia suportada pela V1 é uma instalação WordPress **single-site**, com múltiplos usuários isolados. Ativação em rede e compartilhamento de dados entre sites de WordPress Multisite ficam fora da matriz inicial porque não integram o escopo documentado; ampliar essa topologia exige decisão arquitetural posterior.

Versões mínimas, navegadores, carga esperada, latência máxima e disponibilidade ainda não têm base quantitativa canônica. O gate deve aprovar um perfil operacional e matriz de versões; a Etapa 10 então comprova capacidades e mede metas. Não se declara atendimento por suposição.

### Seams verificáveis

- handler de identidade: aceita somente e-mail no login, uniformiza todas as falhas de credencial e exige senha atual e confirmação na alteração;
- Controller: schema, DTO, status e erro com Service substituído;
- Service/política: invariantes, autorização e transação com portas substituídas;
- Repository/migração: MySQL e MariaDB reais, constraints, locks e concorrência;
- recorrência: projeção sem escrita, idempotência, pivô, quantidade, sucessor e preservação do passado;
- transferência: nenhum efeito unilateral, inclusive sob falha injetada;
- segurança: usuário cruzado, nonce, capability, enumeração e payload malformado;
- backup: round-trip, adulteração, chave errada, proprietário divergente, storage/e-mail falho, rollback e snapshot sob mutação concorrente;
- restauração: lock concorrente, cópia prévia obrigatória, perda do lock/conexão e substituição integral.

Para comprovar `CA-021.2` e `ARQ-014`, a Etapa 10 deverá executar testes de integração com banco real e duas conexões independentes, usando barreiras controláveis em vez de depender de temporização:

1. pausar o backup entre leituras de relações vinculadas, tentar uma mutação completa na segunda conexão e demonstrar, por restauração/round-trip, que a cópia contém somente o estado anterior enquanto a mutação espera ou recebe conflito;
2. confirmar a mutação antes da aquisição do lock pelo backup e demonstrar que a cópia contém somente o estado posterior completo;
3. durante uma restauração, tentar mutar depois da captura `pre_restore` e antes do commit, comprovando que a mutação não atravessa o lock e que a cópia corresponde exatamente ao estado substituído;
4. injetar falhas na leitura, serialização, proteção, persistência, reabertura/validação e substituição, além de perda da conexão, verificando descarte do temporário incompleto, ausência de e-mail parcial, cancelamento ou rollback e inexistência de estado misto.

Testes podem acompanhar a Etapa 10. A Etapa 12 consolida estratégia, evidências e rastreabilidade; `ISSUE-008` permanece não bloqueadora agora e obrigatória antes de seu fechamento.

### Revisão funcional desta proposta

| Baseline ativa | Cobertura arquitetural revisada |
| --- | --- |
| `RF-001`, `RF-002`, `RF-003` | fluxos WordPress, login exclusivamente por e-mail e senha, erro uniforme, alteração mediante senha atual e confirmação, provisionamento e contexto autenticado |
| `RF-004`, `RF-005` | contas sem exclusão, saldo inicial e projeções derivadas |
| `RF-006`, `RF-007`, `RF-008` | compromissos, efetivação/desfazimento e recorrência mensal |
| `RF-009`, `RF-010`, `RF-011` | categorias opcionais, lançamentos e consulta sem escrita |
| `RF-012`, `RF-013`, `RF-014` | transferências e dois efeitos atômicos, inclusive recorrentes |
| `RF-015` e `RF-016` | cartão/parcelamento reutilizam compromissos e recorrência |
| `RF-017` e `RF-018` | Dashboard e navegação por período como projeções |
| `RF-020` | tema em `usermeta` do usuário atual |
| `RF-021` | backup manual, e-mail e restauração integral protegida |

Não foi encontrada capacidade ativa sem componente ou superfície correspondente. `RF-019` permanece explicitamente fora da V1. A revisão preservou categoria opcional, ausência de exclusão de conta, saldo negativo permitido, histórico por desfazimento e ausência de escrita em consultas.

### Revisão de segurança desta proposta

| Ameaça/fronteira | Controle arquitetural | Evidência futura |
| --- | --- | --- |
| acesso cruzado por ID | usuário somente da sessão, busca `id + usuário`, `404` uniforme e FKs compostas | testes com dois usuários em cada Repository/rota |
| CSRF ou rota sem permissão | HTTPS, cookie + nonce e `permission_callback` em toda rota | testes de nonce ausente/inválido e capability |
| SQL injection/identificador físico | valores preparados e nomes apenas de `TableNames` | inspeção e casos malformados |
| replay/corrida financeira | locks, transação, constraints e comandos idempotentes | concorrência e falha injetada |
| arquivo hostil ou adulterado | staging privado, limites, parser sem desserialização, AEAD e validação integral | corpus inválido, adulterado e superdimensionado |
| backup sob mutação concorrente | `UserOperationLock` + transação somente leitura com snapshot consistente | duas conexões, barreiras entre relações e round-trip anterior/posterior |
| restauração concorrente | `UserOperationLock`, cópia prévia validada e rollback | mutação paralela e falhas por etapa |
| exposição por log/erro | erro público estável, correlação e redação | inspeção de respostas e logs |

Não foi identificado bloqueador de segurança para validar o desenho. As decisões de custódia de chave, retenção de backups/logs, perfil operacional e limites quantitativos foram registradas em `DEC-003` e `DEC-004` e devem ser respeitadas na implementação e na liberação em produção.

## 16. Registro de decisões

| ID | Decisão | Rastreabilidade principal |
| --- | --- | --- |
| `ARQ-001` | monólito modular em plugin próprio | `RE-016`, `RNF-015`, `RNF-020` |
| `ARQ-002` | REST → Controller → Service → portas/Repository | `DEP-004`, `RNF-015` |
| `ARQ-003` | identidade/sessão WordPress; login somente por e-mail e senha, falha uniforme e troca protegida por senha atual e confirmação; usuário financeiro só do contexto | `RF-001` a `RF-003`; `UC-002`, `UC-003`; `RNF-001` a `RNF-003` |
| `ARQ-004` | capability + autorização escopada por recurso | `RNF-001`, `RNF-003`, `RNF-004` |
| `ARQ-005` | seis tabelas próprias prefixadas e fiéis ao Modelo Físico | Etapas 6–8, `DEP-002` |
| `ARQ-006` | migrador versionado com verificação de capacidade | `RNF-005`, `RNF-020` |
| `ARQ-007` | unidade transacional por caso de uso crítico | `RNF-005`, `RNF-006` |
| `ARQ-008` | lançamento ativo/desfeito preserva histórico | `RF-007`, `RF-010`, `RNF-007` |
| `ARQ-009` | recorrência por projeção de leitura e materialização por comando | `RF-008`, `RF-014`, `RF-016`, `CA-011.4` |
| `ARQ-010` | transferência confirma dois efeitos atômicos | `RF-012`, `RF-013`; `TRF-RN-001` a `TRF-RN-024` |
| `ARQ-011` | saldo, patrimônio e Dashboard derivados | `RF-005`, `RF-017`, `RE-010` |
| `ARQ-012` | backup lógico versionado e cifrado/autenticado | `RF-021`, `RNF-017`, `RNF-018` |
| `ARQ-013` | storage privado recuperável antes da restauração | `CA-021.6`, `CA-021.9` a `CA-021.11` |
| `ARQ-014` | lock por usuário e snapshot transacional impedem estado misto no backup e excluem mutações durante restauração | `RNF-005`, `RNF-006`; `CA-021.2`, `CA-021.5` a `CA-021.9` |
| `ARQ-015` | erros estáveis e observabilidade redigida | `RNF-004`, `RNF-019` |
| `ARQ-016` | provisionamento inicial idempotente | `RF-001`, `RF-009`, `CA-009.1` |
| `ARQ-017` | implantação V1 em WordPress single-site | `DEP-001`, `DEP-005`, `RNF-014` |
| `ARQ-018` | lifecycle falha fechado e desinstalação preserva dados | integridade; gate Etapa 9 |
| `ARQ-019` | navegador same-origin; REST como única fronteira SGFP | `DEP-004`, `RNF-001`, `RNF-020` |

## 17. Alternativas, riscos e decisões humanas registradas

As decisões abaixo foram tomadas em 11/09/2026 e incorporadas a esta baseline. Elas mantêm a V1 enxuta, preservam integridade referencial e definem um perfil operacional testável.

### 17.1 Decisões registradas

| ID | Tema | Decisão | Rastreabilidade |
| --- | --- | --- | --- |
| `DEC-001` | Conta Principal | A primeira conta criada torna-se automaticamente `PRINCIPAL`. Contas subsequentes são `SECUNDARIA`. Compromissos normais incidem na `PRINCIPAL` por padrão; transferências limitam-se ao par `PRINCIPAL`–`SECUNDARIA`. | Modelo Físico `CONTA_FINANCEIRA.PAPEL`; `RF-005`; `UC-005` |
| `DEC-002` | Exclusão de usuário | Permitida com **dupla confirmação** e **apagamento total** da identidade WordPress e dos dados SGFP. A exclusão só ocorre após confirmação explícita de que o usuário está ciente da perda total dos dados. | `RF-001` a `RF-003`; integridade referencial do Modelo Físico (sem `ON DELETE CASCADE`) |
| `DEC-003` | Matriz de compatibilidade | PHP **8.1+**, WordPress **6.4+**, MySQL **8.0.16+** ou MariaDB **10.6+**, WordPress single-site. Navegadores: 2 últimas versões de Chrome, Firefox, Safari e Edge; IE não suportado. | `RNF-001`, `RNF-003`, `RNF-014`, `DEP-001`, `DEP-005` |
| `DEC-004` | Backup e restauração | Chave via variável de ambiente `SGFP_BACKUP_KEY` (fallback para KMS/secret manager). Formato JSON compactado com gzip. Cópia pré-restauração: **1 snapshot**, mantido por **24 horas** ou até a próxima tentativa de restauração. Limite de anexo por e-mail: **25 MB** (aviso em 20 MB). | `RF-021`, `RNF-017`, `RNF-018`; `CA-021.6` a `CA-021.11` |
| `DEC-005` | Categorias iniciais | Conjunto mínimo criado automaticamente na primeira conta: **Receitas**: Salário, Investimentos, Outras Receitas; **Despesas**: Moradia, Alimentação, Transporte, Saúde, Educação, Lazer, Vestuário, Serviços, Impostos, Outras Despesas. | `RF-011`; `UC-011`; associação opcional a compromissos |

### 17.2 Alternativas não adotadas

| Tema | Alternativa não adotada | Motivo |
| --- | --- | --- |
| persistência | posts/postmeta para finanças | diverge do Modelo Físico e enfraquece integridade |
| acesso a dados | `$wpdb` em Controllers ou Repository genérico | espalha SQL e facilita perda de escopo do usuário |
| recorrência | escrita em `GET`, cron ou horizonte fixo | viola leitura segura ou inventa horizonte não definido |
| esquema | somente `dbDelta()` | não garante todas as construções validadas |
| saldo | coluna/cache autoritativo | cria segunda fonte de verdade |
| restauração | validar enquanto importa ou depender só do e-mail | viola atomicidade e recuperação prévia |
| arquitetura | serviços distribuídos | complexidade operacional sem necessidade demonstrada |
| Conta Principal | escolha explícita obrigatória na primeira conta | aumenta fricção sem ganho demonstrado na V1 |
| Exclusão de usuário | retenção/anonimização dos dados financeiros | exige política formal de privacidade fora do escopo V1 |
| Backup | retenção longa ou múltiplos snapshots | aumenta espaço e complexidade operacional; 1 snapshot/24h é suficiente para proteção pré-restauração |

## 18. Referências técnicas verificadas

- [WordPress REST API — Adding Custom Endpoints](https://developer.wordpress.org/rest-api/extending-the-rest-api/adding-custom-endpoints/): registro de rotas, respostas e `permission_callback`;
- [WordPress REST API — Authentication](https://developer.wordpress.org/rest-api/using-the-rest-api/authentication/): sessão por cookie e nonce REST;
- [WordPress REST API — Routes and Endpoints](https://developer.wordpress.org/rest-api/extending-the-rest-api/routes-and-endpoints/): semântica e ausência de efeitos colaterais em leitura;
- [WordPress — Activation / Deactivation Hooks](https://developer.wordpress.org/plugins/plugin-basics/activation-deactivation-hooks/): callbacks de ativação e desativação;
- [WordPress — Uninstall Methods](https://developer.wordpress.org/plugins/plugin-basics/uninstall-methods/): distinção entre desativação e remoção destrutiva;
- [WordPress — `dbDelta()`](https://developer.wordpress.org/reference/functions/dbdelta/): comportamento e limites da evolução automática de tabelas;
- [PHP — Sodium](https://www.php.net/sodium): capacidade criptográfica adotada para proteção autenticada.

Essas referências orientam o mecanismo técnico e permanecem subordinadas às fontes canônicas do SGFP.

## 19. Gate e passagem para a Etapa 10

**Veredito integrado em 11/09/2026:** **APROVADO** para o gate integral da Etapa 9.

As cinco decisões humanas foram registradas (`DEC-001` a `DEC-005`), os pontos afetados foram incorporados a este artefato e a revisão integral confirmou fidelidade ao Modelo Físico, coerência com `ARQ-001` a `ARQ-019` e resolução do bloqueador técnico de consistência do snapshot (`ARQ-014`).

A Etapa 10 — Desenvolvimento da API está autorizada a iniciar e deverá:

- criar plugin, classes, migrações, Composer e testes;
- verificar a matriz de versões e capacidades em instalações reais;
- registrar rotas e publicar OpenAPI coerente com este contrato;
- implementar Services, Repositories, adaptadores, proteção e observabilidade;
- medir critérios não funcionais no perfil aprovado (`DEC-003`).

A Etapa 11 — Desenvolvimento da Interface Web permanece condicionada aos contratos aprovados desta arquitetura. A `ISSUE-008` continua não bloqueadora para a Etapa 9 e deverá ser resolvida antes do fechamento formal da rastreabilidade de testes na Etapa 12.
