<?php

declare(strict_types=1);

namespace SGFP\Tests\Unit;

use PHPUnit\Framework\TestCase;
use SGFP\Application\Ports\CategoryRepository;
use SGFP\Application\Ports\CommitmentRepository;
use SGFP\Application\Ports\RecurrenceRepository;
use SGFP\Application\Ports\UserContext;
use SGFP\Application\Services\CreateCommitmentService;
use SGFP\Domain\Enums\CommitmentNature;
use SGFP\Domain\Enums\CommitmentStatus;
use SGFP\Domain\Models\Category;
use SGFP\Domain\Models\Commitment;
use SGFP\Domain\Models\Recurrence;

final class CreateCommitmentServiceTest extends TestCase
{
    public function testCreatesCommitment(): void
    {
        $commitments = $this->createMock(CommitmentRepository::class);
        $categories = $this->createMock(CategoryRepository::class);
        $recurrences = $this->createMock(RecurrenceRepository::class);
        $context = $this->createMock(UserContext::class);

        $context->method('requireUserId')->willReturn(1);
        $categories->method('findById')->with(5, 1)->willReturn(
            new Category(5, 1, 'Moradia', new \DateTimeImmutable())
        );
        $commitments->expects($this->once())->method('save')->willReturnCallback(
            function (Commitment $c): Commitment {
                $this->assertSame(CommitmentNature::SAIDA, $c->nature);
                $this->assertSame(CommitmentStatus::PENDENTE, $c->status);
                $this->assertSame('2026-09-01', $c->referenceMonth->format('Y-m-d'));
                return $c->withId(10);
            }
        );

        $service = new CreateCommitmentService($commitments, $categories, $recurrences, $context);
        $result = $service->execute(5, 'Aluguel', 1200.0, 'SAIDA', '2026-09');

        $this->assertSame(10, $result->id);
    }

    public function testCategoryIsOptional(): void
    {
        $service = $this->service();
        $result = $service->execute(null, 'Freelance', 500.0, 'ENTRADA', '2026-08');
        $this->assertNull($result->categoryId);
    }

    public function testRejectsInvalidNature(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->service()->execute(null, 'Nome', 100.0, 'INVALIDO', '2026-08');
    }

    public function testCreatesRecurrentCommitment(): void
    {
        $commitments = $this->createMock(CommitmentRepository::class);
        $categories = $this->createMock(CategoryRepository::class);
        $recurrences = $this->createMock(RecurrenceRepository::class);
        $context = $this->createMock(UserContext::class);
        $context->method('requireUserId')->willReturn(1);

        $recurrences->expects($this->once())->method('save')->willReturnCallback(
            fn (Recurrence $r): Recurrence => $r->withId(50)
        );
        $commitments->expects($this->once())->method('save')->willReturnCallback(
            function (Commitment $c): Commitment {
                $this->assertSame(50, $c->recurrenceId);
                return $c->withId(20);
            }
        );

        $result = (new CreateCommitmentService($commitments, $categories, $recurrences, $context))
            ->execute(null, 'Mensalidade', 200.0, 'SAIDA', '2026-09', 12, 'CURRENT');

        $this->assertSame(20, $result->id);
    }

    public function testRequiresExplicitStartForRecurrentCommitment(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->service()->execute(null, 'Mensalidade', 200.0, 'SAIDA', '2026-09', 12);
    }

    private function service(): CreateCommitmentService
    {
        $commitments = $this->createMock(CommitmentRepository::class);
        $categories = $this->createMock(CategoryRepository::class);
        $recurrences = $this->createMock(RecurrenceRepository::class);
        $context = $this->createMock(UserContext::class);

        $context->method('requireUserId')->willReturn(1);
        $commitments->method('save')->willReturnCallback(fn (Commitment $c): Commitment => $c->withId(1));

        return new CreateCommitmentService($commitments, $categories, $recurrences, $context);
    }
}
