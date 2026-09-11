<?php

declare(strict_types=1);

namespace SGFP\Domain\Models;

use SGFP\Domain\Enums\EntryEffectType;
use SGFP\Domain\Enums\EntryOrigin;
use SGFP\Domain\Enums\EntryState;

final class Entry
{
    public function __construct(
        public readonly ?int $id,
        public readonly int $userId,
        public readonly int $accountId,
        public readonly ?int $commitmentId,
        public readonly EntryOrigin $origin,
        public readonly string $name,
        public readonly float $amount,
        public readonly EntryEffectType $effectType,
        public readonly \DateTimeImmutable $settledAt,
        public readonly ?string $description,
        public readonly EntryState $state,
        public readonly \DateTimeImmutable $createdAt,
        public readonly ?\DateTimeImmutable $undoneAt,
    ) {
    }

    public static function fromCommitment(Commitment $commitment, int $accountId, \DateTimeImmutable $now): self
    {
        return new self(
            null,
            $commitment->userId,
            $accountId,
            $commitment->id,
            EntryOrigin::COMPROMISSO,
            $commitment->name,
            $commitment->amount,
            EntryEffectType::fromCommitmentNature($commitment->nature),
            $now,
            null,
            EntryState::ATIVO,
            $now,
            null,
        );
    }

    public function withId(int $id): self
    {
        return new self(
            $id,
            $this->userId,
            $this->accountId,
            $this->commitmentId,
            $this->origin,
            $this->name,
            $this->amount,
            $this->effectType,
            $this->settledAt,
            $this->description,
            $this->state,
            $this->createdAt,
            $this->undoneAt,
        );
    }

    public function withUndone(\DateTimeImmutable $now): self
    {
        return new self(
            $this->id,
            $this->userId,
            $this->accountId,
            $this->commitmentId,
            $this->origin,
            $this->name,
            $this->amount,
            $this->effectType,
            $this->settledAt,
            $this->description,
            EntryState::DESFEITO,
            $this->createdAt,
            $now,
        );
    }
}
