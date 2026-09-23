<?php

declare(strict_types=1);

namespace SGFP\Tests\Unit;

use PHPUnit\Framework\TestCase;
use SGFP\Application\Ports\CommitmentRepository;
use SGFP\Application\Ports\UserContext;
use SGFP\Application\Services\DeleteCommitmentService;
use SGFP\Domain\Enums\CommitmentNature;
use SGFP\Domain\Enums\CommitmentStatus;
use SGFP\Domain\Models\Commitment;

final class DeleteCommitmentServiceTest extends TestCase
{
    public function testDeletesOnlyPendingCommitments(): void
    {
        $repository = $this->createMock(CommitmentRepository::class);
        $context = $this->createMock(UserContext::class);
        $context->method('requireUserId')->willReturn(1);
        $commitment = $this->commitment(CommitmentStatus::PENDENTE);
        $repository->method('findById')->with(7, 1)->willReturn($commitment);
        $repository->expects($this->once())->method('save')->with($this->callback(
            static fn (Commitment $item): bool => $item->status === CommitmentStatus::EXCLUIDO
        ))->willReturnArgument(0);

        (new DeleteCommitmentService($repository, $context))->execute(7);
    }

    public function testRejectsDeletionOfSettledCommitment(): void
    {
        $repository = $this->createStub(CommitmentRepository::class);
        $context = $this->createStub(UserContext::class);
        $context->method('requireUserId')->willReturn(1);
        $repository->method('findById')->willReturn($this->commitment(CommitmentStatus::EFETIVADO));

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionCode(409);
        (new DeleteCommitmentService($repository, $context))->execute(7);
    }

    private function commitment(CommitmentStatus $status): Commitment
    {
        return new Commitment(7, 1, null, null, 'Aluguel', '1000.00', CommitmentNature::SAIDA, new \DateTimeImmutable('2026-09-01'), $status, new \DateTimeImmutable());
    }
}
