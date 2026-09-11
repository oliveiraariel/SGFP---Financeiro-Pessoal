<?php

declare(strict_types=1);

namespace SGFP\Application\Ports;

use SGFP\Domain\Models\Transfer;

interface TransferRepository
{
    public function save(Transfer $transfer): void;

    public function findByCommitmentId(int $commitmentId, int $userId): ?Transfer;

    /**
     * @return Transfer[]
     */
    public function findAllByUser(int $userId): array;
}
