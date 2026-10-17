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
use SGFP\Application\Services\EndRecurrenceFromOccurrenceService;
use SGFP\Application\Services\SettleRecurrenceOccurrenceService;
use SGFP\Application\Services\UndoRecurrenceOccurrenceSettlementService;
use SGFP\Domain\Enums\CommitmentNature;
use SGFP\Domain\Models\Commitment;
use SGFP\Domain\Models\Recurrence;
use SGFP\Domain\Models\Entry;
use SGFP\Domain\Models\Account;
use SGFP\Domain\Enums\EntryEffectType;
use SGFP\Domain\Enums\EntryOrigin;
use SGFP\Domain\Enums\EntryState;

final class RecurrenceOccurrenceServicesTest extends TestCase
{
    public function testEndingFromPendingOccurrenceDeletesItAndEndsTheSeriesInTheSelectedMonth(): void
    {
        $recurrences = $this->createMock(RecurrenceRepository::class);
        $commitments = $this->createMock(CommitmentRepository::class);
        $context = $this->createMock(UserContext::class);
        $tx = $this->createStub(TransactionManager::class);
        $context->method('requireUserId')->willReturn(1);
        $tx->method('transactional')->willReturnCallback(fn (callable $action) => $action());
        $recurrence = new Recurrence(5, 1, new \DateTimeImmutable('2026-01-01'), null, null, new \DateTimeImmutable());
        $occurrence = Commitment::create(1, null, 'Aluguel', '1000.00', CommitmentNature::SAIDA, new \DateTimeImmutable('2026-03-10'), new \DateTimeImmutable(), 5)->withId(21);
        $recurrences->expects($this->once())->method('findById')->with(5, 1)->willReturn($recurrence);
        $commitments->method('findFirstByRecurrenceId')->willReturn($occurrence);
        $commitments->expects($this->exactly(2))->method('findByRecurrenceIdAndMonth')->with(5, '2026-03-01', 1)->willReturn($occurrence);
        $commitments->expects($this->once())->method('save')->with($this->callback(fn (Commitment $item) => $item->status->value === 'EXCLUIDO'))->willReturn($occurrence->delete());
        $recurrences->expects($this->once())->method('save')->with($this->callback(fn (Recurrence $item) => $item->endedIn?->format('Y-m-d') === '2026-03-01'))->willReturn($recurrence->endIn(new \DateTimeImmutable('2026-03-01')));
        $materializer = new MaterializeRecurrenceOccurrenceService($recurrences, $commitments, $tx, $context);

        (new EndRecurrenceFromOccurrenceService($materializer, $recurrences, $commitments, $tx, $context))->execute(5, '2026-03-01');
    }

    public function testEndingRollsBackTheMaterializedOccurrenceWhenEndingPersistenceFails(): void
    {
        $recurrences = $this->createMock(RecurrenceRepository::class);
        $commitments = $this->createMock(CommitmentRepository::class);
        $context = $this->createStub(UserContext::class);
        $context->method('requireUserId')->willReturn(1);
        $recurrence = new Recurrence(5, 1, new \DateTimeImmutable('2026-01-01'), null, null, new \DateTimeImmutable());
        $base = Commitment::create(1, null, 'Aluguel', '1000.00', CommitmentNature::SAIDA, new \DateTimeImmutable('2026-01-10'), new \DateTimeImmutable(), 5)->withId(20);
        $materialized = Commitment::create(1, null, 'Aluguel', '1000.00', CommitmentNature::SAIDA, new \DateTimeImmutable('2026-03-10'), new \DateTimeImmutable(), 5)->withId(21);
        $committed = false;
        $tx = new class($committed) implements TransactionManager {
            public bool $committed = false;
            public bool $rolledBack = false;
            public function __construct(bool $committed) { $this->committed = $committed; }
            public function begin(): void {}
            public function commit(): void { $this->committed = true; }
            public function rollback(): void { $this->rolledBack = true; }
            public function transactional(callable $action): mixed { try { $result = $action(); $this->commit(); return $result; } catch (\Throwable $e) { $this->rollback(); throw $e; } }
        };
        $recurrences->method('findById')->willReturn($recurrence);
        $commitments->method('findFirstByRecurrenceId')->willReturn($base);
        $commits = 0;
        $commitments->method('findByRecurrenceIdAndMonth')->willReturnCallback(function () use (&$commits, $materialized): ?Commitment { return $commits++ === 0 ? null : $materialized; });
        $commitments->method('save')->willReturnCallback(function (Commitment $commitment) use ($materialized): Commitment { if ($commitment->status === \SGFP\Domain\Enums\CommitmentStatus::EXCLUIDO) { throw new \RuntimeException('persistence failure'); } return $materialized; });
        $materializer = new MaterializeRecurrenceOccurrenceService($recurrences, $commitments, $tx, $context);

        try {
            (new EndRecurrenceFromOccurrenceService($materializer, $recurrences, $commitments, $tx, $context))->execute(5, '2026-03-01');
            self::fail('Expected persistence failure.');
        } catch (\RuntimeException $e) {
            self::assertSame('persistence failure', $e->getMessage());
        }
        self::assertFalse($tx->committed);
        self::assertTrue($tx->rolledBack);
    }

