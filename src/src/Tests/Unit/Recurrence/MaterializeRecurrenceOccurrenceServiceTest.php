<?php

declare(strict_types=1);

namespace SGFPTests\Unit\Recurrence;

use PHPUnit\Framework\TestCase;
use SGFP\Application\Ports\CommitmentRepository;
use SGFP\Application\Ports\RecurrenceRepository;
use SGFP\Application\Ports\TransactionManager;
use SGFP\Application\Ports\UserContext;
use SGFP\Application\Services\MaterializeRecurrenceOccurrenceService;
use SGFP\Domain\Enums\CommitmentNature;
use SGFP\Domain\Models\Commitment;
use SGFP\Domain\Models\Recurrence;

final class MaterializeRecurrenceOccurrenceServiceTest extends TestCase
{
    /** @dataProvider finiteAndIndefiniteRecurrences */
    public function testMaterializesAllowedMonthForFiniteAndIndefiniteRecurrence(
        ?int $monthsCount,
        ?string $endedIn,
        string $month,
    ): void {
        [$service, $commitments] = $this->serviceFor(
            new Recurrence(
                7,
                3,
                new \DateTimeImmutable('2026-09-01'),
                $monthsCount,
                $endedIn === null ? null : new \DateTimeImmutable($endedIn),
                new \DateTimeImmutable('2026-09-01'),
            )
        );

        $result = $service->execute(7, $month);

        self::assertSame(CommitmentNature::SAIDA, $result->nature);
        self::assertSame($month, $result->referenceMonth->format('Y-m-d'));
    }

    public static function finiteAndIndefiniteRecurrences(): iterable
    {
        yield 'finite recurrence before exclusive end' => [3, null, '2026-11-01'];
        yield 'indefinite recurrence' => [null, null, '2036-12-01'];
        yield 'explicit ended month' => [null, '2026-11-01', '2026-11-01'];
    }

    public function testRejectsMonthAfterFiniteRecurrence(): void
    {
        [$service] = $this->serviceFor(
            new Recurrence(
                7,
                3,
                new \DateTimeImmutable('2026-08-01'),
                3,
                null,
                new \DateTimeImmutable('2026-08-01'),
            ),
            new \DateTimeImmutable('2026-09-01'),
        );

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('excede a duração');
        $service->execute(7, '2026-12-01');
    }

    public function testRejectsMonthAfterIndefiniteRecurrenceEndedIn(): void
    {
        [$service] = $this->serviceFor($this->recurrence(null, '2026-11-01'));

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('posterior ao encerramento');
        $service->execute(7, '2026-12-01');
    }

    public function testStartsInCommitmentReferenceMonthEvenWhenRecurrenceWasCreatedEarlier(): void
    {
        [$service] = $this->serviceFor($this->recurrence(3, null));

        $result = $service->execute(7, '2026-09-01');

        self::assertSame('2026-09-01', $result->referenceMonth->format('Y-m-d'));
    }

    /** @dataProvider endOfMonthDates */
    public function testClampsCommitmentDayToTheLastDayOfShorterMonth(string $baseDate, string $month, string $expected): void
    {
        [$service] = $this->serviceFor($this->recurrence(3, null), new \DateTimeImmutable($baseDate));

        $result = $service->execute(7, $month);

        self::assertSame($expected, $result->referenceMonth->format('Y-m-d'));
    }

    public static function endOfMonthDates(): iterable
    {
        yield 'January thirty first to February' => ['2026-01-31', '2026-02-01', '2026-02-28'];
        yield 'January thirty first to leap February' => ['2028-01-31', '2028-02-01', '2028-02-29'];
        yield 'March thirty first to April' => ['2026-03-31', '2026-04-01', '2026-04-30'];
    }

    private function recurrence(?int $monthsCount, ?string $endedIn): Recurrence
    {
        return new Recurrence(
            7,
            3,
            new \DateTimeImmutable('2026-09-01'),
            $monthsCount,
            $endedIn === null ? null : new \DateTimeImmutable($endedIn),
            new \DateTimeImmutable('2026-09-01'),
        );
    }

    /** @return array{MaterializeRecurrenceOccurrenceService, CommitmentRepository&\PHPUnit\Framework\MockObject\MockObject} */
    private function serviceFor(Recurrence $recurrence, ?\DateTimeImmutable $baseMonth = null): array
    {
        $recurrences = $this->createMock(RecurrenceRepository::class);
        $commitments = $this->createMock(CommitmentRepository::class);
        $context = $this->createMock(UserContext::class);
        $transaction = $this->createStub(TransactionManager::class);

        $context->method('requireUserId')->willReturn(3);
        $recurrences->method('findById')->willReturn($recurrence);
        $commitments->method('findByRecurrenceIdAndMonth')->willReturn(null);
        $commitments->method('findFirstByRecurrenceId')->willReturn(
            Commitment::create(
                3,
                null,
                'Mensalidade',
                '100.00',
                CommitmentNature::SAIDA,
                $baseMonth ?? $recurrence->startsIn,
                $recurrence->createdAt,
                $recurrence->id,
            )->withId(20)
        );
        $commitments->method('save')->willReturnCallback(fn (Commitment $commitment): Commitment => $commitment->withId(21));
        $transaction->method('transactional')->willReturnCallback(fn (callable $action) => $action());

        return [
            new MaterializeRecurrenceOccurrenceService($recurrences, $commitments, $transaction, $context),
            $commitments,
        ];
    }
}
