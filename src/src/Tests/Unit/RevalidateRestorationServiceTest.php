<?php

declare(strict_types=1);

namespace SGFP\Tests\Unit;

use PHPUnit\Framework\TestCase;
use SGFP\Application\Backup\BackupArchive;
use SGFP\Application\Backup\BackupPayloadBuilder;
use SGFP\Application\Backup\BackupProtector;
use SGFP\Application\Ports\AccountRepository;
use SGFP\Application\Ports\BackupStore;
use SGFP\Application\Ports\CategoryRepository;
use SGFP\Application\Ports\CommitmentRepository;
use SGFP\Application\Ports\EntryRepository;
use SGFP\Application\Ports\RecurrenceRepository;
use SGFP\Application\Ports\RestorationTokenClaim;
use SGFP\Application\Ports\TransactionManager;
use SGFP\Application\Ports\UserContext;
use SGFP\Application\Ports\UserOperationLock;
use SGFP\Application\Ports\UserPreferenceRepository;
use SGFP\Application\Services\CapturePreRestorationSnapshotService;
use SGFP\Application\Services\RevalidateRestorationService;
use SGFP\Domain\Models\Account;

final class RevalidateRestorationServiceTest extends TestCase
{
    public function testRejectsFalseConfirmationBeforeAnyMutation(): void
    {
        $service = new RevalidateRestorationService(
            $this->createMock(UserPreferenceRepository::class),
            $this->createMock(UserContext::class),
            $this->createMock(UserOperationLock::class),
            $this->snapshotService(7),
            $this->createMock(RestorationTokenClaim::class),
        );

        $this->expectException(\InvalidArgumentException::class);
        $service->confirm(str_repeat('a', 64), false);
    }

    public function testReturnsPreRestoreZipMetadataWithoutExposingBinaryContent(): void
    {
        putenv('SGFP_BACKUP_KEY=test-key');

        $preferences = $this->createMock(UserPreferenceRepository::class);
        $context = $this->createMock(UserContext::class);
        $lock = $this->createMock(UserOperationLock::class);
        $claim = $this->createMock(RestorationTokenClaim::class);

        $token = str_repeat('a', 64);
        $directory = sys_get_temp_dir();
        putenv('SGFP_BACKUP_DIR=' . $directory);

        $zip = $this->validZip(7, 'manual');
        $path = tempnam($directory, 'sgfp-restore-');
        file_put_contents($path, $zip);

        $context->method('requireUserId')->willReturn(7);
        $preferences->method('get')->willReturn(json_encode([
            'expires_at' => time() + 60,
            'origin' => 'manual',
            'hash' => hash('sha256', $zip),
            'path' => $path,
        ], JSON_THROW_ON_ERROR));

        $claim->expects($this->once())
            ->method('claim')
            ->with(7, $token, $this->isType('int'))
            ->willReturn(['claimed_at' => time()]);

        $service = new RevalidateRestorationService(
            $preferences,
            $context,
            $lock,
            $this->snapshotService(7),
            $claim
        );

        $result = $service->confirm($token, true);

        $this->assertSame('confirmation_accepted', $result['status']);
        $this->assertSame('application/zip', $result['snapshot']['content_type']);
        $this->assertArrayHasKey('sha256', $result['snapshot']);
        $this->assertArrayNotHasKey('content_base64', $result['snapshot']);
        $this->assertArrayNotHasKey('content', $result['snapshot']);

        @unlink($path);
    }

    private function snapshotService(int $userId): CapturePreRestorationSnapshotService
    {
        $accounts = $this->createStub(AccountRepository::class);
        $categories = $this->createStub(CategoryRepository::class);
        $commitments = $this->createStub(CommitmentRepository::class);
        $entries = $this->createStub(EntryRepository::class);
        $recurrences = $this->createStub(RecurrenceRepository::class);
        $preferences = $this->createStub(UserPreferenceRepository::class);
        $transactions = $this->createStub(TransactionManager::class);
        $context = $this->createStub(UserContext::class);
        $lock = $this->createStub(UserOperationLock::class);

        $accounts->method('findAllByUser')->willReturn([
            new Account(10, $userId, 'Minha Conta', new \DateTimeImmutable('2026-09-14T00:00:00+00:00')),
        ]);
        $categories->method('findAllByUser')->willReturn([]);
        $commitments->method('findAllByUser')->willReturn([]);
        $entries->method('findAllByUser')->willReturn([]);
        $recurrences->method('findAllByUser')->willReturn([]);
        $preferences->method('get')->willReturn('light');
        $transactions->method('transactional')->willReturnCallback(fn (callable $action) => $action());
        $context->method('requireUserId')->willReturn($userId);

        $store = new class implements BackupStore {
            public function persist(int $userId, string $content, string $origin, int $expiresAt): array
            {
                $path = tempnam(sys_get_temp_dir(), 'sgfp-snapshot-');
                if ($path === false || file_put_contents($path, $content) === false) {
                    throw new \RuntimeException('Falha ao criar snapshot de teste.');
                }

                return [
                    'path' => $path,
                    'hash' => hash('sha256', $content),
                    'expires_at' => $expiresAt,
                    'origin' => $origin,
                ];
            }
        };

        return new CapturePreRestorationSnapshotService(
            new BackupPayloadBuilder(
                $accounts,
                $categories,
                $commitments,
                $entries,
                $recurrences,
                $preferences,
            ),
            $transactions,
            $context,
            $lock,
            $store,
        );
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
