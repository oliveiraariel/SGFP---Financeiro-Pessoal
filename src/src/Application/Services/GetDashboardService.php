<?php

declare(strict_types=1);

namespace SGFP\Application\Services;

use SGFP\Application\Ports\AccountRepository;
use SGFP\Application\Ports\CommitmentRepository;
use SGFP\Application\Ports\EntryRepository;
use SGFP\Application\Ports\UserContext;
use SGFP\Domain\Enums\CommitmentNature;
use SGFP\Domain\Enums\EntryEffectType;
use SGFP\Domain\Models\Decimal;

final class GetDashboardService
{
    public function __construct(
        private readonly AccountRepository $accountRepository,
        private readonly EntryRepository $entryRepository,
        private readonly CommitmentRepository $commitmentRepository,
        private readonly UserContext $userContext,
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
        $pendingCommitments = $this->commitmentRepository->findPendingCommitmentsByUserAndPeriod(
            $userId,
            $monthStart->format('Y-m-d'),
            $monthEnd->format('Y-m-d')
        );

        $openingBalance = $realizedInflows = $realizedOutflows = $expectedInflows = $expectedOutflows = 0;

        foreach ($allEntries as $entry) {
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
