<?php

declare(strict_types=1);

require_once __DIR__ . '/../../vendor/autoload.php';

use SGFP\Application\Ports\AccountRepository;
use SGFP\Application\Ports\CategoryRepository;
use SGFP\Application\Ports\CommitmentRepository;
use SGFP\Application\Ports\EntryRepository;
use SGFP\Application\Ports\RecurrenceRepository;
use SGFP\Application\Ports\TransactionManager;
use SGFP\Application\Ports\UserContext;
use SGFP\Application\Services\CreateCommitmentService;
use SGFP\Application\Services\MaterializeRecurrenceOccurrenceService;
use SGFP\Application\Services\SettleRecurrenceOccurrenceService;
use SGFP\Application\Services\UndoRecurrenceOccurrenceSettlementService;
use SGFP\Domain\Enums\AccountRole;
use SGFP\Domain\Enums\CommitmentNature;
use SGFP\Domain\Enums\CommitmentStatus;
use SGFP\Domain\Enums\CommitmentType;
use SGFP\Domain\Enums\EntryEffectType;
use SGFP\Domain\Enums\EntryOrigin;
use SGFP\Domain\Enums\EntryState;
use SGFP\Domain\Models\Account;
use SGFP\Domain\Models\Category;
use SGFP\Domain\Models\Commitment;
use SGFP\Domain\Models\Entry;
use SGFP\Domain\Models\Recurrence;

final class InMemoryAccountRepository implements AccountRepository
{
    private array $accounts = [];

    public function save(Account $account): Account
    {
        $this->accounts[$account->id] = $account;
        return $account;
    }

    public function findById(int $id, int $userId): ?Account
    {
        $account = $this->accounts[$id] ?? null;
        return $account && $account->userId === $userId ? $account : null;
    }

    public function findAllByUser(int $userId): array
    {
        return array_values(array_filter($this->accounts, fn (Account $a) => $a->userId === $userId));
    }

    public function findPrincipal(int $userId): ?Account
    {
        foreach ($this->accounts as $account) {
            if ($account->userId === $userId && $account->role === AccountRole::PRINCIPAL) {
                return $account;
            }
        }
        return null;
    }

    public function hasPrincipal(int $userId): bool
    {
        return $this->findPrincipal($userId) !== null;
    }

    public function countByUser(int $userId): int
    {
        return count(array_filter($this->accounts, fn (Account $a) => $a->userId === $userId));
    }
}

final class InMemoryCategoryRepository implements CategoryRepository
{
    public function save(Category $category): Category
    {
        return $category;
    }

    public function findById(int $id, int $userId): ?Category
    {
        return null;
    }

    public function findAllByUser(int $userId): array
    {
        return [];
    }

    public function existsByName(int $userId, string $name): bool
    {
        return false;
    }

    public function seedDefaults(int $userId): void
    {
    }
}

final class InMemoryRecurrenceRepository implements RecurrenceRepository
{
    private array $recurrences = [];
    private int $nextId = 1;

    public function save(Recurrence $recurrence): Recurrence
    {
        if ($recurrence->id === null) {
            $recurrence = $recurrence->withId($this->nextId++);
        }
        $this->recurrences[$recurrence->id] = $recurrence;
        return $recurrence;
    }

    public function findById(int $id, int $userId): ?Recurrence
    {
        $recurrence = $this->recurrences[$id] ?? null;
        return $recurrence && $recurrence->userId === $userId ? $recurrence : null;
    }
}

final class InMemoryCommitmentRepository implements CommitmentRepository
{
    private array $commitments = [];
    private int $nextId = 1;

    public function save(Commitment $commitment): Commitment
    {
        if ($commitment->id === null) {
            $commitment = $commitment->withId($this->nextId++);
        }
        $this->commitments[$commitment->id] = $commitment;
        return $commitment;
    }

    public function findById(int $id, int $userId): ?Commitment
    {
        $commitment = $this->commitments[$id] ?? null;
        return $commitment && $commitment->userId === $userId ? $commitment : null;
    }

    public function findAllByUser(int $userId): array
    {
        return array_values(array_filter($this->commitments, fn (Commitment $c) => $c->userId === $userId));
    }

    public function findByRecurrenceIdAndMonth(int $recurrenceId, string $month, int $userId): ?Commitment
    {
        foreach ($this->commitments as $commitment) {
            if (
                $commitment->recurrenceId === $recurrenceId
                && $commitment->referenceMonth->format('Y-m-d') === $month
                && $commitment->userId === $userId
            ) {
                return $commitment;
            }
        }
        return null;
    }

    public function findFirstByRecurrenceId(int $recurrenceId, int $userId): ?Commitment
    {
        $matches = array_values(array_filter(
            $this->commitments,
            fn (Commitment $c) => $c->recurrenceId === $recurrenceId && $c->userId === $userId
        ));
        usort($matches, fn (Commitment $a, Commitment $b) => $a->referenceMonth <=> $b->referenceMonth);
        return $matches[0] ?? null;
    }

    public function findPendingCommitmentsByUserAndPeriod(int $userId, string $startMonth, string $endMonth): array
    {
        return array_values(array_filter(
            $this->commitments,
            fn (Commitment $c) => $c->userId === $userId && $c->status->value === 'PENDENTE' && $c->referenceMonth->format('Y-m-d') >= $startMonth && $c->referenceMonth->format('Y-m-d') <= $endMonth
        ));
    }
}

