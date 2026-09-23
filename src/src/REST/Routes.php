<?php

declare(strict_types=1);

namespace SGFP\REST;

use SGFP\Application\Services\CreateCategoryService;
use SGFP\Application\Services\CreateCommitmentService;
use SGFP\Application\Services\ListCommitmentsService;
use SGFP\Application\Services\DeleteCommitmentService;
use SGFP\Application\Services\CreateBackupService;
use SGFP\Application\Services\ValidateBackupService;
use SGFP\Application\Services\RevalidateRestorationService;
use SGFP\Application\Services\CapturePreRestorationSnapshotService;
use SGFP\Application\Services\GetDashboardService;
use SGFP\Application\Services\ListAccountsService;
use SGFP\Application\Services\ListCategoriesService;
use SGFP\Application\Services\ListMovementsService;
use SGFP\Application\Services\MaterializeRecurrenceOccurrenceService;
use SGFP\Application\Services\SettleRecurrenceOccurrenceService;
use SGFP\Application\Services\UndoRecurrenceOccurrenceSettlementService;
use SGFP\Application\Services\SeedCategoriesService;
use SGFP\Application\Services\SetInitialBalanceService;
use SGFP\Application\Services\SettleCommitmentService;
use SGFP\Application\Services\UndoCommitmentSettlementService;
use SGFP\Application\Services\ThemeService;
use SGFP\Application\Services\ProvisionUserService;
use SGFP\Application\Services\ResetProfileService;
use SGFP\Application\Services\DeleteAccountService;
use SGFP\Infrastructure\WordPress\WpAccountRepository;
use SGFP\Infrastructure\WordPress\WpCategoryRepository;
use SGFP\Infrastructure\WordPress\WpCommitmentRepository;
use SGFP\Infrastructure\WordPress\WpEntryRepository;
use SGFP\Infrastructure\WordPress\WpTransactionManager;
use SGFP\Infrastructure\WordPress\WpUserContext;
use SGFP\Infrastructure\WordPress\WpRecurrenceRepository;
use SGFP\Infrastructure\WordPress\WpUserPreferenceRepository;
use SGFP\Infrastructure\WordPress\WpUserOperationLock;
use SGFP\Infrastructure\WordPress\WpUserDataPurger;
use SGFP\Infrastructure\WordPress\WpUserIdentityDeleter;
use SGFP\Application\Backup\BackupArchive;
use SGFP\Application\Backup\BackupPayloadBuilder;
use SGFP\Application\Backup\BackupProtector;
use SGFP\Infrastructure\WordPress\WpBackupStore;
use SGFP\Infrastructure\WordPress\WpRestorationTokenClaim;
use SGFP\Infrastructure\WordPress\WpRestorationTokenStore;
use SGFP\Infrastructure\WordPress\WpRestorationPersistence;
use SGFP\Application\Services\RestoreFromImportPlanService;
use SGFP\REST\Controllers\AccountController;
use SGFP\REST\Controllers\CategoryController;
use SGFP\REST\Controllers\CommitmentController;
use SGFP\REST\Controllers\RecurrenceController;
use SGFP\REST\Controllers\ReportingController;
use SGFP\REST\Controllers\ThemeController;
use SGFP\REST\Controllers\BackupController;
use SGFP\REST\Controllers\RestoreController;
use SGFP\REST\Controllers\ProfileController;

final class Routes
{
    private const NAMESPACE = 'sgfp/v1';

