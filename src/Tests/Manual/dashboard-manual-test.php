<?php

declare(strict_types=1);

require_once __DIR__ . '/../../vendor/autoload.php';

use SGFP\Application\Services\GetDashboardService;
use SGFP\Application\Services\GetNetWorthService;
use SGFP\Application\Services\ListMovementsService;
use SGFP\Domain\Enums\AccountRole;
use SGFP\Domain\Enums\CommitmentNature;
use SGFP\Domain\Enums\CommitmentStatus;
use SGFP\Domain\Enums\CommitmentType;
use SGFP\Domain\Enums\EntryEffectType;
use SGFP\Domain\Enums\EntryOrigin;
use SGFP\Domain\Enums\EntryState;
use SGFP\Domain\Models\Account;
use SGFP\Domain\Models\Commitment;
use SGFP\Domain\Models\Entry;

final class FixedUserContext implements \SGFP\Application\Ports\UserContext
{
    public function currentUserId(): ?int { return 1; }
    public function requireUserId(): int { return 1; }
    public function hasCapability(string $capability): bool { return true; }
    public function requireCapability(string $capability): void {}
}

final class InMemoryAccountRepository implements \SGFP\Application\Ports\AccountRepository
{
    public function save(Account $account): Account { return $account; }
    public function findById(int $id, int $userId): ?Account { return null; }
    public function findAllByUser(int $userId): array
    {
        return [new Account(1, 1, 'Principal', AccountRole::PRINCIPAL, new \DateTimeImmutable())];
    }
    public function findPrincipal(int $userId): ?Account { return null; }
    public function hasPrincipal(int $userId): bool { return false; }
    public function countByUser(int $userId): int { return 0; }
}

final class InMemoryEntryRepository implements \SGFP\Application\Ports\EntryRepository
{
    private array $entries;

    public function __construct(array $entries)
    {
        $this->entries = $entries;
    }

    public function save(Entry $entry): Entry { return $entry; }
    public function findByCommitmentId(int $commitmentId, int $userId): ?Entry { return null; }
    public function findByCommitmentIdAndAccount(int $commitmentId, int $accountId, int $userId): ?Entry { return null; }
    public function findActiveInitialBalanceByAccount(int $accountId, int $userId): ?Entry { return null; }
    public function findAllByUser(int $userId): array { return array_values(array_filter($this->entries, fn (Entry $e) => $e->userId === $userId)); }
    public function findActiveEntriesByUser(int $userId): array { return $this->entries; }
    public function findActiveEntriesByUserAndPeriod(int $userId, \DateTimeImmutable $start, \DateTimeImmutable $end): array
    {
        return array_values(array_filter(
            $this->entries,
            fn (Entry $e) => $e->settledAt >= $start && $e->settledAt <= $end
        ));
    }
}

final class InMemoryCommitmentRepository implements \SGFP\Application\Ports\CommitmentRepository
{
    private array $commitments;

    public function __construct(array $commitments)
    {
        $this->commitments = $commitments;
    }

    public function save(Commitment $commitment): Commitment { return $commitment; }
    public function findById(int $id, int $userId): ?Commitment { return null; }
    public function findAllByUser(int $userId): array { return []; }
    public function findByRecurrenceIdAndMonth(int $recurrenceId, string $month, int $userId): ?Commitment { return null; }
    public function findFirstByRecurrenceId(int $recurrenceId, int $userId): ?Commitment { return null; }
    public function findPendingCommitmentsByUserAndPeriod(int $userId, string $startMonth, string $endMonth): array
    {
        return $this->commitments;
    }
}

function assertEquals(mixed $expected, mixed $actual, string $message): void
{
    if ($expected !== $actual) {
        throw new \RuntimeException("FALHA: {$message}. Esperado: " . var_export($expected, true) . ', Obtido: ' . var_export($actual, true));
    }
}

$userContext = new FixedUserContext();
$accounts = new InMemoryAccountRepository();

$entries = [
    new Entry(null, 1, 1, null, EntryOrigin::SALDO_INICIAL, 'Saldo', 1000.0, EntryEffectType::ENTRADA, new \DateTimeImmutable('2026-08-01'), null, EntryState::ATIVO, new \DateTimeImmutable(), null),
    new Entry(null, 1, 1, null, EntryOrigin::COMPROMISSO, 'Salário', 2000.0, EntryEffectType::ENTRADA, new \DateTimeImmutable('2026-09-05'), null, EntryState::ATIVO, new \DateTimeImmutable(), null),
    new Entry(null, 1, 1, null, EntryOrigin::COMPROMISSO, 'Aluguel', 800.0, EntryEffectType::SAIDA, new \DateTimeImmutable('2026-09-10'), null, EntryState::ATIVO, new \DateTimeImmutable(), null),
];

$pending = [
    new Commitment(10, 1, null, null, 'Luz', 150.0, CommitmentType::PADRAO, CommitmentNature::SAIDA, new \DateTimeImmutable('2026-09-01'), CommitmentStatus::PENDENTE, new \DateTimeImmutable()),
];

$entryRepo = new InMemoryEntryRepository($entries);
$commitmentRepo = new InMemoryCommitmentRepository($pending);

$listMovements = new ListMovementsService($entryRepo, $userContext);
$netWorth = new GetNetWorthService($accounts, $entryRepo, $userContext);
$dashboard = new GetDashboardService($accounts, $entryRepo, $commitmentRepo, $userContext);

$movements = $listMovements->execute('2026-09');
assertEquals(2, count($movements), 'Movimentos do mês');

$worth = $netWorth->execute();
assertEquals('2200.00', $worth['net_worth'], 'Patrimônio líquido');
assertEquals('2200.00', $worth['accounts'][0]['balance'], 'Saldo da conta');

$dash = $dashboard->execute('2026-09');
assertEquals('1000.00', $dash['opening_balance'], 'Saldo de abertura');
assertEquals('2000.00', $dash['realized_inflows'], 'Entradas realizadas');
assertEquals('800.00', $dash['realized_outflows'], 'Saídas realizadas');
assertEquals('150.00', $dash['expected_outflows'], 'Saídas previstas');
assertEquals('2050.00', $dash['expected_closing_balance'], 'Saldo final previsto');

echo "OK: todos os testes manuais de dashboard passaram.\n";
