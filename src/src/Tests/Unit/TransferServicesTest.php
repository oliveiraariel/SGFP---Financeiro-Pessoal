<?php

declare(strict_types=1);

namespace SGFP\Tests\Unit;

use PHPUnit\Framework\TestCase;
use SGFP\Application\Ports\AccountRepository;
use SGFP\Application\Ports\CommitmentRepository;
use SGFP\Application\Ports\EntryRepository;
use SGFP\Application\Ports\TransactionManager;
use SGFP\Application\Ports\TransferRepository;
use SGFP\Application\Ports\UserContext;
use SGFP\Application\Services\CreateTransferService;
use SGFP\Application\Services\SettleTransferService;
use SGFP\Application\Services\UndoTransferSettlementService;
use SGFP\Domain\Enums\AccountRole;
use SGFP\Domain\Enums\CommitmentNature;
use SGFP\Domain\Enums\CommitmentStatus;
use SGFP\Domain\Enums\CommitmentType;
use SGFP\Domain\Enums\EntryEffectType;
use SGFP\Domain\Enums\EntryOrigin;
use SGFP\Domain\Enums\EntryState;
use SGFP\Domain\Models\Account;
use SGFP\Domain\Models\Commitment;
use SGFP\Domain\Models\Entry;
use SGFP\Domain\Models\Transfer;

final class TransferServicesTest extends TestCase
{
    public function testCreateTransferFromPrincipalToSecondary(): void
    {
        [$create, $accountRepo, $commitmentRepo, $transferRepo] = $this->createCreateTransferService();

        $accountRepo->method('findById')->willReturnCallback(function (int $id) {
            return new Account($id, 1, 'Conta', $id === 10 ? AccountRole::PRINCIPAL : AccountRole::SECUNDARIA, new \DateTimeImmutable());
        });

        $commitmentRepo->expects($this->once())->method('save')->willReturnCallback(function (Commitment $c) {
            return $c->withId(100);
        });
        $transferRepo->expects($this->once())->method('save');

        [$commitment, $transfer] = $create->execute('Transferência', 500.00, '2026-09', 10, 20);

        $this->assertSame(100, $commitment->id);
        $this->assertSame(CommitmentType::TRANSFERENCIA, $commitment->type);
        $this->assertSame(CommitmentNature::SAIDA, $commitment->nature);
        $this->assertSame(10, $transfer->sourceAccountId);
        $this->assertSame(20, $transfer->targetAccountId);
    }

    public function testCreateTransferRejectsSecondaryToSecondary(): void
    {
        [$create, $accountRepo] = $this->createCreateTransferService();

        $accountRepo->method('findById')->willReturnCallback(function (int $id) {
            return new Account($id, 1, 'Conta', AccountRole::SECUNDARIA, new \DateTimeImmutable());
        });

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Principal');
        $create->execute('Transferência', 500.00, '2026-09', 20, 30);
    }

    public function testSettleTransferCreatesTwoEntries(): void
    {
        [$settle, $commitmentRepo, $transferRepo, $entryRepo] = $this->createSettleTransferService();

        $commitment = new Commitment(
            100,
            1,
            null,
            null,
            'Transferência',
            500.00,
            CommitmentType::TRANSFERENCIA,
            CommitmentNature::SAIDA,
            new \DateTimeImmutable('2026-09-01'),
            CommitmentStatus::PENDENTE,
            new \DateTimeImmutable(),
        );
        $commitmentRepo->method('findById')->willReturn($commitment);
        $transferRepo->method('findByCommitmentId')->willReturn(new Transfer(100, 1, 10, 20));

        $entryRepo->expects($this->exactly(2))->method('save')->willReturnCallback(function (Entry $e) {
            return $e->withId($e->effectType === EntryEffectType::SAIDA ? 1 : 2);
        });
        $commitmentRepo->expects($this->once())->method('save')->willReturnCallback(function (Commitment $c) {
            $this->assertSame(CommitmentStatus::EFETIVADO, $c->status);
            return $c;
        });

        [$settled, $outflow, $inflow] = $settle->execute(100);

        $this->assertSame(CommitmentStatus::EFETIVADO, $settled->status);
        $this->assertSame(EntryEffectType::SAIDA, $outflow->effectType);
        $this->assertSame(10, $outflow->accountId);
        $this->assertSame(EntryEffectType::ENTRADA, $inflow->effectType);
        $this->assertSame(20, $inflow->accountId);
    }

