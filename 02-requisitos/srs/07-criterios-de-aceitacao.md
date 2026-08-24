> **Status de consolidação: REVISÃO NECESSÁRIA.**  
> Este arquivo foi produzido com um catálogo alternativo de **25 requisitos funcionais**, enquanto o catálogo atualmente consolidado em `03-requisitos-funcionais.md` possui **19 RFs**. O conteúdo foi preservado para não perder trabalho, mas seus vínculos RF/UC/CA não devem ser tratados como definitivos até a realinhação dos identificadores.

# CRITÉRIOS DE ACEITAÇÃO

## Sistema de Gestão Financeira Pessoal

**Sigla:** SGFP

**Documento:** Especificação de Requisitos de Software (ERS)

**Versão:** 1.0

**Subetapa:** Etapa 3 — Especificação de Requisitos

**Data:** 23/08/2026

## 1. Objetivo

Esta seção define os critérios utilizados para verificar se os requisitos do SGFP foram atendidos.

Os critérios de aceitação traduzem os requisitos especificados em condições observáveis e verificáveis, servindo como base para a elaboração dos casos de teste e para a validação do sistema.

Um requisito somente deverá ser considerado atendido quando seus critérios de aceitação aplicáveis forem satisfeitos.

Os critérios definidos nesta etapa deverão permanecer no nível necessário à validação dos requisitos, sem antecipar detalhes de implementação que pertençam às etapas de arquitetura e desenvolvimento.

## 2. Princípios de Aceitação

Os critérios de aceitação deverão:

1. ser observáveis ou verificáveis;
2. estar relacionados a um requisito identificado;
3. considerar as regras de negócio aplicáveis;
4. contemplar, quando relevante, condições normais, alternativas e exceções;
5. evitar dependência de uma implementação específica;
6. permitir posterior transformação em casos de teste.

Quando um requisito depender de critérios quantitativos ainda não definidos, o requisito poderá ser considerado funcionalmente identificado, mas sua aceitação quantitativa ficará condicionada à definição posterior do respectivo critério técnico.

## 3. Critérios de Aceitação dos Requisitos Funcionais

### RF-001 — Gerenciar conta de usuário

**CA-001.1** — O sistema deverá permitir o cadastro inicial de um usuário.

**CA-001.2** — Após um cadastro válido, o usuário deverá possuir credenciais que possam ser utilizadas para autenticação.

**CA-001.3** — O sistema deverá impedir o cadastro quando os dados obrigatórios não atenderem às regras definidas.

**CA-001.4** — O sistema deverá manter o cadastro associado ao usuário correspondente.

### RF-002 — Autenticar usuário

**CA-002.1** — O sistema deverá permitir a autenticação mediante e-mail e senha válidos.

**CA-002.2** — Credenciais inválidas não deverão conceder acesso autenticado.

**CA-002.3** — Após a autenticação válida, o sistema deverá carregar apenas os dados pertencentes ao usuário autenticado.

**CA-002.4** — O PIN não deverá ser exigido como substituto da autenticação inicial por e-mail e senha.

### RF-003 — Gerenciar senha

**CA-003.1** — O usuário autenticado deverá conseguir alterar sua senha mediante as condições definidas para a operação.

**CA-003.2** — A alteração deverá exigir confirmação obrigatória.

**CA-003.3** — O usuário deverá conseguir iniciar o processo de recuperação de senha por e-mail.

**CA-003.4** — Um link de recuperação expirado ou já utilizado não deverá permitir a criação de nova senha.

**CA-003.5** — A recuperação não deverá revelar a senha anterior.

### RF-004 — Gerenciar contas financeiras

**CA-004.1** — O usuário deverá conseguir criar uma conta financeira.

**CA-004.2** — O sistema deverá permitir a manutenção de múltiplas contas do mesmo usuário.

**CA-004.3** — O usuário deverá conseguir visualizar suas contas.

**CA-004.4** — O usuário deverá conseguir renomear uma conta sem alterar seu histórico financeiro.

**CA-004.5** — O sistema deverá respeitar a existência de uma única conta principal.

