<?php

declare(strict_types=1);

namespace SGFP\Tests\Unit;

if (!class_exists('WP_REST_Request')) {
    final class ReportingRequestStub implements \ArrayAccess
    {
        public function __construct(private array $params) {}
        public function offsetExists(mixed $offset): bool { return array_key_exists($offset, $this->params); }
        public function offsetGet(mixed $offset): mixed { return $this->params[$offset] ?? null; }
        public function offsetSet(mixed $offset, mixed $value): void { $this->params[$offset] = $value; }
        public function offsetUnset(mixed $offset): void { unset($this->params[$offset]); }
    }
    class_alias(ReportingRequestStub::class, 'WP_REST_Request');
}
if (!class_exists('WP_REST_Response')) {
    final class ReportingResponseStub
    {
        public function __construct(private mixed $data = null, private int $status = 200) {}
        public function get_data(): mixed { return $this->data; }
        public function get_status(): int { return $this->status; }
        public function set_data(mixed $data): void { $this->data = $data; }
        public function header(string $name, string $value): void {}
    }
    class_alias(ReportingResponseStub::class, 'WP_REST_Response');
}

use PHPUnit\Framework\TestCase;
use SGFP\Application\Ports\AccountRepository;
use SGFP\Application\Ports\CommitmentRepository;
use SGFP\Application\Ports\EntryRepository;
use SGFP\Application\Ports\RecurrenceRepository;
use SGFP\Application\Ports\UserContext;
use SGFP\Application\Services\GetDashboardService;
use SGFP\Application\Services\ListMovementsService;
use SGFP\Domain\Enums\CommitmentNature;
use SGFP\Domain\Enums\CommitmentStatus;
use SGFP\Domain\Enums\EntryEffectType;
use SGFP\Domain\Enums\EntryOrigin;
use SGFP\Domain\Enums\EntryState;
use SGFP\Domain\Models\Account;
use SGFP\Domain\Models\Commitment;
use SGFP\Domain\Models\Decimal;
use SGFP\Domain\Models\Entry;
use SGFP\Domain\Models\Recurrence;

final class ReportingServicesTest extends TestCase
{
    public function testDashboardControllerReturnsValidationEnvelopeForInvalidMonth(): void
    {
        $service = new GetDashboardService(
            $this->createStub(AccountRepository::class),
            $this->createStub(EntryRepository::class),
            $this->createStub(CommitmentRepository::class),
            $this->createStub(UserContext::class),
        );
        $controller = new \SGFP\REST\Controllers\ReportingController(
            new ListMovementsService(
                $this->createStub(EntryRepository::class),
                $this->createStub(UserContext::class),
            ),
            $service,
        );

        $request = new \WP_REST_Request(['month' => '2026-09']);
        $response = $controller->dashboard($request);

        self::assertSame(400, $response->get_status());
        self::assertSame('VALIDATION_ERROR', $response->get_data()['error']['code']);
    }

    public function testDashboardUsesSingleAccountBalance(): void
    {
        $accounts = $this->createMock(AccountRepository::class);
        $entries = $this->createMock(EntryRepository::class);
        $commitments = $this->createMock(CommitmentRepository::class);
        $context = $this->createMock(UserContext::class);
        $context->method('requireUserId')->willReturn(1);

        $accounts->method('findAllByUser')->willReturn([
            new Account(1, 1, 'Minha Conta', new \DateTimeImmutable()),
        ]);
        $entries->method('findActiveEntriesByUser')->willReturn([
            $this->entry('1000.00', EntryEffectType::ENTRADA, '2026-08-15'),
        ]);
        $entries->method('findActiveEntriesByUserAndPeriod')->willReturn([
            $this->entry('200.00', EntryEffectType::ENTRADA, '2026-09-10'),
            $this->entry('50.00', EntryEffectType::SAIDA, '2026-09-12'),
        ]);
        $commitments->method('findPendingCommitmentsByUserAndPeriod')->willReturn([
            new Commitment(
                10, 1, null, null, 'Conta', '150.00', CommitmentNature::SAIDA,
                new \DateTimeImmutable('2026-09-15'), CommitmentStatus::PENDENTE, new \DateTimeImmutable()
            ),
        ]);

        $result = (new GetDashboardService($accounts, $entries, $commitments, $context))->execute('2026-09-01');

        $this->assertSame('1000.00', $result['opening_balance']);
        $this->assertSame('200.00', $result['realized_inflows']);
        $this->assertSame('-50.00', $result['realized_outflows']);
        $this->assertSame('1150.00', $result['current_balance']);
        $this->assertSame('-150.00', $result['expected_outflows']);
        $this->assertSame('1000.00', $result['expected_closing_balance']);
        $this->assertSame('2026-09-01', $result['pending_commitments'][0]['reference_month']);
    }

