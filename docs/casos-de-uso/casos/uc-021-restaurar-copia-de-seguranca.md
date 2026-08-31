## UC-021 — Restaurar cópia de segurança

**Objetivo**

Permitir que o usuário restaure os dados a partir de uma cópia válida.

**Ator principal**

Usuário.

**Pré-condições**

O usuário deverá possuir um arquivo de cópia de segurança válido, previamente gerado pelo SGFP e preservado pelo usuário.

**Gatilho**

O usuário solicita uma restauração.

**Fluxo principal**

1. O usuário fornece ao sistema o arquivo correspondente à cópia de segurança que deseja restaurar.
2. O sistema valida a cópia.
3. O sistema informa que os dados atuais serão substituídos.
4. O usuário confirma a operação.
5. O sistema restaura os dados existentes na cópia.
6. O sistema finaliza a restauração.
7. O sistema disponibiliza o sistema no estado representado pela cópia.

**Fluxos alternativos e exceções**

* Uma cópia inválida não deverá ser restaurada.
* O sistema não deverá mesclar os dados atuais com os dados da cópia.
* A confirmação será obrigatória.
* A restauração deverá preservar a integridade do estado restaurado.
* A definição do comportamento denominado "preservar o estado anterior do sistema durante a restauração" permanece pendente de decisão, conforme registrado no módulo Configurações; este caso de uso não deverá presumir automaticamente a criação de uma nova cópia ou outro mecanismo técnico.

**Pós-condições**

Os dados atuais estarão substituídos pelos dados da cópia fornecida e validada, respeitada a decisão futura sobre a preservação do estado imediatamente anterior à restauração.

**Requisitos relacionados**

RF-021.

**Regras de negócio relacionadas**

Regras do módulo Configurações referentes à restauração.
