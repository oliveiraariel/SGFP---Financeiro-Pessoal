## UC-021 — Restaurar cópia de segurança

**Objetivo**

Permitir que o usuário restaure os dados a partir de uma cópia válida.

**Ator principal**

Usuário.

**Pré-condições**

O usuário deverá possuir um arquivo de cópia de segurança válido, previamente gerado pelo SGFP e preservado pelo usuário.

**Gatilho**

O usuário, na área de Configurações, solicita uma restauração.

**Fluxo principal**

1. O usuário fornece ao sistema o arquivo correspondente à cópia de segurança que deseja restaurar.
2. O sistema valida a cópia.
3. O sistema informa que os dados atuais serão substituídos.
4. O usuário confirma a operação.
5. O sistema gera uma cópia de segurança do estado atual, identificável como cópia pré-restauração.
6. O sistema confirma que a cópia pré-restauração foi preservada com sucesso em condição recuperável.
7. O sistema restaura os dados existentes na cópia fornecida pelo usuário.
8. O sistema finaliza a restauração.
9. O sistema disponibiliza o sistema no estado representado pela cópia restaurada.
10. O sistema tenta enviar a cópia pré-restauração ao endereço de e-mail cadastrado do usuário.

**Fluxos alternativos e exceções**

* Uma cópia inválida não deverá ser restaurada.
* O sistema não deverá mesclar os dados atuais com os dados da cópia.
* A confirmação será obrigatória.
* Se o usuário não confirmar a restauração, nenhuma cópia pré-restauração precisará ser gerada e os dados atuais permanecerão inalterados.
* Se a cópia pré-restauração não puder ser gerada e preservada com sucesso em condição recuperável, a restauração deverá ser cancelada e os dados atuais permanecerão inalterados.
* A falha isolada no envio por e-mail da cópia pré-restauração não impedirá a restauração quando a cópia já tiver sido preservada de forma recuperável.
* A restauração deverá preservar a integridade do estado restaurado.
* O mecanismo técnico utilizado para preservar a cópia pré-restauração em condição recuperável será definido nas etapas apropriadas.

**Pós-condições**

Os dados atuais estarão substituídos pelos dados da cópia fornecida e validada, e o estado imediatamente anterior à restauração terá sido preservado em uma cópia de segurança recuperável e identificável como pré-restauração.

**Requisitos relacionados**

RF-021.

**Regras de negócio relacionadas**

Regras do módulo Configurações referentes à restauração.
