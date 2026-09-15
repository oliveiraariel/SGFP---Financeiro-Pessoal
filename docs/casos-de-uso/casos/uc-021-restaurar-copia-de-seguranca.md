## UC-021 — Restaurar cópia de segurança

**Objetivo**  
Restaurar integralmente os dados SGFP a partir de ZIP válido.

**Fluxo principal**
1. O usuário seleciona o ZIP.
2. O sistema valida formato, versão, integridade, proteção e pertencimento.
3. Informa que os dados atuais serão substituídos.
4. O usuário confirma.
5. O sistema gera cópia pré-restauração do estado atual.
6. Preserva essa cópia em condição recuperável e apta a download local.
7. Substitui integralmente os dados atuais.
8. Verifica a consistência e conclui.

**Exceções**
- Backup inválido não é restaurado.
- Não há mesclagem.
- Sem confirmação nada é alterado.
- Falha da cópia pré-restauração cancela a operação.
- E-mail não participa de backup/restauração.

**Requisito relacionado:** RF-021.
