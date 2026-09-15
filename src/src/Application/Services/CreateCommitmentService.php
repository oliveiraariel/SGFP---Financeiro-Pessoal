<?php

declare(strict_types=1);

namespace SGFP\Application\Services;

use SGFP\Application\Ports\CategoryRepository;
use SGFP\Application\Ports\CommitmentRepository;
use SGFP\Application\Ports\RecurrenceRepository;
use SGFP\Application\Ports\UserContext;
use SGFP\Domain\Enums\CommitmentNature;
use SGFP\Domain\Models\Commitment;
use SGFP\Domain\Models\Recurrence;

final class CreateCommitmentService
{
    public function __construct(
        private readonly CommitmentRepository $repository,
        private readonly CategoryRepository $categoryRepository,
        private readonly RecurrenceRepository $recurrenceRepository,
        private readonly UserContext $userContext,
    ) {}

    public function execute(
        ?int $categoryId,
        string $name,
        float $amount,
        string $nature,
        string $referenceMonth,
        ?int $recurrenceMonthsCount = null,
        ?string $recurrenceStart = null,
    ): Commitment {
        $userId = $this->userContext->requireUserId();
        $this->userContext->requireCapability('use_sgfp');

        $normalizedName = trim($name);
        if ($normalizedName === '') {
            throw new \InvalidArgumentException('O nome é obrigatório.');
        }
        if ($this->stringLength($normalizedName) > 180) {
            throw new \InvalidArgumentException('O nome deve ter no máximo 180 caracteres.');
        }
        if ($amount < 0) {
            throw new \InvalidArgumentException('O valor deve ser maior ou igual a zero.');
        }

        $commitmentNature = CommitmentNature::tryFrom($nature);
        if ($commitmentNature === null) {
            throw new \InvalidArgumentException('A natureza deve ser ENTRADA ou SAIDA.');
        }

        $month = \DateTimeImmutable::createFromFormat('!Y-m', $referenceMonth);
        $errors = \DateTimeImmutable::getLastErrors();
        if ($month === false || ($errors !== false && ($errors['warning_count'] > 0 || $errors['error_count'] > 0))) {
            throw new \InvalidArgumentException('O mês de referência deve estar no formato YYYY-MM.');
        }

        if ($categoryId !== null && $this->categoryRepository->findById($categoryId, $userId) === null) {
            throw new \InvalidArgumentException('Categoria não encontrada.');
        }

        $recurrenceId = null;
        if ($recurrenceMonthsCount !== null) {
            if ($recurrenceMonthsCount <= 0) {
                throw new \InvalidArgumentException('A quantidade de meses deve ser maior que zero ou omitida.');
            }

            if (!in_array($recurrenceStart, ['CURRENT', 'NEXT'], true)) {
                throw new \InvalidArgumentException('O início da recorrência deve ser CURRENT ou NEXT.');
            }

            $now = new \DateTimeImmutable('first day of this month');
            $month = $recurrenceStart === 'NEXT' ? $now->modify('+1 month') : $now;

            $recurrence = $this->recurrenceRepository->save(
                Recurrence::create($userId, $month, $recurrenceMonthsCount, new \DateTimeImmutable())
            );
            $recurrenceId = $recurrence->id;
        }

        return $this->repository->save(Commitment::create(
            $userId,
            $categoryId,
            $normalizedName,
            $amount,
            $commitmentNature,
            $month,
            new \DateTimeImmutable(),
            $recurrenceId,
        ));
    }

    private function stringLength(string $value): int
    {
        return function_exists('mb_strlen') ? mb_strlen($value, 'UTF-8') : strlen($value);
    }
}
