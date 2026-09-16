# Etapa 11 — Desenvolvimento da Interface Web

**Status:** em andamento na branch `feat/stage-11-web-interface`.

A interface existente foi iniciada sobre a baseline anterior e deverá ser reconciliada com a decisão de 14/09/2026.

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

## Contrato de entrada e estado do usuário — 16/09/2026

A Etapa 11 deve tratar os fluxos nativos do WordPress como a entrada da própria aplicação SGFP:

- **Entrar** → fluxo nativo WordPress;
- **Criar conta** → fluxo nativo de registro WordPress, quando habilitado pela instalação;
- **Recuperar senha** → fluxo nativo WordPress;
- após autenticação/cadastro, o retorno deve conduzir o usuário ao gestor SGFP quando o fluxo WordPress permitir o redirecionamento;
- não existe endpoint, tabela ou sistema de credenciais próprio do SGFP.

A interface distingue três estados:

1. **Anônimo:** mostra as ações de entrada/cadastro/recuperação aplicáveis, sem carregar o manager.
2. **Autenticado não provisionado/não autorizado:** mostra estado de acesso/reparo apropriado, sem carregar o manager.
3. **Autorizado:** somente quando autenticado, `_sgfp_provisioned` confirmado e `use_sgfp` concedida; então o manager e seus assets podem ser carregados.

`use_sgfp` é a capability oficial única da V1. `sgfp_access` é legado de migração e não deve ser aceita como autorização alternativa pelo frontend.

O branding visual de `wp-login.php` não é pré-requisito para considerar os fluxos de entrada funcionalmente integrados na Etapa 11.

