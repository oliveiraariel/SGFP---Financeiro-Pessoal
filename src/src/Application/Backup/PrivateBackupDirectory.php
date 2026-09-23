<?php

declare(strict_types=1);

namespace SGFP\Application\Backup;

/**
 * Resolves the private, short-lived filesystem area used between validation
 * and confirmed restoration. An explicit deployment directory remains the
 * preferred option; a WordPress temporary directory keeps the workflow usable
 * on ordinary installations that have not defined SGFP_BACKUP_DIR.
 */
final class PrivateBackupDirectory
{
    private const FALLBACK_DIRECTORY = 'sgfp-private-backups';

    public function resolve(): string
    {
        $configured = rtrim((string) (getenv('SGFP_BACKUP_DIR') ?: ''), DIRECTORY_SEPARATOR);
        if ($configured !== '') {
            return $this->usable($configured);
        }

        $temporaryRoot = function_exists('get_temp_dir')
            ? (string) get_temp_dir()
            : sys_get_temp_dir();
        $temporaryRoot = rtrim($temporaryRoot, DIRECTORY_SEPARATOR);

        if ($temporaryRoot === '') {
            throw new \RuntimeException('Não foi possível preparar o armazenamento temporário de backups.');
        }

        $directory = $temporaryRoot . DIRECTORY_SEPARATOR . self::FALLBACK_DIRECTORY;
        if (!is_dir($directory) && !mkdir($directory, 0700, true) && !is_dir($directory)) {
            throw new \RuntimeException('Não foi possível preparar o armazenamento temporário de backups.');
        }

        @chmod($directory, 0700);

        return $this->usable($directory);
    }

    private function usable(string $directory): string
    {
        $real = realpath($directory);

        if ($real === false || !is_dir($real) || !is_writable($real)) {
            throw new \RuntimeException('O armazenamento privado de backups não está disponível.');
        }

        return $real;
    }
}
