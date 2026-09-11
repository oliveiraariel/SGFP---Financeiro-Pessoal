<?php

declare(strict_types=1);

namespace SGFP\Application\Services;

use SGFP\Application\Ports\AccountRepository;
use SGFP\Application\Ports\CategoryRepository;
use SGFP\Application\Ports\CommitmentRepository;
use SGFP\Application\Ports\UserContext;
use SGFP\Domain\Enums\CommitmentType;
use SGFP\Domain\Models\Commitment;

final class CreateCommitmentService
{
    public function __construct(
        private readonly CommitmentRepository $repository,
        private readonly AccountRepository $accountRepository,
        private readonly CategoryRepository $categoryRepository,
        private readonly UserContext $userContext,
    ) {
    }

    public function execute(
        int $accountId,
        ?int $categoryId,
        string $description,
        float $amount,
        string $type,
        string $dueDate,
    ): Commitment {
        $userId = $this->userContext->requireUserId();
        $this->userContext->requireCapability('use_sgfp');

        $normalizedDescription = trim($description);

        if ($normalizedDescription === '') {
            throw new \InvalidArgumentException('A descrição é obrigatória.');
        }

        if ($this->stringLength($normalizedDescription) > 255) {
            throw new \InvalidArgumentException('A descrição deve ter no máximo 255 caracteres.');
        }

        if ($amount <= 0) {
            throw new \InvalidArgumentException('O valor deve ser maior que zero.');
        }

        $commitmentType = CommitmentType::tryFrom($type);

        if ($commitmentType === null) {
            throw new \InvalidArgumentException('O tipo deve ser RECEITA ou DESPESA.');
        }

        $due = \DateTimeImmutable::createFromFormat('Y-m-d', $dueDate);

        if ($due === false) {
            throw new \InvalidArgumentException('A data de vencimento deve estar no formato YYYY-MM-DD.');
        }

        $account = $this->accountRepository->findById($accountId, $userId);

        if ($account === null) {
            throw new \InvalidArgumentException('Conta não encontrada.');
        }

        if ($categoryId !== null) {
            $category = $this->categoryRepository->findById($categoryId, $userId);

            if ($category === null) {
                throw new \InvalidArgumentException('Categoria não encontrada.');
            }

            if ($category->type->value !== $type) {
                throw new \InvalidArgumentException('A categoria deve ser do mesmo tipo do compromisso.');
            }
        }

        $commitment = Commitment::create(
            $userId,
            $accountId,
            $categoryId,
            $normalizedDescription,
            $amount,
            $commitmentType,
            $due,
            new \DateTimeImmutable()
        );

        return $this->repository->save($commitment);
    }

    private function stringLength(string $value): int
    {
        return function_exists('mb_strlen') ? mb_strlen($value, 'UTF-8') : strlen($value);
    }
}
