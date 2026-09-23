<?php

declare(strict_types=1);

namespace SGFP\Tests\Unit;

use PHPUnit\Framework\TestCase;
use SGFP\Application\Backup\BackupArchive;
use SGFP\Application\Backup\BackupProtector;
use SGFP\Application\Ports\RestorationTokenStore;
use SGFP\Application\Ports\UserContext;
use SGFP\Application\Services\ValidateBackupService;

final class ValidateBackupServiceTest extends TestCase
{
    public function testValidatesBackupUsingPrivateTemporaryStagingWhenDirectoryIsNotConfigured(): void
    {
        $previousKey = getenv('SGFP_BACKUP_KEY');
        $previousDirectory = getenv('SGFP_BACKUP_DIR');
        $storedPath = null;

        putenv('SGFP_BACKUP_KEY=test-key');
        putenv('SGFP_BACKUP_DIR');

        try {
            $context = $this->createMock(UserContext::class);
            $context->method('requireUserId')->willReturn(7);

            $tokens = $this->createMock(RestorationTokenStore::class);
            $tokens->expects($this->once())
                ->method('store')
                ->willReturnCallback(function (int $userId, string $hash, int $expiresAt, string $metadata) use (&$storedPath): void {
                    self::assertSame(7, $userId);
                    self::assertMatchesRegularExpression('/^[a-f0-9]{64}$/', $hash);
                    self::assertGreaterThan(time(), $expiresAt);
                    $decoded = json_decode($metadata, true, flags: JSON_THROW_ON_ERROR);
                    $storedPath = $decoded['path'] ?? null;
                });

            $result = (new ValidateBackupService($tokens, $context))->validate($this->validZip(7));

            self::assertMatchesRegularExpression('/^[a-f0-9]{64}$/', $result['token']);
            self::assertIsString($storedPath);
            self::assertFileExists($storedPath);
            self::assertStringContainsString('sgfp-private-backups' . DIRECTORY_SEPARATOR, $storedPath);
        } finally {
            if (is_string($storedPath) && is_file($storedPath)) {
                @unlink($storedPath);
            }
            $previousKey === false ? putenv('SGFP_BACKUP_KEY') : putenv('SGFP_BACKUP_KEY=' . $previousKey);
            $previousDirectory === false ? putenv('SGFP_BACKUP_DIR') : putenv('SGFP_BACKUP_DIR=' . $previousDirectory);
        }
    }

    private function validZip(int $owner): string
    {
        $payload = [
            'metadata' => [
                'schema' => 'sgfp-backup',
                'version' => 2,
                'owner' => $owner,
                'origin' => 'manual',
                'created_at' => '2026-09-22T00:00:00+00:00',
            ],
            'theme' => 'dark',
            'accounts' => [[
                'id' => 10,
                'userId' => $owner,
                'name' => 'Minha Conta',
                'createdAt' => '2026-09-22T00:00:00+00:00',
            ]],
            'categories' => [],
            'recurrences' => [],
            'commitments' => [],
            'entries' => [],
        ];

        return (new BackupArchive())->pack(
            (new BackupProtector())->protect(json_encode($payload, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE))
        );
    }
}