    public function testUndoTransferSettlementMarksEntriesUndone(): void
    {
        [$undo, $commitmentRepo, $transferRepo, $entryRepo] = $this->createUndoTransferSettlementService();

        $commitment = new Commitment(
            100,
            1,
            null,
            null,
            'Transferência',
            500.00,
            CommitmentType::TRANSFERENCIA,
            CommitmentNature::SAIDA,
            new \DateTimeImmutable('2026-09-01'),
            CommitmentStatus::EFETIVADO,
            new \DateTimeImmutable(),
        );
        $commitmentRepo->method('findById')->willReturn($commitment);
        $transferRepo->method('findByCommitmentId')->willReturn(new Transfer(100, 1, 10, 20));

        $entryRepo->method('findByCommitmentIdAndAccount')->willReturnCallback(function (int $commitmentId, int $accountId) {
            return new Entry(
                $accountId === 10 ? 1 : 2,
                1,
                $accountId,
                $commitmentId,
                EntryOrigin::COMPROMISSO,
                'Transferência',
                500.00,
                $accountId === 10 ? EntryEffectType::SAIDA : EntryEffectType::ENTRADA,
                new \DateTimeImmutable(),
                null,
                EntryState::ATIVO,
                new \DateTimeImmutable(),
                null,
            );
        });

        $captured = [];
        $entryRepo->expects($this->exactly(2))->method('save')->willReturnCallback(function (Entry $e) use (&$captured) {
            $captured[] = $e;
            return $e;
        });
        $commitmentRepo->expects($this->once())->method('save')->willReturnCallback(function (Commitment $c) {
            $this->assertSame(CommitmentStatus::PENDENTE, $c->status);
            return $c;
        });

        $undo->execute(100);

        $this->assertCount(2, $captured);
        foreach ($captured as $e) {
            $this->assertSame(EntryState::DESFEITO, $e->state);
            $this->assertNotNull($e->undoneAt);
        }
    }

    private function createCreateTransferService(): array
    {
        $accountRepo = $this->createMock(AccountRepository::class);
        $commitmentRepo = $this->createMock(CommitmentRepository::class);
        $transferRepo = $this->createMock(TransferRepository::class);
        $transactionManager = $this->createStub(TransactionManager::class);
        $userContext = $this->createMock(UserContext::class);

        $userContext->method('requireCapability');
        $userContext->method('requireUserId')->willReturn(1);
        $transactionManager->method('transactional')->willReturnCallback(function (callable $action) {
            return $action();
        });

        $service = new CreateTransferService($accountRepo, $commitmentRepo, $transferRepo, $transactionManager, $userContext);
        return [$service, $accountRepo, $commitmentRepo, $transferRepo];
    }

    private function createSettleTransferService(): array
    {
        $commitmentRepo = $this->createMock(CommitmentRepository::class);
        $transferRepo = $this->createMock(TransferRepository::class);
        $entryRepo = $this->createMock(EntryRepository::class);
        $transactionManager = $this->createStub(TransactionManager::class);
        $userContext = $this->createMock(UserContext::class);

        $userContext->method('requireCapability');
        $userContext->method('requireUserId')->willReturn(1);
        $transactionManager->method('transactional')->willReturnCallback(function (callable $action) {
            return $action();
        });

        $service = new SettleTransferService($commitmentRepo, $transferRepo, $entryRepo, $transactionManager, $userContext);
        return [$service, $commitmentRepo, $transferRepo, $entryRepo];
    }

    private function createUndoTransferSettlementService(): array
    {
        $commitmentRepo = $this->createMock(CommitmentRepository::class);
        $transferRepo = $this->createMock(TransferRepository::class);
        $entryRepo = $this->createMock(EntryRepository::class);
        $transactionManager = $this->createStub(TransactionManager::class);
        $userContext = $this->createMock(UserContext::class);

        $userContext->method('requireCapability');
        $userContext->method('requireUserId')->willReturn(1);
        $transactionManager->method('transactional')->willReturnCallback(function (callable $action) {
            return $action();
        });

        $service = new UndoTransferSettlementService($commitmentRepo, $transferRepo, $entryRepo, $transactionManager, $userContext);
        return [$service, $commitmentRepo, $transferRepo, $entryRepo];
    }
}
