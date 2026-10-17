<?php

declare(strict_types=1);

namespace SGFP\Application\Services;

use SGFP\Application\Ports\CategoryRepository;
use SGFP\Application\Ports\CommitmentRepository;
use SGFP\Application\Ports\TransactionManager;
use SGFP\Application\Ports\UserContext;
use SGFP\Domain\Models\Commitment;

final class UpdateRecurrenceFromOccurrenceService
{
    public function __construct(
        private readonly CommitmentRepository $commitments,
        private readonly CategoryRepository $categories,
        private readonly TransactionManager $transactions,
        private readonly UserContext $context,
    ) {}

    public function execute(int $recurrenceId, string $month, ?int $categoryId, string $name, string $amount, ?string $commitmentDate, string $scope): Commitment
    {
        $userId = $this->context->requireUserId();
        $this->context->requireCapability('use_sgfp');
        if (!in_array($scope, ['current', 'current_and_future'], true)) {
            throw new \InvalidArgumentException('O alcance da edição é inválido.');
        }
        $date = \DateTimeImmutable::createFromFormat('!Y-m-d', $month);
        if (!$date || $date->format('Y-m-d') !== $month) throw new \InvalidArgumentException('O mês da ocorrência é inválido.');
        if ($categoryId !== null && $this->categories->findById($categoryId, $userId) === null) throw new \InvalidArgumentException('Categoria não encontrada.');
        $name = trim($name);
        if ($name === '' || strlen($name) > 180) throw new \InvalidArgumentException('O nome é obrigatório e deve ter no máximo 180 caracteres.');
        if (preg_match('/^(?:0|[1-9]\d*)\.\d{2}$/', $amount) !== 1 || preg_match('/^0+\.00$/', $amount) === 1) throw new \InvalidArgumentException('O valor deve usar duas casas decimais e ser maior que zero.');
        $newDate = null;
        if ($commitmentDate !== null) {
            $newDate = \DateTimeImmutable::createFromFormat('!Y-m-d', $commitmentDate);
            if (!$newDate || $newDate->format('Y-m-d') !== $commitmentDate) throw new \InvalidArgumentException('A data do compromisso é inválida.');
        }
        return $this->transactions->transactional(function () use ($scope, $recurrenceId, $month, $userId, $categoryId, $name, $amount, $newDate): Commitment {
            // Resolve the pivot inside the transaction so a concurrent settlement
            // cannot be bypassed by a stale pre-transaction read.
            $current = $this->commitments->findByRecurrenceIdAndMonth($recurrenceId, $month, $userId);
            if ($current === null) throw new \RuntimeException('Ocorrência não encontrada.', 404);
            if ($current->status->value !== 'PENDENTE') throw new \RuntimeException('Desfaça a ocorrência antes de editá-la.', 409);
            $updated = $this->update($current, $categoryId, $name, $amount, $newDate ?? $current->referenceMonth);
            $this->commitments->save($updated);
            if ($scope === 'current_and_future') {
                $base = $this->commitments->findFirstByRecurrenceId($recurrenceId, $userId);
                if ($base !== null && $base->status->value === 'PENDENTE' && $base->id !== $current->id) {
                    $this->commitments->save($this->update($base, $categoryId, $name, $amount, $base->referenceMonth));
                }
                foreach ($this->commitments->findPendingByRecurrenceIdFromMonth($recurrenceId, $month, $userId) as $future) {
                    if ($future->id !== $current->id) $this->commitments->save($this->update($future, $categoryId, $name, $amount, $future->referenceMonth));
                }
            }
            return $updated;
        });
    }

    private function update(Commitment $item, ?int $categoryId, string $name, string $amount, \DateTimeImmutable $date): Commitment
    {
        return new Commitment($item->id, $item->userId, $categoryId, $item->recurrenceId, $name, $amount, $item->nature, $date, $item->status, $item->createdAt);
    }
}
