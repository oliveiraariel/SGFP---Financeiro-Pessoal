<?php
declare(strict_types=1);
namespace SGFP\Application\Services;
use SGFP\Application\Ports\CategoryRepository;
use SGFP\Application\Ports\UserContext;
final class DeleteCategoryService
{
    public function __construct(private readonly CategoryRepository $repository, private readonly UserContext $context) {}
    public function execute(int $id): void
    {
        $this->context->requireCapability('use_sgfp'); $userId = $this->context->requireUserId();
        if ($this->repository->findById($id, $userId) === null) throw new \RuntimeException('Categoria não encontrada.', 404);
        $this->repository->delete($id, $userId);
    }
}