    public function testDashboardRejectsLegacyMonthFormat(): void
    {
        $service = new GetDashboardService(
            $this->createStub(AccountRepository::class),
            $this->createStub(EntryRepository::class),
            $this->createStub(CommitmentRepository::class),
            $this->createStub(UserContext::class),
        );

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('O mês deve estar no formato YYYY-MM-01.');

        $service->execute('2026-09');
    }

    public function testDashboardReturnsZerosWhenThereAreNoEntriesOrPendingCommitments(): void
    {
        $entries = $this->createMock(EntryRepository::class);
        $entries->method('findActiveEntriesByUser')->willReturn([]);
        $entries->method('findActiveEntriesByUserAndPeriod')->willReturn([]);
        $commitments = $this->createMock(CommitmentRepository::class);
        $commitments->method('findPendingCommitmentsByUserAndPeriod')->willReturn([]);
        $context = $this->createMock(UserContext::class);
        $context->method('requireUserId')->willReturn(1);

        $result = (new GetDashboardService(
            $this->createStub(AccountRepository::class), $entries, $commitments, $context
        ))->execute('2026-09-01');

        self::assertSame('0.00', $result['opening_balance']);
        self::assertSame('0.00', $result['current_balance']);
        self::assertSame('0.00', $result['expected_closing_balance']);
        self::assertSame([], $result['pending_commitments']);
    }

    public function testDashboardDerivesOpeningBalanceWithoutRequiringAccountRows(): void
    {
        $entries = $this->createMock(EntryRepository::class);
        $entries->method('findActiveEntriesByUser')->willReturn([
            $this->entry('300.00', EntryEffectType::ENTRADA, '2026-08-15'),
            $this->entry('40.00', EntryEffectType::SAIDA, '2026-08-20'),
            $this->entry('999.00', EntryEffectType::ENTRADA, '2026-09-05'),
        ]);
        $entries->method('findActiveEntriesByUserAndPeriod')->willReturn([]);
        $commitments = $this->createMock(CommitmentRepository::class);
        $commitments->method('findPendingCommitmentsByUserAndPeriod')->willReturn([]);
        $context = $this->createMock(UserContext::class);
        $context->method('requireUserId')->willReturn(1);

        $result = (new GetDashboardService(
            $this->createMock(AccountRepository::class), $entries, $commitments, $context
        ))->execute('2026-09-01');

        self::assertSame('260.00', $result['opening_balance']);
        self::assertSame('260.00', $result['current_balance']);
    }

    public function testDashboardMarksAWholeMonthBeforeThePersistedInitialBalanceWithoutInventingBalance(): void
    {
        $entries = $this->createMock(EntryRepository::class);
        $entries->method('findActiveEntriesByUser')->willReturn([
            $this->entry('500.00', EntryEffectType::ENTRADA, '2026-09-15', EntryOrigin::SALDO_INICIAL),
        ]);
        $entries->method('findActiveEntriesByUserAndPeriod')->willReturn([]);
        $commitments = $this->createMock(CommitmentRepository::class);
        $commitments->method('findPendingCommitmentsByUserAndPeriod')->willReturn([]);
        $context = $this->createMock(UserContext::class);
        $context->method('requireUserId')->willReturn(1);

        $result = (new GetDashboardService(
            $this->createStub(AccountRepository::class), $entries, $commitments, $context
        ))->execute('2026-08-01');

        self::assertTrue($result['is_before_financial_start']);
        self::assertSame('0.00', $result['opening_balance']);
        self::assertSame('0.00', $result['current_balance']);
    }

