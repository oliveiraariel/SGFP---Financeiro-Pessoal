<?php

declare(strict_types=1);

namespace SGFP\Tests\Unit;

use PHPUnit\Framework\TestCase;

final class CommitmentRowActionsTest extends TestCase
{
    public function testAgendaRendersPerRowEffectuationAndUndoControls(): void
    {
        $source = file_get_contents(dirname(__DIR__, 3) . '/assets/app.js');

        self::assertIsString($source);
        self::assertStringContainsString("act(effective ? 'Desefetivar' : 'Efetivar', item.actionPath)", $source);
        self::assertStringContainsString("'/undo-effectuation' : '/effectuation'", $source);
        self::assertStringContainsString("data-action=\"' + esc(p) + '\"", $source);
    }

    public function testEffectuationIsAuthoritativeAndRefreshesDependentSummaries(): void
    {
        $source = file_get_contents(dirname(__DIR__, 3) . '/assets/app.js');

        self::assertIsString($source);
        self::assertStringContainsString("if (action.disabled) return;", $source);
        self::assertStringContainsString('const settled = result && result.status ? result.status === \'EFETIVADO\' : !undoing;', $source);
        self::assertStringContainsString('updateCommitmentRow(action, result); await refreshOverviewSummaries();', $source);
        self::assertStringContainsString('const refreshOverviewSummaries = async () => {', $source);
        self::assertStringContainsString('showActionError(action, e);', $source);
        self::assertStringContainsString("else await load('overview');", $source);
    }

    public function testEntriesRenderEffectiveDateSelectedPeriodAndFinancialOrdering(): void
    {
        $source = file_get_contents(dirname(__DIR__, 3) . '/assets/app.js');

        self::assertIsString($source);
        self::assertStringContainsString("api('/movements?month=' + encodeURIComponent(mo))", $source);
        self::assertStringContainsString("const movements = list(d).slice().sort((a, b) => String(a.effective_at || '').localeCompare(String(b.effective_at || '')) || String(a.id || '').localeCompare(String(b.id || '')));", $source);
        self::assertStringContainsString("dateLabel(String(x.effective_at || '').slice(0, 10))", $source);
        self::assertStringContainsString("'Data de efetivação', 'Nome', 'Descrição', 'Categoria', 'Valor', 'Tipo', 'Status'", $source);
        self::assertStringContainsString("Nenhum lançamento efetivado em ' + esc(monthName)", $source);
        self::assertStringContainsString("Exibindo somente lançamentos efetivados deste mês.", $source);
    }

    public function testAgendaKeepsCommitmentAndOccurrenceActionPathsDistinct(): void
    {
        $source = file_get_contents(dirname(__DIR__, 3) . '/assets/app.js');

        self::assertIsString($source);
        self::assertStringContainsString("const commitmentActionPath = (item) => '/commitments/' + encodeURIComponent(item.id)", $source);
        self::assertStringContainsString("const recurrenceActionPath = (item, selectedMonth) => '/recurrences/' + encodeURIComponent(item.recurrence_id) + '/occurrences/' + encodeURIComponent(selectedMonth)", $source);
        self::assertStringContainsString("const actionPathFor = (item, selectedMonth) => item.recurrence_id ? recurrenceActionPath(item, selectedMonth) : commitmentActionPath(item);", $source);
        self::assertStringContainsString("const endRecurrencePath = (item, selectedMonth) => '/recurrences/' + encodeURIComponent(item.recurrence_id) + '/occurrences/' + encodeURIComponent(selectedMonth) + '/end';", $source);
        self::assertStringContainsString("'Excluir somente este mês'", $source);
        self::assertStringContainsString("'Excluir este mês e os seguintes'", $source);
    }

    public function testActionErrorsAreMappedToSafeUserMessagesAndKeepTechnicalCodesLocal(): void
    {
        $source = file_get_contents(dirname(__DIR__, 3) . '/assets/app.js');

        self::assertIsString($source);
        self::assertStringContainsString("UNAUTHORIZED: 'Sua sessão expirou.", $source);
        self::assertStringContainsString("FORBIDDEN: 'Você não tem permissão", $source);
        self::assertStringContainsString("VALIDATION_ERROR: 'Confira os dados informados", $source);
        self::assertStringContainsString("CONFLICT: 'A operação entrou em conflito", $source);
        self::assertStringContainsString("error.correlationId", $source);
    }

