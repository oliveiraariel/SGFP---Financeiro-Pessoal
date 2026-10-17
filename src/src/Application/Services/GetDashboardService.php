<?php

declare(strict_types=1);

namespace SGFP\Application\Services;

use SGFP\Application\Ports\AccountRepository;
use SGFP\Application\Ports\CommitmentRepository;
use SGFP\Application\Ports\EntryRepository;
use SGFP\Application\Ports\RecurrenceRepository;
use SGFP\Application\Ports\UserContext;
use SGFP\Domain\Enums\CommitmentNature;
use SGFP\Domain\Enums\CommitmentStatus;
use SGFP\Domain\Enums\EntryEffectType;
use SGFP\Domain\Enums\EntryOrigin;
use SGFP\Domain\Models\Commitment;
use SGFP\Domain\Models\Decimal;
use SGFP\Domain\Models\Entry;

final class GetDashboardService
{
    public function __construct(
        private readonly AccountRepository $accountRepository,
        private readonly EntryRepository $entryRepository,
        private readonly CommitmentRepository $commitmentRepository,
        private readonly UserContext $userContext,
        private readonly ?RecurrenceRepository $recurrenceRepository = null,
    ) {
    }

    public function execute(string $month): array
    {
        $this->userContext->requireCapability('use_sgfp');
        $userId = $this->userContext->requireUserId();

        $monthStart = self::parseMonth($month);
        $monthEnd = $monthStart->modify('last day of this month 23:59:59');
        $previousEnd = $monthStart->modify('-1 second');

        $allEntries = $this->entryRepository->findActiveEntriesByUser($userId);
        $monthEntries = $this->entryRepository->findActiveEntriesByUserAndPeriod($userId, $monthStart, $monthEnd);
        $initialBalance = null;

        foreach ($allEntries as $entry) {
            if ($entry->origin === EntryOrigin::SALDO_INICIAL
                && ($initialBalance === null || $entry->settledAt < $initialBalance->settledAt)) {
                $initialBalance = $entry;
            }
        }

        // The configured opening balance establishes the beginning of the
        // financial history. Older rows may exist in a restored/legacy
        // dataset, but must not leak into reporting before that month.
        $financialStart = $initialBalance?->settledAt->modify('first day of this month');
        if ($financialStart !== null) {
            $monthEntries = array_values(array_filter(
                $monthEntries,
                static fn (Entry $entry): bool => $entry->settledAt >= $financialStart,
            ));
        }
        $pendingCommitments = $financialStart !== null && $monthStart < $financialStart
            ? []
            : $this->projectPendingCommitmentsForMonth($userId, $monthStart);

        $openingBalance = $realizedInflows = $realizedOutflows = $expectedInflows = $expectedOutflows = 0;
        foreach ($allEntries as $entry) {
            if ($financialStart !== null && $entry->settledAt < $financialStart) {
                continue;
            }
            if ($entry->settledAt <= $previousEnd) {
                $openingBalance += ($entry->effectType === EntryEffectType::ENTRADA ? 1 : -1) * Decimal::cents($entry->amount);
            }
        }

        foreach ($monthEntries as $entry) {
            if ($entry->effectType === EntryEffectType::ENTRADA) {
                $realizedInflows += Decimal::cents($entry->amount);
            } else {
                $realizedOutflows -= Decimal::cents($entry->amount);
            }
        }

        foreach ($pendingCommitments as $commitment) {
            if ($commitment->nature === CommitmentNature::ENTRADA) {
                $expectedInflows += Decimal::cents($commitment->amount);
            } else {
                $expectedOutflows -= Decimal::cents($commitment->amount);
            }
        }

        $currentBalance = $openingBalance + $realizedInflows + $realizedOutflows;
        $expectedClosingBalance = $currentBalance + $expectedInflows + $expectedOutflows;

        return [
            'month' => $month,
            'is_before_financial_start' => $financialStart !== null && $monthStart < $financialStart,
            'opening_balance' => Decimal::formatCents($openingBalance),
            'realized_inflows' => Decimal::formatCents($realizedInflows),
            'realized_outflows' => Decimal::formatCents($realizedOutflows),
            'current_balance' => Decimal::formatCents($currentBalance),
            'expected_inflows' => Decimal::formatCents($expectedInflows),
            'expected_outflows' => Decimal::formatCents($expectedOutflows),
            'expected_closing_balance' => Decimal::formatCents($expectedClosingBalance),
            'pending_commitments' => array_map(fn ($c) => [
                'id' => $c->id,
                'name' => $c->name,
                'amount' => $c->amount,
                'nature' => $c->nature->value,
                'reference_month' => $c->referenceMonth->format('Y-m-01'),
            ], $pendingCommitments),
        ];
    }