    public function testDashboardDoesNotReportRowsBeforeTheConfiguredFinancialStart(): void
    {
        $entries = $this->createMock(EntryRepository::class);
        $entries->method('findActiveEntriesByUser')->willReturn([
            $this->entry('900.00', EntryEffectType::ENTRADA, '2026-07-15', EntryOrigin::COMPROMISSO),
            $this->entry('500.00', EntryEffectType::ENTRADA, '2026-08-15', EntryOrigin::SALDO_INICIAL),
        ]);
        $entries->method('findActiveEntriesByUserAndPeriod')->willReturn([
            $this->entry('900.00', EntryEffectType::ENTRADA, '2026-07-15', EntryOrigin::COMPROMISSO),
        ]);
        $commitments = $this->createMock(CommitmentRepository::class);
        $commitments->method('findPendingCommitmentsByUserAndPeriod')->willReturn([]);
        $context = $this->createMock(UserContext::class);
        $context->method('requireUserId')->willReturn(1);

        $result = (new GetDashboardService(
            $this->createStub(AccountRepository::class), $entries, $commitments, $context
        ))->execute('2026-07-01');

        self::assertTrue($result['is_before_financial_start']);
        self::assertSame('0.00', $result['realized_inflows']);
        self::assertSame('0.00', $result['current_balance']);
    }

    public function testDashboardKeepsThePartialInitialBalanceMonthQueryable(): void
    {
        $entries = $this->createMock(EntryRepository::class);
        $initial = $this->entry('500.00', EntryEffectType::ENTRADA, '2026-09-15', EntryOrigin::SALDO_INICIAL);
        $entries->method('findActiveEntriesByUser')->willReturn([$initial]);
        $entries->method('findActiveEntriesByUserAndPeriod')->willReturn([$initial]);
        $commitments = $this->createMock(CommitmentRepository::class);
        $commitments->method('findPendingCommitmentsByUserAndPeriod')->willReturn([]);
        $context = $this->createMock(UserContext::class);
        $context->method('requireUserId')->willReturn(1);

        $result = (new GetDashboardService(
            $this->createStub(AccountRepository::class), $entries, $commitments, $context
        ))->execute('2026-09-01');

        self::assertFalse($result['is_before_financial_start']);
        self::assertSame('500.00', $result['realized_inflows']);
        self::assertSame('500.00', $result['current_balance']);
    }

    public function testDashboardPropagatesDependencyFailure(): void
    {
        $entries = $this->createMock(EntryRepository::class);
        $entries->method('findActiveEntriesByUser')->willThrowException(new \RuntimeException('database failure'));
        $context = $this->createMock(UserContext::class);
        $context->method('requireUserId')->willReturn(1);

        $this->expectException(\RuntimeException::class);
        (new GetDashboardService(
            $this->createStub(AccountRepository::class), $entries,
            $this->createStub(CommitmentRepository::class), $context
        ))->execute('2026-09-01');
    }

    public function testMovementsRejectsLegacyMonthFormat(): void
    {
        $service = new ListMovementsService(
            $this->createStub(EntryRepository::class),
            $this->createStub(UserContext::class),
        );

        $this->expectException(\InvalidArgumentException::class);
        $service->execute('2026-09');
    }

    public function testMovementsReturnsEmptyCollectionForEmptyPeriod(): void
    {
        $entries = $this->createMock(EntryRepository::class);
        $entries->expects($this->once())->method('findActiveEntriesByUserAndPeriod')->willReturn([]);
        $context = $this->createMock(UserContext::class);
        $context->method('requireUserId')->willReturn(1);

        self::assertSame([], (new ListMovementsService($entries, $context))->execute('2026-09-01'));
    }