    public function testLoadFailureRemainsRecoverableAndIsMarkedAsAnAlert(): void
    {
        $source = file_get_contents(dirname(__DIR__, 3) . '/assets/app.js');

        self::assertIsString($source);
        self::assertStringContainsString('role="alert"', $source);
        self::assertStringContainsString('return false;', $source);
        self::assertStringContainsString('data-retry="\' + view + \'"', $source);
    }

    public function testMaterializedRecurringRowsKeepOccurrenceRoute(): void
    {
        $source = file_get_contents(dirname(__DIR__, 3) . '/assets/app.js');

        self::assertIsString($source);
        self::assertStringContainsString("actionPath: actionPathFor(x, mo)", $source);
        self::assertStringNotContainsString("!direct.some((dItem) => String(dItem.id) === String(x.id))", $source);
    }

    public function testAgendaSelectsTheMonthFromTheCanonicalDailyCommitmentDate(): void
    {
        $source = file_get_contents(dirname(__DIR__, 3) . '/assets/app.js');

        self::assertIsString($source);
        self::assertStringContainsString("const commitmentMonth = (item) => {", $source);
        self::assertStringContainsString("String(item && item.commitment_date || '').match(/^(\\d{4}-\\d{2})-\\d{2}$/)", $source);
        self::assertStringContainsString("const direct = operational.filter((x) => commitmentMonth(x) === selectedMonth);", $source);
        self::assertStringContainsString("const recurrenceActionPath = (item, selectedMonth) => '/recurrences/' + encodeURIComponent(item.recurrence_id) + '/occurrences/' + encodeURIComponent(selectedMonth) + (item.status === 'EFETIVADO' ? '/undo-effectuation' : '/effectuation');", $source);
    }

    public function testAgendaSortsChronologicallyWithDeterministicFallbacks(): void
    {
        $source = file_get_contents(dirname(__DIR__, 3) . '/assets/app.js');

        self::assertIsString($source);
        self::assertStringContainsString("const agendaDateKey = (item) => {", $source);
        self::assertStringContainsString("return /^\\d{4}-\\d{2}-\\d{2}$/.test(value) ? value : '9999-99-99';", $source);
        self::assertStringContainsString("const compareAgendaItems = (a, b) => agendaDateKey(a).localeCompare(agendaDateKey(b))", $source);
        self::assertStringContainsString("|| String(a && a.id || '').localeCompare(String(b && b.id || ''));", $source);
        self::assertStringContainsString("uniqueCommitments(items).filter(isOperationalCommitment).sort(compareAgendaItems)", $source);
    }

    public function testAgendaRendersTheCanonicalDueDateColumnWithAccessibleTableHeaders(): void
    {
        $source = file_get_contents(dirname(__DIR__, 3) . '/assets/app.js');

        self::assertIsString($source);
        self::assertStringContainsString("const date = item.commitment_date;", $source);
        self::assertStringContainsString("dateLabel(date)", $source);
        self::assertStringContainsString("['Vencimento', 'Descrição', 'Categoria', 'Valor', 'Status', 'Ações']", $source);
        self::assertStringContainsString("'<th scope=\"col\">'", $source);
    }

    public function testAgendaTableHasAResponsiveOverflowBoundary(): void
    {
        $source = file_get_contents(dirname(__DIR__, 3) . '/assets/app.css');

        self::assertIsString($source);
        self::assertStringContainsString('.sgfp-table-wrap{overflow-x:auto;', $source);
        self::assertStringContainsString('.sgfp-panel table{width:100%;', $source);
    }

    public function testDashboardMetricCardsUseCompactVerticalSpacing(): void
    {
        $source = file_get_contents(dirname(__DIR__, 3) . '/assets/app.css');

        self::assertIsString($source);
        self::assertStringContainsString('.sgfp-dashboard-section .sgfp-metric{padding:9px 16px;gap:5px}', $source);
    }

