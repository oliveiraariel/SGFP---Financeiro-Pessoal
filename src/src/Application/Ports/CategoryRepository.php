<?php

declare(strict_types=1);

namespace SGFP\Application\Ports;

use SGFP\Domain\Models\Category;

interface CategoryRepository
{
    public function save(Category $category): Category;

    public function findById(int $id, int $userId): ?Category;

    public function findAllByUser(int $userId): array;

    public function existsByName(int $userId, string $name): bool;

    public function seedDefaults(int $userId): void;
}
