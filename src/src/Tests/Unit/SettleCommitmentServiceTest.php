<?php

declare(strict_types=1);

namespace SGFP\Tests\Unit;

use PHPUnit\Framework\TestCase;
use SGFP\Application\Ports\AccountRepository;
use SGFP\Application\Ports\CommitmentRepository;
use SGFP\Application\Ports\EntryRepository;
use SGFP\Application\Ports\TransactionManager;
use SGFP\Application\Ports\UserContext;
use SGFP\Application\Services\SettleCommitmentService;
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

final class SettleCommitmentServiceTest extends TestCase
{
    public function testSettleCreatesActiveEntryWithEffectTypeFromNatureOnPrincipalAccount(): void
    {
        $commitmentRepository = $this->createMock(CommitmentRepository::class);
        $entryRepository = $this->createMock(EntryRepository::class);
        $accountRepository = $this->createMock(AccountRepository::class);
        $transactionManager = $this->createPassThroughTransactionManager();
        $userContext = $this->createMock(UserContext::class);

        $userContext->method('requireUserId')->willReturn(1);
        $userContext->expects($this->once())->method('requireCapability')->with('use_sgfp');

        $commitment = Commitment::create(
            1,
            null,
            'Salário',
            3000.0,
            CommitmentType::PADRAO,
            CommitmentNature::ENTRADA,
            new \DateTimeImmutable('2026-09-01'),
            new \DateTimeImmutable('2026-09-01')
        )->withId(10);

        $commitmentRepository->method('findById')->with(10, 1)->willReturn($commitment);
        $commitmentRepository->expects($this->once())->method('save')->willReturnCallback(
            function (Commitment $c): Commitment {
                $this->assertSame(CommitmentStatus::EFETIVADO, $c->status);
                return $c;
            }
        );

        $accountRepository->method('findPrincipal')->with(1)->willReturn(
            new Account(7, 1, 'Principal', AccountRole::PRINCIPAL, new \DateTimeImmutable('2026-01-01'))
        );

        $entryRepository->expects($this->once())->method('save')->willReturnCallback(
            function (Entry $entry): Entry {
                $this->assertSame(1, $entry->userId);
                $this->assertSame(7, $entry->accountId);
                $this->assertSame(10, $entry->commitmentId);
                $this->assertSame(EntryOrigin::COMPROMISSO, $entry->origin);
                $this->assertSame('Salário', $entry->name);
                $this->assertSame(3000.0, $entry->amount);
                $this->assertSame(EntryEffectType::ENTRADA, $entry->effectType);
                $this->assertSame(EntryState::ATIVO, $entry->state);
                return $entry->withId(100);
            }
        );

        $service = new SettleCommitmentService(
            $commitmentRepository,
            $entryRepository,
            $accountRepository,
            $transactionManager,
            $userContext
        );

        $result = $service->execute(10);

        $this->assertSame(100, $result->id);
        $this->assertSame(EntryEffectType::ENTRADA, $result->effectType);
        $this->assertSame(EntryState::ATIVO, $result->state);
    }

    public function testSettleOutputNatureProducesOutputEffectType(): void
    {
        $commitmentRepository = $this->createMock(CommitmentRepository::class);
        $entryRepository = $this->createMock(EntryRepository::class);
        $accountRepository = $this->createMock(AccountRepository::class);
        $transactionManager = $this->createPassThroughTransactionManager();
        $userContext = $this->createMock(UserContext::class);

        $userContext->method('requireUserId')->willReturn(1);

        $commitment = Commitment::create(
            1,
            null,
            'Aluguel',
            1200.0,
            CommitmentType::PADRAO,
            CommitmentNature::SAIDA,
            new \DateTimeImmutable('2026-09-01'),
            new \DateTimeImmutable('2026-09-01')
        )->withId(11);

        $commitmentRepository->method('findById')->with(11, 1)->willReturn($commitment);
        $commitmentRepository->expects($this->once())->method('save')->willReturnArgument(0);

        $accountRepository->method('findPrincipal')->with(1)->willReturn(
            new Account(7, 1, 'Principal', AccountRole::PRINCIPAL, new \DateTimeImmutable('2026-01-01'))
        );

        $entryRepository->expects($this->once())->method('save')->willReturnCallback(
            function (Entry $entry): Entry {
                $this->assertSame(EntryEffectType::SAIDA, $entry->effectType);
                return $entry->withId(101);
            }
        );

        $service = new SettleCommitmentService(
            $commitmentRepository,
            $entryRepository,
            $accountRepository,
            $transactionManager,
            $userContext
        );

        $result = $service->execute(11);

        $this->assertSame(EntryEffectType::SAIDA, $result->effectType);
    }

