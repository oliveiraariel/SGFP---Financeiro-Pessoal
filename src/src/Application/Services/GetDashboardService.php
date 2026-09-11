<?php

declare(strict_types=1);

namespace SGFP\Application\Services;

use SGFP\Application\Ports\AccountRepository;
use SGFP\Application\Ports\CommitmentRepository;
use SGFP\Application\Ports\EntryRepository;
use SGFP\Application\Ports\UserContext;
use SGFP\Domain\Enums\CommitmentNature;
use SGFP\Domain\Enums\EntryEffectType;

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

        $monthStart = new \DateTimeImmutable($month . '-01');
        $monthEnd = $monthStart->modify('last day of this month 23:59:59');
        $previousEnd = $monthStart->modify('-1 second');

        $accounts = $this->accountRepository->findAllByUser($userId);
        $allEntries = $this->entryRepository->findActiveEntriesByUser($userId);
        $monthEntries = $this->entryRepository->findActiveEntriesByUserAndPeriod($userId, $monthStart, $monthEnd);
        $pendingCommitments = $this->commitmentRepository->findPendingCommitmentsByUserAndPeriod(
            $userId,
            $monthStart->format('Y-m-d'),
            $monthEnd->format('Y-m-d')
        );

        $openingBalance = 0.0;
        $realizedInflows = 0.0;
        $realizedOutflows = 0.0;
        $expectedInflows = 0.0;
        $expectedOutflows = 0.0;

        foreach ($accounts as $account) {
            foreach ($allEntries as $entry) {
                if ($entry->accountId !== $account->id || $entry->settledAt > $previousEnd) {
                    continue;
                }
                $openingBalance += $entry->effectType === EntryEffectType::ENTRADA ? $entry->amount : -$entry->amount;
            }
        }

        foreach ($monthEntries as $entry) {
            if ($entry->effectType === EntryEffectType::ENTRADA) {
                $realizedInflows += $entry->amount;
            } else {
                $realizedOutflows += $entry->amount;
            }
        }

        foreach ($pendingCommitments as $commitment) {
            if ($commitment->nature === CommitmentNature::ENTRADA) {
                $expectedInflows += $commitment->amount;
            } else {
                $expectedOutflows += $commitment->amount;
            }
        }

        $expectedClosingBalance = $openingBalance + $realizedInflows - $realizedOutflows + $expectedInflows - $expectedOutflows;

        return [
            'month' => $month,
            'opening_balance' => number_format($openingBalance, 2, '.', ''),
            'realized_inflows' => number_format($realizedInflows, 2, '.', ''),
            'realized_outflows' => number_format($realizedOutflows, 2, '.', ''),
            'expected_inflows' => number_format($expectedInflows, 2, '.', ''),
            'expected_outflows' => number_format($expectedOutflows, 2, '.', ''),
            'expected_closing_balance' => number_format($expectedClosingBalance, 2, '.', ''),
            'pending_commitments' => array_map(fn ($c) => [
                'id' => $c->id,
                'name' => $c->name,
                'amount' => number_format($c->amount, 2, '.', ''),
                'nature' => $c->nature->value,
                'reference_month' => $c->referenceMonth->format('Y-m-d'),
            ], $pendingCommitments),
        ];
    }
}
