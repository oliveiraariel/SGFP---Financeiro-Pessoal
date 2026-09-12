<?php

declare(strict_types=1);

namespace SGFP\Infrastructure\WordPress;

use SGFP\Application\Ports\RestorationTokenClaim;

final class WpRestorationTokenClaim implements RestorationTokenClaim
{
    public function claim(int $userId, string $token, int $now): ?array
    {
        global $wpdb;
        $key = 'sgfp_restore_validation_' . hash('sha256', $token);
        $row = $wpdb->get_row($wpdb->prepare(
            "SELECT umeta_id, meta_value FROM {$wpdb->usermeta} WHERE user_id = %d AND meta_key = %s ORDER BY umeta_id DESC LIMIT 1 FOR UPDATE",
            $userId,
            $key
        ), ARRAY_A);
        if (!is_array($row)) return null;
        $metadata = json_decode((string) ($row['meta_value'] ?? ''), true);
        if (!is_array($metadata) || isset($metadata['claimed_at']) || (int) ($metadata['expires_at'] ?? 0) < $now) {
            return null;
        }
        $metadata['claimed_at'] = $now;
        $updated = $wpdb->update(
            $wpdb->usermeta,
            ['meta_value' => json_encode($metadata, JSON_THROW_ON_ERROR)],
            ['umeta_id' => (int) $row['umeta_id'], 'user_id' => $userId],
            ['%s'],
            ['%d', '%d']
        );
        return $updated === 1 ? $metadata : null;
    }
}
