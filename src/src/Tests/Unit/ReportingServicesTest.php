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
            $this->entry(1000.0, EntryEffectType::ENTRADA, '2026-08-15'),
        ]);
        $entries->method('findActiveEntriesByUserAndPeriod')->willReturn([
            $this->entry(200.0, EntryEffectType::ENTRADA, '2026-09-10'),
            $this->entry(50.0, EntryEffectType::SAIDA, '2026-09-12'),
        ]);
        $commitments->method('findPendingCommitmentsByUserAndPeriod')->willReturn([
            new Commitment(
                10, 1, null, null, 'Conta', 150.0, CommitmentNature::SAIDA,
                new \DateTimeImmutable('2026-09-01'), CommitmentStatus::PENDENTE, new \DateTimeImmutable()
            ),
        ]);

        $result = (new GetDashboardService($accounts, $entries, $commitments, $context))->execute('2026-09');

        $this->assertSame('1000.00', $result['opening_balance']);
        $this->assertSame('1000.00', $result['expected_closing_balance']);
    }

    private function entry(float $amount, EntryEffectType $effect, string $date): Entry
    {
        return new Entry(
            null, 1, 1, null, EntryOrigin::SALDO_INICIAL, 'Test', $amount, $effect,
            new \DateTimeImmutable($date), null, EntryState::ATIVO, new \DateTimeImmutable(), null
        );
    }
}
