<?php

declare(strict_types=1);

namespace SGFP\Tests\Unit;

use PHPUnit\Framework\TestCase;
use SGFP\Application\Ports\AccountRepository;
use SGFP\Application\Ports\CommitmentRepository;
use SGFP\Application\Ports\EntryRepository;
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

final class ReportingServicesTest extends TestCase
{
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

    public function testDecimalKeepsCentPrecision(): void
    {
        $this->assertSame(30, Decimal::cents('0.10') + Decimal::cents('0.20'));
        $this->assertSame('0.30', Decimal::formatCents(30));
    }

    private function entry(string $amount, EntryEffectType $effect, string $date): Entry
    {
        return new Entry(
            null, 1, 1, null, EntryOrigin::SALDO_INICIAL, 'Test', $amount, $effect,
            new \DateTimeImmutable($date), null, EntryState::ATIVO, new \DateTimeImmutable(), null
        );
    }
}
