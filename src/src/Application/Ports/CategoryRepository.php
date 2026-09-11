<?php

declare(strict_types=1);

namespace SGFP\Application\Ports;

use SGFP\Domain\Enums\CategoryType;
use SGFP\Domain\Models\Category;

interface CategoryRepository
{
    public function save(Category $category): Category;

    public function findById(int $id, int $userId): ?Category;

    public function findAllByUser(int $userId): array;

    public function existsByNameAndType(int $userId, string $name, CategoryType $type): bool;

    public function seedDefaults(int $userId): void;
}
