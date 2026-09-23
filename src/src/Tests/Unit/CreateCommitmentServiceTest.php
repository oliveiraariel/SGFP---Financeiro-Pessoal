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
                $this->assertSame('2026-09-15', $c->referenceMonth->format('Y-m-d'));
                return $c->withId(10);
            }
        );

        $service = new CreateCommitmentService($commitments, $categories, $recurrences, $context);
        $result = $service->execute(5, 'Aluguel', '1200.00', 'SAIDA', '2026-09-15');

        $this->assertSame(10, $result->id);
    }

    public function testCategoryIsOptional(): void
    {
        $service = $this->service();
        $result = $service->execute(null, 'Freelance', '500.00', 'ENTRADA', '2026-08-01');
        $this->assertNull($result->categoryId);
    }

    public function testPreservesNoRecurrenceWhenMonthsCountIsProvidedButRecurrenceIsDisabled(): void
    {
        $recurrences = $this->createMock(RecurrenceRepository::class);
        $recurrences->expects($this->never())->method('save');

        $commitments = $this->createMock(CommitmentRepository::class);
        $commitments->expects($this->once())->method('save')->willReturnCallback(
            function (Commitment $c): Commitment {
                $this->assertNull($c->recurrenceId);
                return $c->withId(11);
            }
        );

        $context = $this->createMock(UserContext::class);
        $context->method('requireUserId')->willReturn(1);

        $result = (new CreateCommitmentService(
            $commitments,
            $this->createMock(CategoryRepository::class),
            $recurrences,
            $context,
        ))->execute(null, 'Conta de luz', '150.00', 'SAIDA', '2026-08-15', 12, false);

        $this->assertSame(11, $result->id);
        $this->assertNull($result->recurrenceId);
    }

    public function testRejectsInvalidNature(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->service()->execute(null, 'Nome', '100.00', 'INVALIDO', '2026-08-01');
    }

    public function testCreatesRecurrentCommitment(): void
    {
        $commitments = $this->createMock(CommitmentRepository::class);
        $categories = $this->createMock(CategoryRepository::class);
        $recurrences = $this->createMock(RecurrenceRepository::class);
        $context = $this->createMock(UserContext::class);
        $context->method('requireUserId')->willReturn(1);

        $recurrences->expects($this->once())->method('save')->willReturnCallback(
            function (Recurrence $r): Recurrence {
                $this->assertSame('2026-09-01', $r->startsIn->format('Y-m-d'));
                return $r->withId(50);
            }
        );
        $commitments->expects($this->once())->method('save')->willReturnCallback(
            function (Commitment $c): Commitment {
                $this->assertSame(50, $c->recurrenceId);
                $this->assertSame('2026-09-15', $c->referenceMonth->format('Y-m-d'));
                return $c->withId(20);
            }
        );

        $result = (new CreateCommitmentService($commitments, $categories, $recurrences, $context))
            ->execute(null, 'Mensalidade', '200.00', 'SAIDA', '2026-09-15', 12, true);

        $this->assertSame(20, $result->id);
    }

    public function testRecurrentCommitmentStartsInCommitmentMonth(): void
    {
        $recurrences = $this->createMock(RecurrenceRepository::class);
        $commitments = $this->createMock(CommitmentRepository::class);
        $categories = $this->createMock(CategoryRepository::class);
        $context = $this->createMock(UserContext::class);
        $context->method('requireUserId')->willReturn(1);

        $recurrences->expects($this->once())->method('save')->willReturnCallback(
            function (Recurrence $r): Recurrence {
                $this->assertSame('2026-09-01', $r->startsIn->format('Y-m-d'));
                return $r->withId(51);
            }
        );
        $commitments->expects($this->once())->method('save')->willReturnCallback(
            function (Commitment $c): Commitment {
                $this->assertSame('2026-09-01', $c->referenceMonth->format('Y-m-d'));
                return $c->withId(21);
            }
        );

        $result = (new CreateCommitmentService($commitments, $categories, $recurrences, $context))
            ->execute(null, 'Mensalidade', '200.00', 'SAIDA', '2026-09-01', 12, true);

        $this->assertSame(21, $result->id);
    }

    public function testRecurringCommitmentUsesCommitmentMonthWithoutSeparateStartOption(): void
    {
        $recurrences = $this->createMock(RecurrenceRepository::class);
        $recurrences->expects($this->once())->method('save')->willReturnCallback(
            fn (Recurrence $r): Recurrence => $r->withId(53)
        );
        $commitments = $this->createMock(CommitmentRepository::class);
        $commitments->method('save')->willReturnCallback(fn (Commitment $c): Commitment => $c->withId(23));
        $context = $this->createMock(UserContext::class);
        $context->method('requireUserId')->willReturn(1);

        $result = (new CreateCommitmentService($commitments, $this->createMock(CategoryRepository::class), $recurrences, $context))
            ->execute(null, 'Mensalidade', '200.00', 'SAIDA', '2026-09-01', 12, true);

        $this->assertSame(53, $result->recurrenceId);
    }

    public function testCreatesIndefiniteRecurrentCommitment(): void
    {
        $recurrences = $this->createMock(RecurrenceRepository::class);
        $commitments = $this->createMock(CommitmentRepository::class);
        $categories = $this->createMock(CategoryRepository::class);
        $context = $this->createMock(UserContext::class);
        $context->method('requireUserId')->willReturn(1);

        $recurrences->expects($this->once())->method('save')->willReturnCallback(
            function (Recurrence $r): Recurrence {
                $this->assertSame('2026-09-01', $r->startsIn->format('Y-m-d'));
                $this->assertNull($r->monthsCount);
                return $r->withId(52);
            }
        );
        $commitments->expects($this->once())->method('save')->willReturnCallback(
            function (Commitment $c): Commitment {
                $this->assertSame(52, $c->recurrenceId);
                return $c->withId(22);
            }
        );

        $result = (new CreateCommitmentService($commitments, $categories, $recurrences, $context))
            ->execute(null, 'Mensalidade', '200.00', 'SAIDA', '2026-09-01', null, true);

        $this->assertSame(22, $result->id);
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
