# CRITÉRIOS DE ACEITAÇÃO

## Sistema de Gestão Financeira Pessoal

**Sigla:** SGFP

**Documento:** Especificação de Requisitos de Software (ERS)

**Versão:** 3.1

**Baseline:** catálogo RF-001 a RF-023; 19 requisitos ativos na V1; RF-012, RF-013, RF-014 e RF-019 preservados como futuros/inativos

**Subetapa:** Etapa 3 — Especificação de Requisitos

## 1. Objetivo

Esta seção define condições observáveis e verificáveis para validar os requisitos do SGFP. Os critérios permanecem independentes de implementação e deverão servir de base para os casos de teste da Etapa 12.

## 2. Critérios de Aceitação dos Requisitos Funcionais

### RF-001 — Cadastrar usuário

- **CA-001.1:** O sistema deverá permitir o cadastro inicial de um usuário.
- **CA-001.2:** Após cadastro válido, o usuário deverá possuir credenciais utilizáveis para autenticação.
- **CA-001.3:** Dados obrigatórios inválidos deverão impedir o cadastro.
- **CA-001.4:** O cadastro deverá permanecer associado ao usuário correspondente.

### RF-002 — Autenticar usuário

- **CA-002.1:** O sistema deverá permitir autenticação mediante e-mail e senha válidos.
- **CA-002.2:** Credenciais inválidas não deverão conceder acesso autenticado.
- **CA-002.3:** Após autenticação válida, somente os dados do usuário autenticado deverão ser carregados.
- **CA-002.4:** Após logout ou expiração da sessão, o usuário deverá realizar nova autenticação utilizando e-mail e senha.

### RF-003 — Gerenciar senha

- **CA-003.1:** O usuário autenticado deverá conseguir alterar sua senha conforme as condições definidas.
- **CA-003.2:** A alteração de senha deverá exigir confirmação obrigatória.
- **CA-003.3:** O usuário deverá conseguir iniciar a recuperação de senha por e-mail.
- **CA-003.4:** Link expirado ou já utilizado não deverá permitir nova recuperação.
- **CA-003.5:** A recuperação não deverá revelar a senha anterior.

### RF-004 — Gerenciar Conta Financeira única

- **CA-004.1:** Após cadastro válido, o usuário deverá possuir exatamente uma Conta Financeira com nome inicial **Minha Conta**.
- **CA-004.2:** O usuário deverá conseguir visualizar sua conta.
- **CA-004.3:** O usuário deverá conseguir renomear a conta sem alterar o histórico.
- **CA-004.4:** O sistema deverá impedir conta adicional.
- **CA-004.5:** A conta não poderá ser excluída isoladamente.
- **CA-004.6:** Reset deverá reprovisionar a conta.
- **CA-004.7:** O SGFP não deverá considerar provisionamento concluído sem a conta única.

### RF-005 — Consultar saldo da conta

- **CA-005.1:** Saldo = lançamentos ativos da conta.
- **CA-005.2:** Saldo não é atributo armazenado.
- **CA-005.3:** Sem efeitos financeiros, saldo derivado = R$ 0,00.
- **CA-005.4:** Valor inicial usa `SALDO_INICIAL`.
- **CA-005.5:** Efetivação/desfazimento altera/reverte corretamente o saldo.
- **CA-005.6:** Patrimônio Total não é apresentado separadamente na V1.

### RF-006 — Gerenciar compromissos financeiros

- **CA-006.1:** O usuário deverá conseguir cadastrar compromisso com nome, valor, natureza, podendo opcionalmente associá-lo a uma categoria.
- **CA-006.2:** A natureza deverá permitir Entrada ou Saída.
- **CA-006.3:** A ausência de categoria não deverá impedir a conclusão do cadastro do compromisso.
- **CA-006.4:** O usuário deverá conseguir consultar compromissos registrados.
- **CA-006.5:** Compromissos pendentes deverão poder ser alterados conforme as regras.
- **CA-006.6:** Compromissos pendentes deverão poder ser excluídos conforme as regras.
- **CA-006.7:** Compromisso com valor igual a R$ 0,00 poderá existir sem impacto financeiro no mês.
- **CA-006.8:** Compromisso efetivado não deverá ser alterado ou excluído diretamente.
- **CA-006.9:** Durante o cadastro, o usuário poderá criar uma categoria quando necessário.

