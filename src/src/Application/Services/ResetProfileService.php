<?php

declare(strict_types=1);

namespace SGFP\Application\Services;

use SGFP\Application\Ports\TransactionManager;
use SGFP\Application\Ports\UserContext;
use SGFP\Application\Ports\UserDataPurger;
use SGFP\Application\Ports\UserOperationLock;
use SGFP\Domain\Models\Account;

final class ResetProfileService
{
    public const CONFIRMATION_PHRASE = 'RESETAR PERFIL';

    public function __construct(
        private readonly UserDataPurger $purger,
        private readonly ProvisionUserService $provisioner,
        private readonly TransactionManager $transactions,
        private readonly UserOperationLock $lock,
        private readonly UserContext $userContext,
    ) {}

    public function execute(bool $confirmation, string $phrase): Account
    {
        if (!$confirmation) {
            throw new \InvalidArgumentException('A primeira confirmação do reset é obrigatória.');
        }

        if ($phrase !== self::CONFIRMATION_PHRASE) {
            throw new \InvalidArgumentException('Digite exatamente RESETAR PERFIL para confirmar.');
        }

        $this->userContext->requireCapability('use_sgfp');
        $userId = $this->userContext->requireUserId();

        $this->lock->acquire($userId);

        try {
            return $this->transactions->transactional(function () use ($userId): Account {
                $this->purger->purgeSgfpData($userId);
                return $this->provisioner->execute($userId);
            });
        } finally {
            $this->lock->release($userId);
        }
    }
}
