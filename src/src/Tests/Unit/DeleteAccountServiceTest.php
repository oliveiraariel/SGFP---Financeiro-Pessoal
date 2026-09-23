<?php

declare(strict_types=1);

namespace SGFP\Tests\Unit;

use PHPUnit\Framework\TestCase;
use SGFP\Application\Ports\TransactionManager;
use SGFP\Application\Ports\UserContext;
use SGFP\Application\Ports\UserDataPurger;
use SGFP\Application\Ports\UserIdentityDeleter;
use SGFP\Application\Ports\UserOperationLock;
use SGFP\Application\Services\DeleteAccountService;

final class DeleteAccountServiceTest extends TestCase
{
    public function testRequiresExactUppercasePhrase(): void
    {
        $service = $this->serviceForValidation();

        $this->expectException(\InvalidArgumentException::class);
        $service->execute('', 'Excluir Conta');
    }

    public function testMissingTokenIsRejectedBeforeDestructiveWork(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->serviceForValidation()->execute('', DeleteAccountService::CONFIRMATION_PHRASE);
    }

    public function testExpiredTokenIsRejected(): void
    {
        $this->expectExceptionMessage('inválida, expirada ou já consumida');
        $this->serviceWithMeta(['hash' => hash('sha256', 'token'), 'expires_at' => 99], 100)
            ->execute('token', DeleteAccountService::CONFIRMATION_PHRASE);
    }

    public function testWrongUserTokenIsRejected(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->serviceWithMeta(['hash' => hash('sha256', 'other-user-token'), 'expires_at' => 200], 100)
            ->execute('token', DeleteAccountService::CONFIRMATION_PHRASE);
    }

    public function testPhraseFailureDoesNotConsumeValidToken(): void
    {
        $deleted = 0;
        $service = $this->serviceWithMeta(
            ['hash' => hash('sha256', 'token'), 'expires_at' => 200],
            100,
            static function () use (&$deleted): void { $deleted++; }
        );

        try {
            $service->execute('token', 'EXCLUIR CONTA ');
        } catch (\InvalidArgumentException) {
        }

        $this->assertSame(0, $deleted);
    }

    public function testConsumedTokenIsRejectedOnReplay(): void
    {
        $service = $this->serviceWithMeta(['hash' => hash('sha256', 'token'), 'expires_at' => 200], 100);
        $this->expectException(\InvalidArgumentException::class);

        $service->execute('token', DeleteAccountService::CONFIRMATION_PHRASE);
        $service->execute('token', DeleteAccountService::CONFIRMATION_PHRASE);
    }

    public function testPurgesDataAndDeletesWordPressIdentityInTransaction(): void
    {
        $purger = $this->createMock(UserDataPurger::class);
        $identity = $this->createMock(UserIdentityDeleter::class);
        $transactions = $this->createMock(TransactionManager::class);
        $lock = $this->createMock(UserOperationLock::class);
        $context = $this->createMock(UserContext::class);

        $context->method('requireUserId')->willReturn(7);
        $purger->expects($this->once())->method('purgeSgfpData')->with(7);
        $identity->expects($this->once())->method('delete')->with(7);
        $lock->expects($this->once())->method('acquire')->with(7);
        $lock->expects($this->once())->method('release')->with(7);
        $transactions->method('transactional')->willReturnCallback(fn (callable $action) => $action());

        (new DeleteAccountService(
            $purger, $identity, $transactions, $lock, $context
            , static fn () => ['hash' => hash('sha256', 'token'), 'expires_at' => 100], static function () {}, static function () {}, static fn () => 50
        ))->execute('token', DeleteAccountService::CONFIRMATION_PHRASE);

        $this->addToAssertionCount(1);
    }

    public function testIdentityFailurePropagatesSoSuccessIsNotReported(): void
    {
        $purger = $this->createMock(UserDataPurger::class);
        $identity = $this->createMock(UserIdentityDeleter::class);
        $transactions = $this->createMock(TransactionManager::class);
        $lock = $this->createMock(UserOperationLock::class);
        $context = $this->createMock(UserContext::class);

        $context->method('requireUserId')->willReturn(7);
        $identity->method('delete')->willThrowException(new \RuntimeException('falha'));
        $transactions->method('transactional')->willReturnCallback(fn (callable $action) => $action());

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionCode(409);
        $this->expectExceptionMessage('Exclusão incompleta');

        (new DeleteAccountService(
            $purger, $identity, $transactions, $lock, $context
            , static fn () => ['hash' => hash('sha256', 'token'), 'expires_at' => 100], static function () {}, static function () {}, static fn () => 50
        ))->execute('token', DeleteAccountService::CONFIRMATION_PHRASE);
    }

