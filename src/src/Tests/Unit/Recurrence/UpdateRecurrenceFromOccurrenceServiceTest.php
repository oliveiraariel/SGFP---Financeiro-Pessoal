<?php

declare(strict_types=1);

namespace SGFPTests\Unit\Recurrence;

use PHPUnit\Framework\TestCase;
use SGFP\Application\Ports\CategoryRepository;
use SGFP\Application\Ports\CommitmentRepository;
use SGFP\Application\Ports\TransactionManager;
use SGFP\Application\Ports\UserContext;
use SGFP\Application\Services\UpdateRecurrenceFromOccurrenceService;
use SGFP\Domain\Enums\CommitmentNature;
use SGFP\Domain\Enums\CommitmentStatus;
use SGFP\Domain\Models\Commitment;
use SGFP\Domain\Models\Category;

final class UpdateRecurrenceFromOccurrenceServiceTest extends TestCase
{
    public function testUpdatesPivotAndPendingFutureOccurrencesWithoutTouchingSettledOccurrences(): void
    {
        $pivot = $this->commitment(10, '2026-10-15');
        $future = $this->commitment(11, '2026-11-15');
        $settled = $this->commitment(12, '2026-12-15', CommitmentStatus::EFETIVADO);
        [$service, $commitments, $saved] = $this->service($pivot, [$future]);
        $commitments->expects(self::once())->method('findPendingByRecurrenceIdFromMonth')->with(7, '2026-10-01', 3)->willReturn([$future]);

        $result = $service->execute(7, '2026-10-01', 4, 'Atualizado', '250.00', '2026-10-20', 'current_and_future');

        self::assertSame(10, $result->id);
        $items = $saved->getArrayCopy();
        self::assertCount(2, $items);
        self::assertSame(['Atualizado', 'Atualizado'], array_map(static fn (Commitment $item): string => $item->name, $items));
        self::assertSame(['2026-10-20', '2026-11-15'], array_map(static fn (Commitment $item): string => $item->referenceMonth->format('Y-m-d'), $items));
        self::assertSame(CommitmentStatus::PENDENTE, $items[0]->status);
        self::assertSame(CommitmentStatus::PENDENTE, $items[1]->status);
        self::assertNotContains($settled->id, array_map(static fn (Commitment $item): ?int => $item->id, $items));
    }

    public function testRejectsSettledPivotBeforeAnyPersistence(): void
    {
        $pivot = $this->commitment(10, '2026-10-15', CommitmentStatus::EFETIVADO);
        [$service, $commitments] = $this->service($pivot, []);
        $commitments->expects(self::never())->method('save');

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionCode(409);
        $service->execute(7, '2026-10-01', null, 'Atualizado', '250.00', null, 'current_and_future');
    }

    public function testDoesNotUseACommitmentBelongingToAnotherUser(): void
    {
        [$service, $commitments] = $this->service(null, []);
        $commitments->expects(self::once())->method('findByRecurrenceIdAndMonth')->with(7, '2026-10-01', 3)->willReturn(null);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionCode(404);
        $service->execute(7, '2026-10-01', null, 'Atualizado', '250.00', null, 'current');
    }

    public function testRepeatingTheSameSeriesEditIsIdempotentForPendingRecords(): void
    {
        $pivot = $this->commitment(10, '2026-10-15');
        $future = $this->commitment(11, '2026-11-15');
        [$service, , $saved] = $this->service($pivot, [$future]);

        $service->execute(7, '2026-10-01', null, 'Atualizado', '250.00', '2026-10-20', 'current_and_future');
        $service->execute(7, '2026-10-01', null, 'Atualizado', '250.00', '2026-10-20', 'current_and_future');

        $items = $saved->getArrayCopy();
        self::assertCount(4, $items);
        self::assertSame([10, 11, 10, 11], array_map(static fn (Commitment $item): ?int => $item->id, $items));
    }

    /** @return array{UpdateRecurrenceFromOccurrenceService, CommitmentRepository&\PHPUnit\Framework\MockObject\MockObject, list<Commitment>} */
    private function service(?Commitment $pivot, array $future): array
    {
        $commitments = $this->createMock(CommitmentRepository::class);
        $categories = $this->createMock(CategoryRepository::class);
        $transactions = $this->createStub(TransactionManager::class);
        $context = $this->createMock(UserContext::class);
        $saved = new \ArrayObject();

        $context->method('requireUserId')->willReturn(3);
        $categories->method('findById')->willReturnCallback(static fn (?int $id): ?Category => $id === null ? null : new Category($id, 3, 'Categoria', new \DateTimeImmutable('2026-09-01')));
        $commitments->method('findByRecurrenceIdAndMonth')->willReturn($pivot);
        $commitments->method('findFirstByRecurrenceId')->willReturn($pivot);
        $commitments->method('findPendingByRecurrenceIdFromMonth')->willReturn($future);
        $commitments->method('save')->willReturnCallback(static function (Commitment $item) use ($saved): Commitment {
            $saved->append($item);
            return $item;
        });
        $transactions->method('transactional')->willReturnCallback(static fn (callable $action): mixed => $action());

        return [new UpdateRecurrenceFromOccurrenceService($commitments, $categories, $transactions, $context), $commitments, $saved];
    }

    private function commitment(int $id, string $date, CommitmentStatus $status = CommitmentStatus::PENDENTE): Commitment
    {
        return new Commitment($id, 3, null, 7, 'Original', '100.00', CommitmentNature::SAIDA, new \DateTimeImmutable($date), $status, new \DateTimeImmutable('2026-09-01'));
    }
}
