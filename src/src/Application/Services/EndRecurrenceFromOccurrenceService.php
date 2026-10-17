<?php

declare(strict_types=1);

namespace SGFP\Application\Services;

use SGFP\Application\Ports\CommitmentRepository;
use SGFP\Application\Ports\RecurrenceRepository;
use SGFP\Application\Ports\TransactionManager;
use SGFP\Application\Ports\UserContext;
use SGFP\Domain\Enums\CommitmentStatus;

final class EndRecurrenceFromOccurrenceService
{
    public function __construct(
        private readonly MaterializeRecurrenceOccurrenceService $materializeService,
        private readonly RecurrenceRepository $recurrenceRepository,
        private readonly CommitmentRepository $commitmentRepository,
        private readonly TransactionManager $transactionManager,
        private readonly UserContext $userContext,
    ) {}

    public function execute(int $recurrenceId, string $month): void
    {
        $this->userContext->requireCapability('use_sgfp');
        $userId = $this->userContext->requireUserId();
        $monthDate = $this->month($month);
        $recurrence = $this->recurrenceRepository->findById($recurrenceId, $userId);
        if ($recurrence === null) {
            throw new \RuntimeException('Recorrência não encontrada.', 404);
        }

        $this->transactionManager->transactional(function () use ($recurrenceId, $userId, $month, $monthDate, $recurrence): void {
            // A repeated cut is an idempotent no-op for the selected occurrence;
            // still clean any pending materializations that may have been stored
            // after the original request.
            if ($recurrence->endedIn !== null && $monthDate >= $recurrence->endedIn->modify('first day of this month')) {
                $this->cancelPendingMaterializations($recurrenceId, $monthDate, $userId);
                return;
            }
            $occurrence = $this->materializeService->executeWithinTransaction($recurrenceId, $month, $userId, $recurrence);
            if ($occurrence->status !== CommitmentStatus::PENDENTE) {
                throw new \RuntimeException('A ocorrência deve estar pendente para ser excluída.', 409);
            }
            $current = $this->commitmentRepository->findByRecurrenceIdAndMonth($recurrenceId, $monthDate->format('Y-m-d'), $userId);
            if ($current === null || $current->id !== $occurrence->id) {
                throw new \RuntimeException('Ocorrência não encontrada.', 404);
            }
            if ($current->status !== CommitmentStatus::PENDENTE) {
                throw new \RuntimeException('A ocorrência deve estar pendente para ser excluída.', 409);
            }
            $this->commitmentRepository->save($current->delete());
            $this->recurrenceRepository->save($recurrence->endIn($monthDate));
            $this->cancelPendingMaterializations($recurrenceId, $monthDate, $userId);
        });
    }

    private function cancelPendingMaterializations(int $recurrenceId, \DateTimeImmutable $month, int $userId): void
    {
        foreach ($this->commitmentRepository->findPendingByRecurrenceIdFromMonth(
            $recurrenceId,
            $month->format('Y-m-d'),
            $userId,
        ) as $commitment) {
            $this->commitmentRepository->save($commitment->delete());
        }
    }

    private function month(string $month): \DateTimeImmutable
    {
        $parsed = preg_match('/^\d{4}-\d{2}-01$/', $month) === 1 ? \DateTimeImmutable::createFromFormat('!Y-m-d', $month) : false;
        if ($parsed === false || $parsed->format('Y-m-d') !== $month) {
            throw new \InvalidArgumentException('O mês deve estar no formato YYYY-MM-01.');
        }
        return $parsed;
    }
}
