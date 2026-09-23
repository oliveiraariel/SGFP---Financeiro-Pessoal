<?php

declare(strict_types=1);

namespace SGFP\Tests\Unit;

use PHPUnit\Framework\TestCase;
use SGFP\Application\Ports\CategoryRepository;
use SGFP\Application\Ports\CommitmentRepository;
use SGFP\Application\Ports\UserContext;
use SGFP\Application\Services\UpdateCommitmentService;
use SGFP\Domain\Enums\CommitmentNature;
use SGFP\Domain\Enums\CommitmentStatus;
use SGFP\Domain\Models\Commitment;

final class UpdateCommitmentServiceTest extends TestCase
{
    public function testDoesNotUpdateCommitmentOwnedByAnotherUser(): void
    {
        $repository = $this->createMock(CommitmentRepository::class);
        $repository->expects($this->once())->method('findById')->with(10, 2)->willReturn(null);
        $context = $this->createMock(UserContext::class);
        $context->method('requireUserId')->willReturn(2);
        $context->expects($this->once())->method('requireCapability')->with('use_sgfp');

        $this->expectExceptionObject(new \RuntimeException('Compromisso não encontrado.', 404));
        (new UpdateCommitmentService($repository, $this->createMock(CategoryRepository::class), $context))
            ->execute(10, null, 'Tentativa', '10.00');
    }

    public function testUpdatesSettledCommitment(): void
    {
        $repository = $this->createMock(CommitmentRepository::class);
        $repository->method('findById')->willReturn(new Commitment(
            10, 2, null, null, 'Pago', '10.00', CommitmentNature::SAIDA,
            new \DateTimeImmutable('2026-09-01'), CommitmentStatus::EFETIVADO, new \DateTimeImmutable()
        ));
        $context = $this->createMock(UserContext::class);
        $context->method('requireUserId')->willReturn(2);

        $repository->expects($this->once())->method('save')->willReturnArgument(0);
        $updated = (new UpdateCommitmentService($repository, $this->createMock(CategoryRepository::class), $context))
            ->execute(10, null, 'Tentativa', '10.00', '2026-09-20');
        $this->assertSame('Tentativa', $updated->name);
        $this->assertSame('10.00', $updated->amount);
        $this->assertSame(CommitmentStatus::EFETIVADO, $updated->status);
        $this->assertSame('2026-09-20', $updated->referenceMonth->format('Y-m-d'));
    }

    public function testRejectsNonexistentCommitmentDate(): void
    {
        $repository = $this->createMock(CommitmentRepository::class);
        $repository->method('findById')->willReturn(new Commitment(
            10, 2, null, null, 'Conta', '10.00', CommitmentNature::SAIDA,
            new \DateTimeImmutable('2026-09-01'), CommitmentStatus::PENDENTE, new \DateTimeImmutable()
        ));
        $context = $this->createMock(UserContext::class);
        $context->method('requireUserId')->willReturn(2);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('data do compromisso é inválida');
        (new UpdateCommitmentService($repository, $this->createMock(CategoryRepository::class), $context))
            ->execute(10, null, 'Conta', '10.00', '2026-02-31');
    }
}
