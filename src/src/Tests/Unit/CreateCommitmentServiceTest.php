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
use SGFP\Domain\Enums\CommitmentType;
use SGFP\Domain\Models\Category;
use SGFP\Domain\Models\Commitment;
use SGFP\Domain\Models\Recurrence;

final class CreateCommitmentServiceTest extends TestCase
{
    public function testCreatesStandardCommitmentWithReferenceMonthDayOne(): void
    {
        $commitmentRepository = $this->createMock(CommitmentRepository::class);
        $categoryRepository = $this->createMock(CategoryRepository::class);
        $recurrenceRepository = $this->createMock(RecurrenceRepository::class);
        $userContext = $this->createMock(UserContext::class);

        $userContext->method('requireUserId')->willReturn(1);
        $userContext->expects($this->once())->method('requireCapability')->with('use_sgfp');

        $categoryRepository->method('findById')->with(5, 1)->willReturn(
            new Category(5, 1, 'Moradia', new \DateTimeImmutable('2026-01-01'))
        );

        $commitmentRepository->expects($this->once())->method('save')->willReturnCallback(
            function (Commitment $commitment): Commitment {
                $this->assertSame(1, $commitment->userId);
                $this->assertSame(5, $commitment->categoryId);
                $this->assertSame('Aluguel', $commitment->name);
                $this->assertSame(1200.0, $commitment->amount);
                $this->assertSame(CommitmentType::PADRAO, $commitment->type);
                $this->assertSame(CommitmentNature::SAIDA, $commitment->nature);
                $this->assertSame('2026-09-01', $commitment->referenceMonth->format('Y-m-d'));
                $this->assertSame(CommitmentStatus::PENDENTE, $commitment->status);
                return $commitment->withId(10);
            }
        );

        $service = new CreateCommitmentService($commitmentRepository, $categoryRepository, $recurrenceRepository, $userContext);
        $result = $service->execute(5, 'Aluguel', 1200.0, 'PADRAO', 'SAIDA', '2026-09-15');

        $this->assertSame(10, $result->id);
    }

    public function testCategoryIsOptional(): void
    {
        $commitmentRepository = $this->createMock(CommitmentRepository::class);
        $categoryRepository = $this->createMock(CategoryRepository::class);
        $recurrenceRepository = $this->createMock(RecurrenceRepository::class);
        $userContext = $this->createMock(UserContext::class);

        $userContext->method('requireUserId')->willReturn(1);
        $categoryRepository->expects($this->never())->method('findById');

        $commitmentRepository->expects($this->once())->method('save')->willReturnCallback(
            function (Commitment $commitment): Commitment {
                $this->assertNull($commitment->categoryId);
                return $commitment->withId(11);
            }
        );

        $service = new CreateCommitmentService($commitmentRepository, $categoryRepository, $recurrenceRepository, $userContext);
        $result = $service->execute(null, 'Freelance', 500.0, 'PADRAO', 'ENTRADA', '2026-08');

        $this->assertSame(11, $result->id);
        $this->assertNull($result->categoryId);
    }

    public function testEmptyNameThrows(): void
    {
        $service = $this->createService();

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('O nome é obrigatório.');
        $service->execute(null, '   ', 100.0, 'PADRAO', 'ENTRADA', '2026-08');
    }

    public function testNameLongerThanOneHundredEightyCharactersThrows(): void
    {
        $service = $this->createService();

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('O nome deve ter no máximo 180 caracteres.');
        $service->execute(null, str_repeat('a', 181), 100.0, 'PADRAO', 'ENTRADA', '2026-08');
    }

    public function testNegativeAmountThrows(): void
    {
        $service = $this->createService();

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('O valor deve ser maior ou igual a zero.');
        $service->execute(null, 'Nome', -1.0, 'PADRAO', 'ENTRADA', '2026-08');
    }

    public function testInvalidTypeThrows(): void
    {
        $service = $this->createService();

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('O tipo deve ser PADRAO ou TRANSFERENCIA.');
        $service->execute(null, 'Nome', 100.0, 'INVALIDO', 'ENTRADA', '2026-08');
    }

    public function testTransferTypeIsRejected(): void
    {
        $service = $this->createService();

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Transferências devem ser criadas pelo fluxo próprio de transferência.');
        $service->execute(null, 'Transferência', 100.0, 'TRANSFERENCIA', 'SAIDA', '2026-08');
    }

    public function testInvalidNatureThrows(): void
    {
        $service = $this->createService();

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('A natureza deve ser ENTRADA ou SAIDA.');
        $service->execute(null, 'Nome', 100.0, 'PADRAO', 'INVALIDO', '2026-08');
    }

    public function testInvalidReferenceMonthThrows(): void
    {
        $service = $this->createService();

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('O mês de referência deve estar no formato YYYY-MM.');
        $service->execute(null, 'Nome', 100.0, 'PADRAO', 'ENTRADA', '2026/08');
    }

    public function testCreatesRecurrentCommitment(): void
    {
        $commitmentRepository = $this->createMock(CommitmentRepository::class);
        $categoryRepository = $this->createMock(CategoryRepository::class);
        $recurrenceRepository = $this->createMock(RecurrenceRepository::class);
        $userContext = $this->createMock(UserContext::class);

        $userContext->method('requireUserId')->willReturn(1);
        $categoryRepository->expects($this->never())->method('findById');

        $recurrenceRepository->expects($this->once())->method('save')->willReturnCallback(
            function (Recurrence $recurrence): Recurrence {
                $this->assertSame('2026-09-01', $recurrence->startsIn->format('Y-m-d'));
                $this->assertSame(12, $recurrence->monthsCount);
                return $recurrence->withId(50);
            }
        );

        $commitmentRepository->expects($this->once())->method('save')->willReturnCallback(
            function (Commitment $commitment): Commitment {
                $this->assertSame(50, $commitment->recurrenceId);
                return $commitment->withId(20);
            }
        );

        $service = new CreateCommitmentService($commitmentRepository, $categoryRepository, $recurrenceRepository, $userContext);
        $result = $service->execute(null, 'Mensalidade', 200.0, 'PADRAO', 'SAIDA', '2026-09', 12);

        $this->assertSame(20, $result->id);
        $this->assertSame(50, $result->recurrenceId);
    }

    public function testMissingCategoryThrows(): void
    {
        $commitmentRepository = $this->createMock(CommitmentRepository::class);
        $categoryRepository = $this->createMock(CategoryRepository::class);
        $recurrenceRepository = $this->createMock(RecurrenceRepository::class);
        $userContext = $this->createMock(UserContext::class);

        $userContext->method('requireUserId')->willReturn(1);
        $categoryRepository->method('findById')->with(99, 1)->willReturn(null);

        $service = new CreateCommitmentService($commitmentRepository, $categoryRepository, $recurrenceRepository, $userContext);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Categoria não encontrada.');
        $service->execute(99, 'Nome', 100.0, 'PADRAO', 'ENTRADA', '2026-08');
    }

    private function createService(): CreateCommitmentService
    {
        $commitmentRepository = $this->createMock(CommitmentRepository::class);
        $categoryRepository = $this->createMock(CategoryRepository::class);
        $recurrenceRepository = $this->createMock(RecurrenceRepository::class);
        $userContext = $this->createMock(UserContext::class);

        $userContext->method('requireUserId')->willReturn(1);

        return new CreateCommitmentService($commitmentRepository, $categoryRepository, $recurrenceRepository, $userContext);
    }
}
