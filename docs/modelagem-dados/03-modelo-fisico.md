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

## Artefato

- [Modelo Físico MySQL/MariaDB](artefatos/modelo-fisico/sgfp-modelo-fisico-mysql.sql)

O SQL acima é o Modelo Físico de referência do projeto. A estratégia de criação, migração e evolução das tabelas no plugin WordPress será definida na Etapa 9 — Arquitetura da Aplicação.
