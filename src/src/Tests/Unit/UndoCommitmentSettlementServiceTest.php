<?php

declare(strict_types=1);

namespace SGFP\Tests\Unit;

use PHPUnit\Framework\TestCase;
use SGFP\Application\Ports\CommitmentRepository;
use SGFP\Application\Ports\EntryRepository;
use SGFP\Application\Ports\TransactionManager;
use SGFP\Application\Ports\UserContext;
use SGFP\Application\Services\UndoCommitmentSettlementService;
use SGFP\Domain\Enums\CommitmentNature;
use SGFP\Domain\Enums\CommitmentStatus;
use SGFP\Domain\Enums\EntryEffectType;
use SGFP\Domain\Enums\EntryOrigin;
use SGFP\Domain\Enums\EntryState;
use SGFP\Domain\Models\Commitment;
use SGFP\Domain\Models\Entry;

final class UndoCommitmentSettlementServiceTest extends TestCase
{
    public function testUndoesSettledCommitment(): void
    {
        $commitments = $this->createMock(CommitmentRepository::class);
        $entries = $this->createMock(EntryRepository::class);
        $context = $this->createMock(UserContext::class);
        $tx = $this->createStub(TransactionManager::class);

        $context->method('requireUserId')->willReturn(1);
        $tx->method('transactional')->willReturnCallback(fn (callable $action) => $action());

        $commitment = new Commitment(
            10, 1, null, null, 'Salário', 1000.0, CommitmentNature::ENTRADA,
            new \DateTimeImmutable('2026-09-01'), CommitmentStatus::EFETIVADO, new \DateTimeImmutable()
        );
        $entry = new Entry(
            100, 1, 1, 10, EntryOrigin::COMPROMISSO, 'Salário', 1000.0,
            EntryEffectType::ENTRADA, new \DateTimeImmutable(), null,
            EntryState::ATIVO, new \DateTimeImmutable(), null
        );

        $commitments->method('findById')->willReturn($commitment);
        $entries->method('findByCommitmentId')->willReturn($entry);

        $entries->expects($this->once())
            ->method('save')
            ->with($this->callback(fn (Entry $e): bool => $e->state === EntryState::DESFEITO))
            ->willReturnArgument(0);

        $commitments->expects($this->once())
            ->method('save')
            ->with($this->callback(fn (Commitment $c): bool => $c->status === CommitmentStatus::PENDENTE))
            ->willReturnArgument(0);

        (new UndoCommitmentSettlementService($commitments, $entries, $tx, $context))->execute(10);
    }
}
