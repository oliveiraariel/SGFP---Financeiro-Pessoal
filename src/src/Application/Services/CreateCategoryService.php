<?php

declare(strict_types=1);

namespace SGFP\Application\Services;

use SGFP\Application\Ports\CategoryRepository;
use SGFP\Application\Ports\UserContext;
use SGFP\Domain\Models\Category;

final class CreateCategoryService
{
    public function __construct(
        private readonly CategoryRepository $repository,
        private readonly UserContext $userContext,
    ) {
    }

    public function execute(string $name): Category
    {
        $userId = $this->userContext->requireUserId();
        $this->userContext->requireCapability('use_sgfp');

        $normalizedName = trim($name);

        if ($normalizedName === '') {
            throw new \InvalidArgumentException('O nome da categoria é obrigatório.');
        }

        if ($this->stringLength($normalizedName) > 100) {
            throw new \InvalidArgumentException('O nome da categoria deve ter no máximo 100 caracteres.');
        }

        if ($this->repository->existsByName($userId, $normalizedName)) {
            throw new \InvalidArgumentException('Já existe uma categoria com esse nome.');
        }

        $category = Category::create($userId, $normalizedName, new \DateTimeImmutable());

        return $this->repository->save($category);
    }

    private function stringLength(string $value): int
    {
        return function_exists('mb_strlen') ? mb_strlen($value, 'UTF-8') : strlen($value);
    }
}
