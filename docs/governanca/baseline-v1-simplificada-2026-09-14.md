# Baseline V1 simplificada — 14/09/2026

**Status:** decisão humana vigente e normativa para a V1  
**Escopo:** regras de negócio, requisitos, casos de uso, modelagem, arquitetura, API e interface

Este documento registra as decisões que **substituem** as regras anteriores incompatíveis. Artefatos históricos podem mencionar conta principal/secundária, transferências, patrimônio ou backup por e-mail; essas menções devem ser interpretadas como histórico quando não tiverem sido atualizadas.

## 1. Conta financeira única

- Cada usuário possuirá **exatamente uma Conta Financeira** na V1.
- A conta será criada automaticamente no cadastro/provisionamento inicial do usuário.
- O nome inicial será **"Minha Conta"**.
- O usuário poderá renomeá-la.
- A V1 não permitirá criar contas adicionais nem excluir a Conta Financeira isoladamente.
- Não existem papéis `PRINCIPAL` e `SECUNDARIA` na V1 simplificada.

## 2. Saldo

- O saldo pertence conceitualmente à única Conta Financeira do usuário.
- O saldo **não será armazenado como atributo** da conta.
- O saldo será derivado dos Lançamentos Financeiros ativos associados à conta.
- O valor existente no início da utilização, quando informado, será representado por um Lançamento Financeiro de origem `SALDO_INICIAL`.
- A conta inicia em R$ 0,00 enquanto não houver lançamento com efeito financeiro.

## 3. Transferências e patrimônio

- Transferências entre contas saem do escopo ativo da V1, pois a V1 possui apenas uma conta por usuário.
- `RF-012`, `RF-013`, `RF-014`, `UC-013` e regras `TRF-RN-*` permanecem preservados apenas como histórico/possível versão futura.
- A entidade/tabela `TRANSFERENCIA` não pertence ao modelo ativo da V1 simplificada.
- Patrimônio Total sai do escopo ativo da V1. Com uma única conta, o valor seria redundante com o saldo.
- `UC-022` permanece preservado apenas como histórico/possível versão futura.

## 4. Backup local

- O backup ordinário continua manual.
- O resultado entregue ao usuário será um arquivo **ZIP** baixado localmente pelo navegador.
- O envio de backup por e-mail deixa de fazer parte da V1.
- O ZIP é o contêiner de entrega; o conteúdo do backup deverá ser versionado, validável e protegido contra adulteração/acesso não autorizado conforme a arquitetura.
- A restauração recebe um arquivo de backup ZIP válido e continua sendo integral, sem mesclagem.
- A proteção pré-restauração permanece: antes de substituir os dados, o SGFP gera uma cópia do estado atual. Se essa cópia não puder ser gerada e preservada de forma recuperável, a restauração é cancelada.
- A cópia pré-restauração usa o mesmo formato lógico de backup e deverá poder ser disponibilizada ao usuário para download local. Não depende de e-mail.

## 5. Reset do perfil financeiro

A V1 passa a oferecer **Resetar perfil financeiro**.

- O login WordPress é preservado.
- Todos os dados SGFP do usuário são apagados.
- Preferências SGFP retornam aos valores padrão.
- O sistema reprovisiona o estado inicial: uma conta `Minha Conta`, categorias padrão e ausência de movimentações/compromissos.
- A ação exige duas etapas de confirmação; na etapa final o usuário deverá digitar exatamente **`RESETAR PERFIL`**.
- Backups já baixados no computador do usuário não são apagados pelo SGFP.

## 6. Exclusão da conta de acesso

A V1 passa a oferecer **Excluir conta**.

- A operação significa encerrar totalmente o acesso ao portal.
- Todos os dados SGFP do usuário são removidos.
- A identidade/login correspondente em `wp_users` é removida.
- A ação exige duas etapas de confirmação; na etapa final o usuário deverá digitar exatamente **`EXCLUIR CONTA`**.
- A exclusão de conta não é restauração nem reset e não pode ser desfeita pelo portal.
- Um backup local não recria automaticamente um login excluído na V1.

## 7. Provisionamento inicial

Após cadastro válido:

1. WordPress cria a identidade do usuário;
2. SGFP cria a Conta Financeira única chamada `Minha Conta`;
3. SGFP cria as categorias padrão vinculadas ao usuário;
4. o usuário pode acessar o gestor em estado válido.

Não deve existir usuário SGFP ativo sem sua conta única provisionada.

## 7.1 Identidade, cadastro e autorização da aplicação

A decisão complementar de 16/09/2026 está registrada em [decisao-identidade-acesso-v1-2026-09-16.md](decisao-identidade-acesso-v1-2026-09-16.md) e integra esta baseline.

- O cadastro WordPress é o cadastro da aplicação SGFP.
- Não existe tabela própria de usuário SGFP; `wp_users.ID` é a identidade utilizada pelas tabelas financeiras.
- Login, cadastro (quando habilitado) e recuperação de senha reutilizam os fluxos nativos do WordPress.
- O provisionamento SGFP é disparado após a criação da identidade e deve ser idempotente; `wp_login` pode reparar provisionamento incompleto sem duplicar conta ou seed.
- A capability oficial única da V1 é `use_sgfp`.
- `sgfp_access` é legado de migração e não autoriza runtime por si só.
- O manager completo só é disponibilizado a usuário autenticado, provisionado e autorizado por `use_sgfp`.
- O frontend deve distinguir estados anônimo, não autorizado/não provisionado e autorizado.

## 8. Efeito sobre a baseline anterior

Esta decisão substitui explicitamente:

- múltiplas contas;
- Conta Principal/Contas Secundárias;
- transferências na V1;
- patrimônio total na V1;
- backup manual entregue por e-mail;
- decisão anterior de não excluir a Conta Financeira como parte de um reset/exclusão total do perfil.

Os identificadores históricos são preservados quando úteis à rastreabilidade, mas seu status deve indicar `deferred_future`, `historical` ou equivalente quando saírem da V1.

## 9. Implementação

A documentação foi atualizada antes do código. Posteriormente, o backend da Etapa 10 foi reconciliado com esta baseline na branch `feat/stage-9-10-backend` e validado por lint, PHPUnit e integração WordPress + MySQL; a trilha está no Draft PR #12.

A Etapa 11/frontend continua em reconciliação. Qualquer código remanescente que ainda reflita a baseline anterior deve ser tratado como **divergência de implementação a reconciliar**, não como autorização para reverter esta decisão.

A branch 9–10 permanece aberta para ajustes legítimos descobertos durante a integração da Etapa 11, desde que a alteração seja documentada, testada e reintegrada de forma controlada.

**Data da decisão:** 14/09/2026