**CA-004.6** — O sistema deverá respeitar a distinção entre conta principal e contas secundárias conforme as regras de negócio.

### RF-005 — Gerenciar o saldo das contas

**CA-005.1** — O sistema deverá apresentar o saldo de cada conta de acordo com as movimentações aplicáveis.

**CA-005.2** — Uma Entrada efetivada deverá produzir aumento correspondente no saldo da conta aplicável.

**CA-005.3** — Uma Saída efetivada deverá produzir redução correspondente no saldo da conta aplicável.

**CA-005.4** — Uma transferência efetivada deverá alterar os saldos das contas de origem e destino de forma consistente.

**CA-005.5** — O saldo deverá ser atualizado após efetivação e desfazimento de movimentações.

**CA-005.6** — O saldo poderá ser positivo, zero ou negativo, conforme as regras de negócio.

### RF-006 — Gerenciar o saldo inicial da conta principal

**CA-006.1** — O sistema deverá permitir o registro do saldo real da conta principal no início da utilização.

**CA-006.2** — O valor informado deverá ser utilizado como referência para os cálculos financeiros posteriores.

**CA-006.3** — O sistema deverá admitir valor inicial positivo, zero ou negativo, conforme as regras definidas.

### RF-007 — Consultar patrimônio total

**CA-007.1** — O sistema deverá apresentar o patrimônio total calculado a partir das contas e movimentações aplicáveis.

**CA-007.2** — Alterações nos saldos das contas deverão refletir no patrimônio total.

**CA-007.3** — Uma transferência entre contas próprias não deverá alterar o patrimônio total.

### RF-008 — Gerenciar compromissos financeiros

**CA-008.1** — O usuário deverá conseguir cadastrar um compromisso financeiro com nome, valor, natureza e categoria.

**CA-008.2** — A natureza do compromisso deverá permitir Entrada ou Saída.

**CA-008.3** — A categoria deverá ser obrigatória para concluir o cadastro.

**CA-008.4** — O usuário deverá conseguir consultar compromissos registrados.

**CA-008.5** — Compromissos pendentes deverão poder ser alterados conforme as regras de negócio.

**CA-008.6** — Compromissos pendentes deverão poder ser excluídos conforme as regras de negócio.

**CA-008.7** — Um compromisso com valor igual a R$ 0,00 deverá poder existir sem produzir impacto financeiro naquele mês.

**CA-008.8** — Compromissos já efetivados não deverão ser alterados ou excluídos diretamente.

### RF-009 — Gerenciar a efetivação de compromissos financeiros

**CA-009.1** — O usuário deverá conseguir efetivar um compromisso pendente.

**CA-009.2** — O sistema deverá apresentar a data atual como padrão de efetivação.

**CA-009.3** — O usuário deverá poder informar uma data diferente da data atual.

**CA-009.4** — A efetivação deverá gerar o lançamento financeiro correspondente.

**CA-009.5** — A efetivação de Entrada deverá produzir o efeito financeiro correspondente.

**CA-009.6** — A efetivação de Saída deverá produzir o efeito financeiro correspondente.

**CA-009.7** — O usuário deverá conseguir desfazer uma efetivação.

**CA-009.8** — Ao desfazer a efetivação, seu efeito financeiro deverá ser retirado do saldo aplicável.

### RF-010 — Gerenciar categorias financeiras

**CA-010.1** — O usuário deverá conseguir criar uma categoria.

**CA-010.2** — O usuário deverá conseguir visualizar as categorias disponíveis.

**CA-010.3** — O usuário deverá conseguir renomear uma categoria.

**CA-010.4** — O usuário deverá conseguir excluir uma categoria.

**CA-010.5** — A exclusão de uma categoria não deverá excluir os compromissos associados.

**CA-010.6** — Compromissos que perderem sua categoria deverão continuar existindo.

### RF-011 — Criar categoria durante o cadastro de compromisso

**CA-011.1** — Durante o cadastro de um compromisso, o sistema deverá permitir a criação de uma nova categoria.

