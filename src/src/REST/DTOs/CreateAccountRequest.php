<?php

declare(strict_types=1);

namespace SGFP\REST\DTOs;

final class CreateAccountRequest
{
    public function __construct(
        public readonly string $name,
        public readonly ?string $role,
    ) {
    }

    public static function fromRequest(\WP_REST_Request $request): self
    {
        return new self(
            (string) ($request['name'] ?? ''),
            isset($request['role']) ? (string) $request['role'] : null,
        );
    }

    public function validate(): void
    {
        if (trim($this->name) === '') {
            throw new \InvalidArgumentException('O campo name é obrigatório.');
        }
    }
}
