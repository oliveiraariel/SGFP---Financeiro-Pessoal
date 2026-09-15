<?php

declare(strict_types=1);

namespace SGFP\REST\Controllers;

use SGFP\Application\Services\DeleteAccountService;
use SGFP\Application\Services\ResetProfileService;

final class ProfileController
{
    public function __construct(
        private readonly ResetProfileService $resetService,
        private readonly DeleteAccountService $deleteAccountService,
    ) {}

    public function reset(\WP_REST_Request $request): \WP_REST_Response
    {
        try {
            $confirmation = $request->get_param('confirmation');
            if (!is_bool($confirmation)) {
                throw new \InvalidArgumentException('A confirmação deve ser booleana.');
            }

            $account = $this->resetService->execute(
                $confirmation,
                (string) $request->get_param('phrase')
            );

            return new \WP_REST_Response([
                'status' => 'profile_reset',
                'account' => [
                    'id' => $account->id,
                    'name' => $account->name,
                ],
            ], 200);
        } catch (\InvalidArgumentException $e) {
            return new \WP_REST_Response(['error' => $e->getMessage()], 400);
        } catch (\RuntimeException $e) {
            return new \WP_REST_Response(['error' => $e->getMessage()], $e->getCode() ?: 500);
        } catch (\Throwable) {
            return new \WP_REST_Response(['error' => 'Erro interno.'], 500);
        }
    }

    public function deleteAccount(\WP_REST_Request $request): \WP_REST_Response
    {
        try {
            $confirmation = $request->get_param('confirmation');
            if (!is_bool($confirmation)) {
                throw new \InvalidArgumentException('A confirmação deve ser booleana.');
            }

            $this->deleteAccountService->execute(
                $confirmation,
                (string) $request->get_param('phrase')
            );

            return new \WP_REST_Response([
                'status' => 'account_deleted',
            ], 200);
        } catch (\InvalidArgumentException $e) {
            return new \WP_REST_Response(['error' => $e->getMessage()], 400);
        } catch (\RuntimeException $e) {
            return new \WP_REST_Response(['error' => $e->getMessage()], $e->getCode() ?: 500);
        } catch (\Throwable) {
            return new \WP_REST_Response(['error' => 'Erro interno.'], 500);
        }
    }

    public function permissionCheck(): bool
    {
        return current_user_can('use_sgfp');
    }
}
