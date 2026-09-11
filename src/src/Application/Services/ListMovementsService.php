<?php

declare(strict_types=1);

namespace SGFP\Application\Services;

use SGFP\Application\Ports\EntryRepository;
use SGFP\Application\Ports\UserContext;

final class ListMovementsService
{
    public function __construct(
        private readonly EntryRepository $entryRepository,
        private readonly UserContext $userContext,
    ) {
    }

    public function execute(?string $month): array
    {
        $this->userContext->requireCapability('use_sgfp');
        $userId = $this->userContext->requireUserId();

        if ($month === null) {
            return $this->entryRepository->findActiveEntriesByUser($userId);
        }

        $start = new \DateTimeImmutable($month . '-01 00:00:00');
        $end = $start->modify('last day of this month 23:59:59');

        return $this->entryRepository->findActiveEntriesByUserAndPeriod($userId, $start, $end);
    }
}
