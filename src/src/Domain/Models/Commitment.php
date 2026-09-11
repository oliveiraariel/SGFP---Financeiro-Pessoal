<?php

declare(strict_types=1);

namespace SGFP\Domain\Models;

use SGFP\Domain\Enums\CommitmentNature;
use SGFP\Domain\Enums\CommitmentStatus;
use SGFP\Domain\Enums\CommitmentType;

final class Commitment
{
    public function __construct(
        public readonly ?int $id,
        public readonly int $userId,
        public readonly ?int $categoryId,
        public readonly ?int $recurrenceId,
        public readonly string $name,
        public readonly float $amount,
        public readonly CommitmentType $type,
        public readonly CommitmentNature $nature,
        public readonly \DateTimeImmutable $referenceMonth,
        public readonly CommitmentStatus $status,
        public readonly \DateTimeImmutable $createdAt,
    ) {
    }

    public static function create(
        int $userId,
        ?int $categoryId,
        string $name,
        float $amount,
        CommitmentType $type,
        CommitmentNature $nature,
        \DateTimeImmutable $referenceMonth,
        \DateTimeImmutable $now,
    ): self {
        return new self(
            null,
            $userId,
            $categoryId,
            null,
            $name,
            $amount,
            $type,
            $nature,
            $referenceMonth,
            CommitmentStatus::PENDENTE,
            $now,
        );
    }

    public function settle(): self
    {
        return new self(
            $this->id,
            $this->userId,
            $this->categoryId,
            $this->recurrenceId,
            $this->name,
            $this->amount,
            $this->type,
            $this->nature,
            $this->referenceMonth,
            CommitmentStatus::EFETIVADO,
            $this->createdAt,
        );
    }

    public function undoSettlement(): self
    {
        return new self(
            $this->id,
            $this->userId,
            $this->categoryId,
            $this->recurrenceId,
            $this->name,
            $this->amount,
            $this->type,
            $this->nature,
            $this->referenceMonth,
            CommitmentStatus::PENDENTE,
            $this->createdAt,
        );
    }

    public function withId(int $id): self
    {
        return new self(
            $id,
            $this->userId,
            $this->categoryId,
            $this->recurrenceId,
            $this->name,
            $this->amount,
            $this->type,
            $this->nature,
            $this->referenceMonth,
            $this->status,
            $this->createdAt,
        );
    }
}
