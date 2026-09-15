## UC-020 — Criar cópia de segurança

**Objetivo**  
Baixar uma cópia local dos dados SGFP.

**Fluxo principal**
1. Usuário autenticado solicita **Backup**.
2. O sistema captura estado consistente dos dados.
3. Gera conteúdo versionado, íntegro e protegido.
4. Empacota a entrega em arquivo **ZIP**.
5. O navegador inicia o download local.

**Exceções**
- Falha de captura/proteção/validação não deve produzir backup parcial válido.
- A V1 não envia backup por e-mail.

**Pós-condições**  
O usuário possui um ZIP de backup local.

**Requisito relacionado:** RF-021.