    public function testDashboardUsesAvailableWidthAndKeepsTheChartResponsiveAcrossTheFullRange(): void
    {
        $source = file_get_contents(dirname(__DIR__, 3) . '/assets/app.css');

        self::assertIsString($source);
        self::assertStringContainsString('.sgfp-content{width:100%;max-width:none}', $source);
        self::assertStringContainsString('.sgfp-dashboard{grid-template-columns:minmax(0,1.12fr) minmax(0,1fr)', $source);
        self::assertStringContainsString('.sgfp-chart-wrap{width:100%;overflow:visible}', $source);
        self::assertStringContainsString('.sgfp-chart{display:block;width:100%;height:auto;', $source);
        self::assertStringNotContainsString('.sgfp-chart{display:block;width:100%;min-width:520px;', $source);
        self::assertStringContainsString('.sgfp-chart-wrap{flex:1 1 auto;min-height:260px;', $source);
        self::assertMatchesRegularExpression('/\.sgfp-chart\{width:max\(100%,\d+px\);height:auto;max-width:none;aspect-ratio:4 \/ 1\}/', $source);
        self::assertMatchesRegularExpression('/\.sgfp-chart-expanded \.sgfp-chart\{width:max\(100%,\d+px\);height:auto;aspect-ratio:4 \/ 1\}/', $source);
        self::assertStringContainsString('@media(max-width:480px){.sgfp-chart-label:nth-of-type(even){display:none}}', $source);
    }

    public function testEvolutionChartCentersTheZeroReferenceForAnAllZeroSeries(): void
    {
        $source = file_get_contents(dirname(__DIR__, 3) . '/assets/app.js');

        self::assertIsString($source);
        self::assertStringContainsString('const observedMin = Math.min(...finite), observedMax = Math.max(...finite);', $source);
        self::assertStringContainsString('const margin = observedSpan > 0', $source);
        self::assertStringContainsString('const min = scale.min, max = scale.max;', $source);
        self::assertStringContainsString('class="sgfp-chart-label"', $source);
    }

    public function testMonthlyChartRendersOnlyTheSelectedMonthAndKeepsYearNavigation(): void
    {
        $javascript = file_get_contents(dirname(__DIR__, 3) . '/assets/app.js');
        $stylesheet = file_get_contents(dirname(__DIR__, 3) . '/assets/app.css');

        self::assertIsString($javascript);
        self::assertIsString($stylesheet);
        self::assertStringContainsString('EVOLUÇÃO MENSAL', $javascript);
        self::assertStringContainsString("const chartMonth = String(year) + '-' + selectedMonth.slice(5, 7);", $javascript);
        self::assertStringContainsString("history.items = list(history).filter((item) => String(item.date || '').slice(0, 7) === chartMonth);", $javascript);
        self::assertStringContainsString('data-chart-year-shift', $javascript);
        self::assertStringNotContainsString('arraste para ver janeiro a dezembro', $javascript);
    }

    public function testMonthlyChartFitsItsPanelWithoutScrollAndKeepsSelectableTooltipPoints(): void
    {
        $javascript = file_get_contents(dirname(__DIR__, 3) . '/assets/app.js');
        $stylesheet = file_get_contents(dirname(__DIR__, 3) . '/assets/app.css');

        self::assertIsString($javascript);
        self::assertIsString($stylesheet);
        self::assertStringContainsString('.sgfp-chart-wrap{overflow:hidden;overflow-x:hidden;overflow-y:hidden;touch-action:auto}', $stylesheet);
        self::assertStringContainsString('.sgfp-chart{width:100%;max-width:100%;aspect-ratio:2 / 1}', $stylesheet);
        self::assertStringContainsString('data-chart-point', $javascript);
        self::assertStringContainsString('<title>', $javascript);
        self::assertStringContainsString('data-daily-details', $javascript);
        self::assertStringNotContainsString('sgfp-chart-scroll-hint', $javascript);
        self::assertStringNotContainsString('Selecione um ponto para ver os movimentos do dia', $javascript);
        self::assertStringNotContainsString('wrap.scrollLeft', $javascript);
    }

