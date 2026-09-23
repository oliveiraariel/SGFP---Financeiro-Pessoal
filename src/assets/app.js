(function () {
  'use strict';
  const app = document.querySelector('[data-sgfp-app]');
  if (!app) return;
  if (app.dataset.sgfpAccessState !== 'authorized') return;
  const cfg = window.sgfpConfig || {}, base = (cfg.restUrl || '').replace(/\/$/, '');
  const content = app.querySelector('[data-content]'), title = app.querySelector('[data-title]');
  const month = app.querySelector('[data-month]');
  const names = { overview: 'Visão Geral', entries: 'Lançamentos', commitments: 'Compromissos', recurrences: 'Recorrências', account: 'Minha Conta', categories: 'Categorias', settings: 'Configurações' };
  let view = 'overview', restoring = null, locked = false, accountEditing = false, loadSequence = 0;
  const accountSetupKey = (id) => 'sgfp-account-initial-balance:' + String(id);
  const initialBalancePromptKey = (id) => 'sgfp-initial-balance-prompt:' + String(id);
  const esc = (x) => String(x == null ? '' : x).replace(/[&<>"']/g, (c) => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' }[c]));
  const list = (x) => Array.isArray(x) ? x : (Array.isArray(x && x.items) ? x.items : []);
  const money = (x) => {
    if (x == null || String(x).trim() === '') return '—';
    const raw = String(x).trim().replace(/\s/g, '').replace(/^R\$\s*/i, '');
    const value = Number(raw.includes(',') ? raw.replace(/\./g, '').replace(',', '.') : raw);
    return Number.isFinite(value) ? 'R$ ' + value.toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) : '—';
  };
  const dashboardMetric = (label, value, tone, note) => '<article class="sgfp-metric sgfp-metric-' + tone + '"><span>' + esc(label) + '</span><strong>' + money(value) + '</strong><small>' + esc(note) + '</small></article>';
  const monthLabel = (value) => { const parts = String(value || '').split('-'); return parts.length === 2 ? parts[1] + '/' + parts[0] : value; };
  const monthLongLabel = (value) => { const parts = String(value || '').split('-'); if (parts.length !== 2) return value; return new Date(Number(parts[0]), Number(parts[1]) - 1, 1).toLocaleDateString('pt-BR', { month: 'long', year: 'numeric' }); };
  const dateLabel = (value) => { const match = String(value || '').match(/^(\d{4})-(\d{2})-(\d{2})$/); return match ? match[3] + '/' + match[2] + '/' + match[1] : value; };
  const commitmentKey = (item) => item && item.recurrence_id ? 'recurrence:' + String(item.recurrence_id) : 'commitment:' + String(item && item.id);
  const commitmentActionPath = (item) => '/commitments/' + encodeURIComponent(item.id) + (item.status === 'EFETIVADO' ? '/undo-effectuation' : '/effectuation');
  const recurrenceActionPath = (item, selectedMonth) => '/recurrences/' + encodeURIComponent(item.recurrence_id) + '/occurrences/' + encodeURIComponent(selectedMonth) + (item.status === 'EFETIVADO' ? '/undo-effectuation' : '/effectuation');
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
    const ordered = uniqueCommitments(items).sort(compareAgendaItems);
    const monthName = monthLongLabel(selectedMonth);
    const rows = ordered.map((item) => {
      const category = item.category_id && categories[String(item.category_id)] ? categories[String(item.category_id)] : 'Sem categoria';
      const effective = item.status === 'EFETIVADO';
      const date = item.commitment_date;
      const description = item.description || item.name || '—';
      return '<tr><td>' + esc(dateLabel(date)) + '</td><td>' + esc(description) + '</td><td>' + esc(category) + '</td><td class="sgfp-value-column">' + money(item.amount) + '</td><td><span class="sgfp-status-badge sgfp-status-' + (effective ? 'done' : 'pending') + '">' + esc(item.status || 'PENDENTE') + '</span></td><td class="sgfp-agenda-actions" aria-label="Ações de ' + esc(description) + '">' + act(effective ? 'Desefetivar' : 'Efetivar', item.actionPath) + edit('Editar', item.id, 'commitment', effective) + (!effective ? act('Excluir', '/commitments/' + encodeURIComponent(item.id), 'DELETE') : '<span class="sgfp-muted">Desfaça para editar</span>') + '</td></tr>';
    });
    const body = ordered.length ? table('Agenda mensal de ' + monthName, ['Vencimento', 'Descrição', 'Categoria', 'Valor', 'Status', 'Ações'], rows) : '<p class="sgfp-agenda-empty" role="status">Nenhum compromisso previsto para este mês.</p>';
    return panel('Agenda de compromissos', '<div class="sgfp-agenda-heading"><div><p class="sgfp-eyebrow">AGENDA MENSAL</p><h3 class="sgfp-agenda-month" tabindex="-1">' + esc(monthName) + '</h3><p class="sgfp-intro">Compromissos previstos, incluindo recorrências ativas.</p></div><button type="button" class="sgfp-primary-action" data-view="commitments" data-open-commitment>Adicionar Compromisso</button></div><div data-agenda aria-live="polite">' + body + '</div>');
  };
  const dashboard = (d) => {
    const hasActivity = [d.realized_inflows, d.realized_outflows, d.expected_inflows, d.expected_outflows].some((value) => value != null && String(value) !== '0.00');
    return '<div class="sgfp-dashboard" aria-label="Resumo financeiro mensal">' +
      '<section class="sgfp-balance-hero" aria-labelledby="sgfp-balance-title"><div><p class="sgfp-eyebrow">MINHA CONTA · MÊS SELECIONADO</p><h2 id="sgfp-balance-title">Saldo atual</h2><p class="sgfp-dashboard-note">Derivado dos lançamentos financeiros ativos.</p></div><strong>' + money(d.current_balance) + '</strong></section>' +
      (!hasActivity ? '<section class="sgfp-empty-state" aria-live="polite"><h2>Nenhuma movimentação neste mês</h2><p>Crie um compromisso para começar a acompanhar suas entradas e saídas.</p><button type="button" class="sgfp-primary-action" data-view="commitments">Criar compromisso</button></section>' : '') +
      '<section class="sgfp-dashboard-section" aria-labelledby="sgfp-expected-title"><div class="sgfp-panel-heading"><div><p class="sgfp-eyebrow">PREVISTO</p><h2 id="sgfp-expected-title">Compromissos do mês</h2></div><span class="sgfp-status-label">Ainda pendentes</span></div><div class="sgfp-metric-grid">' +
        dashboardMetric('Mês anterior', d.opening_balance, 'neutral', 'Saldo final do último dia') + dashboardMetric('Entradas previstas', d.expected_inflows, 'positive', 'A receber') + dashboardMetric('Saídas previstas', d.expected_outflows, 'negative', 'A pagar') + dashboardMetric('Saldo previsto', d.expected_closing_balance, 'accent', 'Ao final do mês') +
      '</div></section>' +
      '<section class="sgfp-dashboard-section" aria-labelledby="sgfp-realized-title"><div class="sgfp-panel-heading"><div><p class="sgfp-eyebrow">REALIZADO</p><h2 id="sgfp-realized-title">Lançamentos efetivados do mês</h2></div><span class="sgfp-status-label">Movimentações realizadas</span></div><div class="sgfp-metric-grid">' +
        dashboardMetric('Entradas realizadas', d.realized_inflows, 'positive', 'Compromissos efetivados') + dashboardMetric('Saídas realizadas', d.realized_outflows, 'negative', 'Compromissos efetivados') + dashboardMetric('Saldo final', d.current_balance, 'accent', 'Mês anterior + lançamentos realizados') +
      '</div></section>' +
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
    return message ? (code ? message + ' (' + code + ')' : message) : 'Operação não concluída.';
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
    const d = await r.json().catch(() => ({})); if (!r.ok) throw Error(apiError(d)); return d;
  }
  async function promptInitialBalanceForNewProfile() {
    try {
      const account = await api('/account');
      if (!account || account.initial_balance_configured === true || !account.id) return;
      const promptKey = initialBalancePromptKey(account.id);
      if (window.localStorage.getItem(promptKey) === 'shown') return;
      window.localStorage.setItem(promptKey, 'shown');
      content.insertAdjacentHTML('afterbegin', '<section class="sgfp-initial-balance-prompt" role="status"><div><p class="sgfp-eyebrow">PRIMEIRO ACESSO</p><h2>Defina seu saldo inicial</h2><p>Informe o saldo atual da conta e confirme o mês/ano atual para começar o controle financeiro.</p></div><button type="button" class="sgfp-primary-action" data-view="account" data-initial-balance-prompt>Definir saldo inicial</button></section>');
    } catch (_) {
      // The normal page load already renders a recoverable REST error.
    }
  }
  const panel = (h, body) => '<section class="sgfp-panel"><h2>' + h + '</h2>' + body + '</section>';
  const field = (l, n, t = 'text', x = '') => '<label class="sgfp-field"><span>' + l + '</span><input name="' + n + '" type="' + t + '" ' + x + '></label>';
  const form = (n, fields, button = 'Salvar') => '<form class="sgfp-form" data-form="' + n + '">' + fields.join('') + '<button class="sgfp-primary-action" type="submit">' + button + '</button><p class="sgfp-form-status" data-form-status role="status"></p></form>';
  const act = (l, p, m = 'POST') => '<button type="button" class="sgfp-text-action" data-action="' + esc(p) + '" data-method="' + m + '">' + l + '</button>';
  const edit = (l, id, kind, disabled = false) => '<button type="button" class="sgfp-text-action" data-edit="' + esc(id) + '" data-edit-kind="' + kind + '"' + (disabled ? ' disabled aria-disabled="true" title="Desfaça a efetivação para editar"' : '') + '>' + l + '</button>';
  const table = (caption, headers, rows) => '<div class="sgfp-table-wrap"><table><caption class="sgfp-sr-only">' + esc(caption) + '</caption><thead><tr>' + headers.map((h) => '<th scope="col">' + esc(h) + '</th>').join('') + '</tr></thead><tbody>' + (rows.length ? rows.join('') : '<tr><td colspan="' + headers.length + '" class="sgfp-empty">Nenhum registro encontrado.</td></tr>') + '</tbody></table></div>';
  const hydrateCommitmentForm = (target, item) => {
    if (!target || !item) return;
    const values = { name: item.name || '', amount: item.amount || '', nature: item.nature || 'SAIDA', category_id: item.category_id == null ? '' : String(item.category_id), commitment_date: apiToDate(item.commitment_date) };
    Object.keys(values).forEach((name) => { const input = target.querySelector('[name="' + name + '"]'); if (input) input.value = values[name]; });
    target.dataset.editId = item.id;
    const status = target.querySelector('[data-form-status]'); if (status) status.textContent = 'Editando registro.';
    const submitButton = target.querySelector('button[type="submit"]'); if (submitButton) submitButton.textContent = 'Atualizar';
  };
  const setView = (next) => { view = next; app.querySelectorAll('[data-view]').forEach((button) => { const active = button.dataset.view === next; button.classList.toggle('is-active', active); if (active) button.setAttribute('aria-current', 'page'); else button.removeAttribute('aria-current'); }); };
  async function load(next = view) {
    const sequence = ++loadSequence;
    setView(next); title.textContent = names[view]; content.setAttribute('aria-busy', 'true'); content.innerHTML = '<p class="sgfp-loading" role="status">Carregando…</p>';
    try {
      const mo = monthParam();
      if (view === 'overview') { const [d, commitments, categories] = await Promise.all([api('/dashboard?month=' + encodeURIComponent(mo)), api('/commitments'), api('/categories')]); const categoryMap = Object.fromEntries(list(categories).map((x) => [String(x.id), x.name])); const selectedMonth = mo.slice(0, 7); const direct = list(commitments).filter((x) => commitmentMonth(x) === selectedMonth); const recurring = list(commitments).filter((x) => x.recurrence_id && commitmentMonth(x) !== selectedMonth); const occurrences = await Promise.all(recurring.map((x) => api('/recurrences/' + encodeURIComponent(x.recurrence_id) + '/occurrences/' + encodeURIComponent(mo)))); if (sequence !== loadSequence) return; const items = uniqueCommitments(direct.concat(occurrences)).map((x) => ({ ...x, actionPath: actionPathFor(x, mo) })); content.innerHTML = dashboard(d) + agenda(items, categoryMap, selectedMonth); return; }
      if (view === 'entries') {
        const [d, commitments, categories] = await Promise.all([api('/movements?month=' + encodeURIComponent(mo)), api('/commitments'), api('/categories')]);
        if (sequence !== loadSequence) return;
        const commitmentMap = Object.fromEntries(list(commitments).map((x) => [String(x.id), x]));
        const categoryMap = Object.fromEntries(list(categories).map((x) => [String(x.id), x.name]));
        const rows = list(d).map((x) => {
          const commitment = x.commitment_id == null ? null : commitmentMap[String(x.commitment_id)];
          const category = commitment && commitment.category_id != null && categoryMap[String(commitment.category_id)] ? categoryMap[String(commitment.category_id)] : 'Sem categoria';
          const status = (commitment && commitment.status) || x.status || 'EFETIVADO';
          const statusClass = status === 'EFETIVADO' ? 'done' : 'pending';
          return '<tr><td>' + esc(x.name) + '</td><td>' + esc(x.description || '—') + '</td><td>' + esc(category) + '</td><td class="sgfp-value-column">' + money(x.amount) + '</td><td>' + esc(x.effect || x.effect_type) + '</td><td><span class="sgfp-status-badge sgfp-status-' + statusClass + '">' + esc(status) + '</span></td></tr>';
        });
        content.innerHTML = panel('Lançamentos', table('Lançamentos', ['Nome', 'Descrição', 'Categoria', 'Valor', 'Tipo', 'Status'], rows));
        return;
      }
      if (view === 'commitments') { const cats = await api('/categories'); const opts = list(cats).map((x) => '<option value="' + esc(x.id) + '">' + esc(x.name) + '</option>').join(''); content.innerHTML = panel('Compromissos', '<p class="sgfp-intro">Cadastre ou edite um compromisso. A listagem e o acompanhamento ficam na Visão Geral.</p>' + form('commitment', [field('Nome', 'name', 'text', 'required'), '<label class="sgfp-field sgfp-money-field"><span>Valor</span><input class="sgfp-money-input" name="amount" type="text" inputmode="decimal" placeholder="0,00" required></label>', '<label class="sgfp-field"><span>Natureza</span><select name="nature"><option value="ENTRADA">Entrada</option><option value="SAIDA">Saída</option></select></label>', '<label class="sgfp-field"><span>Categoria</span><select name="category_id"><option value="">Sem categoria</option>' + opts + '</select></label><button type="button" class="sgfp-text-action" data-quick-category>+ Nova categoria</button>', field('Data do compromisso', 'commitment_date', 'text', 'inputmode="numeric" autocomplete="off" placeholder="DD/MM/AAAA" value="' + esc(currentDate()) + '" required'), '<label class="sgfp-field"><span>Recorrência</span><select name="recurrence_mode" data-recurrence-mode><option value="none">Sem recorrência</option><option value="finite">Por período definido</option><option value="indefinite">Indefinida</option></select></label>', '<div class="sgfp-recurrence-fields" data-recurrence-fields hidden><p class="sgfp-recurrence-note">A recorrência começa no mês do compromisso.</p><label class="sgfp-field" data-recurrence-count><span>Quantidade de meses</span><input name="recurrence_months_count" type="number" min="1" inputmode="numeric" placeholder="Ex.: 12"></label></div>'])); return; }
      if (view === 'recurrences') { const d = list(await api('/commitments?month=' + encodeURIComponent(mo))).filter((x) => x.recurrence_id); const rows = d.map((x) => '<tr><td>' + esc(x.name) + '</td><td class="sgfp-value-column">' + money(x.amount) + '</td><td>' + act('Abrir ocorrência', '/recurrences/' + encodeURIComponent(x.recurrence_id) + '/occurrences/' + encodeURIComponent(mo), 'GET') + '</td></tr>'); content.innerHTML = panel('Recorrências', '<div data-occurrence></div>' + (rows.length ? table('Recorrências', ['Descrição', 'Valor', 'Ação'], rows) : '<p class="sgfp-empty">Nenhuma recorrência encontrada.</p>')); return; }
      if (view === 'categories') { const d = list(await api('/categories')); content.innerHTML = panel('Categorias', form('category', [field('Nome', 'name')]) + d.map((x) => '<p data-category-id="' + esc(x.id) + '">' + esc(x.name) + ' ' + edit('Editar', x.id, 'category') + ' ' + act('Excluir', '/categories/' + encodeURIComponent(x.id), 'DELETE') + '</p>').join('')); return; }
      if (view === 'account') { const account = await api('/account'); const setup = account.initial_balance_configured === true; const disabled = setup && !accountEditing ? 'disabled' : ''; const initialBalanceRequired = setup ? '' : 'required'; const initialMonth = setup ? disabled : 'value="' + esc(currentMonth()) + '" required readonly'; content.innerHTML = panel(esc(account.name || 'Minha Conta'), '<div class="sgfp-card"><span>Saldo derivado dos lançamentos ativos</span><strong>' + money(account.balance) + '</strong></div>' + form('account', [field('Nome da conta', 'name', 'text', 'value="' + esc(account.name) + '"'), field('Saldo inicial', 'amount', 'text', 'inputmode="decimal" ' + initialBalanceRequired + ' ' + disabled), field('Mês do saldo inicial (mês atual)', 'effective_month', 'month', initialMonth)], 'Salvar conta') + (setup && !accountEditing ? '<button type="button" class="sgfp-secondary-action" data-edit-account>Editar saldo inicial</button>' : '') + (setup ? '<p class="sgfp-form-status">Saldo inicial configurado. Edite para alterar.</p>' : '<p class="sgfp-form-status">Defina o saldo inicial para o mês atual.</p>')); return; }
      if (view === 'settings') { const theme = app.dataset.theme || 'dark'; content.innerHTML = panel('Configurações', '<label class="sgfp-setting"><span>Tema</span><select data-theme><option value="dark">Escuro</option><option value="light">Claro</option></select></label>' + form('backup', [], 'Baixar backup ZIP') + form('restore', [field('Arquivo ZIP', 'backup', 'file', 'accept=".zip"')], 'Validar restauração') + '<div data-restore-confirm></div>' + '<div class="sgfp-danger-zone" data-danger="reset"><h3>Resetar perfil financeiro</h3><p>Remove os dados financeiros e mantém seu login.</p><button class="sgfp-secondary-action" type="button" data-start-danger="reset">Iniciar reset</button><div data-danger-stage></div></div>' + '<div class="sgfp-danger-zone" data-danger="delete"><h3>Excluir conta de acesso</h3><p>Remove os dados SGFP e sua conta WordPress.</p><button class="sgfp-secondary-action" type="button" data-start-danger="delete">Iniciar exclusão</button><div data-danger-stage></div></div>'); content.querySelector('[data-theme]').value = theme; }
    } catch (e) { content.innerHTML = panel('Não foi possível carregar', '<p class="sgfp-error-message">' + esc(e.message) + '</p><button data-retry="' + view + '">Tentar novamente</button>'); } finally { content.setAttribute('aria-busy', 'false'); }
  }
  async function submit(ev) {
    ev.preventDefault(); if (locked) return; locked = true; const f = ev.target, s = f.querySelector('[data-form-status]'), d = Object.fromEntries(new FormData(f)), k = f.dataset.form; s.textContent = 'Processando…';
    try { let path, method = 'POST', body, headers = {};
      if (k === 'account') { if ((d.amount && !d.effective_month) || (!d.amount && d.effective_month)) throw Error('Informe valor e mês do saldo inicial.'); if (f.querySelector('[name="effective_month"]')?.required && d.effective_month !== currentMonth()) throw Error('O saldo inicial do primeiro acesso deve usar o mês atual.'); const updated = await api('/account', { method: 'PATCH', body: JSON.stringify({ name: d.name }), headers: { 'Content-Type': 'application/json' } }); if (d.amount) { if (!/^\d{4}-(0[1-9]|1[0-2])$/.test(d.effective_month)) throw Error('Informe o mês do saldo inicial.'); await api('/account/initial-balance', { method: 'POST', body: JSON.stringify({ amount: d.amount, effective_month: d.effective_month + '-01' }), headers: { 'Content-Type': 'application/json' } }); window.localStorage.setItem(accountSetupKey(updated.id), 'configured'); } accountEditing = false; title.textContent = updated.name || d.name || names.account; }
      else if (k === 'category') { path = f.dataset.editId ? '/categories/' + encodeURIComponent(f.dataset.editId) : '/categories'; method = f.dataset.editId ? 'PATCH' : 'POST'; body = JSON.stringify({ name: d.name }); headers['Content-Type'] = 'application/json'; }
      else if (k === 'commitment') { path = f.dataset.editId ? '/commitments/' + encodeURIComponent(f.dataset.editId) : '/commitments'; method = f.dataset.editId ? 'PATCH' : 'POST'; const amount = decimal(d.amount), commitmentDate = dateToApi(d.commitment_date); body = f.dataset.editId ? JSON.stringify({ name: d.name, amount, category_id: d.category_id ? Number(d.category_id) : null, commitment_date: commitmentDate }) : JSON.stringify({ name: d.name, amount, nature: String(d.nature).toUpperCase(), category_id: d.category_id ? Number(d.category_id) : null, commitment_date: commitmentDate, recurrence_months_count: d.recurrence_mode === 'finite' && d.recurrence_months_count ? Number(d.recurrence_months_count) : null, recurrence_enabled: d.recurrence_mode !== 'none' }); headers['Content-Type'] = 'application/json'; }
      else if (k === 'backup') { const r = await fetch(base + '/backups', { method: 'POST', credentials: 'same-origin', headers: { 'X-WP-Nonce': cfg.nonce || '' } }); if (!r.ok) { const error = await r.json().catch(() => ({})); throw Error(apiError(error)); } const disposition = r.headers.get('Content-Disposition') || ''; const filenameMatch = disposition.match(/filename="?([^";]+)"?/i); const a = document.createElement('a'); const objectUrl = URL.createObjectURL(await r.blob()); a.href = objectUrl; a.download = filenameMatch ? filenameMatch[1] : 'sgfp-backup.zip'; a.hidden = true; document.body.appendChild(a); a.click(); a.remove(); window.setTimeout(() => URL.revokeObjectURL(objectUrl), 0); s.textContent = 'Backup baixado.'; return; }
      else if (k === 'restore') { const result = await api('/restore-validations', { method: 'POST', body: new FormData(f) }); restoring = result.token; s.textContent = 'Arquivo validado; confirme abaixo.'; f.reset(); content.querySelector('[data-restore-confirm]').innerHTML = '<button data-confirm-restore>Confirmar restauração</button>'; return; }
      else if (k === 'reset') { const phrase = 'RESETAR PERFIL'; if (d.phrase !== phrase) throw Error('Digite exatamente ' + phrase + '.'); path = '/profile-reset'; body = JSON.stringify({ token: d.token, phrase: phrase }); headers['Content-Type'] = 'application/json'; }
      else if (k === 'delete') { const phrase = 'EXCLUIR CONTA'; if (d.phrase !== phrase) throw Error('Digite exatamente ' + phrase + '.'); path = '/account-access'; method = 'DELETE'; body = JSON.stringify({ phrase: phrase, confirmation: true }); headers['Content-Type'] = 'application/json'; }
      if (path) await api(path, { method, body, headers }); s.textContent = 'Operação concluída.'; f.reset(); await load();
    } catch (e) { s.textContent = e.message; } finally { locked = false; }
  }
  app.addEventListener('input', (ev) => { if (ev.target.matches('[name="commitment_date"]')) maskDateInput(ev.target); });
  app.addEventListener('submit', submit); app.addEventListener('click', async (ev) => { const n = ev.target.closest('[data-view]'); if (n) load(n.dataset.view); const retry = ev.target.closest('[data-retry]'); if (retry) load(retry.dataset.retry); const quick = ev.target.closest('[data-quick-category]'); if (quick) { const name = window.prompt('Nome da nova categoria:'); if (name && name.trim()) { try { const created = await api('/categories', { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify({ name: name.trim() }) }); const select = content.querySelector('[name="category_id"]'); if (select) { select.insertAdjacentHTML('beforeend', '<option value="' + esc(created.id) + '">' + esc(created.name) + '</option>'); select.value = created.id; } } catch (e) { content.insertAdjacentHTML('afterbegin', '<p class="sgfp-error-message">' + esc(e.message) + '</p>'); } } return; } const editButton = ev.target.closest('[data-edit]'); if (editButton && editButton.dataset.editKind === 'category') { try { const item = list(await api('/categories')).find((x) => String(x.id) === String(editButton.dataset.edit)); if (!item) throw Error('Registro não encontrado.'); const target = content.querySelector('[data-form="category"]'); if (!target) throw Error('Formulário indisponível.'); target.dataset.editId = item.id; target.querySelector('[name="name"]').value = item.name; target.querySelector('[data-form-status]').textContent = 'Editando registro.'; target.querySelector('button[type="submit"]').textContent = 'Atualizar'; } catch (e) { content.insertAdjacentHTML('afterbegin', '<p class="sgfp-error-message">' + esc(e.message) + '</p>'); } return; } const action = ev.target.closest('[data-action]'); if (action) { action.disabled = true; try { const result = await api(action.dataset.action, { method: action.dataset.method }); if (action.dataset.method === 'GET' && view === 'recurrences') { const occurrence = content.querySelector('[data-occurrence]'); occurrence.innerHTML = '<div class="sgfp-card"><strong>' + esc(result.name) + '</strong><span>' + money(result.amount) + ' · ' + esc(result.status) + '</span><div>' + (result.status === 'PENDENTE' ? act('Efetivar ocorrência', action.dataset.action + '/effectuation') : act('Desfazer ocorrência', action.dataset.action + '/undo-effectuation')) + '</div></div>'; } else await load(); } catch (e) { action.disabled = false; action.insertAdjacentHTML('afterend', '<span class="sgfp-error-message" role="alert">' + esc(e.message) + '</span>'); } } if (ev.target.closest('[data-confirm-restore]') && restoring) api('/restorations', { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify({ token: restoring, confirmation: true }) }).then(() => load('settings')).catch((e) => { ev.target.textContent = e.message; }); });
  app.addEventListener('change', (ev) => { if (ev.target.matches('[data-month]')) load(); if (ev.target.matches('[data-theme]')) { const next = applyTheme(ev.target.value); api('/preferences/theme', { method: 'PUT', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify({ theme: next }) }).then((result) => { const saved = themeFromResponse(result) || next; applyTheme(saved); window.localStorage.setItem(themeStorageKey, saved); }).catch((e) => { const previous = applyTheme(window.localStorage.getItem(themeStorageKey) || 'dark'); ev.target.value = previous; const status = content.querySelector('[data-form-status]') || content.querySelector('.sgfp-error-message'); if (status) status.textContent = 'Não foi possível salvar o tema: ' + e.message; }); } if (ev.target.matches('[data-recurrence-mode]')) { const fields = content.querySelector('[data-recurrence-fields]'); const count = content.querySelector('[data-recurrence-count]'); if (fields) fields.hidden = ev.target.value === 'none'; if (count) count.hidden = ev.target.value !== 'finite'; } });
  app.addEventListener('click', (ev) => { const control = ev.target.closest('[data-shift-month]'); if (control) shiftMonth(Number(control.dataset.shiftMonth)); });
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