**CA-011.2** — A nova categoria deverá ficar disponível para associação ao compromisso que originou sua criação.

### RF-012 — Gerenciar compromissos recorrentes

**CA-012.1** — O usuário deverá conseguir configurar um compromisso como recorrente.

**CA-012.2** — O usuário deverá conseguir definir o início da recorrência no mês corrente ou no mês seguinte.

**CA-012.3** — O usuário deverá conseguir definir recorrência sem término ou por quantidade determinada de meses.

**CA-012.4** — A periodicidade das recorrências da V1 deverá ser exclusivamente mensal.

**CA-012.5** — O usuário deverá conseguir alterar o valor de uma ocorrência conforme as regras de aplicação da recorrência.

**CA-012.6** — O usuário deverá conseguir excluir apenas uma ocorrência sem encerrar a recorrência, quando essa operação for aplicável.

**CA-012.7** — O usuário deverá conseguir encerrar uma recorrência a partir do período selecionado, sem alterar os períodos anteriores.

**CA-012.8** — O encerramento de uma recorrência não deverá alterar ocorrências de períodos anteriores.

### RF-013 — Registrar lançamentos financeiros

**CA-013.1** — A efetivação de um compromisso deverá resultar no registro do lançamento correspondente.

**CA-013.2** — O lançamento deverá manter vínculo com o compromisso que lhe deu origem.

**CA-013.3** — O lançamento deverá possuir as informações necessárias para identificar a movimentação efetivamente realizada.

**CA-013.4** — O lançamento deverá registrar a data de efetivação.

**CA-013.5** — O lançamento poderá possuir descrição complementar quando aplicável.

### RF-014 — Consultar movimentações financeiras

**CA-014.1** — O usuário deverá conseguir consultar os lançamentos e demais movimentações financeiras registradas.

**CA-014.2** — A consulta deverá respeitar o isolamento dos dados do usuário.

**CA-014.3** — O usuário deverá conseguir consultar o histórico correspondente ao período selecionado.

**CA-014.4** — A consulta não deverá alterar os dados financeiros.

### RF-015 — Registrar informações financeiras de períodos anteriores

**CA-015.1** — O usuário deverá conseguir cadastrar informações financeiras em períodos anteriores ao início da utilização do sistema.

**CA-015.2** — O preenchimento do histórico anterior deverá ser opcional.

**CA-015.3** — Informações cadastradas em períodos anteriores deverão participar das consultas e cálculos aplicáveis.

### RF-016 — Gerenciar transferências entre contas

**CA-016.1** — O usuário deverá conseguir informar conta de origem, conta de destino e valor.

**CA-016.2** — O sistema deverá impedir uma transferência em que origem e destino sejam a mesma conta.

**CA-016.3** — A transferência deverá ser registrada como operação entre contas pertencentes ao mesmo usuário.

**CA-016.4** — Uma transferência não efetivada não deverá alterar os saldos.

**CA-016.5** — O usuário deverá conseguir alterar uma transferência não efetivada conforme as regras aplicáveis.

**CA-016.6** — O usuário deverá conseguir excluir uma transferência não efetivada conforme as regras aplicáveis.

**CA-016.7** — Uma transferência efetivada não deverá ser alterada ou excluída diretamente.

### RF-017 — Efetivar e desfazer transferências

**CA-017.1** — O usuário deverá conseguir efetivar uma transferência válida.

**CA-017.2** — A efetivação deverá diminuir o saldo da conta de origem.

**CA-017.3** — A efetivação deverá aumentar o saldo da conta de destino.

**CA-017.4** — O registro da operação deverá estar presente no histórico das duas contas.

**CA-017.5** — O desfazimento da efetivação deverá reverter os dois efeitos financeiros.

**CA-017.6** — O desfazimento deverá manter os registros de origem e destino consistentes.

**CA-017.7** — A insuficiência de saldo não deverá impedir a efetivação da transferência.

### RF-018 — Gerenciar transferências recorrentes

**CA-018.1** — O usuário deverá conseguir configurar uma transferência recorrente.

