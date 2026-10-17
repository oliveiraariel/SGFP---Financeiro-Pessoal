<?php

declare(strict_types=1);

namespace SGFP\Application\Services;

use SGFP\Application\Ports\AccountRepository;
use SGFP\Application\Ports\CommitmentRepository;
use SGFP\Application\Ports\EntryRepository;
use SGFP\Application\Ports\RecurrenceRepository;
use SGFP\Application\Ports\TransactionManager;
use SGFP\Application\Ports\UserContext;
use SGFP\Domain\Models\Entry;

final class SettleRecurrenceOccurrenceService
{
    public function __construct(
        private readonly MaterializeRecurrenceOccurrenceService $materializeService,
        private readonly CommitmentRepository $commitmentRepository,
        private readonly EntryRepository $entryRepository,
        private readonly AccountRepository $accountRepository,
        private readonly TransactionManager $transactionManager,
        private readonly UserContext $userContext,
    ) {
    }

    public function execute(int $recurrenceId, string $month): Entry
    {
        $this->userContext->requireCapability('use_sgfp');
        $userId = $this->userContext->requireUserId();

        return $this->transactionManager->transactional(function () use ($recurrenceId, $month, $userId): Entry {
            $commitment = $this->materializeService->executeWithinTransaction($recurrenceId, $month, $userId);

            return (new SettleCommitmentService(
                $this->commitmentRepository,
                $this->entryRepository,
                $this->accountRepository,
                new class implements TransactionManager {
                    public function begin(): void {}
                    public function commit(): void {}
                    public function rollback(): void {}
                    public function transactional(callable $action): mixed { return $action(); }
                },
                $this->userContext,
            ))->execute((int) $commitment->id);
        });
    }
}
