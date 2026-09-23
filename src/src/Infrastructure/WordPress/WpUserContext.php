<?php

declare(strict_types=1);

namespace SGFP\Infrastructure\WordPress;

use SGFP\Application\Ports\UserContext;

final class WpUserContext implements UserContext
{
    public function currentUserId(): ?int
    {
        $userId = get_current_user_id();
        return $userId > 0 ? $userId : null;
    }

    public function requireUserId(): int
    {
        $userId = $this->currentUserId();

        if ($userId === null) {
            throw new \RuntimeException('Usuário não autenticado.', 401);
        }

        return $userId;
    }

    public function hasCapability(string $capability): bool
    {
        if ($capability === 'use_sgfp') {
            return self::canAccessSgfp();
        }

        return current_user_can($capability);
    }

    public static function canAccessSgfp(): bool
    {
        $userId = get_current_user_id();

        return $userId > 0
            && current_user_can('use_sgfp')
            && get_user_meta($userId, '_sgfp_provisioned', true) === '1';
    }

    public static function canRecoverAccountDeletion(): bool
    {
        $userId = get_current_user_id();
        $pending = $userId > 0 ? get_user_meta($userId, '_sgfp_account_deletion_pending', true) : null;
        return $userId > 0 && is_array($pending) && (int) ($pending['user_id'] ?? 0) === $userId;
    }

    public function requireCapability(string $capability): void
    {
        if (!$this->hasCapability($capability)) {
            throw new \RuntimeException('Acesso negado.', 403);
        }
    }
}
