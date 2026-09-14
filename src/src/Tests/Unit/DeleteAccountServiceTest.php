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
        $service->execute(true, 'Excluir Conta');
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
        ))->execute(true, DeleteAccountService::CONFIRMATION_PHRASE);

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

        (new DeleteAccountService(
            $purger, $identity, $transactions, $lock, $context
        ))->execute(true, DeleteAccountService::CONFIRMATION_PHRASE);
    }

    private function serviceForValidation(): DeleteAccountService
    {
        return new DeleteAccountService(
            $this->createStub(UserDataPurger::class),
            $this->createStub(UserIdentityDeleter::class),
            $this->createStub(TransactionManager::class),
            $this->createStub(UserOperationLock::class),
            $this->createStub(UserContext::class),
        );
    }
}
