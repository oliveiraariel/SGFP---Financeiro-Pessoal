# RESTRIÇÕES E DEPENDÊNCIAS

**Sistema:** SGFP  
**Versão:** 2.0  
**Data:** 14/09/2026

## 1. Restrições da V1

### RE-001 — Escopo
Somente capacidades declaradas ativas na baseline V1 podem ser implementadas como V1.

### RE-002 — Inserção Manual de Dados
Não haverá integração bancária automática.

### RE-003 — Ausência de Integrações Financeiras Externas
A V1 não sincroniza automaticamente dados com bancos ou instituições financeiras.

### RE-004 — Recorrência Mensal
Recorrências da V1 são exclusivamente mensais.

### RE-005 — Conta Financeira Única
Cada usuário possuirá exatamente uma Conta Financeira, provisionada automaticamente com nome inicial **Minha Conta**.

### RE-006 — Transferências Fora da V1
Transferências entre contas não integram a V1. RF-012 a RF-014 permanecem apenas como identificadores futuros.

### RE-007 — Patrimônio Total Fora da V1
A V1 não apresenta Patrimônio Total separado do saldo da conta única.

### RE-008 — Cartão de Crédito
A V1 representa pagamento de fatura como compromisso de Saída, sem controle detalhado de compras/limite.

### RE-009 — Parcelamentos
Parcelamentos usam compromissos e recorrências; não constituem entidade independente.

### RE-010 — Dashboard
Dashboard é visão derivada, sem persistência financeira própria.

### RE-011 — Relatórios
Relatórios financeiros específicos permanecem fora da V1.

### RE-012 — Backup Automático
Não haverá backup automático periódico/contínuo. A única geração automática é a cópia de segurança pré-restauração exigida para proteção do estado atual.

### RE-013 — Autenticação
A V1 usa e-mail e senha via WordPress; provedores externos ficam fora do escopo.

### RE-014 — Armazenamento Externo
Não haverá sincronização ou armazenamento externo obrigatório.

### RE-015 — Anexos
Anexação de arquivos a compromissos fica fora da V1.

### RE-016 — Plataforma
Aplicação Web em PHP/WordPress, plugin SGFP próprio e WordPress REST API.

### RE-017 — Separação Domínio/Implementação
Requisitos não determinam automaticamente classes/tabelas além do que a modelagem consolidar.

### RE-018 — Critérios Quantitativos
Desempenho, capacidade e compatibilidade quantitativa permanecem a definir quando houver base técnica.

### RE-019 — Operações Destrutivas
Reset do perfil e exclusão da conta exigem duas etapas de confirmação com frase final em caixa alta definida nos requisitos. Nenhuma dessas ações pode ocorrer por clique único.

## 2. Dependências

### DEP-001 — Ambiente Web
PHP e WordPress.

### DEP-002 — Banco de Dados
MySQL ou MariaDB compatível com WordPress e tabelas próprias do SGFP.

### DEP-003 — Serviço de E-mail
E-mail é dependência da **recuperação de senha do WordPress**. Backup/restauração não dependem de envio por e-mail na V1.

### DEP-004 — API REST
A interface Web depende da API REST SGFP protegida por autenticação/autorização.

### DEP-005 — Download/Upload de Arquivo
O navegador deverá permitir baixar o backup ZIP e selecionar um ZIP válido para restauração.

## 3. Observações
- Backup manual é local por download.
- A cópia pré-restauração pode usar armazenamento privado temporário de implementação, mas deverá permanecer recuperável e disponibilizável ao usuário; isso não constitui armazenamento externo obrigatório.
- Identidade/login pertencem ao WordPress; dados financeiros permanecem nas estruturas SGFP.

## Histórico
### Versão 2.0 — 14/09/2026
Adotou conta única, retirou transferências e patrimônio da V1, removeu dependência de e-mail para backup e acrescentou confirmações destrutivas.
