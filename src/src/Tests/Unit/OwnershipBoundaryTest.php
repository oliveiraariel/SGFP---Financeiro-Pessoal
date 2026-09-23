<?php

declare(strict_types=1);

namespace SGFP\Tests\Unit;

use PHPUnit\Framework\TestCase;
use SGFP\Application\Ports\AccountRepository;
use SGFP\Application\Ports\CategoryRepository;
use SGFP\Application\Ports\CommitmentRepository;
use SGFP\Application\Ports\RecurrenceRepository;
use SGFP\Application\Ports\TransactionManager;
use SGFP\Application\Ports\UserContext;
use SGFP\Application\Services\DeleteCategoryService;
use SGFP\Application\Services\DeleteCommitmentService;
use SGFP\Application\Services\MaterializeRecurrenceOccurrenceService;
use SGFP\Application\Services\RenameCategoryService;
use SGFP\Application\Services\SetInitialBalanceService;
use SGFP\Application\Services\UpdateCommitmentService;

final class OwnershipBoundaryTest extends TestCase
{
    public function testUserCannotRenameAnotherUsersCategory(): void
    {
        $repository = $this->createMock(CategoryRepository::class);
        $repository->expects($this->once())->method('findById')->with(7, 2)->willReturn(null);
        $context = $this->context(2);

        $this->expectExceptionCode(404);
        (new RenameCategoryService($repository, $context))->execute(7, 'Exposta');
    }

    public function testUserCannotDeleteAnotherUsersCategory(): void
    {
        $repository = $this->createMock(CategoryRepository::class);
        $repository->expects($this->once())->method('findById')->with(7, 2)->willReturn(null);
        $context = $this->context(2);

        $this->expectExceptionCode(404);
        (new DeleteCategoryService($repository, $context))->execute(7);
    }

    public function testUserCannotSetInitialBalanceOnAnotherUsersAccount(): void
    {
        $accounts = $this->createMock(AccountRepository::class);
        $accounts->expects($this->once())->method('findById')->with(9, 2)->willReturn(null);
        $context = $this->context(2);

        $this->expectExceptionCode(404);
        (new SetInitialBalanceService(
            $accounts,
            $this->createMock(\SGFP\Application\Ports\EntryRepository::class),
            $this->createMock(TransactionManager::class),
            $context
        ))->execute(9, '10.00', null, null, null);
    }

    public function testUserCannotUpdateAnotherUsersCommitment(): void
    {
        $repository = $this->createMock(CommitmentRepository::class);
        $repository->expects($this->once())->method('findById')->with(11, 2)->willReturn(null);

        $this->expectExceptionCode(404);
        (new UpdateCommitmentService($repository, $this->createMock(CategoryRepository::class), $this->context(2)))
            ->execute(11, null, 'Tentativa', '10.00');
    }

    public function testUserCannotDeleteAnotherUsersCommitment(): void
    {
        $repository = $this->createMock(CommitmentRepository::class);
        $repository->expects($this->once())->method('findById')->with(11, 2)->willReturn(null);

        $this->expectExceptionCode(404);
        (new DeleteCommitmentService($repository, $this->context(2)))->execute(11);
    }

    public function testUserCannotMaterializeAnotherUsersRecurrence(): void
    {
        $repository = $this->createMock(RecurrenceRepository::class);
        $repository->expects($this->once())->method('findById')->with(13, 2)->willReturn(null);

        $this->expectExceptionCode(404);
        (new MaterializeRecurrenceOccurrenceService(
            $repository,
            $this->createMock(CommitmentRepository::class),
            $this->createMock(TransactionManager::class),
            $this->context(2)
        ))->execute(13, '2026-09-01');
    }

    private function context(int $userId): UserContext
    {
        $context = $this->createMock(UserContext::class);
        $context->method('requireUserId')->willReturn($userId);
        return $context;
    }
}