    public function testMovementsRequiresCapabilityAndAuthenticatedUserBeforeQuerying(): void
    {
        $entries = $this->createMock(EntryRepository::class);
        $entries->expects($this->never())->method('findActiveEntriesByUserAndPeriod');
        $context = $this->createMock(UserContext::class);
        $context->expects($this->once())->method('requireCapability')->with('use_sgfp')
            ->willThrowException(new \RuntimeException('forbidden', 403));

        $this->expectExceptionCode(403);
        (new ListMovementsService($entries, $context))->execute('2026-09-01');
    }

    public function testDecimalKeepsCentPrecision(): void
    {
        $this->assertSame(30, Decimal::cents('0.10') + Decimal::cents('0.20'));
        $this->assertSame('0.30', Decimal::formatCents(30));
    }

    public function testEvolutionUsesOnlyPendingCommitmentsInPlannedAndActiveEntriesInEffective(): void
    {
        $commitments = $this->createMock(CommitmentRepository::class);
        $commitments->method('findPendingCommitmentsByUserAndPeriod')->willReturn([
            new Commitment(1, 1, null, null, 'Pendente', '100.00', CommitmentNature::SAIDA,
                new \DateTimeImmutable('2026-09-10'), CommitmentStatus::PENDENTE, new \DateTimeImmutable()),
            new Commitment(2, 1, null, null, 'Efetivado', '200.00', CommitmentNature::SAIDA,
                new \DateTimeImmutable('2026-09-11'), CommitmentStatus::EFETIVADO, new \DateTimeImmutable()),
            new Commitment(3, 1, null, null, 'Excluido', '300.00', CommitmentNature::ENTRADA,
                new \DateTimeImmutable('2026-09-12'), CommitmentStatus::EXCLUIDO, new \DateTimeImmutable()),
        ]);
        $entries = $this->createMock(EntryRepository::class);
        $entries->method('findActiveEntriesByUserAndPeriod')->willReturn([
            $this->entry('200.00', EntryEffectType::SAIDA, '2026-09-11'),
        ]);
        $context = $this->createMock(UserContext::class);
        $context->method('requireUserId')->willReturn(1);

        $result = (new GetDashboardService(
            $this->createStub(AccountRepository::class), $entries, $commitments, $context
        ))->executeEvolution('2026-09-01', 1);

        self::assertSame('-100.00', $result['items'][0]['planned']);
        self::assertSame('-200.00', $result['items'][0]['effective']);
    }

    public function testDashboardProjectsAnUnmaterializedRecurringOccurrenceWithoutPersistingIt(): void
    {
        $commitments = $this->createMock(CommitmentRepository::class);
        $base = new Commitment(10, 1, null, 8, 'Aluguel', '1200.00', CommitmentNature::SAIDA,
            new \DateTimeImmutable('2026-01-31'), CommitmentStatus::PENDENTE, new \DateTimeImmutable('2026-01-01'));
        $commitments->method('findPendingCommitmentsByUserAndPeriod')->willReturn([]);
        $commitments->method('findByRecurrenceIdAndMonth')->willReturn(null);
        $commitments->method('findFirstByRecurrenceId')->willReturn($base);
        $recurrences = $this->createMock(RecurrenceRepository::class);
        $recurrences->method('findAllByUser')->willReturn([
            new Recurrence(8, 1, new \DateTimeImmutable('2026-01-01'), null, null, new \DateTimeImmutable()),
        ]);
        $entries = $this->createMock(EntryRepository::class);
        $entries->method('findActiveEntriesByUser')->willReturn([]);
        $entries->method('findActiveEntriesByUserAndPeriod')->willReturn([]);
        $context = $this->createMock(UserContext::class);
        $context->method('requireUserId')->willReturn(1);

        $result = (new GetDashboardService(
            $this->createStub(AccountRepository::class), $entries, $commitments, $context, $recurrences
        ))->execute('2026-02-01');

        self::assertSame('-1200.00', $result['expected_outflows']);
        self::assertSame('2026-02-01', $result['pending_commitments'][0]['reference_month']);
        self::assertNull($result['pending_commitments'][0]['id']);
    }

