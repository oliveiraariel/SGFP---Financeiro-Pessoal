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
            $account = $this->resetService->execute(
                (string) $request->get_param('token'),
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
            return \SGFP\REST\PublicError::response($e, 400, 'VALIDATION_ERROR');
        } catch (\RuntimeException $e) {
            return \SGFP\REST\PublicError::response($e, $e->getCode() ?: 500);
        } catch (\Throwable $e) {
            return \SGFP\REST\PublicError::response($e, 500);
        }
    }

    public function deleteAccount(\WP_REST_Request $request): \WP_REST_Response
    {
        try {
            $this->deleteAccountService->execute(
                (string) $request->get_param('token'),
                (string) $request->get_param('phrase')
            );

            return new \WP_REST_Response([
                'status' => 'account_deleted',
            ], 200);
        } catch (\InvalidArgumentException $e) {
            return \SGFP\REST\PublicError::response($e, 400, 'VALIDATION_ERROR');
        } catch (\RuntimeException $e) {
            return \SGFP\REST\PublicError::response($e, $e->getCode() ?: 500);
        } catch (\Throwable $e) {
            return \SGFP\REST\PublicError::response($e, 500);
        }
    }

    public function retryDeleteAccount(\WP_REST_Request $request): \WP_REST_Response
    {
        try {
            $this->deleteAccountService->executeRetry((string) $request->get_param('token'), (string) $request->get_param('phrase'));
            return new \WP_REST_Response(['status' => 'account_deleted'], 200);
        } catch (\InvalidArgumentException $e) {
            return \SGFP\REST\PublicError::response($e, 400, 'VALIDATION_ERROR');
        } catch (\RuntimeException $e) {
            return \SGFP\REST\PublicError::response($e, $e->getCode() ?: 500);
        } catch (\Throwable $e) {
            return \SGFP\REST\PublicError::response($e, 500);
        }
    }

    public function beginReset(): \WP_REST_Response { return new \WP_REST_Response(['token' => $this->resetService->begin()], 200); }
    public function beginDelete(): \WP_REST_Response { return new \WP_REST_Response(['token' => $this->deleteAccountService->begin()], 200); }
    public function beginDeleteRetry(): \WP_REST_Response { return new \WP_REST_Response(['token' => $this->deleteAccountService->beginRetry()], 200); }

    public function permissionCheck(): bool
    {
        return \SGFP\Infrastructure\WordPress\WpUserContext::canAccessSgfp();
    }

    public function recoveryPermissionCheck(): bool
    {
        return \SGFP\Infrastructure\WordPress\WpUserContext::canRecoverAccountDeletion();
    }
}
