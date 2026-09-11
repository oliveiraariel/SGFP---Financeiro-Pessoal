<?php

declare(strict_types=1);

namespace SGFP\Application\Services;

use SGFP\Application\Ports\AccountRepository;
use SGFP\Application\Ports\CommitmentRepository;
use SGFP\Application\Ports\EntryRepository;
use SGFP\Application\Ports\TransactionManager;
use SGFP\Application\Ports\UserContext;
use SGFP\Domain\Enums\CommitmentStatus;
use SGFP\Domain\Enums\CommitmentType;
use SGFP\Domain\Models\Entry;

final class SettleCommitmentService
{
    public function __construct(
        private readonly CommitmentRepository $commitmentRepository,
        private readonly EntryRepository $entryRepository,
        private readonly AccountRepository $accountRepository,
        private readonly TransactionManager $transactionManager,
        private readonly UserContext $userContext,
    ) {
    }

    public function execute(int $commitmentId): Entry
    {
        $userId = $this->userContext->requireUserId();
        $this->userContext->requireCapability('use_sgfp');

        return $this->transactionManager->transactional(function () use ($commitmentId, $userId) {
            $commitment = $this->commitmentRepository->findById($commitmentId, $userId);

            if ($commitment === null) {
                throw new \InvalidArgumentException('Compromisso não encontrado.');
            }

            if ($commitment->type === CommitmentType::TRANSFERENCIA) {
                throw new \InvalidArgumentException('Transferências devem ser efetivadas pelo fluxo próprio de transferência.');
            }

            if ($commitment->status !== CommitmentStatus::PENDENTE) {
                throw new \InvalidArgumentException('O compromisso só pode ser efetivado se estiver pendente.');
            }

            $principalAccount = $this->accountRepository->findPrincipal($userId);

            if ($principalAccount === null || $principalAccount->id === null) {
                throw new \InvalidArgumentException('Conta principal não encontrada.');
            }

            $now = new \DateTimeImmutable();
            $settledCommitment = $commitment->settle();
            $this->commitmentRepository->save($settledCommitment);

            $entry = Entry::fromCommitment($settledCommitment, $principalAccount->id, $now);

            return $this->entryRepository->save($entry);
        });
    }
}
