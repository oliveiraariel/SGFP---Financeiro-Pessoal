<?php

declare(strict_types=1);

namespace SGFP\Tests\Unit;

use PHPUnit\Framework\TestCase;
use SGFP\Application\Backup\BackupArchive;

final class BackupArchiveTest extends TestCase
{
    public function testPacksAByteForByteValidZipWithOnlyTheCanonicalEntries(): void
    {
        $archive = new BackupArchive();
        $content = $archive->pack('protected-payload');

        self::assertNotSame('', $content);
        self::assertStringStartsWith('PK', $content);
        self::assertSame([
            'manifest' => [
                'schema' => 'sgfp-backup-zip',
                'version' => 2,
                'payload' => 'payload.bin',
            ],
            'payload' => 'protected-payload',
        ], $archive->unpack($content));
    }

    public function testRejectsZipWithUnexpectedEntries(): void
    {
        $path = tempnam(sys_get_temp_dir(), 'sgfp-test-');
        self::assertNotFalse($path);

        $zip = new \ZipArchive();
        self::assertTrue($zip->open($path, \ZipArchive::OVERWRITE));
        $zip->addFromString('manifest.json', json_encode([
            'schema' => 'sgfp-backup-zip',
            'version' => 2,
            'payload' => 'payload.bin',
        ], JSON_THROW_ON_ERROR));
        $zip->addFromString('payload.bin', 'protected');
        $zip->addFromString('unexpected.txt', 'must not be accepted');
        self::assertTrue($zip->close());

        try {
            $this->expectException(\InvalidArgumentException::class);
            (new BackupArchive())->unpack((string) file_get_contents($path));
        } finally {
            @unlink($path);
        }
    }
}
