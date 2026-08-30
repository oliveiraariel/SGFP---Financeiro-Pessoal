# **MÓDULO 11 – SEGURANÇA**

**Documento:** Levantamento de Requisitos

**Versão:** 1.1

**Objetivo**

Identificar requisitos relacionados à proteção do sistema, dos dados dos usuários, da autenticação e das operações realizadas no SGFP.

Os requisitos de segurança diretamente necessários à Versão 1 serão definidos nos módulos correspondentes, quando estiverem relacionados a uma funcionalidade específica.

Os demais aspectos de segurança identificados durante o levantamento serão registrados como pontos para avaliação e evolução futura do sistema.

## **Segurança já contemplada na Versão 1**

Os seguintes aspectos já foram definidos em outras subetapas:

- Autenticação por e-mail e senha.
- Utilização dos mecanismos nativos do WordPress para identidade, autenticação, sessão, armazenamento seguro e recuperação de senha.
- Isolamento dos dados entre usuários.
- Cada operação sobre dados privados deverá ser executada no contexto do usuário autenticado.
- Proteção da cópia de segurança.
- Confirmação antes da restauração de uma cópia de segurança.
- Substituição integral dos dados durante uma restauração.

A proteção por PIN **não faz parte da Versão 1**.

Esses requisitos permanecerão registrados nos respectivos módulos, não sendo duplicados como novas regras neste módulo.

## **Aspectos para Estudo e Evolução Futura**

### **1. Proteção Opcional por PIN**

Avaliar e implementar, em versão futura, um PIN opcional para bloqueio rápido do SGFP durante uma sessão já autenticada.

O PIN não deverá substituir a autenticação principal por e-mail e senha.

Em caso de esquecimento ou bloqueio do PIN, a redefinição deverá ocorrer mediante nova confirmação da senha da conta, sem mecanismo próprio de recuperação de PIN por e-mail ou token.

Os seguintes pontos serão mantidos como **itens de estudo para versões futuras**, não constituindo requisitos obrigatórios da Versão 1 neste momento:

### **1. Expiração de Sessão**

Avaliar a possibilidade de encerrar automaticamente uma sessão após determinado período de inatividade.

### **2. Proteção contra Tentativas Automatizadas de Login**

Avaliar mecanismos para dificultar ataques automatizados contra o processo de autenticação, como tentativas repetitivas de descoberta de senha.

### **3. Encerramento de Sessão**

Avaliar mecanismos adicionais relacionados ao encerramento manual e automático das sessões dos usuários.

### **4. Proteção das APIs**

Avaliar mecanismos de autenticação, autorização, validação e proteção das APIs REST contra acessos ou chamadas não autorizadas.

### **5. Controle de Acesso**

Avaliar mecanismos adicionais para garantir que cada operação realizada pela aplicação seja executada somente por usuários devidamente autorizados.

### **6. Registro de Atividades Sensíveis**

Avaliar a necessidade de registrar determinadas operações relevantes para segurança e auditoria, como alterações de credenciais, restaurações de cópia de segurança e outras operações sensíveis.

### **7. Proteção de Dados em Trânsito**

Avaliar mecanismos para garantir a proteção das informações durante sua transmissão entre navegador, aplicação, API e demais serviços utilizados.

### **8. Proteção contra Acesso Direto aos Arquivos**

Caso o sistema passe a armazenar arquivos e comprovantes, avaliar mecanismos que impeçam o acesso direto aos arquivos sem a devida autorização do usuário.

## **Decisões Tomadas**

- A Segurança será tratada como uma preocupação transversal do sistema.
- Os requisitos de segurança diretamente relacionados a funcionalidades específicas serão registrados nos respectivos módulos.
- Não será criada uma entidade de domínio denominada "Segurança" apenas para representar esses requisitos.
- Os aspectos de segurança ainda não necessários para a V1 serão mantidos como pontos de estudo e evolução futura.
- Os itens de evolução futura não deverão ser tratados como requisitos obrigatórios da V1 sem uma decisão posterior.

## **Funcionalidades da Versão 1**

Não haverá uma funcionalidade independente denominada **Segurança**.

Os mecanismos de segurança necessários à V1 serão implementados juntamente com as funcionalidades às quais estão relacionados, conforme definido nos respectivos módulos.

## **Funcionalidades Previstas para Versões Futuras**

- Expiração automática de sessão.
- Proteção adicional contra tentativas automatizadas de login.
- Mecanismos adicionais de encerramento de sessão.
- Proteção e controle de acesso das APIs.
- Mecanismos adicionais de autorização.
- Registro de atividades sensíveis para auditoria.
- Proteção adicional dos dados em trânsito.
- Proteção de arquivos e comprovantes contra acesso direto não autorizado.

## **Observações**

A subetapa **Segurança** não representa necessariamente uma entidade do domínio ou uma funcionalidade isolada do SGFP.

Sua finalidade é identificar e organizar os requisitos de segurança que atravessam diferentes partes do sistema.

Os requisitos já definidos na Versão 1 permanecem registrados em seus respectivos módulos para evitar duplicidade e inconsistência na documentação.

Os aspectos relacionados à arquitetura, tecnologias, mecanismos criptográficos, autenticação de APIs, armazenamento seguro e demais decisões técnicas serão definidos posteriormente nas etapas apropriadas do projeto.

**Data de Revisão**

30/08/2026
