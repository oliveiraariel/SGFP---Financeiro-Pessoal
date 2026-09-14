<?php

declare(strict_types=1);

namespace SGFP\Tests\Unit;

use PHPUnit\Framework\TestCase;
use SGFP\Application\Ports\AccountRepository;
use SGFP\Application\Ports\CategoryRepository;
use SGFP\Application\Services\ProvisionUserService;
use SGFP\Domain\Enums\AccountRole;
use SGFP\Domain\Models\Account;

final class ProvisionUserServiceTest extends TestCase
{
    public function testCreatesMinhaContaAndSeedsDefaultsForNewUser(): void
    {
        $accounts = $this->createMock(AccountRepository::class);
        $categories = $this->createMock(CategoryRepository::class);

        $accounts->method('findAllByUser')->with(7)->willReturn([]);
        $accounts->expects($this->once())
            ->method('save')
            ->willReturnCallback(function (Account $account): Account {
                $this->assertSame(7, $account->userId);
                $this->assertSame('Minha Conta', $account->name);

                // Compatibilidade transitória até a migração do Schema.
                $this->assertSame(AccountRole::PRINCIPAL, $account->role);

                return $account->withId(10);
            });

        $categories->expects($this->once())
            ->method('seedDefaults')
            ->with(7);

        $service = new ProvisionUserService($accounts, $categories);
        $result = $service->execute(7);

        $this->assertSame(10, $result->id);
        $this->assertSame('Minha Conta', $result->name);
    }

    public function testProvisioningIsIdempotentWhenSingleAccountAlreadyExists(): void
    {
        $accounts = $this->createMock(AccountRepository::class);
        $categories = $this->createMock(CategoryRepository::class);

        $existing = Account::createPrincipal(
            7,
            'Minha Conta',
            new \DateTimeImmutable('2026-09-14T00:00:00+00:00')
        )->withId(10);

        $accounts->method('findAllByUser')->with(7)->willReturn([$existing]);
        $accounts->expects($this->never())->method('save');

        $categories->expects($this->once())
            ->method('seedDefaults')
            ->with(7);

        $service = new ProvisionUserService($accounts, $categories);
        $result = $service->execute(7);

        $this->assertSame($existing, $result);
    }

    public function testRejectsLegacyStateWithMoreThanOneAccount(): void
    {
        $accounts = $this->createMock(AccountRepository::class);
        $categories = $this->createMock(CategoryRepository::class);

        $first = Account::createPrincipal(
            7,
            'Minha Conta',
            new \DateTimeImmutable()
        )->withId(10);

        $second = Account::createSecondary(
            7,
            'Conta Legada',
            new \DateTimeImmutable()
        )->withId(11);

        $accounts->method('findAllByUser')->with(7)->willReturn([$first, $second]);
        $accounts->expects($this->never())->method('save');
        $categories->expects($this->never())->method('seedDefaults');

        $service = new ProvisionUserService($accounts, $categories);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('mais de uma Conta Financeira');

        $service->execute(7);
    }
}
