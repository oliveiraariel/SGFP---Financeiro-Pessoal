<?php

declare(strict_types=1);

namespace SGFP\Application\Ports;

use SGFP\Domain\Models\Entry;

interface EntryRepository
{
    public function save(Entry $entry): Entry;

    public function findByCommitmentId(int $commitmentId, int $userId): ?Entry;

    public function findByCommitmentIdAndAccount(int $commitmentId, int $accountId, int $userId): ?Entry;

    public function findActiveInitialBalanceByAccount(int $accountId, int $userId): ?Entry;
}
