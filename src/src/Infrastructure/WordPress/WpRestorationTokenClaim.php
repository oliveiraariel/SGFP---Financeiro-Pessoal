<?php

declare(strict_types=1);

namespace SGFP\Infrastructure\WordPress;

use SGFP\Application\Ports\RestorationTokenClaim;
use SGFP\Infrastructure\Database\TableNames;

final class WpRestorationTokenClaim implements RestorationTokenClaim
{
    public function claim(int $userId, string $token, int $now): ?array
    {
        global $wpdb;
        $hash = hash('sha256', $token);
        $table = TableNames::restorationToken();
        $row = $wpdb->get_row($wpdb->prepare(
            "SELECT id, metadata, UNIX_TIMESTAMP(expires_at) AS expires_at FROM {$table} WHERE user_id = %d AND token_hash = %s AND claimed_at IS NULL AND expires_at >= FROM_UNIXTIME(%d) LIMIT 1 FOR UPDATE",
            $userId,
            $hash,
            $now
        ), ARRAY_A);
        if ($row === null && $wpdb->last_error !== '') throw new \RuntimeException('Falha ao bloquear token: ' . $wpdb->last_error);
        if (!is_array($row)) return null;
        $metadata = json_decode((string) ($row['metadata'] ?? ''), true);
        if (!is_array($metadata)) throw new \RuntimeException('Metadata do token inválida.');
        $metadata['claimed_at'] = $now;
        $updated = $wpdb->update(
            $table,
            ['claimed_at' => gmdate('Y-m-d H:i:s', $now)],
            ['id' => (int) $row['id'], 'user_id' => $userId, 'claimed_at' => null],
            ['%s'],
            ['%d', '%d', 'NULL']
        );
        if ($updated === false) throw new \RuntimeException('Falha ao consumir token: ' . $wpdb->last_error);
        return $updated === 1 ? $metadata : null;
    }
}
