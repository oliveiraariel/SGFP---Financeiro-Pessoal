<?php

declare(strict_types=1);

namespace SGFP\Application\Services;

use SGFP\Application\Backup\BackupArchive;
use SGFP\Application\Backup\BackupPayloadBuilder;
use SGFP\Application\Backup\BackupProtector;
use SGFP\Application\Ports\BackupStore;
use SGFP\Application\Ports\TransactionManager;
use SGFP\Application\Ports\UserContext;
use SGFP\Application\Ports\UserOperationLock;

final class CapturePreRestorationSnapshotService
{
    private const ORIGIN = 'pre_restore';

    public function __construct(
        private readonly BackupPayloadBuilder $payloadBuilder,
        private readonly TransactionManager $transactions,
        private readonly UserContext $userContext,
        private readonly UserOperationLock $operationLock,
        private readonly BackupStore $store,
        private readonly BackupProtector $protector = new BackupProtector(),
        private readonly BackupArchive $archive = new BackupArchive(),
    ) {}

    /** @return array{path:string,hash:string,expires_at:int,origin:string} */
    public function capture(): array
    {
        $this->userContext->requireCapability('use_sgfp');
        $userId = $this->userContext->requireUserId();

        $this->operationLock->acquire($userId);
        try {
            return $this->captureUnderLock($userId);
        } finally {
            $this->operationLock->release($userId);
        }
    }

    public function captureUnderLock(int $userId): array
    {
        $this->userContext->requireCapability('use_sgfp');

        if ($this->userContext->requireUserId() !== $userId) {
            throw new \RuntimeException('O usuário do snapshot não corresponde ao usuário autenticado.');
        }

        $payload = $this->transactions->transactional(
            fn (): array => $this->payloadBuilder->build($userId, self::ORIGIN)
        );

        $json = json_encode($payload, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE);
        $zipContent = $this->archive->pack($this->protector->protect($json));

        return $this->store->persist(
            $userId,
            $zipContent,
            self::ORIGIN,
            time() + 86400
        );
    }
}