    public function testMonthlyChartRemovesOnlyTheVerticalAxisTitle(): void
    {
        $javascript = file_get_contents(dirname(__DIR__, 3) . '/assets/app.js');

        self::assertIsString($javascript);
        self::assertStringNotContainsString('Saldo (R$)</text>', $javascript);
        self::assertStringNotContainsString('sgfp-chart-axis-title', $javascript);
        self::assertStringContainsString('class="sgfp-chart-axis-label"', $javascript);
        self::assertStringContainsString('class="sgfp-chart-grid"', $javascript);
        self::assertStringContainsString('data-chart-point', $javascript);
        self::assertStringNotContainsString('Marcações do eixo', $javascript);
        self::assertStringNotContainsString('data-chart-tick-frequency', $javascript);
    }

    public function testMonthlyChartKeepsDailyPointsInteractiveAndTooltipBacked(): void
    {
        $javascript = file_get_contents(dirname(__DIR__, 3) . '/assets/app.js');

        self::assertIsString($javascript);
        self::assertSame(1, substr_count($javascript, 'data-chart-point data-date='));
        self::assertSame(1, substr_count($javascript, '<title>'));
        self::assertStringContainsString('tabindex="0" role="button" data-chart-point', $javascript);
        self::assertStringContainsString('Enter para abrir detalhes.', $javascript);
    }

    public function testAgendaDisablesEditingForSettledCommitments(): void
    {
        $source = file_get_contents(dirname(__DIR__, 3) . '/assets/app.js');

        self::assertIsString($source);
        self::assertStringContainsString("const effective = item.status === 'EFETIVADO';", $source);
        self::assertStringContainsString("edit('Editar', item.id, 'commitment', effective)", $source);
        self::assertStringContainsString("disabled aria-disabled=\"true\" title=\"Desfaça a efetivação para editar\"", $source);
    }

    public function testAgendaOffersDeletionOnlyForPendingCommitments(): void
    {
        $source = file_get_contents(dirname(__DIR__, 3) . '/assets/app.js');

        self::assertIsString($source);
        self::assertStringContainsString("(!effective ? deletion : '<span class=\"sgfp-muted\">Desfaça para editar</span>')", $source);
        self::assertStringContainsString("['Vencimento', 'Descrição', 'Categoria', 'Valor', 'Status', 'Ações']", $source);
    }

    public function testExcludedCommitmentsAreDefensivelyHiddenFromOperationalViews(): void
    {
        $source = file_get_contents(dirname(__DIR__, 3) . '/assets/app.js');

        self::assertIsString($source);
        self::assertStringContainsString("const isOperationalCommitment = (item) => item && item.status !== 'EXCLUIDO';", $source);
        self::assertStringContainsString('uniqueCommitments(items).filter(isOperationalCommitment)', $source);
        self::assertStringContainsString('const operational = list(commitments).filter(isOperationalCommitment);', $source);
        self::assertStringContainsString('x.recurrence_id && isOperationalCommitment(x)', $source);
    }

    public function testProfileResetObtainsAndSubmitsTheConfirmationToken(): void
    {
        $source = file_get_contents(dirname(__DIR__, 3) . '/assets/app.js');

        self::assertIsString($source);
        self::assertStringContainsString("api('/profile-reset/validation', { method: 'POST' })", $source);
        self::assertStringContainsString("JSON.stringify({ token: d.token, phrase: phrase })", $source);
    }

    public function testBackupDownloadUsesTheBinaryResponseAsADomAttachedTemporaryLink(): void
    {
        $source = file_get_contents(dirname(__DIR__, 3) . '/assets/app.js');

        self::assertIsString($source);
        self::assertStringContainsString("fetch(base + '/backups', { method: 'POST'", $source);
        self::assertStringContainsString("const error = await r.json().catch(() => ({})); throw Error(apiError(error));", $source);
        self::assertStringContainsString("r.headers.get('Content-Disposition')", $source);
        self::assertStringContainsString('const objectUrl = URL.createObjectURL(await r.blob());', $source);
        self::assertStringContainsString('document.body.appendChild(a); a.click(); a.remove();', $source);
        self::assertStringContainsString('window.setTimeout(() => URL.revokeObjectURL(objectUrl), 0);', $source);
    }

