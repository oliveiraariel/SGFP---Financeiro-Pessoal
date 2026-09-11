<?php

declare(strict_types=1);

namespace SGFP\Application\Services;

use SGFP\Application\Ports\AccountRepository;
use SGFP\Application\Ports\EntryRepository;
use SGFP\Application\Ports\TransactionManager;
use SGFP\Application\Ports\UserContext;
use SGFP\Domain\Enums\AccountRole;
use SGFP\Domain\Enums\EntryEffectType;
use SGFP\Domain\Enums\EntryOrigin;
use SGFP\Domain\Enums\EntryState;
use SGFP\Domain\Models\Entry;

final class SetInitialBalanceService
{
    public function __construct(
        private readonly AccountRepository $accountRepository,
        private readonly EntryRepository $entryRepository,
        private readonly TransactionManager $transactionManager,
        private readonly UserContext $userContext,
    ) {
    }

    public function execute(
        int $accountId,
        float $amount,
        ?string $name,
        ?string $description,
        ?\DateTimeImmutable $effectiveMonth,
    ): Entry {
        $this->userContext->requireCapability('use_sgfp');
        $userId = $this->userContext->requireUserId();

        $account = $this->accountRepository->findById($accountId, $userId);

        if ($account === null) {
            throw new \RuntimeException('Conta não encontrada.', 404);
        }

        if ($account->role !== AccountRole::PRINCIPAL) {
            throw new \RuntimeException('Saldo inicial somente pode ser definido na conta principal.', 403);
        }

        $now = new \DateTimeImmutable();
        $settledAt = $this->resolveSettledAt($effectiveMonth ?? $now);

        return $this->transactionManager->transactional(function () use ($userId, $accountId, $amount, $name, $description, $now, $settledAt) {
            $existing = $this->entryRepository->findActiveInitialBalanceByAccount($accountId, $userId);

            if ($existing !== null) {
                $this->entryRepository->save($existing->withUndone($now));
            }

            $entry = new Entry(
                null,
                $userId,
                $accountId,
                null,
                EntryOrigin::SALDO_INICIAL,
                $name ?? 'Saldo inicial',
                $amount,
                EntryEffectType::ENTRADA,
                $settledAt,
                $description,
                EntryState::ATIVO,
                $now,
                null,
            );

            return $this->entryRepository->save($entry);
        });
    }

    private function resolveSettledAt(\DateTimeImmutable $reference): \DateTimeImmutable
    {
        return $reference->setDate(
            (int) $reference->format('Y'),
            (int) $reference->format('m'),
            1
        )->setTime(0, 0, 0);
    }
}
