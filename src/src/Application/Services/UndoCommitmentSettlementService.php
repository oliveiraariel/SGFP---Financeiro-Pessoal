<?php

declare(strict_types=1);

namespace SGFP\Application\Services;

use SGFP\Application\Ports\CommitmentRepository;
use SGFP\Application\Ports\EntryRepository;
use SGFP\Application\Ports\TransactionManager;
use SGFP\Application\Ports\UserContext;
use SGFP\Domain\Enums\CommitmentStatus;
use SGFP\Domain\Enums\EntryState;
use SGFP\Domain\Models\Commitment;

final class UndoCommitmentSettlementService
{
    public function __construct(
        private readonly CommitmentRepository $commitmentRepository,
        private readonly EntryRepository $entryRepository,
        private readonly TransactionManager $transactionManager,
        private readonly UserContext $userContext,
    ) {
    }

    public function execute(int $commitmentId): ?Commitment
    {
        $this->userContext->requireCapability('use_sgfp');
        $userId = $this->userContext->requireUserId();

        $commitment = $this->commitmentRepository->findById($commitmentId, $userId);
        if ($commitment === null) {
            throw new \RuntimeException('Compromisso não encontrado.', 404);
        }

        if ($commitment->status !== CommitmentStatus::EFETIVADO) {
            return $commitment;
        }

        $entry = $this->entryRepository->findByCommitmentId($commitmentId, $userId);
        if ($entry === null || $entry->state !== EntryState::ATIVO) {
            return $commitment;
        }

        $now = new \DateTimeImmutable();

        return $this->transactionManager->transactional(function () use ($commitment, $entry, $now) {
            $this->entryRepository->save($entry->withUndone($now));
            $undone = $commitment->undoSettlement();
            $this->commitmentRepository->save($undone);

            return $undone;
        });
    }
}
