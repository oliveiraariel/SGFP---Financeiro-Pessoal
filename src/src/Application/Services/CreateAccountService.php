<?php

declare(strict_types=1);

namespace SGFP\Application\Services;

use SGFP\Application\Ports\AccountRepository;
use SGFP\Application\Ports\UserContext;
use SGFP\Domain\Enums\AccountRole;
use SGFP\Domain\Models\Account;
use SGFP\Domain\Policies\AccountPolicy;

final class CreateAccountService
{
    public function __construct(
        private readonly AccountRepository $repository,
        private readonly UserContext $userContext,
        private readonly AccountPolicy $policy,
        private readonly ?SeedCategoriesService $seedCategories = null,
    ) {
    }

    private function stringLength(string $value): int
    {
        return function_exists('mb_strlen') ? mb_strlen($value, 'UTF-8') : strlen($value);
    }

    public function execute(string $name, ?string $role = null): Account
    {
        $userId = $this->userContext->requireUserId();
        $this->userContext->requireCapability('use_sgfp');

        $normalizedName = trim($name);

        if ($normalizedName === '') {
            throw new \InvalidArgumentException('O nome da conta é obrigatório.');
        }

        if ($this->stringLength($normalizedName) > 120) {
            throw new \InvalidArgumentException('O nome da conta deve ter no máximo 120 caracteres.');
        }

        $count = $this->repository->countByUser($userId);

        if ($count === 0) {
            $account = Account::createPrincipal($userId, $normalizedName, new \DateTimeImmutable());
            $account = $this->repository->save($account);

            if ($this->seedCategories !== null) {
                $this->seedCategories->execute($userId);
            }

            return $account;
        } else {
            if ($role === AccountRole::PRINCIPAL->value && $this->repository->hasPrincipal($userId)) {
                throw new \InvalidArgumentException('O usuário já possui uma conta Principal.');
            }

            if ($role === null || $role === AccountRole::PRINCIPAL->value) {
                if ($this->repository->hasPrincipal($userId)) {
                    $role = AccountRole::SECUNDARIA->value;
                } else {
                    $role = AccountRole::PRINCIPAL->value;
                }
            }

            $accountRole = AccountRole::from($role);

            if ($accountRole === AccountRole::SECUNDARIA && !$this->policy->canCreateSecondary($userId)) {
                throw new \InvalidArgumentException('Não é possível criar conta Secundária sem uma conta Principal.');
            }

            $account = new Account(
                null,
                $userId,
                $normalizedName,
                $accountRole,
                new \DateTimeImmutable()
            );
        }

        return $this->repository->save($account);
    }
}