    public function testIdentityIsCalledOnlyAfterTransactionalPurgeHasReturned(): void
    {
        $events = [];
        $purger = $this->createMock(UserDataPurger::class);
        $identity = $this->createMock(UserIdentityDeleter::class);
        $transactions = $this->createMock(TransactionManager::class);
        $context = $this->createStub(UserContext::class);
        $lock = $this->createStub(UserOperationLock::class);
        $context->method('requireUserId')->willReturn(7);
        $purger->expects($this->once())->method('purgeSgfpData')->willReturnCallback(
            function () use (&$events): void { $events[] = 'purge'; }
        );
        $transactions->method('transactional')->willReturnCallback(function (callable $action) use (&$events): void {
            $action();
            $events[] = 'commit';
        });
        $identity->expects($this->once())->method('delete')->willReturnCallback(
            function () use (&$events): void { $events[] = 'identity'; }
        );

        (new DeleteAccountService($purger, $identity, $transactions, $lock, $context,
            static fn () => ['hash' => hash('sha256', 'token'), 'expires_at' => 100], static function () {}, static function () {}, static fn () => 50
        ))->execute('token', DeleteAccountService::CONFIRMATION_PHRASE);

        $this->assertSame(['purge', 'commit', 'identity'], $events);
    }

    public function testIdentityFailureLeavesDurablePendingStateAndRetryCompletesIt(): void
    {
        $meta = [
            '_sgfp_delete_confirmation' => ['hash' => hash('sha256', 'first'), 'expires_at' => 200],
        ];
        $identity = $this->createMock(UserIdentityDeleter::class);
        $identity->expects($this->exactly(2))->method('delete')->with(7)
            ->willReturnOnConsecutiveCalls($this->throwException(new \RuntimeException('falha')), null);
        $transactions = $this->createStub(TransactionManager::class);
        $transactions->method('transactional')->willReturnCallback(fn (callable $action) => $action());
        $context = $this->createStub(UserContext::class);
        $context->method('requireUserId')->willReturn(7);
        $service = new DeleteAccountService(
            $this->createStub(UserDataPurger::class), $identity, $transactions,
            $this->createStub(UserOperationLock::class), $context,
            static function (int $id, string $key) use (&$meta) { return $meta[$key] ?? null; },
            static function (int $id, string $key, mixed $value) use (&$meta): void { $meta[$key] = $value; },
            static function (int $id, string $key) use (&$meta): void { unset($meta[$key]); },
            static fn () => 100,
        );

        try {
            $service->execute('first', DeleteAccountService::CONFIRMATION_PHRASE);
        } catch (\RuntimeException $e) {
            $this->assertSame(409, $e->getCode());
        }
        $this->assertSame('PENDING_IDENTITY_DELETE', $meta['_sgfp_account_deletion_pending']['status']);

        $retry = $service->beginRetry();
        $service->executeRetry($retry, DeleteAccountService::CONFIRMATION_PHRASE);
        $this->assertArrayNotHasKey('_sgfp_account_deletion_pending', $meta);
    }

    public function testRetryRequiresPendingDeletionForSameUser(): void
    {
        $context = $this->createStub(UserContext::class);
        $context->method('requireUserId')->willReturn(7);
        $service = new DeleteAccountService(
            $this->createStub(UserDataPurger::class),
            $this->createStub(UserIdentityDeleter::class),
            $this->createStub(TransactionManager::class),
            $this->createStub(UserOperationLock::class),
            $context,
            static fn () => null,
            static function (): void {},
            static function (): void {},
            static fn () => 100,
        );

        $this->expectExceptionCode(404);
        $service->beginRetry();
    }

    public function testPendingDeletionCannotBeStartedByAnotherUser(): void
    {
        $meta = [
            '_sgfp_account_deletion_pending' => ['user_id' => 7, 'status' => 'PENDING_IDENTITY_DELETE'],
        ];
        $context = $this->createStub(UserContext::class);
        $context->method('requireUserId')->willReturn(8);
        $service = new DeleteAccountService(
            $this->createStub(UserDataPurger::class),
            $this->createStub(UserIdentityDeleter::class),
            $this->createStub(TransactionManager::class),
            $this->createStub(UserOperationLock::class),
            $context,
            static function (int $id, string $key) use (&$meta) { return $meta[$key] ?? null; },
            static function (): void {},
            static function (): void {},
            static fn () => 100,
        );

        $this->expectExceptionCode(404);
        $service->beginRetry();
    }

