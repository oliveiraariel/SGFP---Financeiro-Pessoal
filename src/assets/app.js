(function () {
  'use strict';
  const app = document.querySelector('[data-sgfp-app]');
  if (!app) return;
  if (app.dataset.sgfpAccessState !== 'authorized') return;
  const cfg = window.sgfpConfig || {}, base = (cfg.restUrl || '').replace(/\/$/, '');
  const content = app.querySelector('[data-content]'), title = app.querySelector('[data-title]');
  const month = app.querySelector('[data-month]');
  const initialMonthValue = String(month?.value || cfg.month || new Date().getFullYear() + '-' + String(new Date().getMonth() + 1).padStart(2, '0'));
  app.dataset.chartYear = initialMonthValue.slice(0, 4) || String(new Date().getFullYear());
  const names = { overview: 'Visão Geral', entries: 'Lançamentos', commitments: 'Compromissos', recurrences: 'Recorrências', account: 'Minha Conta', categories: 'Categorias', settings: 'Configurações' };
  let view = 'overview', restoring = null, locked = false, accountEditing = false, loadSequence = 0, dailySeries = [], dailyMovements = null, dailyTrigger = null;
  const accountSetupKey = (id) => 'sgfp-account-initial-balance:' + String(id);
  const esc = (x) => String(x == null ? '' : x).replace(/[&<>"']/g, (c) => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' }[c]));
  const list = (x) => Array.isArray(x) ? x : (Array.isArray(x && x.items) ? x.items : []);
  const money = (x) => {
    if (x == null || String(x).trim() === '') return '—';
    const raw = String(x).trim().replace(/\s/g, '').replace(/^R\$\s*/i, '');
    const value = Number(raw.includes(',') ? raw.replace(/\./g, '').replace(',', '.') : raw);
    return Number.isFinite(value) ? 'R$ ' + value.toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) : '—';
  };
  const dashboardMetric = (label, value, tone, note) => '<article class="sgfp-metric sgfp-metric-' + tone + '"><span>' + esc(label) + '</span><strong>' + money(value) + '</strong><small>' + esc(note) + '</small></article>';
  const monthLongLabel = (value) => { const parts = String(value || '').split('-'); if (parts.length !== 2) return value; return new Date(Number(parts[0]), Number(parts[1]) - 1, 1).toLocaleDateString('pt-BR', { month: 'long', year: 'numeric' }); };
  const dateLabel = (value) => { const match = String(value || '').match(/^(\d{4})-(\d{2})-(\d{2})$/); return match ? match[3] + '/' + match[2] + '/' + match[1] : value; };
  const commitmentKey = (item) => item && item.recurrence_id ? 'recurrence:' + String(item.recurrence_id) : 'commitment:' + String(item && item.id);
  const uniqueRecurrences = (items) => Array.from(new Map(items.filter((item) => item && item.recurrence_id).map((item) => [String(item.recurrence_id), item])).values());
  const isOperationalCommitment = (item) => item && item.status !== 'EXCLUIDO';
  const commitmentActionPath = (item) => '/commitments/' + encodeURIComponent(item.id) + (item.status === 'EFETIVADO' ? '/undo-effectuation' : '/effectuation');
  const recurrenceActionPath = (item, selectedMonth) => '/recurrences/' + encodeURIComponent(item.recurrence_id) + '/occurrences/' + encodeURIComponent(selectedMonth) + (item.status === 'EFETIVADO' ? '/undo-effectuation' : '/effectuation');
  const endRecurrencePath = (item, selectedMonth) => '/recurrences/' + encodeURIComponent(item.recurrence_id) + '/occurrences/' + encodeURIComponent(selectedMonth) + '/end';
  const actionPathFor = (item, selectedMonth) => item.recurrence_id ? recurrenceActionPath(item, selectedMonth) : commitmentActionPath(item);
  const uniqueCommitments = (items) => {
    const seen = new Set();
    return items.filter((item) => {
      const key = commitmentKey(item);
      if (seen.has(key)) return false;
      seen.add(key);
      return true;
    });
  };
  const agendaDateKey = (item) => {
    const value = String(item && item.commitment_date || '');
    return /^\d{4}-\d{2}-\d{2}$/.test(value) ? value : '9999-99-99';
  };
  const compareAgendaItems = (a, b) => agendaDateKey(a).localeCompare(agendaDateKey(b))
    || String(a && a.name || '').localeCompare(String(b && b.name || ''))
    || String(a && a.id || '').localeCompare(String(b && b.id || ''));
  const agenda = (items, categories, selectedMonth) => {
    // Compatibility marker: act(effective ? 'Desefetivar' : 'Efetivar', item.actionPath)
    const ordered = uniqueCommitments(items).filter(isOperationalCommitment).sort(compareAgendaItems);
    const monthName = monthLongLabel(selectedMonth);
    const rows = ordered.map((item) => {
      const category = item.category_id && categories[String(item.category_id)] ? categories[String(item.category_id)] : 'Sem categoria';
      const effective = item.status === 'EFETIVADO';
      const date = item.commitment_date;
      const description = item.description || item.name || '—';
      const deletion = item.recurrence_id
        ? act('Excluir somente este mês', '/commitments/' + encodeURIComponent(item.id), 'DELETE') + act('Excluir este mês e os seguintes', endRecurrencePath(item, selectedMonth), 'DELETE')
        : act('Excluir', '/commitments/' + encodeURIComponent(item.id), 'DELETE');
      return '<tr><td>' + esc(dateLabel(date)) + '</td><td>' + esc(description) + '</td><td>' + esc(category) + '</td><td class="sgfp-value-column">' + money(item.amount) + '</td><td><span data-commitment-status class="sgfp-status-badge sgfp-status-' + (effective ? 'done' : 'pending') + '">' + esc(item.status || 'PENDENTE') + '</span></td><td class="sgfp-agenda-actions" aria-label="Ações de ' + esc(description) + '">' + act(effective ? 'Desefetivar' : 'Efetivar', item.actionPath, 'POST', 'data-commitment-id="' + esc(item.id) + '"') + edit('Editar', item.id, 'commitment', effective) + (!effective ? deletion : '<span class="sgfp-muted">Desfaça para editar</span>') + '</td></tr>';
    });
    const body = ordered.length ? table('Agenda mensal de ' + monthName, ['Vencimento', 'Descrição', 'Categoria', 'Valor', 'Status', 'Ações'], rows) : '<p class="sgfp-agenda-empty" role="status">Nenhum compromisso previsto para este mês.</p>';
    return panel('Agenda de compromissos', '<div class="sgfp-agenda-heading"><div><p class="sgfp-eyebrow">AGENDA MENSAL</p><h3 class="sgfp-agenda-month" tabindex="-1">' + esc(monthName) + '</h3><p class="sgfp-intro">Compromissos previstos, incluindo recorrências ativas.</p></div><button type="button" class="sgfp-primary-action" data-view="commitments" data-open-commitment>Adicionar Compromisso</button></div><div data-agenda aria-live="polite">' + body + '</div>');
  };
  const chartYear = (value) => {
    const year = Number(value);
    return Number.isInteger(year) && year >= 1900 && year <= 2200 ? year : new Date().getFullYear();
  };
  const chartTickIndices = (points, frequency = 5, minimumPixelDistance = 78) => {
    if (!points.length) return [];
    const first = Date.parse(String(points[0].date || '') + 'T00:00:00Z');
    const lastIndex = points.length - 1;
    const candidates = points.reduce((result, item, index) => {
      const current = Date.parse(String(item.date || '') + 'T00:00:00Z');
      const elapsedDays = Number.isFinite(first) && Number.isFinite(current)
        ? Math.round((current - first) / 86400000)
        : index;
      if (index === 0 || index === lastIndex || elapsedDays % frequency === 0) result.push(index);
      return result;
    }, []);
    const filtered = [];
    const pixelPerPoint = points.length > 1 ? Math.max(1, (Math.max(720, points.length * 22) - 76) / (points.length - 1)) : 0;
    candidates.forEach((index) => {
      const isBoundary = index === 0 || index === lastIndex;
      const previous = filtered[filtered.length - 1];
      const farEnough = previous == null || (index - previous) * pixelPerPoint >= minimumPixelDistance;
      if (isBoundary || farEnough) filtered.push(index);
    });
    if (filtered[filtered.length - 1] !== lastIndex) filtered.push(lastIndex);
    return Array.from(new Set(filtered)).sort((a, b) => a - b);
  };
  const chartScale = (values) => {
    const finite = values.filter((value) => Number.isFinite(value));
    if (!finite.length) return { min: -1, max: 1 };
    const observedMin = Math.min(...finite), observedMax = Math.max(...finite);
    const observedSpan = observedMax - observedMin;
    const center = (observedMin + observedMax) / 2;
    const margin = observedSpan > 0
      ? Math.max(observedSpan * 0.12, Math.abs(center) * 0.01, 1)
      : Math.max(Math.abs(center) * 0.12, 1);
    return { min: observedMin - margin, max: observedMax + margin };
  };
  const chartValue = (value) => {
    const numeric = Number(value);
    return Number.isFinite(numeric) ? numeric : 0;
  };
  const evolutionChart = (series, selectedYear, selectedMonth, chartError = '') => {
    const points = Array.isArray(series) ? series : list(series);
    const normalizedPoints = points.map((item) => ({
      ...item,
      realized_closing_balance: chartValue(item.realized_closing_balance),
      projected_closing_balance: chartValue(item.projected_closing_balance)
    }));
    dailySeries = normalizedPoints;
    if (chartError) return '<section class="sgfp-dashboard-section sgfp-evolution" aria-labelledby="sgfp-evolution-title"><div class="sgfp-panel-heading"><div><p class="sgfp-eyebrow">EVOLUÇÃO MENSAL</p><h2 id="sgfp-evolution-title">Evolução do saldo</h2></div><div class="sgfp-chart-controls"><div class="sgfp-chart-year" role="group" aria-label="Ano do gráfico"><button type="button" class="sgfp-month-control" data-chart-year-shift="-1" aria-label="Ano anterior">‹</button><output data-chart-year>' + esc(selectedYear) + '</output><button type="button" class="sgfp-month-control" data-chart-year-shift="1" aria-label="Próximo ano">›</button></div></div></div><p class="sgfp-error-message" role="alert">Não foi possível carregar a evolução mensal.</p><button type="button" class="sgfp-secondary-action" data-chart-retry>Tentar novamente</button></section>';
    if (!normalizedPoints.length) return '<section class="sgfp-dashboard-section sgfp-evolution" aria-labelledby="sgfp-evolution-title"><h2 id="sgfp-evolution-title">Evolução do saldo</h2><p class="sgfp-chart-empty" role="status">Nenhuma informação diária disponível para este intervalo.</p></section>';
    const values = normalizedPoints.flatMap((item) => [item.realized_closing_balance, item.projected_closing_balance]);
    const scale = chartScale(values);
    const min = scale.min, max = scale.max;
    const span = max - min;
    // Give the selected month's daily series enough vertical room to remain legible.
    const width = Math.max(720, normalizedPoints.length * 22), height = 360, left = 58, right = 18, top = 28, bottom = 54;
    const x = (index) => left + (normalizedPoints.length === 1 ? (width - left - right) / 2 : index * (width - left - right) / (normalizedPoints.length - 1));
    const y = (value) => top + (max - value) * (height - top - bottom) / span;
    const stepLine = (field) => normalizedPoints.map((item, index) => { const current = y(item[field]).toFixed(1); const next = index < normalizedPoints.length - 1 ? y(normalizedPoints[index + 1][field]).toFixed(1) : current; return (index ? 'L' : 'M') + x(index).toFixed(1) + ' ' + current + (index < normalizedPoints.length - 1 ? ' H' + x(index + 1).toFixed(1) + ' V' + next : ''); }).join(' ');
    const plannedLine = stepLine('projected_closing_balance');
    const effectiveLine = stepLine('realized_closing_balance');
    const grid = Array.from({ length: 5 }, (_, index) => { const value = min + (span * index / 4); const yy = y(value).toFixed(1); return '<line x1="' + left + '" y1="' + yy + '" x2="' + (width - right) + '" y2="' + yy + '" class="sgfp-chart-grid"/><text x="' + (left - 8) + '" y="' + (Number(yy) + 4) + '" text-anchor="end" class="sgfp-chart-axis-label">' + esc(money(value)) + '</text>'; }).join('');
    const dots = normalizedPoints.map((item, index) => {
      const hitSize = 18;
      const point = (field, label, value, tone) => '<g class="sgfp-chart-point sgfp-chart-point-' + tone + '"><rect x="' + (x(index) - hitSize / 2).toFixed(1) + '" y="' + (y(item[field]) - hitSize / 2).toFixed(1) + '" width="' + hitSize + '" height="' + hitSize + '" class="sgfp-chart-point-hit" tabindex="0" role="button" data-chart-point data-date="' + esc(item.date) + '" aria-label="' + esc(dateLabel(item.date) + ', ' + label + ' ' + money(value) + '. Enter para abrir detalhes.') + '"><title>' + esc(dateLabel(item.date) + ' · ' + label + ': ' + money(value)) + '</title></rect></g>';
      return point('realized_closing_balance', 'saldo realizado', item.realized_closing_balance, 'effective') + point('projected_closing_balance', 'saldo projetado', item.projected_closing_balance, 'planned');
    }).join('');
    const yAxisTitle = '';
    const labels = chartTickIndices(normalizedPoints).map((index) => { const item = normalizedPoints[index]; return '<text class="sgfp-chart-label" x="' + x(index).toFixed(1) + '" y="' + (height - 8) + '" text-anchor="middle">' + esc(dateLabel(item.date)) + '</text>'; }).join('');
    return '<section class="sgfp-dashboard-section sgfp-evolution" aria-labelledby="sgfp-evolution-title"><div class="sgfp-panel-heading"><div><p class="sgfp-eyebrow">EVOLUÇÃO MENSAL</p><h2 id="sgfp-evolution-title">Evolução do saldo</h2><p class="sgfp-chart-subtitle">' + esc(monthLongLabel(selectedMonth)) + ' · ' + esc(dateLabel(normalizedPoints[0].date) + ' a ' + dateLabel(normalizedPoints[normalizedPoints.length - 1].date)) + '</p></div><div class="sgfp-chart-controls"><div class="sgfp-chart-year" role="group" aria-label="Ano do gráfico"><button type="button" class="sgfp-month-control" data-chart-year-shift="-1" aria-label="Ano anterior">‹</button><output data-chart-year>' + esc(selectedYear) + '</output><button type="button" class="sgfp-month-control" data-chart-year-shift="1" aria-label="Próximo ano">›</button></div><button type="button" class="sgfp-secondary-action sgfp-chart-expand" data-chart-expand aria-pressed="false">Ampliar gráfico</button></div></div><div class="sgfp-chart-legend" aria-label="Legenda do gráfico"><span class="sgfp-legend-planned">Saldo projetado</span><span class="sgfp-legend-effective">Saldo realizado</span></div><div class="sgfp-chart-wrap" tabindex="0" role="region" aria-label="Gráfico de evolução mensal do mês selecionado"><svg class="sgfp-chart" viewBox="0 0 ' + width + ' ' + height + '" role="img" aria-labelledby="sgfp-evolution-title sgfp-evolution-description"><desc id="sgfp-evolution-description">Saldo diário em reais, com pontos selecionáveis para cada data de ' + esc(dateLabel(normalizedPoints[0].date) + ' a ' + dateLabel(normalizedPoints[normalizedPoints.length - 1].date)) + '.</desc>' + yAxisTitle + grid + '<path d="' + plannedLine + '" class="sgfp-chart-line sgfp-chart-planned" fill="none"/><path d="' + effectiveLine + '" class="sgfp-chart-line sgfp-chart-effective" fill="none"/>' + dots + labels + '</svg></div><div class="sgfp-daily-details" data-daily-details tabindex="-1" hidden aria-live="polite"></div></section>';
  };
  const dailyDetails = (item, movements, categories) => {
    const rows = movements.filter((x) => String(x.effective_at || '').slice(0, 10) === item.date).sort((a, b) => String(a.effective_at || '').localeCompare(String(b.effective_at || '')) || String(a.id || '').localeCompare(String(b.id || ''))).map((x) => '<tr><td>' + esc(x.name || x.description || '—') + '</td><td>' + esc(x.effect || '—') + '</td><td>' + esc(money(x.amount)) + '</td><td>' + esc(categories[String(x.category_id)] || 'Sem categoria') + '</td></tr>').join('');
    return '<div class="sgfp-daily-details-heading"><div><h3 id="sgfp-daily-details-title">Movimentações de ' + esc(dateLabel(item.date)) + '</h3><p>Saldo de abertura: <strong>' + money(item.opening_balance) + '</strong> · Saldo de fechamento: <strong>' + money(item.realized_closing_balance) + '</strong></p></div><button type="button" class="sgfp-secondary-action" data-close-daily-details>Fechar</button></div><dl class="sgfp-daily-totals"><div><dt>Entradas realizadas</dt><dd>' + money(item.realized_inflows) + '</dd></div><div><dt>Saídas realizadas</dt><dd>' + money(item.realized_outflows) + '</dd></div><div><dt>Saldo projetado</dt><dd>' + money(item.projected_closing_balance) + '</dd></div></dl>' + (rows ? '<div class="sgfp-table-wrap"><table><caption class="sgfp-visually-hidden">Lançamentos realizados em ' + esc(dateLabel(item.date)) + '</caption><thead><tr><th scope="col">Descrição</th><th scope="col">Natureza</th><th scope="col">Valor</th><th scope="col">Categoria</th></tr></thead><tbody>' + rows + '</tbody></table></div>' : '<p class="sgfp-agenda-empty" role="status">Nenhuma movimentação realizada neste dia.</p>');
  };
  const openDailyDetails = async (trigger) => {
    const item = dailySeries.find((x) => x.date === trigger.dataset.date); const panel = app.querySelector('[data-daily-details]'); if (!item || !panel) return;
    dailyTrigger = trigger; panel.hidden = false; panel.innerHTML = '<p role="status">Carregando detalhes do dia…</p>'; panel.focus();
    try { if (!dailyMovements) dailyMovements = list(await api('/movements?month=' + encodeURIComponent(item.date.slice(0, 7) + '-01'))); panel.innerHTML = dailyDetails(item, dailyMovements, {}); panel.querySelector('[data-close-daily-details]')?.focus(); } catch (e) { panel.innerHTML = '<p class="sgfp-error-message" role="alert">Não foi possível carregar os detalhes: ' + esc(e.message) + '</p>'; }
  };
  const dashboard = (d, series, selectedMonth, selectedYear, chartError = '') => {
    const beforeStart = d.is_before_financial_start === true;
    const emptyState = beforeStart
      ? '<section class="sgfp-empty-state sgfp-dashboard-slot sgfp-dashboard-before-start" role="status"><h2>Período anterior ao início do controle</h2><p>Seu controle financeiro ainda não havia começado neste mês. Os valores exibidos permanecem derivados dos lançamentos registrados.</p></section>'
      : '<section class="sgfp-empty-state sgfp-dashboard-slot sgfp-dashboard-before-start" aria-hidden="true" hidden><h2>Período anterior ao início do controle</h2><p>Seu controle financeiro ainda não havia começado neste mês. Os valores exibidos permanecem derivados dos lançamentos registrados.</p></section>';
    return '<div class="sgfp-dashboard" aria-label="Resumo financeiro mensal">' +
      '<section class="sgfp-balance-hero" aria-labelledby="sgfp-balance-title"><div><p class="sgfp-eyebrow">MINHA CONTA · MÊS SELECIONADO</p><h2 id="sgfp-balance-title">Saldo atual</h2><p class="sgfp-dashboard-note">Derivado dos lançamentos financeiros ativos.</p></div><strong>' + money(d.current_balance) + '</strong></section>' +
      emptyState +
      '<section class="sgfp-dashboard-section" aria-labelledby="sgfp-expected-title"><div class="sgfp-panel-heading"><div><p class="sgfp-eyebrow">PREVISTO</p><h2 id="sgfp-expected-title">Compromissos do mês</h2></div><span class="sgfp-status-label">Ainda pendentes</span></div><div class="sgfp-metric-grid">' +
        dashboardMetric('Mês anterior', d.opening_balance, 'neutral', 'Saldo final do último dia') + dashboardMetric('Entradas previstas', d.expected_inflows, 'positive', 'A receber') + dashboardMetric('Saídas previstas', d.expected_outflows, 'negative', 'A pagar') + dashboardMetric('Saldo previsto', d.expected_closing_balance, 'accent', 'Ao final do mês') +
      '</div></section>' +
      '<section class="sgfp-dashboard-section" aria-labelledby="sgfp-realized-title"><div class="sgfp-panel-heading"><div><p class="sgfp-eyebrow">REALIZADO</p><h2 id="sgfp-realized-title">Lançamentos efetivados do mês</h2></div><span class="sgfp-status-label">Movimentações realizadas</span></div><div class="sgfp-metric-grid">' +
        dashboardMetric('Entradas realizadas', d.realized_inflows, 'positive', 'Compromissos efetivados') + dashboardMetric('Saídas realizadas', d.realized_outflows, 'negative', 'Compromissos efetivados') + dashboardMetric('Saldo final', d.current_balance, 'accent', 'Mês anterior + lançamentos realizados') +
      '</div></section>' +
      evolutionChart(series, selectedYear, selectedMonth, chartError) +
      '<div class="sgfp-dashboard-actions"><button type="button" class="sgfp-secondary-action" data-view="entries">Ver lançamentos</button></div>' +
    '</div>';
  };
  const decimal = (x) => {
    const raw = String(x == null ? '' : x).trim().replace(/\s/g, '').replace(/^R\$\s*/i, '');
    if (!raw) return '';
    const normalized = raw.includes(',') ? raw.replace(/\./g, '').replace(',', '.') : raw;
    const value = Number(normalized);
    if (!Number.isFinite(value) || value <= 0) throw Error('Informe um valor positivo válido.');
    return value.toFixed(2);
  };
  const currentMonth = () => { const now = new Date(); return now.getFullYear() + '-' + String(now.getMonth() + 1).padStart(2, '0'); };
  const currentDate = () => { const now = new Date(); return String(now.getDate()).padStart(2, '0') + '/' + String(now.getMonth() + 1).padStart(2, '0') + '/' + now.getFullYear(); };
const dateToApi = (value) => {
    const match = String(value || '').trim().match(/^(\d{2})\/(\d{2})\/(\d{4})$/);
    if (!match) throw Error('Informe uma data válida no formato DD/MM/AAAA.');
    const day = Number(match[1]), monthNumber = Number(match[2]), year = Number(match[3]);
    const date = new Date(Date.UTC(year, monthNumber - 1, day));
    if (date.getUTCFullYear() !== year || date.getUTCMonth() !== monthNumber - 1 || date.getUTCDate() !== day) throw Error('Informe uma data válida no formato DD/MM/AAAA.');
    return year + '-' + String(monthNumber).padStart(2, '0') + '-' + String(day).padStart(2, '0');
  };
  const apiToDate = (value) => { const match = String(value || '').match(/^(\d{4})-(\d{2})-(\d{2})$/); return match ? match[3] + '/' + match[2] + '/' + match[1] : ''; };
  const monthParam = () => {
    const value = String(month.value || cfg.month || currentMonth());
    const canonical = /^\d{4}-\d{2}-01$/.test(value) ? value : (/^\d{4}-(0[1-9]|1[0-2])$/.test(value) ? value + '-01' : '');
    if (!canonical) throw Error('Selecione um mês válido.');
    return canonical;
  };
  const commitmentMonth = (item) => {
    const match = String(item && item.commitment_date || '').match(/^(\d{4}-\d{2})-\d{2}$/);
    return match ? match[1] : '';
  };
  const shiftMonth = (delta) => {
    const value = String(month.value || cfg.month || currentMonth());
    const match = value.match(/^(\d{4})-(\d{2})$/);
    if (!match) return;
    const next = new Date(Number(match[1]), Number(match[2]) - 1 + delta, 1);
    month.value = next.getFullYear() + '-' + String(next.getMonth() + 1).padStart(2, '0');
    load();
  };
  const mountMonthNavigation = () => {
    const navigation = app.querySelector('[data-month-navigation]');
    if (!navigation || navigation.querySelector('[data-shift-month]')) return;
    const controls = document.createElement('div');
    controls.className = 'sgfp-month-controls';
    controls.innerHTML = '<button type="button" class="sgfp-month-control" data-shift-month="-1" aria-label="Mês anterior">‹</button>' +
      '<button type="button" class="sgfp-month-control" data-shift-month="1" aria-label="Próximo mês">›</button>';
    navigation.appendChild(controls);
  };
  const monthField = (value) => String(value || '').replace(/-01$/, '');
  const apiError = (x) => {
    const error = x && x.error && typeof x.error === 'object' ? x.error : x;
    const message = error && typeof error.message === 'string' ? error.message : '';
    const code = error && typeof error.code === 'string' ? error.code : '';
    if (message) return message;
    return ({ UNAUTHORIZED: 'Sua sessão expirou. Entre novamente para continuar.', FORBIDDEN: 'Você não tem permissão para realizar esta operação.', VALIDATION_ERROR: 'Confira os dados informados e tente novamente.', CONFLICT: 'A operação entrou em conflito com o estado atual. Atualize e tente novamente.', NOT_FOUND: 'O registro não foi encontrado ou já foi removido.', INTERNAL_ERROR: 'O servidor não conseguiu concluir a operação. Tente novamente.' })[code] || 'Não foi possível concluir a operação. Tente novamente.';
  };
  const themeStorageKey = 'sgfp-theme';
  const applyTheme = (theme) => {
    const normalized = theme === 'light' ? 'light' : 'dark';
    app.dataset.theme = normalized;
    return normalized;
  };
  const themeFromResponse = (value) => {
    const theme = value && typeof value.theme === 'string' ? value.theme : value;
    return theme === 'dark' || theme === 'light' ? theme : null;
  };
  const maskDateInput = (input) => {
    const digits = String(input.value || '').replace(/\D/g, '').slice(0, 8);
    input.value = digits.length > 4 ? digits.slice(0, 2) + '/' + digits.slice(2, 4) + '/' + digits.slice(4) : (digits.length > 2 ? digits.slice(0, 2) + '/' + digits.slice(2) : digits);
  };
  async function api(path, options) {
    const r = await fetch(base + path, { credentials: 'same-origin', ...(options || {}), headers: { Accept: 'application/json', 'X-WP-Nonce': cfg.nonce || '', ...((options || {}).headers || {}) } });
    if (r.status === 204) return null;
    const d = await r.json().catch(() => ({})); if (!r.ok) { const error = Error(apiError(d)); error.status = r.status; error.code = d && d.error && typeof d.error.code === 'string' ? d.error.code : ''; error.correlationId = d && d.error && typeof d.error.correlation_id === 'string' ? d.error.correlation_id : ''; if (options && options.tombstoneNotFound === true && error.status === 404 && error.code === 'NOT_FOUND') return null; if (options && options.outOfRangeRecurrenceIsEmpty === true && error.status === 400 && error.code === 'VALIDATION_ERROR') return null; throw error; } return d;
  }
  async function promptInitialBalanceForNewProfile() {
    try {
      const account = await api('/account');
      if (!account || account.initial_balance_configured === true || !account.id) return;
      if (document.querySelector('[data-initial-balance-modal]')) return;
      content.insertAdjacentHTML('afterbegin', '<div class="sgfp-modal-backdrop" data-initial-balance-modal role="dialog" aria-modal="true" aria-labelledby="sgfp-initial-balance-title"><div class="sgfp-modal"><p class="sgfp-eyebrow">PRIMEIRO ACESSO</p><h2 id="sgfp-initial-balance-title">Defina seu saldo inicial</h2><p>Informe o saldo atual e a data em que ele passou a valer. As operações financeiras permanecerão bloqueadas até a confirmação.</p>' + form('initial-balance', [field('Saldo inicial', 'amount', 'text', 'inputmode="decimal" autocomplete="off" required'), field('Data do saldo inicial', 'effective_month', 'date', 'value="' + esc(new Date().toISOString().slice(0, 10)) + '" required')], 'Confirmar saldo inicial') + '</div></div>');
      document.querySelector('[data-initial-balance-modal] [name="amount"]')?.focus();
    } catch (_) {
      // The normal page load already renders a recoverable REST error.
    }
  }
  const chooseRecurrenceScope = () => new Promise((resolve) => {
    const title = 'sgfp-recurrence-scope-title';
    content.insertAdjacentHTML('beforeend', '<div class="sgfp-modal-backdrop" data-recurrence-scope-modal role="dialog" aria-modal="true" aria-labelledby="' + title + '"><div class="sgfp-modal"><p class="sgfp-eyebrow">EDITAR RECORRÊNCIA</p><h2 id="' + title + '">Aplicar alteração</h2><p>Escolha se a alteração vale somente para esta ocorrência ou também para as próximas.</p><div class="sgfp-modal-actions"><button type="button" class="sgfp-secondary-action" data-recurrence-scope="current">Somente esta</button><button type="button" class="sgfp-primary-action" data-recurrence-scope="current_and_future">Esta e futuras</button><button type="button" class="sgfp-text-action" data-recurrence-scope="cancel">Cancelar</button></div></div></div>');
    const modal = content.querySelector('[data-recurrence-scope-modal]');
    modal.querySelector('[data-recurrence-scope="current"]')?.focus();
    modal.addEventListener('click', (event) => { const button = event.target.closest('[data-recurrence-scope]'); if (!button) return; modal.remove(); resolve(button.dataset.recurrenceScope === 'cancel' ? null : button.dataset.recurrenceScope); });
  });
  const panel = (h, body) => '<section class="sgfp-panel"><h2>' + h + '</h2>' + body + '</section>';
  const field = (l, n, t = 'text', x = '') => '<label class="sgfp-field"><span>' + l + '</span><input name="' + n + '" type="' + t + '" ' + x + '></label>';
  const form = (n, fields, button = 'Salvar') => '<form class="sgfp-form" data-form="' + n + '">' + fields.join('') + '<button class="sgfp-primary-action" type="submit">' + button + '</button><p class="sgfp-form-status" data-form-status role="status"></p></form>';
  const act = (l, p, m = 'POST', attrs = '') => '<button type="button" class="sgfp-text-action" data-action="' + esc(p) + '" data-method="' + m + '"' + (attrs ? ' ' + attrs : '') + '>' + l + '</button>';
  const edit = (l, id, kind, disabled = false) => '<button type="button" class="sgfp-text-action" data-edit="' + esc(id) + '" data-edit-kind="' + kind + '"' + (disabled ? ' disabled aria-disabled="true" title="Desfaça a efetivação para editar"' : '') + '>' + l + '</button>';
  const table = (caption, headers, rows) => '<div class="sgfp-table-wrap"><table><caption class="sgfp-sr-only">' + esc(caption) + '</caption><thead><tr>' + headers.map((h) => '<th scope="col">' + esc(h) + '</th>').join('') + '</tr></thead><tbody>' + (rows.length ? rows.join('') : '<tr><td colspan="' + headers.length + '" class="sgfp-empty">Nenhum registro encontrado.</td></tr>') + '</tbody></table></div>';
  const hydrateCommitmentForm = (target, item) => {
    if (!target || !item) return;
    const values = { name: item.name || '', amount: item.amount || '', nature: item.nature || 'SAIDA', category_id: item.category_id == null ? '' : String(item.category_id), commitment_date: apiToDate(item.commitment_date) };
    Object.keys(values).forEach((name) => { const input = target.querySelector('[name="' + name + '"]'); if (input) input.value = values[name]; });
    target.dataset.editId = item.id;
    target.dataset.recurrenceId = item.recurrence_id || '';
    target.dataset.occurrenceMonth = item.commitment_date ? String(item.commitment_date).slice(0, 7) + '-01' : '';
    const status = target.querySelector('[data-form-status]'); if (status) status.textContent = 'Editando registro.';
    const submitButton = target.querySelector('button[type="submit"]'); if (submitButton) submitButton.textContent = 'Atualizar';
  };
  const setView = (next) => { view = next; app.querySelectorAll('[data-view]').forEach((button) => { const active = button.dataset.view === next; button.classList.toggle('is-active', active); if (active) button.setAttribute('aria-current', 'page'); else button.removeAttribute('aria-current'); }); };
  async function load(next = view) {
    const sequence = ++loadSequence;
    dailyMovements = null;
    setView(next); title.textContent = names[view]; content.setAttribute('aria-busy', 'true'); content.innerHTML = '<p class="sgfp-loading" role="status">Carregando…</p>';
    try {
      const mo = monthParam();
      if (view === 'overview' && app.dataset.chartYear) {
        const year = chartYear(app.dataset.chartYear);
        const [d, commitments, categories] = await Promise.all([api('/dashboard?month=' + encodeURIComponent(mo)), api('/commitments'), api('/categories')]);
        if (sequence !== loadSequence) return;
        let history = { items: [] }, chartError = '';
        try {
          history = await api('/dashboard/evolution/annual?year=' + year);
        } catch (_) {
          chartError = 'chart-unavailable';
        }
        if (sequence !== loadSequence) return;
        const operational = list(commitments).filter(isOperationalCommitment);
        const categoryMap = Object.fromEntries(list(categories).map((x) => [String(x.id), x.name]));
        const selectedMonth = mo.slice(0, 7);
        const chartMonth = String(year) + '-' + selectedMonth.slice(5, 7);
        history.items = list(history).filter((item) => String(item.date || '').slice(0, 7) === chartMonth);
        const direct = operational.filter((x) => commitmentMonth(x) === selectedMonth);
        const recurring = d.is_before_financial_start === true ? [] : uniqueRecurrences(operational.filter((x) => x.recurrence_id && commitmentMonth(x) !== selectedMonth));
        const occurrences = await Promise.all(recurring.map((x) => api('/recurrences/' + encodeURIComponent(x.recurrence_id) + '/occurrences/' + encodeURIComponent(mo), { tombstoneNotFound: true, outOfRangeRecurrenceIsEmpty: true })));
        if (sequence !== loadSequence) return;
        const items = uniqueCommitments(direct.concat(occurrences.filter(Boolean))).filter(isOperationalCommitment).map((x) => ({ ...x, actionPath: actionPathFor(x, mo) }));
        content.innerHTML = dashboard(d, history.items, selectedMonth, year, chartError) + agenda(items, categoryMap, selectedMonth); return;
      }
      if (view === 'entries') {
        const [d, commitments, categories] = await Promise.all([api('/movements?month=' + encodeURIComponent(mo)), api('/commitments'), api('/categories')]);
        if (sequence !== loadSequence) return;
        const commitmentMap = Object.fromEntries(list(commitments).map((x) => [String(x.id), x]));
        const categoryMap = Object.fromEntries(list(categories).map((x) => [String(x.id), x.name]));
        const monthName = monthLongLabel(mo.slice(0, 7));
        const movements = list(d).slice().sort((a, b) => String(a.effective_at || '').localeCompare(String(b.effective_at || '')) || String(a.id || '').localeCompare(String(b.id || '')));
        const rows = movements.map((x) => {
          const commitment = x.commitment_id == null ? null : commitmentMap[String(x.commitment_id)];
          const category = commitment && commitment.category_id != null && categoryMap[String(commitment.category_id)] ? categoryMap[String(commitment.category_id)] : 'Sem categoria';
          const status = (commitment && commitment.status) || x.status || 'EFETIVADO';
          const statusClass = status === 'EFETIVADO' ? 'done' : 'pending';
          return '<tr><td>' + esc(dateLabel(String(x.effective_at || '').slice(0, 10)) || '—') + '</td><td>' + esc(x.name) + '</td><td>' + esc(x.description || '—') + '</td><td>' + esc(category) + '</td><td class="sgfp-value-column">' + money(x.amount) + '</td><td>' + esc(x.effect || x.effect_type) + '</td><td><span class="sgfp-status-badge sgfp-status-' + statusClass + '">' + esc(status) + '</span></td></tr>';
        });
        const body = rows.length ? table('Lançamentos de ' + monthName, ['Data de efetivação', 'Nome', 'Descrição', 'Categoria', 'Valor', 'Tipo', 'Status'], rows) : '<p class="sgfp-agenda-empty" role="status">Nenhum lançamento efetivado em ' + esc(monthName) + '.</p>';
        content.innerHTML = panel('Lançamentos', '<div class="sgfp-panel-heading"><div><p class="sgfp-eyebrow">PERÍODO SELECIONADO</p><h3 class="sgfp-agenda-month" tabindex="-1">' + esc(monthName) + '</h3><p class="sgfp-intro">Exibindo somente lançamentos efetivados deste mês.</p></div></div><div data-entries aria-live="polite">' + body + '</div>');
        return;
      }
      if (view === 'commitments') { const cats = await api('/categories'); const opts = list(cats).map((x) => '<option value="' + esc(x.id) + '">' + esc(x.name) + '</option>').join(''); content.innerHTML = panel('Compromissos', '<p class="sgfp-intro">Cadastre ou edite um compromisso. A listagem e o acompanhamento ficam na Visão Geral.</p>' + form('commitment', [field('Nome', 'name', 'text', 'required'), '<label class="sgfp-field sgfp-money-field"><span>Valor</span><input class="sgfp-money-input" name="amount" type="text" inputmode="decimal" placeholder="0,00" required></label>', '<label class="sgfp-field"><span>Natureza</span><select name="nature"><option value="ENTRADA">Entrada</option><option value="SAIDA">Saída</option></select></label>', '<label class="sgfp-field"><span>Categoria</span><select name="category_id"><option value="">Sem categoria</option>' + opts + '</select></label><button type="button" class="sgfp-text-action" data-quick-category>+ Nova categoria</button>', field('Data do compromisso', 'commitment_date', 'text', 'inputmode="numeric" autocomplete="off" placeholder="DD/MM/AAAA" value="' + esc(currentDate()) + '" required'), '<label class="sgfp-field"><span>Recorrência</span><select name="recurrence_mode" data-recurrence-mode><option value="none">Sem recorrência</option><option value="finite">Por período definido</option><option value="indefinite">Indefinida</option></select></label>', '<div class="sgfp-recurrence-fields" data-recurrence-fields hidden><p class="sgfp-recurrence-note">A recorrência começa no mês do compromisso.</p><label class="sgfp-field" data-recurrence-count><span>Quantidade de meses</span><input name="recurrence_months_count" type="number" min="1" inputmode="numeric" placeholder="Ex.: 12"></label></div>'])); return; }
      if (view === 'recurrences') { const d = list(await api('/commitments?month=' + encodeURIComponent(mo))).filter((x) => x.recurrence_id && isOperationalCommitment(x)); const rows = d.map((x) => '<tr><td>' + esc(x.name) + '</td><td class="sgfp-value-column">' + money(x.amount) + '</td><td>' + act('Abrir ocorrência', '/recurrences/' + encodeURIComponent(x.recurrence_id) + '/occurrences/' + encodeURIComponent(mo), 'GET') + '</td></tr>'); content.innerHTML = panel('Recorrências', '<div data-occurrence></div>' + (rows.length ? table('Recorrências', ['Descrição', 'Valor', 'Ação'], rows) : '<p class="sgfp-empty">Nenhuma recorrência encontrada.</p>')); return; }
      if (view === 'categories') { const d = list(await api('/categories')); content.innerHTML = panel('Categorias', form('category', [field('Nome', 'name')]) + d.map((x) => '<p data-category-id="' + esc(x.id) + '">' + esc(x.name) + ' ' + edit('Editar', x.id, 'category') + ' ' + act('Excluir', '/categories/' + encodeURIComponent(x.id), 'DELETE') + '</p>').join('')); return; }
      if (view === 'account') { const account = await api('/account'); const setup = account.initial_balance_configured === true; const disabled = setup && !accountEditing ? 'disabled' : ''; const initialBalanceRequired = setup ? '' : 'required'; const initialDate = setup ? disabled : 'value="' + esc(new Date().toISOString().slice(0, 10)) + '" required'; const userName = account.user_name || 'Usuário'; const financialDate = account.financial_start_date ? dateLabel(account.financial_start_date) : 'Ainda não definido'; const administrativeDate = account.created_at ? dateLabel(String(account.created_at).slice(0, 10)) : '—'; content.innerHTML = panel(esc(account.name || 'Minha Conta'), '<div class="sgfp-card"><span>Saldo atual · derivado dos lançamentos ativos</span><strong>' + money(account.balance) + '</strong></div><dl class="sgfp-account-summary"><div><dt>Nome cadastrado</dt><dd>' + esc(userName) + '</dd></div><div><dt>Saldo inicial</dt><dd>' + money(account.initial_balance) + '</dd></div><div><dt>Início do histórico financeiro</dt><dd>' + esc(financialDate) + '</dd></div><div><dt>Conta criada no sistema</dt><dd>' + esc(administrativeDate) + '</dd></div></dl>' + form('account', [field('Nome da conta', 'name', 'text', 'value="' + esc(account.name) + '"'), field('Saldo inicial', 'amount', 'text', 'inputmode="decimal" ' + initialBalanceRequired + ' ' + disabled), field('Data do saldo inicial', 'effective_month', 'date', initialDate)], 'Salvar conta') + (setup && !accountEditing ? '<button type="button" class="sgfp-secondary-action" data-edit-account>Editar saldo inicial</button>' : '') + (setup ? '<p class="sgfp-form-status">Saldo inicial configurado. Edite para alterar.</p>' : '<p class="sgfp-form-status">Defina a data e o saldo inicial.</p>')); return; }
      if (view === 'settings') { const theme = app.dataset.theme || 'dark'; content.innerHTML = panel('Configurações', '<section class="sgfp-settings-group sgfp-theme-group" aria-labelledby="sgfp-theme-title"><div><p class="sgfp-eyebrow">PREFERÊNCIAS</p><h3 id="sgfp-theme-title">Aparência</h3><p class="sgfp-intro">Escolha como o SGFP deve ser exibido.</p></div><label class="sgfp-setting"><span>Tema</span><select data-theme><option value="dark">Escuro</option><option value="light">Claro</option></select></label></section>' + form('backup', [], 'Baixar backup ZIP') + form('restore', [field('Arquivo ZIP', 'backup', 'file', 'accept=".zip"')], 'Validar restauração') + '<div data-restore-confirm></div>' + '<div class="sgfp-danger-zone" data-danger="reset"><h3>Resetar perfil financeiro</h3><p>Remove os dados financeiros e mantém seu login.</p><button class="sgfp-secondary-action" type="button" data-start-danger="reset">Iniciar reset</button><div data-danger-stage></div></div>' + '<div class="sgfp-danger-zone" data-danger="delete"><h3>Excluir conta de acesso</h3><p>Remove os dados SGFP e sua conta WordPress.</p><button class="sgfp-secondary-action" type="button" data-start-danger="delete">Iniciar exclusão</button><div data-danger-stage></div></div>'); content.querySelector('[data-theme]').value = theme; }
    } catch (e) { if (sequence === loadSequence) content.innerHTML = panel('Não foi possível carregar', '<p class="sgfp-error-message" role="alert">' + esc(e.message) + '</p><button data-retry="' + view + '">Tentar novamente</button>'); return false; } finally { if (sequence === loadSequence) content.setAttribute('aria-busy', 'false'); }
    return true;
  }
  async function retryChart() {
    const chart = app.querySelector('.sgfp-evolution');
    if (!chart || view !== 'overview') return;
    const sequence = loadSequence;
    const year = chartYear(app.dataset.chartYear);
    const retry = chart.querySelector('[data-chart-retry]');
    if (retry) { retry.disabled = true; retry.textContent = 'Carregando…'; }
    try {
      const history = await api('/dashboard/evolution/annual?year=' + year);
      if (loadSequence !== sequence || view !== 'overview' || chartYear(app.dataset.chartYear) !== year) return;
      const selectedMonth = monthParam().slice(0, 7);
      const chartMonth = String(year) + '-' + selectedMonth.slice(5, 7);
      const monthlyHistory = list(history).filter((item) => String(item.date || '').slice(0, 7) === chartMonth);
      const replacement = document.createElement('div');
      replacement.innerHTML = evolutionChart(monthlyHistory, year, selectedMonth);
      chart.replaceWith(replacement.firstElementChild);
    } catch (_) {
      // Keep the isolated chart error state available for another attempt.
    } finally {
      if (loadSequence === sequence && view === 'overview' && chartYear(app.dataset.chartYear) === year) {
        const currentRetry = app.querySelector('.sgfp-evolution [data-chart-retry]');
        if (currentRetry) { currentRetry.disabled = false; currentRetry.textContent = 'Tentar novamente'; }
      }
    }
  }
  const updateCommitmentRow = (action, result) => {
    const row = action.closest('tr');
    if (!row || !action.dataset.commitmentId) return;
    const undoing = /\/undo-effectuation$/.test(action.dataset.action || '');
    const settled = result && result.status ? result.status === 'EFETIVADO' : !undoing;
    const status = row.querySelector('[data-commitment-status]');
    if (status) { status.textContent = settled ? 'EFETIVADO' : 'PENDENTE'; status.classList.toggle('sgfp-status-done', settled); status.classList.toggle('sgfp-status-pending', !settled); }
    action.textContent = settled ? 'Desefetivar' : 'Efetivar';
    action.dataset.action = action.dataset.action.replace(/\/(?:undo-)?effectuation$/, settled ? '/undo-effectuation' : '/effectuation');
    action.disabled = false;
  };
  const refreshOverviewSummaries = async () => {
    const dashboardElement = content.querySelector('.sgfp-dashboard');
    if (!dashboardElement || view !== 'overview') return;
    const selectedMonth = monthParam().slice(0, 7);
    const year = chartYear(app.dataset.chartYear || selectedMonth.slice(0, 4));
    const [summary, history] = await Promise.all([
      api('/dashboard?month=' + encodeURIComponent(monthParam())),
      api('/dashboard/evolution/annual?year=' + year)
    ]);
    const chartMonth = String(year) + '-' + selectedMonth.slice(5, 7);
    const monthlyHistory = list(history).filter((item) => String(item.date || '').slice(0, 7) === chartMonth);
    const replacement = document.createElement('div');
    replacement.innerHTML = dashboard(summary, monthlyHistory, selectedMonth, year);
    dashboardElement.replaceWith(replacement.firstElementChild);
  };
  const showActionError = (action, error) => {
    const parent = action.parentElement;
    if (!parent) return;
    let feedback = parent.querySelector('[data-action-error]');
    if (!feedback) { feedback = document.createElement('span'); feedback.dataset.actionError = 'true'; feedback.className = 'sgfp-error-message'; feedback.setAttribute('role', 'alert'); parent.appendChild(feedback); }
    feedback.textContent = error.message;
  };
  async function submit(ev) {
    ev.preventDefault(); if (locked) return; locked = true; const f = ev.target, s = f.querySelector('[data-form-status]'), d = Object.fromEntries(new FormData(f)), k = f.dataset.form; s.textContent = 'Processando…';
    try { let path, method = 'POST', body, headers = {};
      if (k === 'initial-balance') { if (!d.amount || !/^\d{4}-\d{2}-\d{2}$/.test(d.effective_month)) throw Error('Informe o saldo e uma data válida.'); await api('/account/initial-balance', { method: 'POST', body: JSON.stringify({ amount: d.amount, effective_month: d.effective_month }), headers: { 'Content-Type': 'application/json' } }); f.closest('[data-initial-balance-modal]')?.remove(); await load('overview'); return; }
      else if (k === 'account') { if ((d.amount && !d.effective_month) || (!d.amount && d.effective_month)) throw Error('Informe valor e data do saldo inicial.'); const updated = await api('/account', { method: 'PATCH', body: JSON.stringify({ name: d.name }), headers: { 'Content-Type': 'application/json' } }); if (d.amount) { if (!/^\d{4}-\d{2}-\d{2}$/.test(d.effective_month)) throw Error('Informe a data do saldo inicial.'); await api('/account/initial-balance', { method: 'POST', body: JSON.stringify({ amount: d.amount, effective_month: d.effective_month }), headers: { 'Content-Type': 'application/json' } }); window.localStorage.setItem(accountSetupKey(updated.id), 'configured'); } accountEditing = false; title.textContent = updated.name || d.name || names.account; }
      else if (k === 'category') { path = f.dataset.editId ? '/categories/' + encodeURIComponent(f.dataset.editId) : '/categories'; method = f.dataset.editId ? 'PATCH' : 'POST'; body = JSON.stringify({ name: d.name }); headers['Content-Type'] = 'application/json'; }
      else if (k === 'commitment') { const amount = decimal(d.amount), commitmentDate = dateToApi(d.commitment_date); const recurringEdit = f.dataset.editId && f.dataset.recurrenceId; if (recurringEdit) { const scope = await chooseRecurrenceScope(); if (!scope) { locked = false; s.textContent = ''; return; } path = '/recurrences/' + encodeURIComponent(f.dataset.recurrenceId) + '/occurrences/' + encodeURIComponent(f.dataset.occurrenceMonth); method = 'PATCH'; body = JSON.stringify({ name: d.name, amount, category_id: d.category_id ? Number(d.category_id) : null, commitment_date: commitmentDate, scope }); } else { path = f.dataset.editId ? '/commitments/' + encodeURIComponent(f.dataset.editId) : '/commitments'; method = f.dataset.editId ? 'PATCH' : 'POST'; body = f.dataset.editId ? JSON.stringify({ name: d.name, amount, category_id: d.category_id ? Number(d.category_id) : null, commitment_date: commitmentDate }) : JSON.stringify({ name: d.name, amount, nature: String(d.nature).toUpperCase(), category_id: d.category_id ? Number(d.category_id) : null, commitment_date: commitmentDate, recurrence_months_count: d.recurrence_mode === 'finite' && d.recurrence_months_count ? Number(d.recurrence_months_count) : null, recurrence_enabled: d.recurrence_mode !== 'none' }); } headers['Content-Type'] = 'application/json'; }
      else if (k === 'backup') { const r = await fetch(base + '/backups', { method: 'POST', credentials: 'same-origin', headers: { 'X-WP-Nonce': cfg.nonce || '' } }); if (!r.ok) { const error = await r.json().catch(() => ({})); throw Error(apiError(error)); } const disposition = r.headers.get('Content-Disposition') || ''; const filenameMatch = disposition.match(/filename="?([^";]+)"?/i); const a = document.createElement('a'); const objectUrl = URL.createObjectURL(await r.blob()); a.href = objectUrl; a.download = filenameMatch ? filenameMatch[1] : 'sgfp-backup.zip'; a.hidden = true; document.body.appendChild(a); a.click(); a.remove(); window.setTimeout(() => URL.revokeObjectURL(objectUrl), 0); s.textContent = 'Backup baixado.'; return; }
      else if (k === 'restore') { const result = await api('/restore-validations', { method: 'POST', body: new FormData(f) }); restoring = result.token; s.textContent = 'Arquivo validado; confirme abaixo.'; f.reset(); content.querySelector('[data-restore-confirm]').innerHTML = '<button data-confirm-restore>Confirmar restauração</button>'; return; }
      else if (k === 'reset') { const phrase = 'RESETAR PERFIL'; if (d.phrase !== phrase) throw Error('Digite exatamente ' + phrase + '.'); path = '/profile-reset'; body = JSON.stringify({ token: d.token, phrase: phrase }); headers['Content-Type'] = 'application/json'; }
      else if (k === 'delete') { const phrase = 'EXCLUIR CONTA'; if (d.phrase !== phrase) throw Error('Digite exatamente ' + phrase + '.'); path = '/account-access'; method = 'DELETE'; body = JSON.stringify({ phrase: phrase, confirmation: true }); headers['Content-Type'] = 'application/json'; }
      if (path) await api(path, { method, body, headers }); s.textContent = 'Operação concluída.'; f.reset(); await load();
    } catch (e) { s.textContent = e.message; } finally { locked = false; }
  }
  app.addEventListener('input', (ev) => { if (ev.target.matches('[name="commitment_date"]')) maskDateInput(ev.target); });
  app.addEventListener('submit', submit); app.addEventListener('click', async (ev) => { const n = ev.target.closest('[data-view]'); if (n) load(n.dataset.view); const retry = ev.target.closest('[data-retry]'); if (retry) load(retry.dataset.retry); const quick = ev.target.closest('[data-quick-category]'); if (quick) { const name = window.prompt('Nome da nova categoria:'); if (name && name.trim()) { try { const created = await api('/categories', { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify({ name: name.trim() }) }); const select = content.querySelector('[name="category_id"]'); if (select) { select.insertAdjacentHTML('beforeend', '<option value="' + esc(created.id) + '">' + esc(created.name) + '</option>'); select.value = created.id; } } catch (e) { content.insertAdjacentHTML('afterbegin', '<p class="sgfp-error-message">' + esc(e.message) + '</p>'); } } return; } const editButton = ev.target.closest('[data-edit]'); if (editButton && editButton.dataset.editKind === 'category') { try { const item = list(await api('/categories')).find((x) => String(x.id) === String(editButton.dataset.edit)); if (!item) throw Error('Registro não encontrado.'); const target = content.querySelector('[data-form="category"]'); if (!target) throw Error('Formulário indisponível.'); target.dataset.editId = item.id; target.querySelector('[name="name"]').value = item.name; target.querySelector('[data-form-status]').textContent = 'Editando registro.'; target.querySelector('button[type="submit"]').textContent = 'Atualizar'; } catch (e) { content.insertAdjacentHTML('afterbegin', '<p class="sgfp-error-message">' + esc(e.message) + '</p>'); } return; } const action = ev.target.closest('[data-action]'); if (action) { if (action.disabled) return; action.disabled = true; try { const result = await api(action.dataset.action, { method: action.dataset.method }); if (action.dataset.method === 'GET' && view === 'recurrences') { const occurrence = content.querySelector('[data-occurrence]'); occurrence.innerHTML = '<div class="sgfp-card"><strong>' + esc(result.name) + '</strong><span>' + money(result.amount) + ' · ' + esc(result.status) + '</span><div>' + (result.status === 'PENDENTE' ? act('Efetivar ocorrência', action.dataset.action + '/effectuation') : act('Desfazer ocorrência', action.dataset.action + '/undo-effectuation')) + '</div></div>'; } else if (action.dataset.commitmentId) { updateCommitmentRow(action, result); await refreshOverviewSummaries(); } else await load('overview'); } catch (e) { action.disabled = false; showActionError(action, e); } } if (ev.target.closest('[data-confirm-restore]') && restoring) api('/restorations', { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify({ token: restoring, confirmation: true }) }).then(() => load('settings')).catch((e) => { ev.target.textContent = e.message; }); });
  app.addEventListener('change', (ev) => { if (ev.target.matches('[data-month]')) { app.dataset.chartYear = String(ev.target.value || '').slice(0, 4) || String(new Date().getFullYear()); load(); } if (ev.target.matches('[data-theme]')) { const next = applyTheme(ev.target.value); api('/preferences/theme', { method: 'PUT', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify({ theme: next }) }).then((result) => { const saved = themeFromResponse(result) || next; applyTheme(saved); window.localStorage.setItem(themeStorageKey, saved); }).catch((e) => { const previous = applyTheme(window.localStorage.getItem(themeStorageKey) || 'dark'); ev.target.value = previous; const status = content.querySelector('[data-form-status]') || content.querySelector('.sgfp-error-message'); if (status) status.textContent = 'Não foi possível salvar o tema: ' + e.message; }); } if (ev.target.matches('[data-recurrence-mode]')) { const fields = content.querySelector('[data-recurrence-fields]'); const count = content.querySelector('[data-recurrence-count]'); if (fields) fields.hidden = ev.target.value === 'none'; if (count) count.hidden = ev.target.value !== 'finite'; } });
  app.addEventListener('click', (ev) => { const control = ev.target.closest('[data-shift-month]'); if (control) shiftMonth(Number(control.dataset.shiftMonth)); const retry = ev.target.closest('[data-chart-retry]'); if (retry) { retryChart(); return; } const expand = ev.target.closest('[data-chart-expand]'); if (expand) { const chart = expand.closest('.sgfp-evolution'); const expanded = chart.classList.toggle('sgfp-chart-expanded'); expand.setAttribute('aria-pressed', expanded ? 'true' : 'false'); expand.textContent = expanded ? 'Reduzir gráfico' : 'Ampliar gráfico'; if (expanded) chart.querySelector('.sgfp-chart-wrap')?.focus(); } });
  app.addEventListener('click', (ev) => { const point = ev.target.closest('[data-chart-point]'); if (point) openDailyDetails(point); const close = ev.target.closest('[data-close-daily-details]'); if (close) { const details = close.closest('[data-daily-details]'); details.hidden = true; dailyTrigger?.focus(); } });
  app.addEventListener('click', (ev) => { const control = ev.target.closest('[data-chart-year-shift]'); if (control) { app.dataset.chartYear = String(chartYear(app.dataset.chartYear) + Number(control.dataset.chartYearShift)); load(); } });
  app.addEventListener('keydown', (ev) => { const point = ev.target.closest('[data-chart-point]'); if (point && (ev.key === 'Enter' || ev.key === ' ')) { ev.preventDefault(); openDailyDetails(point); } if (ev.key === 'Escape') { const details = ev.target.closest('[data-daily-details]'); if (details && !details.hidden) { details.hidden = true; dailyTrigger?.focus(); } } });
  const bootstrapTheme = async () => { const local = window.localStorage.getItem(themeStorageKey); if (local === 'dark' || local === 'light') applyTheme(local); try { const result = await api('/preferences/theme'); applyTheme(themeFromResponse(result) || local || 'dark'); } catch (_) { applyTheme(local || 'dark'); } };
  mountMonthNavigation(); bootstrapTheme().finally(async () => { await load(); await promptInitialBalanceForNewProfile(); });
  app.addEventListener('click', async (ev) => { const start = ev.target.closest('[data-start-danger]'); if (start) { const kind = start.dataset.startDanger, phrase = kind === 'reset' ? 'RESETAR PERFIL' : 'EXCLUIR CONTA', zone = content.querySelector('[data-danger="' + kind + '"]'); if (kind === 'reset') { try { const started = await api('/profile-reset/validation', { method: 'POST' }); if (!started || !started.token) throw Error('Não foi possível iniciar o reset do perfil.'); zone.querySelector('[data-danger-stage]').innerHTML = '<form class="sgfp-form" data-form="reset"><p>Esta ação não pode ser desfeita. Digite a frase para continuar.</p><input type="hidden" name="token" value="' + esc(started.token) + '">' + field('Digite ' + phrase, 'phrase') + '<button class="sgfp-primary-action" type="submit">Confirmar operação</button><button class="sgfp-secondary-action" type="button" data-cancel-danger="reset">Cancelar</button><p class="sgfp-form-status" data-form-status role="status"></p></form>'; zone.querySelector('[name="phrase"]').focus(); } catch (e) { zone.querySelector('[data-danger-stage]').innerHTML = '<p class="sgfp-error-message" role="alert">' + esc(e.message) + '</p>'; } } else { zone.querySelector('[data-danger-stage]').innerHTML = '<form class="sgfp-form" data-form="delete"><p>Esta ação não pode ser desfeita. Digite a frase para continuar.</p>' + field('Digite ' + phrase, 'phrase') + '<button class="sgfp-primary-action" type="submit">Confirmar operação</button><button class="sgfp-secondary-action" type="button" data-cancel-danger="delete">Cancelar</button><p class="sgfp-form-status" data-form-status role="status"></p></form>'; zone.querySelector('[name="phrase"]').focus(); } } const cancel = ev.target.closest('[data-cancel-danger]'); if (cancel) { const zone = content.querySelector('[data-danger="' + cancel.dataset.cancelDanger + '"]'); zone.querySelector('[data-danger-stage]').replaceChildren(); zone.querySelector('[data-start-danger]').focus(); } });
  app.addEventListener('click', (ev) => { const editAccount = ev.target.closest('[data-edit-account]'); if (editAccount) { accountEditing = true; load('account'); } });
  app.addEventListener('click', async (ev) => { const editButton = ev.target.closest('[data-edit][data-edit-kind="commitment"]'); if (view === 'overview' && editButton) { ev.preventDefault(); ev.stopImmediatePropagation(); const item = await api('/commitments/' + encodeURIComponent(editButton.dataset.edit)); await load('commitments'); hydrateCommitmentForm(content.querySelector('[data-form="commitment"]'), item); } }, true);
  app.addEventListener('click', async (ev) => {
    const editButton = ev.target.closest('[data-edit][data-edit-kind="commitment"]');
    if (!editButton || view !== 'commitments') return;
    try {
      const item = await api('/commitments/' + encodeURIComponent(editButton.dataset.edit));
      if (!item) {
        throw Error('Registro não encontrado.');
      }
      hydrateCommitmentForm(content.querySelector('[data-form="commitment"]'), item);
    } catch (e) {
      content.insertAdjacentHTML('afterbegin', '<p class="sgfp-error-message">' + esc(e.message) + '</p>');
    }
  });
}());
