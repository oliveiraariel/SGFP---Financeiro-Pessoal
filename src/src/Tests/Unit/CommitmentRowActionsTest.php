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

    public function testAgendaKeepsCommitmentAndOccurrenceActionPathsDistinct(): void
    {
        $source = file_get_contents(dirname(__DIR__, 3) . '/assets/app.js');

        self::assertIsString($source);
        self::assertStringContainsString("const commitmentActionPath = (item) => '/commitments/' + encodeURIComponent(item.id)", $source);
        self::assertStringContainsString("const recurrenceActionPath = (item, selectedMonth) => '/recurrences/' + encodeURIComponent(item.recurrence_id) + '/occurrences/' + encodeURIComponent(selectedMonth)", $source);
        self::assertStringContainsString("const actionPathFor = (item, selectedMonth) => item.recurrence_id ? recurrenceActionPath(item, selectedMonth) : commitmentActionPath(item);", $source);
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
        self::assertStringContainsString("list(commitments).filter((x) => commitmentMonth(x) === selectedMonth)", $source);
        self::assertStringContainsString("encodeURIComponent(mo))));", $source);
    }

    public function testAgendaSortsChronologicallyWithDeterministicFallbacks(): void
    {
        $source = file_get_contents(dirname(__DIR__, 3) . '/assets/app.js');

        self::assertIsString($source);
        self::assertStringContainsString("const agendaDateKey = (item) => {", $source);
        self::assertStringContainsString("return /^\\d{4}-\\d{2}-\\d{2}$/.test(value) ? value : '9999-99-99';", $source);
        self::assertStringContainsString("const compareAgendaItems = (a, b) => agendaDateKey(a).localeCompare(agendaDateKey(b))", $source);
        self::assertStringContainsString("|| String(a && a.id || '').localeCompare(String(b && b.id || ''));", $source);
        self::assertStringContainsString("uniqueCommitments(items).sort(compareAgendaItems)", $source);
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
        self::assertStringContainsString("(!effective ? act('Excluir', '/commitments/' + encodeURIComponent(item.id), 'DELETE') : '<span class=\"sgfp-muted\">Desfaça para editar</span>')", $source);
        self::assertStringContainsString("['Vencimento', 'Descrição', 'Categoria', 'Valor', 'Status', 'Ações']", $source);
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
        self::assertStringContainsString("const initialBalancePromptKey = (id) => 'sgfp-initial-balance-prompt:' + String(id);", $source);
        self::assertStringContainsString("account.initial_balance_configured === true", $source);
        self::assertStringContainsString("window.localStorage.getItem(promptKey) === 'shown'", $source);
        self::assertStringContainsString("window.localStorage.setItem(promptKey, 'shown');", $source);
        self::assertStringContainsString('data-initial-balance-prompt', $source);
        self::assertStringContainsString('Definir saldo inicial', $source);
        self::assertStringContainsString("const initialMonth = setup ? disabled : 'value=\"' + esc(currentMonth()) + '\" required readonly';", $source);
        self::assertStringContainsString("f.querySelector('[name=\"effective_month\"]')?.required && d.effective_month !== currentMonth()", $source);
        self::assertStringContainsString('bootstrapTheme().finally(async () => { await load(); await promptInitialBalanceForNewProfile(); });', $source);
    }

    public function testMovementsUnwrapTheCollectionEnvelopeBeforeMappingRows(): void
    {
        $source = file_get_contents(dirname(__DIR__, 3) . '/assets/app.js');

        self::assertIsString($source);
        self::assertStringContainsString('const rows = list(d).map((x) => {', $source);
        self::assertStringNotContainsString('const rows = d.map((x) => {', $source);
    }

    public function testOverviewRendersDashboardBalancesTogetherWithTheAgenda(): void
    {
        $source = file_get_contents(dirname(__DIR__, 3) . '/assets/app.js');

        self::assertIsString($source);
        self::assertStringContainsString('content.innerHTML = dashboard(d) + agenda(items, categoryMap, selectedMonth);', $source);
        self::assertStringContainsString("dashboardMetric('Mês anterior', d.opening_balance", $source);
        self::assertStringContainsString("dashboardMetric('Saldo previsto', d.expected_closing_balance", $source);
        self::assertStringContainsString("dashboardMetric('Saldo final', d.current_balance", $source);
        self::assertLessThan(
            strpos($source, '<section class="sgfp-dashboard-section" aria-labelledby="sgfp-realized-title">'),
            strpos($source, '<section class="sgfp-dashboard-section" aria-labelledby="sgfp-expected-title">')
        );
    }

    public function testRecurringOccurrenceErrorsAreNotSilentlyDiscarded(): void
    {
        $source = file_get_contents(dirname(__DIR__, 3) . '/assets/app.js');

        self::assertIsString($source);
        self::assertStringNotContainsString("catch (_) { return null; }", $source);
        self::assertStringContainsString("const occurrences = await Promise.all(recurring.map((x) => api('/recurrences/'", $source);
    }
}
