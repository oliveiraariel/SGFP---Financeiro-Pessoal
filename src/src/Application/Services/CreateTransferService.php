<?php

declare(strict_types=1);

namespace SGFP\Application\Services;

use SGFP\Application\Ports\AccountRepository;
use SGFP\Application\Ports\CommitmentRepository;
use SGFP\Application\Ports\TransactionManager;
use SGFP\Application\Ports\TransferRepository;
use SGFP\Application\Ports\UserContext;
use SGFP\Domain\Enums\AccountRole;
use SGFP\Domain\Enums\CommitmentNature;
use SGFP\Domain\Enums\CommitmentType;
use SGFP\Domain\Models\Commitment;
use SGFP\Domain\Models\Transfer;

final class CreateTransferService
{
    public function __construct(
        private readonly AccountRepository $accountRepository,
        private readonly CommitmentRepository $commitmentRepository,
        private readonly TransferRepository $transferRepository,
        private readonly TransactionManager $transactionManager,
        private readonly UserContext $userContext,
    ) {
    }

    public function execute(
        string $name,
        float $amount,
        string $referenceMonth,
        int $sourceAccountId,
        int $targetAccountId,
    ): array {
        $this->userContext->requireCapability('use_sgfp');
        $userId = $this->userContext->requireUserId();

        $source = $this->accountRepository->findById($sourceAccountId, $userId);
        $target = $this->accountRepository->findById($targetAccountId, $userId);

        if ($source === null || $target === null) {
            throw new \RuntimeException('Conta não encontrada.', 404);
        }

        if ($source->id === $target->id) {
            throw new \RuntimeException('Contas de origem e destino devem ser distintas.', 422);
        }

        $this->assertPrincipalSecondaryPair($source->role, $target->role);

        $month = new \DateTimeImmutable($referenceMonth . '-01');
        $now = new \DateTimeImmutable();
        $nature = $this->deriveNature($source->role, $target->role);

        return $this->transactionManager->transactional(function () use ($userId, $name, $amount, $month, $now, $nature, $source, $target) {
            $commitment = Commitment::create(
                $userId,
                null,
                $name,
                $amount,
                CommitmentType::TRANSFERENCIA,
                $nature,
                $month,
                $now,
            );
            $commitment = $this->commitmentRepository->save($commitment);

            $transfer = new Transfer(
                (int) $commitment->id,
                $userId,
                (int) $source->id,
                (int) $target->id,
            );
            $this->transferRepository->save($transfer);

            return [$commitment, $transfer];
        });
    }

    private function assertPrincipalSecondaryPair(AccountRole $source, AccountRole $target): void
    {
        $pair = [$source, $target];
        $expected = [AccountRole::PRINCIPAL, AccountRole::SECUNDARIA];

        if ($pair !== $expected && $pair !== array_reverse($expected)) {
            throw new \RuntimeException('Transferências devem ocorrer entre a conta Principal e uma conta Secundária.', 422);
        }
    }

    private function deriveNature(AccountRole $source, AccountRole $target): CommitmentNature
    {
        return $source === AccountRole::PRINCIPAL
            ? CommitmentNature::SAIDA
            : CommitmentNature::ENTRADA;
    }
}
