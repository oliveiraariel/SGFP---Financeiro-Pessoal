<?php

declare(strict_types=1);

namespace SGFP\Domain\Models;

use SGFP\Domain\Enums\AccountRole;

final class Account
{
    public function __construct(
        public readonly ?int $id,
        public readonly int $userId,
        public readonly string $name,
        public readonly AccountRole $role,
        public readonly \DateTimeImmutable $createdAt,
    ) {
    }

    public static function createPrincipal(int $userId, string $name, \DateTimeImmutable $now): self
    {
        return new self(null, $userId, $name, AccountRole::PRINCIPAL, $now);
    }

    public static function createSecondary(int $userId, string $name, \DateTimeImmutable $now): self
    {
        return new self(null, $userId, $name, AccountRole::SECUNDARIA, $now);
    }

    public function isPrincipal(): bool
    {
        return $this->role === AccountRole::PRINCIPAL;
    }

    public function withId(int $id): self
    {
        return new self($id, $this->userId, $this->name, $this->role, $this->createdAt);
    }
}
