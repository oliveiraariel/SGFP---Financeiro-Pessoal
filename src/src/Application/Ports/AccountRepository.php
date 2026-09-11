<?php

declare(strict_types=1);

namespace SGFP\Application\Ports;

use SGFP\Domain\Models\Account;

interface AccountRepository
{
    public function save(Account $account): Account;

    public function findById(int $id, int $userId): ?Account;

    public function findAllByUser(int $userId): array;

    public function findPrincipal(int $userId): ?Account;

    public function hasPrincipal(int $userId): bool;

    public function countByUser(int $userId): int;
}
