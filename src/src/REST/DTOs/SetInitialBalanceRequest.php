<?php

declare(strict_types=1);

namespace SGFP\REST\DTOs;

final class SetInitialBalanceRequest
{
    public function __construct(
        public readonly ?string $amount,
        public readonly ?string $name,
        public readonly ?string $description,
        public readonly ?string $effectiveMonth,
    ) {
    }

    public static function fromRequest(\WP_REST_Request $request): self
    {
        return new self(
            isset($request['amount']) ? (string) $request['amount'] : null,
            isset($request['name']) ? (string) $request['name'] : null,
            isset($request['description']) ? (string) $request['description'] : null,
            isset($request['effective_month']) ? (string) $request['effective_month'] : null,
        );
    }

    public function validate(): void
    {
        if ($this->amount === null || trim($this->amount) === '') {
            throw new \InvalidArgumentException('O campo amount é obrigatório.');
        }

        if (!is_numeric($this->amount)) {
            throw new \InvalidArgumentException('O campo amount deve ser um valor numérico.');
        }

        if ($this->effectiveMonth !== null && !preg_match('/^\d{4}-\d{2}$/', $this->effectiveMonth)) {
            throw new \InvalidArgumentException('O campo effective_month deve estar no formato YYYY-MM.');
        }
    }

    public function toFloat(): float
    {
        return (float) $this->amount;
    }

    public function toEffectiveMonth(): ?\DateTimeImmutable
    {
        if ($this->effectiveMonth === null) {
            return null;
        }

        return new \DateTimeImmutable($this->effectiveMonth . '-01');
    }
}
