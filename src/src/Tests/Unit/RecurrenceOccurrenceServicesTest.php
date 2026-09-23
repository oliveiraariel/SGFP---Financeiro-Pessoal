<?php

declare(strict_types=1);

namespace SGFP\Tests\Unit;

use PHPUnit\Framework\TestCase;
use SGFP\Application\Ports\CommitmentRepository;
use SGFP\Application\Ports\EntryRepository;
use SGFP\Application\Ports\RecurrenceRepository;
use SGFP\Application\Ports\TransactionManager;
use SGFP\Application\Ports\UserContext;
use SGFP\Application\Services\MaterializeRecurrenceOccurrenceService;
use SGFP\Application\Services\UndoRecurrenceOccurrenceSettlementService;
use SGFP\Domain\Enums\CommitmentNature;
use SGFP\Domain\Models\Commitment;
use SGFP\Domain\Models\Recurrence;

final class RecurrenceOccurrenceServicesTest extends TestCase
{
    public function testMaterializeCreatesOccurrenceWithoutCommitmentType(): void
    {
        $recurrences = $this->createMock(RecurrenceRepository::class);
        $commitments = $this->createMock(CommitmentRepository::class);
        $context = $this->createMock(UserContext::class);
        $tx = $this->createStub(TransactionManager::class);

        $context->method('requireUserId')->willReturn(1);
        $tx->method('transactional')->willReturnCallback(fn (callable $action) => $action());
        $recurrences->method('findById')->willReturn(
            new Recurrence(5, 1, new \DateTimeImmutable('2026-09-01'), 12, null, new \DateTimeImmutable())
        );
        $base = Commitment::create(
            1, null, 'Aluguel', '1000.00', CommitmentNature::SAIDA,
            new \DateTimeImmutable('2026-09-01'), new \DateTimeImmutable(), 5
        )->withId(10);
        $commitments->method('findFirstByRecurrenceId')->willReturn($base);
        $commitments->method('findByRecurrenceIdAndMonth')->willReturn(null);
        $commitments->expects($this->once())->method('save')->willReturnCallback(
            fn (Commitment $c): Commitment => $c->withId(20)
        );

        $result = (new MaterializeRecurrenceOccurrenceService($recurrences, $commitments, $tx, $context))
            ->execute(5, '2026-10-01');

        $this->assertSame(20, $result->id);
        $this->assertSame(CommitmentNature::SAIDA, $result->nature);
    }

    public function testMaterializePreservesIndefiniteRecurrenceForFutureMonthlyOccurrence(): void
    {
        $recurrences = $this->createMock(RecurrenceRepository::class);
        $commitments = $this->createMock(CommitmentRepository::class);
        $context = $this->createMock(UserContext::class);
        $tx = $this->createStub(TransactionManager::class);

        $context->method('requireUserId')->willReturn(1);
        $tx->method('transactional')->willReturnCallback(fn (callable $action) => $action());
        $recurrences->method('findById')->willReturn(
            new Recurrence(7, 1, new \DateTimeImmutable('2026-09-01'), null, null, new \DateTimeImmutable())
        );
        $base = Commitment::create(
            1, null, 'Assinatura', '89.90', CommitmentNature::SAIDA,
            new \DateTimeImmutable('2026-09-15'), new \DateTimeImmutable(), 7
        )->withId(30);
        $commitments->method('findFirstByRecurrenceId')->willReturn($base);
        $commitments->method('findByRecurrenceIdAndMonth')->willReturn(null);
        $commitments->expects($this->once())->method('save')->willReturnCallback(
            fn (Commitment $c): Commitment => $c->withId(31)
        );

        $result = (new MaterializeRecurrenceOccurrenceService($recurrences, $commitments, $tx, $context))
            ->execute(7, '2031-02-01');

        $this->assertSame(31, $result->id);
        $this->assertSame('2031-02-15', $result->referenceMonth->format('Y-m-d'));
    }

    public function testMaterializeRejectsLegacyMonthFormat(): void
    {
        $service = new MaterializeRecurrenceOccurrenceService(
            $this->createMock(RecurrenceRepository::class),
            $this->createMock(CommitmentRepository::class),
            $this->createStub(TransactionManager::class),
            $this->createMock(UserContext::class),
        );

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('O mês deve estar no formato YYYY-MM-01.');
        $service->execute(5, '2026-10');
    }

    public function testUndoRejectsLegacyMonthFormat(): void
    {
        $service = new UndoRecurrenceOccurrenceSettlementService(
            $this->createMock(CommitmentRepository::class),
            $this->createMock(EntryRepository::class),
            $this->createStub(TransactionManager::class),
            $this->createMock(UserContext::class),
        );

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('O mês deve estar no formato YYYY-MM-01.');
        $service->execute(5, '2026-10');
    }
}
