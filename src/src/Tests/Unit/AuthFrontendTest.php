<?php

declare(strict_types=1);

namespace {
    if (!class_exists('WP_User')) {
        class WP_User
        {
            public function __construct(public int $ID = 0, public bool $valid = true, public bool $canUse = false) {}

            public function exists(): bool { return $this->valid; }
            public function has_cap(string $capability): bool { return $capability === 'use_sgfp' && $this->canUse; }
        }
    }

    if (!class_exists('WP_Error')) {
        class WP_Error {}
    }

    if (!class_exists('WP_Post')) {
        class WP_Post
        {
            public function __construct(public string $post_content = '') {}
        }
    }

    if (!function_exists('is_user_logged_in')) {
        function is_user_logged_in(): bool { return $GLOBALS['sgfp_test_logged_in'] ?? false; }
        function get_current_user_id(): int { return $GLOBALS['sgfp_test_user_id'] ?? 0; }
        function wp_get_current_user(): WP_User { return $GLOBALS['sgfp_test_user'] ?? new WP_User(0, false); }
        function current_user_can(string $capability): bool { return $GLOBALS['sgfp_test_can_use'] ?? false; }
        function get_user_meta(int $userId, string $key, bool $single = false): string { return $GLOBALS['sgfp_test_provisioned'] ?? ''; }
        function get_option(string $key, mixed $default = false): mixed { return $GLOBALS['sgfp_test_registration'] ?? $default; }
        function get_permalink(): string { return 'https://sgfp.test/gestor'; }
        function home_url(string $path = '/'): string { return 'https://sgfp.test' . $path; }
        function wp_login_url(string $redirect = ''): string { return 'https://sgfp.test/wp-login.php?redirect_to=' . rawurlencode($redirect); }
        function wp_registration_url(): string { return 'https://sgfp.test/wp-login.php?action=register'; }
        function wp_lostpassword_url(string $redirect = ''): string { return 'https://sgfp.test/wp-login.php?action=lostpassword&redirect_to=' . rawurlencode($redirect); }
        function add_query_arg(string $key, string $value, string $url): string { return $url . '&' . $key . '=' . rawurlencode($value); }
        function esc_url(string $url): string { return $url; }
        function esc_attr(string $value): string { return htmlspecialchars($value, ENT_QUOTES, 'UTF-8'); }
        function wp_date(string $format): string { return '2026-09'; }
        function is_singular(): bool { return $GLOBALS['sgfp_test_singular'] ?? true; }
        function has_shortcode(string $content, string $shortcode): bool { return $GLOBALS['sgfp_test_has_shortcode'] ?? true; }
        function is_admin(): bool { return $GLOBALS['sgfp_test_is_admin'] ?? false; }
        function wp_doing_ajax(): bool { return $GLOBALS['sgfp_test_is_ajax'] ?? false; }
        function wp_doing_cron(): bool { return $GLOBALS['sgfp_test_is_cron'] ?? false; }
        function wp_is_json_request(): bool { return $GLOBALS['sgfp_test_is_json'] ?? false; }
        function wp_validate_redirect(string $redirect, string $fallback): string
        {
            return str_starts_with($redirect, 'https://sgfp.test/') ? $redirect : $fallback;
        }
        function get_page_by_path(string $path): WP_Post|false { return false; }
    }
}

namespace SGFP\Tests\Unit {

use PHPUnit\Framework\TestCase;
use SGFP\Frontend\Frontend;
use SGFP\Frontend\WordPressLogin;
use SGFP\Infrastructure\WordPress\WpUserContext;
use SGFP\REST\Controllers\BackupController;

final class AuthFrontendTest extends TestCase
{
    protected function setUp(): void
    {
        $GLOBALS['sgfp_test_logged_in'] = false;
        $GLOBALS['sgfp_test_user_id'] = 0;
        $GLOBALS['sgfp_test_user'] = new \WP_User(0, false);
        $GLOBALS['sgfp_test_can_use'] = false;
        $GLOBALS['sgfp_test_provisioned'] = '';
        $GLOBALS['sgfp_test_registration'] = false;
        $GLOBALS['sgfp_test_singular'] = true;
        $GLOBALS['sgfp_test_has_shortcode'] = true;
        $GLOBALS['sgfp_test_is_admin'] = false;
        $GLOBALS['sgfp_test_is_ajax'] = false;
        $GLOBALS['sgfp_test_is_cron'] = false;
        $GLOBALS['sgfp_test_is_json'] = false;
        global $post;
        $post = new \WP_Post('[sgfp_app]');
    }

    public function testAnonymousSgfpPageRedirectsDirectlyToWordPressLoginAndDoesNotRenderLanding(): void
    {
        $redirectedTo = null;
        $frontend = new Frontend(static function (string $url) use (&$redirectedTo): void {
            $redirectedTo = $url;
        });

        $frontend->redirectAnonymousVisitor();
        $html = $frontend->render();

        self::assertSame('https://sgfp.test/wp-login.php?redirect_to=https%3A%2F%2Fsgfp.test%2Fgestor', $redirectedTo);
        self::assertSame('', $html);
        self::assertStringNotContainsString('Entre para acessar o gestor financeiro', $html);
    }

