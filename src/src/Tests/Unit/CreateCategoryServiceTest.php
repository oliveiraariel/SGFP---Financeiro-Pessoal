<?php

declare(strict_types=1);

namespace SGFP\Tests\Unit;

use PHPUnit\Framework\TestCase;
use SGFP\Application\Ports\CategoryRepository;
use SGFP\Application\Ports\UserContext;
use SGFP\Application\Services\CreateCategoryService;
use SGFP\Domain\Models\Category;

final class CreateCategoryServiceTest extends TestCase
{
    public function testCreatesCategoryWithoutType(): void
    {
        $repository = $this->createMock(CategoryRepository::class);
        $userContext = $this->createMock(UserContext::class);

        $userContext->method('requireUserId')->willReturn(1);
        $userContext->expects($this->once())->method('requireCapability')->with('use_sgfp');

        $repository->method('existsByName')->with(1, 'Moradia')->willReturn(false);
        $repository->expects($this->once())->method('save')->willReturnCallback(
            function (Category $category): Category {
                $this->assertSame(1, $category->userId);
                $this->assertSame('Moradia', $category->name);
                return $category->withId(7);
            }
        );

        $service = new CreateCategoryService($repository, $userContext);
        $result = $service->execute('Moradia');

        $this->assertSame(7, $result->id);
        $this->assertSame('Moradia', $result->name);
    }

    public function testTrimsName(): void
    {
        $repository = $this->createMock(CategoryRepository::class);
        $userContext = $this->createMock(UserContext::class);

        $userContext->method('requireUserId')->willReturn(1);
        $repository->method('existsByName')->with(1, 'Alimentação')->willReturn(false);
        $repository->expects($this->once())->method('save')->willReturnCallback(
            function (Category $category): Category {
                $this->assertSame('Alimentação', $category->name);
                return $category->withId(8);
            }
        );

        $service = new CreateCategoryService($repository, $userContext);
        $result = $service->execute('  Alimentação  ');

        $this->assertSame('Alimentação', $result->name);
    }

    public function testEmptyNameThrows(): void
    {
        $repository = $this->createMock(CategoryRepository::class);
        $userContext = $this->createMock(UserContext::class);

        $userContext->method('requireUserId')->willReturn(1);

        $service = new CreateCategoryService($repository, $userContext);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('O nome da categoria é obrigatório.');
        $service->execute('   ');
    }

    public function testNameLongerThanOneHundredCharactersThrows(): void
    {
        $repository = $this->createMock(CategoryRepository::class);
        $userContext = $this->createMock(UserContext::class);

        $userContext->method('requireUserId')->willReturn(1);

        $service = new CreateCategoryService($repository, $userContext);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('O nome da categoria deve ter no máximo 100 caracteres.');
        $service->execute(str_repeat('a', 101));
    }

    public function testDuplicateNameThrows(): void
    {
        $repository = $this->createMock(CategoryRepository::class);
        $userContext = $this->createMock(UserContext::class);

        $userContext->method('requireUserId')->willReturn(1);
        $repository->method('existsByName')->with(1, 'Transporte')->willReturn(true);

        $service = new CreateCategoryService($repository, $userContext);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Já existe uma categoria com esse nome.');
        $service->execute('Transporte');
    }
}
