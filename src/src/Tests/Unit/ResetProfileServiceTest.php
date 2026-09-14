<?php

declare(strict_types=1);

namespace SGFP\Tests\Unit;

use PHPUnit\Framework\TestCase;
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
        $service = $this->service();

        $this->expectException(\InvalidArgumentException::class);
        $service->execute(false, ResetProfileService::CONFIRMATION_PHRASE);
    }

    public function testRequiresExactUppercasePhrase(): void
    {
        $service = $this->service();

        $this->expectException(\InvalidArgumentException::class);
        $service->execute(true, 'Resetar Perfil');
    }

    public function testPurgesAndReprovisionsInsideLockAndTransaction(): void
    {
        $purger = $this->createMock(UserDataPurger::class);
        $provisioner = $this->createMock(ProvisionUserService::class);
        $transactions = $this->createMock(TransactionManager::class);
        $lock = $this->createMock(UserOperationLock::class);
        $context = $this->createMock(UserContext::class);

        $context->method('requireUserId')->willReturn(7);
        $purger->expects($this->once())->method('purgeSgfpData')->with(7);
        $provisioner->expects($this->once())->method('execute')->with(7)->willReturn(
            new Account(10, 7, 'Minha Conta', new \DateTimeImmutable())
        );
        $lock->expects($this->once())->method('acquire')->with(7);
        $lock->expects($this->once())->method('release')->with(7);
        $transactions->method('transactional')->willReturnCallback(fn (callable $action) => $action());

        $result = (new ResetProfileService(
            $purger, $provisioner, $transactions, $lock, $context
        ))->execute(true, ResetProfileService::CONFIRMATION_PHRASE);

        $this->assertSame('Minha Conta', $result->name);
    }

    private function service(): ResetProfileService
    {
        return new ResetProfileService(
            $this->createStub(UserDataPurger::class),
            $this->createStub(ProvisionUserService::class),
            $this->createStub(TransactionManager::class),
            $this->createStub(UserOperationLock::class),
            $this->createStub(UserContext::class),
        );
    }
}
