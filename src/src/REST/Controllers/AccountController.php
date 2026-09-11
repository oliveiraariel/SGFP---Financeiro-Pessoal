<?php

declare(strict_types=1);

namespace SGFP\REST\Controllers;

use SGFP\Application\Services\CreateAccountService;
use SGFP\REST\DTOs\CreateAccountRequest;

final class AccountController
{
    public function __construct(
        private readonly CreateAccountService $createService,
    ) {
    }

    public function create(\WP_REST_Request $request): \WP_REST_Response
    {
        try {
            $dto = CreateAccountRequest::fromRequest($request);
            $dto->validate();

            $account = $this->createService->execute($dto->name, $dto->role);

            return new \WP_REST_Response([
                'id' => $account->id,
                'name' => $account->name,
                'role' => $account->role->value,
                'created_at' => $account->createdAt->format('c'),
            ], 201);
        } catch (\InvalidArgumentException $e) {
            return new \WP_REST_Response(['error' => $e->getMessage()], 400);
        } catch (\RuntimeException $e) {
            return new \WP_REST_Response(['error' => $e->getMessage()], $e->getCode() ?: 500);
        } catch (\Throwable $e) {
            return new \WP_REST_Response(['error' => 'Erro interno.'], 500);
        }
    }

    public function permissionCheck(): bool
    {
        return current_user_can('use_sgfp');
    }
}