    public function testEndingCancelsPersistedFuturePendingOccurrencesButPreservesSettledOnes(): void
    {
        $recurrences = $this->createMock(RecurrenceRepository::class);
        $commitments = $this->createMock(CommitmentRepository::class);
        $context = $this->createMock(UserContext::class);
        $tx = $this->createStub(TransactionManager::class);
        $context->method('requireUserId')->willReturn(1);
        $tx->method('transactional')->willReturnCallback(fn (callable $action) => $action());
        $recurrence = new Recurrence(5, 1, new \DateTimeImmutable('2026-09-01'), null, null, new \DateTimeImmutable());
        $selected = Commitment::create(1, null, 'Escola', '100.00', CommitmentNature::SAIDA, new \DateTimeImmutable('2026-11-10'), new \DateTimeImmutable(), 5)->withId(21);
        $future = Commitment::create(1, null, 'Escola', '100.00', CommitmentNature::SAIDA, new \DateTimeImmutable('2026-12-10'), new \DateTimeImmutable(), 5)->withId(22);
        $commitments->method('findFirstByRecurrenceId')->willReturn($selected);
        $commitments->method('findByRecurrenceIdAndMonth')->willReturn($selected);
        $commitments->expects($this->exactly(2))->method('save')->with($this->callback(
            fn (Commitment $item): bool => $item->status === \SGFP\Domain\Enums\CommitmentStatus::EXCLUIDO
        ))->willReturnCallback(fn (Commitment $item): Commitment => $item);
        $commitments->expects($this->once())->method('findPendingByRecurrenceIdFromMonth')->with(5, '2026-11-01', 1)->willReturn([$future]);
        $recurrences->method('findById')->willReturn($recurrence);
        $recurrences->expects($this->once())->method('save')->with($this->callback(fn (Recurrence $item): bool => $item->endedIn?->format('Y-m-d') === '2026-11-01'))->willReturn($recurrence);
        $materializer = new MaterializeRecurrenceOccurrenceService($recurrences, $commitments, $tx, $context);

        (new EndRecurrenceFromOccurrenceService($materializer, $recurrences, $commitments, $tx, $context))->execute(5, '2026-11-01');
    }

    public function testEndingRefusesSettledOccurrenceUntilItIsUndone(): void
    {
        $recurrences = $this->createMock(RecurrenceRepository::class);
        $commitments = $this->createMock(CommitmentRepository::class);
        $context = $this->createMock(UserContext::class);
        $tx = $this->createStub(TransactionManager::class);
        $context->method('requireUserId')->willReturn(1);
        $tx->method('transactional')->willReturnCallback(fn (callable $action) => $action());
        $recurrences->method('findById')->willReturn(new Recurrence(5, 1, new \DateTimeImmutable('2026-01-01'), null, null, new \DateTimeImmutable()));
        $settled = Commitment::create(1, null, 'Aluguel', '1000.00', CommitmentNature::SAIDA, new \DateTimeImmutable('2026-03-10'), new \DateTimeImmutable(), 5)->withId(21)->settle();
        $commitments->method('findFirstByRecurrenceId')->willReturn($settled);
        $commitments->method('findByRecurrenceIdAndMonth')->willReturn($settled);
        $materializer = new MaterializeRecurrenceOccurrenceService($recurrences, $commitments, $tx, $context);

        $this->expectExceptionCode(409);
        (new EndRecurrenceFromOccurrenceService($materializer, $recurrences, $commitments, $tx, $context))->execute(5, '2026-03-01');
    }

