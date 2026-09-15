<?php

declare(strict_types=1);

namespace SGFP\Application\Services;

use SGFP\Application\Ports\TransactionManager;
use SGFP\Application\Ports\UserContext;
use SGFP\Application\Ports\UserDataPurger;
use SGFP\Application\Ports\UserIdentityDeleter;
use SGFP\Application\Ports\UserOperationLock;

final class DeleteAccountService
{
    public const CONFIRMATION_PHRASE = 'EXCLUIR CONTA';

    public function __construct(
        private readonly UserDataPurger $purger,
        private readonly UserIdentityDeleter $identityDeleter,
        private readonly TransactionManager $transactions,
        private readonly UserOperationLock $lock,
        private readonly UserContext $userContext,
    ) {}

    public function execute(bool $confirmation, string $phrase): void
    {
        if (!$confirmation) {
            throw new \InvalidArgumentException('A primeira confirmação da exclusão é obrigatória.');
        }

        if ($phrase !== self::CONFIRMATION_PHRASE) {
            throw new \InvalidArgumentException('Digite exatamente EXCLUIR CONTA para confirmar.');
        }

        $this->userContext->requireCapability('use_sgfp');
        $userId = $this->userContext->requireUserId();

        $this->lock->acquire($userId);

        try {
            $this->transactions->transactional(function () use ($userId): void {
                $this->purger->purgeSgfpData($userId);
                $this->identityDeleter->delete($userId);
            });
        } finally {
            $this->lock->release($userId);
        }
    }
}
