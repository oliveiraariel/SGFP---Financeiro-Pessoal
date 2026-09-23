<?php

declare(strict_types=1);

namespace SGFP\Tests\Unit;

use PHPUnit\Framework\TestCase;
use SGFP\Application\Ports\AccountRepository;
use SGFP\Application\Ports\CategoryRepository;
use SGFP\Application\Ports\TransactionManager;
use SGFP\Application\Ports\UserContext;
use SGFP\Application\Ports\UserDataPurger;
use SGFP\Application\Ports\UserOperationLock;
use SGFP\Application\Services\ProvisionUserService;
use SGFP\Application\Services\ResetProfileService;
use SGFP\Domain\Models\Account;

final class ResetProfileServiceTest extends TestCase
{
    public function testRequiresFirstConfirmation(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->serviceForValidation()->execute('', ResetProfileService::CONFIRMATION_PHRASE);
    }

    public function testRequiresExactUppercasePhrase(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->serviceForValidation()->execute('', 'Resetar Perfil');
    }

    public function testMissingTokenIsRejectedBeforeDestructiveWork(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->serviceForValidation()->execute('', ResetProfileService::CONFIRMATION_PHRASE);
    }

    public function testExpiredTokenIsRejected(): void
    {
        $this->expectExceptionMessage('inválida, expirada ou já consumida');
        $this->serviceWithMeta(['hash' => hash('sha256', 'token'), 'expires_at' => 99], 100)
            ->execute('token', ResetProfileService::CONFIRMATION_PHRASE);
    }

    public function testWrongUserTokenIsRejected(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->serviceWithMeta(['hash' => hash('sha256', 'other-user-token'), 'expires_at' => 200], 100)
            ->execute('token', ResetProfileService::CONFIRMATION_PHRASE);
    }

    public function testPhraseFailureDoesNotConsumeValidToken(): void
    {
        $meta = ['hash' => hash('sha256', 'token'), 'expires_at' => 200];
        $deleted = 0;
        $service = $this->serviceWithMeta($meta, 100, static function () use (&$deleted): void { $deleted++; });

        try {
            $service->execute('token', 'RESETAR PERFIL ');
        } catch (\InvalidArgumentException) {
        }

        $this->assertSame(0, $deleted);
    }

    public function testConsumedTokenIsRejectedOnReplay(): void
    {
        $service = $this->serviceWithMeta(['hash' => hash('sha256', 'token'), 'expires_at' => 200], 100);
        $this->expectException(\InvalidArgumentException::class);

        $service->execute('token', ResetProfileService::CONFIRMATION_PHRASE);
        $service->execute('token', ResetProfileService::CONFIRMATION_PHRASE);
    }

    public function testPurgesAndReprovisionsInsideLockAndTransaction(): void
    {
        $purger = $this->createMock(UserDataPurger::class);
        $accounts = $this->createMock(AccountRepository::class);
        $categories = $this->createMock(CategoryRepository::class);
        $transactions = $this->createMock(TransactionManager::class);
        $lock = $this->createMock(UserOperationLock::class);
        $context = $this->createMock(UserContext::class);

        $context->method('requireUserId')->willReturn(7);
        $purger->expects($this->once())->method('purgeSgfpData')->with(7);
        $accounts->method('findByUser')->with(7)->willReturn(null);
        $accounts->expects($this->once())->method('save')->willReturnCallback(
            fn (Account $account): Account => $account->withId(10)
        );
        $categories->expects($this->once())->method('seedDefaults')->with(7);
        $lock->expects($this->once())->method('acquire')->with(7);
        $lock->expects($this->once())->method('release')->with(7);
        $transactions->method('transactional')->willReturnCallback(fn (callable $action) => $action());

        $provisioner = new ProvisionUserService($accounts, $categories);

        $meta = ['hash' => hash('sha256', 'token'), 'expires_at' => 100];
        $result = (new ResetProfileService(
            $purger, $provisioner, $transactions, $lock, $context,
            static fn () => $meta, static function () {}, static function () {}, static fn () => 50
        ))->execute('token', ResetProfileService::CONFIRMATION_PHRASE);

        $this->assertSame(10, $result->id);
        $this->assertSame('Minha Conta', $result->name);
    }

    private function serviceForValidation(): ResetProfileService
    {
        $accounts = $this->createStub(AccountRepository::class);
        $categories = $this->createStub(CategoryRepository::class);

        return new ResetProfileService(
            $this->createStub(UserDataPurger::class),
            new ProvisionUserService($accounts, $categories),
            $this->transactionManagerExecutingActions(),
            $this->createStub(UserOperationLock::class),
            $this->createStub(UserContext::class),
            static fn () => null, static function () {}, static function () {}, static fn () => 1
        );
    }

    private function serviceWithMeta(array $meta, int $now, ?\Closure $delete = null): ResetProfileService
    {
        $accounts = $this->createStub(AccountRepository::class);
        $accounts->method('findByUser')->willReturn(null);
        $accounts->method('save')->willReturnCallback(static fn (Account $account): Account => $account->withId(10));
        $categories = $this->createStub(CategoryRepository::class);
        $context = $this->createStub(UserContext::class);
        $context->method('requireUserId')->willReturn(7);
        return new ResetProfileService(
            $this->createStub(UserDataPurger::class),
            new ProvisionUserService($accounts, $categories),
            $this->transactionManagerExecutingActions(),
            $this->createStub(UserOperationLock::class),
            $context,
            static function () use (&$meta) { return $meta; },
            static function () {},
            $delete ?? static function () use (&$meta): void { $meta = null; },
            static fn () => $now,
        );
    }

    private function transactionManagerExecutingActions(): TransactionManager
    {
        $transactions = $this->createStub(TransactionManager::class);
        $transactions->method('transactional')->willReturnCallback(static fn (callable $action) => $action());
        return $transactions;
    }
}
