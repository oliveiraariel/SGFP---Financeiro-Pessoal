<?php

declare(strict_types=1);

require_once __DIR__ . '/../../vendor/autoload.php';

use SGFP\Application\Ports\AccountRepository;
use SGFP\Application\Ports\CommitmentRepository;
use SGFP\Application\Ports\EntryRepository;
use SGFP\Application\Ports\TransactionManager;
use SGFP\Application\Ports\UserContext;
use SGFP\Application\Services\CreateCommitmentService;
use SGFP\Application\Services\SettleCommitmentService;
use SGFP\Application\Services\UndoCommitmentSettlementService;
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

final class InMemoryCategoryRepository implements \SGFP\Application\Ports\CategoryRepository
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
$commitments = new InMemoryCommitmentRepository();
$entries = new InMemoryEntryRepository();
$transactions = new DirectTransactionManager();
$userContext = new FixedUserContext(1);

$principal = new Account(1, 1, 'Principal', AccountRole::PRINCIPAL, new \DateTimeImmutable());
$accounts->save($principal);

$createService = new CreateCommitmentService($commitments, $categories, $userContext);
$settleService = new SettleCommitmentService($commitments, $entries, $accounts, $transactions, $userContext);
$undoService = new UndoCommitmentSettlementService($commitments, $entries, $transactions, $userContext);

// Teste 1: cria, efetiva e desfaz um compromisso simples
$commitment = $createService->execute(null, 'Salário', 2000.00, 'PADRAO', 'ENTRADA', '2026-09');
assertEquals(CommitmentStatus::PENDENTE, $commitment->status, 'Status inicial');

$entry = $settleService->execute((int) $commitment->id);
assertEquals(EntryState::ATIVO, $entry->state, 'Lançamento ativo após efetivação');

$undoneCommitment = $commitments->findById((int) $commitment->id, 1);
assertEquals(CommitmentStatus::EFETIVADO, $undoneCommitment?->status, 'Status após efetivação');

$undoService->execute((int) $commitment->id);

$entryAfterUndo = $entries->findByCommitmentId((int) $commitment->id, 1);
assertEquals(EntryState::DESFEITO, $entryAfterUndo?->state, 'Lançamento desfeito');
assertNotNull($entryAfterUndo?->undoneAt, 'Data de desfazimento preenchida');

$commitmentAfterUndo = $commitments->findById((int) $commitment->id, 1);
assertEquals(CommitmentStatus::PENDENTE, $commitmentAfterUndo?->status, 'Compromisso voltou a pendente');

// Teste 2: não permite desfazer compromisso pendente
try {
    $pending = $createService->execute(null, 'Bônus', 100.00, 'PADRAO', 'ENTRADA', '2026-09');
    $undoService->execute((int) $pending->id);
    throw new \RuntimeException('FALHA: deveria rejeitar desfazer compromisso pendente');
} catch (\RuntimeException $e) {
    assertEquals(409, $e->getCode(), 'Código para compromisso não efetivado');
}

echo "OK: todos os testes manuais de desfazer efetivação passaram.\n";
