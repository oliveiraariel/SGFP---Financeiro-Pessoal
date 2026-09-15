<?php
declare(strict_types=1);
namespace SGFP\Application\Services;
use SGFP\Application\Ports\CategoryRepository;
use SGFP\Application\Ports\UserContext;
use SGFP\Domain\Models\Category;
final class RenameCategoryService
{
    public function __construct(private readonly CategoryRepository $repository, private readonly UserContext $context) {}
    public function execute(int $id, string $name): Category
    {
        $this->context->requireCapability('use_sgfp'); $userId = $this->context->requireUserId(); $name = trim($name);
        if ($name === '' || (function_exists('mb_strlen') ? mb_strlen($name, 'UTF-8') : strlen($name)) > 100) throw new \InvalidArgumentException('O nome da categoria é obrigatório e deve ter no máximo 100 caracteres.');
        $category = $this->repository->findById($id, $userId); if ($category === null) throw new \RuntimeException('Categoria não encontrada.', 404);
        if ($category->name !== $name && $this->repository->existsByName($userId, $name)) throw new \InvalidArgumentException('Já existe uma categoria com esse nome.');
        return $this->repository->rename($id, $userId, $name);
    }
}
