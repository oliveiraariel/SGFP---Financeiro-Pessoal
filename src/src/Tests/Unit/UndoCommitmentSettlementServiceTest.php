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
use SGFP\Domain\Enums\CommitmentType;
use SGFP\Domain\Enums\EntryEffectType;
use SGFP\Domain\Enums\EntryOrigin;
use SGFP\Domain\Enums\EntryState;
use SGFP\Domain\Models\Commitment;
use SGFP\Domain\Models\Entry;

final class UndoCommitmentSettlementServiceTest extends TestCase
{
    public function testUndoesSettledCommitment(): void
    {
        $commitmentRepository = $this->createMock(CommitmentRepository::class);
        $entryRepository = $this->createMock(EntryRepository::class);
        $transactionManager = $this->createStub(TransactionManager::class);
        $userContext = $this->createMock(UserContext::class);

        $userContext->method('requireCapability');
        $userContext->method('requireUserId')->willReturn(1);

        $commitment = new Commitment(
            10,
            1,
            null,
            null,
            'Salário',
            1000.00,
            CommitmentType::PADRAO,
            CommitmentNature::ENTRADA,
            new \DateTimeImmutable('2026-09-01'),
            CommitmentStatus::EFETIVADO,
            new \DateTimeImmutable(),
        );
        $commitmentRepository->method('findById')->with(10, 1)->willReturn($commitment);

        $entry = new Entry(
            100,
            1,
            1,
            10,
            EntryOrigin::COMPROMISSO,
            'Salário',
            1000.00,
            EntryEffectType::ENTRADA,
            new \DateTimeImmutable(),
            null,
            EntryState::ATIVO,
            new \DateTimeImmutable(),
            null,
        );
        $entryRepository->method('findByCommitmentId')->with(10, 1)->willReturn($entry);

        $capturedEntry = null;
        $entryRepository->expects($this->once())->method('save')->willReturnCallback(function (Entry $e) use (&$capturedEntry) {
            $capturedEntry = $e;
            return $e;
        });

        $capturedCommitment = null;
        $commitmentRepository->expects($this->once())->method('save')->willReturnCallback(function (Commitment $c) use (&$capturedCommitment) {
            $capturedCommitment = $c;
            return $c;
        });

        $transactionManager->method('transactional')->willReturnCallback(function (callable $action) {
            return $action();
        });

        $service = new UndoCommitmentSettlementService($commitmentRepository, $entryRepository, $transactionManager, $userContext);
        $service->execute(10);

        $this->assertNotNull($capturedEntry);
        $this->assertSame(EntryState::DESFEITO, $capturedEntry->state);
        $this->assertNotNull($capturedEntry->undoneAt);

        $this->assertNotNull($capturedCommitment);
        $this->assertSame(CommitmentStatus::PENDENTE, $capturedCommitment->status);
    }

    public function testFailsWhenCommitmentIsNotSettled(): void
    {
        $commitmentRepository = $this->createMock(CommitmentRepository::class);
        $entryRepository = $this->createStub(EntryRepository::class);
        $transactionManager = $this->createStub(TransactionManager::class);
        $userContext = $this->createMock(UserContext::class);

        $userContext->method('requireCapability');
        $userContext->method('requireUserId')->willReturn(1);

        $commitment = new Commitment(
            10,
            1,
            null,
            null,
            'Salário',
            1000.00,
            CommitmentType::PADRAO,
            CommitmentNature::ENTRADA,
            new \DateTimeImmutable('2026-09-01'),
            CommitmentStatus::PENDENTE,
            new \DateTimeImmutable(),
        );
        $commitmentRepository->method('findById')->willReturn($commitment);

        $service = new UndoCommitmentSettlementService($commitmentRepository, $entryRepository, $transactionManager, $userContext);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('efetivado');
        $service->execute(10);
    }
}
