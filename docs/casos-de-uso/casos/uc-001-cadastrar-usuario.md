## UC-001 — Cadastrar usuário

**Objetivo**  
Criar o acesso inicial ao SGFP e provisionar o estado mínimo válido do novo usuário.

**Ator principal**  
Usuário.

**Pré-condições**  
Nenhuma autenticação é necessária.

**Fluxo principal**
1. O usuário acessa o cadastro nativo do WordPress apresentado como entrada de cadastro do SGFP.
2. Informa os dados solicitados pelo fluxo WordPress habilitado na instalação.
3. O WordPress valida as informações.
4. WordPress cria a identidade/login em `wp_users`.
5. O SGFP cria automaticamente a Conta Financeira única com nome **Minha Conta**.
6. O SGFP cria as categorias padrão vinculadas ao usuário.
7. O sistema conclui o provisionamento idempotente, concede a autorização SGFP vigente e disponibiliza o retorno ao gestor.

**Exceções**
- Dados inválidos devem ser corrigidos.
- E-mail já cadastrado impede duplicação.
- Se o provisionamento da conta/categorias falhar, o cadastro não deverá ser apresentado como concluído.

**Pós-condições**  
O usuário possui identidade WordPress (`wp_users.ID`), exatamente uma Conta Financeira, categorias padrão e estado SGFP provisionado. Não é criada identidade ou tabela de usuário paralela.

**Requisitos relacionados**  
RF-001.
