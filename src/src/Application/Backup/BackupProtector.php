<?php

declare(strict_types=1);

namespace SGFP\Application\Backup;

final class BackupProtector
{
    private const ASSOCIATED_DATA = 'sgfp-backup-v2';

    public function protect(string $plain): string
    {
        $key = $this->key();
        $nonce = random_bytes(SODIUM_CRYPTO_AEAD_XCHACHA20POLY1305_IETF_NPUBBYTES);
        $ciphertext = sodium_crypto_aead_xchacha20poly1305_ietf_encrypt(
            $plain,
            self::ASSOCIATED_DATA,
            $nonce,
            $key
        );

        return $nonce . $ciphertext;
    }

    public function unprotect(string $protected): string
    {
        $nonceSize = SODIUM_CRYPTO_AEAD_XCHACHA20POLY1305_IETF_NPUBBYTES;

        if (strlen($protected) <= $nonceSize) {
            throw new \InvalidArgumentException('Conteúdo protegido do backup é inválido.');
        }

        $plain = sodium_crypto_aead_xchacha20poly1305_ietf_decrypt(
            substr($protected, $nonceSize),
            self::ASSOCIATED_DATA,
            substr($protected, 0, $nonceSize),
            $this->key()
        );

        if ($plain === false) {
            throw new \InvalidArgumentException('Não foi possível autenticar o backup.');
        }

        return $plain;
    }

    private function key(): string
    {
        $value = getenv('SGFP_BACKUP_KEY') ?: '';
        if ($value === '') {
            throw new \RuntimeException('A chave de proteção do backup não está configurada.');
        }

        return hash('sha256', $value, true);
    }
}
