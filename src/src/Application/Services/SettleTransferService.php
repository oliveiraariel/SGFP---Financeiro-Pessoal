<?php

declare(strict_types=1);

namespace SGFP\Application\Services;

use SGFP\Application\Ports\CommitmentRepository;
use SGFP\Application\Ports\EntryRepository;
use SGFP\Application\Ports\TransactionManager;
use SGFP\Application\Ports\TransferRepository;
use SGFP\Application\Ports\UserContext;
use SGFP\Domain\Enums\CommitmentStatus;
use SGFP\Domain\Enums\EntryEffectType;
use SGFP\Domain\Enums\EntryOrigin;
use SGFP\Domain\Enums\EntryState;
use SGFP\Domain\Models\Entry;

final class SettleTransferService
{
    public function __construct(
        private readonly CommitmentRepository $commitmentRepository,
        private readonly TransferRepository $transferRepository,
        private readonly EntryRepository $entryRepository,
        private readonly TransactionManager $transactionManager,
        private readonly UserContext $userContext,
    ) {
    }

    public function execute(int $commitmentId): array
    {
        $this->userContext->requireCapability('use_sgfp');
        $userId = $this->userContext->requireUserId();

        $commitment = $this->commitmentRepository->findById($commitmentId, $userId);
        if ($commitment === null) {
            throw new \RuntimeException('Compromisso não encontrado.', 404);
        }

        if ($commitment->status !== CommitmentStatus::PENDENTE) {
            throw new \RuntimeException('O compromisso deve estar pendente para ser efetivado.', 409);
        }

        $transfer = $this->transferRepository->findByCommitmentId($commitmentId, $userId);
        if ($transfer === null) {
            throw new \RuntimeException('Transferência não encontrada.', 404);
        }

        $now = new \DateTimeImmutable();

        return $this->transactionManager->transactional(function () use ($commitment, $transfer, $userId, $now) {
            $outflow = new Entry(
                null,
                $userId,
                $transfer->sourceAccountId,
                $commitment->id,
                EntryOrigin::COMPROMISSO,
                $commitment->name,
                $commitment->amount,
                EntryEffectType::SAIDA,
                $now,
                null,
                EntryState::ATIVO,
                $now,
                null,
            );

            $inflow = new Entry(
                null,
                $userId,
                $transfer->targetAccountId,
                $commitment->id,
                EntryOrigin::COMPROMISSO,
                $commitment->name,
                $commitment->amount,
                EntryEffectType::ENTRADA,
                $now,
                null,
                EntryState::ATIVO,
                $now,
                null,
            );

            $savedOutflow = $this->entryRepository->save($outflow);
            $savedInflow = $this->entryRepository->save($inflow);
            $settledCommitment = $this->commitmentRepository->save($commitment->settle());

            return [$settledCommitment, $savedOutflow, $savedInflow];
        });
    }
}