    public function testMaterializeDoesNotReactivateMonthsAfterAnEndedRecurrence(): void
    {
        $recurrences = $this->createMock(RecurrenceRepository::class);
        $commitments = $this->createMock(CommitmentRepository::class);
        $context = $this->createMock(UserContext::class);
        $context->method('requireUserId')->willReturn(1);
        $tx = $this->createStub(TransactionManager::class);
        $tx->method('transactional')->willReturnCallback(fn (callable $action) => $action());
        $recurrences->method('findById')->willReturn(new Recurrence(5, 1, new \DateTimeImmutable('2026-01-01'), null, new \DateTimeImmutable('2026-03-01'), new \DateTimeImmutable()));
        $commitments->method('findFirstByRecurrenceId')->willReturn(Commitment::create(1, null, 'Aluguel', '1000.00', CommitmentNature::SAIDA, new \DateTimeImmutable('2026-01-10'), new \DateTimeImmutable(), 5)->withId(21));
        $commitments->expects($this->never())->method('save');

        $this->expectException(\InvalidArgumentException::class);
        (new MaterializeRecurrenceOccurrenceService($recurrences, $commitments, $tx, $context))->execute(5, '2026-04-01');
    }
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
        $tx = $this->createStub(TransactionManager::class);
        $tx->method('transactional')->willReturnCallback(fn (callable $action) => $action());
        $service = new MaterializeRecurrenceOccurrenceService(
            $this->createMock(RecurrenceRepository::class),
            $this->createMock(CommitmentRepository::class),
            $tx,
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

    public function testSettlingAnAlreadySettledOccurrenceIsIdempotent(): void
    {
        $recurrences = $this->createMock(RecurrenceRepository::class);
        $commitments = $this->createMock(CommitmentRepository::class);
        $entries = $this->createMock(EntryRepository::class);
        $accounts = $this->createMock(\SGFP\Application\Ports\AccountRepository::class);
        $context = $this->createMock(UserContext::class);
        $tx = $this->createStub(TransactionManager::class);
        $context->method('requireUserId')->willReturn(1);
        $context->method('requireCapability');
        $tx->method('transactional')->willReturnCallback(fn (callable $action) => $action());

        $recurrence = new Recurrence(5, 1, new \DateTimeImmutable('2026-01-01'), null, null, new \DateTimeImmutable());
        $commitment = Commitment::create(1, null, 'Aluguel', '1000.00', CommitmentNature::SAIDA, new \DateTimeImmutable('2026-03-10'), new \DateTimeImmutable(), 5)->withId(21)->settle();
        $entry = new Entry(31, 1, 9, 21, EntryOrigin::COMPROMISSO, 'Aluguel', '1000.00', EntryEffectType::SAIDA, new \DateTimeImmutable('2026-03-10'), null, EntryState::ATIVO, new \DateTimeImmutable(), null);

        $recurrences->method('findById')->willReturn($recurrence);
        $commitments->method('findFirstByRecurrenceId')->willReturn($commitment);
        $commitments->method('findByRecurrenceIdAndMonth')->willReturn($commitment);
        $commitments->method('findById')->willReturn($commitment);
        $entries->method('findByCommitmentId')->willReturn($entry);

        $result = (new SettleRecurrenceOccurrenceService(
            new MaterializeRecurrenceOccurrenceService($recurrences, $commitments, $tx, $context),
            $commitments,
            $entries,
            $accounts,
            $tx,
            $context,
        ))->execute(5, '2026-03-01');

        self::assertSame(31, $result->id);
        self::assertSame(EntryState::ATIVO, $result->state);
    }
}
