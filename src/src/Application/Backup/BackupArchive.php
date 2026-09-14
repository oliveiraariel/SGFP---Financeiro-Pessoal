<?php

declare(strict_types=1);

namespace SGFP\Application\Backup;

final class BackupArchive
{
    private const MANIFEST_NAME = 'manifest.json';
    private const PAYLOAD_NAME = 'payload.bin';

    public function pack(string $encryptedPayload): string
    {
        if (!class_exists(\ZipArchive::class)) {
            throw new \RuntimeException('A extensão ZIP do PHP não está disponível.');
        }

        $temporary = tempnam(sys_get_temp_dir(), 'sgfp-zip-');
        if ($temporary === false) {
            throw new \RuntimeException('Não foi possível preparar o arquivo ZIP.');
        }

        $zip = new \ZipArchive();
        $isOpen = false;

        try {
            $opened = $zip->open($temporary, \ZipArchive::OVERWRITE);
            if ($opened !== true) {
                throw new \RuntimeException('Não foi possível criar o arquivo ZIP.');
            }
            $isOpen = true;

            $manifest = json_encode([
                'schema' => 'sgfp-backup-zip',
                'version' => 2,
                'payload' => self::PAYLOAD_NAME,
            ], JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE);

            if (!$zip->addFromString(self::MANIFEST_NAME, $manifest)
                || !$zip->addFromString(self::PAYLOAD_NAME, $encryptedPayload)) {
                throw new \RuntimeException('Não foi possível montar o arquivo ZIP.');
            }

            if (!$zip->close()) {
                throw new \RuntimeException('Não foi possível finalizar o arquivo ZIP.');
            }
            $isOpen = false;

            $content = file_get_contents($temporary);
            if ($content === false || $content === '') {
                throw new \RuntimeException('Não foi possível ler o arquivo ZIP gerado.');
            }

            return $content;
        } finally {
            if ($isOpen) {
                try {
                    $zip->close();
                } catch (\ValueError) {
                    // O objeto já pode ter sido invalidado pelo driver ZIP.
                }
            }
            @unlink($temporary);
        }
    }

    /** @return array{manifest:array<string,mixed>,payload:string} */
    public function unpack(string $zipContent): array
    {
        if (!class_exists(\ZipArchive::class)) {
            throw new \RuntimeException('A extensão ZIP do PHP não está disponível.');
        }

        $temporary = tempnam(sys_get_temp_dir(), 'sgfp-zip-');
        if ($temporary === false || file_put_contents($temporary, $zipContent, LOCK_EX) !== strlen($zipContent)) {
            if ($temporary !== false) {
                @unlink($temporary);
            }
            throw new \InvalidArgumentException('Arquivo ZIP inválido.');
        }

        $zip = new \ZipArchive();
        $isOpen = false;

        try {
            if ($zip->open($temporary) !== true) {
                throw new \InvalidArgumentException('Arquivo ZIP inválido.');
            }
            $isOpen = true;

            $manifestRaw = $zip->getFromName(self::MANIFEST_NAME);
            $payload = $zip->getFromName(self::PAYLOAD_NAME);

            $manifest = is_string($manifestRaw)
                ? json_decode($manifestRaw, true)
                : null;

            if (!is_array($manifest)
                || ($manifest['schema'] ?? null) !== 'sgfp-backup-zip'
                || ($manifest['version'] ?? null) !== 2
                || ($manifest['payload'] ?? null) !== self::PAYLOAD_NAME
                || !is_string($payload)
                || $payload === '') {
                throw new \InvalidArgumentException('Estrutura do backup ZIP inválida.');
            }

            return ['manifest' => $manifest, 'payload' => $payload];
        } finally {
            if ($isOpen) {
                try {
                    $zip->close();
                } catch (\ValueError) {
                    // O objeto já pode ter sido invalidado pelo driver ZIP.
                }
            }
            @unlink($temporary);
        }
    }
}
