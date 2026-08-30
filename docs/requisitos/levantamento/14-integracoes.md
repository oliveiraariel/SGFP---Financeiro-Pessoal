# **MÓDULO 14 – INTEGRAÇÕES**

**Documento:** Levantamento de Requisitos

**Versão:** 1.1

**Objetivo**

Definir a necessidade de integração do sistema com serviços ou sistemas externos.

## **Regras de Negócio**

### **RN-001 Integrações Externas**

A Versão 1 do sistema não possuirá integração com serviços ou sistemas externos.

Todas as informações financeiras serão registradas manualmente pelo usuário.

## **Decisões Tomadas**

- A Versão 1 não possuirá integrações com serviços ou sistemas externos.
- A API REST será parte da própria aplicação.
- A API REST será utilizada para disponibilizar as funcionalidades do sistema.
- A API REST permitirá futuramente que outras interfaces, como uma aplicação mobile, utilizem a mesma API.
- Integrações com serviços externos permanecerão fora do escopo da Versão 1.

## **Funcionalidades da Versão 1**

- Não haverá integração com serviços ou sistemas externos.

## **Funcionalidades Previstas para Versões Futuras**

- Integrações com serviços externos poderão ser avaliadas em versões futuras, caso exista necessidade real para o sistema.

## **Observações**

A API REST da própria aplicação não é considerada uma integração externa.

O uso do WordPress como plataforma da aplicação Web também não constitui integração externa de negócio; trata-se de uma restrição tecnológica da solução.

O backend específico do SGFP será implementado em plugin próprio e utilizará a infraestrutura REST da plataforma.

As demais definições relacionadas à arquitetura e implementação serão refinadas nas etapas correspondentes.

**Data de Revisão**

30/08/2026
