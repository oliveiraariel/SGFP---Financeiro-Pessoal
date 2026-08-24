# MÓDULO 10 — CONFIGURAÇÕES

**Documento:** Levantamento de Requisitos

**Versão:** 1.0

**Objetivo**

Definir as preferências gerais e os mecanismos de proteção e preservação dos dados que poderão ser configurados pelo usuário no SGFP.

## **Regras de Negócio**

### **RN-001 — PIN Opcional**

O uso de PIN será opcional.

O usuário poderá utilizar o sistema sem configurar um PIN ou poderá ativar a proteção posteriormente.

### **RN-002 — Solicitação do PIN**

Quando o PIN estiver ativado, o sistema deverá solicitá-lo ao iniciar ou reabrir o aplicativo após este ter sido fechado.

O PIN não será solicitado apenas pelo retorno do aplicativo ao primeiro plano enquanto ele permanecer aberto.

### **RN-003 — Alteração do PIN**

O usuário poderá alterar o PIN cadastrado.

Para realizar a alteração, deverá informar o PIN atual.

### **RN-004 — Desativação do PIN**

O usuário poderá desativar a proteção por PIN.

Para realizar a desativação, deverá informar o PIN atual e confirmar a ação.

### **RN-005 — Limite de Tentativas do PIN**

O sistema permitirá no máximo cinco tentativas consecutivas de inserção do PIN.

Após a quinta tentativa incorreta, o acesso por PIN será bloqueado e o usuário deverá utilizar o mecanismo de recuperação por e-mail.

### **RN-006 — Recuperação do PIN**

Caso o usuário esqueça o PIN, poderá utilizar o mecanismo de recuperação associado ao endereço de e-mail cadastrado em seu perfil.

O sistema não permitirá que o usuário indique livremente outro endereço de e-mail para recuperação do PIN.

### **RN-007 — Aviso sobre Recuperação do PIN**

Ao ativar o PIN, o sistema deverá informar ao usuário que o endereço de e-mail cadastrado em seu perfil será utilizado para a recuperação do PIN.

### **RN-008 — Tema da Aplicação**

O sistema permitirá ao usuário escolher entre os temas **Claro** e **Escuro**.

A escolha deverá ser aplicada à apresentação da aplicação.

### **RN-009 — Criação de Cópia de Segurança**

O usuário poderá solicitar manualmente a criação de uma cópia de segurança de seus dados.

A cópia deverá contemplar os dados necessários para preservar o estado do sistema no momento em que for criada.

### **RN-010 — Armazenamento ou Entrega da Cópia de Segurança**

O sistema deverá disponibilizar a cópia de segurança ao usuário para que ela possa ser preservada fora do ambiente de utilização do sistema.

O mecanismo de armazenamento ou entrega da cópia de segurança será definido posteriormente.

### **RN-011 — Restauração de Cópia de Segurança**

O usuário poderá solicitar a restauração de uma cópia de segurança previamente criada.

Para realizar a restauração, deverá fornecer a cópia de segurança correspondente ao estado que deseja restaurar.

### **RN-012 — Restauração Integral**

A restauração de uma cópia de segurança substituirá integralmente os dados atuais pelos dados existentes na cópia selecionada.

O sistema não realizará mesclagem entre os dados atuais e os dados da cópia de segurança.

### **RN-013 — Confirmação da Restauração**

Antes de executar uma restauração, o sistema deverá informar ao usuário que os dados atuais serão substituídos pelos dados existentes na cópia selecionada e solicitar sua confirmação.

### **RN-014 — Proteção da Cópia de Segurança**

A cópia de segurança deverá possuir proteção adequada contra acesso não autorizado.

A forma técnica de proteção será definida posteriormente na etapa de arquitetura e implementação.

## **Decisões Tomadas**

- O uso de PIN será opcional.
- O PIN será solicitado ao iniciar ou reabrir o aplicativo após seu fechamento.
- O usuário poderá alterar o PIN mediante informação do PIN atual.
- O usuário poderá desativar o PIN mediante informação do PIN atual e confirmação.
- Serão permitidas no máximo cinco tentativas consecutivas de inserção do PIN.
- Após cinco tentativas incorretas, o usuário deverá utilizar o mecanismo de recuperação por e-mail.
- A recuperação do PIN utilizará o e-mail cadastrado no perfil do usuário.
- Ao ativar o PIN, o usuário será informado sobre a utilização do e-mail para recuperação.
- A aplicação disponibilizará os temas Claro e Escuro.
- A criação de cópia de segurança será manual.
- A cópia de segurança deverá preservar o estado dos dados no momento em que for criada.
- O mecanismo definitivo de armazenamento ou entrega da cópia de segurança ainda será definido.
- O usuário poderá restaurar uma cópia de segurança previamente criada.
- A restauração representará um ponto de restauração integral do sistema.
- A restauração substituirá os dados atuais pelos dados existentes na cópia selecionada.
- Não haverá mesclagem de dados na Versão 1.
- O sistema solicitará confirmação antes de executar uma restauração.
- A cópia de segurança deverá possuir proteção contra acesso não autorizado.
- As definições técnicas relacionadas à proteção, armazenamento, geração e restauração do backup serão realizadas posteriormente.

## **Funcionalidades da Versão 1**

- Ativação e desativação opcional do PIN.
- Alteração do PIN.
- Recuperação do PIN por e-mail.
- Limitação de tentativas de acesso por PIN.
- Escolha entre tema Claro e Escuro.
- Criação manual de cópia de segurança.
- Disponibilização da cópia de segurança para preservação pelo usuário.
- Restauração de cópia de segurança.
- Confirmação antes da restauração.
- Substituição integral dos dados durante a restauração.

## **Funcionalidades Previstas para Versões Futuras**

- Outras opções de personalização visual.
- Backup automático.
- Integração com serviços externos de armazenamento.
- Autenticação utilizando provedores externos, como Google, Apple/iCloud ou outros serviços que venham a ser definidos.
- Integração autorizada com serviços de armazenamento associados à conta do usuário.
- Outras funcionalidades de configuração que sejam identificadas durante a utilização do sistema.

## **Observações**

A subetapa **Configurações** trata exclusivamente das preferências gerais, mecanismos de proteção e preservação de dados disponibilizados ao usuário.

A **Versão 1** terá como objetivo fundamental a gestão financeira pessoal por meio da **inserção manual dos dados pelo usuário**, juntamente com mecanismos de preservação dos dados por meio de cópia de segurança.

A definição sobre o mecanismo de armazenamento ou entrega da cópia de segurança permanece em aberto e será discutida posteriormente com a equipe.

A possibilidade de utilização de **Google, Apple/iCloud ou outros provedores para autenticação** será considerada em versões futuras.

A utilização de serviços externos de armazenamento, como o **Google Drive**, também será considerada posteriormente e dependerá de autorização explícita do usuário.

O login por meio de um provedor externo e a autorização para utilização de seu armazenamento são funcionalidades distintas e não deverão ser consideradas automaticamente vinculadas.

O armazenamento de arquivos e comprovantes financeiros em serviços externos será avaliado em uma versão posterior e não constitui requisito da Versão 1.

As definições de arquitetura e implementação necessárias para concretizar as funcionalidades deste módulo serão realizadas posteriormente.

**Data da Última Atualização**

10/08/2026
