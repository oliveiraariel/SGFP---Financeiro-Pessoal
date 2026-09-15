# MÓDULO 10 — CONFIGURAÇÕES

**Documento:** Levantamento de Requisitos  
**Versão:** 2.0  
**Data de Revisão:** 14/09/2026

## Objetivo

Definir preferências, backup/restauração e operações destrutivas sobre os dados do usuário.

## Regras de Negócio — Versão 1

### RN-008 — Tema da Aplicação
O usuário poderá escolher entre Claro e Escuro. A escolha ficará associada ao usuário. O estado padrão após cadastro/reset será o tema padrão definido pela interface, atualmente **Claro**.

### RN-009 — Criação Manual de Backup
O usuário poderá solicitar manualmente uma cópia de segurança dos dados SGFP.

### RN-010 — Entrega Local do Backup
O backup manual será entregue como arquivo **ZIP** para download local pelo navegador. O envio de backup por e-mail não faz parte da V1.

O ZIP é o contêiner de entrega; o conteúdo interno deverá possuir versão, integridade verificável e proteção compatível com RNF-017/RNF-018.

### RN-011 — Restauração por Arquivo
O usuário poderá selecionar um arquivo ZIP de backup previamente gerado pelo SGFP. O sistema deverá validar formato, versão, integridade e pertencimento antes de restaurar.

### RN-012 — Restauração Integral
A restauração substituirá integralmente os dados SGFP atuais pelos dados existentes na cópia. Não haverá mesclagem na V1.

### RN-013 — Confirmação da Restauração
Antes da restauração, o sistema deverá informar claramente que os dados atuais serão substituídos e solicitar confirmação explícita.

### RN-014 — Proteção do Backup
A cópia de segurança deverá ser protegida contra adulteração e acesso não autorizado. ZIP por si só não é considerado mecanismo suficiente de segurança; a arquitetura definirá a proteção do conteúdo.

### RN-015 — Cópia de Segurança Pré-Restauração
Depois de validar a cópia escolhida e receber a confirmação do usuário, mas antes de substituir os dados atuais, o sistema deverá gerar uma cópia do estado imediatamente anterior.

A restauração só prosseguirá se essa cópia puder ser gerada e preservada em condição recuperável. A cópia pré-restauração utilizará o mesmo formato lógico da cópia manual e deverá poder ser disponibilizada para download local. Não haverá dependência de envio por e-mail.

### RN-016 — Resetar Perfil Financeiro
O usuário poderá retornar o SGFP ao estado de primeiro acesso sem remover sua identidade WordPress.

O reset:
- apaga compromissos, lançamentos, recorrências, categorias e Conta Financeira atuais;
- remove/restaura preferências SGFP aos valores padrão;
- reprovisiona a Conta Financeira **Minha Conta**;
- reprovisiona as categorias padrão;
- preserva login, senha e identidade WordPress;
- não remove arquivos de backup já baixados no computador do usuário.

A operação exige duas etapas de confirmação. Na etapa final o usuário deverá digitar exatamente **RESETAR PERFIL**.

### RN-017 — Excluir Conta de Acesso
O usuário poderá encerrar definitivamente seu acesso ao portal.

A exclusão:
- remove todos os dados SGFP do usuário;
- remove a identidade/login correspondente em `wp_users`;
- encerra o acesso ao portal;
- não é revertida automaticamente por um backup local.

A operação exige duas etapas de confirmação. Na etapa final o usuário deverá digitar exatamente **EXCLUIR CONTA**.

## Regras Preservadas para Versão Futura — PIN
As antigas RN-001 a RN-007 de PIN permanecem históricas/futuras e não integram a V1.

## Funcionalidades da Versão 1
- Tema Claro/Escuro.
- Backup manual local em ZIP.
- Restauração integral por arquivo ZIP.
- Snapshot/cópia automática pré-restauração.
- Reset do perfil financeiro com dupla confirmação.
- Exclusão definitiva da conta de acesso com dupla confirmação.

## Funcionalidades Futuras
- PIN.
- Backup automático periódico/contínuo.
- Armazenamento/sincronização externa.

## Decisões consolidadas
- Backup por e-mail foi removido da V1.
- E-mail permanece necessário para recuperação de senha do WordPress, não para backup.
- Reset e exclusão de conta são operações distintas.
