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
            return new \WP_REST_Response(['error' => $e->getMessage()], $e->getCode() ?: 500);
        } catch (\Throwable $e) {
            return new \WP_REST_Response(['error' => 'Erro interno.'], 500);
        }
    }

    public function update(\WP_REST_Request $request): \WP_REST_Response
    {
        try {
            $theme = isset($request['theme']) ? (string) $request['theme'] : '';

            $updatedTheme = $this->themeService->update($theme);

            return new \WP_REST_Response(['theme' => $updatedTheme], 200);
        } catch (\InvalidArgumentException $e) {
            return new \WP_REST_Response(['error' => $e->getMessage()], 400);
        } catch (\RuntimeException $e) {
            return new \WP_REST_Response(['error' => $e->getMessage()], $e->getCode() ?: 500);
        } catch (\Throwable $e) {
            return new \WP_REST_Response(['error' => 'Erro interno.'], 500);
        }
    }

    public function permissionCheck(): bool
    {
        return current_user_can('use_sgfp');
    }
}
