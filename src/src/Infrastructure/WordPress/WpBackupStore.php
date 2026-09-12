<?php

declare(strict_types=1);

namespace SGFP\Infrastructure\WordPress;

use SGFP\Application\Ports\BackupStore;

final class WpBackupStore implements BackupStore
{
    public function persist(int $userId, string $content, string $origin, int $expiresAt): array
    {
        $directory = rtrim((string) (getenv('SGFP_BACKUP_DIR') ?: ''), DIRECTORY_SEPARATOR);
        if ($directory === '' || !is_dir($directory) || !is_writable($directory)) {
            throw new \RuntimeException('O armazenamento privado de backups não está configurado.');
        }
        $temporary = tempnam($directory, 'sgfp-');
        $filename = bin2hex(random_bytes(24)) . '.sgfp';
        $path = $directory . DIRECTORY_SEPARATOR . $filename;
        if ($temporary === false || file_put_contents($temporary, $content, LOCK_EX) !== strlen($content) || !rename($temporary, $path)) {
            if ($temporary !== false) @unlink($temporary);
            throw new \RuntimeException('Não foi possível persistir o snapshot.');
        }
        $stored = file_get_contents($path);
        if ($stored === false || !hash_equals(hash('sha256', $content), hash('sha256', $stored))) {
            @unlink($path);
            throw new \RuntimeException('Não foi possível confirmar o snapshot persistido.');
        }
        return ['path' => $path, 'hash' => hash('sha256', $stored), 'expires_at' => $expiresAt, 'origin' => $origin];
    }
}
