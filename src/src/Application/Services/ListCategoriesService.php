<?php

declare(strict_types=1);

namespace SGFP\Application\Services;

use SGFP\Application\Ports\CategoryRepository;
use SGFP\Application\Ports\UserContext;

final class ListCategoriesService
{
    public function __construct(
        private readonly CategoryRepository $repository,
        private readonly UserContext $userContext,
    ) {
    }

    public function execute(): array
    {
        $userId = $this->userContext->requireUserId();
        $this->userContext->requireCapability('use_sgfp');

        return $this->repository->findAllByUser($userId);
    }
}