    public function executeEvolution(string $anchorMonth, int $months): array
    {
        $this->userContext->requireCapability('use_sgfp');
        $userId = $this->userContext->requireUserId();
        $anchor = self::parseMonth($anchorMonth);
        if ($months < 1 || $months > 12) {
            throw new \InvalidArgumentException('A quantidade de meses deve estar entre 1 e 12.');
        }

        $series = [];
        for ($offset = $months - 1; $offset >= 0; $offset--) {
            $start = $anchor->modify('-' . $offset . ' months');
            $end = $start->modify('+1 month');
            $planned = 0;
            foreach ($this->projectPendingCommitmentsForMonth($userId, $start) as $commitment) {
                $planned += ($commitment->nature === CommitmentNature::ENTRADA ? 1 : -1) * Decimal::cents($commitment->amount);
            }
            $effective = 0;
            foreach ($this->entryRepository->findActiveEntriesByUserAndPeriod($userId, $start, $end->modify('-1 second')) as $entry) {
                $effective += ($entry->effectType === EntryEffectType::ENTRADA ? 1 : -1) * Decimal::cents($entry->amount);
            }
            $series[] = [
                'month' => $start->format('Y-m-01'),
                'planned' => Decimal::formatCents($planned),
                'effective' => Decimal::formatCents($effective),
            ];
        }
        return ['anchor_month' => $anchorMonth, 'months' => $months, 'items' => $series];
    }

    /**
     * Returns a reconciliable daily balance series without materializing occurrences.
     * The existing monthly evolution contract remains unchanged.
     */
    public function executeDailyEvolution(string $anchorMonth, int $months): array
    {
        $this->userContext->requireCapability('use_sgfp');
        $userId = $this->userContext->requireUserId();
        $anchor = self::parseMonth($anchorMonth);
        if ($months < 1 || $months > 12) {
            throw new \InvalidArgumentException('A quantidade de meses deve estar entre 1 e 12.');
        }

        // The dashboard's three-month view is centered on the selected month:
        // previous month, selected month, next month. Keep the generic
        // historical behavior for other callers (including annual evolution).
        $startOffset = $months === 3 ? -1 : -($months - 1);
        $endOffset = $months === 3 ? 2 : 1;
        $start = $anchor->modify(($startOffset >= 0 ? '+' : '') . $startOffset . ' months')->setTime(0, 0);
        $endExclusive = $anchor->modify('+' . $endOffset . ' months')->setTime(0, 0);
        $entries = $this->entryRepository->findActiveEntriesByUser($userId);
        $initialBalance = null;
        foreach ($entries as $entry) {
            if ($entry->origin === EntryOrigin::SALDO_INICIAL
                && ($initialBalance === null || $entry->settledAt < $initialBalance->settledAt)) {
                $initialBalance = $entry;
            }
        }
        $financialStart = $initialBalance?->settledAt->modify('first day of this month');
        $balance = 0;
        $byDay = [];
        foreach ($entries as $entry) {
            if ($financialStart !== null && $entry->settledAt < $financialStart) {
                continue;
            }
            if ($entry->settledAt < $start) {
                $balance += ($entry->effectType === EntryEffectType::ENTRADA ? 1 : -1) * Decimal::cents($entry->amount);
                continue;
            }
            if ($entry->settledAt >= $endExclusive) {
                continue;
            }
            $day = $entry->settledAt->format('Y-m-d');
            $byDay[$day][] = $entry;
        }

        $pendingByDay = [];
        $pendingByMonth = [];
        $cursor = $start;
        while ($cursor < $endExclusive) {
            $monthStart = $cursor->modify('first day of this month');
            $pending = $financialStart !== null && $monthStart < $financialStart
                ? []
                : $this->projectPendingCommitmentsForMonth($userId, $monthStart);
            foreach ($pending as $commitment) {
                $day = $commitment->referenceMonth->format('Y-m-d');
                if ($day >= $start->format('Y-m-d') && $day < $endExclusive->format('Y-m-d')) {
                    $pendingByDay[$day][] = $commitment;
                    $month = $commitment->referenceMonth->format('Y-m');
                    $pendingByMonth[$month] = ($pendingByMonth[$month] ?? 0)
                        + ($commitment->nature === CommitmentNature::ENTRADA ? 1 : -1) * Decimal::cents($commitment->amount);
                }
            }
            $cursor = $monthStart->modify('+1 month');
        }

        $items = [];
        for ($day = $start; $day < $endExclusive; $day = $day->modify('+1 day')) {
            $date = $day->format('Y-m-d');
            $opening = $balance;
            $realizedInflows = $realizedOutflows = $pendingInflows = $pendingOutflows = 0;
            foreach ($byDay[$date] ?? [] as $entry) {
                $value = Decimal::cents($entry->amount);
                if ($entry->effectType === EntryEffectType::ENTRADA) {
                    $realizedInflows += $value;
                } else {
                    $realizedOutflows -= $value;
                }
            }
            foreach ($pendingByDay[$date] ?? [] as $commitment) {
                $value = Decimal::cents($commitment->amount);
                if ($commitment->nature === CommitmentNature::ENTRADA) {
                    $pendingInflows += $value;
                } else {
                    $pendingOutflows -= $value;
                }
            }
            $realizedClosing = $opening + $realizedInflows + $realizedOutflows;
            // The projected line must use the same pending population as the
            // monthly dashboard card. A pending commitment remains projected
            // for every day in its reference month until its persisted state
            // changes; pending amounts from other months do not leak in.
            $projectedClosing = $realizedClosing + ($pendingByMonth[$day->format('Y-m')] ?? 0);
            $items[] = [
                'date' => $date,
                'opening_balance' => Decimal::formatCents($opening),
                'realized_inflows' => Decimal::formatCents($realizedInflows),
                'realized_outflows' => Decimal::formatCents($realizedOutflows),
                'realized_closing_balance' => Decimal::formatCents($realizedClosing),
                'pending_inflows' => Decimal::formatCents($pendingInflows),
                'pending_outflows' => Decimal::formatCents($pendingOutflows),
                'projected_closing_balance' => Decimal::formatCents($projectedClosing),
                'realized_entry_count' => count($byDay[$date] ?? []),
                'pending_commitment_count' => count($pendingByDay[$date] ?? []),
            ];
            $balance = $realizedClosing;
        }

        return [
            'anchor_month' => $anchorMonth,
            'months' => $months,
            'window' => ['start' => $start->format('Y-m-d'), 'end' => $endExclusive->modify('-1 day')->format('Y-m-d')],
            'timezone' => date_default_timezone_get(),
            'items' => $items,
        ];
    }

