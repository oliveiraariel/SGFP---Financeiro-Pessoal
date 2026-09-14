## UC-023 — Resetar perfil financeiro

**Objetivo**  
Apagar os dados SGFP e retornar ao estado inicial sem remover o login WordPress.

**Fluxo principal**
1. Usuário escolhe **Resetar perfil financeiro**.
2. O sistema apresenta aviso de perda dos dados SGFP.
3. O usuário confirma a primeira etapa.
4. O sistema exige a digitação exata de **RESETAR PERFIL**.
5. Remove os dados SGFP e restaura preferências padrão.
6. Reprovisiona **Minha Conta** e categorias padrão.
7. O login permanece válido.

**Exceções**
- Cancelamento ou frase incorreta não altera dados.
- O sistema não deve apresentar sucesso com estado parcialmente resetado.
- Backups locais já baixados não são removidos.

**Requisito relacionado:** RF-022.