    public function register(): void
    {
        $this->registerResponseContractFilter();
        $this->registerBinaryBackupResponseFilter();

        $validMonth = static function ($value): bool {
            $value = (string) $value;
            $date = \DateTimeImmutable::createFromFormat('!Y-m-d', $value);
            return $date !== false && $date->format('Y-m-d') === $value;
        };
        $userContext = new WpUserContext();
        $accountRepository = new WpAccountRepository();
        $categoryRepository = new WpCategoryRepository();
        $commitmentRepository = new WpCommitmentRepository();
        $entryRepository = new WpEntryRepository();
        $transactionManager = new WpTransactionManager();
        $seedCategories = new SeedCategoriesService($categoryRepository);

        $accountController = new AccountController(
            new ListAccountsService($accountRepository, $userContext),
            $entryRepository,
            new SetInitialBalanceService($accountRepository, $entryRepository, $transactionManager, $userContext),
            new \SGFP\Application\Services\RenameAccountService($accountRepository, $userContext)
        );

        $categoryController = new CategoryController(
            new CreateCategoryService($categoryRepository, $userContext),
            new ListCategoriesService($categoryRepository, $userContext)
            , new \SGFP\Application\Services\RenameCategoryService($categoryRepository, $userContext), new \SGFP\Application\Services\DeleteCategoryService($categoryRepository, $userContext)
        );

        $recurrenceRepository = new WpRecurrenceRepository();

        $commitmentController = new CommitmentController(
            new CreateCommitmentService($commitmentRepository, $categoryRepository, $recurrenceRepository, $userContext),
            new ListCommitmentsService($commitmentRepository, $userContext),
            new SettleCommitmentService($commitmentRepository, $entryRepository, $accountRepository, $transactionManager, $userContext),
            new UndoCommitmentSettlementService($commitmentRepository, $entryRepository, $transactionManager, $userContext)
            , new \SGFP\Application\Services\UpdateCommitmentService($commitmentRepository, $categoryRepository, $userContext)
            , new DeleteCommitmentService($commitmentRepository, $userContext)
        );


        $recurrenceController = new RecurrenceController(
            new MaterializeRecurrenceOccurrenceService($recurrenceRepository, $commitmentRepository, $transactionManager, $userContext),
            new SettleRecurrenceOccurrenceService(
                new MaterializeRecurrenceOccurrenceService($recurrenceRepository, $commitmentRepository, $transactionManager, $userContext),
                $commitmentRepository,
                $entryRepository,
                $accountRepository,
                $transactionManager,
                $userContext
            ),
            new UndoRecurrenceOccurrenceSettlementService(
                $commitmentRepository,
                $entryRepository,
                $transactionManager,
                $userContext
            )
        );

        $reportingController = new ReportingController(
            new ListMovementsService($entryRepository, $userContext),
            new GetDashboardService($accountRepository, $entryRepository, $commitmentRepository, $userContext)
        );

        $themeController = new ThemeController(
            new ThemeService(new WpUserPreferenceRepository(), $userContext)
        );

        $userDataPurger = new WpUserDataPurger();

        $profileController = new ProfileController(
            new ResetProfileService(
                $userDataPurger,
                new ProvisionUserService($accountRepository, $categoryRepository),
                $transactionManager,
                new WpUserOperationLock(),
                $userContext,
            ),
            new DeleteAccountService(
                $userDataPurger,
                new WpUserIdentityDeleter(),
                $transactionManager,
                new WpUserOperationLock(),
                $userContext,
            )
        );

        $preferenceRepository = new WpUserPreferenceRepository();
        $backupPayloadBuilder = new BackupPayloadBuilder(
            $accountRepository,
            $categoryRepository,
            $commitmentRepository,
            $entryRepository,
            $recurrenceRepository,
            $preferenceRepository,
        );

        $backupController = new BackupController(new CreateBackupService(
            $backupPayloadBuilder,
            $transactionManager,
            $userContext,
            new WpUserOperationLock(),
            new BackupProtector(),
            new BackupArchive(),
        ));

        $snapshotCapture = new CapturePreRestorationSnapshotService(
            $backupPayloadBuilder,
            $transactionManager,
            $userContext,
            new WpUserOperationLock(),
            new WpBackupStore(),
            new BackupProtector(),
            new BackupArchive(),
        );
        $restorer = new RestoreFromImportPlanService(new WpRestorationPersistence(), $transactionManager, new WpRestorationTokenClaim());
        $restoreController = new RestoreController(new ValidateBackupService(
            new WpRestorationTokenStore(),
            $userContext,
        ), new RevalidateRestorationService($preferenceRepository, $userContext, new WpUserOperationLock(), $snapshotCapture, new WpRestorationTokenClaim(), new WpRestorationTokenStore(), new \SGFP\Application\Backup\StagedBackupDecoder(), new \SGFP\Application\Backup\RestorationImportPlanner(), $restorer));

        register_rest_route(self::NAMESPACE, '/account', [
            'methods' => \WP_REST_Server::READABLE,
            'callback' => [$accountController, 'get'],
            'permission_callback' => [$accountController, 'permissionCheck'],
        ]);

        register_rest_route(self::NAMESPACE, '/account', [
            'methods' => \WP_REST_Server::EDITABLE, 'callback' => function (\WP_REST_Request $request) use ($accountController, $accountRepository, $userContext): \WP_REST_Response {
                $account = $accountRepository->findByUser($userContext->requireUserId());
                if ($account === null) return PublicError::fromCode(404, 'NOT_FOUND');
                $request['id'] = $account->id; return $accountController->rename($request);
            }, 'permission_callback' => [$accountController, 'permissionCheck'],
            'args' => ['name'=>['required'=>true,'type'=>'string','sanitize_callback'=>'sanitize_text_field']],
        ]);

        register_rest_route(self::NAMESPACE, '/account/initial-balance', [
            'methods' => \WP_REST_Server::CREATABLE,
            'callback' => function (\WP_REST_Request $request) use ($accountController, $accountRepository, $userContext): \WP_REST_Response {
                $account = $accountRepository->findByUser($userContext->requireUserId());
                if ($account === null) return PublicError::fromCode(404, 'NOT_FOUND');
                $request['id'] = $account->id;
                return $accountController->setInitialBalance($request);
            },
            'permission_callback' => [$accountController, 'permissionCheck'],
            'args' => [
                'amount' => ['required' => true, 'type' => 'string'],
                'name' => ['required' => false, 'type' => 'string', 'sanitize_callback' => 'sanitize_text_field'],
                'description' => ['required' => false, 'type' => 'string', 'sanitize_callback' => 'sanitize_textarea_field'],
                'effective_month' => ['required' => false, 'type' => 'string', 'pattern' => '^\\d{4}-\\d{2}-01$', 'validate_callback' => $validMonth],
            ],
        ]);

        register_rest_route(self::NAMESPACE, '/categories', [
            'methods' => \WP_REST_Server::CREATABLE,
            'callback' => [$categoryController, 'create'],
            'permission_callback' => [$categoryController, 'permissionCheck'],
            'args' => [
                'name' => [
                    'required' => true,
                    'type' => 'string',
                    'sanitize_callback' => 'sanitize_text_field',
                ],
            ],
        ]);

        register_rest_route(self::NAMESPACE, '/categories/(?P<id>\d+)', [
            'methods' => \WP_REST_Server::EDITABLE, 'callback' => [$categoryController, 'rename'], 'permission_callback' => [$categoryController, 'permissionCheck'],
            'args' => ['id'=>['required'=>true,'type'=>'integer'], 'name'=>['required'=>true,'type'=>'string','sanitize_callback'=>'sanitize_text_field']],
        ]);
        register_rest_route(self::NAMESPACE, '/categories/(?P<id>\d+)', [
            'methods' => \WP_REST_Server::DELETABLE, 'callback' => [$categoryController, 'delete'], 'permission_callback' => [$categoryController, 'permissionCheck'],
            'args' => ['id'=>['required'=>true,'type'=>'integer']],
        ]);

        register_rest_route(self::NAMESPACE, '/categories', [
            'methods' => \WP_REST_Server::READABLE,
            'callback' => [$categoryController, 'list'],
            'permission_callback' => [$categoryController, 'permissionCheck'],
        ]);

        register_rest_route(self::NAMESPACE, '/commitments', [
            'methods' => \WP_REST_Server::READABLE,
            'callback' => [$commitmentController, 'list'],
            'permission_callback' => [$commitmentController, 'permissionCheck'],
            'args' => [
                'month' => ['required' => false, 'type' => 'string', 'pattern' => '^\\d{4}-\\d{2}-01$', 'validate_callback' => $validMonth],
                'page' => ['required' => false, 'type' => 'integer', 'minimum' => 1],
                'per_page' => ['required' => false, 'type' => 'integer', 'minimum' => 1, 'maximum' => 100],
            ],
        ]);

        register_rest_route(self::NAMESPACE, '/commitments/(?P<id>\d+)', [
            'methods' => \WP_REST_Server::READABLE, 'callback' => [$commitmentController, 'show'], 'permission_callback' => [$commitmentController, 'permissionCheck'],
            'args' => ['id' => ['required' => true, 'type' => 'integer']],
        ]);
        register_rest_route(self::NAMESPACE, '/commitments/(?P<id>\d+)', [
            'methods' => \WP_REST_Server::EDITABLE, 'callback' => [$commitmentController, 'update'], 'permission_callback' => [$commitmentController, 'permissionCheck'],
            'args' => ['id' => ['required' => true, 'type' => 'integer'], 'name' => ['required' => true, 'type' => 'string'], 'amount' => ['required' => true, 'type' => 'string'], 'commitment_date' => [
                'required' => false,
                'type' => ['string', 'null'],
                'pattern' => '^\d{4}-(0[1-9]|1[0-2])-([0-2]\d|3[01])$',
            ], 'category_id' => [
                'required' => false,
                'type' => ['integer', 'null'],
                'sanitize_callback' => static fn ($value) => $value === null ? null : (int) $value,
            ]],
        ]);
        register_rest_route(self::NAMESPACE, '/commitments/(?P<id>\d+)', [
            'methods' => \WP_REST_Server::DELETABLE, 'callback' => [$commitmentController, 'delete'], 'permission_callback' => [$commitmentController, 'permissionCheck'],
            'args' => ['id' => ['required' => true, 'type' => 'integer']],
        ]);

        register_rest_route(self::NAMESPACE, '/commitments', [
            'methods' => \WP_REST_Server::CREATABLE,
            'callback' => [$commitmentController, 'create'],
            'permission_callback' => [$commitmentController, 'permissionCheck'],
            'args' => [
                'category_id' => [
                    'required' => false,
                    'type' => ['integer', 'null'],
                ],
                'name' => [
                    'required' => true,
                    'type' => 'string',
                    'sanitize_callback' => 'sanitize_text_field',
                ],
                'amount' => [
                    'required' => true,
                    'type' => 'string',
                ],
                'nature' => [
                    'required' => true,
                    'type' => 'string',
                    'enum' => ['ENTRADA', 'SAIDA'],
                ],
                'commitment_date' => [
                    'required' => true,
                    'type' => 'string',
                    'pattern' => '^\d{4}-(0[1-9]|1[0-2])-([0-2]\d|3[01])$',
                ],
                'recurrence_months_count' => [
                    'required' => false,
                    'type' => ['integer', 'null'],
                ],
                'recurrence_enabled' => [
                    'required' => false,
                    'type' => 'boolean',
                ],
            ],
        ]);

        register_rest_route(self::NAMESPACE, '/commitments/(?P<id>\d+)/effectuation', [
            'methods' => \WP_REST_Server::CREATABLE,
            'callback' => [$commitmentController, 'settle'],
            'permission_callback' => [$commitmentController, 'permissionCheck'],
            'args' => [
                'id' => ['required' => true, 'type' => 'integer'],
                'settled_at' => [
                    'required' => false,
                    'type' => 'string',
                    'validate_callback' => static function ($value): bool {
                        if ($value === null || $value === '') {
                            return true;
                        }
                        $value = (string) $value;
                        if (!preg_match('/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}(?:\.\d+)?(?:Z|[+-]\d{2}:\d{2})$/', $value)) {
                            return false;
                        }
                        try {
                            $date = new \DateTimeImmutable($value);
                        } catch (\Exception) {
                            return false;
                        }
                        $errors = \DateTimeImmutable::getLastErrors();
                        return $errors === false || ($errors['warning_count'] === 0 && $errors['error_count'] === 0);
                    },
                ],
            ],
        ]);

        register_rest_route(self::NAMESPACE, '/commitments/(?P<id>\d+)/undo-effectuation', [
            'methods' => \WP_REST_Server::CREATABLE,
            'callback' => [$commitmentController, 'undo'],
            'permission_callback' => [$commitmentController, 'permissionCheck'],
            'args' => [
                'id' => [
                    'required' => true,
                    'type' => 'integer',
                ],
            ],
        ]);


        register_rest_route(self::NAMESPACE, '/recurrences/(?P<id>\d+)/occurrences/(?P<month>\d{4}-\d{2}-01)', [
            'methods' => \WP_REST_Server::READABLE,
            'callback' => [$recurrenceController, 'showOccurrence'],
            'permission_callback' => [$recurrenceController, 'permissionCheck'],
            'args' => [
                'id' => [
                    'required' => true,
                    'type' => 'integer',
                ],
                'month' => [
                    'required' => true,
                    'type' => 'string',
                    'pattern' => '^\d{4}-\d{2}-01$',
                    'validate_callback' => $validMonth,
                ],
            ],
        ]);

        register_rest_route(self::NAMESPACE, '/recurrences/(?P<id>\d+)/occurrences/(?P<month>\d{4}-\d{2}-01)/effectuation', [
            'methods' => \WP_REST_Server::CREATABLE,
            'callback' => [$recurrenceController, 'settleOccurrence'],
            'permission_callback' => [$recurrenceController, 'permissionCheck'],
            'args' => [
                'id' => [
                    'required' => true,
                    'type' => 'integer',
                ],
                'month' => [
                    'required' => true,
                    'type' => 'string',
                    'pattern' => '^\d{4}-\d{2}-01$',
                    'validate_callback' => $validMonth,
                ],
            ],
        ]);

        register_rest_route(self::NAMESPACE, '/recurrences/(?P<id>\d+)/occurrences/(?P<month>\d{4}-\d{2}-01)/undo-effectuation', [
            'methods' => \WP_REST_Server::CREATABLE,
            'callback' => [$recurrenceController, 'undoOccurrence'],
            'permission_callback' => [$recurrenceController, 'permissionCheck'],
            'args' => [
                'id' => [
                    'required' => true,
                    'type' => 'integer',
                ],
                'month' => [
                    'required' => true,
                    'type' => 'string',
                    'pattern' => '^\d{4}-\d{2}-01$',
                    'validate_callback' => $validMonth,
                ],
            ],
        ]);

        register_rest_route(self::NAMESPACE, '/movements', [
            'methods' => \WP_REST_Server::READABLE,
            'callback' => [$reportingController, 'movements'],
            'permission_callback' => [$reportingController, 'permissionCheck'],
            'args' => [
                'month' => [
                    'required' => false,
                    'type' => 'string',
                    'pattern' => '^\d{4}-\d{2}-01$',
                    'validate_callback' => $validMonth,
                ],
            ],
        ]);


        register_rest_route(self::NAMESPACE, '/dashboard', [
            'methods' => \WP_REST_Server::READABLE,
            'callback' => [$reportingController, 'dashboard'],
            'permission_callback' => [$reportingController, 'permissionCheck'],
            'args' => [
                'month' => [
                    'required' => true,
                    'type' => 'string',
                    'pattern' => '^\d{4}-\d{2}-01$',
                    'validate_callback' => $validMonth,
                ],
            ],
        ]);

        register_rest_route(self::NAMESPACE, '/preferences/theme', [
            'methods' => \WP_REST_Server::READABLE,
            'callback' => [$themeController, 'get'],
            'permission_callback' => [$themeController, 'permissionCheck'],
        ]);

        register_rest_route(self::NAMESPACE, '/backups', [
            'methods' => \WP_REST_Server::CREATABLE,
            'callback' => [$backupController, 'create'],
            'permission_callback' => [$backupController, 'permissionCheck'],
        ]);

        register_rest_route(self::NAMESPACE, '/restore-validations', [
            'methods' => \WP_REST_Server::CREATABLE,
            'callback' => [$restoreController, 'validate'],
            'permission_callback' => [$restoreController, 'permissionCheck'],
        ]);

        register_rest_route(self::NAMESPACE, '/restorations', [
            'methods' => \WP_REST_Server::CREATABLE,
            'callback' => [$restoreController, 'prepare'],
            'permission_callback' => [$restoreController, 'permissionCheck'],
            'args' => [
                'token' => ['required' => true, 'type' => 'string'],
                'confirmation' => ['required' => true, 'type' => 'boolean'],
            ],
        ]);

        register_rest_route(self::NAMESPACE, '/profile-reset/validation', [
            'methods' => \WP_REST_Server::CREATABLE, 'callback' => [$profileController, 'beginReset'], 'permission_callback' => [$profileController, 'permissionCheck'],
        ]);
        register_rest_route(self::NAMESPACE, '/profile-reset', [
            'methods' => \WP_REST_Server::CREATABLE,
            'callback' => [$profileController, 'reset'],
            'permission_callback' => [$profileController, 'permissionCheck'],
            'args' => [
                'token' => ['required' => true, 'type' => 'string'],
                'phrase' => ['required' => true, 'type' => 'string'],
            ],
        ]);

        register_rest_route(self::NAMESPACE, '/account-access/validation', [
            'methods' => \WP_REST_Server::CREATABLE, 'callback' => [$profileController, 'beginDelete'], 'permission_callback' => [$profileController, 'permissionCheck'],
        ]);
        register_rest_route(self::NAMESPACE, '/account-access', [
            'methods' => \WP_REST_Server::DELETABLE,
            'callback' => [$profileController, 'deleteAccount'],
            'permission_callback' => [$profileController, 'permissionCheck'],
            'args' => [
                'token' => ['required' => true, 'type' => 'string'],
                'phrase' => ['required' => true, 'type' => 'string'],
            ],
        ]);
        register_rest_route(self::NAMESPACE, '/account-access/retry/validation', [
            'methods' => \WP_REST_Server::CREATABLE, 'callback' => [$profileController, 'beginDeleteRetry'], 'permission_callback' => [$profileController, 'recoveryPermissionCheck'],
        ]);
        register_rest_route(self::NAMESPACE, '/account-access/retry', [
            'methods' => \WP_REST_Server::DELETABLE, 'callback' => [$profileController, 'retryDeleteAccount'], 'permission_callback' => [$profileController, 'recoveryPermissionCheck'],
            'args' => ['token' => ['required' => true, 'type' => 'string'], 'phrase' => ['required' => true, 'type' => 'string']],
        ]);

        register_rest_route(self::NAMESPACE, '/preferences/theme', [
            'methods' => \WP_REST_Server::EDITABLE,
            'callback' => [$themeController, 'update'],
            'permission_callback' => [$themeController, 'permissionCheck'],
            'args' => [
                'theme' => [
                    'required' => true,
                    'type' => 'string',
                    'enum' => ['light', 'dark'],
                ],
            ],
        ]);
    }

