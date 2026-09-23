<?php

declare(strict_types=1);

namespace SGFP\Application\Services;

use SGFP\Application\Ports\TransactionManager;
use SGFP\Application\Ports\UserContext;
use SGFP\Application\Ports\UserDataPurger;
use SGFP\Application\Ports\UserIdentityDeleter;
use SGFP\Application\Ports\UserOperationLock;

final class DeleteAccountService
{
    public const CONFIRMATION_PHRASE = 'EXCLUIR CONTA';
    private const TOKEN_TTL = 600;
    private const PENDING_META = '_sgfp_account_deletion_pending';
    private const RETRY_META = '_sgfp_account_deletion_retry';

    public function __construct(
        private readonly UserDataPurger $purger,
        private readonly UserIdentityDeleter $identityDeleter,
        private readonly TransactionManager $transactions,
        private readonly UserOperationLock $lock,
        private readonly UserContext $userContext,
        private readonly ?\Closure $metaGet = null,
        private readonly ?\Closure $metaSet = null,
        private readonly ?\Closure $metaDelete = null,
        private readonly ?\Closure $clock = null,
    ) {}

    public function begin(): string
    {
        $this->userContext->requireCapability('use_sgfp');
        $userId = $this->userContext->requireUserId();
        $token = bin2hex(random_bytes(32));
        $this->metaSet()($userId, '_sgfp_delete_confirmation', [
            'hash' => hash('sha256', $token),
            'expires_at' => $this->now() + self::TOKEN_TTL,
        ]);
        return $token;
    }

    public function execute(string $token, string $phrase): void
    {

        if ($phrase !== self::CONFIRMATION_PHRASE) {
            throw new \InvalidArgumentException('Digite exatamente EXCLUIR CONTA para confirmar.');
        }

        $this->userContext->requireCapability('use_sgfp');
        $userId = $this->userContext->requireUserId();

        $this->lock->acquire($userId);

        try {
            $this->consume($userId, $token);
            // A identidade WordPress não participa da transação SQL do SGFP.
            // Depois do COMMIT, uma falha deixa a exclusão explicitamente
            // incompleta e pode ser retomada sem recriar os dados removidos.
            $this->transactions->transactional(function () use ($userId): void {
                $this->purger->purgeSgfpData($userId);
            });
            $this->metaSet()($userId, self::PENDING_META, [
                'user_id' => $userId,
                'operation_id' => bin2hex(random_bytes(16)),
                'phase' => 'PURGE_COMMITTED',
                'status' => 'PENDING_IDENTITY_DELETE',
                'created_at' => $this->now(),
                'last_attempt_at' => $this->now(),
                'version' => 1,
            ]);
            try {
                $this->identityDeleter->delete($userId);
                $this->metaDelete()($userId, self::PENDING_META);
            } catch (\Throwable $e) {
                $this->metaSet()($userId, self::PENDING_META, [
                    'user_id' => $userId,
                    'phase' => 'PURGE_COMMITTED',
                    'status' => 'PENDING_IDENTITY_DELETE',
                    'last_attempt_at' => $this->now(),
                    'version' => 1,
                ]);
                throw new \RuntimeException(
                    'Exclusão incompleta: os dados SGFP foram removidos, mas a identidade WordPress não foi excluída. Tente novamente.',
                    409,
                    $e
                );
            }
        } finally {
            $this->lock->release($userId);
        }
    }

    public function beginRetry(): string
    {
        $userId = $this->userContext->requireUserId();
        $pending = $this->metaGet()($userId, self::PENDING_META);
        if (!is_array($pending) || (int) ($pending['user_id'] ?? 0) !== $userId) {
            throw new \RuntimeException('Nenhuma exclusão pendente.', 404);
        }
        $token = bin2hex(random_bytes(32));
        $this->metaSet()($userId, self::RETRY_META, [
            'hash' => hash('sha256', $token),
            'expires_at' => $this->now() + self::TOKEN_TTL,
            'user_id' => $userId,
        ]);
        return $token;
    }

    public function executeRetry(string $token, string $phrase): void
    {
        if ($phrase !== self::CONFIRMATION_PHRASE) {
            throw new \InvalidArgumentException('Digite exatamente EXCLUIR CONTA para confirmar.');
        }
        $userId = $this->userContext->requireUserId();
        $this->lock->acquire($userId);
        try {
            $this->consume($userId, $token, self::RETRY_META);
            $pending = $this->metaGet()($userId, self::PENDING_META);
            if (!is_array($pending) || (int) ($pending['user_id'] ?? 0) !== $userId) {
                throw new \RuntimeException('Nenhuma exclusão pendente.', 404);
            }
            $this->metaSet()($userId, self::PENDING_META, $pending + ['last_attempt_at' => $this->now()]);
            try {
                $this->identityDeleter->delete($userId);
                $this->metaDelete()($userId, self::PENDING_META);
            } catch (\Throwable $e) {
                throw new \RuntimeException('Exclusão incompleta: tente novamente.', 409, $e);
            }
        } finally {
            $this->lock->release($userId);
        }
    }

    private function consume(int $userId, string $token, string $key = '_sgfp_delete_confirmation'): void
    {
        $record = $this->metaGet()($userId, $key);
        if (!is_array($record) || !hash_equals((string) ($record['hash'] ?? ''), hash('sha256', $token)) || (int) ($record['expires_at'] ?? 0) < $this->now()) {
            throw new \InvalidArgumentException('Confirmação da exclusão inválida, expirada ou já consumida.');
        }
        $this->metaDelete()($userId, $key);
    }

    private function metaGet(): callable { return $this->metaGet ?? static fn (int $id, string $key) => get_user_meta($id, $key, true); }
    private function metaSet(): callable { return $this->metaSet ?? static fn (int $id, string $key, mixed $value) => update_user_meta($id, $key, $value); }
    private function metaDelete(): callable { return $this->metaDelete ?? static fn (int $id, string $key) => delete_user_meta($id, $key); }
    private function now(): int { return $this->clock !== null ? ($this->clock)() : time(); }
}
