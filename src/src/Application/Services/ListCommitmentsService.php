<?php

declare(strict_types=1);

namespace SGFP\Application\Services;

use SGFP\Application\Ports\CommitmentRepository;
use SGFP\Application\Ports\UserContext;

final class ListCommitmentsService
{
    public function __construct(
        private readonly CommitmentRepository $repository,
        private readonly UserContext $userContext,
    ) {}

    /** @return array{items: array<int, object>, pagination: array{page: int, per_page: int, total: int, total_pages: int}} */
    public function execute(?string $month = null, int $page = 1, int $perPage = 50): array
    {
        $userId = $this->userContext->requireUserId();
        $this->userContext->requireCapability('use_sgfp');
        $items = $this->repository->findAllByUser($userId);

        if ($month !== null) {
            $date = \DateTimeImmutable::createFromFormat('!Y-m-d', $month);
            $errors = \DateTimeImmutable::getLastErrors();
            if ($date === false || ($errors !== false && ($errors['warning_count'] > 0 || $errors['error_count'] > 0)) || $date->format('Y-m-d') !== $month) {
                throw new \InvalidArgumentException('O mês deve estar no formato YYYY-MM-01.');
            }
            // A competência mensal deve incluir qualquer dia do mês selecionado.
            // O dia permanece relevante para a ordenação da agenda e para
            // recorrências, mas não deve excluir o compromisso da competência.
            $items = array_values(array_filter($items, static fn (object $item): bool => $item->referenceMonth->format('Y-m') === substr($month, 0, 7)));
        }

        $total = count($items);
        $page = max(1, $page);
        $perPage = min(100, max(1, $perPage));
        $items = array_slice($items, ($page - 1) * $perPage, $perPage);

        return [
            'items' => $items,
            'pagination' => ['page' => $page, 'per_page' => $perPage, 'total' => $total, 'total_pages' => $total === 0 ? 0 : (int) ceil($total / $perPage)],
        ];
    }

    public function find(int $id): object
    {
        $userId = $this->userContext->requireUserId();
        $this->userContext->requireCapability('use_sgfp');
        $item = $this->repository->findById($id, $userId);
        if ($item === null) throw new \RuntimeException('Compromisso não encontrado.', 404);
        return $item;
    }
}