    public function testAuthenticatedVisitorIsNotRedirectedAndRendersApplication(): void
    {
        $GLOBALS['sgfp_test_logged_in'] = true;
        $GLOBALS['sgfp_test_user_id'] = 7;
        $GLOBALS['sgfp_test_user'] = new \WP_User(7, true, true);
        $GLOBALS['sgfp_test_can_use'] = true;
        $GLOBALS['sgfp_test_provisioned'] = '1';
        $redirectedTo = null;
        $frontend = new Frontend(static function (string $url) use (&$redirectedTo): void {
            $redirectedTo = $url;
        });

        $frontend->redirectAnonymousVisitor();

        self::assertNull($redirectedTo);
        self::assertStringContainsString('data-sgfp-access-state="authorized"', $frontend->render());
    }

    public function testNonSgfpRequestsAreNeverRedirected(): void
    {
        foreach (['sgfp_test_is_admin', 'sgfp_test_is_ajax', 'sgfp_test_is_cron', 'sgfp_test_is_json', 'sgfp_test_singular', 'sgfp_test_has_shortcode'] as $flag) {
            $this->setUp();
            $GLOBALS[$flag] = $flag === 'sgfp_test_singular' || $flag === 'sgfp_test_has_shortcode' ? false : true;
            $redirectedTo = null;
            $frontend = new Frontend(static function (string $url) use (&$redirectedTo): void {
                $redirectedTo = $url;
            });

            $frontend->redirectAnonymousVisitor();

            self::assertNull($redirectedTo, $flag . ' must bypass the SGFP login redirect.');
        }
    }

    public function testPluginRegistersTheRedirectAtThePublicTemplateBoundary(): void
    {
        $source = file_get_contents(dirname(__DIR__, 2) . '/Plugin.php');

        self::assertIsString($source);
        self::assertStringContainsString("add_action('template_redirect', [\$frontend, 'redirectAnonymousVisitor'], 0);", $source);
        self::assertStringContainsString("add_action('rest_api_init', [new Routes(), 'register']);", $source);
    }

    public function testLoggedInButUnprovisionedStateDoesNotRenderApplication(): void
    {
        $GLOBALS['sgfp_test_logged_in'] = true;
        $GLOBALS['sgfp_test_user_id'] = 7;
        $GLOBALS['sgfp_test_user'] = new \WP_User(7, true, true);
        $GLOBALS['sgfp_test_can_use'] = true;

        $html = (new Frontend())->render();

        self::assertStringContainsString('data-sgfp-access-state="unauthorized-unprovisioned"', $html);
        self::assertStringNotContainsString('data-sgfp-app', $html);
    }

    public function testAuthorizedProvisionedStateRendersApplicationShell(): void
    {
        $GLOBALS['sgfp_test_logged_in'] = true;
        $GLOBALS['sgfp_test_user_id'] = 7;
        $GLOBALS['sgfp_test_user'] = new \WP_User(7, true, true);
        $GLOBALS['sgfp_test_can_use'] = true;
        $GLOBALS['sgfp_test_provisioned'] = '1';

        $html = (new Frontend())->render();

        self::assertStringContainsString('data-sgfp-access-state="authorized"', $html);
        self::assertStringContainsString('Minha Conta', $html);
    }

    public function testLoginRedirectRejectsExternalDestinationAndKeepsInternalDestination(): void
    {
        $login = new WordPressLogin();
        $user = new \WP_User(7, true, true);

        self::assertSame('https://sgfp.test/', $login->safeRedirect('https://evil.test/', '', $user));
        self::assertSame('https://sgfp.test/gestor', $login->safeRedirect('https://sgfp.test/gestor', '', $user));
    }

    public function testLoginRedirectSafelyHandlesWordPressErrorContext(): void
    {
        $login = new WordPressLogin();

        self::assertSame('https://sgfp.test/', $login->safeRedirect('https://evil.test/', '', new \WP_Error()));
    }

    public function testAuthenticatedUserWithoutProvisioningCannotPassCentralSgfpGuard(): void
    {
        $GLOBALS['sgfp_test_user_id'] = 7;
        $GLOBALS['sgfp_test_can_use'] = true;

        self::assertFalse(WpUserContext::canAccessSgfp());
    }

    public function testDirectRestPermissionCallbackUsesCentralGuard(): void
    {
        $GLOBALS['sgfp_test_user_id'] = 7;
        $GLOBALS['sgfp_test_can_use'] = true;

        $controller = (new \ReflectionClass(BackupController::class))->newInstanceWithoutConstructor();

        self::assertFalse($controller->permissionCheck());

        $GLOBALS['sgfp_test_provisioned'] = '1';

        self::assertTrue($controller->permissionCheck());
    }
}
}