    public function testMissingCommitmentThrows(): void
    {
        $commitmentRepository = $this->createMock(CommitmentRepository::class);
        $entryRepository = $this->createMock(EntryRepository::class);
        $accountRepository = $this->createMock(AccountRepository::class);
        $transactionManager = $this->createPassThroughTransactionManager();
        $userContext = $this->createMock(UserContext::class);

        $userContext->method('requireUserId')->willReturn(1);
        $commitmentRepository->method('findById')->with(99, 1)->willReturn(null);

        $service = new SettleCommitmentService(
            $commitmentRepository,
            $entryRepository,
            $accountRepository,
            $transactionManager,
            $userContext
        );

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Compromisso não encontrado.');
        $service->execute(99);
    }

    public function testTransferCommitmentCannotBeSettled(): void
    {
        $service = $this->createServiceWithCommitment(
            Commitment::create(
                1,
                null,
                'Transferência',
                100.0,
                CommitmentType::TRANSFERENCIA,
                CommitmentNature::SAIDA,
                new \DateTimeImmutable('2026-09-01'),
                new \DateTimeImmutable('2026-09-01')
            )->withId(12)
        );

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Transferências devem ser efetivadas pelo fluxo próprio de transferência.');
        $service->execute(12);
    }

    public function testNonPendingCommitmentCannotBeSettled(): void
    {
        $service = $this->createServiceWithCommitment(
            new Commitment(
                13,
                1,
                null,
                null,
                'Pago',
                100.0,
                CommitmentType::PADRAO,
                CommitmentNature::SAIDA,
                new \DateTimeImmutable('2026-09-01'),
                CommitmentStatus::EFETIVADO,
                new \DateTimeImmutable('2026-09-01')
            )
        );

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('O compromisso só pode ser efetivado se estiver pendente.');
        $service->execute(13);
    }

    public function testMissingPrincipalAccountThrows(): void
    {
        $commitmentRepository = $this->createMock(CommitmentRepository::class);
        $entryRepository = $this->createMock(EntryRepository::class);
        $accountRepository = $this->createMock(AccountRepository::class);
        $transactionManager = $this->createPassThroughTransactionManager();
        $userContext = $this->createMock(UserContext::class);

        $userContext->method('requireUserId')->willReturn(1);

        $commitment = Commitment::create(
            1,
            null,
            'Nome',
            100.0,
            CommitmentType::PADRAO,
            CommitmentNature::ENTRADA,
            new \DateTimeImmutable('2026-09-01'),
            new \DateTimeImmutable('2026-09-01')
        )->withId(14);

        $commitmentRepository->method('findById')->with(14, 1)->willReturn($commitment);
        $accountRepository->method('findPrincipal')->with(1)->willReturn(null);

        $service = new SettleCommitmentService(
            $commitmentRepository,
            $entryRepository,
            $accountRepository,
            $transactionManager,
            $userContext
        );

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Conta principal não encontrada.');
        $service->execute(14);
    }

    private function createPassThroughTransactionManager(): TransactionManager
    {
        return new class implements TransactionManager {
            public function begin(): void
            {
            }

            public function commit(): void
            {
            }

            public function rollback(): void
            {
            }

            public function transactional(callable $action): mixed
            {
                return $action();
            }
        };
    }

    private function createServiceWithCommitment(Commitment $commitment): SettleCommitmentService
    {
        $commitmentRepository = $this->createMock(CommitmentRepository::class);
        $entryRepository = $this->createMock(EntryRepository::class);
        $accountRepository = $this->createMock(AccountRepository::class);
        $transactionManager = $this->createPassThroughTransactionManager();
        $userContext = $this->createMock(UserContext::class);

        $userContext->method('requireUserId')->willReturn(1);
        $commitmentRepository->method('findById')->with($commitment->id, 1)->willReturn($commitment);

        return new SettleCommitmentService(
            $commitmentRepository,
            $entryRepository,
            $accountRepository,
            $transactionManager,
            $userContext
        );
    }
}
