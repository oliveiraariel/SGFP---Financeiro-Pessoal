## UC-021 — Restaurar cópia de segurança

**Objetivo**

Permitir que o usuário restaure os dados a partir de uma cópia válida.

**Ator principal**

Usuário.

**Pré-condições**

O usuário deverá possuir uma cópia de segurança válida.

**Gatilho**

O usuário solicita uma restauração.

**Fluxo principal**

1. O usuário fornece o arquivo correspondente a uma cópia de segurança previamente criada e preservada.
2. O sistema valida a cópia.
3. O sistema informa que os dados atuais serão substituídos.
4. O usuário confirma a operação.
5. O sistema restaura os dados existentes na cópia.
6. O sistema finaliza a restauração.
7. O sistema disponibiliza o sistema no estado representado pela cópia.

**Fluxos alternativos e exceções**

* O arquivo fornecido poderá corresponder à cópia anteriormente enviada ao e-mail cadastrado no fluxo do UC-020.
* Uma cópia inválida não deverá ser restaurada.
* O sistema não deverá mesclar os dados atuais com os dados da cópia.
* A confirmação será obrigatória.
* A restauração deverá preservar a integridade do estado restaurado.

**Pós-condições**

Os dados atuais estarão substituídos pelos dados da cópia selecionada.

**Requisitos relacionados**

RF-021.

**Regras de negócio relacionadas**

Regras do módulo Configurações referentes à restauração.
