<?php

declare(strict_types=1);

namespace SGFP\Application\Services;

use SGFP\Application\Ports\CommitmentRepository;
use SGFP\Application\Ports\RecurrenceRepository;
use SGFP\Application\Ports\TransactionManager;
use SGFP\Application\Ports\UserContext;
use SGFP\Domain\Models\Commitment;

final class MaterializeRecurrenceOccurrenceService
{
    public function __construct(
        private readonly RecurrenceRepository $recurrenceRepository,
        private readonly CommitmentRepository $commitmentRepository,
        private readonly TransactionManager $transactionManager,
        private readonly UserContext $userContext,
    ) {
    }

    public function execute(int $recurrenceId, string $month): Commitment
    {
        $this->userContext->requireCapability('use_sgfp');
        $userId = $this->userContext->requireUserId();

        $monthDate = \DateTimeImmutable::createFromFormat('!Y-m', $month);
        if ($monthDate === false) {
            throw new \InvalidArgumentException('O mês deve estar no formato YYYY-MM.');
        }
        $monthFormatted = $monthDate->format('Y-m-d');

        $recurrence = $this->recurrenceRepository->findById($recurrenceId, $userId);
        if ($recurrence === null) {
            throw new \RuntimeException('Recorrência não encontrada.', 404);
        }

        $existing = $this->commitmentRepository->findByRecurrenceIdAndMonth($recurrenceId, $monthFormatted, $userId);
        if ($existing !== null) {
            return $existing;
        }

        $base = $this->commitmentRepository->findFirstByRecurrenceId($recurrenceId, $userId);
        if ($base === null) {
            throw new \RuntimeException('Ocorrência base da recorrência não encontrada.', 404);
        }

        return $this->transactionManager->transactional(function () use ($userId, $base, $recurrenceId, $monthDate) {
            $occurrence = Commitment::create(
                $userId,
                $base->categoryId,
                $base->name,
                $base->amount,
                $base->type,
                $base->nature,
                $monthDate,
                new \DateTimeImmutable(),
                $recurrenceId,
            );

            return $this->commitmentRepository->save($occurrence);
        });
    }
}
