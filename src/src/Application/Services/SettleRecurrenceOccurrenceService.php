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

        $commitment = $this->materializeService->execute($recurrenceId, $month);

        if ($commitment->status->value !== 'PENDENTE') {
            throw new \RuntimeException('A ocorrência deve estar pendente para ser efetivada.', 409);
        }

        $settleService = new SettleCommitmentService(
            $this->commitmentRepository,
            $this->entryRepository,
            $this->accountRepository,
            $this->transactionManager,
            $this->userContext,
        );

        return $settleService->execute((int) $commitment->id);
    }
}
