<?php

declare(strict_types=1);

namespace SGFP\Domain\Models;

final class Transfer
{
    public function __construct(
        public readonly int $commitmentId,
        public readonly int $userId,
        public readonly int $sourceAccountId,
        public readonly int $targetAccountId,
    ) {
    }
}
