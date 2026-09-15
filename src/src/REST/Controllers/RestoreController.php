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
            $tmpName = (string) ($file['tmp_name'] ?? '');
            $size = (int) ($file['size'] ?? -1);
            if ($tmpName === '' || !is_uploaded_file($tmpName)) {
                throw new \InvalidArgumentException('Arquivo de backup inválido.');
            }
            if ($size < 0 || $size > 26214400) {
                throw new \InvalidArgumentException('Arquivo de backup excede o limite permitido.');
            }
            $content = file_get_contents($tmpName);
            if ($content === false) throw new \InvalidArgumentException('Não foi possível ler o arquivo.');
            return new \WP_REST_Response($this->service->validate($content), 200);
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
            $confirmation = $request->get_param('confirmation');
            if (!is_bool($confirmation)) {
                throw new \InvalidArgumentException('A confirmação da restauração deve ser booleana.');
            }
            return new \WP_REST_Response($this->revalidation->confirm((string) $request->get_param('token'), $confirmation), 200);
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
