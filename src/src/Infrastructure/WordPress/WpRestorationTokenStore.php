<?php
declare(strict_types=1);
namespace SGFP\Infrastructure\WordPress;

use SGFP\Application\Ports\RestorationTokenStore;
use SGFP\Infrastructure\Database\TableNames;

final class WpRestorationTokenStore implements RestorationTokenStore
{
    public function store(int $userId, string $hash, int $expiresAt, string $metadata): void
    {
        global $wpdb;
        $ok = $wpdb->insert(TableNames::restorationToken(), [
            'user_id' => $userId, 'token_hash' => $hash, 'metadata' => $metadata,
            'expires_at' => gmdate('Y-m-d H:i:s', $expiresAt),
        ], ['%d', '%s', '%s', '%s']);
        if ($ok !== 1) throw new \RuntimeException('Não foi possível persistir o token de restauração.');
    }

    public function get(int $userId, string $hash): ?array
    {
        global $wpdb;
        $row = $wpdb->get_row($wpdb->prepare(
            'SELECT metadata, UNIX_TIMESTAMP(expires_at) AS expires_at, claimed_at FROM ' . TableNames::restorationToken() . ' WHERE user_id = %d AND token_hash = %s LIMIT 1', $userId, $hash
        ), ARRAY_A);
        return is_array($row) ? $row : null;
    }
}
