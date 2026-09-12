<?php

declare(strict_types=1);

namespace SGFP\REST\Controllers;

use SGFP\Application\Services\ValidateBackupService;
use SGFP\Application\Services\RevalidateRestorationService;

final class RestoreController
{
    public function __construct(
        private readonly ValidateBackupService $service,
        private readonly RevalidateRestorationService $revalidation,
    ) {}

    public function validate(\WP_REST_Request $request): \WP_REST_Response
    {
        try {
            $files = $request->get_file_params();
            $file = $files['backup'] ?? null;
            if (!is_array($file) || ($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
                throw new \InvalidArgumentException('Arquivo de backup não informado.');
            }
            $content = file_get_contents((string) $file['tmp_name']);
            if ($content === false) throw new \InvalidArgumentException('Não foi possível ler o arquivo.');
            return new \WP_REST_Response($this->service->validate(trim($content)), 200);
        } catch (\InvalidArgumentException $e) {
            return new \WP_REST_Response(['error' => $e->getMessage()], 400);
        } catch (\RuntimeException $e) {
            return new \WP_REST_Response(['error' => $e->getMessage()], $e->getCode() ?: 500);
        } catch (\Throwable) {
            return new \WP_REST_Response(['error' => 'Erro interno.'], 500);
        }
    }

    public function prepare(\WP_REST_Request $request): \WP_REST_Response
    {
        try {
            return new \WP_REST_Response($this->revalidation->prepare((string) $request->get_param('token')), 200);
        } catch (\InvalidArgumentException $e) {
            return new \WP_REST_Response(['error' => $e->getMessage()], 400);
        } catch (\RuntimeException $e) {
            return new \WP_REST_Response(['error' => $e->getMessage()], $e->getCode() ?: 500);
        } catch (\Throwable) {
            return new \WP_REST_Response(['error' => 'Erro interno.'], 500);
        }
    }

    public function permissionCheck(): bool { return current_user_can('use_sgfp'); }
}
