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
    ) {
    }

    public function execute(int $userId): Account
    {
        if ($userId <= 0) {
            throw new \InvalidArgumentException('Usuário inválido para provisionamento.');
        }

        $accounts = $this->accountRepository->findAllByUser($userId);

        if (count($accounts) > 1) {
            throw new \RuntimeException(
                'Estado legado inconsistente: o usuário possui mais de uma Conta Financeira.'
            );
        }

        if (count($accounts) === 1) {
            $this->categoryRepository->seedDefaults($userId);
            return $accounts[0];
        }

        /*
         * AccountRole permanece temporariamente no modelo interno
         * apenas enquanto a migração do Schema/Transferências ainda
         * não foi executada. Para a V1 vigente existe uma única conta.
         */
        $account = Account::createPrincipal(
            $userId,
            'Minha Conta',
            new \DateTimeImmutable()
        );

        $account = $this->accountRepository->save($account);
        $this->categoryRepository->seedDefaults($userId);

        return $account;
    }
}
