<?php

declare(strict_types=1);

namespace SGFP\Domain\Models;

final class Recurrence
{
    public function __construct(
        public readonly ?int $id,
        public readonly int $userId,
        public readonly \DateTimeImmutable $startsIn,
        public readonly ?int $monthsCount,
        public readonly ?\DateTimeImmutable $endedIn,
        public readonly \DateTimeImmutable $createdAt,
    ) {
    }

    public static function create(
        int $userId,
        \DateTimeImmutable $startsIn,
        ?int $monthsCount,
        \DateTimeImmutable $now,
    ): self {
        return new self(
            null,
            $userId,
            $startsIn,
            $monthsCount,
            null,
            $now,
        );
    }

    public function withId(int $id): self
    {
        return new self(
            $id,
            $this->userId,
            $this->startsIn,
            $this->monthsCount,
            $this->endedIn,
            $this->createdAt,
        );
    }
}
