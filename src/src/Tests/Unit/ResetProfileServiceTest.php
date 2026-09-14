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
        $this->serviceForValidation()->execute(false, ResetProfileService::CONFIRMATION_PHRASE);
    }

    public function testRequiresExactUppercasePhrase(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->serviceForValidation()->execute(true, 'Resetar Perfil');
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

        $result = (new ResetProfileService(
            $purger, $provisioner, $transactions, $lock, $context
        ))->execute(true, ResetProfileService::CONFIRMATION_PHRASE);

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
            $this->createStub(TransactionManager::class),
            $this->createStub(UserOperationLock::class),
            $this->createStub(UserContext::class),
        );
    }
}