final class InMemoryEntryRepository implements EntryRepository
{
    private array $entries = [];
    private int $nextId = 1;

    public function save(Entry $entry): Entry
    {
        if ($entry->id === null) {
            $entry = $entry->withId($this->nextId++);
        }
        $this->entries[$entry->id] = $entry;
        return $entry;
    }

    public function findByCommitmentId(int $commitmentId, int $userId): ?Entry
    {
        foreach ($this->entries as $entry) {
            if ($entry->commitmentId === $commitmentId && $entry->userId === $userId) {
                return $entry;
            }
        }
        return null;
    }

    public function findByCommitmentIdAndAccount(int $commitmentId, int $accountId, int $userId): ?Entry
    {
        foreach ($this->entries as $entry) {
            if (
                $entry->commitmentId === $commitmentId
                && $entry->accountId === $accountId
                && $entry->userId === $userId
            ) {
                return $entry;
            }
        }
        return null;
    }

    public function findActiveInitialBalanceByAccount(int $accountId, int $userId): ?Entry
    {
        foreach ($this->entries as $entry) {
            if (
                $entry->accountId === $accountId
                && $entry->userId === $userId
                && $entry->origin === EntryOrigin::SALDO_INICIAL
                && $entry->state === EntryState::ATIVO
            ) {
                return $entry;
            }
        }
        return null;
    }

    public function findActiveEntriesByUser(int $userId): array
    {
        return array_values(array_filter(
            $this->entries,
            fn (Entry $e) => $e->userId === $userId && $e->state === EntryState::ATIVO
        ));
    }

    public function findActiveEntriesByUserAndPeriod(int $userId, \DateTimeImmutable $start, \DateTimeImmutable $end): array
    {
        return array_values(array_filter(
            $this->entries,
            fn (Entry $e) => $e->userId === $userId && $e->state === EntryState::ATIVO && $e->settledAt >= $start && $e->settledAt <= $end
        ));
    }
}

final class DirectTransactionManager implements TransactionManager
{
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
}

final class FixedUserContext implements UserContext
{
    public function __construct(private readonly int $userId)
    {
    }

    public function currentUserId(): ?int
    {
        return $this->userId;
    }

    public function requireUserId(): int
    {
        return $this->userId;
    }

    public function hasCapability(string $capability): bool
    {
        return true;
    }

    public function requireCapability(string $capability): void
    {
    }
}

function assertEquals(mixed $expected, mixed $actual, string $message): void
{
    if ($expected !== $actual) {
        throw new \RuntimeException("FALHA: {$message}. Esperado: " . var_export($expected, true) . ', Obtido: ' . var_export($actual, true));
    }
}

function assertNotNull(mixed $actual, string $message): void
{
    if ($actual === null) {
        throw new \RuntimeException("FALHA: {$message}. Esperado não-nulo.");
    }
}

$accounts = new InMemoryAccountRepository();
$categories = new InMemoryCategoryRepository();
$recurrences = new InMemoryRecurrenceRepository();
$commitments = new InMemoryCommitmentRepository();
$entries = new InMemoryEntryRepository();
$transactions = new DirectTransactionManager();
$userContext = new FixedUserContext(1);

$principal = new Account(1, 1, 'Principal', AccountRole::PRINCIPAL, new \DateTimeImmutable());
$accounts->save($principal);

$createService = new CreateCommitmentService($commitments, $categories, $recurrences, $userContext);
$materializeService = new MaterializeRecurrenceOccurrenceService($recurrences, $commitments, $transactions, $userContext);
$settleService = new SettleRecurrenceOccurrenceService($materializeService, $commitments, $entries, $accounts, $transactions, $userContext);
$undoService = new UndoRecurrenceOccurrenceSettlementService($materializeService, $commitments, $entries, $transactions, $userContext);

// Cria compromisso recorrente
$base = $createService->execute(null, 'Netflix', 50.00, 'PADRAO', 'SAIDA', '2026-09', 12);
assertNotNull($base->recurrenceId, 'Compromisso deve ter recurrence_id');

// Materializa ocorrência de outubro
$october = $materializeService->execute((int) $base->recurrenceId, '2026-10');
assertEquals('Netflix', $october->name, 'Nome da ocorrência materializada');
assertEquals('2026-10-01', $october->referenceMonth->format('Y-m-d'), 'Mês da ocorrência');
assertEquals(CommitmentStatus::PENDENTE, $october->status, 'Status da ocorrência');

// Efetiva a ocorrência de outubro
$entry = $settleService->execute((int) $base->recurrenceId, '2026-10');
assertEquals(EntryEffectType::SAIDA, $entry->effectType, 'Efeito da ocorrência');
assertEquals(EntryState::ATIVO, $entry->state, 'Estado do lançamento');

// Desfaz a efetivação
$undoService->execute((int) $base->recurrenceId, '2026-10');
$undoneEntry = $entries->findByCommitmentId((int) $october->id, 1);
assertEquals(EntryState::DESFEITO, $undoneEntry?->state, 'Lançamento desfeito');

echo "OK: todos os testes manuais de ocorrências de recorrência passaram.\n";