    public function testEvolutionProjectsOnlyValidRecurringMonthsAndPreservesTombstones(): void
    {
        $commitments = $this->createMock(CommitmentRepository::class);
        $base = new Commitment(10, 1, null, 8, 'Academia', '99.99', CommitmentNature::SAIDA,
            new \DateTimeImmutable('2026-01-15'), CommitmentStatus::PENDENTE, new \DateTimeImmutable('2026-01-01'));
        $tombstone = $base->delete();
        $commitments->method('findPendingCommitmentsByUserAndPeriod')->willReturn([]);
        $commitments->method('findFirstByRecurrenceId')->willReturn($base);
        $commitments->method('findByRecurrenceIdAndMonth')->willReturnCallback(
            static fn (int $recurrenceId, string $month) => $month === '2026-02-01' ? $tombstone : null,
        );
        $recurrences = $this->createMock(RecurrenceRepository::class);
        $recurrences->method('findAllByUser')->willReturn([
            new Recurrence(8, 1, new \DateTimeImmutable('2026-01-01'), 3, null, new \DateTimeImmutable()),
        ]);
        $entries = $this->createMock(EntryRepository::class);
        $entries->method('findActiveEntriesByUserAndPeriod')->willReturn([]);
        $context = $this->createMock(UserContext::class);
        $context->method('requireUserId')->willReturn(1);

        $result = (new GetDashboardService(
            $this->createStub(AccountRepository::class), $entries, $commitments, $context, $recurrences
        ))->executeEvolution('2026-04-01', 4);

        self::assertSame('-99.99', $result['items'][0]['planned']);
        self::assertSame('0.00', $result['items'][1]['planned']);
        self::assertSame('-99.99', $result['items'][2]['planned']);
        self::assertSame('0.00', $result['items'][3]['planned']);
    }

    public function testDailyEvolutionReconcilesOpeningSameDayAndProjectedBalances(): void
    {
        $entries = $this->createMock(EntryRepository::class);
        $entries->method('findActiveEntriesByUser')->willReturn([
            $this->entry('2000.00', EntryEffectType::ENTRADA, '2026-08-31 12:00:00'),
            $this->entry('3000.00', EntryEffectType::ENTRADA, '2026-09-05 09:00:00'),
            $this->entry('1800.00', EntryEffectType::SAIDA, '2026-09-10 09:00:00'),
            $this->entry('100.00', EntryEffectType::SAIDA, '2026-09-10 10:00:00'),
        ]);
        $commitments = $this->createMock(CommitmentRepository::class);
        $commitments->method('findPendingCommitmentsByUserAndPeriod')->willReturn([
            new Commitment(20, 1, null, null, 'Pendente', '500.00', CommitmentNature::ENTRADA,
                new \DateTimeImmutable('2026-09-20'), CommitmentStatus::PENDENTE, new \DateTimeImmutable()),
        ]);
        $context = $this->createMock(UserContext::class);
        $context->method('requireUserId')->willReturn(1);

        $result = (new GetDashboardService(
            $this->createStub(AccountRepository::class), $entries, $commitments, $context
        ))->executeDailyEvolution('2026-09-01', 1);

        self::assertCount(30, $result['items']);
        self::assertSame('2000.00', $result['items'][0]['opening_balance']);
        self::assertSame('5000.00', $result['items'][4]['realized_closing_balance']);
        self::assertSame('-1900.00', $result['items'][9]['realized_outflows']);
        self::assertSame('3100.00', $result['items'][9]['realized_closing_balance']);
        self::assertSame('3600.00', $result['items'][19]['projected_closing_balance']);
    }

