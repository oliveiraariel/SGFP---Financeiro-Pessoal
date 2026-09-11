<?php

declare(strict_types=1);

namespace SGFP;

use SGFP\Infrastructure\WordPress\WpUserContext;
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
    }

    public function registerCapabilities(): void
    {
        $role = get_role('administrator');
        if ($role !== null && !$role->has_cap('use_sgfp')) {
            $role->add_cap('use_sgfp');
        }
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
