<?php

declare(strict_types=1);

namespace SGFP\Application\Services;

use SGFP\Application\Ports\CommitmentRepository;
use SGFP\Application\Ports\EntryRepository;
use SGFP\Application\Ports\RecurrenceRepository;
use SGFP\Application\Ports\TransactionManager;
use SGFP\Application\Ports\UserContext;

final class UndoRecurrenceOccurrenceSettlementService
{
    public function __construct(
        private readonly MaterializeRecurrenceOccurrenceService $materializeService,
        private readonly CommitmentRepository $commitmentRepository,
        private readonly EntryRepository $entryRepository,
        private readonly TransactionManager $transactionManager,
        private readonly UserContext $userContext,
    ) {
    }

    public function execute(int $recurrenceId, string $month): void
    {
        $this->userContext->requireCapability('use_sgfp');
        $this->userContext->requireUserId();

        $commitment = $this->materializeService->execute($recurrenceId, $month);

        $undoService = new UndoCommitmentSettlementService(
            $this->commitmentRepository,
            $this->entryRepository,
            $this->transactionManager,
            $this->userContext,
        );

        $undoService->execute((int) $commitment->id);
    }
}
