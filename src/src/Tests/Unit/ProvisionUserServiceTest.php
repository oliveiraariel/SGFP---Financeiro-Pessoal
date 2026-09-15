<?php

declare(strict_types=1);

namespace SGFP\Tests\Unit;

use PHPUnit\Framework\TestCase;
use SGFP\Application\Ports\AccountRepository;
use SGFP\Application\Ports\CategoryRepository;
use SGFP\Application\Services\ProvisionUserService;
use SGFP\Domain\Models\Account;

final class ProvisionUserServiceTest extends TestCase
{
    public function testCreatesMinhaContaAndSeedsDefaultsForNewUser(): void
    {
        $accounts = $this->createMock(AccountRepository::class);
        $categories = $this->createMock(CategoryRepository::class);

        $accounts->method('findByUser')->with(7)->willReturn(null);
        $accounts->expects($this->once())->method('save')->willReturnCallback(
            function (Account $account): Account {
                $this->assertSame(7, $account->userId);
                $this->assertSame('Minha Conta', $account->name);
                return $account->withId(10);
            }
        );
        $categories->expects($this->once())->method('seedDefaults')->with(7);

        $result = (new ProvisionUserService($accounts, $categories))->execute(7);

        $this->assertSame(10, $result->id);
        $this->assertSame('Minha Conta', $result->name);
    }

    public function testProvisioningIsIdempotentWhenAccountAlreadyExists(): void
    {
        $accounts = $this->createMock(AccountRepository::class);
        $categories = $this->createMock(CategoryRepository::class);

        $existing = Account::create(
            7,
            'Conta Pessoal',
            new \DateTimeImmutable('2026-09-14T00:00:00+00:00')
        )->withId(10);

        $accounts->method('findByUser')->with(7)->willReturn($existing);
        $accounts->expects($this->never())->method('save');
        $categories->expects($this->once())->method('seedDefaults')->with(7);

        $result = (new ProvisionUserService($accounts, $categories))->execute(7);

        $this->assertSame($existing, $result);
    }
}
