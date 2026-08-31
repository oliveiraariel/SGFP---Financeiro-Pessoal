# **MÓDULO 11 – SEGURANÇA**

**Documento:** Levantamento de Requisitos  
**Versão:** 1.2

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
- Proteção das operações e dos recursos da API contra acessos não autorizados.

A proteção básica da API faz parte da Versão 1. Isso inclui exigir autenticação e autorização compatíveis com a operação, respeitar o contexto do usuário autenticado e impedir o acesso a dados pertencentes a outros usuários.

Os mecanismos técnicos concretos utilizados para atender essas exigências serão definidos na Etapa 9 — Arquitetura da Aplicação e detalhados na Etapa 10 — Desenvolvimento da API.

A proteção por PIN **não faz parte da Versão 1**.

Esses requisitos permanecerão registrados nos respectivos módulos, não sendo duplicados como novas regras neste módulo.

## **Aspectos para Estudo e Evolução Futura**

Os seguintes pontos serão mantidos como **itens de estudo para versões futuras**, não constituindo requisitos obrigatórios adicionais da Versão 1 neste momento:

### **1. Proteção Opcional por PIN**

Avaliar e implementar, em versão futura, um PIN opcional para bloqueio rápido do SGFP durante uma sessão já autenticada.

O PIN não deverá substituir a autenticação principal por e-mail e senha.

Quando implementado, o usuário poderá bloquear o SGFP e utilizar o PIN apenas para desbloquear a aplicação dentro da sessão autenticada.

Em caso de esquecimento ou bloqueio do PIN, a redefinição deverá ocorrer mediante nova confirmação da senha da conta, sem mecanismo próprio de recuperação de PIN por e-mail ou token.

### **2. Expiração e Bloqueio por Inatividade**

Avaliar a possibilidade de bloquear o SGFP ou encerrar automaticamente uma sessão após determinado período de inatividade.

Esse comportamento poderá ser estudado em conjunto com a proteção futura por PIN.

### **3. Proteção contra Tentativas Automatizadas de Login**

Avaliar mecanismos adicionais para dificultar ataques automatizados contra o processo de autenticação, como tentativas repetitivas de descoberta de senha.

### **4. Encerramento de Sessão**

Avaliar mecanismos adicionais relacionados ao encerramento manual e automático das sessões dos usuários.

### **5. Mecanismos Adicionais de Proteção das APIs**

A proteção básica das APIs é obrigatória na Versão 1. Para versões futuras, poderão ser avaliados mecanismos adicionais de endurecimento de segurança, auditoria, limitação de chamadas, políticas mais específicas de autorização e outras proteções complementares que venham a ser justificadas.

### **6. Controle de Acesso Adicional**

A Versão 1 já deverá garantir que cada operação seja executada somente por usuário autenticado e autorizado e somente sobre dados pertencentes ao seu contexto. Controles adicionais ou políticas mais granulares poderão ser avaliados futuramente conforme a evolução da aplicação.

### **7. Registro de Atividades Sensíveis**

Avaliar a necessidade de registrar determinadas operações relevantes para segurança e auditoria, como alterações de credenciais, restaurações de cópia de segurança e outras operações sensíveis.

### **8. Proteção de Dados em Trânsito**

Avaliar e definir, na arquitetura e na infraestrutura, os mecanismos técnicos necessários para proteger as informações durante sua transmissão entre navegador, aplicação, API e demais serviços utilizados.

### **9. Proteção contra Acesso Direto aos Arquivos**

Caso o sistema passe a armazenar arquivos e comprovantes, avaliar mecanismos que impeçam o acesso direto aos arquivos sem a devida autorização do usuário.

## **Decisões Tomadas**

- A Segurança será tratada como uma preocupação transversal do sistema.
- Os requisitos de segurança diretamente relacionados a funcionalidades específicas serão registrados nos respectivos módulos.
- Não será criada uma entidade de domínio denominada "Segurança" apenas para representar esses requisitos.
- Na Versão 1, a autenticação será realizada por e-mail e senha.
- O WordPress será utilizado para os mecanismos de identidade, autenticação, sessão, armazenamento seguro e recuperação de senha.
- A proteção básica das operações e dos recursos da API contra acesso não autorizado faz parte da Versão 1.
- Os mecanismos técnicos de autenticação, autorização e proteção da API serão refinados nas etapas de Arquitetura e Desenvolvimento da API, sem alterar essa obrigação da V1.
- O PIN foi retirado do escopo da Versão 1 e permanece previsto para versão futura.
- A solução futura de PIN será um mecanismo secundário de bloqueio rápido durante sessão autenticada, sem substituir e-mail e senha.
- Não será criado mecanismo próprio de recuperação de PIN por e-mail ou token.
- Os aspectos de segurança adicionais ainda não necessários para a V1 serão mantidos como pontos de estudo e evolução futura.
- Os itens de evolução futura não deverão ser tratados como requisitos obrigatórios da V1 sem uma decisão posterior.

## **Funcionalidades da Versão 1**

Não haverá uma funcionalidade independente denominada **Segurança**.

Os mecanismos de segurança necessários à V1 serão implementados juntamente com as funcionalidades às quais estão relacionados, conforme definido nos respectivos módulos.

## **Funcionalidades Previstas para Versões Futuras**

- Proteção opcional por PIN.
- Bloqueio manual do SGFP e desbloqueio por PIN.
- Redefinição do PIN mediante confirmação da senha da conta.
- Bloqueio ou expiração automática por inatividade.
- Proteção adicional contra tentativas automatizadas de login.
- Mecanismos adicionais de encerramento de sessão.
- Mecanismos adicionais de proteção e controle de acesso das APIs.
- Registro de atividades sensíveis para auditoria.
- Proteção adicional dos dados em trânsito.
- Proteção de arquivos e comprovantes contra acesso direto não autorizado.

## **Observações**

A subetapa **Segurança** não representa necessariamente uma entidade do domínio ou uma funcionalidade isolada do SGFP.

Sua finalidade é identificar e organizar os requisitos de segurança que atravessam diferentes partes do sistema.

Os requisitos já definidos na Versão 1 permanecem registrados em seus respectivos módulos para evitar duplicidade e inconsistência na documentação.

Os aspectos relacionados à arquitetura, mecanismos concretos de autorização da API, proteção de dados, infraestrutura e demais decisões técnicas serão refinados nas etapas apropriadas do projeto.

**Data de Revisão**

30/08/2026
