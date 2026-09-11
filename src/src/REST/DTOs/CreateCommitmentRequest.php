<?php

declare(strict_types=1);

namespace SGFP\REST\DTOs;

final class CreateCommitmentRequest
{
    public function __construct(
        public readonly ?int $categoryId,
        public readonly string $name,
        public readonly float $amount,
        public readonly string $type,
        public readonly string $nature,
        public readonly string $referenceMonth,
    ) {
    }

    public static function fromRequest(\WP_REST_Request $request): self
    {
        return new self(
            isset($request['category_id']) ? (int) $request['category_id'] : null,
            (string) ($request['name'] ?? ''),
            (float) ($request['amount'] ?? 0),
            (string) ($request['type'] ?? ''),
            (string) ($request['nature'] ?? ''),
            (string) ($request['reference_month'] ?? ''),
        );
    }

    public function validate(): void
    {
        if (trim($this->name) === '') {
            throw new \InvalidArgumentException('O campo name é obrigatório.');
        }
    }
}
