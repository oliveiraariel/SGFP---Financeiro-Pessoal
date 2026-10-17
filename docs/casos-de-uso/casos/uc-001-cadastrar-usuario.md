## UC-001 — Cadastrar usuário

**Objetivo**  
Criar o acesso inicial ao SGFP e provisionar o estado mínimo válido do novo usuário.

**Ator principal**  
Usuário.

**Pré-condições**  
Nenhuma autenticação é necessária.

**Fluxo principal**
1. O usuário acessa o cadastro nativo WordPress, quando habilitado.
2. Informa os dados pedidos pelo WordPress.
3. O WordPress valida e cria a identidade em `wp_users`.
4. O hook `user_register` aciona o provisionamento SGFP.
5. O SGFP garante a conta única **Minha Conta** e as categorias padrão, sem duplicação.
6. Após provisionamento bem-sucedido, registra `_sgfp_provisioned` e concede `use_sgfp`.
7. O gestor fica acessível somente ao usuário provisionado e autorizado.

**Exceções**
- Dados inválidos devem ser corrigidos.
- E-mail já cadastrado impede duplicação.
- Se o provisionamento financeiro falhar, a identidade WordPress pode já existir, mas o acesso ao gestor não deve ser liberado. O hook `wp_login` permite reparo idempotente posterior.

**Pós-condições**  
Usuário autorizado tem `wp_users.ID`, conta financeira única, categorias padrão e marcador de provisionamento. Não existe tabela própria de usuários SGFP.

**Requisitos relacionados**  
RF-001.
