<?php

declare(strict_types=1);

namespace SGFP\Application\Services;

use SGFP\Application\Backup\BackupArchive;
use SGFP\Application\Backup\BackupPayloadBuilder;
use SGFP\Application\Backup\BackupProtector;
use SGFP\Application\Ports\TransactionManager;
use SGFP\Application\Ports\UserContext;
use SGFP\Application\Ports\UserOperationLock;

final class CreateBackupService
{
    private const MAX_BYTES = 26214400;

    public function __construct(
        private readonly BackupPayloadBuilder $payloadBuilder,
        private readonly TransactionManager $transactions,
        private readonly UserContext $userContext,
        private readonly UserOperationLock $operationLock,
        private readonly BackupProtector $protector = new BackupProtector(),
        private readonly BackupArchive $archive = new BackupArchive(),
    ) {}

    /** @return array{filename:string,content:string,content_type:string,sha256:string} */
    public function create(): array
    {
        $this->userContext->requireCapability('use_sgfp');
        $userId = $this->userContext->requireUserId();

        $this->operationLock->acquire($userId);
        try {
            $payload = $this->transactions->transactional(
                fn (): array => $this->payloadBuilder->build($userId, 'manual')
            );
        } finally {
            $this->operationLock->release($userId);
        }

        $json = json_encode($payload, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE);
        $zipContent = $this->archive->pack($this->protector->protect($json));

        if (strlen($zipContent) > self::MAX_BYTES) {
            throw new \RuntimeException('O backup excede o limite permitido.', 413);
        }

        return [
            'filename' => 'sgfp-backup-' . gmdate('Ymd-His') . '.zip',
            'content_type' => 'application/zip',
            // The REST controller sends this value as application/zip. Keep
            // the archive binary here; base64 would make the downloaded ZIP
            // invalid while also making the advertised digest misleading.
            'content' => $zipContent,
            'sha256' => hash('sha256', $zipContent),
        ];
    }
}
