<?php

declare(strict_types=1);

namespace SGFP\Application\Services;

use SGFP\Application\Ports\AccountRepository;
use SGFP\Application\Ports\CategoryRepository;
use SGFP\Application\Ports\CommitmentRepository;
use SGFP\Application\Ports\EntryRepository;
use SGFP\Application\Ports\RecurrenceRepository;
use SGFP\Application\Ports\TransferRepository;
use SGFP\Application\Ports\TransactionManager;
use SGFP\Application\Ports\UserContext;
use SGFP\Application\Ports\UserPreferenceRepository;

final class CreateBackupService
{
    public function __construct(
        private readonly AccountRepository $accounts,
        private readonly CategoryRepository $categories,
        private readonly CommitmentRepository $commitments,
        private readonly EntryRepository $entries,
        private readonly RecurrenceRepository $recurrences,
        private readonly TransferRepository $transfers,
        private readonly UserPreferenceRepository $preferences,
        private readonly TransactionManager $transactions,
        private readonly UserContext $userContext,
    ) {}

    /** @return array{filename:string,content:string,content_type:string} */
    public function create(): array
    {
        $this->userContext->requireCapability('use_sgfp');
        $userId = $this->userContext->requireUserId();

        $payload = $this->transactions->transactional(function () use ($userId): array {
            return [
                'version' => 1,
                'origin' => 'manual',
                'user_id' => $userId,
                'created_at' => gmdate('c'),
                'theme' => $this->preferences->get('theme', $userId) ?: 'light',
                'accounts' => $this->accounts->findAllByUser($userId),
                'categories' => $this->categories->findAllByUser($userId),
                'recurrences' => $this->recurrences->findAllByUser($userId),
                'commitments' => $this->commitments->findAllByUser($userId),
                'transfers' => $this->transfers->findAllByUser($userId),
                'entries' => $this->entries->findAllByUser($userId),
            ];
        });

        $json = json_encode($this->normalize($payload), JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE);
        $compressed = gzencode($json, 9, ZLIB_ENCODING_GZIP);
        if ($compressed === false) {
            throw new \RuntimeException('Não foi possível compactar o backup.');
        }

        $key = getenv('SGFP_BACKUP_KEY') ?: '';
        if ($key === '') {
            throw new \RuntimeException('A chave de proteção do backup não está configurada.');
        }
        $key = hash('sha256', $key, true);
        $nonce = random_bytes(SODIUM_CRYPTO_AEAD_XCHACHA20POLY1305_IETF_NPUBBYTES);
        $ciphertext = sodium_crypto_aead_xchacha20poly1305_ietf_encrypt($compressed, '', $nonce, $key);

        return [
            'filename' => 'sgfp-backup-' . gmdate('Ymd-His') . '.sgfp',
            'content_type' => 'application/octet-stream',
            'content' => base64_encode($nonce . $ciphertext),
        ];
    }

    private function normalize(mixed $value): mixed
    {
        if ($value instanceof \DateTimeInterface) return $value->format('c');
        if ($value instanceof \BackedEnum) return $value->value;
        if (is_array($value)) return array_map(fn (mixed $v): mixed => $this->normalize($v), $value);
        if (is_object($value)) return $this->normalize(get_object_vars($value));
        return $value;
    }
}
