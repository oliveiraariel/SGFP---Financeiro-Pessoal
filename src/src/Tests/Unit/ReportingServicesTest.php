<?php

declare(strict_types=1);

namespace SGFP\Tests\Unit;

use PHPUnit\Framework\TestCase;
use SGFP\Application\Ports\AccountRepository;
use SGFP\Application\Ports\CommitmentRepository;
use SGFP\Application\Ports\EntryRepository;
use SGFP\Application\Ports\UserContext;
use SGFP\Application\Services\GetDashboardService;
use SGFP\Application\Services\GetNetWorthService;
use SGFP\Application\Services\ListMovementsService;
use SGFP\Domain\Enums\AccountRole;
use SGFP\Domain\Enums\CommitmentNature;
use SGFP\Domain\Enums\CommitmentStatus;
use SGFP\Domain\Enums\CommitmentType;
use SGFP\Domain\Enums\EntryEffectType;
use SGFP\Domain\Enums\EntryOrigin;
use SGFP\Domain\Enums\EntryState;
use SGFP\Domain\Models\Account;
use SGFP\Domain\Models\Commitment;
use SGFP\Domain\Models\Entry;

final class ReportingServicesTest extends TestCase
{
    public function testListMovementsByMonth(): void
    {
        $entryRepository = $this->createMock(EntryRepository::class);
        $userContext = $this->createMock(UserContext::class);

        $userContext->method('requireCapability');
        $userContext->method('requireUserId')->willReturn(1);

        $entryRepository->method('findActiveEntriesByUserAndPeriod')->willReturn([
            $this->makeEntry(1, 100.0, EntryEffectType::ENTRADA, '2026-09-15 10:00:00'),
        ]);

        $service = new ListMovementsService($entryRepository, $userContext);
        $result = $service->execute('2026-09');

        $this->assertCount(1, $result);
        $this->assertSame(100.0, $result[0]->amount);
    }

    public function testNetWorthSumsActiveEntries(): void
    {
        $accountRepository = $this->createMock(AccountRepository::class);
        $entryRepository = $this->createMock(EntryRepository::class);
        $userContext = $this->createMock(UserContext::class);

        $userContext->method('requireCapability');
        $userContext->method('requireUserId')->willReturn(1);

        $accountRepository->method('findAllByUser')->willReturn([
            new Account(1, 1, 'Principal', AccountRole::PRINCIPAL, new \DateTimeImmutable()),
        ]);

        $entryRepository->method('findActiveEntriesByUser')->willReturn([
            $this->makeEntry(1, 1000.0, EntryEffectType::ENTRADA, '2026-09-01'),
            $this->makeEntry(1, 300.0, EntryEffectType::SAIDA, '2026-09-02'),
        ]);

        $service = new GetNetWorthService($accountRepository, $entryRepository, $userContext);
        $result = $service->execute();

        $this->assertSame('700.00', $result['accounts'][0]['balance']);
        $this->assertSame('700.00', $result['net_worth']);
    }

    public function testDashboardCalculatesExpectedClosingBalance(): void
    {
        $accountRepository = $this->createMock(AccountRepository::class);
        $entryRepository = $this->createMock(EntryRepository::class);
        $commitmentRepository = $this->createMock(CommitmentRepository::class);
        $userContext = $this->createMock(UserContext::class);

        $userContext->method('requireCapability');
        $userContext->method('requireUserId')->willReturn(1);

        $accountRepository->method('findAllByUser')->willReturn([
            new Account(1, 1, 'Principal', AccountRole::PRINCIPAL, new \DateTimeImmutable()),
        ]);

        $entryRepository->method('findActiveEntriesByUser')->willReturn([
            $this->makeEntry(1, 1000.0, EntryEffectType::ENTRADA, '2026-08-15'),
        ]);

        $entryRepository->method('findActiveEntriesByUserAndPeriod')->willReturn([
            $this->makeEntry(1, 200.0, EntryEffectType::ENTRADA, '2026-09-10'),
            $this->makeEntry(1, 50.0, EntryEffectType::SAIDA, '2026-09-12'),
        ]);

        $commitmentRepository->method('findPendingCommitmentsByUserAndPeriod')->willReturn([
            new Commitment(10, 1, null, null, 'Conta', 150.0, CommitmentType::PADRAO, CommitmentNature::SAIDA, new \DateTimeImmutable('2026-09-01'), CommitmentStatus::PENDENTE, new \DateTimeImmutable()),
        ]);

        $service = new GetDashboardService($accountRepository, $entryRepository, $commitmentRepository, $userContext);
        $result = $service->execute('2026-09');

        $this->assertSame('1000.00', $result['opening_balance']);
        $this->assertSame('200.00', $result['realized_inflows']);
        $this->assertSame('50.00', $result['realized_outflows']);
        $this->assertSame('0.00', $result['expected_inflows']);
        $this->assertSame('150.00', $result['expected_outflows']);
        $this->assertSame('1000.00', $result['expected_closing_balance']);
        $this->assertCount(1, $result['pending_commitments']);
    }

    private function makeEntry(int $accountId, float $amount, EntryEffectType $effect, string $settledAt): Entry
    {
        return new Entry(
            null,
            1,
            $accountId,
            null,
            EntryOrigin::SALDO_INICIAL,
            'Test',
            $amount,
            $effect,
            new \DateTimeImmutable($settledAt),
            null,
            EntryState::ATIVO,
            new \DateTimeImmutable(),
            null,
        );
    }
}
