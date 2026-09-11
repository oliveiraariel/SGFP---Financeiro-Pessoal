<?php

declare(strict_types=1);

require_once __DIR__ . '/../../vendor/autoload.php';

use SGFP\Application\Ports\AccountRepository;
use SGFP\Application\Ports\CommitmentRepository;
use SGFP\Application\Ports\EntryRepository;
use SGFP\Application\Ports\TransactionManager;
use SGFP\Application\Ports\TransferRepository;
use SGFP\Application\Ports\UserContext;
use SGFP\Application\Services\CreateTransferService;
use SGFP\Application\Services\SettleTransferService;
use SGFP\Application\Services\UndoTransferSettlementService;
use SGFP\Domain\Enums\AccountRole;
use SGFP\Domain\Enums\CommitmentStatus;
use SGFP\Domain\Enums\CommitmentType;
use SGFP\Domain\Enums\EntryEffectType;
use SGFP\Domain\Enums\EntryOrigin;
use SGFP\Domain\Enums\EntryState;
use SGFP\Domain\Models\Account;
use SGFP\Domain\Models\Commitment;
use SGFP\Domain\Models\Entry;
use SGFP\Domain\Models\Transfer;

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
        return null;
    }

    public function findFirstByRecurrenceId(int $recurrenceId, int $userId): ?Commitment
    {
        return null;
    }

    public function findPendingCommitmentsByUserAndPeriod(int $userId, string $startMonth, string $endMonth): array
    {
        return array_values(array_filter(
            $this->commitments,
            fn (Commitment $c) => $c->userId === $userId && $c->status->value === 'PENDENTE' && $c->referenceMonth->format('Y-m-d') >= $startMonth && $c->referenceMonth->format('Y-m-d') <= $endMonth
        ));
    }
}

final class InMemoryTransferRepository implements TransferRepository
{
    private array $transfers = [];

    public function save(Transfer $transfer): void
    {
        $this->transfers[$transfer->commitmentId] = $transfer;
    }

    public function findByCommitmentId(int $commitmentId, int $userId): ?Transfer
    {
        $transfer = $this->transfers[$commitmentId] ?? null;
        return $transfer && $transfer->userId === $userId ? $transfer : null;
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
$commitments = new InMemoryCommitmentRepository();
$transfers = new InMemoryTransferRepository();
$entries = new InMemoryEntryRepository();
$transactions = new DirectTransactionManager();
$userContext = new FixedUserContext(1);

$principal = new Account(1, 1, 'Principal', AccountRole::PRINCIPAL, new \DateTimeImmutable());
$secondary = new Account(2, 1, 'Reserva', AccountRole::SECUNDARIA, new \DateTimeImmutable());
$accounts->save($principal);
$accounts->save($secondary);

$createService = new CreateTransferService($accounts, $commitments, $transfers, $transactions, $userContext);
$settleService = new SettleTransferService($commitments, $transfers, $entries, $transactions, $userContext);
$undoService = new UndoTransferSettlementService($commitments, $transfers, $entries, $transactions, $userContext);

// Teste 1: cria transferência Principal -> Secundária
[$commitment, $transfer] = $createService->execute('Reserva de emergência', 300.00, '2026-09', 1, 2);
assertEquals(CommitmentType::TRANSFERENCIA, $commitment->type, 'Tipo deve ser TRANSFERENCIA');
assertEquals('SAIDA', $commitment->nature->value, 'Natureza Principal->Secundária é SAIDA');
assertEquals(1, $transfer->sourceAccountId, 'Origem');
assertEquals(2, $transfer->targetAccountId, 'Destino');

// Teste 2: rejeita Secundária -> Secundária
try {
    $createService->execute('Inválida', 100.00, '2026-09', 2, 2);
    throw new \RuntimeException('FALHA: deveria rejeitar origem e destino iguais');
} catch (\RuntimeException $e) {
    assertEquals(422, $e->getCode(), 'Código para contas iguais');
}

// Teste 3: efetiva a transferência, gerando dois lançamentos
[$settled, $outflow, $inflow] = $settleService->execute((int) $commitment->id);
assertEquals(CommitmentStatus::EFETIVADO, $settled->status, 'Status após efetivação');
assertEquals(EntryEffectType::SAIDA, $outflow->effectType, 'Saída da origem');
assertEquals(1, $outflow->accountId, 'Conta de saída');
assertEquals(EntryEffectType::ENTRADA, $inflow->effectType, 'Entrada no destino');
assertEquals(2, $inflow->accountId, 'Conta de entrada');
assertEquals(300.00, $outflow->amount, 'Valor saída');
assertEquals(300.00, $inflow->amount, 'Valor entrada');

// Teste 4: desfaz a efetivação
$undoService->execute((int) $commitment->id);
$undoneOutflow = $entries->findByCommitmentIdAndAccount((int) $commitment->id, 1, 1);
$undoneInflow = $entries->findByCommitmentIdAndAccount((int) $commitment->id, 2, 1);
assertEquals(EntryState::DESFEITO, $undoneOutflow?->state, 'Saída desfeita');
assertEquals(EntryState::DESFEITO, $undoneInflow?->state, 'Entrada desfeita');
assertNotNull($undoneOutflow?->undoneAt, 'Data de desfazimento saída');

$undoneCommitment = $commitments->findById((int) $commitment->id, 1);
assertEquals(CommitmentStatus::PENDENTE, $undoneCommitment?->status, 'Compromisso voltou a pendente');

echo "OK: todos os testes manuais de transferência passaram.\n";
