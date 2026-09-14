# SGFP — Arquitetura da Aplicação V1

**Etapa:** 9 — Arquitetura da Aplicação

**Status:** baseline revisada em 14/09/2026; implementação das Etapas 10/11 requer reconciliação com esta revisão

**Baseline:** RF-001 a RF-023; RF-012 a RF-014 e RF-019 futuros/inativos
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
- saldo e Dashboard sempre derivados;
- recorrência exclusivamente mensal e histórico anterior preservado;
- uma Conta Financeira por usuário, provisionada automaticamente como `Minha Conta`;
- backup ordinário manual por ZIP para download local e proteção automática pré-restauração;
- reset do perfil e exclusão da conta de acesso com dupla confirmação.

### Fora desta arquitetura V1

PIN, múltiplas contas, transferências, Patrimônio Total, integração bancária, sincronização/armazenamento externo obrigatório, relatórios específicos e aplicativo móvel.

## 3. Contexto e fronteiras de confiança

```text
Navegador
   │ HTTPS + cookie de sessão + nonce REST
   ▼
WordPress ── identidade, sessão, capabilities, REST e e-mail de recuperação
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
Portas de saída (repositories, transação, relógio, backup, identidade WordPress, log)
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
    Accounts/               conta única, saldo inicial e saldo
    Categories/             categorias e desvinculação
    Commitments/            compromissos, efetivação e desfazimento
    Recurrences/            projeção e comandos sobre ocorrências mensais
    Reporting/              movimentações e Dashboard
    Preferences/            tema do usuário
    Backup/                 ZIP local, validação e restauração
  Domain/                   políticas e Value Objects justificados
  Infrastructure/
    Persistence/            repositories, transações, locks e migrações
    WordPress/              usuário atual, capability, recuperação de senha e usermeta
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

| Capacidade | Superfície relativa a `/sgfp/v1` | Regra |
| --- | --- | --- |
| Identidade/senha | fluxos WordPress + `POST /onboarding` quando necessário | login por e-mail/senha; provisionamento cria conta/categorias |
| Conta | `GET /account`; `PATCH /account` | uma única conta; sem `POST` de conta adicional e sem `DELETE` isolado |
| Saldo inicial | `POST /account/initial-balance` | cria lançamento `SALDO_INICIAL` na conta única |
| Categorias | `GET/POST /categories`; `PATCH/DELETE /categories/{id}` | categoria opcional; exclusão desvincula compromissos |
| Compromissos | `GET/POST /commitments`; `GET/PATCH/DELETE /commitments/{id}` | efetivado exige desfazimento para editar/excluir |
| Efetivação | `POST /commitments/{id}/effectuation`; `POST .../undo-effectuation` | efeito somente via Lançamento |
| Recorrências | rotas de ocorrência/série | periodicidade mensal; leitura não grava |
| Consultas | `GET /movements`; `GET /dashboard` | saldo e projeções derivados; sem `/net-worth` |
| Tema | `GET/PUT /preferences/theme` | `light|dark` |
| Backup | `POST /backups` | retorna/download de ZIP local; sem e-mail |
| Restauração | `POST /restore-validations`; `POST /restorations` | valida primeiro; substituição integral protegida |
| Reset | `POST /profile-reset-validations`; `POST /profile-reset` | dupla confirmação + `RESETAR PERFIL` |
| Excluir conta | `POST /account-deletion-validations`; `DELETE /account-access` | dupla confirmação + `EXCLUIR CONTA`; remove dados + login |

RF-012 a RF-014 não possuem superfície V1. A implementação atual que ainda expõe transferências ou múltiplas contas deverá ser removida/migrada.

### 7.3 Representações mínimas

| Recurso | Campos públicos estáveis |
| --- | --- |
| conta | `id`, `name`, `created_at`; `balance` somente projeção |
| categoria | `id`, `name`, `created_at` |
| compromisso | `id`, `name`, `amount`, `nature`, `month`, `status`, `category_id` anulável, recorrência anulável |
| recorrência | `id`, `starts_in`, `months_count` anulável, `ended_in` anulável |
| lançamento | `id`, `commitment_id` anulável, `account_id`, `origin`, `name`, `amount`, `effect`, `effective_at`, `description`, `state`, `undone_at` |
| Dashboard | `month`, `opening_balance`, `expected_inflows`, `expected_outflows`, `expected_closing_balance`, compromissos |
| backup | arquivo ZIP autenticado/versionado |
| validação de restauração | token, expiração, origem/data e resumo; nunca chave/conteúdo interno |

### 7.4 Fronteira com clientes

A interface V1 e qualquer cliente futuro consomem somente REST ou os fluxos nativos de identidade: não acessam banco, `$wpdb`, arquivos internos nem `admin-ajax` como API paralela. Cálculos, autorização, estados e invariantes permanecem no backend; o cliente apenas valida para usabilidade e nunca é autoridade.

O navegador V1 opera na mesma origem WordPress com cookie e `X-WP-Nonce`; CORS amplo não é habilitado. Um futuro mobile exigirá decisão separada sobre autenticação suportada pelo WordPress, revogação, CORS e armazenamento de credencial, sem alterar Services ou Repositories.

## 8. Persistência e evolução do esquema

### 8.1 Adaptação fiel

Permanecem cinco estruturas financeiras oficiais: `CONTA_FINANCEIRA`, `CATEGORIA`, `RECORRENCIA`, `COMPROMISSO_FINANCEIRO` e `LANCAMENTO_FINANCEIRO`. `TRANSFERENCIA` foi retirada da V1. `CONTA_FINANCEIRA.FK_ID_USUARIO` é único.

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

Para produzir um backup, depois de adquirir o lock, o `BackupService` inicia na mesma conexão uma transação somente leitura em `REPEATABLE READ` com snapshot consistente. Todas as consultas às cinco tabelas financeiras InnoDB, filtradas pelo usuário, são consumidas pelo `BackupCodec` dentro dessa única visão; o tema SGFP também é lido enquanto o lock permanece retido. A transação de leitura só termina depois que o último registro lógico foi serializado no contêiner temporário protegido. Nenhum Repository do exportador pode abrir outra conexão ou executar uma leitura fora dessa transação.

Se uma mutação obtiver o lock primeiro, o backup começa apenas depois de seu commit e representa o estado posterior completo. Se o backup obtiver o lock primeiro, a mutação aguarda ou recebe conflito após o timeout e o contêiner representa integralmente o estado anterior. Assim, nenhuma cópia pode combinar registros de antes e depois da mesma mutação. A entrega do ZIP ao navegador ocorre depois da captura/validação e não altera o ponto temporal capturado.

Na restauração, o mesmo lock permanece sob a mesma conexão desde a captura da cópia `pre_restore` até o commit ou rollback da substituição integral. A transação somente leitura da captura e a transação de escrita da restauração são distintas, mas não existe intervalo desbloqueado entre elas; portanto, nenhuma mutação pode ocorrer entre o estado preservado e o estado imediatamente substituído.

## 10. Fluxos financeiros críticos

### 10.1 Compromisso e lançamento

Compromisso pendente incide sobre a Conta Financeira única. Criar o compromisso não altera saldo. A efetivação cria um Lançamento `ATIVO` de mesma natureza e muda o compromisso para `EFETIVADO` na mesma transação. O desfazimento marca o lançamento como `DESFEITO`, preenche `DESFEITO_EM` e retorna o compromisso a `PENDENTE`.

Saldo inicial é comando próprio na conta única: `ORIGEM=SALDO_INICIAL`, `TIPO_EFEITO=ENTRADA`, no máximo um ativo por conta, valor positivo, zero ou negativo.

### 10.2 Transferência — fora da V1

Não há fluxo arquitetural de Transferência na V1. RF-012 a RF-014 permanecem somente como possibilidade futura.

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

A estratégia atende planejamento futuro dos Compromissos recorrentes sem cron, horizonte arbitrário ou escrita causada por `GET`.

## 12. Saldo e Dashboard

Repositories calculam com `DECIMAL`, considerando somente Lançamentos `ATIVO`:

- saldo da conta = entradas menos saídas;
- saldo de abertura = efeitos anteriores ao primeiro instante do mês;
- saldo final previsto = saldo de abertura + entradas previstas − saídas previstas;
- Dashboard = composição dessas projeções e compromissos do período.

Não existe tabela/cache autoritativo de saldo. Patrimônio Total não integra a V1.

## 13. Backup local, restauração, reset e exclusão

### 13.1 Formato e proteção

O backup é um contêiner lógico versionado e protegido, entregue ao usuário dentro de **ZIP**. Credenciais, senha/hash, cookies, nonces, logs e dados de outros usuários são excluídos. O ZIP é embalagem; integridade/confidencialidade do conteúdo continuam sob `BackupProtector` (Sodium ou mecanismo equivalente aprovado).

### 13.2 Backup manual

Sob `UserOperationLock`, o Service captura snapshot consistente das cinco tabelas financeiras e preferências SGFP, serializa/protege, valida o artefato e então disponibiliza o ZIP para download autenticado pelo navegador. Não há envio por e-mail nem retenção periódica do backup manual no servidor.

### 13.3 Restauração

1. receber ZIP em staging privado;
2. validar tamanho, formato, proteção, versão, proprietário, schema e referências sem mutar dados;
3. retornar resumo/token opaco;
4. após confirmação, adquirir lock e revalidar;
5. gerar e preservar cópia `pre_restore` recuperável antes de qualquer substituição;
6. se a cópia falhar, cancelar;
7. substituir integralmente dados SGFP e preferências em transação;
8. verificar invariantes antes do commit;
9. disponibilizar a cópia `pre_restore` para download local conforme política de retenção temporária.

Não há mesclagem e não há dependência de e-mail.

### 13.4 Reset do perfil

Reset exige duas etapas, com frase final exata `RESETAR PERFIL`. Sob lock/transação, remove os dados SGFP e preferências e reprovisiona `Minha Conta` + categorias padrão. A identidade WordPress permanece.

### 13.5 Exclusão da conta de acesso

Exclusão exige duas etapas, com frase final exata `EXCLUIR CONTA`. O serviço remove dados SGFP em ordem segura e conclui a remoção da identidade/login WordPress. A operação somente pode ser reportada como concluída quando o acesso estiver efetivamente encerrado.

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

| Baseline ativa | Cobertura arquitetural |
| --- | --- |
| RF-001 a RF-003 | identidade WordPress, autenticação, senha e provisionamento |
| RF-004, RF-005 | conta única, saldo inicial por lançamento e saldo derivado |
| RF-006 a RF-011 | compromissos, recorrência, categorias, lançamentos e consultas |
| RF-012 a RF-014 | **fora da V1** |
| RF-015 a RF-018 | casos específicos, Dashboard e períodos |
| RF-019 | **futuro** |
| RF-020 | tema |
| RF-021 | ZIP local + restauração integral protegida |
| RF-022 | reset do perfil |
| RF-023 | exclusão da conta de acesso |

A implementação antiga de múltiplas contas/transferências/net-worth/backup por e-mail é dívida de migração após esta revisão.

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
| `ARQ-005` | cinco tabelas financeiras; conta única por `UNIQUE(FK_ID_USUARIO)` | Etapas 6–8, `DEP-002` |
| `ARQ-006` | migrador versionado com verificação de capacidade | `RNF-005`, `RNF-020` |
| `ARQ-007` | unidade transacional por caso de uso crítico | `RNF-005`, `RNF-006` |
| `ARQ-008` | lançamento ativo/desfeito preserva histórico | `RF-007`, `RF-010`, `RNF-007` |
| `ARQ-009` | recorrência por projeção de leitura e materialização por comando | `RF-008`, `RF-016`, `CA-011.4` |
| `ARQ-010` | exatamente uma Conta Financeira por usuário | `RF-004`, `RE-005` |
| `ARQ-011` | saldo e Dashboard derivados | `RF-005`, `RF-017`, `RE-010` |
| `ARQ-012` | backup lógico protegido entregue em ZIP local | `RF-021`, `RNF-017`, `RNF-018` |
| `ARQ-013` | storage privado recuperável antes da restauração | `CA-021.6`, `CA-021.9` a `CA-021.11` |
| `ARQ-014` | lock por usuário e snapshot transacional impedem estado misto no backup e excluem mutações durante restauração | `RNF-005`, `RNF-006`; `CA-021.2`, `CA-021.5` a `CA-021.9` |
| `ARQ-015` | erros estáveis e observabilidade redigida | `RNF-004`, `RNF-019` |
| `ARQ-016` | provisionamento inicial idempotente de Minha Conta + categorias | `RF-001`, `RF-004`, `RF-009` |
| `ARQ-017` | implantação V1 em WordPress single-site | `DEP-001`, `DEP-005`, `RNF-014` |
| `ARQ-018` | reset e exclusão de acesso usam confirmação explícita e limpeza coordenada | `RF-022`, `RF-023` |
| `ARQ-019` | navegador same-origin; REST como única fronteira SGFP | `DEP-004`, `RNF-001`, `RNF-020` |

## 17. Alternativas, riscos e decisões humanas registradas

As decisões abaixo foram tomadas em 11/09/2026 e incorporadas a esta baseline. Elas mantêm a V1 enxuta, preservam integridade referencial e definem um perfil operacional testável.

### 17.1 Decisões registradas

| ID | Tema | Decisão |
| --- | --- | --- |
| DEC-001 | Conta | exatamente uma conta, criada como `Minha Conta`; renomeável; sem papel principal/secundária |
| DEC-002 | Operações destrutivas | reset preserva login; exclusão remove dados + login; ambas com dupla confirmação |
| DEC-003 | Compatibilidade | mantém matriz técnica previamente aprovada |
| DEC-004 | Backup | ZIP local; conteúdo protegido/versionado; pre_restore temporário recuperável; sem e-mail |
| DEC-005 | Categorias iniciais | conjunto padrão permanece provisionado por usuário |
| DEC-006 | Transferências/Patrimônio | ambos fora da V1 |

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
| múltiplas contas | manter Principal/Secundárias na V1 | complexidade removida pela decisão de conta única |
| Exclusão de usuário | retenção/anonimização dos dados financeiros | exige política formal de privacidade fora do escopo V1 |
| Backup | envio por e-mail | dependência desnecessária; V1 usa download local em ZIP |

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
