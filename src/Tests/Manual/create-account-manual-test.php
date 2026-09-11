<?php

declare(strict_types=1);

require_once __DIR__ . '/../../vendor/autoload.php';

use SGFP\Application\Ports\AccountRepository;
use SGFP\Application\Ports\UserContext;
use SGFP\Application\Services\CreateAccountService;
use SGFP\Domain\Enums\AccountRole;
use SGFP\Domain\Models\Account;
use SGFP\Domain\Policies\AccountPolicy;

final class InMemoryAccountRepository implements AccountRepository
{
    private array $accounts = [];
    private int $nextId = 1;

    public function save(Account $account): Account
    {
        if ($account->id === null) {
            $account = $account->withId($this->nextId++);
        }
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

    public function hasPrincipal(int $userId): bool
    {
        foreach ($this->accounts as $account) {
            if ($account->userId === $userId && $account->role === AccountRole::PRINCIPAL) {
                return true;
            }
        }
        return false;
    }

    public function countByUser(int $userId): int
    {
        return count(array_filter($this->accounts, fn (Account $a) => $a->userId === $userId));
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
        throw new \RuntimeException("FALHA: {$message}. Esperado: " . var_export($expected, true) . ", Obtido: " . var_export($actual, true));
    }
}

$repository = new InMemoryAccountRepository();
$userContext = new FixedUserContext(1);
$policy = new AccountPolicy($repository);
$service = new CreateAccountService($repository, $userContext, $policy);

// Teste 1: primeira conta vira Principal
$first = $service->execute('Minha Conta');
assertEquals(1, $first->id, 'ID da primeira conta');
assertEquals(AccountRole::PRINCIPAL, $first->role, 'Papel da primeira conta');
assertEquals('Minha Conta', $first->name, 'Nome da primeira conta');

// Teste 2: segunda conta vira Secundária
$second = $service->execute('Carteira');
assertEquals(2, $second->id, 'ID da segunda conta');
assertEquals(AccountRole::SECUNDARIA, $second->role, 'Papel da segunda conta');

// Teste 3: nome vazio lança exceção
try {
    $service->execute('   ');
    throw new \RuntimeException('FALHA: deveria ter lançado exceção para nome vazio');
} catch (\InvalidArgumentException) {
    // esperado
}

echo "OK: todos os testes manuais passaram.\n";
