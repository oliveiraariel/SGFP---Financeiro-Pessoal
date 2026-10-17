<?php

declare(strict_types=1);

namespace SGFP\Tests\Unit\Commitment;

use PHPUnit\Framework\TestCase;
use SGFP\Application\Ports\CommitmentRepository;
use SGFP\Application\Ports\UserContext;
use SGFP\Application\Services\ListCommitmentsService;
use SGFP\Domain\Enums\CommitmentNature;
use SGFP\Domain\Enums\CommitmentStatus;
use SGFP\Domain\Models\Commitment;

final class ListCommitmentsServiceTest extends TestCase
{
    public function testOverviewTreatsOnlyTheCanonicalNotFoundTombstoneAsAnAbsentOccurrence(): void
    {
        $source = file_get_contents(dirname(__DIR__, 4) . '/assets/app.js');

        self::assertIsString($source);
        self::assertStringContainsString("options.tombstoneNotFound === true && error.status === 404 && error.code === 'NOT_FOUND'", $source);
        self::assertStringContainsString('{ tombstoneNotFound: true, outOfRangeRecurrenceIsEmpty: true }', $source);
        self::assertStringContainsString('occurrences.filter(Boolean)', $source);
    }

    public function testOverviewTreatsOnlyOutOfRangeRecurringOccurrencesAsEmpty(): void
    {
        $source = file_get_contents(dirname(__DIR__, 4) . '/assets/app.js');

        self::assertIsString($source);
        self::assertStringContainsString(
            "options.outOfRangeRecurrenceIsEmpty === true && error.status === 400 && error.code === 'VALIDATION_ERROR'",
            $source,
        );
        self::assertStringContainsString('{ tombstoneNotFound: true, outOfRangeRecurrenceIsEmpty: true }', $source);
    }

    public function testMonthFilterIncludesEveryCommitmentDayInTheSelectedMonth(): void
    {
        $repository = $this->createMock(CommitmentRepository::class);
        $context = $this->createMock(UserContext::class);
        $context->method('requireUserId')->willReturn(7);
        $repository->method('findAllByUser')->willReturn([
            $this->commitment('2026-09-01'),
            $this->commitment('2026-09-30'),
            $this->commitment('2026-10-01'),
        ]);

        $result = (new ListCommitmentsService($repository, $context))->execute('2026-09-01');

        self::assertCount(2, $result['items']);
        self::assertSame(2, $result['pagination']['total']);
    }

    public function testOperationalListsExcludeSoftDeletedCommitments(): void
    {
        $repository = $this->createMock(CommitmentRepository::class);
        $context = $this->createMock(UserContext::class);
        $context->method('requireUserId')->willReturn(7);
        $repository->method('findAllByUser')->willReturn([
            $this->commitment('2026-09-01'),
            new Commitment(2, 7, null, null, 'Excluído', '20.00', CommitmentNature::SAIDA, new \DateTimeImmutable('2026-09-02'), CommitmentStatus::EXCLUIDO, new \DateTimeImmutable()),
        ]);

        $result = (new ListCommitmentsService($repository, $context))->execute('2026-09-01');

        self::assertCount(1, $result['items']);
        self::assertSame(1, $result['pagination']['total']);
        self::assertSame(CommitmentStatus::PENDENTE, $result['items'][0]->status);
    }

    private function commitment(string $date): Commitment
    {
        return new Commitment(1, 7, null, null, 'Conta', '10.00', CommitmentNature::SAIDA, new \DateTimeImmutable($date), CommitmentStatus::PENDENTE, new \DateTimeImmutable());
    }
}
