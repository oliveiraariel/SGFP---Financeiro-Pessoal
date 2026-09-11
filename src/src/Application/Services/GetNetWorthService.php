<?php

declare(strict_types=1);

namespace SGFP\Application\Services;

use SGFP\Application\Ports\AccountRepository;
use SGFP\Application\Ports\EntryRepository;
use SGFP\Application\Ports\UserContext;
use SGFP\Domain\Enums\EntryEffectType;

final class GetNetWorthService
{
    public function __construct(
        private readonly AccountRepository $accountRepository,
        private readonly EntryRepository $entryRepository,
        private readonly UserContext $userContext,
    ) {
    }

    public function execute(): array
    {
        $this->userContext->requireCapability('use_sgfp');
        $userId = $this->userContext->requireUserId();

        $accounts = $this->accountRepository->findAllByUser($userId);
        $entries = $this->entryRepository->findActiveEntriesByUser($userId);

        $balances = [];
        $total = 0.0;

        foreach ($accounts as $account) {
            $balance = 0.0;
            foreach ($entries as $entry) {
                if ($entry->accountId !== $account->id) {
                    continue;
                }
                $balance += $entry->effectType === EntryEffectType::ENTRADA ? $entry->amount : -$entry->amount;
            }
            $balances[] = [
                'id' => $account->id,
                'name' => $account->name,
                'role' => $account->role->value,
                'balance' => number_format($balance, 2, '.', ''),
            ];
            $total += $balance;
        }

        return [
            'accounts' => $balances,
            'net_worth' => number_format($total, 2, '.', ''),
        ];
    }
}