### RF-007 — Efetivar e desfazer compromissos financeiros

- **CA-007.1:** O usuário deverá conseguir efetivar compromisso pendente.
- **CA-007.2:** A data atual deverá ser apresentada como padrão de efetivação.
- **CA-007.3:** O usuário poderá informar outra data de efetivação.
- **CA-007.4:** A efetivação deverá gerar o lançamento financeiro correspondente.
- **CA-007.5:** A efetivação de Entrada deverá produzir o efeito financeiro correspondente.
- **CA-007.6:** A efetivação de Saída deverá produzir o efeito financeiro correspondente.
- **CA-007.7:** O usuário deverá conseguir desfazer a efetivação.
- **CA-007.8:** O desfazimento deverá retirar o efeito financeiro do saldo aplicável.

### RF-008 — Gerenciar compromissos recorrentes

- **CA-008.1:** O usuário deverá conseguir configurar compromisso recorrente.
- **CA-008.2:** A recorrência poderá iniciar no mês corrente ou seguinte.
- **CA-008.3:** A recorrência poderá não ter término ou possuir quantidade determinada de meses.
- **CA-008.4:** A periodicidade da V1 deverá ser exclusivamente mensal.
- **CA-008.5:** O usuário deverá conseguir alterar uma ocorrência conforme as regras de aplicação.
- **CA-008.6:** O usuário deverá conseguir excluir ocorrência ou encerrar recorrência quando aplicável.
- **CA-008.7:** Alterações futuras não deverão modificar indevidamente períodos anteriores.

### RF-009 — Gerenciar categorias financeiras

- **CA-009.1:** Após o cadastro do usuário, o sistema deverá disponibilizar seu conjunto inicial de categorias já vinculado a ele, e o usuário deverá conseguir criar e visualizar suas próprias categorias.
- **CA-009.2:** O usuário deverá conseguir renomear e excluir categorias.
- **CA-009.3:** A exclusão de categoria não deverá excluir compromissos associados.
- **CA-009.4:** Compromissos que perderem categoria deverão continuar existindo.
- **CA-009.5:** O usuário deverá conseguir criar uma categoria durante o cadastro de compromisso e associá-la ao compromisso.

### RF-010 — Registrar lançamentos financeiros

- **CA-010.1:** A efetivação de compromisso deverá registrar o lançamento correspondente.
- **CA-010.2:** O lançamento deverá manter vínculo com o compromisso de origem.
- **CA-010.3:** O lançamento deverá conter informações suficientes para identificar a movimentação realizada.
- **CA-010.4:** O lançamento deverá registrar a data de efetivação.
- **CA-010.5:** O lançamento poderá possuir descrição complementar quando aplicável.
- **CA-010.6:** O valor inicial da Conta Financeira única deverá ser representado por lançamento de Entrada quando informado.
- **CA-010.7:** Informações financeiras de períodos anteriores deverão ser registradas por lançamentos conforme as funcionalidades aplicáveis.
- **CA-010.8:** Não deverá existir saldo inicial armazenado como atributo independente da conta.
- **CA-010.9:** O registro do valor inicial deverá utilizar um Lançamento de origem `SALDO_INICIAL`; a V1 não possui contas adicionais.

### RF-011 — Consultar movimentações financeiras

- **CA-011.1:** O usuário deverá conseguir consultar lançamentos e demais movimentações registradas.
- **CA-011.2:** A consulta deverá respeitar o isolamento dos dados do usuário.
- **CA-011.3:** O usuário deverá conseguir consultar o histórico do período selecionado.
- **CA-011.4:** A consulta não deverá alterar dados financeiros.

### RF-012 a RF-014 — Transferências — Versão Futura

A V1 não deverá disponibilizar criação, efetivação, desfazimento ou recorrência de transferências enquanto possuir uma única Conta Financeira por usuário.

### RF-015 — Gerenciar compromissos de cartão de crédito

