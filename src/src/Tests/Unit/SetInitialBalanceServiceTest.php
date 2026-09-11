<?php

declare(strict_types=1);

namespace SGFP\Tests\Unit;

use PHPUnit\Framework\TestCase;
use SGFP\Application\Ports\AccountRepository;
use SGFP\Application\Ports\EntryRepository;
use SGFP\Application\Ports\TransactionManager;
use SGFP\Application\Ports\UserContext;
use SGFP\Application\Services\SetInitialBalanceService;
use SGFP\Domain\Enums\AccountRole;
use SGFP\Domain\Enums\EntryEffectType;
use SGFP\Domain\Enums\EntryOrigin;
use SGFP\Domain\Enums\EntryState;
use SGFP\Domain\Models\Account;
use SGFP\Domain\Models\Entry;

final class SetInitialBalanceServiceTest extends TestCase
{
    public function testCreatesInitialBalanceOnPrincipalAccount(): void
    {
        $accountRepository = $this->createMock(AccountRepository::class);
        $entryRepository = $this->createMock(EntryRepository::class);
        $transactionManager = $this->createStub(TransactionManager::class);
        $userContext = $this->createMock(UserContext::class);

        $userContext->expects($this->once())->method('requireCapability')->with('use_sgfp');
        $userContext->method('requireUserId')->willReturn(1);

        $account = new Account(10, 1, 'Principal', AccountRole::PRINCIPAL, new \DateTimeImmutable());
        $accountRepository->method('findById')->with(10, 1)->willReturn($account);

        $entryRepository->method('findActiveInitialBalanceByAccount')->with(10, 1)->willReturn(null);
        $entryRepository->expects($this->once())->method('save')->willReturnCallback(
            function (Entry $entry) {
                return $entry->withId(100);
            }
        );

        $transactionManager->method('transactional')->willReturnCallback(
            function (callable $action) {
                return $action();
            }
        );

        $service = new SetInitialBalanceService($accountRepository, $entryRepository, $transactionManager, $userContext);
        $entry = $service->execute(10, 1500.00, 'Meu saldo', 'Inicial', new \DateTimeImmutable('2026-09-01'));

        $this->assertSame(100, $entry->id);
        $this->assertSame(10, $entry->accountId);
        $this->assertSame(EntryOrigin::SALDO_INICIAL, $entry->origin);
        $this->assertSame(EntryEffectType::ENTRADA, $entry->effectType);
        $this->assertSame(EntryState::ATIVO, $entry->state);
        $this->assertSame(1500.00, $entry->amount);
        $this->assertSame('Meu saldo', $entry->name);
        $this->assertSame('Inicial', $entry->description);
        $this->assertNull($entry->commitmentId);
    }

    public function testReplacesExistingInitialBalance(): void
    {
        $accountRepository = $this->createMock(AccountRepository::class);
        $entryRepository = $this->createMock(EntryRepository::class);
        $transactionManager = $this->createStub(TransactionManager::class);
        $userContext = $this->createMock(UserContext::class);

        $userContext->method('requireCapability');
        $userContext->method('requireUserId')->willReturn(1);

        $account = new Account(10, 1, 'Principal', AccountRole::PRINCIPAL, new \DateTimeImmutable());
        $accountRepository->method('findById')->willReturn($account);

        $existing = new Entry(
            99,
            1,
            10,
            null,
            EntryOrigin::SALDO_INICIAL,
            'Saldo inicial',
            500.00,
            EntryEffectType::ENTRADA,
            new \DateTimeImmutable('2026-08-01 00:00:00'),
            null,
            EntryState::ATIVO,
            new \DateTimeImmutable('-1 day'),
            null,
        );

        $entryRepository->method('findActiveInitialBalanceByAccount')->willReturn($existing);

        $capturedUndone = null;
        $entryRepository->expects($this->exactly(2))->method('save')->willReturnCallback(
            function (Entry $entry) use (&$capturedUndone) {
                if ($entry->state === EntryState::DESFEITO) {
                    $capturedUndone = $entry;
                    return $entry;
                }
                return $entry->withId(101);
            }
        );

        $transactionManager->method('transactional')->willReturnCallback(
            function (callable $action) {
                return $action();
            }
        );

        $service = new SetInitialBalanceService($accountRepository, $entryRepository, $transactionManager, $userContext);
        $entry = $service->execute(10, 2000.00, null, null, null);

        $this->assertNotNull($capturedUndone);
        $this->assertSame(99, $capturedUndone->id);
        $this->assertSame(EntryState::DESFEITO, $capturedUndone->state);
        $this->assertNotNull($capturedUndone->undoneAt);

        $this->assertSame(101, $entry->id);
        $this->assertSame(2000.00, $entry->amount);
        $this->assertSame(EntryState::ATIVO, $entry->state);
    }

    public function testFailsWhenAccountNotFound(): void
    {
        $accountRepository = $this->createMock(AccountRepository::class);
        $entryRepository = $this->createStub(EntryRepository::class);
        $transactionManager = $this->createStub(TransactionManager::class);
        $userContext = $this->createMock(UserContext::class);

        $userContext->method('requireCapability');
        $userContext->method('requireUserId')->willReturn(1);
        $accountRepository->method('findById')->willReturn(null);

        $service = new SetInitialBalanceService($accountRepository, $entryRepository, $transactionManager, $userContext);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Conta não encontrada.');
        $service->execute(10, 100.00, null, null, null);
    }

    public function testFailsWhenAccountIsNotPrincipal(): void
    {
        $accountRepository = $this->createMock(AccountRepository::class);
        $entryRepository = $this->createStub(EntryRepository::class);
        $transactionManager = $this->createStub(TransactionManager::class);
        $userContext = $this->createMock(UserContext::class);

        $userContext->method('requireCapability');
        $userContext->method('requireUserId')->willReturn(1);

        $account = new Account(10, 1, 'Reserva', AccountRole::SECUNDARIA, new \DateTimeImmutable());
        $accountRepository->method('findById')->willReturn($account);

        $service = new SetInitialBalanceService($accountRepository, $entryRepository, $transactionManager, $userContext);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Saldo inicial somente pode ser definido na conta principal.');
        $service->execute(10, 100.00, null, null, null);
    }
}