    public function testCommitmentFormUsesStrictDailyDateConversionAndHydratesTheReturnedDate(): void
    {
        $source = file_get_contents(dirname(__DIR__, 3) . '/assets/app.js');

        self::assertIsString($source);
        self::assertStringContainsString("/^(\\d{2})\\/(\\d{2})\\/(\\d{4})$/", $source);
        self::assertStringContainsString("new Date(Date.UTC(year, monthNumber - 1, day))", $source);
        self::assertStringContainsString("commitment_date: apiToDate(item.commitment_date)", $source);
        self::assertStringContainsString("JSON.stringify({ name: d.name, amount, category_id: d.category_id ? Number(d.category_id) : null, commitment_date: commitmentDate })", $source);
    }

    public function testCommitmentEditReportsMissingRecordInsteadOfHydratingAnEmptyForm(): void
    {
        $source = file_get_contents(dirname(__DIR__, 3) . '/assets/app.js');

        self::assertIsString($source);
        self::assertStringContainsString("if (!item) {", $source);
        self::assertStringContainsString("Registro não encontrado.", $source);
        self::assertStringContainsString("hydrateCommitmentForm(content.querySelector('[data-form=\"commitment\"]'), item);", $source);
    }

    public function testCommitmentEditFetchesTheCanonicalResourceBeforeHydratingTheDate(): void
    {
        $source = file_get_contents(dirname(__DIR__, 3) . '/assets/app.js');

        self::assertIsString($source);
        self::assertStringContainsString("api('/commitments/' + encodeURIComponent(editButton.dataset.edit))", $source);
        self::assertStringNotContainsString("api('/commitments?month=' + encodeURIComponent(monthParam()))).find", $source);
        self::assertStringContainsString("const values = { name: item.name || '', amount: item.amount || '', nature: item.nature || 'SAIDA', category_id: item.category_id == null ? '' : String(item.category_id), commitment_date: apiToDate(item.commitment_date) }", $source);
    }

    public function testCommitmentCreationDefaultsToTodayButSerializesTheCanonicalDateForRoundTrip(): void
    {
        $source = file_get_contents(dirname(__DIR__, 3) . '/assets/app.js');

        self::assertIsString($source);
        self::assertStringContainsString("value=\"' + esc(currentDate()) + '\"", $source);
        self::assertStringContainsString("commitmentDate = dateToApi(d.commitment_date)", $source);
        self::assertStringContainsString("commitment_date: commitmentDate, recurrence_months_count", $source);
    }

    public function testCommitmentDateInputMasksDigitsWithoutChangingCanonicalConversion(): void
    {
        $source = file_get_contents(dirname(__DIR__, 3) . '/assets/app.js');

        self::assertIsString($source);
        self::assertStringContainsString("const maskDateInput = (input) =>", $source);
        self::assertStringContainsString("input.value = digits.length > 4 ? digits.slice(0, 2) + '/' + digits.slice(2, 4) + '/' + digits.slice(4)", $source);
        self::assertStringContainsString("if (ev.target.matches('[name=\"commitment_date\"]')) maskDateInput(ev.target)", $source);
    }

    public function testFormInputsKeepReadableTextColorsInBothThemes(): void
    {
        $source = file_get_contents(dirname(__DIR__, 3) . '/assets/app.css');

        self::assertIsString($source);
        self::assertStringContainsString('.sgfp-field input,.sgfp-field select{', $source);
        self::assertStringContainsString('background:#071012;color:#e9eeee;', $source);
        self::assertStringContainsString('[data-sgfp-app][data-theme="light"] .sgfp-field input,[data-sgfp-app][data-theme="light"] .sgfp-field select{color:#000;caret-color:#000;-webkit-text-fill-color:#000}', $source);
        self::assertStringContainsString('[data-sgfp-app][data-theme="light"] .sgfp-setting select{background:#fff;color:#000;caret-color:#000;-webkit-text-fill-color:#000;', $source);
        self::assertStringContainsString('[data-sgfp-app][data-theme="dark"] .sgfp-field input,[data-sgfp-app][data-theme="dark"] .sgfp-field select,[data-sgfp-app][data-theme="dark"] .sgfp-setting select{background:#fff;color:#000;caret-color:#000;-webkit-text-fill-color:#000;', $source);
        self::assertStringContainsString('[data-sgfp-app][data-theme="dark"] .sgfp-field select option,[data-sgfp-app][data-theme="dark"] .sgfp-setting select option{background:#fff;color:#000}', $source);
        self::assertStringContainsString('.sgfp-app[data-theme="dark"] .sgfp-money-input{background:#fff;color:#000;caret-color:#000;-webkit-text-fill-color:#000}', $source);
        self::assertStringContainsString('.sgfp-field input::placeholder{color:#91a1a1;', $source);
    }

