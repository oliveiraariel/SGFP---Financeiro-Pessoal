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
    ) {}

    public function execute(int $recurrenceId, string $month): Commitment
    {
        $this->userContext->requireCapability('use_sgfp');
        $userId = $this->userContext->requireUserId();

        $format = preg_match('/^\d{4}-\d{2}-01$/', $month) === 1 ? '!Y-m-d' : null;
        $monthDate = $format === null ? false : \DateTimeImmutable::createFromFormat($format, $month);
        if ($monthDate === false || $monthDate->format('Y-m' . ($format === '!Y-m-d' ? '-d' : '')) !== $month) {
            throw new \InvalidArgumentException('O mês deve estar no formato YYYY-MM-01.');
        }

        $monthFormatted = $monthDate->format('Y-m-d');
        $recurrence = $this->recurrenceRepository->findById($recurrenceId, $userId);
        if ($recurrence === null) {
            throw new \RuntimeException('Recorrência não encontrada.', 404);
        }

        $base = $this->commitmentRepository->findFirstByRecurrenceId($recurrenceId, $userId);
        if ($base === null) {
            throw new \RuntimeException('Ocorrência base da recorrência não encontrada.', 404);
        }

        // O primeiro mês da série é o mês de referência do compromisso criado,
        // e não o mês corrente do servidor usado no provisionamento da recorrência.
        $start = $base->referenceMonth->modify('first day of this month');
        if ($monthDate < $start) {
            throw new \InvalidArgumentException('O mês da ocorrência não pode ser anterior ao início da recorrência.');
        }
        if ($recurrence->monthsCount !== null && $monthDate >= $start->modify('+' . $recurrence->monthsCount . ' months')) {
            throw new \InvalidArgumentException('O mês da ocorrência excede a duração da recorrência.');
        }
        if ($recurrence->endedIn !== null && $monthDate > $recurrence->endedIn->modify('first day of this month')) {
            throw new \InvalidArgumentException('O mês da ocorrência é posterior ao encerramento da recorrência.');
        }

        $existing = $this->commitmentRepository->findByRecurrenceIdAndMonth($recurrenceId, $monthFormatted, $userId);
        if ($existing !== null) {
            return $existing;
        }

        $day = (int) $base->referenceMonth->format('d');
        $lastDay = (int) $monthDate->format('t');
        $occurrenceDate = $monthDate->setDate(
            (int) $monthDate->format('Y'),
            (int) $monthDate->format('m'),
            min($day, $lastDay),
        );

        return $this->transactionManager->transactional(function () use ($userId, $base, $recurrenceId, $occurrenceDate) {
            return $this->commitmentRepository->save(Commitment::create(
                $userId,
                $base->categoryId,
                $base->name,
                $base->amount,
                $base->nature,
                $occurrenceDate,
                new \DateTimeImmutable(),
                $recurrenceId,
            ));
        });
    }
}
