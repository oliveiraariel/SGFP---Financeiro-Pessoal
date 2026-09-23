<?php

declare(strict_types=1);

namespace SGFP\REST\Controllers;

use SGFP\Application\Services\ThemeService;

final class ThemeController
{
    public function __construct(
        private readonly ThemeService $themeService,
    ) {
    }

    public function get(): \WP_REST_Response
    {
        try {
            $theme = $this->themeService->get();

            return new \WP_REST_Response(['theme' => $theme], 200);
        } catch (\RuntimeException $e) {
            return \SGFP\REST\PublicError::response($e, $e->getCode() ?: 500);
        } catch (\Throwable $e) {
            return \SGFP\REST\PublicError::response($e, 500);
        }
    }

    public function update(\WP_REST_Request $request): \WP_REST_Response
    {
        try {
            $theme = isset($request['theme']) ? (string) $request['theme'] : '';

            $updatedTheme = $this->themeService->update($theme);

            return new \WP_REST_Response(['theme' => $updatedTheme], 200);
        } catch (\InvalidArgumentException $e) {
            return \SGFP\REST\PublicError::response($e, 400, 'VALIDATION_ERROR');
        } catch (\RuntimeException $e) {
            return \SGFP\REST\PublicError::response($e, $e->getCode() ?: 500);
        } catch (\Throwable $e) {
            return \SGFP\REST\PublicError::response($e, 500);
        }
    }

    public function permissionCheck(): bool
    {
        return \SGFP\Infrastructure\WordPress\WpUserContext::canAccessSgfp();
    }
}
