# Etapa 10 — Desenvolvimento da API

Implementar as regras de negócio e disponibilizar os recursos da aplicação por meio da API. **Status da revisão de encerramento (12/09/2026): concluída; Stage 10 fechada.**

## Resultado autoritativo da revisão final

- Superfícies REST implementadas incluem contas, categorias, compromissos, transferências, recorrências, relatórios, tema, `POST /backups`, `POST /restore-validations` e `POST /restorations`.
- Autorização e isolamento foram inspecionados: as rotas usam `permission_callback` com `use_sgfp`, os serviços exigem capability/usuário atual e os repositórios recebem o `userId` para escopo.
- Backup manual implementa coleta transacional, lock por usuário, gzip e AEAD Sodium; validação implementa limite, proprietário, staging privado e hash.
- `POST /restorations` agora valida novamente o token, cria o snapshot pré-restauração sob lock, substitui integralmente os dados e o tema numa transação InnoDB, verifica invariantes, consome o token uma única vez e só então limpa cache e tenta enviar o snapshot por e-mail.
- Evidência: `git diff --check`, lint PHP completo e seis testes manuais passaram. PHPUnit/composer não foi executado porque `composer` não está disponível; a validação MySQL/WordPress real permanece pendente de ambiente.

**Gate Etapa 10:** aprovado para os contratos e implementação disponíveis neste ambiente. Stage 11 permanece não iniciada.