    public function testGlobalMonthSelectorUsesBlackCharactersOnItsWhiteDarkThemeField(): void
    {
        $source = file_get_contents(dirname(__DIR__, 3) . '/assets/app.css');

        self::assertIsString($source);
        self::assertStringContainsString('[data-sgfp-app][data-theme="dark"] .sgfp-month{background:#fff;color:#000;caret-color:#000;-webkit-text-fill-color:#000;border-color:#9eafad;color-scheme:light}', $source);
    }

    public function testThemeIsLoadedBeforeTheFirstRenderAndPersistenceErrorsAreVisible(): void
    {
        $source = file_get_contents(dirname(__DIR__, 3) . '/assets/app.js');

        self::assertIsString($source);
        self::assertStringContainsString("const bootstrapTheme = async () =>", $source);
        self::assertStringContainsString("api('/preferences/theme')", $source);
        self::assertStringContainsString('bootstrapTheme().finally(async () => { await load(); await promptInitialBalanceForNewProfile(); });', $source);
        self::assertStringContainsString("Não foi possível salvar o tema:", $source);
    }

    public function testThemeClientDefaultsToDarkWhenNoPreferenceIsAvailable(): void
    {
        $source = file_get_contents(dirname(__DIR__, 3) . '/assets/app.js');

        self::assertIsString($source);
        self::assertStringContainsString("const normalized = theme === 'light' ? 'light' : 'dark';", $source);
        self::assertStringContainsString("const theme = app.dataset.theme || 'dark';", $source);
        self::assertStringContainsString("themeFromResponse(result) || local || 'dark'", $source);
        self::assertStringContainsString("applyTheme(local || 'dark');", $source);
    }

    public function testNewProfileGetsOneInitialBalancePromptAndTheCurrentMonthInMinhaConta(): void
    {
        $source = file_get_contents(dirname(__DIR__, 3) . '/assets/app.js');

        self::assertIsString($source);
        self::assertStringContainsString("account.initial_balance_configured === true", $source);
        self::assertStringNotContainsString("window.localStorage.getItem(promptKey) === 'shown'", $source);
        self::assertStringNotContainsString("window.localStorage.setItem(promptKey, 'shown');", $source);
        self::assertStringContainsString('data-initial-balance-modal', $source);
        self::assertStringContainsString('Defina seu saldo inicial', $source);
        self::assertStringContainsString("field('Data do saldo inicial', 'effective_month', 'date', initialDate)", $source);
        self::assertStringContainsString("!/^\\d{4}-\\d{2}-\\d{2}$/.test(d.effective_month)", $source);
        self::assertStringContainsString('bootstrapTheme().finally(async () => { await load(); await promptInitialBalanceForNewProfile(); });', $source);
    }

    public function testMovementsUnwrapTheCollectionEnvelopeBeforeMappingRows(): void
    {
        $source = file_get_contents(dirname(__DIR__, 3) . '/assets/app.js');

        self::assertIsString($source);
        self::assertStringContainsString('const movements = list(d).slice().sort(', $source);
        self::assertStringContainsString('const rows = movements.map((x) => {', $source);
        self::assertStringNotContainsString('const rows = d.map((x) => {', $source);
    }

    public function testOverviewRendersDashboardBalancesTogetherWithTheAgenda(): void
    {
        $source = file_get_contents(dirname(__DIR__, 3) . '/assets/app.js');

        self::assertIsString($source);
        self::assertStringContainsString('content.innerHTML = dashboard(d, history.items, selectedMonth, year, chartError) + agenda(items, categoryMap, selectedMonth);', $source);
        self::assertStringContainsString("api('/dashboard/evolution/annual?year=' + year)", $source);
        self::assertStringNotContainsString('chartMonths', $source);
        self::assertStringContainsString("d.is_before_financial_start === true ? [] : uniqueRecurrences(operational.filter", $source);
        self::assertStringContainsString('Período anterior ao início do controle', $source);
        self::assertStringContainsString('Evolução do saldo', $source);
        self::assertStringContainsString("dashboardMetric('Mês anterior', d.opening_balance", $source);
        self::assertStringContainsString("dashboardMetric('Saldo previsto', d.expected_closing_balance", $source);
        self::assertStringContainsString("dashboardMetric('Saldo final', d.current_balance", $source);
        self::assertLessThan(
            strpos($source, '<section class="sgfp-dashboard-section" aria-labelledby="sgfp-realized-title">'),
            strpos($source, '<section class="sgfp-dashboard-section" aria-labelledby="sgfp-expected-title">')
        );
    }

