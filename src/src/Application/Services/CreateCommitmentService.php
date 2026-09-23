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
        string $amount,
        string $nature,
        string $commitmentDate,
        ?int $recurrenceMonthsCount = null,
        bool $recurrenceEnabled = false,
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
        if (preg_match('/^(?:0|[1-9]\d*)\.\d{2}$/', $amount) !== 1 || preg_match('/^0+\.00$/', $amount) === 1) {
            throw new \InvalidArgumentException('O valor deve usar duas casas decimais e ser maior que zero.');
        }

        $commitmentNature = CommitmentNature::tryFrom($nature);
        if ($commitmentNature === null) {
            throw new \InvalidArgumentException('A natureza deve ser ENTRADA ou SAIDA.');
        }

        $format = preg_match('/^\d{4}-\d{2}-\d{2}$/', $commitmentDate) === 1 ? '!Y-m-d' : null;
        $month = $format === null ? false : \DateTimeImmutable::createFromFormat($format, $commitmentDate);
        $errors = \DateTimeImmutable::getLastErrors();
        if ($month === false || ($errors !== false && ($errors['warning_count'] > 0 || $errors['error_count'] > 0)) ||
            $month->format('Y-m-d') !== $commitmentDate) {
            throw new \InvalidArgumentException('A data do compromisso deve estar no formato YYYY-MM-DD.');
        }

        if ($categoryId !== null && $this->categoryRepository->findById($categoryId, $userId) === null) {
            throw new \InvalidArgumentException('Categoria não encontrada.');
        }

        $recurrenceId = null;
        if ($recurrenceEnabled && $recurrenceMonthsCount !== null && $recurrenceMonthsCount <= 0) {
            throw new \InvalidArgumentException('A quantidade de meses deve ser maior que zero ou omitida.');
        }
        if ($recurrenceEnabled) {
            $recurrenceStartDate = $month->modify('first day of this month');
            $recurrence = $this->recurrenceRepository->save(
                Recurrence::create($userId, $recurrenceStartDate, $recurrenceMonthsCount, new \DateTimeImmutable())
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
