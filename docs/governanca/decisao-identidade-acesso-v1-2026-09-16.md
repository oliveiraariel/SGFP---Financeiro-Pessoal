# Decisão V1 — Identidade, cadastro, acesso e capability SGFP

**Data:** 16/09/2026  
**Status:** decisão vigente para harmonização da Etapa 11  
**Escopo:** identidade, autenticação, cadastro, provisionamento, tela inicial e autorização do gestor SGFP

## 1. Identidade do usuário

- O WordPress é a fonte autoritativa de identidade, credenciais, sessão, cadastro e recuperação de senha.
- O SGFP não cria tabela própria de usuário, não duplica login/senha e não mantém identidade paralela.
- `wp_users.ID` é o identificador do usuário usado pelo SGFP.
- As tabelas de domínio SGFP referenciam diretamente `wp_users.ID` por `fk_id_usuario`.
- A Conta Financeira da V1 permanece única por usuário e é provisionada como `Minha Conta`.

## 2. Cadastro e provisionamento

O cadastro WordPress é o cadastro da aplicação SGFP.

Fluxo canônico:

```text
WordPress cria wp_users
    -> user_register
    -> provisionamento SGFP idempotente
    -> cria Minha Conta se ausente
    -> cria/garante apenas dados iniciais previstos pela V1
    -> marca _sgfp_provisioned
    -> concede use_sgfp
    -> usuário pode entrar no gestor
```

O provisionamento deve ser idempotente. Repetições, inclusive por reparo no `wp_login`, não podem criar segunda conta nem duplicar o seed inicial.

## 3. Capability oficial

A única capability oficial de autorização SGFP na V1 é:

```text
use_sgfp
```

`sgfp_access` é legado de implementação/documentação anterior.

Regra de compatibilidade:

- `sgfp_access` pode ser lida somente para migração de usuário legado;
- usuário legado com `sgfp_access` e sem `use_sgfp` pode receber `use_sgfp` por migração idempotente;
- `sgfp_access` isoladamente não autoriza manager, REST ou serviços;
- novos usuários recebem `use_sgfp`; não devem depender de `sgfp_access`.

Não é necessário apagar imediatamente a capability legada já persistida. Ela pode permanecer sem efeito de autorização.

## 4. Gate do frontend / tela inicial

O frontend deve distinguir três estados:

1. **ANÔNIMO** — não autenticado;
2. **NÃO AUTORIZADO / NÃO PROVISIONADO** — autenticado, mas sem estado SGFP válido;
3. **AUTORIZADO** — autenticado, provisionado e com `use_sgfp`.

O manager SGFP completo e seus assets só devem ser entregues/enfileirados quando todas as condições forem verdadeiras:

```text
usuário autenticado
AND _sgfp_provisioned confirmado
AND current_user_can('use_sgfp')
```

Usuário autenticado sem `use_sgfp` não deve receber o shell gerencial apenas para ser rejeitado posteriormente pela API.

## 5. Login, cadastro e recuperação

A V1 reutiliza as telas e mecanismos nativos do WordPress para:

- entrar;
- criar conta, quando o registro estiver habilitado pela instalação;
- recuperar senha.

O SGFP não cria endpoint próprio de cadastro, sistema próprio de senha ou repositório próprio de usuários.

A experiência da aplicação deve oferecer links coerentes para os fluxos WordPress e, quando suportado pelo fluxo nativo, preservar retorno ao gestor SGFP após autenticação/cadastro.

O branding visual de `wp-login.php` é opcional para a conclusão funcional da Etapa 11 e não altera este contrato.

## 6. Onboarding e reparo

Qualquer endpoint/fluxo interno de onboarding existente deve ser entendido como reparo/provisionamento de uma identidade WordPress já existente, não como substituto do cadastro WordPress.

Provisionamento e reparo não podem ampliar autorização: ao final, o runtime continua exigindo `use_sgfp`.

## 7. Persistência e modelagem

Não existe entidade/tabela SGFP de usuário na V1.

A ligação é nativa pela identidade WordPress:

```text
wp_users.ID
  -> <prefixo_wp>sgfp_conta_financeira.fk_id_usuario
  -> demais tabelas SGFP por fk_id_usuario
```

Na Conta Financeira, `uq_conta_usuario` garante uma única conta por usuário e `fk_conta_usuario` referencia `wp_users(ID)`.

## 8. Regra de precedência

Esta decisão harmoniza a Etapa 11 e substitui menções incompatíveis que tratem `sgfp_access` como capability vigente ou impliquem autenticação/cadastro SGFP paralelos ao WordPress.

Quando houver conflito documental, prevalecem:

1. baseline V1 simplificada vigente;
2. esta decisão de identidade/acesso de 16/09/2026;
3. documentação técnica reconciliada posteriormente.
