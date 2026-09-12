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
            $email = wp_get_current_user()->user_email;
            $tmp = wp_tempnam($backup['filename']);
            if ($tmp === false || file_put_contents($tmp, base64_decode($backup['content'], true)) === false) {
                throw new \RuntimeException('Não foi possível preparar o backup.');
            }
            $sent = wp_mail($email, 'Cópia de segurança do SGFP', 'A cópia de segurança está anexada.', [], [$tmp]);
            @unlink($tmp);
            if (!$sent) throw new \RuntimeException('Não foi possível enviar o backup por e-mail.');
            return new \WP_REST_Response(['status' => 'sent'], 201);
        } catch (\RuntimeException $e) {
            return new \WP_REST_Response(['error' => $e->getMessage()], $e->getCode() ?: 500);
        } catch (\Throwable) {
            return new \WP_REST_Response(['error' => 'Erro interno.'], 500);
        }
    }

    public function permissionCheck(): bool { return current_user_can('use_sgfp'); }
}
