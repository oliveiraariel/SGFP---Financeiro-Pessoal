<?php

declare(strict_types=1);

namespace SGFP\Tests\Unit;

use PHPUnit\Framework\TestCase;
use SGFP\Domain\Models\Recurrence;
use SGFP\Infrastructure\WordPress\WpRecurrenceRepository;

final class WpRecurrenceRepositoryTest extends TestCase
{
    protected function tearDown(): void
    {
        global $wpdb;
        unset($wpdb);
    }

    public function testSaveReportsUpdateFailure(): void
    {
        global $wpdb;

        $wpdb = new class {
            public string $prefix = 'wp_';
            public string $last_error = 'database unavailable';

            public function update(string $table, array $data, array $where, array $format, array $whereFormat): false
            {
                return false;
            }
        };

        $recurrence = new Recurrence(
            7,
            11,
            new \DateTimeImmutable('2026-01-01'),
            12,
            null,
            new \DateTimeImmutable('2026-01-01'),
        );

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Falha ao atualizar recorrência: database unavailable');

        (new WpRecurrenceRepository())->save($recurrence);
    }
}
