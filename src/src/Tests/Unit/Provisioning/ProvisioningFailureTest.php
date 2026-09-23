<?php

declare(strict_types=1);

namespace SGFP\Tests\Unit\Provisioning;

use PHPUnit\Framework\TestCase;
use SGFP\Application\Ports\AccountRepository;
use SGFP\Application\Ports\CategoryRepository;
use SGFP\Application\Services\ProvisionUserService;
use SGFP\Domain\Models\Account;

final class ProvisioningFailureTest extends TestCase
{
    public function testInvalidUserDoesNotTouchRepositories(): void
    {
        $accounts = $this->createMock(AccountRepository::class);
        $categories = $this->createMock(CategoryRepository::class);
        $accounts->expects($this->never())->method('findByUser');
        $categories->expects($this->never())->method('seedDefaults');

        $this->expectException(\InvalidArgumentException::class);
        (new ProvisionUserService($accounts, $categories))->execute(0);
    }

    public function testCategoryFailureIsReportedAfterAccountWasEnsured(): void
    {
        $accounts = $this->createMock(AccountRepository::class);
        $categories = $this->createMock(CategoryRepository::class);
        $account = Account::create(7, 'Minha Conta', new \DateTimeImmutable())->withId(10);

        $accounts->expects($this->once())->method('findByUser')->with(7)->willReturn(null);
        $accounts->expects($this->once())->method('save')->with($this->isInstanceOf(Account::class))->willReturn($account);
        $categories->expects($this->once())->method('seedDefaults')->with(7)
            ->willThrowException(new \RuntimeException('database unavailable'));

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Falha ao provisionar categorias.');
        (new ProvisionUserService($accounts, $categories))->execute(7);
    }

    public function testRetryCanReuseExistingAccountAndSeedCategories(): void
    {
        $accounts = $this->createMock(AccountRepository::class);
        $categories = $this->createMock(CategoryRepository::class);
        $account = Account::create(7, 'Minha Conta', new \DateTimeImmutable())->withId(10);

        $accounts->expects($this->once())->method('findByUser')->with(7)->willReturn($account);
        $accounts->expects($this->never())->method('save');
        $categories->expects($this->once())->method('seedDefaults')->with(7);

        self::assertSame($account, (new ProvisionUserService($accounts, $categories))->execute(7));
    }
}
