<?php

declare(strict_types=1);

namespace SGFP\Application\Services;

use SGFP\Application\Ports\TransactionManager;
use SGFP\Application\Ports\UserContext;
use SGFP\Application\Ports\UserDataPurger;
use SGFP\Application\Ports\UserOperationLock;
use SGFP\Domain\Models\Account;

final class ResetProfileService
{
    public const CONFIRMATION_PHRASE = 'RESETAR PERFIL';
    private const TOKEN_TTL = 600;

    public function __construct(
        private readonly UserDataPurger $purger,
        private readonly ProvisionUserService $provisioner,
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
        $this->metaSet()($userId, '_sgfp_reset_confirmation', [
            'hash' => hash('sha256', $token),
            'expires_at' => $this->now() + self::TOKEN_TTL,
        ]);
        return $token;
    }

    public function execute(string $token, string $phrase): Account
    {
        if ($phrase !== self::CONFIRMATION_PHRASE) {
            throw new \InvalidArgumentException('Digite exatamente RESETAR PERFIL para confirmar.');
        }

        $this->userContext->requireCapability('use_sgfp');
        $userId = $this->userContext->requireUserId();

        $this->lock->acquire($userId);

        try {
            $this->consume($userId, $token);
            return $this->transactions->transactional(function () use ($userId): Account {
                $this->purger->purgeSgfpData($userId);
                return $this->provisioner->execute($userId);
            });
        } finally {
            $this->lock->release($userId);
        }
    }

    private function consume(int $userId, string $token): void
    {
        $record = $this->metaGet()($userId, '_sgfp_reset_confirmation');
        if (!is_array($record) || !hash_equals((string) ($record['hash'] ?? ''), hash('sha256', $token)) || (int) ($record['expires_at'] ?? 0) < $this->now()) {
            throw new \InvalidArgumentException('Confirmação do reset inválida, expirada ou já consumida.');
        }
        $this->metaDelete()($userId, '_sgfp_reset_confirmation');
    }

    private function metaGet(): callable { return $this->metaGet ?? static fn (int $id, string $key) => get_user_meta($id, $key, true); }
    private function metaSet(): callable { return $this->metaSet ?? static fn (int $id, string $key, mixed $value) => update_user_meta($id, $key, $value); }
    private function metaDelete(): callable { return $this->metaDelete ?? static fn (int $id, string $key) => delete_user_meta($id, $key); }
    private function now(): int { return $this->clock !== null ? ($this->clock)() : time(); }
}
