<?php

declare(strict_types=1);

namespace SGFP\REST\DTOs;

use SGFP\Domain\Models\Decimal;

final class CreateCommitmentRequest
{
    public function __construct(
        public readonly ?int $categoryId,
        public readonly string $name,
        public readonly string $amount,
        public readonly string $nature,
        public readonly string $commitmentDate,
        public readonly ?int $recurrenceMonthsCount,
        public readonly bool $recurrenceEnabled = false,
    ) {}

    public static function fromRequest(\WP_REST_Request $request): self
    {
        return new self(
            isset($request['category_id']) ? (int) $request['category_id'] : null,
            (string) ($request['name'] ?? ''),
            (string) ($request['amount'] ?? ''),
            (string) ($request['nature'] ?? ''),
            (string) ($request['commitment_date'] ?? $request['reference_month'] ?? ''),
            isset($request['recurrence_months_count']) ? (int) $request['recurrence_months_count'] : null,
            (bool) ($request['recurrence_enabled'] ?? false),
        );
    }

    public function validate(): void
    {
        if (trim($this->name) === '') {
            throw new \InvalidArgumentException('O campo name é obrigatório.');
        }
        $normalized = Decimal::normalize($this->amount);
        if (Decimal::cents($normalized) <= 0) throw new \InvalidArgumentException('O campo amount deve ser um decimal positivo com duas casas.');
        if (preg_match('/^\d{4}-(0[1-9]|1[0-2])-\d{2}$/', $this->commitmentDate) !== 1) {
            throw new \InvalidArgumentException('A data do compromisso deve estar no formato YYYY-MM-DD.');
        }
        $date = \DateTimeImmutable::createFromFormat('!Y-m-d', $this->commitmentDate);
        $errors = \DateTimeImmutable::getLastErrors();
        if ($date === false || ($errors !== false && ($errors['warning_count'] > 0 || $errors['error_count'] > 0))) {
            throw new \InvalidArgumentException('A data do compromisso é inválida.');
        }

        if ($this->recurrenceEnabled && $this->recurrenceMonthsCount !== null && $this->recurrenceMonthsCount <= 0) {
            throw new \InvalidArgumentException('recurrence_months_count deve ser maior que zero.');
        }
    }

    public function normalizedAmount(): string { return Decimal::normalize($this->amount); }
}
