# Etapa 11 — Desenvolvimento da Interface Web

**Status:** entrega funcional estável 1.0.15 publicada na `main`; fechamento formal de testes não declarado completo.

A interface foi iniciada sobre a baseline anterior. O checklist abaixo é histórico; a situação publicada é descrita em `docs/governanca/STATUS-RELEASE-1.0.15-2026-10-17.md`.

## Alvos obrigatórios da reconciliação

- fluxo de cadastro/login antes do gestor;
- conta única `Minha Conta`, sem criação de contas adicionais;
- remoção da UI de Transferências e Patrimônio Total;
- Compromisso pendente não altera saldo; efetivação gera Lançamento;
- backup manual em ZIP para download local;
- restauração por ZIP;
- Resetar perfil com dupla confirmação + `RESETAR PERFIL`;
- Excluir conta com dupla confirmação + `EXCLUIR CONTA`.

A auditoria de usabilidade/defeitos deverá ser decomposta em Work Units após a reconciliação documental e de baseline.

## Contrato de acesso implementado na versão 1.0.15

- Login, cadastro quando habilitado e recuperação usam os fluxos WordPress, sem identidade paralela.
- Visitante anônimo é direcionado à tela de login WordPress.
- Apenas usuário autenticado com `_sgfp_provisioned = '1'` e `use_sgfp` recebe o gestor e seus assets.
- Conta não provisionada/autorizada apresenta indisponibilidade em vez de carregar o gestor.
- `sgfp_access` não é alternativa de autorização; `POST /onboarding` não está registrado.
- A exigência normativa de login exclusivamente por e-mail ainda precisa ser conferida no fluxo nativo `wp-login.php`.

