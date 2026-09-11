<?php

declare(strict_types=1);

namespace SGFP\Domain\Models;

final class Category
{
    public function __construct(
        public readonly ?int $id,
        public readonly int $userId,
        public readonly string $name,
        public readonly \DateTimeImmutable $createdAt,
    ) {
    }

    public static function create(int $userId, string $name, \DateTimeImmutable $now): self
    {
        return new self(null, $userId, $name, $now);
    }

    public function withId(int $id): self
    {
        return new self($id, $this->userId, $this->name, $this->createdAt);
    }
}
