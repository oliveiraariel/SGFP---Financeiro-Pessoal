<?php

declare(strict_types=1);

require_once __DIR__ . '/../../vendor/autoload.php';

use SGFP\Application\Ports\AccountRepository;
use SGFP\Application\Ports\EntryRepository;
use SGFP\Application\Ports\TransactionManager;
use SGFP\Application\Ports\UserContext;
use SGFP\Application\Services\SetInitialBalanceService;
use SGFP\Domain\Enums\AccountRole;
use SGFP\Domain\Enums\EntryEffectType;
use SGFP\Domain\Enums\EntryOrigin;
use SGFP\Domain\Enums\EntryState;
use SGFP\Domain\Models\Account;
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

    public function findById(int $id): ?Entry
    {
        return $this->entries[$id] ?? null;
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
$entries = new InMemoryEntryRepository();
$transactions = new DirectTransactionManager();
$userContext = new FixedUserContext(1);

$principal = new Account(1, 1, 'Principal', AccountRole::PRINCIPAL, new \DateTimeImmutable());
$accounts->save($principal);

$secondary = new Account(2, 1, 'Reserva', AccountRole::SECUNDARIA, new \DateTimeImmutable());
$accounts->save($secondary);

$service = new SetInitialBalanceService($accounts, $entries, $transactions, $userContext);

// Teste 1: cria saldo inicial na conta principal
$entry = $service->execute(1, 1500.00, 'Saldo de abertura', 'Setembro', new \DateTimeImmutable('2026-09-01'));
assertEquals(1, $entry->id, 'ID do lançamento');
assertEquals(1, $entry->accountId, 'Conta do lançamento');
assertEquals(EntryOrigin::SALDO_INICIAL, $entry->origin, 'Origem');
assertEquals(EntryEffectType::ENTRADA, $entry->effectType, 'Tipo de efeito');
assertEquals(EntryState::ATIVO, $entry->state, 'Estado');
assertEquals(1500.00, $entry->amount, 'Valor');
assertEquals('Saldo de abertura', $entry->name, 'Nome');
assertEquals('Setembro', $entry->description, 'Descrição');
assertEquals('2026-09-01T00:00:00+00:00', $entry->settledAt->format('c'), 'Data de efetivação');

// Teste 2: substitui saldo inicial anterior
$replacement = $service->execute(1, 2500.00, 'Novo saldo', null, null);
$active = $entries->findActiveInitialBalanceByAccount(1, 1);
assertEquals(2, $active?->id, 'ID do novo lançamento ativo');
assertEquals(2500.00, $active?->amount, 'Valor substituído');
$old = $entries->findById(1);
assertEquals(EntryState::DESFEITO, $old?->state, 'Saldo anterior deve estar desfeito');
assertNotNull($old?->undoneAt, 'Saldo anterior deve ter data de desfazimento');

// Teste 3: conta secundária é rejeitada
try {
    $service->execute(2, 100.00, null, null, null);
    throw new \RuntimeException('FALHA: deveria ter rejeitado conta secundária');
} catch (\RuntimeException $e) {
    assertEquals(403, $e->getCode(), 'Código HTTP para conta secundária');
}

// Teste 4: conta inexistente é rejeitada
try {
    $service->execute(99, 100.00, null, null, null);
    throw new \RuntimeException('FALHA: deveria ter rejeitado conta inexistente');
} catch (\RuntimeException $e) {
    assertEquals(404, $e->getCode(), 'Código HTTP para conta inexistente');
}

echo "OK: todos os testes manuais de saldo inicial passaram.\n";
