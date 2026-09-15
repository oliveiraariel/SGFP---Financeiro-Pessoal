## UC-005 — Gerenciar Conta Financeira

**Objetivo**  
Consultar e renomear a Conta Financeira única.

**Pré-condições**
- Usuário autenticado.
- Conta provisionada no cadastro.

**Fluxo principal**
1. O usuário acessa a área de Conta.
2. O sistema apresenta a única Conta Financeira e seu saldo derivado.
3. O usuário poderá alterar o nome da conta.
4. O sistema salva o novo nome sem alterar lançamentos ou histórico.

**Regras**
- A V1 não permite conta adicional.
- A conta não pode ser excluída isoladamente.
- Reset reprovisiona a conta; exclusão do acesso remove-a com os demais dados.

**Requisitos relacionados**  
RF-004, RF-005.
