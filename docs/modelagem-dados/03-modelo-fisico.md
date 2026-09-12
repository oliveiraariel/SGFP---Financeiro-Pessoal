# Etapa 8 — Modelo Físico

## Objetivo

Transformar a modelagem validada em uma estrutura relacional compatível com MySQL/MariaDB e com a plataforma WordPress, definindo tabelas, colunas, tipos, chaves, restrições e índices.

**Status:** concluída e validada em 05/09/2026.

## Resultado

O Modelo Físico da V1 foi consolidado considerando, entre outros pontos:

- usuários e autenticação providos pelo WordPress, sem tabela `USUARIO` própria do SGFP;
- Conta Principal e Contas Secundárias;
- saldo derivado de lançamentos, sem saldo armazenado na conta;
- Categoria opcional para Compromisso Financeiro;
- Recorrência representada separadamente;
- Transferência como especialização de Compromisso Financeiro;
- dois lançamentos para a efetivação de uma transferência;
- preservação do histórico por meio do estado dos lançamentos;
- integridade e isolamento dos registros por usuário.

Durante a implementação da Etapa 10 foi adicionada uma estrutura técnica de suporte,
sem alterar as entidades financeiras do modelo validado: `sgfp_token_restauracao`.
Ela é uma tabela própria do plugin, prefixada, InnoDB, com FK para `wp_users`,
ownership por usuário, hash SHA-256 único, expiração e estado de claim. Sua criação
ocorre por migração incremental; ela não substitui nem altera o artefato SQL das
seis tabelas financeiras.

## Artefato

- [Modelo Físico MySQL/MariaDB](artefatos/modelo-fisico/sgfp-modelo-fisico-mysql.sql)

O SQL acima é o Modelo Físico de referência do projeto. A estratégia de criação, migração e evolução das tabelas no plugin WordPress será definida na Etapa 9 — Arquitetura da Aplicação.