- **CA-015.1:** O usuário deverá conseguir registrar pagamento de fatura como compromisso de Saída.
- **CA-015.2:** O compromisso poderá utilizar recorrência.
- **CA-015.3:** O valor poderá ser ajustado conforme o valor real de cada mês.
- **CA-015.4:** O compromisso deverá seguir as regras gerais de efetivação e desfazimento.
- **CA-015.5:** A V1 não deverá exigir cadastro de fechamento, vencimento ou limite do cartão.

### RF-016 — Gerenciar compromissos parcelados

- **CA-016.1:** O usuário deverá conseguir representar parcelamento por compromisso recorrente.
- **CA-016.2:** O usuário deverá conseguir definir a quantidade de meses.
- **CA-016.3:** O parcelamento deverá seguir as regras gerais de recorrência.
- **CA-016.4:** Alterações e efetivações deverão respeitar as regras dos compromissos.

### RF-017 — Consultar o Dashboard financeiro

- **CA-017.1:** O Dashboard deverá apresentar inicialmente o mês e ano correntes.
- **CA-017.2:** O Dashboard deverá apresentar o saldo de abertura do período selecionado, derivado do saldo final do período anterior.
- **CA-017.3:** O Dashboard deverá apresentar entradas previstas, saídas previstas e saldo final previsto.
- **CA-017.4:** O Dashboard deverá apresentar os compromissos que compõem os valores.
- **CA-017.5:** Alterações que afetem o período deverão ser refletidas no Dashboard.
- **CA-017.6:** O Dashboard não deverá possuir dados financeiros independentes.

### RF-018 — Navegar entre períodos financeiros

- **CA-018.1:** O usuário deverá conseguir selecionar mês e ano.
- **CA-018.2:** O sistema deverá carregar as informações do período selecionado.
- **CA-018.3:** O usuário deverá conseguir consultar períodos anteriores e posteriores.
- **CA-018.4:** O usuário deverá conseguir registrar informações em períodos anteriores conforme RF-010 e as regras aplicáveis.
- **CA-018.5:** A navegação não deverá criar regra temporal além das regras existentes.

### RF-019 — Gerenciar proteção por PIN — Versão Futura

Os critérios abaixo permanecem registrados para preservar a rastreabilidade do RF-019, mas não constituem critérios de aceite da Versão 1.

- **CA-019.1:** Quando a funcionalidade futura for implementada, o usuário deverá conseguir ativar voluntariamente a proteção por PIN.
- **CA-019.2:** O PIN não deverá substituir a autenticação principal por e-mail e senha.
- **CA-019.3:** O usuário deverá conseguir bloquear o SGFP e, durante uma sessão já autenticada, desbloqueá-lo utilizando o PIN válido.
- **CA-019.4:** A solução futura deverá limitar as tentativas de PIN e, em caso de esquecimento ou bloqueio, permitir redefinição mediante confirmação da senha da conta, sem recuperação própria por e-mail ou token.
- **CA-019.5:** O bloqueio automático por inatividade poderá ser incorporado na versão futura, sem constituir requisito da V1.

### RF-020 — Gerenciar tema da aplicação

- **CA-020.1:** O usuário deverá conseguir selecionar o tema Claro.
- **CA-020.2:** O usuário deverá conseguir selecionar o tema Escuro.
- **CA-020.3:** A interface deverá aplicar o tema selecionado.

### RF-021 — Gerenciar cópias de segurança e restauração

- **CA-021.1:** Solicitar backup manual.
- **CA-021.2:** Entrega em ZIP para download local.
- **CA-021.3:** Conteúdo versionado e íntegro.
- **CA-021.4:** Selecionar ZIP válido para restauração.
- **CA-021.5:** Avisar e confirmar substituição integral.
- **CA-021.6:** Gerar cópia pré-restauração recuperável.
- **CA-021.7:** Não realizar mesclagem.
- **CA-021.8:** Falha da cópia pré-restauração cancela a restauração.
- **CA-021.9:** Cópia pré-restauração usa o mesmo formato lógico e pode ser baixada.
- **CA-021.10:** Backup/restauração não dependem de e-mail.
- **CA-021.11:** Cópias devem ser protegidas contra acesso não autorizado/adulteração.

### RF-022 — Resetar perfil financeiro

