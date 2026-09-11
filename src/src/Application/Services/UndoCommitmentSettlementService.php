<?php

declare(strict_types=1);

namespace SGFP\Application\Services;

use SGFP\Application\Ports\CommitmentRepository;
use SGFP\Application\Ports\EntryRepository;
use SGFP\Application\Ports\TransactionManager;
use SGFP\Application\Ports\UserContext;
use SGFP\Domain\Enums\CommitmentStatus;
use SGFP\Domain\Enums\EntryState;

final class UndoCommitmentSettlementService
{
    public function __construct(
        private readonly CommitmentRepository $commitmentRepository,
        private readonly EntryRepository $entryRepository,
        private readonly TransactionManager $transactionManager,
        private readonly UserContext $userContext,
    ) {
    }

    public function execute(int $commitmentId): void
    {
        $this->userContext->requireCapability('use_sgfp');
        $userId = $this->userContext->requireUserId();

        $commitment = $this->commitmentRepository->findById($commitmentId, $userId);
        if ($commitment === null) {
            throw new \RuntimeException('Compromisso não encontrado.', 404);
        }

        if ($commitment->status !== CommitmentStatus::EFETIVADO) {
            throw new \RuntimeException('O compromisso deve estar efetivado para ser desfeito.', 409);
        }

        $entry = $this->entryRepository->findByCommitmentId($commitmentId, $userId);
        if ($entry === null || $entry->state !== EntryState::ATIVO) {
            throw new \RuntimeException('Efeito do compromisso não encontrado ou já desfeito.', 409);
        }

        $now = new \DateTimeImmutable();

        $this->transactionManager->transactional(function () use ($commitment, $entry, $now) {
            $this->entryRepository->save($entry->withUndone($now));
            $this->commitmentRepository->save($commitment->undoSettlement());

            return null;
        });
    }
}
