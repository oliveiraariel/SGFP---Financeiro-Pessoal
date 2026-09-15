## UC-001 — Cadastrar usuário

**Objetivo**  
Criar o acesso inicial ao SGFP e provisionar o estado mínimo válido do novo usuário.

**Ator principal**  
Usuário.

**Pré-condições**  
Nenhuma autenticação é necessária.

**Fluxo principal**
1. O usuário acessa o cadastro.
2. Informa os dados solicitados.
3. O sistema valida as informações.
4. WordPress cria a identidade/login.
5. O SGFP cria automaticamente a Conta Financeira única com nome **Minha Conta**.
6. O SGFP cria as categorias padrão vinculadas ao usuário.
7. O sistema conclui o provisionamento e disponibiliza o acesso.

**Exceções**
- Dados inválidos devem ser corrigidos.
- E-mail já cadastrado impede duplicação.
- Se o provisionamento da conta/categorias falhar, o cadastro não deverá ser apresentado como concluído.

**Pós-condições**  
O usuário possui identidade WordPress, exatamente uma Conta Financeira e categorias padrão.

**Requisitos relacionados**  
RF-001.
