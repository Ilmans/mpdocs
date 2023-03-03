<?php

use App\Yantrana\Components\Article\Controllers\ArticleController;
use App\Yantrana\Components\Home\Controllers\HomeController;
use App\Yantrana\Components\Project\Controllers\ProjectController;
use App\Yantrana\Components\User\Controllers\UserController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

/**
 * Start Routes
 *********************************************************************************************/
Route::group(
    [
        'namespace' => '\App\Yantrana\Components',
    ],
    function () {
        Route::group(
            [
                /* 'middleware' => 'auth' */
            ],
            function () {
                Route::group(['namespace' => 'Home\Controllers'], function () {
                    // Home
                    Route::get('/', [
                        HomeController::class,
                        'publicIndex',
                    ])->name('public.app');
                    // Home
                    Route::get('/docs', [
                        HomeController::class,
                        'publicIndex',
                    ])->name('public.app.docs');

                    // Read Search Data
                    Route::get('/search', [
                        HomeController::class,
                        'readSearchData',
                    ])->name('public.search.read');

                    // Project Details
                    Route::get(
                        '/docs/{projectSlug}/{versionSlug?}/{articleSlug?}',
                        [HomeController::class, 'showDocView']
                    )->name('doc.view');

                    // Project Details
                    Route::get(
                        '/embed/{projectSlug}/{versionSlug?}/{articleSlug?}',
                        [HomeController::class, 'showDocEmbedView']
                    )->name('embed.doc.view');
                });

                Route::group(
                    ['namespace' => 'Project\Controllers'],
                    function () {
                        // Project Details
                        Route::get('/project/{projectUid}/details', [
                            ProjectController::class,
                            'publicProjectDetailsView',
                        ])->name('public.project.read.view_details');
                    }
                );

                Route::group(
                    ['namespace' => 'Article\Controllers'],
                    function () {
                        // Articles
                        Route::get('/article/{articleContentUid}/details', [
                            ArticleController::class,
                            'publicDetailsView',
                        ])->name('public.article.read.details_view');

                        // embed article
                        Route::get('/embed/{articleContentUid}', [
                            ArticleController::class,
                            'publicEmbedDetailsView',
                        ])->name('embed.article.read.details_view');

                        // embed script
                        Route::get('/embed-doc.js', [
                            ArticleController::class,
                            'loadEmbedScript',
                        ])->name('embed.script');
                    }
                );

                /*
        Start user Component public Routes
        ----------------------------------------------------------------------- */
                Route::group(['namespace' => 'User\Controllers'], function () {
                    Route::post('/logout', [
                        UserController::class,
                        'logout',
                    ])->name('user.logout');

                    // contact us form
                    Route::get('/contact-us', [
                        UserController::class,
                        'contactUsForm',
                    ])->name('public.contact.view');

                    // contact us request
                    Route::post('/contact-us-request', [
                        UserController::class,
                        'contactUsFormRequest',
                    ])->name('public.contact.request.process');
                });
            }
        );

        /**
         *End Public Web Routes
         *********************************************************************************************/

        /**
         * Api Routes
         *
         *********************************************************************************************/

        // verify installation
        Route::get('/app-configuration', [
            'as' => 'installation.verify',
            'uses' => 'Installation\InstallationVerification@verify',
        ]);

        Route::get('/error-not-found', [
            'as' => 'error.public-not-found',
            'uses' => '__Igniter@errorNotFound',
        ]);

        // get all application template using this route
        Route::get('/get-template/{viewName}', [
            'as' => 'template.get',
            'uses' => '__Igniter@getTemplate',
        ]);

        // captcha generate url
        Route::get('/generate-captcha', [
            'as' => 'security.captcha',
            'uses' => '__Igniter@captcha',
        ]);

        // get all application template using this route
        Route::get('/email-view/{viewName}', [
            'as' => 'template.email',
            'uses' => '__Igniter@emailTemplate',
        ]);

        // Change Theme Color
        Route::get('/{colorName}/change-theme-color', [
            'as' => 'theme_color',
            'uses' => '__Igniter@changeThemeColor',
        ]);

        /*
    User Components Public Section Related Routes
    ----------------------------------------------------------------------- */

        Route::get('/base-data', [
            'as' => 'base_data',
            'uses' => '__Igniter@baseData',
        ]);

        Route::get('/app', [
            'as' => 'manage.app',
            'uses' => '__Igniter@manageIndex',
        ]);

        Route::group(['middleware' => 'auth'], function () {
            Route::group(
                [
                    'namespace' => 'User\Controllers',
                    'prefix' => 'user',
                ],
                function () {
                    // profile
                    Route::get('/profile', [
                        'as' => 'user.profile',
                        'uses' => 'UserController@profile',
                    ]);

                    // change password
                    Route::get('/change-password', [
                        'as' => 'user.change_password',
                        'uses' => 'UserController@changePassword',
                    ]);

                    // new email activation
                    Route::get(
                        '/{userID}/{activationKey}/new-email-activation',
                        [
                            'as' => 'user.new_email.activation',
                            'uses' => 'UserController@newEmailActivation',
                        ]
                    );
                }
            );
        });

        /*
      Start After Authentication Accessible Routes
      ---------------------------------------------------------------------- */

        Route::group(['middleware' => 'authority.checkpost'], function () {
            /*
          Start Dashboard Components Manage Section Related Routes
        ------------------------------------------------------------------- */

            // Request Initialization for evenry request
            Route::get('/{routeName}/request-initialization', [
                'as' => 'request.initialization',
                'uses' => '__Igniter@getRequestInitialization',
            ]);

            Route::group(
                [
                    'namespace' => 'Media\Controllers',
                    'prefix' => 'media',
                ],
                function () {
                    // upload image media detail
                    Route::get('/read-uploaded-user-profile-files-detail', [
                        'as' => 'media.upload.read_user_profile',
                        'uses' => 'MediaController@readUserProfileFiles',
                    ]);

                    Route::post('/upload-user-profile', [
                        'as' => 'media.upload.write.user_profile',
                        'uses' => 'MediaController@uploadUserProfile',
                    ]);
                }
            );

            Route::group(
                [
                    'namespace' => 'Article\Controllers',
                    'prefix' => '/article',
                ],
                function () {
                    // Article list
                    Route::get(
                        'list/{projectSlug}/{versionSlug}/{articleSlug}',
                        [
                            'as' => 'manage.article.read.print_article',
                            'uses' => 'ArticleController@printArticleDocument',
                        ]
                    );
                }
            );

            /**
             *
             * Start api section routes after login
             *
             * ---------------------------------------------------------------------------------------- */
            Route::group(['prefix' => 'api'], function () {
                /*
		    Start Projects Component Related Routes
		    ----------------------------------------------------------------------- */
                Route::group(
                    [
                        'namespace' => 'Project\Controllers',
                        'prefix' => '/project',
                    ],
                    function () {
                        // Project list
                        Route::get('/list', [
                            'as' => 'manage.project.read.list',
                            'uses' => 'ProjectController@prepareProjectList',
                        ]);

                        // Project delete process
                        Route::post('/{projectIdOrUid}/delete-process', [
                            'as' => 'manage.project.write.delete',
                            'uses' => 'ProjectController@processProjectDelete',
                        ]);

                        // Project create support data
                        Route::get('/add-support-data', [
                            'as' => 'manage.project.read.support_data',
                            'uses' =>
                                'ProjectController@prepareProjectSupportData',
                        ]);

                        // Project details
                        Route::get('/{projectIdOrUid}/project-details', [
                            'as' => 'manage.project.read.details.data',
                            'uses' => 'ProjectController@prepareProjectDetails',
                        ]);

                        // Project create process
                        Route::post('/add-process', [
                            'as' => 'manage.project.write.create',
                            'uses' => 'ProjectController@processProjectCreate',
                        ]);

                        // Project get the data
                        Route::get('/{projectIdOrUid}/get-update-data', [
                            'as' => 'manage.project.read.update.data',
                            'uses' => 'ProjectController@updateProjectData',
                        ]);

                        // Project update process
                        Route::post('/{projectIdOrUid}/update-process', [
                            'as' => 'manage.project.write.update',
                            'uses' => 'ProjectController@processProjectUpdate',
                        ]);

                        // Project delete
                        Route::post(
                            '/{projectIdOrUid}/{languageId}/delete-language-process',
                            [
                                'as' => 'manage.project.write.language_delete',
                                'uses' =>
                                    'ProjectController@processProjectLanguageDelete',
                            ]
                        );

                        // Delete Project Media
                        Route::post(
                            '/{projectIdOrUid}/{mediaType}/delete-media-process',
                            [
                                'as' => 'manage.project.write.media_delete',
                                'uses' =>
                                    'ProjectController@deleteProjectMedia',
                            ]
                        );
                    }
                );

                /*
		    End Projects Component Related Routes
		    ----------------------------------------------------------------------- */

                Route::group(
                    [
                        'namespace' => 'Version\Controllers',
                        'prefix' => '/version/{projectIdOrUid}/',
                    ],
                    function () {
                        // Project list
                        Route::get('/read-project-info', [
                            'as' => 'manage.project.version.read.project_info',
                            'uses' => 'VersionController@readProjectInfo',
                        ]);

                        // Project list
                        Route::get('/version-list', [
                            'as' => 'manage.project.version.read.list',
                            'uses' => 'VersionController@prepareVersionList',
                        ]);

                        // Project delete process
                        Route::post('/{versionUid}/version-delete-process', [
                            'as' => 'manage.project.version.write.delete',
                            'uses' => 'VersionController@processVersionDelete',
                        ]);

                        // Project create support data
                        Route::get('/version-add-support-data', [
                            'as' => 'manage.project.version.read.support_data',
                            'uses' =>
                                'VersionController@prepareVersionSupportData',
                        ]);

                        // Project create process
                        Route::post('/version-add-process', [
                            'as' => 'manage.project.version.write.create',
                            'uses' => 'VersionController@processVersionCreate',
                        ]);

                        // Project get the data
                        Route::get('/{versionUid}/version-get-update-data', [
                            'as' => 'manage.project.version.read.update.data',
                            'uses' => 'VersionController@updateVersionData',
                        ]);

                        // Get Version Support Data
                        Route::get('/{versionUid}/version-get-support-data', [
                            'as' =>
                                'manage.project.version.read.get_support_data',
                            'uses' => 'VersionController@updateVersionData',
                        ]);

                        // Project update process
                        Route::post('/{versionUid}/version-update-process', [
                            'as' => 'manage.project.version.write.update',
                            'uses' => 'VersionController@processVersionUpdate',
                        ]);

                        // PDF Viewer Route
                        Route::get('/download/{versionUid}', [
                            'as' =>
                                'manage.project.version.document_download_pdf',
                            'uses' => 'VersionController@downloadDocumentPdf',
                        ]);
                    }
                );

                /*
		    Start Articles Component Related Routes
		    ----------------------------------------------------------------------- */

                Route::group(
                    [
                        'namespace' => 'Article\Controllers',
                        'prefix' => '/article',
                    ],
                    function () {
                        // Article list
                        Route::get('list/{projectUid?}/{versionUid?}', [
                            'as' => 'manage.article.read.list',
                            'uses' => 'ArticleController@prepareArticleList',
                        ]);

                        // Article delete process
                        Route::post('/{articleIdOrUid}/delete-process', [
                            'as' => 'manage.article.write.delete',
                            'uses' => 'ArticleController@processArticleDelete',
                        ]);

                        // Article create support data
                        Route::get(
                            '/add-support-data/{projectUid}/{versionUid}',
                            [
                                'as' => 'manage.article.read.support_data',
                                'uses' =>
                                    'ArticleController@prepareArticleSupportData',
                            ]
                        );

                        // Article create process
                        Route::post('/add-process/{requestType}/{projectUid}', [
                            'as' => 'manage.article.write.create',
                            'uses' => 'ArticleController@processArticleCreate',
                        ]);

                        // Article get the data
                        Route::get(
                            '/{articleIdOrUid}/get-update-data/{projectUid}/{versionUid}',
                            [
                                'as' => 'manage.article.read.update.data',
                                'uses' => 'ArticleController@updateArticleData',
                            ]
                        );

                        // Article update process
                        Route::post(
                            '/{projectUid}/{articleIdOrUid}/{requestType}/update-process',
                            [
                                'as' => 'manage.article.write.update',
                                'uses' =>
                                    'ArticleController@processArticleUpdate',
                            ]
                        );

                        // Article get the data
                        Route::get(
                            '/{articleIdOrUid}/{contentUid}/fetch-article-content-details',
                            [
                                'as' => 'manage.article.read.content_details',
                                'uses' =>
                                    'ArticleController@readArticleContentDetails',
                            ]
                        );

                        // Article get the data
                        Route::get('/{articleIdOrUid}/details', [
                            'as' => 'manage.article.read.details',
                            'uses' => 'ArticleController@readArticleDetails',
                        ]);

                        // Article update parent
                        Route::post('/{articleIdOrUid}/update-parent', [
                            'as' => 'manage.article.write.update_parent',
                            'uses' => 'ArticleController@updateParent',
                        ]);
                    }
                );

                /*
		    End Articles Component Related Routes
		    ----------------------------------------------------------------------- */

                /*
		    Start Language Component Related Routes
		    ----------------------------------------------------------------------- */
                Route::group(
                    [
                        'namespace' => 'Language\Controllers',
                        'prefix' => '/language',
                    ],
                    function () {
                        // Language list
                        Route::get('/list', [
                            'as' => 'manage.language.read.list',
                            'uses' => 'LanguageController@prepareLanguageList',
                        ]);

                        // Language delete process
                        Route::post('/{languageIdOrUid}/delete-process', [
                            'as' => 'manage.language.write.delete',
                            'uses' =>
                                'LanguageController@processLanguageDelete',
                        ]);

                        // Language create support data
                        Route::get('/add-support-data', [
                            'as' => 'manage.language.read.support_data',
                            'uses' =>
                                'LanguageController@prepareLanguageSupportData',
                        ]);

                        // Language create process
                        Route::post('/add-process', [
                            'as' => 'manage.language.write.create',
                            'uses' =>
                                'LanguageController@processLanguageCreate',
                        ]);

                        // Language get the data
                        Route::get('/{languageIdOrUid}/get-update-data', [
                            'as' => 'manage.language.read.update.data',
                            'uses' => 'LanguageController@updateLanguageData',
                        ]);

                        // Language update process
                        Route::post('/{languageIdOrUid}/update-process', [
                            'as' => 'manage.language.write.update',
                            'uses' =>
                                'LanguageController@processLanguageUpdate',
                        ]);
                    }
                );

                /*
		    End Language Component Related Routes
		    ----------------------------------------------------------------------- */

                /*
		    Start Faq Component Related Routes
		    ----------------------------------------------------------------------- */
                Route::group(
                    [
                        'namespace' => 'Faq\Controllers',
                        'prefix' => '/faq',
                    ],
                    function () {
                        // Faq list
                        Route::get('/list', [
                            'as' => 'manage.faq.read.list',
                            'uses' => 'FaqController@prepareFaqList',
                        ]);

                        // Faq delete process
                        Route::post('/{faqIdOrUid}/delete-process', [
                            'as' => 'manage.faq.write.delete',
                            'uses' => 'FaqController@processFaqDelete',
                        ]);

                        // Faq create support data
                        Route::get('/add-support-data', [
                            'as' => 'manage.faq.read.support_data',
                            'uses' => 'FaqController@prepareFaqSupportData',
                        ]);

                        // Faq create process
                        Route::post('/add-process', [
                            'as' => 'manage.faq.write.create',
                            'uses' => 'FaqController@processFaqCreate',
                        ]);

                        // Faq get the data
                        Route::get('/{faqIdOrUid}/get-update-data', [
                            'as' => 'manage.faq.read.update.data',
                            'uses' => 'FaqController@updateFaqData',
                        ]);

                        // Faq update process
                        Route::post('/{faqIdOrUid}/update-process', [
                            'as' => 'manage.faq.write.update',
                            'uses' => 'FaqController@processFaqUpdate',
                        ]);

                        // Faq get the data
                        Route::get('/{faqIdOrUid}/faq-details', [
                            'as' => 'manage.faq.read.details.data',
                            'uses' => 'FaqController@getFaqDetails',
                        ]);
                    }
                );

                /*
		    End Faq Component Related Routes
		    ----------------------------------------------------------------------- */

                /*
		    Start Comment Component Related Routes
		    ----------------------------------------------------------------------- */
                Route::group(
                    [
                        'namespace' => 'Comment\Controllers',
                        'prefix' => '/comments',
                    ],
                    function () {
                        // Comment list
                        Route::get(
                            '/list/{requestType}/{status}/{articleUid?}',
                            [
                                'as' => 'manage.comment.read.list',
                                'uses' =>
                                    'CommentController@prepareCommentList',
                            ]
                        );

                        // Comment delete process
                        Route::post('/{commentIdOrUid}/delete-process', [
                            'as' => 'manage.comment.write.delete',
                            'uses' => 'CommentController@processCommentDelete',
                        ]);

                        // Comment get the data
                        Route::get('/{commentIdOrUid}/get-update-data', [
                            'as' => 'manage.comment.read.update.data',
                            'uses' => 'CommentController@updateCommentData',
                        ]);

                        // Comment update process
                        Route::post('/{commentIdOrUid}/update-process', [
                            'as' => 'manage.comment.write.update',
                            'uses' => 'CommentController@processCommentUpdate',
                        ]);

                        // Comment get the data
                        Route::get('/{commentIdOrUid}/get-comment-reply-data', [
                            'as' => 'manage.comment.read.reply.data',
                            'uses' => 'CommentController@readCommentReply',
                        ]);

                        // Comment update process
                        Route::post('/{commentIdOrUid}/add-reply-process', [
                            'as' => 'manage.comment.reply.write',
                            'uses' =>
                                'CommentController@processCommentReplyAdd',
                        ]);
                    }
                );

                /*
		    Start Company Activity-log Component Related Routes
		    ----------------------------------------------------------------------- */
                //Activity Log
                Route::group(
                    [
                        'namespace' => 'ActivityLog\Controllers',
                        'prefix' => '/activity',
                    ],
                    function () {
                        // Activity list
                        Route::get('/{startDate}/{endDate}/list', [
                            'as' => 'manage.activity_log.read.list',
                            'uses' =>
                                'ActivityLogController@prepareActivityLogList',
                        ]);
                    }
                );

                /*
            Start User Role Permission Components Manage Section Related Routes
            ------------------------------------------------------------------- */
                Route::group(
                    [
                        'namespace' => 'User\Controllers',
                        'prefix' => '/role-permission',
                    ],
                    function () {
                        // Get Role Add Support Data
                        Route::get('/role-add-support-data', [
                            'as' =>
                                'manage.user.role_permission.read.add_support_data',
                            'uses' =>
                                'RolePermissionController@getAddSuppotData',
                        ]);

                        // Get role all permission using id
                        Route::get('/{roleId}/get-permission', [
                            'as' => 'manage.user.role_permission.read.using_id',
                            'uses' =>
                                'RolePermissionController@getPermissionById',
                        ]);

                        // Add New Role Permissions
                        Route::post('/add-process', [
                            'as' =>
                                'manage.user.role_permission.write.role.create',
                            'uses' => 'RolePermissionController@addNewRole',
                        ]);

                        // Role Permission list
                        Route::get('/list', [
                            'as' => 'manage.user.role_permission.read.list',
                            'uses' =>
                                'RolePermissionController@prepareRolePermissionList',
                        ]);

                        // Role Permission delete process
                        Route::post('/{rolePermissionIdOrUid}/delete-process', [
                            'as' => 'manage.user.role_permission.write.delete',
                            'uses' =>
                                'RolePermissionController@processRolePermissionDelete',
                        ]);

                        // Get Role Permissions
                        Route::get('/{roleId}/permissions', [
                            'as' => 'manage.user.role_permission.read',
                            'uses' => 'RolePermissionController@getPermissions',
                        ]);

                        // Create User Role Dynamic Permission
                        Route::post(
                            '/{roleId}/add-dynamic-permission-process',
                            [
                                'as' =>
                                    'manage.user.role_permission.write.create',
                                'uses' =>
                                    'RolePermissionController@processDynamicRolePermission',
                            ]
                        );
                    }
                );
                /*
            End User Role Permission Components Manage Section Related Routes
            ------------------------------------------------------------------- */

                /*
            Start User Components Manage Section Related Routes
            ------------------------------------------------------------------- */

                Route::group(
                    [
                        'namespace' => 'User\Controllers',
                        'prefix' => 'user',
                    ],
                    function () {
                        Route::get('/process-logout', [
                            'as' => 'user.process_logout',
                            'uses' => 'UserController@processLogout',
                        ]);

                        // change email support data
                        Route::get('/change-email-support-data', [
                            'as' => 'user.change_email.support_data',
                            'uses' =>
                                'UserController@getChangeEmailSupportData',
                        ]);

                        // change password process
                        Route::post('/change-password', [
                            'as' => 'user.change_password.process',
                            'uses' => 'UserController@changePasswordProcess',
                        ]);

                        // profile details
                        Route::get('/profile-details', [
                            'as' => 'user.profile.details',
                            'uses' => 'UserController@profileDetails',
                        ]);

                        // profile edit support data
                        Route::get('/profile/edit-support-data', [
                            'as' => 'user.profile.edit_support_data',
                            'uses' => 'UserController@profileEditSupportData',
                        ]);

                        // profile update
                        Route::get('/profile/edit', [
                            'as' => 'user.profile.update',
                            'uses' => 'UserController@updateProfile',
                        ]);

                        // profile update process
                        Route::post('/profile/edit', [
                            'as' => 'user.profile.update.process',
                            'uses' => 'UserController@updateProfileProcess',
                        ]);

                        // change email process
                        Route::post('/change-email', [
                            'as' => 'user.change_email.process',
                            'uses' => 'UserController@changeEmailProcess',
                        ]);

                        // fetch users list for datatable
                        Route::get('/{status}/fetch-list', [
                            'as' => 'manage.user.read.datatable.list',
                            'uses' => 'UserController@index',
                        ])->where('status', '[0-9]+');

                        // fetch users detail data
                        Route::get('/{userID}/user-detail', [
                            'as' => 'manage.user.read.detail.data',
                            'uses' => 'UserController@userDetailData',
                        ]);

                        // delete user
                        Route::post('/{userID}/delete', [
                            'as' => 'manage.user.write.delete',
                            'uses' => 'UserController@delete',
                        ])->where('userID', '[0-9]+');

                        // restore user
                        Route::post('/{userID}/restore', [
                            'as' => 'manage.user.write.restore',
                            'uses' => 'UserController@restore',
                        ])->where('userID', '[0-9]+');

                        // change password by admin process
                        Route::post('/{userID}/change-password', [
                            'as' => 'manage.user.write.change_password.process',
                            'uses' => 'UserController@changePasswordByAdmin',
                        ])->where('userID', '[0-9]+');

                        // fetch users details
                        Route::get('/{userID}/contact', [
                            'as' => 'manage.user.read.contact',
                            'uses' => 'UserController@contact',
                        ])->where('status', '[0-9]+');

                        // process contact form
                        Route::get('/{userId}/get-user-info', [
                            'as' => 'manage.user.read.info',
                            'uses' => 'UserController@getInfo',
                        ]);

                        // Get add support data
                        Route::get('/add-support-data', [
                            'as' => 'manage.user.read.create.support_data',
                            'uses' => 'UserController@getAddSupportData',
                        ]);

                        // Get User available permissions
                        Route::get('/{userId}/get-user-permissions', [
                            'as' => 'manage.user.read.get_user_permissions',
                            'uses' => 'UserController@getUserPermissions',
                        ]);

                        // Get User Edit Data
                        Route::get('/{userId}/get-user-edit data', [
                            'as' => 'manage.user.read.edit_suppport_data',
                            'uses' => 'UserController@getUserEditSupportData',
                        ]);

                        // Store user dynamic permissions
                        Route::post('/{userId}/user-update-process', [
                            'as' => 'manage.user.write.update_process',
                            'uses' => 'UserController@processUserUpdate',
                        ]);

                        // Store user dynamic permissions
                        Route::post(
                            '/{userId}/user-dynamic-permission-process',
                            [
                                'as' =>
                                    'manage.user.write.user_dynamic_permission',
                                'uses' =>
                                    'UserController@processUserPermissions',
                            ]
                        );

                        // Add New User
                        Route::post('/add', [
                            'as' => 'manage.user.write.create',
                            'uses' => 'UserController@add',
                        ]);
                    }
                );
                /*
            End User Components Manage Section Related Routes
            ------------------------------------------------------------------- */

                /*
            Start Configuration Components Manage Section Related Routes
            ------------------------------------------------------------------- */

                Route::group(
                    [
                        'namespace' => 'Configuration\Controllers',
                        'prefix' => 'configuration',
                    ],
                    function () {
                        // process configuration
                        Route::post('/process/{formType}', [
                            'as' => 'manage.configuration.process',
                            'uses' => 'ConfigurationController@process',
                        ]);

                        // process get configuration
                        Route::get('/get-support-data/{formType}', [
                            'as' => 'manage.configuration.get.support.data',
                            'uses' => 'ConfigurationController@getSupportData',
                        ]);
                    }
                );
                /*
            End Configuration Components Manage Section Related Routes
            ------------------------------------------------------------------- */

                Route::group(
                    [
                        'namespace' => 'FileManager\Controllers',
                        'prefix' => '/file-manager',
                    ],
                    function () {
                        // upload common files
                        Route::post('/file-upload', [
                            'as' => 'file_manager.upload',
                            'uses' => 'FileManagerController@upload',
                        ]);
                    }
                );

                /*
            End tag Components Manage Section Related Routes
            ------------------------------------------------------------------- */
            });

            /**
             *
             * End console section routes after login
             *
             * ---------------------------------------------------------------------------------------- */

            /**
             *
             * Public section routes after login
             *
             * ---------------------------------------------------------------------------------------- */

            /*
            Media Components Public Section Related Routes
            ----------------------------------------------------------------------- */

            Route::group(
                [
                    'namespace' => 'Media\Controllers',
                    'prefix' => 'media',
                ],
                function () {
                    // upload image media
                    Route::post('/upload-files', [
                        'as' => 'media.upload.write',
                        'uses' => 'MediaController@upload',
                    ]);

                    // upload image media
                    Route::post('/upload-project-files', [
                        'as' => 'media.upload.write.project',
                        'uses' => 'MediaController@uploadProjectFiles',
                    ]);

                    // upload image media
                    Route::get('/read-project-files', [
                        'as' => 'media.upload.read.project_files',
                        'uses' => 'MediaController@readProjectFiles',
                    ]);

                    // upload all media
                    Route::post('/upload-logo', [
                        'as' => 'media.upload.write.logo',
                        'uses' => 'MediaController@uploadLogo',
                    ]);

                    // delete media file
                    Route::post('/{fileName}/delete', [
                        'as' => 'media.upload.delete',
                        'uses' => 'MediaController@delete',
                    ]);

                    // delete multiple media files
                    Route::post('/multiple-delete', [
                        'as' => 'media.upload.delete_multiple',
                        'uses' => 'MediaController@multipleDeleteFiles',
                    ]);

                    // select media files
                    Route::post('/select-files', [
                        'as' => 'media.upload.select_files',
                        'uses' => 'MediaController@selectFiles',
                    ]);

                    // upload image media
                    Route::get('/read-files', [
                        'as' => 'media.upload.read.files',
                        'uses' => 'MediaController@readFiles',
                    ]);

                    // upload image media detail
                    Route::get('/read-uploaded-files-detail', [
                        'as' => 'media.upload.read_logo',
                        'uses' => 'MediaController@readLogoFiles',
                    ]);

                    // upload image media detail
                    Route::get('/read-uploaded-favicon-files-detail', [
                        'as' => 'media.upload.read_favicon',
                        'uses' => 'MediaController@readFaviconFiles',
                    ]);

                    // upload image media detail
                    Route::get('/read-uploaded-attachment-files-detail', [
                        'as' => 'media.upload.read_message_attachment',
                        'uses' => 'MediaController@readMessageAttachmentFiles',
                    ]);

                    // upload image media detail
                    Route::get(
                        '/read-uploaded-requirement-attachment-files-detail',
                        [
                            'as' => 'media.upload.read_requirement_attachment',
                            'uses' =>
                                'MediaController@readRequirementAttachmentFiles',
                        ]
                    );
                }
            );
        });

        /*
      End After Authentication Accessible Routes
      ---------------------------------------------------------------------- */

        /*
     start  Guest Auth Routes
    -------------------------------------------------------------------------- */

        Route::group(['middleware' => 'guest'], function () {
            /*
          Start User Components Public Section Related Routes
          ----------------------------------------------------------------------- */

            Route::get('login', [UserController::class,'login'])->name('login');
            Route::group(
                [
                    'namespace' => 'User\Controllers',
                    'prefix' => 'user',
                ],
                function () {
                    // login process

                  
                    Route::post('/process-login', [
                        'as' => 'user.login.process',
                        'uses' => 'UserController@loginProcess',
                    ]);

                    // login attempts
                    Route::get('/login-attempts', [
                        'as' => 'user.login.attempts',
                        'uses' => 'UserController@loginAttempts',
                    ]);

                    // forgot password
                    Route::post('/forgot-password', [
                        'as' => 'user.forgot_password.process',
                        'uses' => 'UserController@forgotPasswordProcess',
                    ]);

                    // reset password
                    Route::get('/reset-password/{reminderToken}', [
                        'as' => 'user.reset_password',
                        'uses' => 'UserController@restPassword',
                    ]);

                    // reset password process
                    Route::post('/process-reset-password/{reminderToken}', [
                        'as' => 'user.reset_password.process',
                        'uses' => 'UserController@restPasswordProcess',
                    ]);

                  
                }
            );

            /*
          End User Components Public Section Related Routes
          ----------------------------------------------------------------------- */
        });

        /*
     end  Guest Auth Routes
    -------------------------------------------------------------------------- */
    }
);

/*
 * End Api Routes
 *
 * ***************************************************************************************/
