<?php

declare(strict_types=1);

namespace SGFP\Application\Services;

use SGFP\Application\Ports\CategoryRepository;

final class SeedCategoriesService
{
    public function __construct(
        private readonly CategoryRepository $repository,
    ) {
    }

    public function execute(int $userId): void
    {
        $this->repository->seedDefaults($userId);
    }
}
