<?php

declare(strict_types=1);

namespace SGFP\Domain\Models;

use SGFP\Domain\Enums\CommitmentType;

final class Entry
{
    public function __construct(
        public readonly ?int $id,
        public readonly int $userId,
        public readonly int $accountId,
        public readonly ?int $commitmentId,
        public readonly ?int $transferId,
        public readonly string $description,
        public readonly float $amount,
        public readonly CommitmentType $type,
        public readonly \DateTimeImmutable $competenceDate,
        public readonly \DateTimeImmutable $settledAt,
        public readonly \DateTimeImmutable $createdAt,
    ) {
    }

    public static function fromCommitment(Commitment $commitment, \DateTimeImmutable $now): self
    {
        return new self(
            null,
            $commitment->userId,
            $commitment->accountId,
            $commitment->id,
            null,
            $commitment->description,
            $commitment->amount,
            $commitment->type,
            $commitment->dueDate,
            $now,
            $now,
        );
    }

    public function withId(int $id): self
    {
        return new self(
            $id,
            $this->userId,
            $this->accountId,
            $this->commitmentId,
            $this->transferId,
            $this->description,
            $this->amount,
            $this->type,
            $this->competenceDate,
            $this->settledAt,
            $this->createdAt,
        );
    }
}
