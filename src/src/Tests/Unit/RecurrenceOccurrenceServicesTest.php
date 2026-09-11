<?php

declare(strict_types=1);

namespace SGFP\Tests\Unit;

use PHPUnit\Framework\TestCase;
use SGFP\Application\Ports\AccountRepository;
use SGFP\Application\Ports\CommitmentRepository;
use SGFP\Application\Ports\EntryRepository;
use SGFP\Application\Ports\RecurrenceRepository;
use SGFP\Application\Ports\TransactionManager;
use SGFP\Application\Ports\UserContext;
use SGFP\Application\Services\MaterializeRecurrenceOccurrenceService;
use SGFP\Application\Services\SettleRecurrenceOccurrenceService;
use SGFP\Application\Services\UndoRecurrenceOccurrenceSettlementService;
use SGFP\Domain\Enums\AccountRole;
use SGFP\Domain\Enums\CommitmentNature;
use SGFP\Domain\Enums\CommitmentStatus;
use SGFP\Domain\Enums\CommitmentType;
use SGFP\Domain\Models\Account;
use SGFP\Domain\Models\Commitment;
use SGFP\Domain\Models\Entry;
use SGFP\Domain\Models\Recurrence;

final class RecurrenceOccurrenceServicesTest extends TestCase
{
    public function testMaterializeCreatesOccurrenceWhenNotExists(): void
    {
        [$materialize, $recurrenceRepo, $commitmentRepo] = $this->createMaterializeService();

        $recurrenceRepo->method('findById')->with(5, 1)->willReturn(
            new Recurrence(5, 1, new \DateTimeImmutable('2026-09-01'), 12, null, new \DateTimeImmutable())
        );

        $base = $this->makeCommitment(10, 1, 'Aluguel', 1000.0, CommitmentNature::SAIDA, '2026-09-01', CommitmentStatus::PENDENTE, 5);
        $commitmentRepo->method('findFirstByRecurrenceId')->with(5, 1)->willReturn($base);
        $commitmentRepo->method('findByRecurrenceIdAndMonth')->willReturn(null);
        $commitmentRepo->expects($this->once())->method('save')->willReturnCallback(
            function (Commitment $c) {
                return $c->withId(20);
            }
        );

        $result = $materialize->execute(5, '2026-10');

        $this->assertSame(20, $result->id);
        $this->assertSame(5, $result->recurrenceId);
        $this->assertSame('2026-10-01', $result->referenceMonth->format('Y-m-d'));
        $this->assertSame('Aluguel', $result->name);
        $this->assertSame(1000.0, $result->amount);
    }

    public function testMaterializeReturnsExistingOccurrence(): void
    {
        [$materialize, $recurrenceRepo, $commitmentRepo] = $this->createMaterializeService();

        $recurrenceRepo->method('findById')->willReturn(
            new Recurrence(5, 1, new \DateTimeImmutable('2026-09-01'), 12, null, new \DateTimeImmutable())
        );

        $existing = $this->makeCommitment(20, 1, 'Aluguel', 1000.0, CommitmentNature::SAIDA, '2026-10-01', CommitmentStatus::PENDENTE, 5);
        $commitmentRepo->method('findByRecurrenceIdAndMonth')->willReturn($existing);
        $commitmentRepo->expects($this->never())->method('save');

        $result = $materialize->execute(5, '2026-10');

        $this->assertSame(20, $result->id);
    }

    public function testSettleMaterializesAndSettles(): void
    {
        [$settle, $commitmentRepo, $entryRepo, $accountRepo] = $this->createSettleService();

        $occurrence = $this->makeCommitment(20, 1, 'Aluguel', 1000.0, CommitmentNature::SAIDA, '2026-10-01', CommitmentStatus::PENDENTE, 5);
        $commitmentRepo->method('findByRecurrenceIdAndMonth')->willReturn($occurrence);
        $commitmentRepo->method('findById')->with(20, 1)->willReturn($occurrence);

        $accountRepo->method('findPrincipal')->with(1)->willReturn(
            new Account(1, 1, 'Principal', AccountRole::PRINCIPAL, new \DateTimeImmutable())
        );

        $entryRepo->method('findByCommitmentId')->willReturn(null);
        $entryRepo->expects($this->once())->method('save')->willReturnCallback(
            function (Entry $e) {
                return $e->withId(100);
            }
        );
        $commitmentRepo->expects($this->once())->method('save')->willReturnCallback(
            function (Commitment $c) {
                return $c;
            }
        );

        $result = $settle->execute(5, '2026-10');

        $this->assertSame(100, $result->id);
        $this->assertSame(20, $result->commitmentId);
    }

    private function createMaterializeService(): array
    {
        $recurrenceRepo = $this->createMock(RecurrenceRepository::class);
        $commitmentRepo = $this->createMock(CommitmentRepository::class);
        $transactionManager = $this->createStub(TransactionManager::class);
        $userContext = $this->createMock(UserContext::class);

        $userContext->method('requireCapability');
        $userContext->method('requireUserId')->willReturn(1);
        $transactionManager->method('transactional')->willReturnCallback(function (callable $action) {
            return $action();
        });

        $service = new MaterializeRecurrenceOccurrenceService($recurrenceRepo, $commitmentRepo, $transactionManager, $userContext);
        return [$service, $recurrenceRepo, $commitmentRepo];
    }

    private function createSettleService(): array
    {
        $recurrenceRepo = $this->createMock(RecurrenceRepository::class);
        $commitmentRepo = $this->createMock(CommitmentRepository::class);
        $entryRepo = $this->createMock(EntryRepository::class);
        $accountRepo = $this->createMock(AccountRepository::class);
        $transactionManager = $this->createStub(TransactionManager::class);
        $userContext = $this->createMock(UserContext::class);

        $userContext->method('requireCapability');
        $userContext->method('requireUserId')->willReturn(1);
        $transactionManager->method('transactional')->willReturnCallback(function (callable $action) {
            return $action();
        });

        $materialize = new MaterializeRecurrenceOccurrenceService($recurrenceRepo, $commitmentRepo, $transactionManager, $userContext);
        $service = new SettleRecurrenceOccurrenceService($materialize, $commitmentRepo, $entryRepo, $accountRepo, $transactionManager, $userContext);
        return [$service, $commitmentRepo, $entryRepo, $accountRepo];
    }

    private function makeCommitment(
        int $id,
        int $userId,
        string $name,
        float $amount,
        CommitmentNature $nature,
        string $month,
        CommitmentStatus $status,
        ?int $recurrenceId,
    ): Commitment {
        return new Commitment(
            $id,
            $userId,
            null,
            $recurrenceId,
            $name,
            $amount,
            CommitmentType::PADRAO,
            $nature,
            new \DateTimeImmutable($month),
            $status,
            new \DateTimeImmutable(),
        );
    }
}
