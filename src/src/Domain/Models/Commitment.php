<?php

declare(strict_types=1);

namespace SGFP\Domain\Models;

use SGFP\Domain\Enums\CommitmentStatus;
use SGFP\Domain\Enums\CommitmentType;

final class Commitment
{
    public function __construct(
        public readonly ?int $id,
        public readonly int $userId,
        public readonly int $accountId,
        public readonly ?int $categoryId,
        public readonly string $description,
        public readonly float $amount,
        public readonly CommitmentType $type,
        public readonly CommitmentStatus $status,
        public readonly \DateTimeImmutable $dueDate,
        public readonly ?\DateTimeImmutable $settledAt,
        public readonly \DateTimeImmutable $createdAt,
    ) {
    }

    public static function create(
        int $userId,
        int $accountId,
        ?int $categoryId,
        string $description,
        float $amount,
        CommitmentType $type,
        \DateTimeImmutable $dueDate,
        \DateTimeImmutable $now,
    ): self {
        return new self(
            null,
            $userId,
            $accountId,
            $categoryId,
            $description,
            $amount,
            $type,
            CommitmentStatus::PENDENTE,
            $dueDate,
            null,
            $now,
        );
    }

    public function settle(\DateTimeImmutable $settledAt): self
    {
        return new self(
            $this->id,
            $this->userId,
            $this->accountId,
            $this->categoryId,
            $this->description,
            $this->amount,
            $this->type,
            CommitmentStatus::EFETIVADO,
            $this->dueDate,
            $settledAt,
            $this->createdAt,
        );
    }

    public function withId(int $id): self
    {
        return new self(
            $id,
            $this->userId,
            $this->accountId,
            $this->categoryId,
            $this->description,
            $this->amount,
            $this->type,
            $this->status,
            $this->dueDate,
            $this->settledAt,
            $this->createdAt,
        );
    }
}
