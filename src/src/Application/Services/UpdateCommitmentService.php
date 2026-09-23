<?php

declare(strict_types=1);

namespace SGFP\Application\Services;

use SGFP\Application\Ports\CategoryRepository;
use SGFP\Application\Ports\CommitmentRepository;
use SGFP\Application\Ports\UserContext;
use SGFP\Domain\Models\Commitment;

final class UpdateCommitmentService
{
    public function __construct(
        private readonly CommitmentRepository $repository,
        private readonly CategoryRepository $categoryRepository,
        private readonly UserContext $userContext,
    ) {}

    public function execute(int $id, ?int $categoryId, string $name, string $amount, ?string $commitmentDate = null): Commitment
    {
        $userId = $this->userContext->requireUserId();
        $this->userContext->requireCapability('use_sgfp');
        $commitment = $this->repository->findById($id, $userId);
        if ($commitment === null) {
            throw new \RuntimeException('Compromisso não encontrado.', 404);
        }
        $name = trim($name);
        if ($name === '' || (function_exists('mb_strlen') ? mb_strlen($name, 'UTF-8') : strlen($name)) > 180) {
            throw new \InvalidArgumentException('O nome é obrigatório e deve ter no máximo 180 caracteres.');
        }
        if (preg_match('/^(?:0|[1-9]\d*)\.\d{2}$/', $amount) !== 1 || preg_match('/^0+\.00$/', $amount) === 1) {
            throw new \InvalidArgumentException('O valor deve usar duas casas decimais e ser maior que zero.');
        }
        if ($categoryId !== null && $this->categoryRepository->findById($categoryId, $userId) === null) {
            throw new \InvalidArgumentException('Categoria não encontrada.');
        }
        $date = $commitment->referenceMonth;
        if ($commitmentDate !== null) {
            $date = \DateTimeImmutable::createFromFormat('!Y-m-d', $commitmentDate);
            $errors = \DateTimeImmutable::getLastErrors();
            if (preg_match('/^\d{4}-(0[1-9]|1[0-2])-\d{2}$/', $commitmentDate) !== 1
                || $date === false
                || ($errors !== false && ($errors['warning_count'] > 0 || $errors['error_count'] > 0))
                || $date->format('Y-m-d') !== $commitmentDate) {
                throw new \InvalidArgumentException('A data do compromisso é inválida.');
            }
        }
        return $this->repository->save(new Commitment(
            $commitment->id, $commitment->userId, $categoryId, $commitment->recurrenceId,
            $name, $amount, $commitment->nature, $date,
            $commitment->status, $commitment->createdAt
        ));
    }
}