- **CA-022.1:** Usuário autenticado pode iniciar reset.
- **CA-022.2:** Duas etapas de confirmação.
- **CA-022.3:** Frase final exata **RESETAR PERFIL**.
- **CA-022.4:** Apaga dados SGFP e reprovisiona estado inicial.
- **CA-022.5:** Login WordPress permanece válido.

### RF-023 — Excluir conta de acesso

- **CA-023.1:** Usuário autenticado pode iniciar exclusão.
- **CA-023.2:** Duas etapas de confirmação.
- **CA-023.3:** Frase final exata **EXCLUIR CONTA**.
- **CA-023.4:** Remove dados SGFP e identidade/login WordPress.
- **CA-023.5:** Login excluído não acessa mais o portal.

## 3. Critérios de Aceitação dos Requisitos Não Funcionais

### RNF-001 — Segurança

- **CA-NF-001.1:** Operações protegidas deverão exigir autenticação ou autorização compatível.
- **CA-NF-001.2:** Um usuário não deverá acessar dados de outro usuário.

### RNF-002 — Proteção das Credenciais

- **CA-NF-002.1:** Senhas não deverão ser armazenadas em texto puro.
- **CA-NF-002.2:** O sistema não deverá disponibilizar senha anterior durante recuperação.

### RNF-003 — Isolamento dos Dados dos Usuários

- **CA-NF-003.1:** Consultas e operações deverão retornar ou modificar somente dados do usuário autenticado.

### RNF-004 — Proteção dos Dados

- **CA-NF-004.1:** Dados pessoais e financeiros não deverão ser expostos por operações não autorizadas.
- **CA-NF-004.2:** Mecanismos técnicos específicos serão verificados na etapa de testes correspondente.

### RNF-005 — Integridade dos Dados Financeiros

- **CA-NF-005.1:** Operações financeiras válidas não deverão produzir inconsistências entre compromissos, lançamentos, contas e saldos.

### RNF-006 — Consistência das Operações

- **CA-NF-006.1:** Efetivações deverão manter consistência entre compromisso, lançamento e saldo.
- **CA-NF-006.2:** Reset e exclusão não deverão ser apresentados como sucesso quando deixarem estado parcialmente removido.

### RNF-007 — Preservação do Histórico Financeiro

- **CA-NF-007.1:** Alterações aplicadas a períodos futuros não deverão modificar indevidamente períodos anteriores.

### RNF-008 — Confiabilidade dos Cálculos Financeiros

- **CA-NF-008.1:** Saldos e valores calculados deverão corresponder aos movimentos registrados e às regras aplicáveis.

### RNF-009 — Integridade dos Valores Monetários

- **CA-NF-009.1:** Operações monetárias deverão manter precisão suficiente para evitar divergências indevidas.

### RNF-010 — Desempenho

- **CA-NF-010.1:** O sistema deverá atender aos critérios quantitativos de desempenho definidos posteriormente.

### RNF-011 — Disponibilidade

- **CA-NF-011.1:** O sistema deverá atender à meta de disponibilidade definida posteriormente para a infraestrutura.

### RNF-012 — Usabilidade

- **CA-NF-012.1:** Funcionalidades deverão apresentar informações e comandos de forma clara e consistente.
- **CA-NF-012.2:** Fluxos principais deverão permitir concluir objetivos sem exigir conhecimento técnico interno.

### RNF-013 — Responsividade

- **CA-NF-013.1:** A interface deverá adaptar-se aos tamanhos de tela suportados.
- **CA-NF-013.2:** Critérios técnicos detalhados serão verificados conforme a especificação de interface.

### RNF-014 — Compatibilidade

- **CA-NF-014.1:** O sistema deverá funcionar nos navegadores e ambientes oficialmente suportados.

### RNF-015 — Manutenibilidade

- **CA-NF-015.1:** A implementação deverá permitir correção e evolução sem alterações desnecessariamente amplas.

### RNF-016 — Escalabilidade

- **CA-NF-016.1:** A arquitetura deverá permitir evolução conforme necessidades identificadas para a aplicação.

### RNF-017 — Recuperação de Dados

- **CA-NF-017.1:** Uma cópia válida deverá permitir restaurar os dados que representa.
- **CA-NF-017.2:** O estado restaurado deverá permanecer consistente.

