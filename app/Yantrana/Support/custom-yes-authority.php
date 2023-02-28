<?php
/*
 *  YesAuthority Configurations
 *
 *  This configuration file is part of YesAuthority
 *
 *------------------------------------------------------------------------------------------------*/
return [
    /* authority configurations
     *--------------------------------------------------------------------------------------------*/
    'config' => [
        /*
         *   @required - if you want use name other than 'authority.checkpost'
         *   middleware_name - YesAuthority Middleware name
        */
        'middleware_name'       => 'authority.checkpost',
        /*
         *   @required
         *   col_user_id - ID column name for users table
        */
        'col_user_id'           => 'users__id',

        /*
         *   @required
         *   col_role - Your Role ID column name for users table
        */
        'col_role'              => 'user_roles__id',

        /*
         *   @optional - if you want to use dynamic permissions
         *   col_user_permissions - Dynamic Permissions(json) column on users table
         *   This column should contain json encoded array containing 'allow' & 'deny' arrays
        */
        'col_user_permissions'  => '__permissions',

        /*
         *   @required
         *   user_model - User Model
        */
        'user_model'            => 'App\Yantrana\Components\User\Models\UserAuthorityModel',
        /*
         *   @optional
         *   role_model - Role Model
        */
        'role_model'            => 'App\Yantrana\Components\User\Models\UserRole',
        /*
         *   @optional
         *   col_role_id - ID column name for role table
        */
        //'col_role_id'           => '_id',

        /*
         *   @optional
         *   ccol_role_permissions - Dynamic Permissions(json) column on role table,
         *   This column should contain json encoded array containing 'allow' & 'deny' arrays
        */
        // 'col_role_permissions'  => '__permissions',

        'pseudo_access_ids'       => [
            'admin', 'guest'
        ],

        'default_allowed_access_ids' => []
    ],
    /*
     *  Authority rules
     *
     *  Rules item needs to have 2 arrays with keys allow & deny value of it will be array
     *  containing access ids as required.
     *  wildcard entries are accepted using *
     *  for each section level deny will be more powerful than allow
     *  also key length also matters more is length more
     *--------------------------------------------------------------------------------------------*/
    'rules' => [
        // base rules for all roles user
        'base' => [
            'allow' => [
                '*',
            ],
            'deny' => [
                'admin'
            ]
        ],
        /*
         *  Role Based rules
         *  First level of defence
         *----------------------------------------------------------------------------------------*/
        'roles' => [
            /*
             *  Rules for the Roles for using id (key will be id)
             *------------------------------------------------------------------------------------*/
            // @example given for role id of 1
            // Admin Role
            1 => [
                'allow' => [
                    '*',
                    'admin'
                ],
                'deny'  => [
                    'guest',
                ],
            ],
            // Stock Incharge Role
            2 => [
                'allow' => [
                    'guest',
                    'view_only_manage_projects'
                ],
                'deny'  => [
                    'admin',
                    'installation.version.*',
                    /*------------projects----------------*/
					'add_project',
					'update_project',
					'delete_project',

					/*------------versions----------------*/
					'view_only_manage_versions',
					'add_version',
					'update_version',
					'delete_version',

					/*------------articles----------------*/
					'view_only_manage_articles',
					'add_article',
					'update_article',
					'delete_article',

					/*------------languages----------------*/
					'view_only_manage_languages',
					'add_language',
					'update_language',
					'delete_language',

					/*------------users----------------*/
                    'view_only_manage_users',
                    'add_user',
                    'edit_user',
                    'delete_and_restore_user',
                    'view_user_details',
                    
                    /*------------roles----------------*/
                    'view_only_manage_role',
                    'add_role',
                    'manage_role_permission',
                    'delete_role',

                    /*------------settings----------------*/
                    'manage_confguration_setting',

                    /*------------activity logs----------------*/
                    'view_activity_log',
                    'activity_log',
                ],
            ]
        ],

        /*
         *  User based rules
         *  2nd level of defense
         *  Will override the rules of above 1st level(roles) if matched
         *----------------------------------------------------------------------------------------*/
        'users' => [
             /*
             *  Rules for the Users for using id (key will be id)
             *------------------------------------------------------------------------------------*/
            // @example given for user id of 1
            1 => [ // this may be admin user id
                'allow' => [],
                'deny'  => [],
            ]
        ],

        /*
         *  DB Role Based rules
         *  3rd level of defense
         *  Will override the rules of above 2nd level(user) if matched
         *  As it will be database based you don't need to do anything here
         *----------------------------------------------------------------------------------------*/

        /*
         *  DB User Based rules
         *  4th level of defense
         *  Will override the rules of above 3rd level(db roles) if matched
         *  As it will be database based you don't need to do anything here
         *----------------------------------------------------------------------------------------*/

        /*  Dynamic permissions based on conditions
         *  Will override the rules of above 4th level(db user) if matched
         *  5th level of defense
         * each condition will be array with following options available:
         *  @key - string - name
         *      @value - string - it will be condition identifier (alpha-numeric-dash)
         *  @key - string - access_ids
         *      @value - array - of ids (alpha-numeric-dash)
         *  @key - string - uses
         *      @value - string - of of classNamespace@method
         *          OR
         *      @value - anonymous function -
         *  @note - both the function/method receive following 3 parameters so you can
         *          run your own magic of logic using it.
         *  $accessIdKey            - string - requested id key
         *  $isAccess               - bool - what is the access received from the above level/condition
         *  $currentRouteAccessId   - current route/accessIds being checked.
         *----------------------------------------------------------------------------------------*/
        'conditions' => [
            // Example conditions
            //  It should return boolean values, true for access allow & false for deny
            [
                'name'       => 'demo_authority',
                'access_ids' => ['demo_authority', 'file_manager.*'],
                'uses'       => function($accessId, $isAccess, $currentRouteAccessId) {

                    if (isDemo()) {
                        
                        $demouserIds     = [2];
                        $currentUserId   = Auth::id();

                        if (in_array($currentUserId, $demouserIds) and in_array($currentRouteAccessId, [
                                'user.change_password.process',
                                'user.change_email.process',
                                'user.profile.update.process'
                            ])) {

                            return false;
                        }

                        if((Auth::id() !== 1)
                            and ((in_array($currentRouteAccessId, [
                                'manage.configuration.process',
                                'media.upload.delete',
                                'media.upload.delete_multiple',
                                'file_manager.upload',
                            ]))
                                or (str_is('media.upload*.write*', $currentRouteAccessId) === true)
                                or (str_is('file_manager.file.*', $currentRouteAccessId) === true)
                                or (str_is('file_manager.folder.*', $currentRouteAccessId) === true)
                                or (str_is('manage.project.write.*', $currentRouteAccessId) === true)
                                or (str_is('manage.project.version.write.*', $currentRouteAccessId) === true)
                                or (str_is('manage.article.write.*', $currentRouteAccessId) === true)
                                or (str_is('manage.language.write.*', $currentRouteAccessId) === true)
                                or (str_is('manage.user.role_permission.write.*', $currentRouteAccessId) === true)
                                or (str_is('manage.user.write.*', $currentRouteAccessId) === true)
                            )
                        ) {
                            return false;
                        }
                    }

                    return true;
                }
            ]
        ]
    ],

    /*
     *  Dynamic access zones
     *
     *  Zones can be created for various reasons, when using dynamic permission system
     *  its bad to store direct access ids into database in that case we can create dynamic access
     *  zones which is the group of access ids & these can be handled with one single key id.
     *----------------------------------------------------------------------------------------*/
    'dynamic_access_zones' => [
        
        /*-----------------------Manage Projects----------------------------------------*/

        'manage_projects' => [
            'title' => "Manage Projects",
            'access_ids' => []
        ],

        'view_only_manage_projects' => [
            'title' => "Read",
            'access_ids' => [
                'manage.project.read.list'
            ],
            'dependencies' => [
            ],
            'parent' => 'manage_projects'
        ],

        // Add project
        'add_project' => [
            'title' => 'Create',
            'access_ids' => [
                'manage.project.read.support_data',
                'manage.project.write.create',
            ],
            'dependencies' => [
                'view_only_manage_projects'
            ],
            'parent' => 'manage_projects'
        ],

        // edit project
        'update_project' => [
            'title' => 'Update',
            'access_ids' => [
                'manage.project.read.update.data',
                'manage.project.write.update'
            ],
            'dependencies' => [
                'view_only_manage_projects'
            ],
            'parent' => 'manage_projects'
        ],

        // delete project
        'delete_project' => [
            'title' => 'Delete',
            'access_ids' => [
                'manage.project.write.delete'
            ],
            'dependencies' => [
                'view_only_manage_projects'
            ],
            'parent' => 'manage_projects'
        ],
        /*----------------------- / Manage Projects----------------------------------------*/

        /*-----------------------Manage Versions----------------------------------------*/

        'manage_versions' => [
            'title' => "Manage Versions",
            'access_ids' => []
        ],

        //list
        'view_only_manage_versions' => [
            'title' => "Read",
            'access_ids' => [
                'manage.project.version.read.list'
            ],
            'dependencies' => [
            ],
            'parent' => 'manage_versions'
        ],

        // add version
        'add_version' => [
            'title' => 'Create',
            'access_ids' => [
                'manage.project.version.read.support_data',
                'manage.project.version.write.create',
            ],
            'dependencies' => [
                'view_only_manage_versions'
            ],
            'parent' => 'manage_versions'
        ],

        // edit version
        'update_version' => [
            'title' => 'Update',
            'access_ids' => [
                'manage.project.version.read.update.data',
                'manage.project.version.write.update',
                'manage.project.version.print_document'
            ],
            'dependencies' => [
                'view_only_manage_versions'
            ],
            'parent' => 'manage_versions'
        ],

        // delete project
        'delete_version' => [
            'title' => 'Delete',
            'access_ids' => [
                'manage.project.version.write.delete'
            ],
            'dependencies' => [
                'view_only_manage_versions'
            ],
            'parent' => 'manage_versions'
        ],

        /*----------------------- / Manage Versions----------------------------------------*/

        /*-----------------------Manage Articles----------------------------------------*/

        'manage_articles' => [
            'title' => "Manage Articles",
            'access_ids' => []
        ],

        'view_only_manage_articles' => [
            'title' => "Read",
            'access_ids' => [
                'manage.article.read.list',
                'manage.project.version.read.get_support_data'
            ],
            'dependencies' => [
            ],
            'parent' => 'manage_articles'
        ],
        // Add project
        'add_article' => [
            'title' => 'Create',
            'access_ids' => [
                'manage.article.read.support_data',
                'manage.article.write.create',
                'manage.article.read.update.data'
            ],
            'dependencies' => [
                'view_only_manage_articles'
            ],
            'parent' => 'manage_articles'
        ],

        // edit project
        'update_article' => [
            'title' => 'Update',
            'access_ids' => [
                'manage.article.read.update.data',
                'manage.article.write.update',
                'manage.article.write.update_parent'
            ],
            'dependencies' => [
                'view_only_manage_articles'
            ],
            'parent' => 'manage_articles'
        ],

        // delete project
        'delete_article' => [
            'title' => 'Delete',
            'access_ids' => [
                'manage.article.write.delete'
            ],
            'dependencies' => [
                'view_only_manage_articles'
            ],
            'parent' => 'manage_articles'
        ],

        /*----------------------- / Manage Articles----------------------------------------*/


        /*-----------------------Manage Languages----------------------------------------*/

        'manage_languages' => [
            'title' => "Manage Languages",
            'access_ids' => []
        ],

        'view_only_manage_languages' => [
            'title' => "Read",
            'access_ids' => [
                'manage.language.read.list'
            ],
            'dependencies' => [
            ],
            'parent' => 'manage_languages'
        ],
        // Add language
        'add_language' => [
            'title' => 'Create',
            'access_ids' => [
                'manage.language.read.support_data',
                'manage.language.write.create',
            ],
            'dependencies' => [
                'view_only_manage_languages'
            ],
            'parent' => 'manage_languages'
        ],

        // edit language
        'update_language' => [
            'title' => 'Update',
            'access_ids' => [
                'manage.language.read.update.data',
                'manage.language.write.update'
            ],
            'dependencies' => [
                'view_only_manage_languages'
            ],
            'parent' => 'manage_languages'
        ],

        // delete language
        'delete_language' => [
            'title' => 'Delete',
            'access_ids' => [
                'manage.language.write.delete'
            ],
            'dependencies' => [
                'view_only_manage_languages'
            ],
            'parent' => 'manage_languages'
        ],
        /*----------------------- / Manage Articles----------------------------------------*/

        /*----------------------- Manage Users  ----------------------------------------*/
        // Manage Users
        'manage_users' => [
            'title' => "Manage Users",
            'access_ids' => []
        ],
        'view_only_manage_users' => [
            'title' => "Read",
            'access_ids' => [
                'manage.user.read.datatable.list'
            ],
            'dependencies' => [
            ],
            'parent' => 'manage_users'
        ],
        // Add User
        'add_user' => [
            'title' => 'Create',
            'access_ids' => [
                'manage.user.write.create',
                'manage.user.read.create.support_data'
            ],
            'dependencies' => [
                'view_only_manage_users'
            ],
            'parent' => 'manage_users'
        ],
        // Edit User
        'edit_user' => [
            'title' => 'Update',
            'access_ids' => [
                'manage.user.read.edit_suppport_data',
                'manage.user.write.update_process'
            ],
            'dependencies' => [
                'view_only_manage_users'
            ],
            'parent' => 'manage_users'
        ],
        // Delete User
        'delete_and_restore_user' => [
            'title' => 'Delete',
            'access_ids' => [
                'manage.user.write.delete',
                'manage.user.write.restore'
            ],
            'dependencies' => [
                'view_only_manage_users'
            ],
            'parent' => 'manage_users'
        ],
        'view_user_details' => [
            'title' => "View Details",
            'access_ids' => [
                'manage.user.read.detail.data'
            ],
            'dependencies' => [
                'view_only_manage_users'
            ],
            'parent' => 'manage_users'
        ],
        /*----------------------- Manage Users  ----------------------------------------*/

        /*----------------------- Manage Roles ----------------------------------------*/

        // Roles
        'manage_roles' => [
            'title' => "Manage Roles",
            'access_ids' => []
        ],
        'view_only_manage_role' => [
            'title' => "Read",
            'access_ids' => [
                'manage.user.role_permission.read.list'
            ],
            'dependencies' => [
            ],
            'parent' => 'manage_roles'
        ],
        'add_role' => [
            'title' => 'Create',
            'access_ids' => [
                'manage.user.role_permission.read.add_support_data',
                'manage.user.role_permission.read.using_id',
                'manage.user.role_permission.write.role.create'
            ],
            'dependencies' => [
                'view_only_manage_role'
            ],
            'parent' => 'manage_roles'
        ],
        'manage_role_permission' => [
            'title' => 'Update',
            'access_ids' => [
                'manage.user.role_permission.read',
                'manage.user.role_permission.write.create'
            ],
            'dependencies' => [
                'view_only_manage_role'
            ],
            'parent' => 'manage_roles'
        ],
        'delete_role' => [
            'title' => 'Delete',
            'access_ids' => [
                'manage.user.role_permission.write.delete'
            ],
            'dependencies' => [
                'view_only_manage_role'
            ],
            'parent' => 'manage_roles'
        ],
        /*----------------------- Manage Roles ----------------------------------------*/

        /*----------------------- Configuration & Setings----------------------------------------*/

        'confguration_setting' => [
            'title' => "General Settings",
            'access_ids' => []
        ],
        'manage_confguration_setting' => [
            'title' => "Settings",
            'access_ids' => [
                'manage.configuration.get.support.data',
                'manage.configuration.process',
                'media.upload.read_logo',
                'media.upload.read_favicon'
            ],
            'parent' => 'confguration_setting'
        ],

        /*----------------------- / Configuration & Setings----------------------------------------*/

        /*----------------------- Activity Log ----------------------------------------*/
        'activity_log' => [
            'title' => "Activity Log",
            'access_ids' => []
        ],
        'view_activity_log' => [
            'title' => "Read",
            'access_ids' => [
                'manage.activity_log.read.list',
                'manage.activity_log.action_type.read.data'
            ],
            'parent' => 'activity_log'
        ],

        /*-----------------------/ Activity Log ----------------------------------------*/

    ],
    /*'entities' => [
        'project' => [
            'model' => 'App\Yantrana\Components\Project\Models\ProjectUserModel',
            'id_column' => 'projects__id',
            'permission_column' => '__permissions',
            'user_id_column' => 'users__id'
        ]
    ]*/
];