**CA-018.2** — A transferência recorrente deverá seguir as regras mensais de recorrência.

**CA-018.3** — O usuário deverá conseguir aplicar alterações conforme as regras definidas para recorrências.

**CA-018.4** — O usuário deverá conseguir aplicar exclusões ou encerramentos conforme as regras definidas para recorrências.

### RF-019 — Gerenciar compromissos de cartão de crédito

**CA-019.1** — O usuário deverá conseguir registrar o pagamento de uma fatura como compromisso de Saída.

**CA-019.2** — O compromisso de cartão poderá utilizar recorrência.

**CA-019.3** — O usuário deverá conseguir alterar o valor da fatura conforme o valor real de cada mês.

**CA-019.4** — O compromisso de cartão deverá seguir as regras gerais de efetivação e desfazimento.

**CA-019.5** — O sistema não deverá exigir, na V1, cadastro de fechamento, vencimento ou limite de crédito do cartão.

### RF-020 — Gerenciar compromissos parcelados

**CA-020.1** — O usuário deverá conseguir representar um parcelamento por meio de compromisso recorrente.

**CA-020.2** — O usuário deverá conseguir definir a quantidade de meses do parcelamento.

**CA-020.3** — O parcelamento deverá seguir as regras gerais de recorrência.

**CA-020.4** — Alterações e efetivações das parcelas deverão respeitar as regras gerais dos compromissos financeiros.

### RF-021 — Consultar o Dashboard financeiro

**CA-021.1** — Ao acessar o Dashboard, o sistema deverá apresentar inicialmente o mês e ano correntes.

**CA-021.2** — O sistema deverá apresentar o saldo inicial do mês selecionado.

**CA-021.3** — O sistema deverá apresentar o total de entradas previstas.

**CA-021.4** — O sistema deverá apresentar o total de saídas previstas.

**CA-021.5** — O sistema deverá apresentar o saldo final previsto conforme a fórmula definida no levantamento.

**CA-021.6** — O Dashboard deverá apresentar os compromissos que compõem os valores apresentados.

**CA-021.7** — Alterações, inclusões, exclusões, efetivações e desfazimentos que afetem o período deverão ser refletidos no Dashboard.

**CA-021.8** — O Dashboard não deverá possuir dados financeiros independentes.

### RF-022 — Navegar entre períodos financeiros

**CA-022.1** — O usuário deverá conseguir selecionar outro mês e ano.

**CA-022.2** — O sistema deverá carregar as informações correspondentes ao período selecionado.

**CA-022.3** — O usuário deverá conseguir consultar períodos anteriores.

**CA-022.4** — O usuário deverá conseguir consultar períodos posteriores.

**CA-022.5** — O usuário deverá conseguir cadastrar informações em períodos anteriores, conforme as funcionalidades aplicáveis.

### RF-023 — Gerenciar proteção por PIN

**CA-023.1** — O usuário deverá conseguir ativar a proteção por PIN.

**CA-023.2** — Ao ativar o PIN, o sistema deverá informar que o e-mail cadastrado será utilizado para recuperação.

**CA-023.3** — O PIN não deverá substituir a autenticação inicial por e-mail e senha.

**CA-023.4** — O usuário deverá conseguir desbloquear a aplicação utilizando o PIN quando a proteção estiver ativa.

**CA-023.5** — O usuário deverá conseguir alterar o PIN mediante as condições definidas.

**CA-023.6** — O usuário deverá conseguir desativar o PIN mediante informação do PIN atual e confirmação.

**CA-023.7** — O sistema deverá permitir no máximo cinco tentativas consecutivas de PIN.

**CA-023.8** — Após a quinta tentativa incorreta, o acesso por PIN deverá ser bloqueado e o usuário deverá utilizar o mecanismo de recuperação por e-mail.

**CA-023.9** — O bloqueio automático por inatividade não deverá fazer parte da V1.

### RF-024 — Gerenciar tema da aplicação

**CA-024.1** — O usuário deverá conseguir selecionar o tema Claro.

