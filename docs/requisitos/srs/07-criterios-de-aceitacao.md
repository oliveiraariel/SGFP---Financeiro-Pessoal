# CRITÉRIOS DE ACEITAÇÃO

## Sistema de Gestão Financeira Pessoal

**Sigla:** SGFP

**Documento:** Especificação de Requisitos de Software (ERS)

**Versão:** 2.1

**Baseline:** catálogo preservado com 21 requisitos funcionais; 20 requisitos ativos na V1 e RF-019 adiado para versão futura

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

### RF-004 — Gerenciar contas financeiras

- **CA-004.1:** O usuário deverá conseguir criar uma conta financeira.
- **CA-004.2:** O sistema deverá permitir múltiplas contas do mesmo usuário.
- **CA-004.3:** O usuário deverá conseguir visualizar suas contas.
- **CA-004.4:** O usuário deverá conseguir renomear uma conta sem alterar seu histórico.
- **CA-004.5:** O sistema deverá respeitar a existência de uma única conta principal.
- **CA-004.6:** O sistema deverá respeitar a distinção entre conta principal e contas secundárias.
- **CA-004.7:** A V1 não deverá disponibilizar exclusão de contas financeiras.

### RF-005 — Consultar saldos das contas e patrimônio total

- **CA-005.1:** O sistema deverá calcular e apresentar o saldo de cada conta a partir dos movimentos registrados.
- **CA-005.2:** Entrada efetivada deverá aumentar o saldo aplicável.
- **CA-005.3:** Saída efetivada deverá reduzir o saldo aplicável.
- **CA-005.4:** O patrimônio total deverá ser calculado a partir dos saldos das contas.
- **CA-005.5:** Transferência entre contas próprias não deverá alterar o patrimônio total.
- **CA-005.6:** O saldo poderá ser positivo, zero ou negativo conforme as regras de negócio.
- **CA-005.7:** Saldos não deverão depender de atributo independente de saldo inicial.
- **CA-005.8:** O valor inicial de uma conta secundária deverá ser representado por transferência com a conta principal, sem Entrada direta para sua composição inicial.

### RF-006 — Gerenciar compromissos financeiros

- **CA-006.1:** O usuário deverá conseguir cadastrar compromisso com nome, valor, natureza e categoria.
- **CA-006.2:** A natureza deverá permitir Entrada ou Saída.
- **CA-006.3:** A categoria deverá ser obrigatória para concluir o cadastro.
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
- **CA-010.6:** O valor inicial da conta principal deverá ser representado por lançamento de Entrada quando informado.
- **CA-010.7:** Informações financeiras de períodos anteriores deverão ser registradas por lançamentos conforme as funcionalidades aplicáveis.
- **CA-010.8:** Não deverá existir saldo inicial armazenado como atributo independente da conta.
- **CA-010.9:** O registro do valor inicial da conta principal deverá utilizar um lançamento de Entrada; essa regra não deverá ser aplicada como Entrada direta a contas secundárias.

### RF-011 — Consultar movimentações financeiras

- **CA-011.1:** O usuário deverá conseguir consultar lançamentos e demais movimentações registradas.
- **CA-011.2:** A consulta deverá respeitar o isolamento dos dados do usuário.
- **CA-011.3:** O usuário deverá conseguir consultar o histórico do período selecionado.
- **CA-011.4:** A consulta não deverá alterar dados financeiros.

### RF-012 — Gerenciar transferências entre contas

- **CA-012.1:** O usuário deverá conseguir informar origem, destino e valor.
- **CA-012.2:** Origem e destino não poderão ser a mesma conta.
- **CA-012.3:** A transferência deverá ocorrer entre contas do mesmo usuário.
- **CA-012.4:** Transferência não efetivada não deverá alterar saldos.
- **CA-012.5:** O usuário deverá conseguir alterar transferência não efetivada.
- **CA-012.6:** O usuário deverá conseguir excluir transferência não efetivada.
- **CA-012.7:** Transferência efetivada não deverá ser alterada ou excluída diretamente.

### RF-013 — Efetivar e desfazer transferências

- **CA-013.1:** O usuário deverá conseguir efetivar transferência válida.
- **CA-013.2:** A efetivação deverá diminuir o saldo da origem.
- **CA-013.3:** A efetivação deverá aumentar o saldo do destino.
- **CA-013.4:** A operação deverá aparecer no histórico das duas contas.
- **CA-013.5:** O desfazimento deverá reverter os dois efeitos financeiros.
- **CA-013.6:** O desfazimento deverá manter origem, destino e históricos consistentes.
- **CA-013.7:** A insuficiência de saldo não deverá impedir a efetivação, conforme regra existente.

### RF-014 — Gerenciar transferências recorrentes

- **CA-014.1:** O usuário deverá conseguir configurar transferência recorrente.
- **CA-014.2:** A transferência recorrente deverá seguir as regras mensais.
- **CA-014.3:** O usuário deverá conseguir alterar, excluir ou encerrar recorrências conforme as regras.

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

- **CA-021.1:** O usuário deverá conseguir solicitar manualmente uma cópia de segurança.
- **CA-021.2:** A cópia deverá representar o estado dos dados no momento da criação.
- **CA-021.3:** O usuário deverá conseguir fornecer uma cópia válida para restauração.
- **CA-021.4:** A restauração deverá informar e confirmar a substituição dos dados atuais.
- **CA-021.5:** A restauração deverá substituir os dados pelos dados representados na cópia.
- **CA-021.6:** A restauração deverá preservar o estado anterior conforme as regras existentes.
- **CA-021.7:** A V1 não deverá realizar mesclagem de dados.

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
- **CA-NF-006.2:** Transferências deverão manter consistência entre origem, destino e registros.

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
- Deverá existir exatamente uma conta principal por usuário, conforme RE-005.
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

Esses itens dependem de decisões técnicas posteriores e não constituem novos requisitos funcionais.

## 6. Relação com Casos de Teste e Regras de Negócio

Cada caso de teste deverá referenciar, quando aplicável:

**Requisito → Critério de Aceitação → Caso de Teste → Resultado**

Os critérios deverão respeitar as regras de negócio consolidadas no Levantamento de Requisitos. As regras não são duplicadas nesta seção.
