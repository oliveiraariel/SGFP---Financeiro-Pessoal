<?php

declare(strict_types=1);

namespace SGFP\Tests\Unit;

use PHPUnit\Framework\TestCase;
use SGFP\Application\Backup\BackupArchive;
use SGFP\Application\Backup\BackupProtector;
use SGFP\Application\Ports\RestorationTokenClaim;
use SGFP\Application\Ports\UserContext;
use SGFP\Application\Ports\UserOperationLock;
use SGFP\Application\Ports\UserPreferenceRepository;
use SGFP\Application\Services\CapturePreRestorationSnapshotService;
use SGFP\Application\Services\RevalidateRestorationService;

final class RevalidateRestorationServiceTest extends TestCase
{
    public function testRejectsFalseConfirmationBeforeAnyMutation(): void
    {
        $service = new RevalidateRestorationService(
            $this->createMock(UserPreferenceRepository::class),
            $this->createMock(UserContext::class),
            $this->createMock(UserOperationLock::class),
            $this->createMock(CapturePreRestorationSnapshotService::class),
            $this->createMock(RestorationTokenClaim::class),
        );

        $this->expectException(\InvalidArgumentException::class);
        $service->confirm(str_repeat('a', 64), false);
    }

    public function testReturnsPreRestoreZipForLocalDownload(): void
    {
        putenv('SGFP_BACKUP_KEY=test-key');

        $preferences = $this->createMock(UserPreferenceRepository::class);
        $context = $this->createMock(UserContext::class);
        $lock = $this->createMock(UserOperationLock::class);
        $snapshotService = $this->createMock(CapturePreRestorationSnapshotService::class);
        $claim = $this->createMock(RestorationTokenClaim::class);

        $token = str_repeat('a', 64);
        $directory = sys_get_temp_dir();
        putenv('SGFP_BACKUP_DIR=' . $directory);

        $zip = $this->validZip(7, 'manual');
        $path = tempnam($directory, 'sgfp-restore-');
        file_put_contents($path, $zip);

        $snapshotZip = $this->validZip(7, 'pre_restore');
        $snapshotPath = tempnam($directory, 'sgfp-snapshot-');
        file_put_contents($snapshotPath, $snapshotZip);

        $context->method('requireUserId')->willReturn(7);
        $preferences->method('get')->willReturn(json_encode([
            'expires_at' => time() + 60,
            'origin' => 'manual',
            'hash' => hash('sha256', $zip),
            'path' => $path,
        ], JSON_THROW_ON_ERROR));

        $snapshotService->expects($this->once())
            ->method('captureUnderLock')
            ->with(7)
            ->willReturn([
                'path' => $snapshotPath,
                'hash' => hash('sha256', $snapshotZip),
                'expires_at' => time() + 86400,
                'origin' => 'pre_restore',
            ]);

        $claim->expects($this->once())
            ->method('claim')
            ->with(7, $token, $this->isType('int'))
            ->willReturn(['claimed_at' => time()]);

        $service = new RevalidateRestorationService(
            $preferences,
            $context,
            $lock,
            $snapshotService,
            $claim
        );

        $result = $service->confirm($token, true);

        $this->assertSame('confirmation_accepted', $result['status']);
        $this->assertSame('application/zip', $result['snapshot']['content_type']);
        $this->assertSame($snapshotZip, base64_decode($result['snapshot']['content_base64'], true));

        @unlink($path);
        @unlink($snapshotPath);
    }

    private function validZip(int $owner, string $origin): string
    {
        $payload = [
            'metadata' => [
                'schema' => 'sgfp-backup',
                'version' => 2,
                'owner' => $owner,
                'origin' => $origin,
                'created_at' => '2026-09-14T00:00:00+00:00',
            ],
            'theme' => 'light',
            'accounts' => [[
                'id' => 10,
                'userId' => $owner,
                'name' => 'Minha Conta',
                'createdAt' => '2026-09-14T00:00:00+00:00',
            ]],
            'categories' => [],
            'recurrences' => [],
            'commitments' => [],
            'entries' => [],
        ];

        $json = json_encode($payload, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE);

        return (new BackupArchive())->pack(
            (new BackupProtector())->protect($json)
        );
    }
}
