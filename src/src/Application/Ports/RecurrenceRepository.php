<?php

declare(strict_types=1);

namespace SGFP\Application\Ports;

use SGFP\Domain\Models\Recurrence;

interface RecurrenceRepository
{
    public function save(Recurrence $recurrence): Recurrence;

    public function findById(int $id, int $userId): ?Recurrence;

    /**
     * @return Recurrence[]
     */
    public function findAllByUser(int $userId): array;
}
