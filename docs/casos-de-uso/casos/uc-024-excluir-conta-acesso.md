## UC-024 — Excluir conta de acesso

**Objetivo**  
Encerrar definitivamente o acesso ao portal SGFP.

**Fluxo principal**
1. Usuário escolhe **Excluir conta**.
2. O sistema informa que dados SGFP e login serão removidos.
3. O usuário confirma a primeira etapa.
4. O sistema exige a digitação exata de **EXCLUIR CONTA**.
5. Remove os dados SGFP.
6. Remove a identidade/login WordPress.
7. O acesso ao portal é encerrado.

**Exceções**
- Cancelamento ou frase incorreta não altera dados.
- A operação não pode ser apresentada como concluída se o login permanecer ativo.

**Requisito relacionado:** RF-023.
