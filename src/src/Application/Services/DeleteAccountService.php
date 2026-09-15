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
            // A identidade WordPress não participa da transação SQL do SGFP.
            // Depois do COMMIT, uma falha deixa a exclusão explicitamente
            // incompleta e pode ser retomada sem recriar os dados removidos.
            $this->transactions->transactional(function () use ($userId): void {
                $this->purger->purgeSgfpData($userId);
            });
            try {
                $this->identityDeleter->delete($userId);
            } catch (\Throwable $e) {
                throw new \RuntimeException(
                    'Exclusão incompleta: os dados SGFP foram removidos, mas a identidade WordPress não foi excluída. Tente novamente.',
                    409,
                    $e
                );
            }
        } finally {
            $this->lock->release($userId);
        }
    }
}
