<?php

declare(strict_types=1);

namespace SGFP\Application\Services;

use SGFP\Application\Ports\AccountRepository;
use SGFP\Application\Ports\UserContext;

final class ListAccountsService
{
    public function __construct(
        private readonly AccountRepository $repository,
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
