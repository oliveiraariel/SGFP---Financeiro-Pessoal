<?php

declare(strict_types=1);

namespace SGFP\Infrastructure\WordPress;

use SGFP\Application\Ports\UserIdentityDeleter;

final class WpUserIdentityDeleter implements UserIdentityDeleter
{
    public function delete(int $userId): void
    {
        if ($userId <= 0) {
            throw new \InvalidArgumentException('Usuário inválido para exclusão.');
        }

        if (!function_exists('wp_delete_user')) {
            require_once ABSPATH . 'wp-admin/includes/user.php';
        }

        // Retry idempotente: a etapa já satisfeita não deve falhar.
        if (!get_user_by('id', $userId)) {
            wp_clear_auth_cookie();
            return;
        }

        if (!wp_delete_user($userId)) {
            throw new \RuntimeException('Não foi possível excluir a identidade WordPress.');
        }

        wp_clear_auth_cookie();
    }
}
