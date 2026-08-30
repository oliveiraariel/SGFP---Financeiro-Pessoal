## UC-018 — Gerenciar proteção por PIN — Versão Futura

**Status**

Caso de Uso preservado para versão futura. Não integra o escopo ativo da Versão 1.

**Objetivo**

Permitir, em versão futura, que o usuário configure e utilize uma proteção opcional por PIN como mecanismo de bloqueio rápido do SGFP durante uma sessão já autenticada.

**Ator principal**

Usuário.

**Pré-condições**

O usuário deverá estar autenticado por e-mail e senha.

A funcionalidade de PIN deverá ter sido incluída em uma versão futura do sistema.

**Gatilho**

O usuário acessa a configuração do PIN ou tenta desbloquear o SGFP previamente bloqueado.

**Fluxo principal**

1. O usuário opta por ativar o PIN.
2. O sistema informa que o PIN é uma proteção secundária e não substitui a autenticação principal.
3. O usuário define o PIN.
4. O sistema registra a proteção.
5. Durante uma sessão já autenticada, o usuário poderá bloquear o SGFP.
6. Para desbloquear a aplicação, o sistema solicita o PIN.
7. O usuário informa o PIN.
8. O sistema valida o PIN e libera o acesso ao SGFP.

**Fluxos alternativos e exceções**

* O PIN será opcional.
* O PIN não substituirá o e-mail e a senha na autenticação principal.
* O usuário poderá alterar ou desativar o PIN conforme as regras da versão futura.
* A solução futura deverá limitar as tentativas consecutivas de PIN.
* A referência atualmente preservada é de no máximo cinco tentativas consecutivas antes do bloqueio do PIN.
* Em caso de esquecimento ou bloqueio do PIN, o usuário deverá confirmar novamente a senha da conta para definir um novo PIN.
* Não haverá mecanismo próprio de recuperação do PIN por e-mail, token ou link temporário.
* O bloqueio automático por inatividade poderá ser incorporado à versão futura, mas não pertence à V1.

**Pós-condições**

A proteção por PIN estará configurada, alterada, desativada ou o SGFP terá sido desbloqueado conforme a operação realizada.

**Requisitos relacionados**

RF-019 — Versão futura.

**Regras de negócio relacionadas**

Regras futuras dos módulos Usuários, Configurações e Segurança relacionadas ao PIN.
