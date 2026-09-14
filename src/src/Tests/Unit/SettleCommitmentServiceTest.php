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
use SGFP\Domain\Enums\CommitmentNature;
use SGFP\Domain\Enums\EntryEffectType;
use SGFP\Domain\Models\Account;
use SGFP\Domain\Models\Commitment;
use SGFP\Domain\Models\Entry;

final class SettleCommitmentServiceTest extends TestCase
{
    public function testSettleCreatesEntryOnOnlyAccount(): void
    {
        $commitments = $this->createMock(CommitmentRepository::class);
        $entries = $this->createMock(EntryRepository::class);
        $accounts = $this->createMock(AccountRepository::class);
        $context = $this->createMock(UserContext::class);
        $tx = $this->createStub(TransactionManager::class);

        $context->method('requireUserId')->willReturn(1);
        $tx->method('transactional')->willReturnCallback(fn (callable $action) => $action());

        $commitment = Commitment::create(
            1, null, 'Salário', 3000.0, CommitmentNature::ENTRADA,
            new \DateTimeImmutable('2026-09-01'), new \DateTimeImmutable()
        )->withId(10);

        $commitments->method('findById')->willReturn($commitment);
        $commitments->method('save')->willReturnCallback(fn (Commitment $c): Commitment => $c);
        $accounts->method('findByUser')->willReturn(
            new Account(7, 1, 'Minha Conta', new \DateTimeImmutable())
        );
        $entries->expects($this->once())->method('save')->willReturnCallback(
            fn (Entry $e): Entry => $e->withId(100)
        );

        $result = (new SettleCommitmentService($commitments, $entries, $accounts, $tx, $context))
            ->execute(10);

        $this->assertSame(100, $result->id);
        $this->assertSame(7, $result->accountId);
        $this->assertSame(EntryEffectType::ENTRADA, $result->effectType);
    }

    public function testMissingAccountThrows(): void
    {
        $commitments = $this->createMock(CommitmentRepository::class);
        $entries = $this->createStub(EntryRepository::class);
        $accounts = $this->createMock(AccountRepository::class);
        $context = $this->createMock(UserContext::class);
        $tx = $this->createStub(TransactionManager::class);

        $context->method('requireUserId')->willReturn(1);
        $tx->method('transactional')->willReturnCallback(fn (callable $action) => $action());

        $commitment = Commitment::create(
            1, null, 'Conta', 100.0, CommitmentNature::SAIDA,
            new \DateTimeImmutable('2026-09-01'), new \DateTimeImmutable()
        )->withId(10);
        $commitments->method('findById')->willReturn($commitment);
        $accounts->method('findByUser')->willReturn(null);

        $this->expectException(\InvalidArgumentException::class);
        (new SettleCommitmentService($commitments, $entries, $accounts, $tx, $context))->execute(10);
    }
}
