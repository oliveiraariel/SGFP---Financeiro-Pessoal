<?php

declare(strict_types=1);

namespace {
    if (!class_exists('WP_REST_Server')) {
        class WP_REST_Server
        {
            public const READABLE = 'GET';
            public const CREATABLE = 'POST';
            public const EDITABLE = 'PUT';
            public const DELETABLE = 'DELETE';
        }
    }

    if (!function_exists('add_filter')) {
        function add_filter(string $hook, callable $callback, int $priority = 10, int $acceptedArgs = 1): void {}
    }

    if (!function_exists('register_rest_route')) {
        function register_rest_route(string $namespace, string $route, array $args): void
        {
            $GLOBALS['sgfp_registered_routes'][] = [$namespace, $route, $args];
        }
    }
}

namespace SGFP\Tests\Unit {
    use PHPUnit\Framework\TestCase;
    use SGFP\REST\Routes;
    use SGFP\REST\Controllers\RecurrenceController;

    final class RoutesCompositionTest extends TestCase
    {
        protected function setUp(): void
        {
            $GLOBALS['sgfp_registered_routes'] = [];
        }

        public function testRegisterComposesRecurrenceControllerAndUndoRoute(): void
        {
            (new Routes())->register();

            $routes = array_values(array_filter(
                $GLOBALS['sgfp_registered_routes'],
                static fn (array $route): bool => str_contains($route[1], '/recurrences/')
                    && str_ends_with($route[1], '/undo-effectuation')
            ));

            self::assertCount(1, $routes);
            self::assertInstanceOf(RecurrenceController::class, $routes[0][2]['callback'][0]);
            self::assertSame('undoOccurrence', $routes[0][2]['callback'][1]);
        }

        public function testCreateCommitmentRouteAllowsExplicitNullForOptionalFields(): void
        {
            (new Routes())->register();

            $routes = array_values(array_filter(
                $GLOBALS['sgfp_registered_routes'],
                static fn (array $route): bool => $route[1] === '/commitments'
                    && $route[2]['methods'] === \WP_REST_Server::CREATABLE
            ));

            self::assertCount(1, $routes);
            $args = $routes[0][2]['args'];
            self::assertSame(['integer', 'null'], $args['category_id']['type']);
            self::assertSame(['integer', 'null'], $args['recurrence_months_count']['type']);
            self::assertArrayNotHasKey('recurrence_start', $args);
        }

        public function testCreateCommitmentRouteUsesExplicitBooleanForRecurrenceMode(): void
        {
            (new Routes())->register();

            $routes = array_values(array_filter(
                $GLOBALS['sgfp_registered_routes'],
                static fn (array $route): bool => $route[1] === '/commitments'
                    && $route[2]['methods'] === \WP_REST_Server::CREATABLE
            ));

            self::assertCount(1, $routes);
            self::assertSame('boolean', $routes[0][2]['args']['recurrence_enabled']['type']);
        }

        public function testUpdateCommitmentRouteAllowsExplicitNullCategory(): void
        {
            (new Routes())->register();

            $routes = array_values(array_filter(
                $GLOBALS['sgfp_registered_routes'],
                static fn (array $route): bool => $route[1] === '/commitments/(?P<id>\\d+)'
                    && $route[2]['methods'] === \WP_REST_Server::EDITABLE
            ));

            self::assertCount(1, $routes);
            self::assertSame(['integer', 'null'], $routes[0][2]['args']['category_id']['type']);
        }

        public function testUpdateCommitmentRouteSanitizesCategoryWithoutConvertingNullToZero(): void
        {
            (new Routes())->register();

            $routes = array_values(array_filter(
                $GLOBALS['sgfp_registered_routes'],
                static fn (array $route): bool => $route[1] === '/commitments/(?P<id>\\d+)'
                    && $route[2]['methods'] === \WP_REST_Server::EDITABLE
            ));

            self::assertCount(1, $routes);
            $sanitize = $routes[0][2]['args']['category_id']['sanitize_callback'];
            self::assertNull($sanitize(null));
            self::assertSame(42, $sanitize('42'));
        }

        public function testAccountRouteCallbacksUseThePublicErrorContract(): void
        {
            $source = file_get_contents((new \ReflectionClass(Routes::class))->getFileName());

            self::assertIsString($source);
            self::assertSame(2, substr_count($source, "PublicError::fromCode(404, 'NOT_FOUND')"));
            self::assertStringNotContainsString("['error'=>'Conta não encontrada.']", $source);
            self::assertStringNotContainsString("['error' => 'Conta não encontrada.']", $source);
        }

        public function testSuccessfulBackupIsServedBeforeWordPressJsonSerializesTheResponse(): void
        {
            $source = file_get_contents((new \ReflectionClass(Routes::class))->getFileName());

            self::assertIsString($source);
            self::assertStringContainsString("add_filter('rest_pre_serve_request'", $source);
            self::assertStringContainsString("\$request->get_route() !== '/' . self::NAMESPACE . '/backups'", $source);
            self::assertStringContainsString('$request->get_method() !== \'POST\'', $source);
            self::assertStringContainsString('echo $content;', $source);
            self::assertStringContainsString('return true;', $source);
        }
    }
}