    public function testDailyEvolutionKeepsPendingAmountsProjectedForTheirWholeReferenceMonth(): void
    {
        $entries = $this->createMock(EntryRepository::class);
        $entries->method('findActiveEntriesByUser')->willReturn([
            $this->entry('100.00', EntryEffectType::ENTRADA, '2026-09-01'),
        ]);
        $commitments = $this->createMock(CommitmentRepository::class);
        $commitments->method('findPendingCommitmentsByUserAndPeriod')->willReturn([
            new Commitment(21, 1, null, null, 'Entrada futura', '25.01', CommitmentNature::ENTRADA,
                new \DateTimeImmutable('2026-09-10'), CommitmentStatus::PENDENTE, new \DateTimeImmutable()),
            new Commitment(22, 1, null, null, 'Saída futura', '10.02', CommitmentNature::SAIDA,
                new \DateTimeImmutable('2026-09-15'), CommitmentStatus::PENDENTE, new \DateTimeImmutable()),
        ]);
        $context = $this->createMock(UserContext::class);
        $context->method('requireUserId')->willReturn(1);

        $result = (new GetDashboardService(
            $this->createStub(AccountRepository::class), $entries, $commitments, $context
        ))->executeDailyEvolution('2026-09-01', 1);

        self::assertSame('100.00', $result['items'][0]['realized_closing_balance']);
        self::assertSame('114.99', $result['items'][0]['projected_closing_balance']);
        self::assertSame('114.99', $result['items'][8]['projected_closing_balance']);
        self::assertSame('114.99', $result['items'][9]['projected_closing_balance']);
        self::assertSame('114.99', $result['items'][10]['projected_closing_balance']);
    }

    public function testDailyEvolutionTerminalProjectedBalanceMatchesMonthlySummary(): void
    {
        $entries = $this->createMock(EntryRepository::class);
        $entries->method('findActiveEntriesByUser')->willReturn([
            $this->entry('1000.00', EntryEffectType::ENTRADA, '2026-09-01 08:00:00'),
            $this->entry('75.00', EntryEffectType::SAIDA, '2026-09-30 18:00:00'),
        ]);
        $entries->method('findActiveEntriesByUserAndPeriod')->willReturn([
            $this->entry('1000.00', EntryEffectType::ENTRADA, '2026-09-01 08:00:00'),
            $this->entry('75.00', EntryEffectType::SAIDA, '2026-09-30 18:00:00', EntryOrigin::COMPROMISSO),
        ]);
        $commitments = $this->createMock(CommitmentRepository::class);
        $commitments->method('findPendingCommitmentsByUserAndPeriod')->willReturn([
            new Commitment(30, 1, null, null, 'Último dia', '25.00', CommitmentNature::ENTRADA,
                new \DateTimeImmutable('2026-09-30'), CommitmentStatus::PENDENTE, new \DateTimeImmutable()),
        ]);
        $context = $this->createMock(UserContext::class);
        $context->method('requireUserId')->willReturn(1);

        $service = new GetDashboardService($this->createStub(AccountRepository::class), $entries, $commitments, $context);
        $summary = $service->execute('2026-09-01');
        $series = $service->executeDailyEvolution('2026-09-01', 1);

        self::assertSame($summary['expected_closing_balance'], $series['items'][29]['projected_closing_balance']);
        self::assertSame('925.00', $series['items'][29]['projected_closing_balance']);
    }

    public function testDailyEvolutionRejectsMoreThanTwelveMonths(): void
    {
        $service = new GetDashboardService(
            $this->createStub(AccountRepository::class),
            $this->createStub(EntryRepository::class),
            $this->createStub(CommitmentRepository::class),
            $this->createStub(UserContext::class),
        );
        $this->expectException(\InvalidArgumentException::class);
        $service->executeDailyEvolution('2026-09-01', 13);
    }

