<?php

declare(strict_types=1);

namespace SGFP\REST;

use SGFP\Application\Services\CreateCategoryService;
use SGFP\Application\Services\CreateCommitmentService;
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
        $userContext = new WpUserContext();
        $accountRepository = new WpAccountRepository();
        $categoryRepository = new WpCategoryRepository();
        $commitmentRepository = new WpCommitmentRepository();
        $entryRepository = new WpEntryRepository();
        $transactionManager = new WpTransactionManager();
        $seedCategories = new SeedCategoriesService($categoryRepository);

        $accountController = new AccountController(
            new ListAccountsService($accountRepository, $userContext),
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
            new SettleCommitmentService($commitmentRepository, $entryRepository, $accountRepository, $transactionManager, $userContext),
            new UndoCommitmentSettlementService($commitmentRepository, $entryRepository, $transactionManager, $userContext)
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
                new MaterializeRecurrenceOccurrenceService($recurrenceRepository, $commitmentRepository, $transactionManager, $userContext),
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

        register_rest_route(self::NAMESPACE, '/accounts', [
            'methods' => \WP_REST_Server::READABLE,
            'callback' => [$accountController, 'list'],
            'permission_callback' => [$accountController, 'permissionCheck'],
        ]);

        register_rest_route(self::NAMESPACE, '/accounts/(?P<id>\d+)/initial-balance', [
            'methods' => \WP_REST_Server::CREATABLE,
            'callback' => [$accountController, 'setInitialBalance'],
            'permission_callback' => [$accountController, 'permissionCheck'],
            'args' => [
                'id' => [
                    'required' => true,
                    'type' => 'integer',
                ],
                'amount' => [
                    'required' => true,
                    'type' => 'string',
                ],
                'name' => [
                    'required' => false,
                    'type' => 'string',
                    'sanitize_callback' => 'sanitize_text_field',
                ],
                'description' => [
                    'required' => false,
                    'type' => 'string',
                    'sanitize_callback' => 'sanitize_textarea_field',
                ],
                'effective_month' => [
                    'required' => false,
                    'type' => 'string',
                    'pattern' => '^\d{4}-\d{2}$',
                ],
            ],
        ]);

        register_rest_route(self::NAMESPACE, '/accounts/(?P<id>\d+)', [
            'methods' => \WP_REST_Server::EDITABLE, 'callback' => [$accountController, 'rename'], 'permission_callback' => [$accountController, 'permissionCheck'],
            'args' => ['id' => ['required'=>true,'type'=>'integer'], 'name'=>['required'=>true,'type'=>'string','sanitize_callback'=>'sanitize_text_field']],
        ]);

        register_rest_route(self::NAMESPACE, '/account', [
            'methods' => \WP_REST_Server::EDITABLE, 'callback' => function (\WP_REST_Request $request) use ($accountController, $accountRepository, $userContext): \WP_REST_Response {
                $account = $accountRepository->findByUser($userContext->requireUserId());
                if ($account === null) return new \WP_REST_Response(['error'=>'Conta não encontrada.'],404);
                $request['id'] = $account->id; return $accountController->rename($request);
            }, 'permission_callback' => [$accountController, 'permissionCheck'],
            'args' => ['name'=>['required'=>true,'type'=>'string','sanitize_callback'=>'sanitize_text_field']],
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
            'methods' => \WP_REST_Server::CREATABLE,
            'callback' => [$commitmentController, 'create'],
            'permission_callback' => [$commitmentController, 'permissionCheck'],
            'args' => [
                'category_id' => [
                    'required' => false,
                    'type' => 'integer',
                ],
                'name' => [
                    'required' => true,
                    'type' => 'string',
                    'sanitize_callback' => 'sanitize_text_field',
                ],
                'amount' => [
                    'required' => true,
                    'type' => 'number',
                ],
                'nature' => [
                    'required' => true,
                    'type' => 'string',
                    'enum' => ['ENTRADA', 'SAIDA'],
                ],
                'reference_month' => [
                    'required' => true,
                    'type' => 'string',
                    'pattern' => '^\d{4}-\d{2}$',
                ],
                'recurrence_months_count' => [
                    'required' => false,
                    'type' => 'integer',
                ],
                'recurrence_start' => [
                    'required' => false,
                    'type' => 'string',
                    'enum' => ['CURRENT', 'NEXT'],
                ],
            ],
        ]);

        register_rest_route(self::NAMESPACE, '/commitments/(?P<id>\d+)/settle', [
            'methods' => \WP_REST_Server::CREATABLE,
            'callback' => [$commitmentController, 'settle'],
            'permission_callback' => [$commitmentController, 'permissionCheck'],
            'args' => [
                'id' => [
                    'required' => true,
                    'type' => 'integer',
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


        register_rest_route(self::NAMESPACE, '/recurrences/(?P<id>\d+)/occurrences/(?P<month>\d{4}-\d{2})', [
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
                    'pattern' => '^\d{4}-\d{2}$',
                ],
            ],
        ]);

        register_rest_route(self::NAMESPACE, '/recurrences/(?P<id>\d+)/occurrences/(?P<month>\d{4}-\d{2})/effectuation', [
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
                    'pattern' => '^\d{4}-\d{2}$',
                ],
            ],
        ]);

        register_rest_route(self::NAMESPACE, '/recurrences/(?P<id>\d+)/occurrences/(?P<month>\d{4}-\d{2})/undo-effectuation', [
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
                    'pattern' => '^\d{4}-\d{2}$',
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
                    'pattern' => '^\d{4}-\d{2}$',
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
                    'pattern' => '^\d{4}-\d{2}$',
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

        register_rest_route(self::NAMESPACE, '/profile-reset', [
            'methods' => \WP_REST_Server::CREATABLE,
            'callback' => [$profileController, 'reset'],
            'permission_callback' => [$profileController, 'permissionCheck'],
            'args' => [
                'confirmation' => ['required' => true, 'type' => 'boolean'],
                'phrase' => ['required' => true, 'type' => 'string'],
            ],
        ]);

        register_rest_route(self::NAMESPACE, '/account-access', [
            'methods' => \WP_REST_Server::DELETABLE,
            'callback' => [$profileController, 'deleteAccount'],
            'permission_callback' => [$profileController, 'permissionCheck'],
            'args' => [
                'confirmation' => ['required' => true, 'type' => 'boolean'],
                'phrase' => ['required' => true, 'type' => 'string'],
            ],
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
}
