<?php

declare(strict_types=1);

namespace SGFP\REST\DTOs;

use SGFP\Domain\Models\Decimal;

final class UpdateCommitmentRequest
{
    public function __construct(public readonly ?int $categoryId, public readonly string $name, public readonly string $amount, public readonly ?string $commitmentDate = null) {}

    public static function fromRequest(\WP_REST_Request $request): self
    {
        return new self(isset($request['category_id']) ? (int) $request['category_id'] : null, (string) ($request['name'] ?? ''), (string) ($request['amount'] ?? ''), isset($request['commitment_date']) ? (string) $request['commitment_date'] : null);
    }

    public function validate(): void
    {
        if (trim($this->name) === '') throw new \InvalidArgumentException('O nome é obrigatório.');
        if (function_exists('mb_strlen') ? mb_strlen(trim($this->name), 'UTF-8') > 180 : strlen(trim($this->name)) > 180) throw new \InvalidArgumentException('O nome deve ter no máximo 180 caracteres.');
        if (Decimal::cents(Decimal::normalize($this->amount)) <= 0) throw new \InvalidArgumentException('O valor deve usar duas casas decimais e ser maior que zero.');
        if ($this->commitmentDate !== null) {
            $date = \DateTimeImmutable::createFromFormat('!Y-m-d', $this->commitmentDate);
            $errors = \DateTimeImmutable::getLastErrors();
            if (preg_match('/^\d{4}-(0[1-9]|1[0-2])-\d{2}$/', $this->commitmentDate) !== 1
                || $date === false
                || ($errors !== false && ($errors['warning_count'] > 0 || $errors['error_count'] > 0))
                || $date->format('Y-m-d') !== $this->commitmentDate) {
                throw new \InvalidArgumentException('A data do compromisso é inválida.');
            }
        }
    }

    public function normalizedAmount(): string { return Decimal::normalize($this->amount); }
}
