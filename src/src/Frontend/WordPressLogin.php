<?php

declare(strict_types=1);

namespace SGFP\Frontend;

/**
 * Personaliza somente a apresentação do fluxo nativo do WordPress.
 * Autenticação, cadastro, recuperação e credenciais continuam sob controle
 * das telas e mecanismos oficiais de wp-login.php.
 */
final class WordPressLogin
{
    public function register(): void
    {
        add_action('login_enqueue_scripts', [$this, 'enqueueStyles']);
        add_filter('login_headerurl', [$this, 'headerUrl']);
        add_filter('login_headertext', [$this, 'headerText']);
        add_filter('login_body_class', [$this, 'bodyClasses']);
        add_filter('login_redirect', [$this, 'safeRedirect'], 10, 3);
    }

    public function enqueueStyles(): void
    {
        wp_enqueue_style(
            'sgfp-login',
            plugins_url('assets/app.css', SGFP_PLUGIN_FILE),
            [],
            SGFP_VERSION
        );
    }

    public function headerUrl(string $url): string
    {
        return function_exists('home_url') ? home_url('/') : $url;
    }

    public function headerText(string $text): string
    {
        return 'SGFP — Gestão Financeira';
    }

    /** @param list<string> $classes */
    public function bodyClasses(array $classes): array
    {
        $classes[] = 'sgfp-login-screen';

        return $classes;
    }

    /**
     * O WordPress valida o destino com wp_validate_redirect; destinos
     * externos ou inválidos retornam para a área inicial do portal.
     */
    public function safeRedirect(string $redirectTo, string $requestedRedirectTo, \WP_User|\WP_Error $user): string
    {
        $fallback = function_exists('home_url') ? home_url('/') : '/';

        if (function_exists('get_page_by_path')) {
            $page = get_page_by_path('sgfp');
            if ($page instanceof \WP_Post && function_exists('get_permalink')) {
                $fallback = get_permalink($page);
            }
        }

        if ($user instanceof \WP_Error) {
            return $fallback;
        }

        return function_exists('wp_validate_redirect')
            ? wp_validate_redirect($redirectTo, $fallback)
            : $fallback;
    }
}