    /**
     * Keeps resource payloads backward-compatible while making the REST edge
     * observable and errors machine-readable for the web client.
     */
    private function registerResponseContractFilter(): void
    {
        static $registered = false;
        if ($registered) {
            return;
        }
        $registered = true;

        add_filter('rest_post_dispatch', static function ($response, $server, $request) {
            if (!str_starts_with($request->get_route(), '/sgfp/v1/')) {
                return $response;
            }

            $correlationId = (string) $request->get_header('X-Correlation-ID');
            if (!preg_match('/^[A-Za-z0-9._:-]{1,128}$/', $correlationId)) {
                try {
                    $correlationId = bin2hex(random_bytes(16));
                } catch (\Throwable) {
                    $correlationId = uniqid('sgfp-', true);
                }
            }

            // Argument validation can return WP_Error before the controller is
            // invoked. Normalize it at the same REST boundary as controller
            // errors so clients always receive the public contract.
            if ($response instanceof \WP_Error) {
                $errorCode = $response->get_error_code();
                $errorData = $response->get_error_data($errorCode);
                $status = is_array($errorData) && isset($errorData['status'])
                    ? (int) $errorData['status']
                    : 400;
                $details = is_array($errorData) ? ($errorData['details'] ?? null) : null;
                $normalized = PublicError::contract([
                    'code' => (string) ($errorCode ?: 'REQUEST_ERROR'),
                    'message' => $response->get_error_message($errorCode),
                    'details' => $details,
                ], $status, $correlationId);
                $response = new \WP_REST_Response(['error' => $normalized], $status);
            }

            if (!$response instanceof \WP_REST_Response) {
                return $response;
            }
            $response->header('X-Correlation-ID', $correlationId);

            if ($response->get_status() >= 400) {
                $data = $response->get_data();
                $error = is_array($data) ? ($data['error'] ?? null) : null;
                $status = $response->get_status();
                if (is_array($data)) {
                    $data['error'] = PublicError::contract($error, $status, $correlationId);
                    $response->set_data($data);
                }
            }

            return $response;
        }, 10, 3);
    }

    /**
     * WordPress REST serializes ordinary response data as JSON. A ZIP cannot
     * travel through that serializer without changing its bytes, so only the
     * successful backup route is manually served at the final REST boundary.
     * Errors deliberately stay in the regular JSON public-error contract.
     */
    private function registerBinaryBackupResponseFilter(): void
    {
        static $registered = false;
        if ($registered) {
            return;
        }
        $registered = true;

        add_filter('rest_pre_serve_request', static function (
            bool $served,
            \WP_REST_Response $response,
            \WP_REST_Request $request,
            \WP_REST_Server $server,
        ): bool {
            if ($served
                || $request->get_method() !== 'POST'
                || $request->get_route() !== '/' . self::NAMESPACE . '/backups'
                || $response->get_status() < 200
                || $response->get_status() >= 300) {
                return $served;
            }

            $content = $response->get_data();
            if (!is_string($content) || $content === '') {
                return false;
            }

            // serve_request() already sent the response status and headers
            // (including application/zip and Content-Disposition) before
            // this hook. Echoing here bypasses only its JSON encoder.
            echo $content;
            return true;
        }, 10, 4);
    }
}
