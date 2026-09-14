# Etapa 10 — Desenvolvimento da API

**Status histórico:** concluída sobre a baseline anterior.  
**Status atual:** **reconciliação obrigatória** após a baseline V1 simplificada de 14/09/2026.

A implementação existente ainda contém capacidades que deixaram de fazer parte da V1 e, por isso, não deve ser tratada como fonte normativa.

## Migrações necessárias

- substituir múltiplas contas/roles por uma conta única provisionada como **Minha Conta**;
- remover superfícies e serviços de Transferência da V1;
- remover consulta de Patrimônio Total/net-worth da V1;
- adaptar Compromisso/Lançamento para a conta única;
- adaptar esquema/migrações de seis para cinco tabelas financeiras;
- trocar backup gzip/e-mail por entrega local em ZIP, preservando proteção/integridade;
- manter restauração integral e snapshot pré-restauração, sem envio por e-mail;
- implementar RF-022 (Resetar perfil financeiro);
- implementar RF-023 (Excluir conta de acesso).

## Regra de precedência

Enquanto a migração não ocorrer:
**documentação normativa revisada > implementação antiga**.

Não remover mecanismos de segurança/isolamento apenas para simplificar. A simplificação é funcional/estrutural, não redução de proteção.