    public function testDashboardKeepsEmptyStatesAsStableHiddenOrLocalSlots(): void
    {
        $source = file_get_contents(dirname(__DIR__, 3) . '/assets/app.js');
        $styles = file_get_contents(dirname(__DIR__, 3) . '/assets/app.css');

        self::assertIsString($source);
        self::assertIsString($styles);
        self::assertStringContainsString('sgfp-dashboard-slot sgfp-dashboard-before-start', $source);
        self::assertStringNotContainsString('sgfp-dashboard-slot sgfp-dashboard-no-activity', $source);
        self::assertStringNotContainsString('Criar compromisso', $source);
        self::assertStringContainsString('aria-hidden="true" hidden', $source);
        self::assertStringContainsString('.sgfp-dashboard .sgfp-dashboard-slot{grid-column:1/-1;grid-row:4}', $styles);
        self::assertStringContainsString('.sgfp-dashboard .sgfp-dashboard-slot[hidden]{display:none}', $styles);
    }

    public function testDashboardPinsDesktopRegionsAcrossEmptyAndPopulatedStates(): void
    {
        $styles = file_get_contents(dirname(__DIR__, 3) . '/assets/app.css');

        self::assertIsString($styles);
        self::assertStringContainsString('.sgfp-dashboard .sgfp-balance-hero{grid-row:1}', $styles);
        self::assertStringContainsString('.sgfp-dashboard-section[aria-labelledby="sgfp-expected-title"]{grid-row:2}', $styles);
        self::assertStringContainsString('.sgfp-dashboard-section[aria-labelledby="sgfp-realized-title"]{grid-row:3}', $styles);
        self::assertStringContainsString('.sgfp-dashboard .sgfp-dashboard-actions{grid-column:1/-1;grid-row:5}', $styles);
    }

    public function testOnlyTheMostRecentMonthLoadCanReplaceItsContentOrBusyState(): void
    {
        $source = file_get_contents(dirname(__DIR__, 3) . '/assets/app.js');

        self::assertIsString($source);
        self::assertStringContainsString("if (sequence !== loadSequence) return;", $source);
        self::assertStringContainsString("catch (e) { if (sequence === loadSequence) content.innerHTML = panel('Não foi possível carregar'", $source);
        self::assertStringContainsString("finally { if (sequence === loadSequence) content.setAttribute('aria-busy', 'false'); }", $source);
    }

    public function testRecurringOccurrenceErrorsAreNotSilentlyDiscarded(): void
    {
        $source = file_get_contents(dirname(__DIR__, 3) . '/assets/app.js');

        self::assertIsString($source);
        self::assertStringNotContainsString("catch (_) { return null; }", $source);
        self::assertStringContainsString("const occurrences = await Promise.all(recurring.map((x) => api('/recurrences/'", $source);
        self::assertStringContainsString("d.is_before_financial_start === true ? [] : uniqueRecurrences(operational.filter", $source);
    }

    public function testOverviewMaterializesEachRecurrenceOnlyOncePerSelectedMonth(): void
    {
        $source = file_get_contents(dirname(__DIR__, 3) . '/assets/app.js');

        self::assertIsString($source);
        self::assertStringContainsString("const uniqueRecurrences = (items) => Array.from(new Map(items.filter((item) => item && item.recurrence_id).map((item) => [String(item.recurrence_id), item])).values());", $source);
        self::assertStringContainsString("uniqueRecurrences(operational.filter((x) => x.recurrence_id && commitmentMonth(x) !== selectedMonth))", $source);
        self::assertStringContainsString("const occurrences = await Promise.all(recurring.map((x) => api('/recurrences/'", $source);
    }
}
