<?php

declare(strict_types=1);

namespace SGFP\Application\Services;

use SGFP\Application\Backup\BackupArchive;
use SGFP\Application\Backup\BackupProtector;
use SGFP\Application\Backup\StagedBackupDecoder;
use SGFP\Application\Ports\RestorationTokenStore;
use SGFP\Application\Ports\UserContext;

final class ValidateBackupService
{
    private const MAX_BYTES = 26214400;

    public function __construct(
        private readonly RestorationTokenStore $tokens,
        private readonly UserContext $userContext,
        private readonly StagedBackupDecoder $decoder = new StagedBackupDecoder(),
        private readonly BackupArchive $archive = new BackupArchive(),
        private readonly BackupProtector $protector = new BackupProtector(),
    ) {}

    /** @return array{token:string,version:int,origin:string,created_at:string,counts:array<string,int>} */
    public function validate(string $zipContent): array
    {
        $this->userContext->requireCapability('use_sgfp');
        $userId = $this->userContext->requireUserId();

        if ($zipContent === '' || strlen($zipContent) > self::MAX_BYTES) {
            throw new \InvalidArgumentException('Arquivo de backup inválido ou excede o limite permitido.');
        }

        $packed = $this->archive->unpack($zipContent);
        $json = $this->protector->unprotect($packed['payload']);
        $payload = json_decode($json, true);

        if (!is_array($payload)) {
            throw new \InvalidArgumentException('Backup inválido.');
        }

        $validated = $this->decoder->decode($payload, $userId);

        $directory = rtrim((string) (getenv('SGFP_BACKUP_DIR') ?: ''), DIRECTORY_SEPARATOR);
        if ($directory === '' || !is_dir($directory) || !is_writable($directory)) {
            throw new \RuntimeException('O staging privado de backups não está configurado.');
        }

        $temporary = tempnam($directory, 'sgfp-');
        $filename = bin2hex(random_bytes(24)) . '.zip';

        if ($temporary === false
            || file_put_contents($temporary, $zipContent, LOCK_EX) !== strlen($zipContent)) {
            if ($temporary !== false) {
                @unlink($temporary);
            }
            throw new \RuntimeException('Não foi possível preservar o backup para validação.');
        }

        $path = $directory . DIRECTORY_SEPARATOR . $filename;

        if (!rename($temporary, $path)) {
            @unlink($temporary);
            throw new \RuntimeException('Não foi possível preservar o backup para validação.');
        }

        $stored = file_get_contents($path);
        if ($stored === false
            || !hash_equals(hash('sha256', $zipContent), hash('sha256', $stored))) {
            @unlink($path);
            throw new \RuntimeException('Não foi possível confirmar o staging do backup.');
        }

        $token = bin2hex(random_bytes(32));
        $expiresAt = time() + 900;

        try {
            $this->tokens->store(
                $userId,
                hash('sha256', $token),
                $expiresAt,
                json_encode([
                    'expires_at' => $expiresAt,
                    'origin' => $validated->metadata['origin'],
                    'hash' => hash('sha256', $stored),
                    'path' => $path,
                ], JSON_THROW_ON_ERROR)
            );
        } catch (\Throwable $e) {
            @unlink($path);
            throw $e;
        }

        return [
            'token' => $token,
            'version' => 2,
            'origin' => $validated->metadata['origin'],
            'created_at' => $validated->metadata['created_at'],
            'counts' => $validated->counts(),
        ];
    }
}
