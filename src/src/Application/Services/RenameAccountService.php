<?php
declare(strict_types=1);
namespace SGFP\Application\Services;
use SGFP\Application\Ports\AccountRepository;
use SGFP\Application\Ports\UserContext;
use SGFP\Domain\Models\Account;
final class RenameAccountService {
    public function __construct(private readonly AccountRepository $repository, private readonly UserContext $context) {}
    public function execute(int $id, string $name): Account {
        $this->context->requireCapability('use_sgfp'); $userId = $this->context->requireUserId();
        $name = trim($name);
        if ($name === '' || mb_strlen($name, 'UTF-8') > 100) throw new \InvalidArgumentException('O nome da conta é obrigatório e deve ter no máximo 100 caracteres.');
        if ($this->repository->findById($id, $userId) === null) throw new \RuntimeException('Conta não encontrada.', 404);
        return $this->repository->rename($id, $userId, $name);
    }
}
