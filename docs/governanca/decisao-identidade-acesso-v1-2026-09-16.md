# Decisão V1 — Identidade, cadastro, acesso e capability SGFP

**Origem:** PR #14, decisão de 16/09/2026.  
**Reconciliação:** 17/10/2026, código publicado v1.0.15.  
**Natureza:** norma de identidade e registro das diferenças entre requisito e execução verificada.

## Identidade e modelo

WordPress mantém a autoridade sobre usuários, credenciais, cadastro, sessão e recuperação de senha. O SGFP não possui tabela nem credenciais próprias de usuário; identifica cada pessoa por `wp_users.ID` e usa `fk_id_usuario` nas tabelas financeiras. `Schema.php` inclui `fk_conta_usuario` apontando aos usuários WordPress e `uq_conta_usuario`, garantindo unicidade da conta financeira por usuário.

## Cadastro e provisionamento

1. O WordPress cria a identidade e dispara `user_register`.
2. `Plugin::provisionUser` garante idempotentemente `Minha Conta` e as categorias iniciais.
3. Após sucesso, marca `_sgfp_provisioned = '1'` e concede `use_sgfp`.
4. `wp_login` pode repetir o processo para reparar provisionamento incompleto, sem duplicar conta/categorias.

Se o provisionamento financeiro falhar, a identidade WordPress pode estar criada, mas isso não autoriza o gestor SGFP.

## Capability de runtime e estados

A capability vigente de acesso financeiro é `use_sgfp`. A nomenclatura antiga `sgfp_access` não concede autorização independente. O acesso aos assets/shell do gestor e às operações financeiras exige autenticação, `_sgfp_provisioned` e `use_sgfp`.

Há três situações: anônimo (redirecionado ao login WordPress ao acessar a página do SGFP); autenticado sem provisionamento/autorização (tela de indisponibilidade); e autorizado (gestor completo).

**Diferença em relação à migração histórica:** o plugin 1.0.15 também concede `use_sgfp` a determinados papéis WordPress e a usuários provisionados. Não se verificou concessão condicionada exclusivamente à posse anterior de `sgfp_access`. A decisão histórica não deve ser descrita como mecanismo já implementado.

## Autenticação, cadastro e recuperação

Esses fluxos são executados pelo WordPress nativo, sem endpoint SGFP de credenciais. O código `WordPressLogin.php` pode personalizar a apresentação/retorno sem substituir a autenticação.

**Pendência de conformidade:** a V1 exige exclusivamente e-mail e senha. Na versão 1.0.15 não foi identificado filtro SGFP que impeça login pelo nome de usuário WordPress; a restrição deve ser testada e, se necessário, implementada em uma versão futura, sem alterar o release estável.

## Endpoint legado de onboarding

A rota `POST /onboarding`, mencionada em documentação anterior, **não é registrada** em `Routes.php` na v1.0.15. O reparo identificado ocorre nos hooks WordPress `user_register` e `wp_login`. Não anunciar essa rota como disponível.

## Precedência

A [baseline V1](baseline-v1-simplificada-2026-09-14.md) mantém autoridade sobre o domínio financeiro; este documento consolida a identidade/acesso; o [registro de release](STATUS-RELEASE-1.0.15-2026-10-17.md) identifica a entrega verificável. Checkpoints históricos não prevalecem sobre código atual e decisões normativas válidas.

Esta reconciliação é estritamente documental e **não modifica o comportamento do plugin**.
