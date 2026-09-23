<?php

declare(strict_types=1);

namespace SGFP;

use SGFP\Application\Services\ProvisionUserService;
use SGFP\Frontend\Frontend;
use SGFP\Frontend\WordPressLogin;
use SGFP\Infrastructure\WordPress\WpAccountRepository;
use SGFP\Infrastructure\WordPress\WpCategoryRepository;
use SGFP\REST\Routes;

final class Plugin
{
    private static ?self $instance = null;

    public static function boot(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
            self::$instance->register();
        }

        return self::$instance;
    }

    private function register(): void
    {
        $frontend = new Frontend();
        (new WordPressLogin())->register();

        add_action('init', [$this, 'registerCapabilities']);
        add_action('rest_api_init', [new Routes(), 'register']);
        add_action('template_redirect', [$frontend, 'redirectAnonymousVisitor'], 0);
        add_action('wp_enqueue_scripts', [$frontend, 'enqueue']);
        add_shortcode('sgfp_app', [$frontend, 'render']);

        /*
         * Provisionamento principal: imediatamente após o WordPress
         * criar a identidade do usuário.
         */
        add_action('user_register', [$this, 'provisionUser']);

        /*
         * Fallback idempotente: se o provisionamento inicial falhar
         * por indisponibilidade transitória, o próximo login tenta
         * recompor o estado mínimo obrigatório.
         */
        add_action('wp_login', [$this, 'provisionUserOnLogin'], 10, 2);
    }

    public function registerCapabilities(): void
    {
        foreach (['subscriber', 'administrator', 'contributor', 'author', 'editor'] as $roleName) {
            $role = get_role($roleName);
            if ($role !== null && !$role->has_cap('use_sgfp')) {
                $role->add_cap('use_sgfp');
            }
        }
    }

    public function provisionUser(int $userId): void
    {
        if ($userId <= 0) {
            return;
        }

        $lockKey = 'sgfp_provision_user_' . $userId;
        $lockAcquired = !function_exists('wp_cache_add') || wp_cache_add($lockKey, '1', 'sgfp', 30);

        if (!$lockAcquired) {
            return;
        }

        try {
            $service = new ProvisionUserService(
                new WpAccountRepository(),
                new WpCategoryRepository(),
            );

            $service->execute($userId);

            $user = function_exists('get_user_by') ? get_user_by('id', $userId) : false;
            if ($user instanceof \WP_User && !$user->has_cap('use_sgfp')) {
                $user->add_cap('use_sgfp');
            }

            update_user_meta($userId, '_sgfp_provisioned', '1');
        } catch (\Throwable $e) {
            error_log(sprintf('[SGFP] Falha ao provisionar usuário %d.', $userId));
        } finally {
            if (function_exists('wp_cache_delete')) {
                wp_cache_delete($lockKey, 'sgfp');
            }
        }
    }

    public function provisionUserOnLogin(string $userLogin, \WP_User $user): void
    {
        // Existing users may carry the pre-V1 capability. The migration is
        // completed by provisionUser only after the account and categories
        // have been successfully ensured.
        $this->provisionUser((int) $user->ID);
    }

    public static function activate(): void
    {
        self::guardEnvironment();
        Activation::run();
    }

    public static function deactivate(): void
    {
        Deactivation::run();
    }

    private static function guardEnvironment(): void
    {
        if (version_compare(PHP_VERSION, '8.1', '<')) {
            wp_die(esc_html__('SGFP requer PHP 8.1 ou superior.', 'sgfp'));
        }

        global $wp_version;
        if (version_compare($wp_version, '6.4', '<')) {
            wp_die(esc_html__('SGFP requer WordPress 6.4 ou superior.', 'sgfp'));
        }

        if (!extension_loaded('sodium')) {
            wp_die(esc_html__('SGFP requer a extensão PHP sodium.', 'sgfp'));
        }

        if (!class_exists(\ZipArchive::class)) {
            wp_die(esc_html__('SGFP requer a extensão PHP zip.', 'sgfp'));
        }
    }
}
