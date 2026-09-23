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

        $start = preg_match('/^\d{4}-\d{2}-01$/', $month) === 1
            ? \DateTimeImmutable::createFromFormat('!Y-m-d', $month)
            : false;
        $errors = \DateTimeImmutable::getLastErrors();
        if ($start === false || ($errors !== false && ($errors['warning_count'] > 0 || $errors['error_count'] > 0)) || $start->format('Y-m-d') !== $month) {
            throw new \InvalidArgumentException('O mês deve estar no formato YYYY-MM-01.');
        }
        $end = $start->modify('last day of this month 23:59:59');

        return $this->entryRepository->findActiveEntriesByUserAndPeriod($userId, $start, $end);
    }
}
