<?php

declare(strict_types=1);

namespace SGFP\Application\Services;

use SGFP\Application\Ports\CommitmentRepository;
use SGFP\Application\Ports\UserContext;
use SGFP\Domain\Enums\CommitmentStatus;

final class DeleteCommitmentService
{
    public function __construct(
        private readonly CommitmentRepository $repository,
        private readonly UserContext $userContext,
    ) {}

    public function execute(int $id): void
    {
        $this->userContext->requireCapability('use_sgfp');
        $userId = $this->userContext->requireUserId();
        $commitment = $this->repository->findById($id, $userId);
        if ($commitment === null) {
            throw new \RuntimeException('Compromisso não encontrado.', 404);
        }
        if ($commitment->status !== CommitmentStatus::PENDENTE) {
            throw new \RuntimeException('Somente compromissos pendentes podem ser excluídos.', 409);
        }

        $this->repository->save($commitment->delete());
    }
}