### RNF-018 — Proteção das Cópias de Segurança

- **CA-NF-018.1:** Uma cópia não deverá ser disponibilizada de modo a permitir acesso não autorizado.

### RNF-019 — Privacidade

- **CA-NF-019.1:** O tratamento de dados pessoais e financeiros deverá respeitar os requisitos de privacidade definidos.
- **CA-NF-019.2:** Requisitos legais e técnicos específicos serão incorporados quando definidos.

### RNF-020 — Evolutividade

- **CA-NF-020.1:** Alterações futuras deverão poder ser incorporadas sem comprometer desnecessariamente funcionalidades existentes.

## 4. Critérios de Aceitação das Restrições da V1

As restrições deverão ser verificadas por inspeção documental e, quando produzirem comportamento observável, por testes correspondentes.

- A V1 não deverá realizar integração bancária ou financeira externa, conforme RE-002, RE-003 e RE-007.
- As recorrências da V1 deverão utilizar exclusivamente periodicidade mensal, conforme RE-004 e as regras do módulo de Recorrência.
- Deverá existir exatamente uma Conta Financeira por usuário, conforme RE-005.
- Transferências e Patrimônio Total ficam fora da V1, conforme RE-006 e RE-007.
- Reset e exclusão exigem dupla confirmação, conforme RE-019.
- O Dashboard deverá ser uma visão derivada e consolidada, sem manter dados financeiros independentes, conforme RE-010.
- Funcionalidades fora do escopo da V1 não deverão ser disponibilizadas, conforme RE-001 e as restrições específicas da V1. O RF-019 e seus critérios CA-019.1 a CA-019.5 permanecem documentados exclusivamente para versão futura.

## 5. Critérios em Aberto

Os seguintes critérios permanecem deliberadamente sem valores quantitativos nesta versão:

| Aspecto | Situação |
| --- | --- |
| Tempo máximo de resposta | A definir |
| Meta de disponibilidade | A definir |
| Navegadores suportados | A definir |
| Versões suportadas | A definir |
| Critérios técnicos de responsividade | A definir |
| Capacidade de usuários | A definir |
| Capacidade de dados | A definir |
| Critérios quantitativos de escalabilidade | A definir |
| Critérios técnicos detalhados de segurança | A definir |

Esses itens dependem de decisões posteriores e não constituem novos requisitos funcionais. A preservação do estado imediatamente anterior à restauração já possui comportamento de negócio e mecanismo técnico definidos nas etapas concluídas; a Etapa 12 deverá consolidar sua verificação formal.

## 6. Relação com Casos de Teste e Regras de Negócio

Cada caso de teste deverá referenciar, quando aplicável:

**Requisito → Critério de Aceitação → Caso de Teste → Resultado**

Os critérios deverão respeitar as regras de negócio consolidadas no Levantamento de Requisitos. As regras não são duplicadas nesta seção.

## 7. Histórico de atualização

### Versão 3.1 — 23/09/2026

Corrigiu o cabeçalho da baseline para RF-001 a RF-023, 19 requisitos ativos e quatro requisitos futuros/inativos; corrigiu a duplicação do título da seção 3 e atualizou a referência ao mecanismo técnico de restauração já definido.

### Versão 3.0 — 14/09/2026

Alinhou conta única, retirada de transferências/patrimônio, backup ZIP local e RF-022/RF-023.

### Versão 2.4 - 04/09/2026

Atualizou os critérios CA-006.1 e CA-006.3 para refletir a decisão de tornar opcional a associação de Categoria ao Compromisso Financeiro, presenvanda os identificadores e a rastreabilidade existentes

### Versão 2.3 — 30/08/2026

Resolveu a interpretação de CA-021.6 e acrescentou critérios verificáveis para a cópia automática pré-restauração, incluindo preservação recuperável obrigatória, cancelamento em caso de falha, compatibilidade com o processo normal de restauração, identificação da origem e tratamento não bloqueante da falha isolada de envio por e-mail.

### Versão 2.2 — 30/08/2026

Incluiu critério explícito para a entrega do backup por e-mail, registrou a pendência de interpretação de CA-021.6 e manteve a padronização dos identificadores RNF no formato `RNF-001` a `RNF-020`.