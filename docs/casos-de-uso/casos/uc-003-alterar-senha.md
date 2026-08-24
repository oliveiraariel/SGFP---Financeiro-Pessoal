## UC-003 — Alterar senha

**Objetivo**

Permitir que o usuário altere sua senha diretamente na aplicação.

**Ator principal**

Usuário.

**Pré-condições**

O usuário deverá estar autenticado.

**Gatilho**

O usuário solicita a alteração da senha.

**Fluxo principal**

1. O usuário acessa a alteração de senha.
2. O sistema solicita a senha atual e a nova senha.
3. O usuário informa os dados.
4. O sistema valida a operação.
5. O sistema exige a confirmação da alteração.
6. O sistema altera a senha.
7. O sistema informa a conclusão da operação.

**Fluxos alternativos e exceções**

* Se a senha atual estiver incorreta, a alteração não será realizada.
* Se a confirmação obrigatória não for realizada, a alteração não será concluída.

**Pós-condições**

A nova senha estará válida para futuras autenticações.

**Requisitos relacionados**

RF-003.

**Regras de negócio relacionadas**

Regras do módulo Usuários referentes à alteração de senha e confirmação obrigatória.
