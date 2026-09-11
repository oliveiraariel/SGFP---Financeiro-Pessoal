<?php

declare(strict_types=1);

namespace SGFP\Domain\Policies;

use SGFP\Application\Ports\AccountRepository;

final class AccountPolicy
{
    public function __construct(
        private readonly AccountRepository $repository,
    ) {
    }

    public function canCreateSecondary(int $userId): bool
    {
        return $this->repository->hasPrincipal($userId);
    }
}
