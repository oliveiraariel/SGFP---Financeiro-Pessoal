<?php

declare(strict_types=1);

namespace SGFP\REST\Controllers;

use SGFP\Application\Services\CreateBackupService;

final class BackupController
{
    public function __construct(private readonly CreateBackupService $service) {}

    public function create(): \WP_REST_Response
    {
        try {
            $backup = $this->service->create();

            return new \WP_REST_Response([
                'filename' => $backup['filename'],
                'content_type' => $backup['content_type'],
                'content_base64' => $backup['content'],
                'sha256' => $backup['sha256'],
            ], 201);
        } catch (\RuntimeException $e) {
            return new \WP_REST_Response(['error' => $e->getMessage()], $e->getCode() ?: 500);
        } catch (\Throwable) {
            return new \WP_REST_Response(['error' => 'Erro interno.'], 500);
        }
    }

    public function permissionCheck(): bool
    {
        return current_user_can('use_sgfp');
    }
}
