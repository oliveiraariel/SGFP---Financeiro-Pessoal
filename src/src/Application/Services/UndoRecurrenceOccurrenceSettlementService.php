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
        private readonly CommitmentRepository $commitmentRepository,
        private readonly EntryRepository $entryRepository,
        private readonly TransactionManager $transactionManager,
        private readonly UserContext $userContext,
    ) {
    }

    public function execute(int $recurrenceId, string $month): void
    {
        $this->userContext->requireCapability('use_sgfp');
        $userId = $this->userContext->requireUserId();
        $format = preg_match('/^\d{4}-\d{2}-01$/', $month) === 1 ? '!Y-m-d' : null;
        $monthDate = $format === null ? false : \DateTimeImmutable::createFromFormat($format, $month);
        if ($monthDate === false || $monthDate->format('Y-m' . ($format === '!Y-m-d' ? '-d' : '')) !== $month) {
            throw new \InvalidArgumentException('O mês deve estar no formato YYYY-MM-01.');
        }
        $commitment = $this->commitmentRepository->findByRecurrenceIdAndMonth(
            $recurrenceId, $monthDate->format('Y-m-d'), $userId
        );
        if ($commitment === null) {
            throw new \RuntimeException('Ocorrência não encontrada.', 404);
        }

        $undoService = new UndoCommitmentSettlementService(
            $this->commitmentRepository,
            $this->entryRepository,
            $this->transactionManager,
            $this->userContext,
        );

        $undoService->execute((int) $commitment->id);
    }
}
