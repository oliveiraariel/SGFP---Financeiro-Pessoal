<?php

declare(strict_types=1);

namespace SGFP\Tests\Unit;

use PHPUnit\Framework\TestCase;
use SGFP\Application\Ports\AccountRepository;
use SGFP\Application\Ports\EntryRepository;
use SGFP\Application\Ports\TransactionManager;
use SGFP\Application\Ports\UserContext;
use SGFP\Application\Services\SetInitialBalanceService;
use SGFP\Domain\Enums\EntryState;
use SGFP\Domain\Models\Account;
use SGFP\Domain\Models\Entry;

final class SetInitialBalanceServiceTest extends TestCase
{
    public function testCreatesInitialBalanceOnOnlyAccount(): void
    {
        $accounts = $this->createMock(AccountRepository::class);
        $entries = $this->createMock(EntryRepository::class);
        $context = $this->createMock(UserContext::class);
        $tx = $this->transactionManager();

        $context->method('requireUserId')->willReturn(1);
        $account = new Account(10, 1, 'Minha Conta', new \DateTimeImmutable());
        $accounts->method('findById')->willReturn($account);
        $accounts->method('findByUser')->willReturn($account);
        $entries->method('findActiveInitialBalanceByAccount')->willReturn(null);
        $entries->expects($this->once())->method('save')->willReturnCallback(
            fn (Entry $e): Entry => $e->withId(100)
        );

        $result = (new SetInitialBalanceService($accounts, $entries, $tx, $context))
            ->execute(10, 1500.0, null, null, null);

        $this->assertSame(100, $result->id);
        $this->assertSame(10, $result->accountId);
    }

    public function testReplacesExistingInitialBalance(): void
    {
        $accounts = $this->createMock(AccountRepository::class);
        $entries = $this->createMock(EntryRepository::class);
        $context = $this->createMock(UserContext::class);
        $context->method('requireUserId')->willReturn(1);

        $account = new Account(10, 1, 'Minha Conta', new \DateTimeImmutable());
        $accounts->method('findById')->willReturn($account);
        $accounts->method('findByUser')->willReturn($account);

        $existing = new Entry(
            99, 1, 10, null,
            \SGFP\Domain\Enums\EntryOrigin::SALDO_INICIAL,
            'Saldo inicial', 500.0,
            \SGFP\Domain\Enums\EntryEffectType::ENTRADA,
            new \DateTimeImmutable(), null,
            EntryState::ATIVO, new \DateTimeImmutable(), null
        );
        $entries->method('findActiveInitialBalanceByAccount')->willReturn($existing);
        $entries->expects($this->exactly(2))->method('save')->willReturnCallback(
            fn (Entry $e): Entry => $e->state === EntryState::ATIVO ? $e->withId(101) : $e
        );

        $result = (new SetInitialBalanceService($accounts, $entries, $this->transactionManager(), $context))
            ->execute(10, 2000.0, null, null, null);

        $this->assertSame(101, $result->id);
    }

    private function transactionManager(): TransactionManager
    {
        $tx = $this->createStub(TransactionManager::class);
        $tx->method('transactional')->willReturnCallback(fn (callable $action) => $action());
        return $tx;
    }
}
