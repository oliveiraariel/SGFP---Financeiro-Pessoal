<?php

declare(strict_types=1);

namespace SGFP\Tests\Unit;

use PHPUnit\Framework\TestCase;
use SGFP\Application\Ports\UserContext;
use SGFP\Application\Ports\UserOperationLock;
use SGFP\Application\Ports\UserPreferenceRepository;
use SGFP\Application\Services\RevalidateRestorationService;
use SGFP\Application\Services\CapturePreRestorationSnapshotService;

final class RevalidateRestorationServiceTest extends TestCase
{
    public function testRejectsFalseConfirmationBeforeAnyMutation(): void
    {
        $service = new RevalidateRestorationService(
            $this->createMock(UserPreferenceRepository::class),
            $this->createMock(UserContext::class),
            $this->createMock(UserOperationLock::class),
            $this->createMock(CapturePreRestorationSnapshotService::class),
        );

        $this->expectException(\InvalidArgumentException::class);
        $service->confirm(str_repeat('a', 64), false);
    }

    public function testAcceptsConfirmationAfterRevalidatingPreparedToken(): void
    {
        $preferences = $this->createMock(UserPreferenceRepository::class);
        $context = $this->createMock(UserContext::class);
        $lock = $this->createMock(UserOperationLock::class);
        $token = str_repeat('a', 64);
        $directory = sys_get_temp_dir();
        $path = tempnam($directory, 'sgfp-test-');
        file_put_contents($path, 'staged-backup');
        putenv('SGFP_BACKUP_DIR=' . $directory);

        $context->method('requireUserId')->willReturn(7);
        $snapshot = $this->createMock(CapturePreRestorationSnapshotService::class);
        $snapshot->expects($this->once())->method('captureUnderLock')->with(7)->willReturn([
            'path' => '/private/pre-restore.sgfp',
            'hash' => str_repeat('b', 64),
            'expires_at' => time() + 86400,
            'origin' => 'pre_restore',
        ]);
        $preferences->method('get')->willReturn(json_encode([
            'expires_at' => time() + 60,
            'origin' => 'manual',
            'hash' => hash('sha256', 'staged-backup'),
            'path' => $path,
        ], JSON_THROW_ON_ERROR));

        $service = new RevalidateRestorationService($preferences, $context, $lock, $snapshot);
        $result = $service->confirm($token, true);
        $this->assertSame('confirmation_accepted', $result['status']);
        $this->assertSame('manual', $result['origin']);
        $this->assertSame('pre_restore', $result['snapshot']['origin']);
        $this->assertGreaterThan(time(), $result['expires_at']);
        unlink($path);
    }
}
