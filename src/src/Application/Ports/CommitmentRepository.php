<?php

declare(strict_types=1);

namespace SGFP\Application\Ports;

use SGFP\Domain\Models\Commitment;

interface CommitmentRepository
{
    public function save(Commitment $commitment): Commitment;

    public function findById(int $id, int $userId): ?Commitment;

    public function findAllByUser(int $userId): array;

    public function findByRecurrenceIdAndMonth(int $recurrenceId, string $month, int $userId): ?Commitment;

    public function findFirstByRecurrenceId(int $recurrenceId, int $userId): ?Commitment;
}