**CA-024.2** — O usuário deverá conseguir selecionar o tema Escuro.

**CA-024.3** — A interface deverá aplicar o tema selecionado.

### RF-025 — Gerenciar cópias de segurança

**CA-025.1** — O usuário deverá conseguir solicitar manualmente a criação de uma cópia de segurança.

**CA-025.2** — A cópia deverá representar o estado dos dados no momento de sua criação.

**CA-025.3** — O sistema deverá disponibilizar a cópia ao usuário para preservação fora do ambiente de utilização.

**CA-025.4** — O usuário deverá conseguir fornecer uma cópia válida para restauração.

**CA-025.5** — Antes da restauração, o sistema deverá informar que os dados atuais serão substituídos.

**CA-025.6** — A restauração deverá exigir confirmação.

**CA-025.7** — A restauração deverá substituir integralmente os dados atuais pelos dados da cópia.

**CA-025.8** — O sistema não deverá realizar mesclagem de dados na V1.

## 4. Critérios de Aceitação dos Requisitos Não Funcionais

### RNF-001 — Segurança

**CA-NF-001.1** — Operações protegidas deverão exigir autenticação ou autorização compatível com a funcionalidade.

**CA-NF-001.2** — Um usuário não deverá conseguir acessar dados de outro usuário por meio das funcionalidades da aplicação.

### RNF-002 — Proteção das Credenciais

**CA-NF-002.1** — Senhas não deverão ser armazenadas em texto puro.

**CA-NF-002.2** — O sistema não deverá disponibilizar a senha anterior durante processos de recuperação.

### RNF-003 — Isolamento dos Dados dos Usuários

**CA-NF-003.1** — Consultas e operações deverão retornar ou modificar somente dados pertencentes ao usuário autenticado.

### RNF-004 — Proteção dos Dados

**CA-NF-004.1** — Dados pessoais e financeiros não deverão ser expostos por operações não autorizadas.

**CA-NF-004.2** — Mecanismos técnicos específicos de proteção deverão ser verificados na etapa de testes de segurança correspondente.

### RNF-005 — Integridade dos Dados Financeiros

**CA-NF-005.1** — Após operações financeiras válidas, não deverão existir inconsistências entre compromissos, lançamentos, contas e saldos correspondentes.

### RNF-006 — Consistência das Operações

**CA-NF-006.1** — Efetivações deverão manter consistência entre compromisso, lançamento e saldo.

**CA-NF-006.2** — Transferências deverão manter consistência entre origem, destino e respectivos registros.

### RNF-007 — Preservação do Histórico Financeiro

**CA-NF-007.1** — Alterações aplicadas a períodos futuros não deverão modificar indevidamente os registros dos períodos anteriores.

### RNF-008 — Confiabilidade dos Cálculos Financeiros

**CA-NF-008.1** — Os saldos e demais valores calculados deverão corresponder aos dados financeiros registrados e às regras de negócio aplicáveis.

### RNF-009 — Integridade dos Valores Monetários

**CA-NF-009.1** — Operações monetárias deverão manter precisão suficiente para que arredondamentos ou cálculos não produzam divergências financeiras indevidas.

### RNF-010 — Desempenho

**CA-NF-010.1** — O sistema deverá atender aos critérios quantitativos de desempenho definidos posteriormente.

O critério quantitativo permanece em aberto nesta versão da ERS.

### RNF-011 — Disponibilidade

**CA-NF-011.1** — O sistema deverá atender à meta quantitativa de disponibilidade definida posteriormente para a infraestrutura.

A meta permanece em aberto nesta versão da ERS.

### RNF-012 — Usabilidade

**CA-NF-012.1** — As funcionalidades deverão apresentar informações e comandos de forma clara e consistente.

**CA-NF-012.2** — Os fluxos principais deverão permitir que o usuário complete os objetivos definidos sem exigir conhecimento técnico do funcionamento interno.

### RNF-013 — Responsividade

**CA-NF-013.1** — A interface deverá se adaptar aos tamanhos de tela oficialmente suportados.

