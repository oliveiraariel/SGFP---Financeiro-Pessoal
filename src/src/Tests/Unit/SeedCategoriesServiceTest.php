<?php

declare(strict_types=1);

namespace SGFP\Tests\Unit;

use PHPUnit\Framework\TestCase;
use SGFP\Application\Ports\CategoryRepository;
use SGFP\Application\Services\SeedCategoriesService;

final class SeedCategoriesServiceTest extends TestCase
{
    public function testSeedsDefaultCategoriesForUser(): void
    {
        $repository = $this->createMock(CategoryRepository::class);
        $repository->expects($this->once())->method('seedDefaults')->with(42);

        $service = new SeedCategoriesService($repository);
        $service->execute(42);
    }
}