    public function testThreeMonthDailyEvolutionIsCenteredOnSelectedMonthAcrossYearBoundary(): void
    {
        $context = $this->createMock(UserContext::class);
        $context->method('requireUserId')->willReturn(1);
        $commitments = $this->createMock(CommitmentRepository::class);
        $commitments->method('findPendingCommitmentsByUserAndPeriod')->willReturn([]);

        $result = (new GetDashboardService(
            $this->createStub(AccountRepository::class),
            $this->createStub(EntryRepository::class),
            $commitments,
            $context,
        ))->executeDailyEvolution('2027-01-01', 3);

        self::assertSame('2026-12-01', $result['window']['start']);
        self::assertSame('2027-02-28', $result['window']['end']);
        self::assertCount(90, $result['items']);
        self::assertSame('2026-12-01', $result['items'][0]['date']);
        self::assertSame('2027-02-28', $result['items'][89]['date']);
    }

    public function testThreeMonthDailyEvolutionIncludesLeapDayWhenCenteredOnFebruary(): void
    {
        $context = $this->createMock(UserContext::class);
        $context->method('requireUserId')->willReturn(1);
        $commitments = $this->createMock(CommitmentRepository::class);
        $commitments->method('findPendingCommitmentsByUserAndPeriod')->willReturn([]);

        $result = (new GetDashboardService(
            $this->createStub(AccountRepository::class),
            $this->createStub(EntryRepository::class),
            $commitments,
            $context,
        ))->executeDailyEvolution('2028-02-01', 3);

        self::assertSame('2028-01-01', $result['window']['start']);
        self::assertSame('2028-03-31', $result['window']['end']);
        self::assertCount(91, $result['items']);
        self::assertSame('2028-02-29', $result['items'][59]['date']);
    }

    public function testAnnualEvolutionReturnsACompleteLeapYearAndCarriesBalanceAcrossMonths(): void
    {
        $entries = $this->createMock(EntryRepository::class);
        $entries->method('findActiveEntriesByUser')->willReturn([
            $this->entry('100.01', EntryEffectType::ENTRADA, '2023-12-31 12:00:00'),
            $this->entry('0.10', EntryEffectType::SAIDA, '2024-02-29 09:00:00'),
        ]);
        $commitments = $this->createMock(CommitmentRepository::class);
        $commitments->method('findPendingCommitmentsByUserAndPeriod')->willReturn([]);
        $context = $this->createMock(UserContext::class);
        $context->method('requireUserId')->willReturn(1);

        $result = (new GetDashboardService(
            $this->createStub(AccountRepository::class), $entries, $commitments, $context
        ))->executeAnnualEvolution('2024');

        self::assertSame('2024', $result['year']);
        self::assertSame('2024-01-01', $result['window']['start']);
        self::assertSame('2024-12-31', $result['window']['end']);
        self::assertCount(366, $result['items']);
        self::assertSame('100.01', $result['items'][0]['opening_balance']);
        self::assertSame('99.91', $result['items'][59]['realized_closing_balance']);
        self::assertSame('99.91', $result['items'][60]['opening_balance']);
    }

    public function testAnnualEvolutionKeepsEmptyYearsAsZeroSeries(): void
    {
        $entries = $this->createMock(EntryRepository::class);
        $entries->method('findActiveEntriesByUser')->willReturn([]);
        $commitments = $this->createMock(CommitmentRepository::class);
        $commitments->method('findPendingCommitmentsByUserAndPeriod')->willReturn([]);
        $context = $this->createMock(UserContext::class);
        $context->method('requireUserId')->willReturn(1);

        $result = (new GetDashboardService(
            $this->createStub(AccountRepository::class), $entries, $commitments, $context
        ))->executeAnnualEvolution('2023');

        self::assertCount(365, $result['items']);
        self::assertSame('0.00', $result['items'][364]['projected_closing_balance']);
    }

