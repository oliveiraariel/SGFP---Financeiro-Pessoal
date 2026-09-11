<?php

declare(strict_types=1);

namespace SGFP\REST\DTOs;

final class CreateTransferRequest
{
    public function __construct(
        public readonly string $name,
        public readonly ?string $amount,
        public readonly ?string $referenceMonth,
        public readonly ?int $sourceAccountId,
        public readonly ?int $targetAccountId,
    ) {
    }

    public static function fromRequest(\WP_REST_Request $request): self
    {
        return new self(
            (string) ($request['name'] ?? ''),
            isset($request['amount']) ? (string) $request['amount'] : null,
            isset($request['reference_month']) ? (string) $request['reference_month'] : null,
            isset($request['source_account_id']) ? (int) $request['source_account_id'] : null,
            isset($request['target_account_id']) ? (int) $request['target_account_id'] : null,
        );
    }

    public function validate(): void
    {
        if (trim($this->name) === '') {
            throw new \InvalidArgumentException('O campo name é obrigatório.');
        }

        if ($this->amount === null || trim($this->amount) === '') {
            throw new \InvalidArgumentException('O campo amount é obrigatório.');
        }

        if (!is_numeric($this->amount)) {
            throw new \InvalidArgumentException('O campo amount deve ser um valor numérico.');
        }

        if ($this->referenceMonth === null || !preg_match('/^\d{4}-\d{2}$/', $this->referenceMonth)) {
            throw new \InvalidArgumentException('O campo reference_month deve estar no formato YYYY-MM.');
        }

        if ($this->sourceAccountId === null) {
            throw new \InvalidArgumentException('O campo source_account_id é obrigatório.');
        }

        if ($this->targetAccountId === null) {
            throw new \InvalidArgumentException('O campo target_account_id é obrigatório.');
        }
    }

    public function toFloat(): float
    {
        return (float) $this->amount;
    }
}
