<?php

declare(strict_types=1);

namespace SGFP\Frontend;

final class Frontend
{
    public function __construct(private readonly ?\Closure $redirector = null) {}

    public function redirectAnonymousVisitor(): void
    {
        if ($this->isLoggedIn() || !$this->isSgfpFrontendPage()) {
            return;
        }

        $loginUrl = function_exists('wp_login_url')
            ? wp_login_url($this->currentPageUrl())
            : '';

        if ($loginUrl === '') {
            return;
        }

        if ($this->redirector !== null) {
            ($this->redirector)($loginUrl);
            return;
        }

        wp_safe_redirect($loginUrl);
        exit;
    }

    public function enqueue(): void
    {
        if (!$this->isAuthorized()) {
            return;
        }
        if (!is_singular() || !function_exists('has_shortcode')) {
            return;
        }

        global $post;
        if (!$post instanceof \WP_Post || !has_shortcode((string) $post->post_content, 'sgfp_app')) {
            return;
        }

        wp_enqueue_style('sgfp-app', plugins_url('assets/app.css', SGFP_PLUGIN_FILE), [], SGFP_VERSION);
        wp_enqueue_script('sgfp-app', plugins_url('assets/app.js', SGFP_PLUGIN_FILE), [], SGFP_VERSION, true);
        wp_localize_script('sgfp-app', 'sgfpConfig', [
            'restUrl' => esc_url_raw(rest_url('sgfp/v1')),
            'nonce' => wp_create_nonce('wp_rest'),
            'month' => wp_date('Y-m'),
        ]);
    }

    public function render(): string
    {
        if (!$this->isLoggedIn()) {
            return '';
        }

        if (!$this->isAuthorized()) {
            return '<section class="sgfp-access-unavailable" data-sgfp-access-state="unauthorized-unprovisioned" aria-labelledby="sgfp-access-title"><h2 id="sgfp-access-title">Gestor financeiro indisponível</h2><p>Sua conta ainda não está autorizada ou provisionada para acessar o SGFP. Entre novamente após a conclusão do cadastro.</p></section>';
        }

        ob_start(); ?>
        <main class="sgfp-app" data-sgfp-app data-sgfp-shell data-sgfp-access-state="authorized">
            <aside class="sgfp-sidebar" aria-label="Navegação principal">
                <div class="sgfp-brand"><span aria-hidden="true">▥</span><strong>SGFP</strong><small>Gestão Financeira</small></div>
                <nav class="sgfp-nav" aria-label="Seções da aplicação">
                    <button class="is-active" type="button" data-view="overview" aria-current="page">Visão Geral</button>
                    <button type="button" data-view="entries">Lançamentos</button>
                    <button type="button" data-view="commitments">Compromissos</button>
                    <button type="button" data-view="recurrences">Recorrências</button>
                    <button type="button" data-view="account">Minha Conta</button>
                    <button type="button" data-view="categories">Categorias</button>
                    <button type="button" data-view="settings">Configurações</button>
                </nav>
            </aside>
            <section class="sgfp-main" aria-labelledby="sgfp-page-title">
                <header class="sgfp-header">
                    <div><p class="sgfp-eyebrow">GESTOR FINANCEIRO</p><h1 id="sgfp-page-title" data-title>Visão Geral</h1></div>
                    <div class="sgfp-header-actions">
                        <div class="sgfp-month-navigation" data-month-navigation>
                            <label class="sgfp-month-label" for="sgfp-month">Mês</label>
                            <input id="sgfp-month" class="sgfp-month" type="month" data-month value="<?php echo esc_attr(wp_date('Y-m')); ?>">
                        </div>
                    </div>
                </header>
                <div class="sgfp-content" data-content aria-live="polite" aria-busy="true"><p class="sgfp-loading" role="status">Carregando resumo…</p><noscript><p>Ative JavaScript para utilizar o gestor financeiro.</p></noscript></div>
            </section>
        </main>
        <?php return (string) ob_get_clean();
    }

    private function isLoggedIn(): bool
    {
        return function_exists('is_user_logged_in') && is_user_logged_in();
    }

    private function isAuthorized(): bool
    {
        if (!$this->isLoggedIn()) {
            return false;
        }

        $user = function_exists('wp_get_current_user') ? wp_get_current_user() : null;
        if (!$user instanceof \WP_User || !$user->exists()) {
            return false;
        }

        $hasCapability = function_exists('current_user_can')
            && current_user_can('use_sgfp');
        $provisioned = function_exists('get_user_meta')
            && get_user_meta((int) $user->ID, '_sgfp_provisioned', true) === '1';

        return $hasCapability && $provisioned;
    }

    private function isSgfpFrontendPage(): bool
    {
        if ((function_exists('is_admin') && is_admin())
            || (function_exists('wp_doing_ajax') && wp_doing_ajax())
            || (function_exists('wp_doing_cron') && wp_doing_cron())
            || (function_exists('wp_is_json_request') && wp_is_json_request())
            || (defined('REST_REQUEST') && REST_REQUEST)
            || !function_exists('is_singular')
            || !is_singular()
            || !function_exists('has_shortcode')) {
            return false;
        }

        global $post;

        return $post instanceof \WP_Post
            && has_shortcode((string) $post->post_content, 'sgfp_app');
    }

    private function currentPageUrl(): string
    {
        $url = function_exists('get_permalink') ? get_permalink() : '';

        return is_string($url) && $url !== '' ? $url : (function_exists('home_url') ? home_url('/') : '/');
    }

}
