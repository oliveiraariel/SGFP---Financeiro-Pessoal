# MÓDULO 10 — CONFIGURAÇÕES

**Documento:** Levantamento de Requisitos

**Versão:** 1.1

**Objetivo**

Definir as preferências gerais e os mecanismos de preservação dos dados que poderão ser configurados pelo usuário no SGFP.

## Regras de Negócio — Versão 1

### RN-008 — Tema da Aplicação

O sistema permitirá ao usuário escolher entre os temas **Claro** e **Escuro**.

A escolha deverá ser aplicada à apresentação da aplicação e permanecer associada ao usuário correspondente.

### RN-009 — Criação de Cópia de Segurança

O usuário poderá solicitar manualmente a criação de uma cópia de segurança de seus dados.

A cópia deverá contemplar os dados necessários para preservar o estado do sistema no momento em que for criada.

### RN-010 — Envio da Cópia de Segurança

A cópia de segurança criada manualmente será enviada para o endereço de e-mail cadastrado no perfil do usuário.

Na Versão 1, não haverá geração automática de cópias de segurança.

### RN-011 — Restauração de Cópia de Segurança

O usuário poderá solicitar a restauração de uma cópia de segurança previamente criada.

Para realizar a restauração, deverá fornecer o arquivo correspondente à cópia que deseja utilizar.

### RN-012 — Restauração Integral

A restauração de uma cópia de segurança substituirá integralmente os dados atuais pelos dados existentes na cópia selecionada.

O sistema não realizará mesclagem entre os dados atuais e os dados da cópia de segurança.

### RN-013 — Confirmação da Restauração

Antes de executar uma restauração, o sistema deverá informar ao usuário que os dados atuais serão substituídos pelos dados existentes na cópia selecionada e solicitar sua confirmação.

### RN-014 — Proteção da Cópia de Segurança

A cópia de segurança deverá possuir proteção adequada contra acesso não autorizado.

A forma técnica de proteção será definida posteriormente na etapa de arquitetura e implementação.

## Regras Preservadas para Versão Futura — PIN

Os identificadores abaixo são preservados para manter o histórico do levantamento, mas **não constituem requisitos obrigatórios da Versão 1**.

### RN-001 — PIN Opcional — Futuro

Em versão futura, o usuário poderá ativar voluntariamente a proteção por PIN.

### RN-002 — Solicitação do PIN — Futuro

O PIN não fará parte do login inicial.

Quando a funcionalidade for implementada, o PIN será solicitado somente para desbloquear o SGFP quando a aplicação tiver sido previamente bloqueada durante uma sessão já autenticada.

### RN-003 — Alteração do PIN — Futuro

O usuário poderá alterar o PIN cadastrado conforme as regras que forem consolidadas para a versão que implementar a funcionalidade.

### RN-004 — Desativação do PIN — Futuro

O usuário poderá desativar a proteção por PIN.

A confirmação da identidade deverá respeitar as regras definidas para a implementação futura.

### RN-005 — Limite de Tentativas do PIN — Futuro

A versão futura deverá limitar as tentativas consecutivas de desbloqueio por PIN.

A definição atualmente preservada é de, no máximo, cinco tentativas consecutivas antes do bloqueio do PIN.

### RN-006 — Redefinição do PIN — Futuro

Em caso de esquecimento ou bloqueio do PIN, o usuário deverá confirmar novamente a senha da conta e, após validação, poderá definir um novo PIN.

Não será criado mecanismo próprio de recuperação do PIN por e-mail, token ou link temporário.

### RN-007 — Natureza do PIN — Futuro

O PIN será uma proteção secundária de bloqueio rápido da aplicação durante uma sessão já autenticada.

O PIN não substituirá a autenticação principal por e-mail e senha.

## Decisões Tomadas

- A proteção por PIN foi retirada do escopo da Versão 1.
- O PIN permanecerá previsto para versão futura como mecanismo opcional de bloqueio rápido durante uma sessão já autenticada.
- O PIN futuro não substituirá o login por e-mail e senha.
- A recuperação futura do PIN não utilizará e-mail, token próprio ou link com prazo de validade; a redefinição dependerá da confirmação da senha da conta.
- O bloqueio automático por inatividade poderá ser avaliado juntamente com a funcionalidade futura de PIN.
- A aplicação disponibilizará os temas Claro e Escuro na Versão 1.
- A criação de cópia de segurança será manual.
- A cópia de segurança será enviada ao e-mail cadastrado no perfil do usuário.
- Não haverá backup automático na Versão 1.
- O usuário poderá restaurar uma cópia de segurança por meio do arquivo correspondente.
- A restauração representará um ponto de restauração integral do sistema.
- A restauração substituirá os dados atuais pelos dados existentes na cópia selecionada.
- Não haverá mesclagem de dados na Versão 1.
- O sistema solicitará confirmação antes de executar uma restauração.
- A cópia de segurança deverá possuir proteção contra acesso não autorizado.
- As decisões técnicas relacionadas à proteção do backup serão definidas posteriormente.

## Funcionalidades da Versão 1

- Escolha entre tema Claro e Escuro.
- Criação manual de cópia de segurança.
- Envio da cópia de segurança para o e-mail cadastrado.
- Restauração de cópia de segurança por arquivo.
- Confirmação antes da restauração.
- Substituição integral dos dados durante a restauração.

## Funcionalidades Previstas para Versões Futuras

- Proteção opcional por PIN.
- Bloqueio manual do SGFP e desbloqueio por PIN.
- Limite de tentativas do PIN.
- Redefinição do PIN mediante confirmação da senha da conta.
- Bloqueio automático da aplicação após período de inatividade.
- Outras opções de personalização visual.
- Backup automático.
- Integração com serviços externos de armazenamento.
- Outras formas de proteção e recuperação de dados.
- Outras funcionalidades de configuração que sejam identificadas durante a utilização do sistema.

## Observações

A subetapa **Configurações** trata exclusivamente das preferências gerais e dos mecanismos de preservação de dados disponibilizados ao usuário.

Os demais itens existentes no menu lateral do sistema não fazem parte deste módulo quando possuírem finalidade distinta de configuração.

O menu lateral também poderá conter:

- **Compartilhar** — funcionalidade prevista para versão futura.
- **Avaliar** — funcionalidade prevista para versão futura.
- **Sobre** — seção informativa contendo Política de Privacidade e opção de Entrar em contato.

Esses itens não constituem configurações do sistema e, portanto, não possuem regras de negócio específicas nesta subetapa.

As definições de arquitetura e implementação necessárias para concretizar as funcionalidades deste módulo serão realizadas posteriormente.

## Data de Revisão

**30/08/2026**