    public function testAnnualEvolutionProjectsOnlyThePendingCommitmentsOfEachMonth(): void
    {
        $entries = $this->createMock(EntryRepository::class);
        $entries->method('findActiveEntriesByUser')->willReturn([
            $this->entry('100.00', EntryEffectType::ENTRADA, '2025-12-31 12:00:00'),
        ]);
        $commitments = $this->createMock(CommitmentRepository::class);
        $commitments->method('findPendingCommitmentsByUserAndPeriod')->willReturnCallback(
            static function (int $userId, string $start): array {
                return match ($start) {
                    '2026-09-01' => [new Commitment(41, 1, null, null, 'Setembro', '40.00', CommitmentNature::ENTRADA,
                        new \DateTimeImmutable('2026-09-10'), CommitmentStatus::PENDENTE, new \DateTimeImmutable())],
                    '2026-10-01' => [new Commitment(42, 1, null, null, 'Outubro', '25.00', CommitmentNature::SAIDA,
                        new \DateTimeImmutable('2026-10-15'), CommitmentStatus::PENDENTE, new \DateTimeImmutable())],
                    default => [],
                };
            },
        );
        $context = $this->createMock(UserContext::class);
        $context->method('requireUserId')->willReturn(1);

        $result = (new GetDashboardService(
            $this->createStub(AccountRepository::class), $entries, $commitments, $context
        ))->executeAnnualEvolution('2026');

        self::assertSame('140.00', $result['items'][252]['projected_closing_balance']); // 10 Sep
        self::assertSame('140.00', $result['items'][272]['projected_closing_balance']); // 30 Sep
        self::assertSame('75.00', $result['items'][273]['projected_closing_balance']); // 1 Oct
        self::assertSame('100.00', $result['items'][364]['projected_closing_balance']); // 31 Dec
    }

    public function testAnnualEvolutionIncludesNovemberAndDecemberBeforeNextYear(): void
    {
        $entries = $this->createMock(EntryRepository::class);
        $entries->method('findActiveEntriesByUser')->willReturn([
            $this->entry('100.00', EntryEffectType::ENTRADA, '2026-11-30 12:00:00'),
            $this->entry('25.00', EntryEffectType::SAIDA, '2026-12-31 12:00:00'),
        ]);
        $commitments = $this->createMock(CommitmentRepository::class);
        $commitments->method('findPendingCommitmentsByUserAndPeriod')->willReturn([]);
        $context = $this->createMock(UserContext::class);
        $context->method('requireUserId')->willReturn(1);

        $result = (new GetDashboardService(
            $this->createStub(AccountRepository::class), $entries, $commitments, $context
        ))->executeAnnualEvolution('2026');

        self::assertSame('2026-11-30', $result['items'][333]['date']);
        self::assertSame('100.00', $result['items'][333]['realized_inflows']);
        self::assertSame('100.00', $result['items'][333]['realized_closing_balance']);
        self::assertSame('2026-12-31', $result['items'][364]['date']);
        self::assertSame('-25.00', $result['items'][364]['realized_outflows']);
        self::assertSame('75.00', $result['items'][364]['realized_closing_balance']);
    }

    public function testAnnualEvolutionRejectsInvalidYear(): void
    {
        $service = new GetDashboardService(
            $this->createStub(AccountRepository::class),
            $this->createStub(EntryRepository::class),
            $this->createStub(CommitmentRepository::class),
            $this->createStub(UserContext::class),
        );

        $this->expectException(\InvalidArgumentException::class);
        $service->executeAnnualEvolution('2024-01');
    }

    public function testAnnualEvolutionRejectsYearsOutsideTheRestContractRange(): void
    {
        $service = new GetDashboardService(
            $this->createStub(AccountRepository::class),
            $this->createStub(EntryRepository::class),
            $this->createStub(CommitmentRepository::class),
            $this->createStub(UserContext::class),
        );

        foreach (['1899', '2201'] as $year) {
            try {
                $service->executeAnnualEvolution($year);
                self::fail('Expected an invalid year exception for ' . $year);
            } catch (\InvalidArgumentException $e) {
                self::assertStringContainsString('1900 e 2200', $e->getMessage());
            }
        }
    }

    private function entry(string $amount, EntryEffectType $effect, string $date, EntryOrigin $origin = EntryOrigin::SALDO_INICIAL): Entry
    {
        return new Entry(
            null, 1, 1, null, $origin, 'Test', $amount, $effect,
            new \DateTimeImmutable($date), null, EntryState::ATIVO, new \DateTimeImmutable(), null
        );
    }
}