**CA-NF-013.2** — Os critérios técnicos detalhados de responsividade serão verificados conforme a especificação de interface.

### RNF-014 — Compatibilidade

**CA-NF-014.1** — O sistema deverá funcionar corretamente nos navegadores e ambientes oficialmente definidos como suportados para a V1.

A lista de navegadores e versões permanece em aberto.

### RNF-015 — Manutenibilidade

**CA-NF-015.1** — A implementação deverá permitir correção e evolução do sistema sem alterações desnecessariamente amplas em funcionalidades não afetadas.

Os critérios técnicos detalhados serão definidos e verificados na etapa de arquitetura e desenvolvimento.

### RNF-016 — Escalabilidade

**CA-NF-016.1** — A arquitetura deverá permitir evolução do sistema de acordo com as necessidades identificadas para a aplicação.

Os critérios quantitativos de capacidade permanecem em aberto.

### RNF-017 — Recuperação de Dados

**CA-NF-017.1** — Uma cópia válida deverá permitir a restauração dos dados que ela representa.

**CA-NF-017.2** — O estado restaurado deverá permanecer consistente após a operação.

### RNF-018 — Proteção das Cópias de Segurança

**CA-NF-018.1** — Uma cópia de segurança não deverá ser disponibilizada de maneira que permita acesso não autorizado.

Os mecanismos técnicos específicos de proteção serão definidos posteriormente.

### RNF-019 — Privacidade

**CA-NF-019.1** — O tratamento dos dados pessoais e financeiros deverá respeitar os requisitos de privacidade definidos para o sistema.

**CA-NF-019.2** — Requisitos legais e técnicos específicos deverão ser incorporados aos testes correspondentes quando definidos.

### RNF-020 — Evolutividade

**CA-NF-020.1** — Alterações futuras deverão poder ser incorporadas sem comprometer desnecessariamente funcionalidades existentes.

## 5. Critérios de Aceitação das Restrições

As restrições deverão ser verificadas tanto por inspeção documental quanto por testes quando produzirem comportamento observável.

Exemplos:

- A V1 não deverá realizar integração bancária.
- Recorrências da V1 deverão utilizar periodicidade mensal.
- A conta principal deverá permanecer única.
- O Dashboard não deverá possuir dados financeiros independentes.
- Funcionalidades fora do escopo não deverão ser disponibilizadas na V1.

## 6. Critérios em Aberto

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

Esses itens não serão considerados ausentes da especificação. Eles foram identificados e permaneceram em aberto por dependerem de decisões técnicas posteriores.

## 7. Relação com os Casos de Teste

Os critérios de aceitação serão utilizados como base para a elaboração dos casos de teste na Etapa 12.

Sempre que possível, cada caso de teste deverá referenciar:

**Requisito → Critério de Aceitação → Caso de Teste → Resultado**

Essa relação deverá compor a rastreabilidade do projeto.

## 8. Relação com as Regras de Negócio

Os critérios de aceitação deverão respeitar as regras de negócio consolidadas no Levantamento de Requisitos.

As regras de negócio não serão reproduzidas nesta seção.

Quando uma condição de aceitação depender diretamente de uma regra específica, sua validação deverá considerar a regra correspondente.

## 9. Situação da Subetapa

Os critérios de aceitação foram definidos como condições verificáveis para os requisitos consolidados na ERS.

Os critérios quantitativos que dependem de arquitetura, infraestrutura ou escolhas técnicas permanecem em aberto até que essas decisões sejam realizadas nas etapas apropriadas.

## 10. Histórico de Atualização

### Versão 1.0 — 23/08/2026

Primeira consolidação da subetapa **Critérios de Aceitação** da Etapa 3 — Especificação de Requisitos.

Principais conteúdos:

- definição dos princípios de aceitação;
- definição dos critérios de aceitação dos requisitos funcionais;
- definição dos critérios de aceitação dos requisitos não funcionais;
- definição dos critérios de validação das restrições;
- identificação dos critérios quantitativos ainda pendentes;
- definição da relação entre requisito, critério de aceitação e teste.
