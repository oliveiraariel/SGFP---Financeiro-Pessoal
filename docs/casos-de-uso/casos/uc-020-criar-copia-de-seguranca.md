## UC-020 — Criar cópia de segurança

**Objetivo**

Permitir que o usuário crie manualmente uma cópia dos dados do sistema.

**Ator principal**

Usuário.

**Pré-condições**

O usuário deverá estar autenticado.

**Gatilho**

O usuário solicita a criação de uma cópia.

**Fluxo principal**

1. O usuário solicita a criação do backup.
2. O sistema reúne os dados necessários para preservar o estado do sistema.
3. O sistema gera a cópia.
4. O sistema envia a cópia para o endereço de e-mail cadastrado no perfil do usuário.
5. O usuário poderá salvar o arquivo recebido e preservá-lo fora do ambiente de utilização.

**Fluxos alternativos e exceções**

* A criação do backup será manual na Versão 1.
* O envio por e-mail faz parte da Versão 1; o mecanismo técnico de geração do arquivo, proteção da cópia e entrega da mensagem será definido posteriormente nas etapas técnicas apropriadas.

**Pós-condições**

Uma cópia representando o estado dos dados no momento da operação terá sido gerada e enviada ao endereço de e-mail cadastrado do usuário.

**Requisitos relacionados**

RF-021.

**Regras de negócio relacionadas**

Regras do módulo Configurações referentes à cópia de segurança.