    /**
     * Returns the complete calendar-year daily series used by annual charts.
     * The December anchor keeps the existing daily contract as the source of
     * truth while making the 365/366-day boundary explicit to callers.
     */
    public function executeAnnualEvolution(string $year): array
    {
        if (preg_match('/^\d{4}$/', $year) !== 1 || (int) $year < 1900 || (int) $year > 2200) {
            throw new \InvalidArgumentException('O ano deve estar no formato YYYY entre 1900 e 2200.');
        }

        return $this->executeDailyEvolution($year . '-12-01', 12) + [
            'year' => $year,
        ];
    }

    /**
     * Projects a recurrence occurrence only for reporting. Dashboard reads must
     * not persist an occurrence, and a persisted tombstone remains authoritative.
     *
     * @return Commitment[] pending commitments for the selected month
     */
    private function projectPendingCommitmentsForMonth(int $userId, \DateTimeImmutable $monthStart): array
    {
        $monthEnd = $monthStart->modify('last day of this month 23:59:59');
        $pending = array_values(array_filter(
            $this->commitmentRepository->findPendingCommitmentsByUserAndPeriod(
                $userId,
                $monthStart->format('Y-m-d'),
                $monthEnd->format('Y-m-d')
            ),
            static fn (Commitment $commitment): bool => $commitment->status === CommitmentStatus::PENDENTE,
        ));

        if ($this->recurrenceRepository === null) {
            return $pending;
        }

        foreach ($this->recurrenceRepository->findAllByUser($userId) as $recurrence) {
            if ($recurrence->id === null) {
                continue;
            }

            // Any persisted occurrence, including EFETIVADO or an EXCLUIDO
            // tombstone, is the source of truth for its month.
            $existing = $this->commitmentRepository->findByRecurrenceIdAndMonth(
                $recurrence->id,
                $monthStart->format('Y-m-d'),
                $userId,
            );
            if ($existing !== null) {
                continue;
            }

            $base = $this->commitmentRepository->findFirstByRecurrenceId($recurrence->id, $userId);
            if ($base === null) {
                continue;
            }

            $seriesStart = $base->referenceMonth->modify('first day of this month');
            if ($monthStart < $seriesStart
                || ($recurrence->monthsCount !== null && $monthStart >= $seriesStart->modify('+' . $recurrence->monthsCount . ' months'))
                || ($recurrence->endedIn !== null && $monthStart > $recurrence->endedIn->modify('first day of this month'))) {
                continue;
            }

            $occurrenceDate = $monthStart->setDate(
                (int) $monthStart->format('Y'),
                (int) $monthStart->format('m'),
                min((int) $base->referenceMonth->format('d'), (int) $monthStart->format('t')),
            );
            $pending[] = Commitment::create(
                $userId,
                $base->categoryId,
                $base->name,
                $base->amount,
                $base->nature,
                $occurrenceDate,
                $base->createdAt,
                $recurrence->id,
            );
        }

        return $pending;
    }

    private static function parseMonth(string $month): \DateTimeImmutable
    {
        $format = preg_match('/^\d{4}-\d{2}-01$/', $month) === 1 ? '!Y-m-d' : null;
        $date = $format === null ? false : \DateTimeImmutable::createFromFormat($format, $month);
        if ($date === false || $date->format('Y-m-d') !== $month) {
            throw new \InvalidArgumentException('O mês deve estar no formato YYYY-MM-01.');
        }
        return $date;
    }
}