    public function testRetryTokenIsSingleUse(): void
    {
        $meta = [
            '_sgfp_account_deletion_pending' => ['user_id' => 7, 'status' => 'PENDING_IDENTITY_DELETE'],
        ];
        $identity = $this->createMock(UserIdentityDeleter::class);
        $identity->expects($this->once())->method('delete')->with(7);
        $context = $this->createStub(UserContext::class);
        $context->method('requireUserId')->willReturn(7);
        $service = new DeleteAccountService(
            $this->createMock(UserDataPurger::class),
            $identity,
            $this->createStub(TransactionManager::class),
            $this->createStub(UserOperationLock::class),
            $context,
            static function (int $id, string $key) use (&$meta) { return $meta[$key] ?? null; },
            static function (int $id, string $key, mixed $value) use (&$meta): void { $meta[$key] = $value; },
            static function (int $id, string $key) use (&$meta): void { unset($meta[$key]); },
            static fn () => 100,
        );

        $token = $service->beginRetry();
        $service->executeRetry($token, DeleteAccountService::CONFIRMATION_PHRASE);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('inválida, expirada ou já consumida');
        $service->executeRetry($token, DeleteAccountService::CONFIRMATION_PHRASE);
    }

    public function testRetryFailurePreservesPendingStateAndDoesNotPurgeAgain(): void
    {
        $meta = [
            '_sgfp_account_deletion_pending' => ['user_id' => 7, 'status' => 'PENDING_IDENTITY_DELETE'],
        ];
        $purger = $this->createMock(UserDataPurger::class);
        $purger->expects($this->never())->method('purgeSgfpData');
        $identity = $this->createMock(UserIdentityDeleter::class);
        $identity->expects($this->once())->method('delete')->with(7)
            ->willThrowException(new \RuntimeException('identidade indisponível'));
        $context = $this->createStub(UserContext::class);
        $context->method('requireUserId')->willReturn(7);
        $service = new DeleteAccountService(
            $purger,
            $identity,
            $this->createStub(TransactionManager::class),
            $this->createStub(UserOperationLock::class),
            $context,
            static function (int $id, string $key) use (&$meta) { return $meta[$key] ?? null; },
            static function (int $id, string $key, mixed $value) use (&$meta): void { $meta[$key] = $value; },
            static function (int $id, string $key) use (&$meta): void { unset($meta[$key]); },
            static fn () => 100,
        );

        $token = $service->beginRetry();
        try {
            $service->executeRetry($token, DeleteAccountService::CONFIRMATION_PHRASE);
            $this->fail('A falha da identidade deveria manter o retry incompleto.');
        } catch (\RuntimeException $e) {
            $this->assertSame(409, $e->getCode());
        }

        $this->assertSame('PENDING_IDENTITY_DELETE', $meta['_sgfp_account_deletion_pending']['status']);
        $this->assertArrayNotHasKey('_sgfp_account_deletion_retry', $meta);
    }

    private function serviceForValidation(): DeleteAccountService
    {
        return new DeleteAccountService(
            $this->createStub(UserDataPurger::class),
            $this->createStub(UserIdentityDeleter::class),
            $this->createStub(TransactionManager::class),
            $this->createStub(UserOperationLock::class),
            $this->createStub(UserContext::class),
            static fn () => null, static function () {}, static function () {}, static fn () => 1,
        );
    }

    private function serviceWithMeta(array $meta, int $now, ?\Closure $delete = null): DeleteAccountService
    {
        $context = $this->createStub(UserContext::class);
        $context->method('requireUserId')->willReturn(7);
        return new DeleteAccountService(
            $this->createStub(UserDataPurger::class),
            $this->createStub(UserIdentityDeleter::class),
            $this->createStub(TransactionManager::class),
            $this->createStub(UserOperationLock::class),
            $context,
            static function () use (&$meta) { return $meta; },
            static function () {},
            $delete ?? static function () use (&$meta): void { $meta = null; },
            static fn () => $now,
        );
    }
}
