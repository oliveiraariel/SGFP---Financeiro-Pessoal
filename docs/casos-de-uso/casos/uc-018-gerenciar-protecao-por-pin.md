## UC-018 — Gerenciar proteção por PIN

**Objetivo**

Permitir que o usuário configure e utilize a proteção opcional por PIN.

**Ator principal**

Usuário.

**Pré-condições**

Para alterar ou desativar um PIN existente, o usuário deverá atender às regras de autenticação específicas.

**Gatilho**

O usuário acessa a configuração do PIN ou tenta desbloquear a aplicação protegida.

**Fluxo principal**

1. O usuário opta por ativar o PIN.
2. O sistema informa a finalidade do PIN e o mecanismo de recuperação.
3. O usuário cria o PIN.
4. O sistema registra a proteção.
5. Em situação de bloqueio, o sistema solicita o PIN.
6. O usuário informa o PIN.
7. O sistema valida o PIN e libera o acesso.

**Fluxos alternativos e exceções**

* O PIN será opcional.
* O PIN não substituirá o e-mail e a senha na autenticação inicial.
* O usuário poderá alterar o PIN.
* O usuário poderá desativar o PIN mediante confirmação e informação do PIN atual.
* Serão permitidas no máximo cinco tentativas consecutivas.
* Após cinco tentativas incorretas, o acesso por PIN será bloqueado e será necessário utilizar o mecanismo de recuperação.
* O bloqueio automático por inatividade não fará parte da Versão 1.
* A recuperação utilizará o e-mail cadastrado.

**Pós-condições**

A proteção por PIN estará configurada ou o acesso terá sido liberado conforme a operação realizada.

**Requisitos relacionados**

RF-019.

**Regras de negócio relacionadas**

Regras dos módulos Usuários, Configurações e Segurança.
