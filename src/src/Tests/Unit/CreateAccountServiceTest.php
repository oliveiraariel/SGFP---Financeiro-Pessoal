<?php

declare(strict_types=1);

namespace SGFP\Tests\Unit;

use PHPUnit\Framework\TestCase;
use SGFP\Application\Ports\AccountRepository;
use SGFP\Application\Ports\UserContext;
use SGFP\Application\Services\CreateAccountService;
use SGFP\Domain\Enums\AccountRole;
use SGFP\Domain\Models\Account;
use SGFP\Domain\Policies\AccountPolicy;

final class CreateAccountServiceTest extends TestCase
{
    public function testFirstAccountBecomesPrincipal(): void
    {
        $repository = $this->createMock(AccountRepository::class);
        $userContext = $this->createMock(UserContext::class);
        $policy = $this->createMock(AccountPolicy::class);

        $userContext->method('requireUserId')->willReturn(1);
        $userContext->expects($this->once())->method('requireCapability')->with('use_sgfp');

        $repository->method('countByUser')->with(1)->willReturn(0);
        $repository->expects($this->once())->method('save')->willReturnCallback(
            function (Account $account): Account {
                $this->assertSame(AccountRole::PRINCIPAL, $account->role);
                $this->assertSame('Minha Conta', $account->name);
                $this->assertSame(1, $account->userId);
                return $account->withId(10);
            }
        );

        $service = new CreateAccountService($repository, $userContext, $policy);
        $result = $service->execute('Minha Conta');

        $this->assertSame(10, $result->id);
        $this->assertSame(AccountRole::PRINCIPAL, $result->role);
    }

    public function testSecondAccountBecomesSecondaryWhenPrincipalExists(): void
    {
        $repository = $this->createMock(AccountRepository::class);
        $userContext = $this->createMock(UserContext::class);
        $policy = $this->createMock(AccountPolicy::class);

        $userContext->method('requireUserId')->willReturn(1);
        $userContext->expects($this->once())->method('requireCapability')->with('use_sgfp');

        $repository->method('countByUser')->with(1)->willReturn(1);
        $repository->method('hasPrincipal')->with(1)->willReturn(true);
        $policy->method('canCreateSecondary')->with(1)->willReturn(true);

        $repository->expects($this->once())->method('save')->willReturnCallback(
            function (Account $account): Account {
                $this->assertSame(AccountRole::SECUNDARIA, $account->role);
                return $account->withId(11);
            }
        );

        $service = new CreateAccountService($repository, $userContext, $policy);
        $result = $service->execute('Conta Secundária');

        $this->assertSame(11, $result->id);
        $this->assertSame(AccountRole::SECUNDARIA, $result->role);
    }

    public function testEmptyNameThrows(): void
    {
        $repository = $this->createMock(AccountRepository::class);
        $userContext = $this->createMock(UserContext::class);
        $policy = $this->createMock(AccountPolicy::class);

        $userContext->method('requireUserId')->willReturn(1);

        $service = new CreateAccountService($repository, $userContext, $policy);

        $this->expectException(\InvalidArgumentException::class);
        $service->execute('   ');
    }
}
