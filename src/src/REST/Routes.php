<?php

declare(strict_types=1);

namespace SGFP\REST;

use SGFP\Application\Services\CreateAccountService;
use SGFP\Application\Services\CreateCategoryService;
use SGFP\Application\Services\CreateCommitmentService;
use SGFP\Application\Services\CreateTransferService;
use SGFP\Application\Services\CreateBackupService;
use SGFP\Application\Services\ValidateBackupService;
use SGFP\Application\Services\RevalidateRestorationService;
use SGFP\Application\Services\CapturePreRestorationSnapshotService;
use SGFP\Application\Services\GetDashboardService;
use SGFP\Application\Services\GetNetWorthService;
use SGFP\Application\Services\ListAccountsService;
use SGFP\Application\Services\ListCategoriesService;
use SGFP\Application\Services\ListMovementsService;
use SGFP\Application\Services\SeedCategoriesService;
use SGFP\Application\Services\SetInitialBalanceService;
use SGFP\Application\Services\SettleCommitmentService;
use SGFP\Application\Services\UndoCommitmentSettlementService;
use SGFP\Application\Services\SettleTransferService;
use SGFP\Application\Services\ThemeService;
use SGFP\Application\Services\UndoTransferSettlementService;
use SGFP\Domain\Policies\AccountPolicy;
use SGFP\Infrastructure\WordPress\WpAccountRepository;
use SGFP\Infrastructure\WordPress\WpCategoryRepository;
use SGFP\Infrastructure\WordPress\WpCommitmentRepository;
use SGFP\Infrastructure\WordPress\WpEntryRepository;
use SGFP\Infrastructure\WordPress\WpTransactionManager;
use SGFP\Infrastructure\WordPress\WpUserContext;
use SGFP\Infrastructure\WordPress\WpRecurrenceRepository;
use SGFP\Infrastructure\WordPress\WpUserPreferenceRepository;
use SGFP\Infrastructure\WordPress\WpUserOperationLock;
use SGFP\Infrastructure\WordPress\WpTransferRepository;
use SGFP\Infrastructure\WordPress\WpBackupStore;
use SGFP\Infrastructure\WordPress\WpRestorationTokenClaim;
use SGFP\Infrastructure\WordPress\WpRestorationTokenStore;
use SGFP\REST\Controllers\AccountController;
use SGFP\REST\Controllers\CategoryController;
use SGFP\REST\Controllers\CommitmentController;
use SGFP\REST\Controllers\RecurrenceController;
use SGFP\REST\Controllers\ReportingController;
use SGFP\REST\Controllers\ThemeController;
use SGFP\REST\Controllers\TransferController;
use SGFP\REST\Controllers\BackupController;
use SGFP\REST\Controllers\RestoreController;

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
        $accountPolicy = new AccountPolicy($accountRepository);
        $seedCategories = new SeedCategoriesService($categoryRepository);

        $accountController = new AccountController(
            new CreateAccountService($accountRepository, $userContext, $accountPolicy, $seedCategories),
            new ListAccountsService($accountRepository, $userContext),
            new SetInitialBalanceService($accountRepository, $entryRepository, $transactionManager, $userContext)
        );

        $categoryController = new CategoryController(
            new CreateCategoryService($categoryRepository, $userContext),
            new ListCategoriesService($categoryRepository, $userContext)
        );

        $recurrenceRepository = new WpRecurrenceRepository();

        $commitmentController = new CommitmentController(
            new CreateCommitmentService($commitmentRepository, $categoryRepository, $recurrenceRepository, $userContext),
            new SettleCommitmentService($commitmentRepository, $entryRepository, $accountRepository, $transactionManager, $userContext),
            new UndoCommitmentSettlementService($commitmentRepository, $entryRepository, $transactionManager, $userContext)
        );

        $transferRepository = new WpTransferRepository();

        $transferController = new TransferController(
            new CreateTransferService($accountRepository, $commitmentRepository, $transferRepository, $transactionManager, $userContext),
            new SettleTransferService($commitmentRepository, $transferRepository, $entryRepository, $transactionManager, $userContext),
            new UndoTransferSettlementService($commitmentRepository, $transferRepository, $entryRepository, $transactionManager, $userContext)
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
            new GetNetWorthService($accountRepository, $entryRepository, $userContext),
            new GetDashboardService($accountRepository, $entryRepository, $commitmentRepository, $userContext)
        );

        $themeController = new ThemeController(
            new ThemeService(new WpUserPreferenceRepository(), $userContext)
        );

        $backupController = new BackupController(new CreateBackupService(
            $accountRepository,
            $categoryRepository,
            $commitmentRepository,
            $entryRepository,
            $recurrenceRepository,
            $transferRepository,
            new WpUserPreferenceRepository(),
            $transactionManager,
            $userContext,
            new WpUserOperationLock(),
        ));
        $snapshotCapture = new CapturePreRestorationSnapshotService(
            $accountRepository, $categoryRepository, $commitmentRepository, $entryRepository,
            $recurrenceRepository, $transferRepository, new WpUserPreferenceRepository(),
            $transactionManager, $userContext, new WpUserOperationLock(), new WpBackupStore()
        );
        $restoreController = new RestoreController(new ValidateBackupService(
            new WpRestorationTokenStore(),
            $userContext,
        ), new RevalidateRestorationService(new WpUserPreferenceRepository(), $userContext, new WpUserOperationLock(), $snapshotCapture, new WpRestorationTokenClaim(), new WpRestorationTokenStore()));

        register_rest_route(self::NAMESPACE, '/accounts', [
            'methods' => \WP_REST_Server::CREATABLE,
            'callback' => [$accountController, 'create'],
            'permission_callback' => [$accountController, 'permissionCheck'],
            'args' => [
                'name' => [
                    'required' => true,
                    'type' => 'string',
                    'sanitize_callback' => 'sanitize_text_field',
                ],
                'role' => [
                    'required' => false,
                    'type' => 'string',
                    'enum' => ['PRINCIPAL', 'SECUNDARIA'],
                ],
            ],
        ]);

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
                'type' => [
                    'required' => true,
                    'type' => 'string',
                    'enum' => ['PADRAO', 'TRANSFERENCIA'],
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

        register_rest_route(self::NAMESPACE, '/transfers', [
            'methods' => \WP_REST_Server::CREATABLE,
            'callback' => [$transferController, 'create'],
            'permission_callback' => [$transferController, 'permissionCheck'],
            'args' => [
                'name' => [
                    'required' => true,
                    'type' => 'string',
                    'sanitize_callback' => 'sanitize_text_field',
                ],
                'amount' => [
                    'required' => true,
                    'type' => 'string',
                ],
                'reference_month' => [
                    'required' => true,
                    'type' => 'string',
                    'pattern' => '^\d{4}-\d{2}$',
                ],
                'source_account_id' => [
                    'required' => true,
                    'type' => 'integer',
                ],
                'target_account_id' => [
                    'required' => true,
                    'type' => 'integer',
                ],
            ],
        ]);

        register_rest_route(self::NAMESPACE, '/transfers/(?P<id>\d+)/effectuation', [
            'methods' => \WP_REST_Server::CREATABLE,
            'callback' => [$transferController, 'settle'],
            'permission_callback' => [$transferController, 'permissionCheck'],
            'args' => [
                'id' => [
                    'required' => true,
                    'type' => 'integer',
                ],
            ],
        ]);

        register_rest_route(self::NAMESPACE, '/transfers/(?P<id>\d+)/undo-effectuation', [
            'methods' => \WP_REST_Server::CREATABLE,
            'callback' => [$transferController, 'undo'],
            'permission_callback' => [$transferController, 'permissionCheck'],
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

        register_rest_route(self::NAMESPACE, '/net-worth', [
            'methods' => \WP_REST_Server::READABLE,
            'callback' => [$reportingController, 'netWorth'],
            'permission_callback' => [$reportingController, 'permissionCheck'],
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
