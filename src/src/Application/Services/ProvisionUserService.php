<?php

declare(strict_types=1);

namespace SGFP\Application\Services;

use SGFP\Application\Ports\AccountRepository;
use SGFP\Application\Ports\CategoryRepository;
use SGFP\Domain\Models\Account;

final class ProvisionUserService
{
    public function __construct(
        private readonly AccountRepository $accountRepository,
        private readonly CategoryRepository $categoryRepository,
    ) {}

    public function execute(int $userId): Account
    {
        if ($userId <= 0) {
            throw new \InvalidArgumentException('Usuário inválido para provisionamento.');
        }

        $account = $this->accountRepository->findByUser($userId);

        if ($account === null) {
            $account = $this->accountRepository->save(
                Account::create($userId, 'Minha Conta', new \DateTimeImmutable())
            );
        }

        try {
            $this->categoryRepository->seedDefaults($userId);
        } catch (\Throwable $e) {
            throw new \RuntimeException('Falha ao provisionar categorias: ' . $e->getMessage(), 0, $e);
        }

        return $account;
    }
}
