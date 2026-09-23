<?php

declare(strict_types=1);

namespace SGFP\Application\Services;

use SGFP\Application\Ports\AccountRepository;
use SGFP\Application\Ports\CommitmentRepository;
use SGFP\Application\Ports\EntryRepository;
use SGFP\Application\Ports\TransactionManager;
use SGFP\Application\Ports\UserContext;
use SGFP\Domain\Enums\CommitmentStatus;
use SGFP\Domain\Models\Entry;

final class SettleCommitmentService
{
    public function __construct(
        private readonly CommitmentRepository $commitmentRepository,
        private readonly EntryRepository $entryRepository,
        private readonly AccountRepository $accountRepository,
        private readonly TransactionManager $transactionManager,
        private readonly UserContext $userContext,
    ) {}

    public function execute(int $commitmentId, ?string $settledAt = null): Entry
    {
        $userId = $this->userContext->requireUserId();
        $this->userContext->requireCapability('use_sgfp');

        return $this->transactionManager->transactional(function () use ($commitmentId, $userId, $settledAt) {
            $commitment = $this->commitmentRepository->findById($commitmentId, $userId);

            if ($commitment === null) {
                throw new \InvalidArgumentException('Compromisso não encontrado.');
            }
            if ($commitment->status !== CommitmentStatus::PENDENTE) {
                $existing = $this->entryRepository->findByCommitmentId($commitmentId, $userId);
                if ($existing !== null && $existing->state === \SGFP\Domain\Enums\EntryState::ATIVO) {
                    return $existing;
                }
                throw new \InvalidArgumentException('O compromisso só pode ser efetivado se estiver pendente.');
            }

            $account = $this->accountRepository->findByUser($userId);
            if ($account === null || $account->id === null) {
                throw new \InvalidArgumentException('Conta Financeira não encontrada.');
            }

            try {
                $settledDate = $settledAt === null ? new \DateTimeImmutable() : new \DateTimeImmutable($settledAt);
            } catch (\Exception $e) {
                throw new \InvalidArgumentException('A data de efetivação deve ser um timestamp ISO-8601 válido.', 0, $e);
            }
            $settledCommitment = $this->commitmentRepository->save($commitment->settle());

            $existing = $this->entryRepository->findByCommitmentId($commitmentId, $userId);
            if ($existing !== null) {
                return $this->entryRepository->save($existing->withSettlement($settledCommitment, $settledDate));
            }

            return $this->entryRepository->save(
                Entry::fromCommitment($settledCommitment, $account->id, $settledDate)
            );
        });
    }
}
