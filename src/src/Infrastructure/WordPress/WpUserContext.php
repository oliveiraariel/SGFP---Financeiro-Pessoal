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
        return current_user_can($capability);
    }

    public function requireCapability(string $capability): void
    {
        if (!$this->hasCapability($capability)) {
            throw new \RuntimeException('Acesso negado.', 403);
        }
    }
}
