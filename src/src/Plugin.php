<?php

declare(strict_types=1);

namespace SGFP;

use SGFP\Application\Services\ProvisionUserService;
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
        add_action('init', [$this, 'registerCapabilities']);
        add_action('rest_api_init', [new Routes(), 'register']);

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
        $role = get_role('administrator');
        if ($role !== null && !$role->has_cap('use_sgfp')) {
            $role->add_cap('use_sgfp');
        }
    }

    public function provisionUser(int $userId): void
    {
        try {
            $service = new ProvisionUserService(
                new WpAccountRepository(),
                new WpCategoryRepository(),
            );

            $service->execute($userId);
        } catch (\Throwable $e) {
            error_log(sprintf(
                '[SGFP] Falha ao provisionar usuário %d: %s',
                $userId,
                $e->getMessage()
            ));
        }
    }

    public function provisionUserOnLogin(string $userLogin, \WP_User $user): void
    {
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
    }
}
