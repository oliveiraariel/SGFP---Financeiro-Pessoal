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

            $response = new \WP_REST_Response($backup['content'], 200);
            $response->header('Content-Type', $backup['content_type']);
            $response->header('Content-Disposition', 'attachment; filename="' . $backup['filename'] . '"');
            $response->header('Content-Length', (string) strlen($backup['content']));
            $response->header('X-SGFP-Backup-SHA256', $backup['sha256']);
            return $response;
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
