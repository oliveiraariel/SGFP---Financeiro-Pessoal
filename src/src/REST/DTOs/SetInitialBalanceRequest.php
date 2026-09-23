<?php

declare(strict_types=1);

namespace SGFP\REST\DTOs;

use SGFP\Domain\Models\Decimal;

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

        try {
            Decimal::normalize($this->amount, true);
        } catch (\InvalidArgumentException) {
            throw new \InvalidArgumentException('O campo amount deve ser um decimal válido com duas casas.');
        }

        if ($this->effectiveMonth !== null) {
            $date = \DateTimeImmutable::createFromFormat('!Y-m-d', $this->effectiveMonth);
            if ($date === false || $date->format('Y-m-d') !== $this->effectiveMonth || !preg_match('/^\d{4}-\d{2}-01$/', $this->effectiveMonth)) {
                throw new \InvalidArgumentException('O campo effective_month deve ser uma data válida no formato YYYY-MM-01.');
            }
        }
    }

    public function toDecimal(): string
    {
        return Decimal::normalize($this->amount ?? '', true);
    }

    public function toEffectiveMonth(): ?\DateTimeImmutable
    {
        if ($this->effectiveMonth === null) {
            return null;
        }

        return new \DateTimeImmutable($this->effectiveMonth);
    }
}
