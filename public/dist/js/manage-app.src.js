(function() {
'use strict';

  angular.module('ManageApp', [
    'ngMessages',
    'ngAnimate',
    'ngSanitize',
    'ui.router',
    'ui.tree',
    'ngNotify',
    'ngDialog',
    'angular-loading-bar',
    'selectize',
    'NgSwitchery',
    'lw.core.utils',
    'lw.security.main',
    'lw.auth',
    'lw.data.datastore',
    'lw.data.datatable',
    'lw.form.main',
    'app.service',
    'app.http',
    'app.notification',
    'app.form',
    'app.directives',
    'app.fileUploader',
    'ManageApp.master',
    'app.UploaderDataService',
    'app.UploaderEngine',

    'ManageApp.ManageUserDataService',
    'ManageApp.users',

    'CommonApp.CommonUserDataService',
    'CommonApp.users',

    'Manage-app.users',
    'ManageApp.UserDataService',

    'ManageApp.ConfigurationDataService',
    'ManageApp.configuration',

    'app.RolePermissionDataServices',
    'app.RolePermissionEngine',

 	'app.ActivityDataServices',
	'app.ActivityEngine',

	'app.ProjectDataServices',
	'app.ProjectEngine',

	'app.ArticleDataServices',
	'app.ArticleEngine',

	'app.LanguageDataServices',
    'app.LanguageEngine',

	'app.VersionDataServices', 
	'app.VersionEngine'

  ]).
  //constant('__ngSupport', window.__ngSupport).
  run([
    '__Auth', '$state', '$rootScope', '$transitions','$trace', function(__Auth, $state, $rootScope, $transitions, $trace) {

        _.delay(function() {

            __Auth.verifyRoute($state);
            /* $rootScope.$on('$viewContentLoading', function(event, viewConfig) {

                var accessObject = $state.current;

                if( accessObject  && _.has( accessObject, 'loginRequired' ) && accessObject.loginRequired === false) {

                    if (__Auth.isLoggedIn()) {
                        $state.go( 'project' );
                    }

                    event.preventDefault();

                    return false;
                }
            });
 */
        }, 100);

        $rootScope.__ngSupport = window.__ngSupport;

    }
  ]).
  config([
    '$stateProvider', '$urlRouterProvider', '$interpolateProvider','$compileProvider', routes
  ]);


  /**
    * Application Routes Configuration
    *
    * @inject $stateProvider
    * @inject $urlRouterProvider
    * @inject $interpolateProvider
    * @inject $compileProvider
    *
    * @return void
    *---------------------------------------------------------------- */

  function routes($stateProvider, $urlRouterProvider, $interpolateProvider, $compileProvider) {

    if( window.appConfig && window.appConfig.debug === false) {
        $compileProvider.debugInfoEnabled(false);
    }

    $interpolateProvider.startSymbol('[[');
    $interpolateProvider.endSymbol(']]');

    $urlRouterProvider.otherwise(function($injector, $location, $transitions) {
       var state = $injector.get('$state'),
            auth = $injector.get('__Auth'),
            redirectState = __globals.appTemps('stateViaRoute');
        if(_.has(redirectState, 'stateName')
            && !_.isUndefined(redirectState.stateName)
            && !_.isEmpty(redirectState.stateName)) {
            if (_.isEmpty(redirectState.stateParams)) {
                state.go(redirectState.stateName);
            } else {
                state.go(redirectState.stateName, redirectState.stateParams);
            }
        } else {
            state.go('project');
        }

        return $location.path();
    });

    //state configurations
    $stateProvider

        .state('base', {
            abstract: true,
            resolve: {
                baseData: ['$stateParams', 'BaseDataService', function($stateParams, BaseDataService) {
                    return BaseDataService.getBaseData('account_logged');
                }]
        }})

        // login
        .state('login',
            __globals.stateConfig('/login', 'user.login', {
                parent : 'base',
                access  : {
                    authority : 'user.login.process'
                }
            }) )

        // forgot password
        .state('forgot_password',
            __globals.stateConfig('/forgot-password', 'user.forgot-password', {
                parent : 'base',
                access  : {
                    authority : 'user.forgot_password.process'
                }
            })
        )

		// forgot password
        .state('reset_password',
            __globals.stateConfig('/reset-password/{reminderToken}', 'user.reset-password', {
                parent  : 'base',
                access  : {
                    authority : 'user.reset_password.process'
                }
            })
        )

        // Forgot Password Success
        .state('forgot_password_sucess',
            __globals.stateConfig('/forgot-password', 'user.forgot-password-success')
        )

        // home
        .state('home',
             __globals.stateConfig('/home', 'home', {
                access  : {
                    authority : 'public.app'
                },
                parent : 'base'
              }
            )
        )

        // invalid request
        .state('invalid_request', __globals.stateConfig('/invalid-request',
            'errors/invalid-request'
        ))

        // not found
        .state('not_found', __globals.stateConfig('/not-found',
            'errors.manage-not-exist'
        ))

        // not exist
        .state('not_exist', __globals.stateConfig('/not-exist',
            'errors.manage-not-exist'
        ))

        // unauthorized
        .state('unauthorized', __globals.stateConfig('/unauthorized',
            'errors.unauthorized'
		))

        // users
        .state('users',
             __globals.stateConfig('/users', 'user/manage/list', {
                controller : 'ManageUsersController as manageUsersCtrl',
                access  : {
                    authority : 'manage.user.read.datatable.list'
                },
                parent : 'base'
              }
            )
        )

        // RolePermission list
        .state('role_permission',
            __globals.stateConfig('/role-permissions', 'user/role-permission/list', {
            access  : {
                authority:'manage.user.role_permission.read.list'
            },
            controller : 'RolePermissionListController as rolePermissionListCtrl',
            parent : 'base'
        } ))

        // profile
        .state('profile',
             __globals.stateConfig('/profile', 'user/manage-profile', {
                access  : {
                    authority : 'user.profile.update'
                },
                parent : 'base'
              }
            )
        )

        // profile edit
        .state('profileEdit',
             __globals.stateConfig('/profile/edit', 'user/profile-edit', {
                access  : {
                    authority : 'user.profile.update.process'
                },
                parent : 'base'
              }
            )
        )

        // change password
        .state('changePassword',
             __globals.stateConfig('/change-password', 'user/change-password', {
                access  : {
                    authority : 'user.change_password.process'
                },
                parent : 'base'
              }
            )
        )

        // change email
        .state('changeEmail',
             __globals.stateConfig('/change-email', 'user/change-email', {
                access  : {
                    authority : 'user.change_email.process'
                },
                parent : 'base'
              }
            )
        )

        // activity log
        .state('activity_log',
			__globals.stateConfig('/activity-log', 'activity/activity-log', {
            	controller  : 'ActivityLogListController as activityLogListCtrl',
                parent : 'base',
                access  : {
                    authority : 'manage.activity_log.read.list'
                }
        } ))

        // configuration general
        .state('configuration_general',
			__globals.stateConfig('/general', 'configuration.general', {
                parent : 'base',
                controller  : 'GeneralController as generalCtrl',
                access  : {
                    authority : 'manage.configuration.process'
                },
                resolve: {
                    getGeneralData: ["ConfigurationDataService", function(ConfigurationDataService) {
						return ConfigurationDataService
							.readConfigurationData(1) // general form
							.then(function(response) {
							return response;
						});
                    }]
                },
                parent : 'base'
            }) )


        // Project list
        .state('project',
            __globals.stateConfig('/projects', 'project/list', {
            access  : {
            	authority : 'manage.project.read.list'
            },
            controller : 'ProjectListController as projectListCtrl',
            parent : 'base'
        } ))

        // Article list
        .state('project_articles',
            __globals.stateConfig('/project/:projectUid/:versionUid/articles', 'article/list', {
            access  : {
            	authority : 'manage.article.read.list'
            },
            controller : 'ArticleListController as articleListCtrl',
            parent : 'base',
            resolve : {
                GetVersionDetails : ['VersionDataService', '$stateParams', function(VersionDataService, $stateParams) {
                    return VersionDataService
                            .getVersionSupportData($stateParams.projectUid, $stateParams.versionUid);
                }],
                GetArticles : ['ArticleDataService', '$stateParams', function(ArticleDataService, $stateParams) {
                    return ArticleDataService
                            .getArticles($stateParams.projectUid, $stateParams.versionUid);
                }]
            }
        } ))

        // Article add dialog
       .state('project_article_add',
            __globals.stateConfig('/project/:projectUid/:versionUid/article/:requestType/add', 'article/add', {
            access  : {
                authority : 'manage.article.write.create'
            },
            controller : 'ArticleAddController as ArticleAddCtrl',
            parent : 'base',
            resolve : {
                articleAddData : ['ArticleDataService', '$stateParams', function(ArticleDataService, $stateParams) {
                    return ArticleDataService.getAddSupportData($stateParams.projectUid, $stateParams.versionUid);
                }],
                GetVersionDetails : ['VersionDataService', '$stateParams', function(VersionDataService, $stateParams) {
                    return VersionDataService
                            .getVersionSupportData($stateParams.projectUid, $stateParams.versionUid);
                }]
            }

        } ))

       // add sub article
       .state('project_subarticle_add',
            __globals.stateConfig('/project/:projectUid/:versionUid/:prevArticle/article/:requestType/add', 'article/add', {
            access  : {
                authority : 'manage.article.write.create'
            },
            controller : 'ArticleAddController as ArticleAddCtrl',
            parent : 'base',
            resolve : {
                articleAddData : ['ArticleDataService', '$stateParams', function(ArticleDataService, $stateParams) {
                    return ArticleDataService.getAddSupportData($stateParams.projectUid, $stateParams.versionUid);
                }]
            },
            parent : 'base'

        } ))

        // Article edit dialog
       .state('project_article_edit',
       		__globals.stateConfig( '/project/:projectUid/:versionUid/article/:articleIdOrUid/edit', 'article/edit', {
            access  : {
                authority : 'manage.article.read.update.data'
            },
            controller : 'ArticleEditController as articleEditCtrl',
            parent : 'base',
            resolve : {
                articleEditData : ['ArticleDataService', '$stateParams', function(ArticleDataService, $stateParams) {
                    return ArticleDataService
                            .getEditSupportData($stateParams.articleIdOrUid, $stateParams.projectUid, $stateParams.versionUid);
                }]
            },
            parent : 'base'
        } ))

        // Article list
        .state('article',
            __globals.stateConfig('/articles', 'article/list', {
            access  : {
            	authority : 'manage.article.read.list'
            },
            controller : 'ArticleListController as articleListCtrl',
            parent : 'base'
        } ))

        // Article add dialog
       .state('article_add',
            __globals.stateConfig('/article-add', 'article/add', {
            access  : {
                authority : 'manage.article.write.create'
            },
            controller : 'ArticleAddController as ArticleAddCtrl',
            parent : 'base',
            resolve : {
                articleAddData : ['ArticleDataService', function(ArticleDataService) {
                    return ArticleDataService.getAddSupportData();
                }]
            }

        } ))

        // Article edit dialog
       .state('article_edit', __globals.stateConfig( '/:articleIdOrUid/edit', 'article/edit', {
            access  : {
                authority : 'manage.article.write.update'
            },
            controller : 'ArticleEditController as articleEditCtrl',
            parent : 'base',
            resolve : {
                articleEditData : ['ArticleDataService', '$stateParams', function(ArticleDataService, $stateParams) {
                    return ArticleDataService
                            .getEditSupportData($stateParams.articleIdOrUid);
                }]
            }
        } ))

       	// Article details dialog
       	.state('article_details', __globals.stateConfig( '/article/:articleIdOrUid/:requestType/details', 'article/details', {
            access  : {
                authority : 'manage.article.read.details'
            },
            controller : 'ArticleDetailsController as ArticleDetailsCtrl',
            parent : 'base',
            resolve : {
                ArticleData : ['ArticleDataService', '$stateParams', function(ArticleDataService, $stateParams) {
					return ArticleDataService.getArticleDetails($stateParams.articleIdOrUid);
				}]
            }
        } ))


         // Language list
        .state('language',
            __globals.stateConfig('/languages', 'language/list', {
            access  : {
                authority : 'manage.language.read.list'
            },
            controller : 'LanguageListController as languageListCtrl',
            parent : 'base'
        } ))
        
         // Version list
        .state('project_versions', 
            __globals.stateConfig('/project/{projectIdOrUid}/versions', 'version/list', {
            access  : {
                authority : 'manage.project.version.read.list'
            },
            parent : 'base',
            controller : 'VersionListController as versionListCtrl',
            resolve : {
                ProjectInfo : ["VersionDataService", "$stateParams", function(VersionDataService, $stateParams) {
                    return VersionDataService.getProjectInfo($stateParams.projectIdOrUid);
                }],
                GetVersions : ["VersionDataService", "$stateParams", function(VersionDataService, $stateParams) {
                    return VersionDataService.getVersions($stateParams.projectIdOrUid);
                }]
            }
        } ))

        
        // Version add dialog
        .state('version.add', 
            __globals.stateConfig('/project-add', null, {
            access  : {
                authority : 'manage.project.version.write.create'
            },
            controller : 'VersionAddDialogController',
            resolve : {
                versionAddData : function(VersionDataService) {
                    return VersionDataService.getAddSupportData();
                }
            }

        } ))

        
        // Version edit dialog
        .state('version.edit', __globals.stateConfig( '/:projectIdOrUid/edit', null, {
            access  : {
                authority : 'manage.project.version.write.update'
            },
            controller : 'VersionEditDialogController as versionEditDialogCtrl',
            resolve : {
                versionEditData : function(VersionDataService, $stateParams) {
                    return VersionDataService
                            .getEditSupportData($stateParams.projectIdOrUid);
                }
            }
 
         } ))


        ;
    };
})();;
(function() {
'use strict';

	/*
	 ManageController
	-------------------------------------------------------------------------- */

	angular
        .module('ManageApp.master', [])
        .controller('ManageController', 	[
			'$rootScope',
            '__DataStore',
            '$scope',
            '__Auth',
            'appServices',
            'appNotify',
			'__Form',
			'$state',
			'appToastNotify',
            'ConfigurationDataService',
            ManageController
	 	]);

 /**
	* ManageController for manage page application
	*
	* @inject $rootScope
	* @inject __DataStore
	* @inject $scope
	* @inject __Auth
	* @inject appServices
	* @inject appNotify
	*
	* @return void
	*-------------------------------------------------------- */

	function ManageController($rootScope, __DataStore, $scope, __Auth,  appServices, appNotify, __Form, $state, appToastNotify, ConfigurationDataService) {

	 	var scope 	= this;

        scope.dropdown_menu = __globals.getAppImmutables('dropdown_menu');

        scope.pageStatus    = false;

		scope.refreshAuthObj = function() {
            
            __Auth.refresh(function(authInfo) {

		 		scope.auth_info = authInfo;
		 	});

        };
		scope.refreshAuthObj();        

        scope.notify = __globals.getAppImmutables('notifyToAdmin');
        scope.restrict_user_email_update = __globals.getAppImmutables('restrict_user_email_update');

        scope.unhandledError = function() {

            appNotify.error(__globals.getReactionMessage(19)); // Unhanded errors

        };

        $rootScope.isAdmin = function() {
           return scope.auth_info.designation === 1;
        };

        $rootScope.$on('auth_info_updated', function (event, args) {
            $rootScope.auth_info = args.auth_info;
            if (!_.isEmpty(args.userFullName)) {
                scope.userUpdateData = args.userFullName;
            }
            scope.auth_info = $rootScope.auth_info;
            
        });

		$rootScope.$on('lw.events.logged_in_user', function () {
	 		scope.refreshAuthObj();
        });

        $rootScope.$on('lw.events.state.change_start', function () {
	 		appServices.closeAllDialog();
        });

        $rootScope.$on('lw.datastore.event.post.started', __globals.showButtonLoader);

        $rootScope.$on('lw.datastore.event.fetch.started', __globals.showFormLoader);

        $rootScope.$on('lw.form.event.process.started');

        $rootScope.$on('lw.form.event.fetch.started', __globals.showFormLoader );

        $rootScope.$on('lw.datastore.event.fetch.finished', __globals.hideFormLoader );

        $rootScope.$on('lw.datastore.event.post.finished', __globals.hideButtonLoader);

        $rootScope.$on('lw.form.event.process.finished', __globals.hideButtonLoader);

        $rootScope.$on('lw.datastore.event.fetch.error', scope.unhandledError );

        $rootScope.$on('lw.form.event.process.error', scope.unhandledError );

		$rootScope.$on('$stateChangeSuccess', function($stateEvent, $stateInfo) {

            var scrollOffsets  = __globals.getScrollOffsets(),
        	yOffset = Math.round(scrollOffsets.y);
           	// document.body.scrollTop = document.documentElement.scrollTop = 0;
           	$('html, body').animate({scrollTop:0}, yOffset < 500 ? 500 : yOffset);
        });

        // Dialog Opened Event
        $rootScope.$on('ngDialog.opened', function (e, $dialog) {
            _.defer(function(){
                $('.ngdialog').scrollTop(0);
            });
        });

		/**
		* Check if user logged in
		*
		* @return boolean
		*---------------------------------------------------------------- */

		scope.isLoggedIn = function() {
			return __Auth.isLoggedIn();   // isLoggedIn
		};

        /**
        * Check if user logged in
        *
        * @return boolean
        *---------------------------------------------------------------- */

        scope.logoutUser = function() {

           	__Auth.registerIntended("dashboard");

            __DataStore.post('user.logout')
                    .success(function(responseData) {
					
                    if (responseData.reaction == 1) {
                        // __globals.setCookie('auth_access_token', '');

                        __Auth.checkOut({},function(authInfo) {
                            $state.go('login');
                        });
                    }
                    
                });
        };

        scope.showGeneralSetting = function() {

            ConfigurationDataService
                .readConfigurationData(1)
                .then(function(responseData) {

                    var logo_background_color = responseData.data.configuration.logo_background_color;
                  	
                    appServices.showDialog({
                        'responseData' : responseData
                    }, {
                        templateUrl : __globals.getTemplateURL('configuration.general')
                    }, function(promiseObj) {
            
                        $('#lwchangeBgHeaderColor').css('background', "#"+logo_background_color);
                    });
                });
        };

        scope.themeColors = __globals.getAppImmutables('config')['theme_colors'];

        /**
        * Set Theme color
        *---------------------------------------------------------------- */
        scope.setThemeColor = function(colorName) {
            __DataStore.fetch({
                'apiURL': 'theme_color',
                'colorName': colorName
            }).success(function(responseData) {
                location.reload();
            });
        }

        /**
        * Show Hide Theme Color
        *---------------------------------------------------------------- */
        scope.showHideThemeContainer = function() {
            if (!$('.lw-theme-color-container').hasClass('lw-theme-container-active')) {
                $('.lw-theme-color-container').addClass('lw-theme-container-active');
                $('.lw-switch i:first').replaceWith("<span>&times;</span>");
            } else {
                $('.lw-theme-color-container').removeClass('lw-theme-container-active');
                $('.lw-switch span:first').replaceWith("<i class='fa fa-cog'></i>");
            }
        }
	};

})();;
/*!
*  Component  : Manage Users
*  File       : ManageUserDataService.js  
*  Engine     : ManageUserDataService 
----------------------------------------------------------------------------- */

(function(window, angular, undefined) {

    'use strict';

    angular
        .module('ManageApp.ManageUserDataService', [])

        /**
          Manage User Data Service  
        ---------------------------------------------------------------- */
        .service('ManageUserDataService',[
            '$q', 
            '__DataStore', 
            '__Form',
            'appServices',
            ManageUserDataService
        ]);

        function ManageUserDataService($q, __DataStore,__Form, appServices) {
            
            /*
            Get User Info
            -----------------------------------------------------------------*/
            this.getUserInfo = function(userId) {

                //create a differed object          
                var defferedObject = $q.defer();   
   
                __DataStore.fetch({
                        'apiURL' : 'manage.user.read.info',
                        'userId' : userId
                    }).success(function(responseData) {
                            
                    appServices.processResponse(responseData, null, function(reactionCode) {

                        //this method calls when the require        
                        //work has completed successfully        
                        //and results are returned to client        
                        defferedObject.resolve(responseData);  

                    }); 

                });       

               //return promise to caller          
               return defferedObject.promise; 
            }; 

            /*
            Get User Add Support Data
            -----------------------------------------------------------------*/
            this.getUserAddSupportData = function() {

                //create a differed object          
                var defferedObject = $q.defer();   
   
                __DataStore.fetch('manage.user.read.create.support_data')
                    .success(function(responseData) {
                            
                    appServices.processResponse(responseData, null, function(reactionCode) {

                        //this method calls when the require        
                        //work has completed successfully        
                        //and results are returned to client        
                        defferedObject.resolve(responseData);  

                    }); 

                });       

               //return promise to caller          
               return defferedObject.promise; 
            }; 

            /*
            Get User User Permissions
            -----------------------------------------------------------------*/
            this.getUserPermissions = function(userId) {

                //create a differed object          
                var defferedObject = $q.defer();   
   
                __DataStore.fetch({
                    'apiURL' : 'manage.user.read.get_user_permissions',
                    'userId' : userId
                }).success(function(responseData) {
                            
                    appServices.processResponse(responseData, null, function(reactionCode) {

                        //this method calls when the require        
                        //work has completed successfully        
                        //and results are returned to client        
                        defferedObject.resolve(responseData.data);  

                    }); 

                });       

               //return promise to caller          
               return defferedObject.promise; 
            };

            /*
            Get User User Permissions
            -----------------------------------------------------------------*/
            this.getUserDetailData = function(userId) {

                //create a differed object          
                var defferedObject = $q.defer();   
   
                __DataStore.fetch({
                    'apiURL' : 'manage.user.read.detail.data',
                    'userID' : userId
                }).success(function(responseData) {
                            
                    appServices.processResponse(responseData, null, function(reactionCode) {

                        //this method calls when the require        
                        //work has completed successfully        
                        //and results are returned to client        
                        defferedObject.resolve(responseData.data);  

                    }); 

                });       

               //return promise to caller          
               return defferedObject.promise; 
            };

            /*
            Get User Edit Data
            -----------------------------------------------------------------*/
            this.getUserEditData = function(userId) {

                //create a differed object          
                var defferedObject = $q.defer();   
   
                __DataStore.fetch({
                    'apiURL' : 'manage.user.read.edit_suppport_data',
                    'userId' : userId
                }).success(function(responseData) {
                            
                    appServices.processResponse(responseData, null, function(reactionCode) {

                        //this method calls when the require        
                        //work has completed successfully        
                        //and results are returned to client        
                        defferedObject.resolve(responseData.data);  

                    }); 

                });       

               //return promise to caller          
               return defferedObject.promise; 
            };
        }
    ;

})(window, window.angular);;
/*!
*  Component  : Manage User
*  File       : ManageUserEngine.js  
*  Engine     : ManageUserEngine 
----------------------------------------------------------------------------- */

(function(window, angular, undefined) {

    'use strict';

    angular
        .module('ManageApp.users', [])

        /**
         * Manage Users Controller
         *
         * @inject $scope
         * @inject __DataStore
         * @inject appServices
         *
         * @return void
         *-------------------------------------------------------- */
        .controller('ManageUsersController',   [
            '$scope', 
            '__DataStore',
            'appServices',
            'ManageUserDataService',
            'ConfigurationDataService',
            function ManageUsersController($scope, __DataStore, appServices, ManageUserDataService, ConfigurationDataService) {

                var dtUsersColumnsData = [
                    {
                        "name"      : null,
                        "template"  : "#profileImageColumnTemplate"
                    },
                    {
                        "name"      : "name",
                        "template"  : "#userNameColumnTemplate",
                        "orderable" : true
                    }, 
					{
                        "name"      : "username",
                        "orderable" : true
                    },
                    {
                        "name"      : "email",
                        "orderable" : true
                    },
                    {
                        "name"      : "updated_at",
                        "template"  : "#userUpdatedDateColumnTemplate",
                        "orderable" : true
                    },
                    {
                        "name"      : "user_role",
                        "orderable" : true
                    },
                    {
                        "name"      : null,
                        "template"  : "#userActionColumnTemplate"
                    }
                ],
                dtDeletedUsersColumnsData = [
                    {
                        "name"      : null,
                        "template"  : "#profileImageColumnTemplate"
                    },
                    {
                        "name"      : "name",
                        "template"  : "#userNameColumnTemplate",
                        "orderable" : true
                    }, 
					{
                        "name"      : "username",
                        "orderable" : true
                    },
                    {
                        "name"      : "email",
                        "orderable" : true
                    },
                    {
                        "name"      : "updated_at",
                        "template"  : "#userUpdatedDateColumnTemplate",
                        "orderable" : true
                    },
                    {
                        "name"      : "user_role",
                        "orderable" : true
                    },
                    {
                        "name"      : null,
                        "template"  : "#userActionColumnTemplate"
                    }
                ],
                tabs    = {
                    'active'    : {
                        id      : 'activeUsersTabList',
                        status  : 1 // Active
                    },
                    'inactive'    : {
                        id      : 'inactiveUsersTabList',
                        status  : 2 // Inactive
                    },
                    'deleted'    : {
                        id      : 'deletedUsersTabList',
                        status  : 5 // Soft Deleted
                    }
                },
                currentStatus   = 1,
                scope           = this;


                // Manage users tab action
                // When clicking on tab, its related tab data load on same page

                $('#manageUsersTabs a').click(function (e) {

                    e.preventDefault();

                    var $this       = $(this),
                        tabName     = $this.attr('aria-controls'),
                        selectedTab = tabs[tabName];

                    // Check if selected tab exist    
                    if (!_.isEmpty(selectedTab)) {

                        $(this).tab('show')

                        currentStatus = selectedTab.status;
                        scope.getUsers(selectedTab.id, selectedTab.status);

                    }
                    
                });

                /**
                  * Get users as a datatable source  
                  *
                  * @param string tableID
                  * @param number status
                  *
                  * @return void
                  *---------------------------------------------------------------- */
                                
                scope.getUsers   = function(tableID, status) {

                    // destroy if existing instatnce available
                    if (scope.usersListDataTable) {
                        scope.usersListDataTable.destroy();
                    }

                    scope.usersListDataTable = __DataStore.dataTable('#'+tableID, { 
                        url         : {
                            'apiURL'    : 'manage.user.read.datatable.list',
                            'status'    : status
                        }, 
                        dtOptions   : {
                            "searching" : true,
                            "pageLength" : 25,
                            "order"     : [[ 1, "asc" ]]
                        },
                        columnsData : status == 5 ? dtDeletedUsersColumnsData : dtUsersColumnsData, 
                        scope       : $scope

                    });

                };

                // load initial data for first tab
                scope.getUsers('activeUsersTabList', 1);

                /*
                  Reload current datatable
                  ------------------------------------------------------------------- */
                
                scope.reloadDT = function() {
                    __DataStore.reloadDT(scope.usersListDataTable);
                };

                /**
                  * Delete user 
                  *
                  * @param number userID
                  * @param string userName
                  *
                  * @return void
                  *---------------------------------------------------------------- */

                scope.delete = function(userID, userName, deleteType) {
                    
                    scope.deletingUserName = unescape(userName);

                    _.defer(function(){

                        var $lwUserDeleteConfirmTextMsg = $('#lwUserDeleteConfirmTextMsg');

                        var $lwUserPerDeleteConfirmTextMsg = $('#lwUserPerDeleteConfirmTextMsg');

                        if (deleteType == 1) { // Soft delete
                            scope.deleteText = $lwUserDeleteConfirmTextMsg .attr('data-message');
                            scope.deleteConfirmBtnText = $lwUserDeleteConfirmTextMsg .attr('data-delete-button-text');
                            scope.successMsgText = $lwUserDeleteConfirmTextMsg .attr('success-msg');
                        } else { // Permanent delete
                            scope.deleteText = $lwUserPerDeleteConfirmTextMsg .attr('data-message');
                            scope.deleteConfirmBtnText = $lwUserPerDeleteConfirmTextMsg .attr('data-delete-button-text');
                            scope.successMsgText = $lwUserPerDeleteConfirmTextMsg.attr('success-msg');
                        }

                    });

                   _.defer(function(){

                        __globals.showConfirmation({
                            html                : scope.deleteText,
                            confirmButtonText   : scope.deleteConfirmBtnText
                        },
                        function() {

                            __DataStore.post({
                                'apiURL'  :'manage.user.write.delete',
                                'userID'  : userID,
                            })
                            .success(function(responseData) {
                            
                                var message = responseData.data.message;

                                appServices.processResponse(responseData, {

                                        error : function() {

                                            __globals.showConfirmation({
                                                title   : 'Deleted!',
                                                text    : message,
                                                type    : 'error'
                                            });

                                        }
                                    },
                                    function() {

                                        __globals.showConfirmation({
                                            title   : 'Deleted!',
                                            text    : scope.successMsgText,
                                            type    : 'success'
                                        });
                                        scope.reloadDT();   // reload datatable

                                    }
                                );    

                            })

                        })

                   });

                };

                /**
                  * Restore deleted user 
                  *
                  * @param number userID
                  * @param string userName
                  *
                  * @return void
                  *---------------------------------------------------------------- */

                scope.restore = function(userID, userName) {

                    scope.restoringUserName = unescape(userName);

                    _.defer(function(){

                        var $lwUserRestoreConfirmTextMsg = $('#lwUserRestoreConfirmTextMsg');

                        __globals.showConfirmation({
                            text                : $lwUserRestoreConfirmTextMsg .attr('data-message'),
                            confirmButtonText   : $lwUserRestoreConfirmTextMsg .attr('data-restore-button-text')
                        },
                        function() {

                            __DataStore.post({
                                'apiURL'  : 'manage.user.write.restore',
                                'userID'  : userID,
                            })
                            .success(function(responseData) {
                            
                                var message = responseData.data.message;

                                appServices.processResponse(responseData, {

                                        error : function() {
                                            __globals.showConfirmation({
                                                title   : 'Restore!',
                                                text    : message,
                                                type    : 'error'
                                            });
                                        }
                                    },
                                    function() {

                                        __globals.showConfirmation({
                                                title   : 'Restore!',
                                                text    : message,
                                                type    : 'success'
                                            });
                                        scope.reloadDT();   // reload datatable

                                    })   

                                })

                            })

                    });

                };

                /**
                  * Change password of user by Admin 
                  *
                  * @param number userID
                  * @param number name
                  *
                  * @return void
                  *---------------------------------------------------------------- */

                scope.changePassword = function (userID, name) {
                    
                    // open change password dialog
                    appServices.showDialog({
                            userID : userID,
                            name   : unescape(name)
                        },
                        {   
                            templateUrl : __globals.getTemplateURL('user.manage.change-password'),
                            controller: 'ManageUserChangePasswordController as userChangePassword'
                        },
                        function(promiseObj) {

                        });
                };

                /**
                  * Show add new user dialog
                  *
                  * @return void
                  *---------------------------------------------------------------- */

                scope.showAddNewDialog = function () {
                    
                    appServices.showDialog(
                    {
                        'showRoleSelectBox' : true
                    },
                    {   
                        templateUrl : __globals.getTemplateURL('user.manage.add-dialog'),
                    },
                    function(promiseObj) {

                        // Check if category updated
                        if (_.has(promiseObj.value, 'user_added') 
                            && promiseObj.value.user_added == true && currentStatus == 1) {
                            scope.reloadDT();
                        }

                    });
                };

                /**
                  * Edit User Dialog
                  *
                  * @return void
                  *---------------------------------------------------------------- */

                scope.editUserDialog = function(userId, name) {
                    appServices.showDialog(
                    {
                        'userId' : userId,
                        'name'   : name
                    },
                    {   
                        templateUrl : 'user.manage.edit-dialog',
                        controller : 'EditUserDialogController as EditUserDialogCtrl',
                        resolve : {
                            EditUserData : function() {
                                return ManageUserDataService
                                        .getUserEditData(userId);
                            }
                        }
                    },
                    function(promiseObj) {

                        // Check if category updated
                        if (_.has(promiseObj.value, 'user_updated') 
                            && promiseObj.value.user_updated == true) {
                            scope.reloadDT();
                        }

                    });
                };

                scope.showUsersConfigurationDialog = function() {

                    ConfigurationDataService
                        .readConfigurationData(5)
                        .then( function(responseData) {

                        appServices.showDialog({
                            'responseData' : responseData
                        }, {
                            templateUrl : __globals.getTemplateURL('configuration.users')
                        }, function(promiseObj) {

                        });

                    });

                };

                /**
                  * Show User Permission Dialog
                  *
                  * @return void
                  *---------------------------------------------------------------- */
                scope.usersPermissionDialog = function(userId, fullName) {

                    appServices.showDialog({
                        'userId'      : userId,
                        'fullName'    : _.unescape(fullName)
                    }, {
                        templateUrl : __globals.getTemplateURL('user.manage.user-dynamic-permission'),
                        controller : 'ManageUsersDynamicPermissionController as manageUsersDynamicPermissionCtrl',
                        resolve : {
                            UserPermission : function() {
                                return ManageUserDataService.getUserPermissions(userId);
                            }
                        }
                    }, function(promiseObj) {

                    });
                };

                /**
                  * Show User Permission Dialog
                  *
                  * @return void
                  *---------------------------------------------------------------- */
                scope.openUserDetailsDialog = function(userId) {

                    appServices.showDialog({}, {
                        templateUrl : __globals.getTemplateURL('user.manage.user-detail-dialog'),
                        controller : 'ManageUsersDetailController as manageUsersDetailCtrl',
                        resolve : {
                            UserDetailData : function() {
                                return ManageUserDataService.getUserDetailData(userId);
                            }
                        }
                    }, function(promiseObj) {

                    });
                };

                /**
                  * Show Assign location dialog
                  *
                  * @return void
                  *---------------------------------------------------------------- */
                scope.showAssignLocationDialog = function(userAuthorityId, name) {

                    appServices.showDialog(
                        {
                            userAuthorityId: userAuthorityId,
                            name: unescape(name)
                        }, 
                        {
                        templateUrl : __globals.getTemplateURL('location.assign-location-dialog'),
                        controller : 'AssignLocationController as AssignLocationCtrl',
                        resolve : {
                            assignLocationData : function() {
                                return LocationDataService.getAssignLocationData(userAuthorityId);
                            }
                        }
                    }, function(promiseObj) {

                    });
                };
            }
        ])


        /**
          * Add User Dialog Controller handle add new user dialog scope
          * 
          * @inject $scope
          * @inject __Form
          * @inject appServices
          * 
          * @return void
          *-------------------------------------------------------- */

        .controller('AddUserDialogController',   [
            '$scope',
            '__Form', 
            'appServices',
            'ManageUserDataService',
            function ($scope,__Form, appServices, ManageUserDataService) {

                var scope   = this;

                scope = __Form.setup(scope, 'add_user_form', 'userData', { 
                            secured : true,
                            unsecuredFields : [
                                'first_name',
                                'last_name'
                            ]
                        });
                
                scope.showRoleSelectBox = $scope.ngDialogData.showRoleSelectBox;

                // Get User add Support Data
                ManageUserDataService
                    .getUserAddSupportData()
                    .then(function(responseData) {
                        var requestData = responseData.data;
                        scope.userRoles = requestData.userRoles;
                    });
                /*
                 Submit form action
                -------------------------------------------------------------------------- */

                scope.submit = function() {
                    
                    __Form.process('manage.user.write.create', scope)
                        .success(function(responseData) {
                            
                        appServices.processResponse(responseData, null, function() {
                            
                            // close dialog
                            $scope.closeThisDialog({
                                user_added : true,
                                'user_data' : responseData.data.userData
                            });

                        });    

                    });

                };

                /**
                  * Close dialog
                  *
                  * @return void
                  *---------------------------------------------------------------- */

                scope.closeDialog = function() {
                    $scope.closeThisDialog();
                };

            } 
        ])

         /**
          * ManageUserChangePasswordController handle change password by admin
          * 
          * @inject $scope
          * @inject __Form
          * @inject appServices
          * 
          * @return void
          *-------------------------------------------------------- */
        .controller('ManageUserChangePasswordController',   [
            '$scope',
            '__Form', 
            'appServices',
            function ManageUserChangePasswordController($scope,__Form, appServices) {

                var scope   = this;

                scope = __Form.setup(scope, 'change_password_form', 'changePasswordData', {
                            secured : true
                        });
                
                scope.ngDialogData = $scope.ngDialogData;

                scope.title = unescape(scope.ngDialogData.name);

                // get id of user
                scope.userID = scope.ngDialogData.userID;


                /*
                 Submit form action
                -------------------------------------------------------------------------- */

                scope.submit = function() {
                    
                    __Form.process({
                        'apiURL'    : 'manage.user.write.change_password.process',
                        'userID'    : scope.userID
                    }, scope)
                        .success(function(responseData) {
                            
                        appServices.processResponse(responseData, null, function() {
                            
                            // close dialog
                            $scope.closeThisDialog();

                        });    

                    });

                };

                /**
                  * Close dialog
                  *
                  * @return void
                  *---------------------------------------------------------------- */

                scope.closeDialog = function() {
                    $scope.closeThisDialog();
                };
            }
        ])

            /**
         * User Detail Dialog Controller
         *
         * inject object $scope
         * inject object __DataStore
         * inject object __Form
         * inject object $stateParams
         *
         * @return  void
         *---------------------------------------------------------------- */

        .controller('ManageUsersDetailController', [
            '$scope',
            '__DataStore',
            '__Form',
            '$stateParams',
            'appServices',
            'UserDetailData',
            function ($scope, __DataStore, __Form, $stateParams, appServices, UserDetailData) {

                var scope = this,
                    requestData = UserDetailData;

                    scope.userData = requestData.userData;
                  

                /**
                  * Close dialog
                  *
                  * @return void
                  *---------------------------------------------------------------- */
                scope.closeDialog = function() {
                    $scope.closeThisDialog();
                };
            }
        ])

        /**
          * Manage User Dynamic Permissions
          *
          * @inject $scope
          * @inject __Form
          * 
          * @return void
          *-------------------------------------------------------- */
        .controller('ManageUsersDynamicPermissionController',   [
            '$scope',
            '__Form',
            '__DataStore',
            'appServices',
            'UserPermission',
            function ManageUsersDynamicPermissionController($scope, __Form, __DataStore, appServices, UserPermission) {

                var scope   = this;
                
                scope  = __Form.setup(scope, 'user_dynamic_access', 'accessData', {
                    secured : true,
                    unsecuredFields : []
                });
                
                scope.ngDialogData  = $scope.ngDialogData;
                scope.userId  = scope.ngDialogData.userId;
                scope.fullName = scope.ngDialogData.fullName;
                scope.requestData   = UserPermission;
                scope.permissions = scope.requestData.permissions;

             	scope.accessData.allow_permissions = scope.requestData.allow_permissions;
				scope.accessData.deny_permissions = scope.requestData.deny_permissions;
				scope.accessData.inherit_permissions = scope.requestData.inherit_permissions;

                scope.disablePermissions = function(eachPermission, permissionID) {

                    _.map(eachPermission.children, function(key) {
                        if (_.includes(key.dependencies, permissionID)) {
                            _.delay(function(text) {
                                $('input[name="'+key.id+'"]').attr('disabled', true);
                            }, 500);
                        }
                    });

                }

				scope.checkedPermission = {};

				_.map(scope.accessData.allow_permissions, function(permission) {
					scope.checkedPermission[permission] = "2";
				});

				_.map(scope.accessData.deny_permissions, function(permission) {
					scope.checkedPermission[permission] = "3";


                    _.map(scope.permissions, function(eachPermission) {

                        var pluckedIDs = _.get(eachPermission.children, 'id');
                        
                        if (_.includes(pluckedIDs, permission)) {
                            scope.disablePermissions(eachPermission, permission)
                        }

                        if (_.has(eachPermission, 'children_permission_group')) {
                             
                            _.map(eachPermission.children_permission_group, function(groupchild) {

                                var pluckedIDs = _.get(groupchild.children, 'id');
                        
                                if (_.includes(pluckedIDs, permission)) {
                                    scope.disablePermissions(groupchild, permission)
                                }
                            });
                        }
                    });

				});

				_.map(scope.accessData.inherit_permissions, function(permission) {
					scope.checkedPermission[permission] = "1";

                    _.map(scope.permissions, function(eachPermission) {

                        var pluckedIDs = _.map(eachPermission.children, 'id');
                         
                        if (_.includes(pluckedIDs, permission) && eachPermission.children[0].inheritStatus == false && eachPermission.children[0].result == "1") {
                            scope.disablePermissions(eachPermission, permission);
                        }

                        if (_.has(eachPermission, 'children_permission_group')) {
                             
                            _.map(eachPermission.children_permission_group, function(groupchild) {

                                var pluckedIDs = _.map(groupchild.children, 'id');
                        
                                if (_.includes(pluckedIDs, permission) && groupchild.children[0].inheritStatus == false && groupchild.children[0].result == "1") {
                                    scope.disablePermissions(groupchild, permission);
                                }

                            });
                        }
                    });
				});
                   
                //for updating permissions
                scope.checkPermission = function(childId, status) {
 					
 					if (!_.isString(status)) {
 						status = status.toString();
 					}

 					scope.checkedPermission[childId] = status;
 					
                 	if (status == "2") {
                		if(!_.includes(scope.accessData.allow_permissions, childId)) {
                 			scope.accessData.allow_permissions.push(childId);
                		}
                 		if (_.includes(scope.accessData.deny_permissions, childId)) {
                 			scope.accessData.deny_permissions = _.without(scope.accessData.deny_permissions, childId);
                 		}
                	} else if (status == "3")  {

	                   	if(!_.includes(scope.accessData.deny_permissions, childId)) {
                 			scope.accessData.deny_permissions.push(childId);
                		}
                 		if (_.includes(scope.accessData.allow_permissions, childId)) {
                 			scope.accessData.allow_permissions = _.without(scope.accessData.allow_permissions, childId);
                 		}
                	} else {
                		if (_.includes(scope.accessData.deny_permissions, childId)) {
                 			scope.accessData.deny_permissions = _.without(scope.accessData.deny_permissions, childId);
                 		}
						if (_.includes(scope.accessData.allow_permissions, childId)) {
                 			scope.accessData.allow_permissions = _.without(scope.accessData.allow_permissions, childId);
                 		}
                	}

                	_.map(scope.permissions, function(permission) {

                        var pluckedIDs = _.map(permission.children, 'id'), 
                        keyPermissions = [];
                        
                        if (_.includes(pluckedIDs, childId) && permission.children[0].id == childId) {
                            
                            _.map(permission.children, function(key) {
                                if (_.includes(key.dependencies, childId) && status == "3") {
                                    
                                    $('input[name="'+key.id+'"]').attr('disabled', true);

                                }  else if (_.includes(key.dependencies, childId) && status == "1" && permission.children[0].result && permission.children[0].inheritStatus == false) {
               
                                            $('input[name="'+key.id+'"]').attr('disabled', true);

                                        }
                                else {
                                    $('input[name="'+key.id+'"]').attr('disabled', false);
                                }

                            });

                        }
                        
						if (_.has(permission, 'children_permission_group')) {
 			 		 		_.map(permission.children_permission_group, function(groupchild) {

                                var pluckedGroupChildIDs = _.map(groupchild.children, 'id'),
                                keyPermissionsGroup = [];

                                //for disabling options if read option  in denied
                                if (_.includes(pluckedGroupChildIDs, childId) && groupchild.children[0].id == childId) {
                            
                                    _.map(groupchild.children, function(groupchildkey) {
                                        if (_.includes(groupchildkey.dependencies, childId) && status == "3") {
                                            $('input[name="'+groupchildkey.id+'"]').attr('disabled', true);
 
                                        } else if (_.includes(groupchildkey.dependencies, childId) && status == "1" && groupchild.children[0].result && groupchild.children[0].inheritStatus == false) {
               
                                            $('input[name="'+groupchildkey.id+'"]').attr('disabled', true);

                                        }  else {
                                            $('input[name="'+groupchildkey.id+'"]').attr('disabled', false);
                                        }
                                        
                                         
                                    });

                                }
							})
					 	}
					})
              	}
              	
                /*
                 Submit form action
                -------------------------------------------------------------------------- */

                scope.submit = function() {
                    // scope.preparePermissions();
                    __Form.process({
                        'apiURL' : 'manage.user.write.user_dynamic_permission',
                        'userId' : scope.userId
                    }, scope)
                        .success(function(responseData) {
                        appServices.processResponse(responseData, null, function() {
                            // close dialog
                            $scope.closeThisDialog();
                        });    
                    });
                };

                /**
                  * Close dialog
                  *
                  * @return void
                  *---------------------------------------------------------------- */
                scope.closeDialog = function() {
                    $scope.closeThisDialog();
                };
                        
            }
        ])

        /**
          * Edit User Dialog Controller
          *
          * inject object $scope
          * inject object __DataStore
          * inject object __Form
          * inject object $stateParams
          *
          * @return  void
          *---------------------------------------------------------------- */

        .controller('EditUserDialogController', [
            '$scope',
            '__DataStore',
            '__Form',
            '$stateParams',
            'appServices',
            'EditUserData',
            function ($scope, __DataStore, __Form, $stateParams, appServices, EditUserData) {

                var scope = this,
                    requestData = EditUserData,
                    ngDialogData = $scope.ngDialogData;

                scope.userRoles = requestData.userRoles;
                scope  = __Form.setup(scope, 'user_edit_form', 'userData');
                scope = __Form.updateModel(scope, requestData.userUpdateData);

                /*
                 Submit form action
                -------------------------------------------------------------------------- */

                scope.submit = function() {
                    
                    __Form.process({
                        'apiURL' : 'manage.user.write.update_process',
                        'userId' : ngDialogData.userId
                    }, scope)
                        .success(function(responseData) {
                        appServices.processResponse(responseData, null, function() {
                            // close dialog
                            $scope.closeThisDialog({'user_updated' : true});
                        });    
                    });
                };

                /**
                  * Close dialog
                  *
                  * @return void
                  *---------------------------------------------------------------- */
                scope.closeDialog = function() {
                    $scope.closeThisDialog();
                };
            }
        ])

})(window, window.angular);;
/*!
*  Component  : Users
*  File       : UserDataService.js  
*  Engine     : UserDataService 
----------------------------------------------------------------------------- */
(function(window, angular, undefined) {

    'use strict';

    angular
        .module('ManageApp.UserDataService', [])

        /**
          User Data Service  
        ---------------------------------------------------------------- */
        .service('UserDataService',[
            '$q', 
            '__DataStore', 
            '__Form',
            'appServices',
            UserDataService
        ]);

        function UserDataService($q, __DataStore,__Form, appServices) {

            /*
            Get Login attempts 
            -----------------------------------------------------------------*/
            this.getLoginAttempts = function() {

                //create a differed object          
                var defferedObject = $q.defer();   
   
                __Form.fetch('user.login.attempts')
                    .success(function(responseData) {
                            
                    appServices.processResponse(responseData, null, function(reactionCode) {

                        //this method calls when the require        
                        //work has completed successfully        
                        //and results are returned to client        
                        defferedObject.resolve(responseData);  

                    }); 

                });       

               //return promise to caller          
               return defferedObject.promise; 
            };

            /*
            Get Countries List 
            -----------------------------------------------------------------*/
            this.getCountries = function() {

                //create a differed object          
                var defferedObject = $q.defer();   
   
                __DataStore.fetch('user.get.country_list')
                    .success(function(responseData) {
                            
                    appServices.processResponse(responseData, null, function(reactionCode) {

                        //this method calls when the require        
                        //work has completed successfully        
                        //and results are returned to client        
                        defferedObject.resolve(responseData);  

                    }); 

                });       

               //return promise to caller          
               return defferedObject.promise; 
            };
        }
    ;
})(window, window.angular);;
/*!
*  Component  : User
*  File       : UserEngine.js  
*  Engine     : UserEngine 
----------------------------------------------------------------------------- */
(function(window, angular, undefined) {

    'use strict';

    angular
        .module('Manage-app.users', [])

        /**
          * UserLoginController - login a user in application
          *
          * @inject __Form
          * @inject __Auth
          * @inject appServices
          * @inject __Utils
          * 
          * @return void
          *-------------------------------------------------------- */

        .controller('UserLoginController',   [
            '__Form', 
            '__Auth', 
            'appServices',
            '__Utils',
            'UserDataService',
            '$state',
			'$rootScope',
            function (__Form, __Auth, appServices, __Utils, UserDataService, $state, $rootScope) {

                var scope   = this;

                scope = __Form.setup(scope, 'form_user_login', 'loginData', {
                    secured : true
                });

                scope.show_captcha      = false;
                scope.request_completed = false;

                /**
                  * Get login attempts for this client ip
                  *
                  * @return void
                  *---------------------------------------------------------------- */

                UserDataService.getLoginAttempts()
                    .then(function(responseData) {
                        scope.show_captcha      = responseData.data.show_captcha;
                        scope.site_key      = responseData.data.site_key;
                        scope.request_completed = true;
                });

				scope.redirectToIntended = function() {

					if( __globals.intended && __globals.intended.name && __globals.intended.params) {
						
						return $state.go(__globals.intended.name, __globals.intended.params);       
             	
					} else if( __globals.intended && __globals.intended.name) {

						return $state.go(__globals.intended.name);      
					}

					return $state.go('project');      

                };

                /**
                  * Fetch captch url
                  *
                  * @return string
                  *---------------------------------------------------------------- */

                scope.getCaptchaURL = function() {
                    return __Utils.apiURL('security.captcha')+'?ver='+Math.random();
                };

                /**
                  * Refresh captch 
                  *
                  * @return void
                  *---------------------------------------------------------------- */

                scope.refreshCaptcha = function() {
                    scope.captchaURL = scope.getCaptchaURL();
                };

                scope.captchaURL  = scope.getCaptchaURL();
			
                /**
                * Submit login form action
                *
                * @return void
                *---------------------------------------------------------------- */
				
                scope.submit = function() {

                    scope.isInActive     = false;
                    scope.accountDeleted = false;

                    __Form.process('user.login.process', scope).success(function(responseData) {

                        var requestData = responseData.data;

                        appServices.processResponse(responseData, {
                                error : function() {

                                    scope.show_captcha = requestData.show_captcha;

                                    // reset password field
                                    scope[scope.ngFormModelName].password   = "";

                                    // Check if show captcha exist then refresh captcha
                                    if (scope.show_captcha) {
                                        scope[scope.ngFormModelName].confirmation_code   = "";
                                        scope.refreshCaptcha();
                                    }

                                },
                                otherError : function(reactionCode) {

                                    scope.isInActive         = requestData.isInActive;
                                    scope.accountDeleted     = requestData.accountDeleted;

                                    // If reaction code is Server Side Validation Error Then 
                                    // Unset the form fields
                                    if (reactionCode == 3) {

                                        // Check if show captcha exist then refresh captcha
                                        if (scope.show_captcha) {
                                            scope.refreshCaptcha();
                                        }

                                    }

                                    // If reaction code 10 is already authenticate.
                                    if (reactionCode == 10) {

                                        // Check if show captcha exist then refresh captcha
                                        scope.redirectToIntended();
                                       //__globals.redirectBrowser(__Utils.apiURL('dashboard'));

                                    }

                                }
                            },
                            function() {
 
                                __Auth.checkIn(requestData.auth_info, function() {
                                 
                                // __globals.setCookie('auth_access_token', requestData.access_token);
                  
                                    if(requestData.availableRoutes) {
                                        __globals.appImmutable('availableRoutes', 
                                            requestData.availableRoutes);
                                    }

									if(requestData.ckeditor) {
										__globals.appImmutable('ckeditor', requestData.ckeditor);
									}


                                    if (requestData.intendedUrl) {

                                        __globals.redirectBrowser(requestData.intendedUrl);

                                    } else {

										$rootScope.$emit('lw.events.logged_in_user', {data : true});

                                         scope.redirectToIntended();

                                    }

                                });
                            });    

                    });

                };

            }

        ])

        
        /**
          * UserLogoutController for login logout
          *
          * @inject __DataStore
          * @inject __Auth
          * @inject appServices
          * 
          * @return void
          *-------------------------------------------------------- */
        .controller('UserLogoutController',   [
            '__DataStore', 
            '__Auth', 
            'appServices', 
            function UserLogoutController(__DataStore, __Auth, appServices) {

                var scope   = this;

                __DataStore.post('user.logout').success(function(responseData) {

                    appServices.processResponse(responseData, function(reactionCode) {

                        // set user auth information
                        __Auth.checkIn(responseData.data.auth_info);  

                    });

                });

            }
        ])

        /**
          * UserForgotPasswordController - request to send password reminder
          *
          * @inject __Form
          * @inject appServices
          * 
          * @return void
          *-------------------------------------------------------- */

        .controller('UserForgotPasswordController',   [
            '__Form', 
            'appServices',
            '__Utils',
            '$state',
            function (__Form, appServices, __Utils, $state) {

                var scope   = this;


                scope = __Form.setup(scope, 'user_forgot_password_form', 'userData', {
                    secured : true
                });

                /**
                  * Submit form
                  *
                  * @return void
                  *---------------------------------------------------------------- */

                scope.submit = function() {

                    __Form.process('user.forgot_password.process', scope)
                    .success(function(responseData) {

                        appServices.processResponse(responseData, null, function() {
                            
                           $state.go('forgot_password_sucess');
                           
                        });    

                    });

                };

            }

        ])

		/**
          * UserResetPasswordController for reset user password
          *
          * @inject __Form
          * @inject appServices
          * @inject __Utils
          * 
          * @return void
          *-------------------------------------------------------- */

        .controller('UserResetPasswordController',   [
            '__Form', 
            'appServices',
            '__Utils',
			'$state',
            function (__Form, appServices, __Utils, $state) {

                var scope = this;

                scope = __Form.setup(scope, 'user_reset_password_form', 'userData', {
                    secured : true
                });

                /**
                  * Submit reset password form action
                  *
                  * @return void
                  *---------------------------------------------------------------- */
                
                scope.submit = function() {

                    __Form.process({
                        'apiURL'        : 'user.reset_password.process',
                        'reminderToken' : $state.params.reminderToken
                    }, scope)
                        .success(function(responseData) {
                            
                        appServices.processResponse(responseData, null,
                            function(reactionCode) {
                            $state.go('login');
                        });    

                    });

                };

            }
        ])
})(window, window.angular);;
/*!
*  Component  : Configuration
*  File       : ConfigurationDataService.js  
*  Engine     : ConfigurationDataService 
----------------------------------------------------------------------------- */

(function(window, angular, undefined) {

    'use strict';

    angular
        .module('ManageApp.ConfigurationDataService', [])
        .service('ConfigurationDataService',[
            '$q', 
            '__DataStore',
            'appServices',
            ConfigurationDataService
        ])

        /*!
         This service use for to get the promise on data
        ----------------------------------------------------------------------------- */

        function ConfigurationDataService($q, __DataStore, appServices) {

            /*
            Get the data of configuration
            -----------------------------------------------------------------*/

            this.readConfigurationData = function(formType) {

                //create a differed object          
                var defferedObject = $q.defer();   
   
                __DataStore.fetch({
                        'apiURL'   :'manage.configuration.get.support.data',
                        'formType' : formType // different form type like 1, 2,3,4 etc
                    }).success(function(responseData) {
                            
                    appServices.processResponse(responseData, null, function(reactionCode) {

                        //this method calls when the require        
                        //work has completed successfully        
                        //and results are returned to client        
                        defferedObject.resolve(responseData);  

                    }); 

                });       

               //return promise to caller          
               return defferedObject.promise; 
            };

        };

    
})(window, window.angular);
;

/*!
*  Component  : Configuration
*  File       : ConfigurationEngine.js
*  Engine     : ConfigurationEngine
----------------------------------------------------------------------------- */

(function(window, angular, undefined) {

    'use strict';

    angular
        .module('ManageApp.configuration', [])

        /**
         * GeneralDialogController for update request
         *
         * @inject $scope
         * @inject __DataStore
         * @inject appServices
         * @inject __Form
         *
         * @return void
         *-------------------------------------------------------- */
        .controller('GeneralController', [
            '$scope',
            '__Form',
            '$state',
            'appServices',
            'lwFileUploader',
            '__Utils',
            '$rootScope',
            'getGeneralData',
        function GeneralController(
            $scope, __Form, $state, appServices, lwFileUploader, __Utils, $rootScope, getGeneralData
        ) {

        var scope  = this;
           scope.default_header_background_color = getGeneralData.data.configuration.default_header_background_color;
            scope.default_header_text_link_color = getGeneralData.data.configuration.default_header_text_link_color;
            scope.themeColors = getGeneralData.data.configuration.theme_colors;
            
            scope  = __Form.setup(scope, 'general_edit', 'editData', {
            	secured : true,
                modelUpdateWatcher:true,
                unsecuredFields : ['logoURL', 'faviconURL', 'name']
            });

            scope.pageStatus = false;

            scope.timezone_select_config = __globals.getSelectizeOptions({
                valueField  : 'value',
                labelField  : 'text',
                searchField : [ 'text' ]
            });

            scope.home_page_select_config = __globals.getSelectizeOptions({
                valueField  : 'id'
            });

            scope.selectSiteColor = function(themeColor) {
                scope.editData.header_background_color = themeColor.background;
                scope.editData.header_text_link_color = themeColor.text;
            }

            scope.checkLogo = function(from) {
                var isSame = false;

                if (scope.editData.invoice_logo_image == scope.editData.logo_image) {
                    isSame = true;
                }

                if (isSame) {
                    if (from == 1) { // Logo
                        scope.editData.invoice_logo_image = '';
                    } else if (from == 2) { // Invoice Logo
                        scope.editData.logo_image = '';
                    }
                }
            };

            /**
              * Clear Color 
              *
              * @return void
              *---------------------------------------------------------------- */
            scope.clearColor = function() {
            	// var logo_background_color = ngDialogData.responseData.data.configuration.logo_background_color;
			    scope.editData.header_background_color = scope.default_header_background_color;
				
			}

			/**
              * Clear Color 
              *
              * @return void
              *---------------------------------------------------------------- */
            scope.clearPrimaryColor = function() {
			    scope.editData.header_text_link_color = scope.default_header_text_link_color;
			}
			
            /**
              * Fetch support data
              *
              * @return void
              *---------------------------------------------------------------- */

            var requestData         = getGeneralData.data;
                scope.timezoneData  = requestData.configuration.timezone_list;
                scope.homePageData  = __globals.generateKeyValueItems(requestData.configuration.home_page_list);
				
            	scope.languages   = requestData.configuration.locale_list;
                var configuration = requestData.configuration;
				
                __Form.updateModel(scope, configuration);

                scope.pageStatus = true;

                scope.imagesSelectConfig  = __globals.getSelectizeOptions({
                    valueField  : 'name',
                    labelField  : 'name',
                    render      : {
                        item: function(item, escape) {
                            return  __Utils.template('#imageListItemTemplate',
                            item
                            );
                        },
                        option: function(item, escape) {
                            return  __Utils.template('#imageListOptionTemplate',
                            item
                            );
                        }
                    },
                    searchField : ['name']
                });


            /**
              * Retrieve files required for account logo
              *
              * @return void
              *---------------------------------------------------------------- */

            scope.retrieveSpecificFiles  =  function() {

                lwFileUploader.getTempUploadedFiles(scope, {
                    'url' : __Utils.apiURL('media.upload.read_logo')
                }, function(uploadedFile) {
                    scope.logoFiles       = uploadedFile;
                    scope.logoFilesCount = uploadedFile.length;
                });

            };
            scope.retrieveSpecificFiles();

            scope.retrieveFaviconFiles = function() {
                lwFileUploader.getTempUploadedFiles(scope, {
                    'url' : __Utils.apiURL('media.upload.read_favicon')
                }, function(uploadedFile) {
                    scope.faviconFiles      = uploadedFile;
                    scope.faviconFilesCount = uploadedFile.length;
                });
            };

            scope.retrieveFaviconFiles();

            $rootScope.$on('lw-loader-event-start', function (event, data) {
                $scope.loading = true; 
				$("#lwFileupload").attr("disabled", true);
            });
            
            $rootScope.$on('lw-loader-event-stop', function (event, data) {
                $scope.loading = false;
				$("#lwFileupload").attr("disabled", false); 
            });

            // uploader file instance
            $scope.upload = function() {
				
                lwFileUploader.upload({
                        'url' : __Utils.apiURL('media.upload.write.logo')
                    }, function(response) {

                    scope.retrieveSpecificFiles();
                    scope.retrieveFaviconFiles();

                });
            };

            /**
              * Show uploaded media files
              *
              * @return void
              *---------------------------------------------------------------- */

            $scope.showUploadedMediaDialog = function() {

                lwFileUploader.openDialog(scope, {
                    'url' : __Utils.apiURL('media.upload.read_logo')
                },
                function(promiseObject) {
                    scope.retrieveSpecificFiles();
                    scope.retrieveFaviconFiles();
                });

            };

            /**
              * update blog data
              *
              * @return void
              *---------------------------------------------------------------- */

            scope.submit = function() {

                __Form.process({
                    'apiURL'    : 'manage.configuration.process',
                    'formType'  : 1
                }, scope)
                    .success(function(responseData) {

                    appServices.processResponse(responseData, null, function() {

                        var requestData = responseData.data;                        
                        if (requestData.showRealodButton == true) {
                            __globals.showConfirmation({
                                title               : responseData.data.message,
                                text                : responseData.data.textMessage,
                                type                : "success",
                                confirmButtonClass  : "btn-success",
                               confirmButtonText   : $("#lwReloadBtnText").attr('data-message'),
                                confirmButtonColor :  "#337ab7",
                            }, function() {
                                location.reload();
                            });
                        }
                    });
                });
            };
               
            /**
              * Close dialog
              *
              * @return void
              *---------------------------------------------------------------- */
			var logo_background_color = getGeneralData.data.configuration.logo_background_color;
				
            scope.closeDialog = function() {
				
				// $('#lwchangeHeaderColor').css('background', "#"+logo_background_color);
                $scope.closeThisDialog();
            };
        }
        ])

        /**
         * CurrencyConfigurationController for manage currency of store
         *
         * @inject $scope
         * @inject __Form
         * @inject appServices
         *
         * @return void
         *-------------------------------------------------------- */
        .controller('CurrencyConfigurationController', [
            '$scope',
            '__Form',
            'appServices',
            'getCurrencyData',
        function CurrencyConfigurationController( $scope, __Form, appServices, getCurrencyData) {

            var scope = this,
            ngDialogData = $scope.ngDialogData;

            scope.isZeroDecimalCurrency = false;
            
            /**
              * Generate key value
              *
              * @param bool responseKeyValue
              *
              * @return void
              *---------------------------------------------------------------- */

            scope.generateCurrenciesArray = function(currencies, responseKeyValue) {

                if (!responseKeyValue) {
                    return currencies;
                }

                var currenciesArray = [];

                _.forEach(currencies, function(value, key) {

                    currenciesArray.push({
                        'currency_code'     : key,
                        'currency_name'     : value.name
                    });

                });

                var $lwCurrencySettingTxtMsg = $('#lwCurrencySettingTxtMsg');

                currenciesArray.push({
                    'currency_code'  : 'other',
                    'currency_name'  : $lwCurrencySettingTxtMsg.attr('other-text')
                });

                return currenciesArray;

            };

            /**
              *  Check the the currency match with zero decimal
              *
              * @param array zeroDecimalCurrecies
              * @param string selectedCurrencyValue
              *
              * @return void
              *---------------------------------------------------------------- */

            scope.checkIsZeroDecimalCurrency = function(zeroDecimalCurrecies, selectedCurrencyValue) {

                var isMatch = _.filter(zeroDecimalCurrecies, function(value, key) {

                        return  (key === selectedCurrencyValue);
                    });

                scope.isZeroDecimalCurrency = Boolean(isMatch.length);

            };

            /**
              * Check if current currency is Paypal supported or not
              *
              * @return void
              *---------------------------------------------------------------- */
            scope.checkIsPaypalSupported = function (currencyValue) {

                var isPaypalSupported = _.filter(scope.options, function(value, key) {

                    return  (key == currencyValue);
                });

                scope.isPaypalSupport = Boolean(isPaypalSupported.length);
            };

            /**
              * format currency symbol and currency value
              *
              * @return void
              *---------------------------------------------------------------- */
            scope.formatCurrency = function (currencySymbol, currency) {

                _.defer(function() {

                    var $lwCurrencyFormat = $('#lwCurrencyFormat');

                    var string = $lwCurrencyFormat.attr('data-format');

                    scope.currency_format_preview  =  string.split('{__currencySymbol__}').join(currencySymbol)
                                                            .split('{__amount__}').join(100)
                                                            .split('{__currencyCode__}').join(currency);
                });
            };

            scope.pageStatus = false;

            scope  = __Form.setup(scope, 'edit_currency_configuration', 'editData', {
                secured : true,
                unsecuredFields : [
                    'currency_symbol',
                    'currency_format'
                ]
            });

            scope.currencies_select_config = __globals.getSelectizeOptions({
                valueField  : 'currency_code',
                labelField  : 'currency_name',
                searchField : [ 'currency_code', 'currency_name' ]
            });

            scope.multi_currencies_select_config = __globals.getSelectizeOptions({
                valueField  : 'currency_code',
                labelField  : 'currency_name',
                searchField : [ 'currency_code', 'currency_name' ],
                plugins     : ['remove_button'],
                maxItems    : 1000,
                delimiter   : ',',
                persist     : false
            });

            scope.is_support_paypal = true;


            var requestData     = getCurrencyData.data,
                currenciesData  = requestData.configuration.currencies;

            scope.options     = currenciesData.options;
            scope.currencies  = currenciesData.details;
            scope.zeroDecimal = currenciesData.zero_decimal;
            /*scope.currencies_options
                    = scope.generateCurrenciesArray(currenciesData.details, true);*/

            _.defer(function() {
                scope.currencies_options
                    = scope.generateCurrenciesArray(currenciesData.details, true);
            });

            scope.checkIsZeroDecimalCurrency(scope.zeroDecimal, requestData.configuration.currency_value);

            scope.checkIsPaypalSupported(requestData.configuration.currency);

            scope.default_currency_format = requestData.configuration.default_currency_format;

            scope.placeholders = requestData.placeholders;
            scope = __Form.updateModel(scope, requestData.configuration);

            _.forEach(scope.currencies, function(currencyObj, key) {

                if (key == scope.editData.currency_value) {
                    scope.currencySymbol = currencyObj.symbol;
                }
            });

            if (requestData.configuration.currency == 'other') {
                scope.currencySymbol = requestData.configuration.currency_symbol;
            }

            scope.formatCurrency(scope.currencySymbol, scope.editData.currency_value);


            scope.pageStatus = true;


            /**
              * Use default format for currency
              *
              * @param string defaultCurrencyFormat
              *
              * @return string
              *---------------------------------------------------------------- */
            scope.useDefaultFormat = function(defaultCurrencyFormat, currency_symbol, currency_value) {

                scope.editData.currency_format = defaultCurrencyFormat;

                var string = scope.editData.currency_format;

                scope.currency_format_preview  =  string.split('{__currencySymbol__}').join(currency_symbol)
                                                    .split('{__amount__}').join(100)
                                                    .split('{__currencyCode__}').join(currency_value);
            };


            /**
              * Use default format for currency
              *
              * @param string defaultCurrencyFormat
              *
              * @return string
              *---------------------------------------------------------------- */
            scope.updateCurrencyPreview = function(currency_symbol, currency_value) {

                if (_.isUndefined(currency_symbol)) {
                    currency_symbol = '';
                }

                if (_.isUndefined(currency_value)) {
                    currency_value = '';
                }

                var $lwCurrencyFormat = $('#lwCurrencyFormat');

                var string = $lwCurrencyFormat.attr('data-format');

                scope.currency_format_preview  =  string.split('{__currencySymbol__}').join(currency_symbol)
                                                        .split('{__amount__}').join(100)
                                                        .split('{__currencyCode__}').join(currency_value);

            };

            /**
              * Submit currency Data
              *
              * @return void
              *---------------------------------------------------------------- */

            scope.submit = function() {

                __Form.process({
                    'apiURL'    : 'manage.configuration.process',
                    'formType'  : 2 // currency
                }, scope)
                    .success(function(responseData) {

                    appServices.processResponse(responseData, null, function() {

                        var requestData = responseData.data;
                        
                        if (requestData.showRealodButton == true) {

                            __globals.showConfirmation({
                                title               : responseData.data.message,
                                text                : responseData.data.textMessage,
                                type                : "success",
                                showCancelButton    : true,
                                confirmButtonClass  : "btn-success",
                                confirmButtonText   : $("#lwReloadBtnText").attr('data-message'),
                                confirmButtonColor :  "#337ab7"
                            }, function() {

                               location.reload();

                            });
                        }
                    });

                });
            };


            /**
              * currency change
              *
              * @param selectedCurrency
              * @return void
              *---------------------------------------------------------------- */
            scope.currencyChange = function(selectedCurrency) {

                scope.checkIsZeroDecimalCurrency(scope.zeroDecimal, selectedCurrency);

                if (!_.isEmpty(selectedCurrency) && selectedCurrency != 'other') {

                    _.forEach(scope.currencies, function(currencyObj, key) {

                        if (key == selectedCurrency) {
                            scope.editData.currency_value   = selectedCurrency;
                            scope.editData.currency_symbol  = currencyObj.ASCII;
                            scope.currencySymbol            = currencyObj.symbol;
                        }

                    });

                    scope.is_support_paypal = true;

                } else {

                    scope.editData.currency_value   = '';
                    scope.editData.currency_symbol  = '';

                }

                scope.updateCurrencyPreview(scope.currencySymbol, scope.editData.currency_value);

                scope.checkIsPaypalSupported(scope.editData.currency_value);

            };

            /**
              * currency value change
              *
              * @param currencyValue
              *
              * @return void
              *---------------------------------------------------------------- */
            scope.currencyValueChange = function(currencyValue) {

                scope.checkIsZeroDecimalCurrency(scope.zeroDecimal, currencyValue);

                if (!_.isEmpty(currencyValue) && currencyValue != 'other') {

                    var currency = {};
                    _.forEach(scope.currencies, function(currencyObj, key) {

                        if (key == currencyValue) {
                            currency = currencyObj;
                        }

                    });

                    if (_.isEmpty(currency)) {
                        //scope.is_support_paypal = false;
                        scope.editData.currency  = 'other';
                    } else {
                        //scope.is_support_paypal     = true;
                        scope.editData.currency     = currencyValue;
                        scope.editData.currency_symbol  = currency.ASCII;
                        scope.currencySymbol           = currency.symbol;
                    }

                } else if (!_.isEmpty(currencyValue)) {

                    //scope.is_support_paypal     = false;
                    scope.editData.currency     = 'other';

                } else {

                    //scope.is_support_paypal  = true;
                    scope.editData.currency  = '';

                }

                scope.checkIsPaypalSupported(currencyValue);

                if (_.isUndefined(scope.editData.currency_value)) {
                    scope.currencySymbol = '';
                }

                scope.updateCurrencyPreview(scope.currencySymbol, scope.editData.currency_value);
            };

            /**
              * Close dialog
              *
              * @return void
              *---------------------------------------------------------------- */

            scope.closeDialog = function() {
                $scope.closeThisDialog(scope.currencySymbol, scope.editData.currency);
            };
        }
        ])
        ;

})(window, window.angular);;
/*!
*  Component  : RolePermission
*  File       : RolePermissionDataServices.js  
*  Engine     : RolePermissionServices 
----------------------------------------------------------------------------- */

(function(window, angular, undefined) {

    'use strict';

    angular
        .module('app.RolePermissionDataServices', [])
        .service('RolePermissionDataService',[
            '$q', 
            '__DataStore',
            'appServices',
            RolePermissionDataService
        ])

        /*!
         This service use for to get the promise on data
        ----------------------------------------------------------------------------- */

        function RolePermissionDataService($q, __DataStore, appServices) {

            /*
            Get Permissions
            -----------------------------------------------------------------*/
            this.getPermissions = function(roleId) {
                
                //create a differed object          
                var defferedObject = $q.defer();   
   
                __DataStore.fetch({
                    'apiURL' : 'manage.user.role_permission.read',
                    'roleId' : roleId
                }).success(function(responseData) {
                            
                    appServices.processResponse(responseData, null, function(reactionCode) {

                        //this method calls when the require        
                        //work has completed successfully        
                        //and results are returned to client        
                        defferedObject.resolve(responseData.data);  

                    }); 

                });       

               //return promise to caller          
               return defferedObject.promise; 
            };

            /*
            Get Add Role Support Data
            -----------------------------------------------------------------*/
            this.getAddSupportData = function() {
                
                //create a differed object          
                var defferedObject = $q.defer();   
   
                __DataStore.fetch('manage.user.role_permission.read.add_support_data')
                    .success(function(responseData) {
                            
                    appServices.processResponse(responseData, null, function(reactionCode) {

                        //this method calls when the require        
                        //work has completed successfully        
                        //and results are returned to client        
                        defferedObject.resolve(responseData.data);  

                    }); 

                });       

               //return promise to caller          
               return defferedObject.promise; 
            };

            /*
            Get add support Data 
            -----------------------------------------------------------------*/
            this.getAllPermissionsById = function(roleId) {
                
                //create a differed object          
                var defferedObject = $q.defer();   
   
                __DataStore.fetch({
                        'apiURL' : 'manage.user.role_permission.read.using_id',
                        'roleId' : roleId
                    }).success(function(responseData) {
                            
                    appServices.processResponse(responseData, null, function(reactionCode) {

                        //this method calls when the require        
                        //work has completed successfully        
                        //and results are returned to client        
                        defferedObject.resolve(responseData.data);  

                    }); 

                });       

               //return promise to caller          
               return defferedObject.promise; 
            };

        };

})(window, window.angular);
;
/*!
*  Component  : RolePermission
*  File       : RolePermission.js
*  Engine     : RolePermission
----------------------------------------------------------------------------- */

(function(window, angular, undefined) {

    'use strict';

    angular
        .module('app.RolePermissionEngine', [])

        /**
        * Role Permission List Controller
        *
        * inject object $scope
        * inject object __DataStore
        * inject object __Form
        * inject object $state
        * inject object appServices
        * inject object $rootScope
        * inject object RolePermissionDataService
        *
        * @return  void
        *---------------------------------------------------------------- */

        .controller('RolePermissionListController', [
            '$scope',
            '__DataStore',
            '__Form',
            '$state',
            'appServices',
            '$rootScope',
            'RolePermissionDataService',
        function ( $scope, __DataStore, __Form, $state, appServices, $rootScope, RolePermissionDataService) {
            var dtColumnsData = [
                    {
                        "name"      : "title",
                        "orderable" : true,
                    },
                    {
                        "name"      : null,
                        "template"  : "#rolePermissionActionColumnTemplate"
                    }
                ],
                scope   = this;

                /**
                * Get general user test as a datatable source object
                *
                * @return  void
                *---------------------------------------------------------- */

                scope.rolePermissionDataTable = __DataStore.dataTable('#lwrolePermissionList', {
                    url         : 'manage.user.role_permission.read.list',
                    dtOptions   : {
                        "searching": true,
                        "pageLength" : 25
                    },
                    columnsData : dtColumnsData, 
                    scope       : $scope
                });

                /*
                Reload current datatable
                ------------------------------------------------------------ */
                scope.reloadDT = function() {
                    __DataStore.reloadDT(scope.rolePermissionDataTable);
                };

               /**
                * rolePermission delete
                *
                * inject rolePermissionIdUid
                *
                * @return    void
                *---------------------------------------------------------------- */

                scope.delete = function(rolePermissionIdOrUid, name) {

                    var $lwRolePermissionDeleteTextMsg = $('#lwRolePermissionDeleteTextMsg');

                    __globals.showConfirmation({
                        html : __globals.getReplacedString($lwRolePermissionDeleteTextMsg,
                                    '__name__',
                                    _.unescape(name)
                                ),
                        confirmButtonText : $lwRolePermissionDeleteTextMsg.attr('data-delete-button-text')
                    }, function() {

                        __DataStore.post({
                            'apiURL' : 'manage.user.role_permission.write.delete',
                            'rolePermissionIdOrUid' : rolePermissionIdOrUid
                        }).success(function(responseData) {

                            var message = responseData.data.message;

                            appServices.processResponse(responseData, {

                                error : function(data) {
                                __globals.showConfirmation({
                                    title   : $lwRolePermissionDeleteTextMsg .attr('data-error-text'),
                                    text    : message,
                                    type    : 'error'
                                });
                            }

                            }, function(data) {
                                __globals.showConfirmation({
                                    title   : $lwRolePermissionDeleteTextMsg .attr('data-success-text'),
                                    text    : message,
                                    type    : 'success'
                                });
                                scope.reloadDT();
                            });

                        });

                    });
                };

                /**
                * Show add new role dialog
                *
                * @return    void
                *---------------------------------------------------------------- */
                scope.showAddNewDialog = function() {

                    appServices.showDialog({},
                    {
                        templateUrl : __globals.getTemplateURL(
                                'user.role-permission.add-dialog'
                            ),
                        controller: 'AddRoleController as addRoleCtrl',
                        resolve : {
                            addSupportData : function() {
                                return RolePermissionDataService
                                        .getAddSupportData();
                            }
                        }
                    },
                    function(promiseObj) {
                        if (_.has(promiseObj.value, 'role_Added') 
                            && (promiseObj.value.role_Added === true)) {
                            scope.reloadDT();
                        }
                    });
                };

              /**
                * Role Permission Dialog
                *
                * inject roleId
                *
                * @return    void
                *---------------------------------------------------------------- */
                scope.rolePermissionDialog = function(roleId, title) {

                    appServices.showDialog({
                        'roleId' : roleId,
                        'title'  : _.unescape(title)
                    },
                    {
                        templateUrl : __globals.getTemplateURL(
                                'user.role-permission.dynamic-role-permissions'
                            ),
                        controller: 'DynamicRolePermissionController as DynamicRolePermissionCtrl',
                        resolve : {
                            permissionData : function() {
                                return RolePermissionDataService
                                        .getPermissions(roleId);
                            }
                        }
                    },
                    function(promiseObj) {

                    });
                };
        }
        ])
        // Role Permission List Controller ends here

        /**
          * Dynamic Role Permission Controller
          *
          * inject object $scope
          * inject object __DataStore
          * inject object __Form
          * inject object $stateParams
          *
          * @return  void
          *---------------------------------------------------------------- */

        .controller('DynamicRolePermissionController', [
            '$scope',
            '__DataStore',
            '__Form',
            '$stateParams',
            'appServices',
            'permissionData',
            function ($scope, __DataStore, __Form, $stateParams, appServices, permissionData) {
                var scope    = this,
                    ngDialog = $scope.ngDialogData,
                    roleId   = ngDialog.roleId;

                scope  = __Form.setup(scope, 'user_role_dynamic_access', 'accessData', {
                    secured : true,
                    unsecuredFields : []
                });

                scope.title = ngDialog.title;
                scope.permissions = permissionData.permissions;

			 	scope.accessData.allow_permissions = permissionData.allow_permissions;
				scope.accessData.deny_permissions = permissionData.deny_permissions;
 				scope.checkedPermission = {};

				scope.disablePermissions = function(eachPermission, permissionID) {

                    _.map(eachPermission.children, function(key) {
                        if (_.includes(key.dependencies, permissionID)) {
                            _.delay(function(text) {
                                $('input[name="'+key.id+'"]').attr('disabled', true);
                            }, 500);
                        }
                    });

                }

				_.map(scope.accessData.allow_permissions, function(permission) {
				 	scope.checkedPermission[permission] = "2";
				})
				_.map(scope.accessData.deny_permissions, function(permission) {
				 	scope.checkedPermission[permission] = "3";

				 	_.map(scope.permissions, function(eachPermission) {

                        var pluckedIDs = _.map(eachPermission.children, 'id');

                        if (_.includes(pluckedIDs, permission)) {
                            scope.disablePermissions(eachPermission, permission)
                        }

                        if (_.has(eachPermission, 'children_permission_group')) {

                            _.map(eachPermission.children_permission_group, function(groupchild) {

                                var pluckedIDs = _.map(groupchild.children, 'id');

                                if (_.includes(pluckedIDs, permission)) {
                                    scope.disablePermissions(groupchild, permission)
                                }
                            });
                        }
                    });
				})

                scope = __Form.updateModel(scope, scope.accessData);

 				//for updating permissions
                scope.checkPermission = function(childId, status) {

 					if (!_.isString(status)) {
 						status = status.toString();
 					}

 					scope.checkedPermission[childId] = status;

                 	if (status == "2") {
                		if(!_.includes(scope.accessData.allow_permissions, childId)) {
                 			scope.accessData.allow_permissions.push(childId);
                		}
                 		if (_.includes(scope.accessData.deny_permissions, childId)) {
                 			scope.accessData.deny_permissions = _.without(scope.accessData.deny_permissions, childId);
                 		}
                	} else if (status == "3")  {

	                   	if(!_.includes(scope.accessData.deny_permissions, childId)) {
                 			scope.accessData.deny_permissions.push(childId);
                		}
                 		if (_.includes(scope.accessData.allow_permissions, childId)) {
                 			scope.accessData.allow_permissions = _.without(scope.accessData.allow_permissions, childId);
                 		}
                	} else {

                		if (_.includes(scope.accessData.deny_permissions, childId)) {
                 			scope.accessData.deny_permissions = _.without(scope.accessData.deny_permissions, childId);
                 		}
                 		if (_.includes(scope.accessData.allow_permissions, childId)) {
                 			scope.accessData.allow_permissions = _.without(scope.accessData.allow_permissions, childId);
                 		}
                	}

                	_.map(scope.permissions, function(permission) {

                        var pluckedIDs = _.map(permission.children, 'id'),
                        keyPermissions = [];

                        if (_.includes(pluckedIDs, childId) && permission.children[0].id == childId) {

                            _.map(permission.children, function(key) {

                                if (key.id != permission.children[0].id) {
                                    _.map(key.dependencies, function(dependency) {

                                        if (_.includes(key.dependencies, childId) && status == "3") {

                                            $('input[name="'+key.id+'"]').attr('disabled', true);

                                        } else {
                                            $('input[name="'+key.id+'"]').attr('disabled', false);

                                        }
                                    });
                                }
                            })
                        }

                        if (_.has(permission, 'children_permission_group')) {
                            _.map(permission.children_permission_group, function(groupchild) {

                                var pluckedGroupChildIDs = _.map(groupchild.children, 'id'), 
                                keyPermissionsGroup = [];

                                if (_.includes(pluckedGroupChildIDs, childId) && groupchild.children[0].id == childId) {

                                    _.map(groupchild.children, function(key2) {

                                        if (key2.id != groupchild.children[0].id) {
                                            _.map(key2.dependencies, function(dependency) {

                                                if (_.includes(key2.dependencies, childId) && status == "3") {

                                                    $('input[name="'+key2.id+'"]').attr('disabled', true);

                                                } else {
                                                    $('input[name="'+key2.id+'"]').attr('disabled', false);

                                                }
                                            })
                                        }
                                    });
                                }
                            })
                        }
					})
              	}

                /*
                 Submit form action
                -------------------------------------------------------------------------- */

                scope.submit = function() {
                    // scope.preparePermissionData();
                    __Form.process({
                        'apiURL' : 'manage.user.role_permission.write.create',
                        'roleId' : roleId
                    }, scope)
                        .success(function(responseData) {
                        appServices.processResponse(responseData, null, function() {
                            // close dialog
                            $scope.closeThisDialog();
                        });
                    });
                };

                /*
                 * Check if value updated then enable and disable radio button according to
                 * current radio button
                 *
                 * @param string name
                 * @param number value
                 * @param array dependencies
                 * @param bool inheritStatus
                 *
                 * return array
                 * -------------------------------------------------------------------------- */
                scope.valueUpdated = function(name, value, dependencies, inheritStatus) {

                    _.forEach(scope.accessData.permissions, function(permission) {
                        if (permission[0].name == name) {

                            if (permission[0].allow == 2) { //Allow

                                _.map(permission, function(item) {
                                    if (!_.isEmpty(item.dependencies)) {
                                        item.disabled = false;
                                    }
                                });

                            } else if (permission[0].allow == 3) { // Deny

                                _.map(permission, function(item) {
                                    if (!_.isEmpty(item.dependencies)) {
                                        item.disabled = true;
                                        item.allow = 3;
                                    }
                                });

                            } else if (permission[0].allow == 1) { // Inherited

                                if (permission[0].currentStatus) {

                                    _.map(permission, function(item) {
                                        if (!_.isEmpty(item.dependencies)) {
                                            item.disabled = false;
                                            item.allow = 1;
                                        }
                                    });

                                } else {

                                    _.map(permission, function(item) {
                                        if (!_.isEmpty(item.dependencies)) {
                                            item.disabled = true;
                                            item.allow = 1;
                                        }
                                    });
                                }
                            }
                        }
                    });
                };

                /**
                  * Close dialog
                  *
                  * @return void
                  *---------------------------------------------------------------- */
                scope.closeDialog = function() {
                    $scope.closeThisDialog();
                };
            }
        ])

                /**
          * Add new Role Permission Controller
          *
          * inject object $scope
          * inject object __DataStore
          * inject object __Form
          * inject object $stateParams
          *
          * @return  void
          *---------------------------------------------------------------- */

        .controller('AddRoleController', [
            '$scope',
            '__DataStore',
            '__Form',
            '$stateParams',
            'addSupportData',
            'appServices',
            'RolePermissionDataService',
            function ($scope, __DataStore, __Form, $stateParams, addSupportData, appServices, RolePermissionDataService) {

                var scope  = this;

                scope  = __Form.setup(scope, 'add_role', 'roleData', {
                    secured : false,
                    unsecuredFields : []
                });

                scope.userRoles = addSupportData.userRoles; 
                scope.permissions = addSupportData.permissionData;
                scope.roleData.allow_permissions = [];
				scope.roleData.deny_permissions = [];
				scope.checkedPermission = {};

                scope.disablePermissions = function(eachPermission, permissionID) {

                    _.map(eachPermission.children, function(key) {
                        if (_.includes(key.dependencies, permissionID)) {
                            _.delay(function(text) {
                                $('input[name="'+key.id+'"]').attr('disabled', true);
                            }, 500);
                        }
                    });

                }


                /*
                 Get Permission basis on the role id
                -------------------------------------------------------------------------- */
                scope.getPermissions = function(roleId) {

                    RolePermissionDataService
                        .getAllPermissionsById(roleId)
                        .then(function(responseData) {

                            scope.permissions = responseData.permissionData;
                            scope.roleData.selected_permissions = responseData.allowedData;

                            scope.roleData.allow_permissions = responseData.allow_permissions;
 							scope.roleData.deny_permissions = responseData.deny_permissions;
 							scope.checkedPermission = {};

							_.map(scope.roleData.allow_permissions, function(permission) {
							 	scope.checkedPermission[permission] = "2";
							})
							_.map(scope.roleData.deny_permissions, function(permission) {
							 	scope.checkedPermission[permission] = "3";

                                 _.map(scope.permissions, function(eachPermission) {

                                    var pluckedIDs = _.map(eachPermission.children, 'id');

                                    if (_.includes(pluckedIDs, permission)) {
                                        scope.disablePermissions(eachPermission, permission)
                                    }

                                    if (_.has(eachPermission, 'children_permission_group')) {

                                        _.map(eachPermission.children_permission_group, function(groupchild) {

                                            var pluckedIDs = _.map(groupchild.children, 'id');

                                            if (_.includes(pluckedIDs, permission)) {
                                                scope.disablePermissions(groupchild, permission)
                                            }
                                        });
                                    }
                                });
							})
                        })
                };

 				//for updating permissions
                scope.checkPermission = function(childId, status) {

 					if (!_.isString(status)) {
 						status = status.toString();
 					}

 					scope.checkedPermission[childId] = status;

                 	if (status == "2") {

                		if(!_.includes(scope.roleData.allow_permissions, childId)) {
                 			scope.roleData.allow_permissions.push(childId);
                		}
                 		if (_.includes(scope.roleData.deny_permissions, childId)) {
                 			scope.roleData.deny_permissions = _.without(scope.roleData.deny_permissions, childId);
                 		}

                	} else if (status == "3")  {

	                   	if(!_.includes(scope.roleData.deny_permissions, childId)) {
                 			scope.roleData.deny_permissions.push(childId);
                		}
                 		if (_.includes(scope.roleData.allow_permissions, childId)) {
                 			scope.roleData.allow_permissions = _.without(scope.roleData.allow_permissions, childId);
                 		}
                	}

                	_.map(scope.permissions, function(permission) {

                        var pluckedIDs = _.map(permission.children, 'id'), 
                        keyPermissions = [];
                        if (_.includes(pluckedIDs, childId) && permission.children[0].id == childId) {
                            _.map(permission.children, function(key) {

                                if (key.id != permission.children[0].id) {
                                    _.map(key.dependencies, function(dependency) {

                                        if (_.includes(key.dependencies, childId) && status == "3") {

                                            $('input[name="'+key.id+'"]').attr('disabled', true);

                                        } else {
                                            $('input[name="'+key.id+'"]').attr('disabled', false);

                                        }
                                    });
                                }
                            })
                        }

                        if (_.has(permission, 'children_permission_group')) {
                            _.map(permission.children_permission_group, function(groupchild) {

                                var pluckedGroupChildIDs = _.map(groupchild.children, 'id'),
                                keyPermissionsGroup = [];
                                if (_.includes(pluckedGroupChildIDs, childId) && groupchild.children[0].id == childId) {
                                    _.map(groupchild.children, function(key2) {
                                        if (key2.id != groupchild.children[0].id) {
                                            _.map(key2.dependencies, function(dependency) {
                                                if (_.includes(key2.dependencies, childId) && status == "3") {
                                                    $('input[name="'+key2.id+'"]').attr('disabled', true);

                                                } else {
                                                    $('input[name="'+key2.id+'"]').attr('disabled', false);

                                                }
                                            })
                                        }
                                    });
                                }

                            });
                        }
					})
                }

                /*
                 Submit form action
                -------------------------------------------------------------------------- */
                scope.submit = function() {
                    // scope.preparePermissions();
                    __Form.process('manage.user.role_permission.write.role.create', scope)
                        .success(function(responseData) {
                        appServices.processResponse(responseData, null, function() {
                            // close dialog
                            $scope.closeThisDialog({'role_Added' : true});
                        });
                    });
                };

                /**
                  * Close dialog
                  *
                  * @return void
                  *---------------------------------------------------------------- */
                scope.closeDialog = function() {
                    $scope.closeThisDialog();
                };
            }
        ])
    ;

})(window, window.angular);;
/*!
*  Component  : Dashboard
*  File       : ActivityDataServices.js  
*  Engine     : ActivityDataServices 
----------------------------------------------------------------------------- */

(function(window, angular, undefined) {

    'use strict';

    angular
        .module('app.ActivityDataServices', [])
        .service('ActivityDataServices',[
            '$q', 
            '__DataStore', 
            'appServices',
            ActivityDataServices
        ])

        /*!
         This service use for to get the promise on data
        ----------------------------------------------------------------------------- */

        function ActivityDataServices($q, __DataStore, appServices) {
            
        };

    
})(window, window.angular);
;
/*!
*  Component  : Activity
*  File       : ActivityEngine.js  
*  Engine     : ActivityEngine 
----------------------------------------------------------------------------- */
(function(window, angular, undefined) {

    'use strict';

    angular
        .module('app.ActivityEngine', [])
		
		/**
          * Calendar Controller 
          *
          * inject object $scope
          * inject object __DataStore
          * inject object __Form
          * inject object $stateParams
          *
          * @return  void
          *---------------------------------------------------------------- */

        .controller('ActivityLogListController', [
            '$scope',
            '__DataStore',
            '__Form',
            '$stateParams',
            'appServices',
            'ActivityDataServices',
            function ($scope, __DataStore, __Form, $stateParams, appServices, ActivityDataServices) {
				var dtColumnsData = [
					{
	                    "name"      : "created_at",
	                    "orderable" : true,
	                },
					{
	                    "name"      : "created_by_user",
	                    "orderable" : true,
	                },
	                {
	                    "name"      : "entity_type",
	                    "orderable" : true,
	                },
					{
	                    "name"      : "ip",
	                    "orderable" : false,
	                },
                    {
                        "name"      : 'activity'
                    },
                    {
                        "name"      : 'description'
                    }
	            ],
	            scope   = this;

	            //form setup
		        scope  = __Form.setup(scope, 'activity_form_filters', 'activityLogsData', {
	                secured : false,
	                unsecuredFields : []
	            });

	            /**
	            * Declare separate variables do not use non-repeatedly
	            *
	            * @return  void
	            *---------------------------------------------------------- */
				scope.getDate = function(duration, dateFrom, dateTo){
					scope.startDate = dateFrom;
					scope.endDate = dateTo;
					scope.duration = duration;
				};
				 
				/**
	            * Start Date greater than end date ,then convert start date to end date
	            *
	            * @return  void
	            *---------------------------------------------------------- */
				scope.changeDate = function(startDate, endDate){
					
					if(scope.startDate > scope.endDate){
						
						scope.endDate = scope.startDate;
					}
				};

	            /**
	            * define all Variables data
	            *
	            * @return  void
	            *---------------------------------------------------------- */
				var dateFrom, dateTo,
				 	
					startCurrentMonth = moment().startOf('month').format('YYYY-MM-D'),
					endCurrentMonth = moment().endOf('month').format('YYYY-MM-D'),

					startLastMonth = moment().subtract(1,'months').startOf('month').format('YYYY-MM-D'),
					endLastMonth = moment().subtract(1,'months').endOf('month').format('YYYY-MM-D'),

					startCurrentWeek = moment().startOf('week').format('YYYY-MM-D'),
					endCurrentWeek = moment().endOf('week').format('YYYY-MM-D'),
					
					startLastWeek = moment().subtract(1,'week').startOf('week').format('YYYY-MM-D'),
					endLastWeek = moment().subtract(1,'week').endOf('week').format('YYYY-MM-D'),

					startToday = moment().startOf('day').format('YYYY-MM-D'),
					endToday = moment().endOf('day').format('YYYY-MM-D'),

					startYesterday = moment().subtract(1, 'day').format('YYYY-MM-D'),
					endYesterday = moment().subtract(1, 'day').format('YYYY-MM-D'),

					startLastYear = moment().subtract(1, 'years').startOf('years').format('YYYY-MM-D'),
					endLastYear = moment().subtract(1, 'years').endOf('years').format('YYYY-MM-D'),

					startCurrentYear = moment().startOf('years').format('YYYY-MM-D'),
					endCurrentYear = moment().endOf('years').format('YYYY-MM-D'),
					
					startLastThirtyDays = moment().subtract(30, 'days').format('YYYY-MM-D'),
					endLastThirtyDays =  moment().format('YYYY-MM-D');

				/**
	            * Get the all Duration value and use moment library to fetch date
	            *
	            * @return  void
	            *---------------------------------------------------------- */
				scope.activityDataTable = function(duration, startDate, endDate) {
				
					switch(parseInt(duration)) {
						case 1:
						    dateFrom = startCurrentMonth;
							dateTo   = endCurrentMonth;
							scope.getDate(duration, dateFrom, dateTo);
							
						break;

						case 2:
							dateFrom = startLastMonth;
							dateTo = endLastMonth;
							scope.getDate(duration, dateFrom, dateTo);
							
						break;

						case 3:
							dateFrom = startCurrentWeek;
							dateTo = endCurrentWeek;
							scope.getDate(duration, dateFrom, dateTo);
							
						break;

						case 4:
							dateFrom = startLastWeek;
						    dateTo = endLastWeek;
							scope.getDate(duration, dateFrom, dateTo);
						   
						break;

						case 5:
						    dateFrom = startToday;
							dateTo = endToday;
							scope.getDate(duration, dateFrom, dateTo);
							
						break;

						case 6:
							dateFrom = startYesterday;
							dateTo = endYesterday;
							scope.getDate(duration, dateFrom, dateTo);
							
						break;

						case 7:
							dateFrom = startLastYear;
							dateTo = endLastYear;
							scope.getDate(duration, dateFrom, dateTo);
							
						break;
						case 8:
							dateFrom = startCurrentYear;
							dateTo = endCurrentYear;
							scope.getDate(duration, dateFrom, dateTo);
							
						break;
						case 9:
							dateFrom = startLastThirtyDays;
							dateTo = endLastThirtyDays;
							scope.getDate(duration, dateFrom, dateTo);
							
						break;
						case 10:
							var manipulateDate = "Add Custom date";
						break;
					};

				};

	            /**
	            * Request to server
	            *
	            * @return  void
	            *---------------------------------------------------------- */
	            scope.dateChange = function() {
	            	
	            	if (scope.activityLogDataTable){
						scope.activityLogDataTable.destroy();
					}

		            scope.activityLogDataTable = __DataStore.dataTable('#lwActivityLogList', {
		                url         : {
							'apiURL'    : 'manage.activity_log.read.list',
							'startDate' : scope.startDate,
							'endDate'   : scope.endDate
						},
		                dtOptions   : {
		                    "searching": true,
		                    "order": [[ 0, 'desc' ]],
		                    "pageLength" : 25
		                },
		                columnsData : dtColumnsData, 
		                scope       : $scope
		            }, null, function(responseData) {
		            	scope.durations 	= responseData._options.durations;
		            });
	            };
	            /*
	            Reload current datatable
	            ------------------------------------------------------------ */
	            scope.reloadDT = function() {
	                __DataStore.reloadDT(scope.activityLogDataTable);
	            };
	            
	            // when add new record 
	            $scope.$on('activity_added_or_updated', function (data) {
	                
	                if (data) {
	                    // scope.reloadDT();
	                    scope.dateChange('Today',moment().format('YYYY-MM-D'),moment().format('YYYY-MM-D'));
	                }

	            });
				
				
				/**
	            * Calling activityDataTable() function to get the current value.
	            *
	            * @return  void
	            *---------------------------------------------------------- */
				scope.activityDataTable('1',moment().format('YYYY-MM-D'),moment().format('YYYY-MM-D'));
				
				scope.dateChange();
            }
        ])
	;	

})(window, window.angular);;
/*!
*  Component  : Project
*  File       : ProjectDataServices.js  
*  Engine     : ProjectServices 
----------------------------------------------------------------------------- */

(function(window, angular, undefined) {

    'use strict';

    angular
        .module('app.ProjectDataServices', [])
        .service('ProjectDataService',[
            '$q', 
            '__DataStore',
            'appServices',
            ProjectDataService
        ])

        /*!
         This service use for to get the promise on data
        ----------------------------------------------------------------------------- */

        function ProjectDataService($q, __DataStore, appServices) {

		    /*
		    Get Add Support Data
		    -------------------------------------------------------------- */
		    this.getAddSupportData = function() {

		        //create a differed object
		        var defferedObject = $q.defer();

		        __DataStore.fetch('manage.project.read.support_data')
		            .success(function(responseData) {

		            appServices.processResponse(responseData, null, function(reactionCode) {

		                //this method calls when the require        
		                //work has completed successfully        
		                //and results are returned to client        
		                defferedObject.resolve(responseData.data);
		            }); 
		        });

		        //return promise to caller          
		        return defferedObject.promise; 
		    };

		    /*
		    Get Edit Support Data
		    -------------------------------------------------------------- */
		    this.getEditSupportData = function(projectIdOrUid) {

		        //create a differed object
		        var defferedObject = $q.defer();

		        __DataStore.fetch({
		            'apiURL': 'manage.project.read.update.data',
		            'projectIdOrUid'   : projectIdOrUid
		        }, {fresh:true}).success(function(responseData) {

		            appServices.processResponse(responseData, null, function(reactionCode) {

		                //this method calls when the require        
		                //work has completed successfully        
		                //and results are returned to client        
		                defferedObject.resolve(responseData.data);
		            }); 
		        });

		        //return promise to caller          
		        return defferedObject.promise; 
		    };

		    /*
		    Get Edit Support Data
		    -------------------------------------------------------------- */
		    this.getProjectDetails = function(projectIdOrUid) {

		        //create a differed object
		        var defferedObject = $q.defer();

		        __DataStore.fetch({
		            'apiURL': 'manage.project.read.details.data',
		            'projectIdOrUid'   : projectIdOrUid
		        }).success(function(responseData) {

		            appServices.processResponse(responseData, null, function(reactionCode) {

		                //this method calls when the require        
		                //work has completed successfully        
		                //and results are returned to client        
		                defferedObject.resolve(responseData.data);
		            }); 
		        });

		        //return promise to caller          
		        return defferedObject.promise; 
		    };

    
        };

})(window, window.angular);;
/*!
*  Component  : Project
*  File       : Project.js  
*  Engine     : Project 
----------------------------------------------------------------------------- */

(function(window, angular, undefined) {

    'use strict';

    angular
        .module('app.ProjectEngine', [])
        
         
        /**
        * Project List Controller
        *
        * inject object $scope
        * inject object __DataStore
        * inject object __Form
        * inject object $state
        * inject object appServices
        * inject object $rootScope
        * inject object ProjectDataService
        *
        * @return  void
        *---------------------------------------------------------------- */

        .controller('ProjectListController', [
            '$scope',                
            '__DataStore',                
            '__Form',                
            '$state',                
            'appServices',                
            '$rootScope',                
            'ProjectDataService',
        function ( $scope, __DataStore, __Form, $state, appServices, $rootScope, ProjectDataService) {
            var dtColumnsData = [
                    {
                        "name"      : "name",
                        "orderable" : true,
                        'template'	: '#projectDetailsTemplate'
                    },
                    {
                        "name"      : "created_at",
                        "orderable" : true,
                        'template'	: '#projectCreatedAtTemplate'
                    },
                    {
                        "name"      : "updated_at",
                        "orderable" : true,
                    },
                    {
                        "name"      : "formatted_status",
                    },
                    {
                        "name"      : "formatted_type",
                    },
                    {
                        "name"      : null,
                        "template"  : "#projectActionColumnTemplate"
                    }
                ],
                scope   = this;

                /**
                * Get general user test as a datatable source object  
                *
                * @return  void
                *---------------------------------------------------------- */

                scope.projectDataTable = __DataStore.dataTable('#lwprojectList', {
                    url         : 'manage.project.read.list', 
                    dtOptions   : {
                        "searching": true
                    },
                    columnsData : dtColumnsData, 
                    scope       : $scope
                });

                /*
                Reload current datatable
                ------------------------------------------------------------ */
                scope.reloadDT = function() {
                    __DataStore.reloadDT(scope.projectDataTable);
                };
                
                // when add new record 
                $scope.$on('project_added_or_updated', function (data) {
                    
                    if (data) {
                        scope.reloadDT();
                    }

                });
				
				/*
                Project Add
                ------------------------------------------------------------ */
				scope.addProject = function() {

					appServices.showDialog({}, {
	                    templateUrl     : "project.add-dialog",
	                    controller : 'ProjectAddController as projectAddCtrl',
	                    resolve : {
			                projectAddData : ['ProjectDataService', function(ProjectDataService) {
								return ProjectDataService.getAddSupportData();
			                }]
			            }
	                }, function(promiseObj) {

	                    if (_.has(promiseObj.value, 'project_added_or_updated') && promiseObj.value.project_added_or_updated) {

	                        $rootScope.$broadcast('project_added_or_updated', true);
	                    }

	                    //$state.go('project');

	                });

				}

                /*
                Project edit
                ------------------------------------------------------------ */
				scope.editProject = function(projectIdOrUid) {

					appServices.showDialog({
						'projectIdOrUid' : projectIdOrUid
					}, {
	                    templateUrl     : "project.edit-dialog",
	                    controller : 'ProjectEditController as projectEditCtrl',
	                    resolve : {
			                projectEditData : ['ProjectDataService', function(ProjectDataService) {
								return ProjectDataService.getEditSupportData(projectIdOrUid);
							}]
			            }
	                }, function(promiseObj) {

	                    if (_.has(promiseObj.value, 'project_added_or_updated') && promiseObj.value.project_added_or_updated) {

	                        $rootScope.$broadcast('project_added_or_updated', true);
	                    }
	                });

				}

				/*
                Project details
                ------------------------------------------------------------ */
				scope.showProjectDetails = function(projectIdOrUid) {

					appServices.showDialog({
						'projectIdOrUid' : projectIdOrUid
					}, {
	                    templateUrl     : "project.details-dialog",
	                    controller : 'ProjectDetailsController as projectDetailsCtrl',
	                    resolve : {
			                ProjectDetailsData : ['ProjectDataService', function(ProjectDataService) {
								return ProjectDataService.getProjectDetails(projectIdOrUid);
							}]
			            }
	                }, function(promiseObj) {
	                });

				}

                /**
                * Show Embeded Script Dialog 
                *
                * inject projectIdUid
                *
                * @return    void
                *---------------------------------------------------------------- */
                scope.showEmbedScriptDialog = function(projectSlug) {

                    appServices.showDialog({
                        'projectSlug' : projectSlug,
                        'embededType': 1
                    }, {
                        templateUrl : "embed.embed-script-dialog",
                        controller  : 'EmbedScriptDialogController as EmbedScriptDialogCtrl',
                    }, function(promiseObj) {
                    });

                }

               /**
                * project delete 
                *
                * inject projectIdUid
                *
                * @return    void
                *---------------------------------------------------------------- */

                scope.delete = function(projectIdOrUid, projectName) {

                	scope.projectName = projectName;

                	_.defer(function(){

                        var lwProjectDeleteConfirmTextMsg = $('#lwProjectDeleteConfirmTextMsg');
 	 					 
                        __globals.showConfirmation({
                            html                : lwProjectDeleteConfirmTextMsg .attr('data-message'),
                            confirmButtonText   : lwProjectDeleteConfirmTextMsg .attr('data-delete-button-text')
                        },
                        function() {

                        	__DataStore.post({
	                            'apiURL' : 'manage.project.write.delete',
	                            'projectIdOrUid' : projectIdOrUid
	                        }).success(function(responseData) {
	                        
								var message = responseData.data.message;

                                appServices.processResponse(responseData, {

                                        error : function() {

                                            __globals.showConfirmation({
                                                title   : 'Deleted!',
                                                text    : message,
                                                type    : 'error'
                                            });

                                        }
                                    },
                                    function() {

                                        __globals.showConfirmation({
                                            title   : 'Deleted!',
                                            text    : lwProjectDeleteConfirmTextMsg .attr('success-msg'),
                                            type    : 'success'
                                        });
                                        scope.reloadDT();   // reload datatable

                                    }
                                );    


	                        });

                        })

                   });

                };


        }
        ])
        // Project List Controller ends here

        /**
        * Project Controller
        *
        * inject object $scope
        * inject object __DataStore
        * inject object __Form
        * inject object $state
        * inject object appServices
        * inject object $rootScope
        *
        * @return  void
        *---------------------------------------------------------------- */

        .controller('ProjectAddController', [
            '$scope',
            '__DataStore',
            '__Form',
            '$state',
            'appServices',
            '$rootScope',
            'projectAddData',
            'lwFileUploader',
            '__Utils',
        function ( $scope,  __DataStore,  __Form,  $state,  appServices,  $rootScope, projectAddData, lwFileUploader, __Utils) {

                var scope = this;
 				scope.project_type = projectAddData.project_type;
 				scope.recent_languages = projectAddData.recent_languages;
                scope.showLoader = true;
                scope  = __Form.setup(scope, 'project_form', 'projectData', {
                    secured : false,
                    unsecuredFields : []
                });

                scope.projectData.status = 1;
                scope.projectData.type = 1;
                scope.languages = projectAddData.languages;
                scope.languageSelectize  = __globals.getSelectizeOptions({
                    valueField  : 'id',
                    labelField  : 'name',
                    searchField : ['name'],
                    maxItems : null,
                    plugins : ['remove_button'],
                    onChange : function(value) {
                    	scope.updatePrimaryLangOptions(value);
                    }
                });

                scope.primaryLanguageSelectize  = __globals.getSelectizeOptions({
                    valueField  : 'id',
                    labelField  : 'name',
                    searchField : ['name']
                });

                scope.projectData.article_comments_status = 1;
                scope.projectData.article_votes_status = 1;

                scope.primarylanguages = [];

                // Add New Language
                scope.addNewLanguage = function() {
                    appServices.showDialog(scope, {
                        templateUrl     : __globals.getTemplateURL("language.add-dialog"),
                        controller : 'LanguageAddController as languageAddCtrl',
                    }, function(promiseObj) {
                        if (_.has(promiseObj.value, 'language_added_or_updated') 
                            && promiseObj.value.language_added_or_updated) {
                            scope.languages.push(promiseObj.value.add_language_data);
                        }
                    });
                }    

                scope.updatePrimaryLangOptions = function(selected_lang) {
                	scope.primarylanguages = [];
                	scope.projectData.primary_language = '';
                	_.map(scope.languages, function(langoption) {
            			if (_.includes(selected_lang.split(','), langoption.id)) {
            				scope.primarylanguages.push(langoption);
            			}
            		});
                }

                scope.projectData.project_languages = [];
                /*
				* select recent language
                */
                scope.selectRecentLanguage = function(langId) {
                	if (_.isUndefined(scope.projectData.project_languages)) {
                		scope.projectData.project_languages = [];
                	}
                	
                	if (!_.includes(scope.projectData.project_languages, langId)) {
               			scope.projectData.project_languages.push(langId);
                	}
                }

                /*
	            add dialog
	            ------------------------------------------------------------ */
	            scope.openVersionAddDialog = function(projectIdOrUid) {

	                appServices.showDialog({
	                    'projectUid' : projectIdOrUid
	                }, {
	                    templateUrl : __globals.getTemplateURL("version.add-dialog"),
	                    controller  : 'VersionAddController as versionAddCtrl',
	                    resolve : {
	                        versionAddData : ['VersionDataService', function(VersionDataService) {
	                            return VersionDataService.getAddSupportData(projectIdOrUid);
	                        }]
	                    }
	                }, function(promiseObj) {

	                    if (_.has(promiseObj.value, 'version_added') && promiseObj.value.version_added) {

	                        $rootScope.$broadcast('load_versions_list');
	                    }

	                });

	            };

                /**
                  * Submit form
                  *
                  * @return  void
                  *---------------------------------------------------------------- */

                scope.submit = function() {

                    __Form.process('manage.project.write.create', scope)
                    .success(function(responseData) {

                        appServices.processResponse(responseData, null, function() {

                        	if (responseData.reaction) {
                        		$scope.closeThisDialog( {'project_added_or_updated' : true} );
                                if ($rootScope.canAccess('manage.project.version.read.list')) {
                                    $state.go('project_versions', {
                                        'projectIdOrUid' : responseData.data.projectIdOrUid
                                    });
                                }
                        	}
                        });    

                    });
                };

                // uploader file instance
                $scope.upload = function() {
				
                    lwFileUploader.upload({
					    'url' : __Utils.apiURL('media.upload.write.project')
					}, function(response) {
						
						var fileDetails =  response.result.data.fileDetails;
						
						if(!_.isUndefined(fileDetails)) {

							if (fileDetails.extension === 'ico') {
								scope.projectData.favicon_image = fileDetails.file_name;
								scope.favicon_image_url = fileDetails.url;
							} else {
								scope.projectData.logo_image = fileDetails.file_name;
								scope.logo_image_url = fileDetails.url;
							}

						}

					});
                };

                $(document).bind('dragover', function (e) {
                	$scope.upload();
				});

				$rootScope.$on('lw-loader-event-start', function (event, data) {
					
					$scope.loading = true; 
					$("#lwFileupload").attr("disabled", true);
				});
				
				$rootScope.$on('lw-loader-event-stop', function (event, data) {

					$scope.loading = false; 
					 $("#lwFileupload").attr("disabled", false); 
				});


                /**
                  * Generate slug
                  *
                  * @return  void
                  *---------------------------------------------------------------- */
                scope.generateSlug = function(string) {
                	scope.projectData.slug = __globals.slug(string);
                }

                /**
                  * Close dialog
                  *
                  * @return  void
                  *---------------------------------------------------------------- */

                scope.closeDialog = function() {
                    $scope.closeThisDialog();
                };
            }
        ])
        // ProjectAddController ends here

        /**
        * Project Edit Controller
        *
        * inject object $scope
        * inject object __DataStore
        * inject object __Form
        * inject object $state
        * inject object appServices
        * inject object $rootScope
        *
        * @return  void
        *---------------------------------------------------------------- */

        .controller('ProjectEditController', [
            '$scope',
            '__DataStore',
            '__Form',
            '$state',
            'appServices',
            '$rootScope',
            'projectEditData',
			'lwFileUploader',
			'__Utils',
        function ( $scope,  __DataStore,  __Form,  $state,  appServices,  $rootScope, projectEditData, lwFileUploader, __Utils) {

            var scope = this;
            scope.showLoader = true;
			scope.projectIdOrUid = $scope.ngDialogData.projectIdOrUid;
            
            scope  = __Form.setup(scope, 'project_form', 'projectData', {
                secured : false,
                unsecuredFields : []
            });

            var requestData = projectEditData;
            scope = __Form.updateModel(scope, requestData.edit_data);
            scope.logoImageExists = requestData.logoImageExists;
            scope.faviconImageExist = requestData.faviconImageExist;
            scope.showLoader = false;
            scope.languages = projectEditData.languages;
            scope.projectLanguages = projectEditData.projectLanguages;

            scope.languageSelectize  = __globals.getSelectizeOptions({
                valueField  : 'lang_id',
                labelField  : 'name',
                searchField : ['name'],
                plugins : ['remove_button'],
				maxItems    : null,
				delimiter   : ',',
				persist     : false,
                onDelete : function(values) {
                    var $instance = this;
                    scope.deleteLanguage(values[0], $instance)
                    .then(function(success) {
                        return true;
                    })
                    .catch(function(error) {
                        return false;
                    });
                    return false;
                }
            });

            scope.primaryLanguageSelectize  = __globals.getSelectizeOptions({
                valueField  : 'lang_id',
                labelField  : 'name',
                searchField : ['name']
            });

			scope.primarylanguages = [];

            /*
            * Delete Language
            */
            scope.deleteLanguage = function(languageId, instance) {
                return new Promise(function(resolve, reject) {
                    var $lwLanguageDeleteConfirm = $('#lwLanguageDeleteConfirm');
                    if (_.includes(scope.projectLanguages, languageId)) {
                        __globals.showConfirmation({
                            html                : $lwLanguageDeleteConfirm.attr('data-message'),
                            confirmButtonText   : $lwLanguageDeleteConfirm.attr('data-delete-button-text')
                        },
                        function() {
                            __DataStore.post({
                                'apiURL' : 'manage.project.write.language_delete',
                                'projectIdOrUid' : scope.projectIdOrUid,
                                'languageId' : languageId
                            }).success(function(responseData) {                        
                                var message = responseData.data.message;
                                appServices.processResponse(responseData, {
                                    error : function() {
                                        __globals.showConfirmation({
                                            title   : 'Deleted!',
                                            text    : message,
                                            type    : 'error'
                                        });
                                        reject('error');
                                    }
                                },
                                function() {
                                    __globals.showConfirmation({
                                        title   : 'Deleted!',
                                        text    : scope.successMsgText,
                                        type    : 'success'
                                    });
                                    instance.removeOption(languageId);
                                    instance.refreshOptions();
                                    instance.clearCache();
                                    resolve('success');
                                });
                            });
                        })
                    } else {
                        instance.removeOption(languageId);
                        instance.refreshOptions();
                        instance.clearCache();
                    }
                });
            }

            /**
            * Delete Logo or Favicon
            *
            * @return  void
            *---------------------------------------------------------------- */
            scope.deleteMedia = function(mediaType) {
                __DataStore.post({
                    'apiURL': 'manage.project.write.media_delete',
                    'projectIdOrUid': scope.projectIdOrUid,
                    'mediaType': mediaType
                }, {}).success(function(responseData) {
                    appServices.processResponse(responseData, null, function(reactionCode) {
                        if (reactionCode == 1) {
                            if (mediaType == 1) {
                                scope.logo_image_url = '';
                            } else if (mediaType == 2) {
                                scope.favicon_image_url = '';
                            }
                        }
                    });  
                });
            }

            /**
            * Submit form
            *
            * @return  void
            *---------------------------------------------------------------- */

            scope.submit = function() {

                __Form.process({
                    'apiURL': 'manage.project.write.update',
                    'projectIdOrUid'   : scope.projectIdOrUid
                }, scope).success(function(responseData) {

                        appServices.processResponse(responseData, null, function() {
                            $scope.closeThisDialog( {'project_added_or_updated' : true} );
                        });    
                });

            };

   			scope.logo_image_url = scope.projectData.logo_image_url;
   			scope.favicon_image_url = scope.projectData.favicon_image_url;

            // uploader file instance
                $scope.upload = function() {
				
                    lwFileUploader.upload({
					    'url' : __Utils.apiURL('media.upload.write.project')
					}, function(response) {
						
						var fileDetails =  response.result.data.fileDetails;
						
						if(!_.isUndefined(fileDetails)) {

							if (fileDetails.extension === 'ico') {
								scope.projectData.favicon_image = fileDetails.file_name;
								scope.favicon_image_url = fileDetails.url;
							} else {
								scope.projectData.logo_image = fileDetails.file_name;
								scope.logo_image_url = fileDetails.url;
							}

						}

					});
                };

                $(document).bind('dragover', function (e) {
                	$scope.upload();
				});

				$rootScope.$on('lw-loader-event-start', function (event, data) {
					
					$scope.loading = true; 
					$("#lwFileupload").attr("disabled", true);
				});
				
				$rootScope.$on('lw-loader-event-stop', function (event, data) {

					$scope.loading = false; 
					 $("#lwFileupload").attr("disabled", false); 
				});

            /**
              * Generate slug
              *
              * @return  void
              *---------------------------------------------------------------- */
            scope.generateSlug = function(string) {
            	scope.projectData.slug = __globals.slug(string);
            }

            /**
            * Close dialog
            *
            * @return  void
            *---------------------------------------------------------------- */

            scope.closeDialog = function() {
                $scope.closeThisDialog();
            };

            }

        ])
        // Project Edit Controller ends here


         /**
        * Project Details Controller
        *
        * inject object $scope
        * inject object __DataStore
        * inject object __Form
        * inject object $state
        * inject object appServices
        * inject object $rootScope
        *
        * @return  void
        *---------------------------------------------------------------- */

        .controller('ProjectDetailsController', [
            '$scope',
            'appServices',
            'ProjectDetailsData', 
        function ( $scope, appServices, ProjectDetailsData) {

                var scope = this;
 				scope.projectData = ProjectDetailsData.projectData;

                /**
                  * Close dialog
                  *
                  * @return  void
                  *---------------------------------------------------------------- */

                scope.closeDialog = function() {
                    $scope.closeThisDialog();
                };
            }
        ])
        // ProjectAddController ends here

    ;

})(window, window.angular);;
/*!
*  Component  : Article
*  File       : ArticleDataServices.js  
*  Engine     : ArticleServices 
----------------------------------------------------------------------------- */

(function(window, angular, undefined) {

    'use strict';

    angular
        .module('app.ArticleDataServices', [])
        .service('ArticleDataService',[
            '$q', 
            '__DataStore',
            'appServices',
            ArticleDataService
        ])

        /*!
         This service use for to get the promise on data
        ----------------------------------------------------------------------------- */

        function ArticleDataService($q, __DataStore, appServices) {

            
            /*
            Get Add Support Data
            -------------------------------------------------------------- */
            this.getAddSupportData = function(projectUid, versionUid) {
        
                //create a differed object
                var defferedObject = $q.defer();
        		
                __DataStore.fetch({
					'apiURL': 'manage.article.read.support_data',
                    'projectUid'   : (projectUid) ? projectUid : null,
                    'versionUid'   : (versionUid) ? versionUid : null
                }).success(function(responseData) {
        
                    appServices.processResponse(responseData, null, function(reactionCode) {
        
                        //this method calls when the require        
                        //work has completed successfully        
                        //and results are returned to client        
                        defferedObject.resolve(responseData.data);
                    }); 
                });
        
                //return promise to caller          
                return defferedObject.promise; 
            };
            /*
            Get Edit Support Data
            -------------------------------------------------------------- */
            this.getEditSupportData = function(articleIdOrUid, projectUid, versionUid) {
        
                //create a differed object
                var defferedObject = $q.defer();
        
                __DataStore.fetch({
                    'apiURL': 'manage.article.read.update.data',
                    'articleIdOrUid'   : articleIdOrUid,
                    'projectUid'   : (projectUid) ? projectUid : null,
                    'versionUid'   : (versionUid) ? versionUid : null

                }).success(function(responseData) {
        
                    appServices.processResponse(responseData, null, function(reactionCode) {
        
                        //this method calls when the require        
                        //work has completed successfully        
                        //and results are returned to client        
                        defferedObject.resolve(responseData.data);
                    }); 
                });
        
                //return promise to caller          
                return defferedObject.promise; 
            };

            /*
            Get Votes Support Data
            -------------------------------------------------------------- */
            this.getVotesSupportData = function(articleIdOrUid) {
        
                //create a differed object
                var defferedObject = $q.defer();
        
                __DataStore.fetch({
                    'apiURL': 'manage.article.votes.read',
                    'articleIdOrUid'   : articleIdOrUid,
                }).success(function(responseData) {
        
                    appServices.processResponse(responseData, null, function(reactionCode) {
        
                        //this method calls when the require        
                        //work has completed successfully        
                        //and results are returned to client        
                        defferedObject.resolve(responseData.data);
                    }); 
                });
        
                //return promise to caller          
                return defferedObject.promise; 
            };

            /*
            Get article content details
            -------------------------------------------------------------- */
            this.getArticleContentDetails = function(contentUid, articleIdOrUid) {
        
                //create a differed object
                var defferedObject = $q.defer();
        
                __DataStore.fetch({
                    'apiURL': 'manage.article.read.content_details',
                    'articleIdOrUid' : articleIdOrUid,
					'contentUid' : contentUid
                }).success(function(responseData) {
        
                    appServices.processResponse(responseData, null, function(reactionCode) {
        
                        //this method calls when the require        
                        //work has completed successfully        
                        //and results are returned to client        
                        defferedObject.resolve(responseData.data);
                    }); 
                });
        
                //return promise to caller          
                return defferedObject.promise; 
            };

            /*
            Get article details
            -------------------------------------------------------------- */
            this.getArticleDetails = function(articleIdOrUid) {
        	
                //create a differed object
                var defferedObject = $q.defer();
        
                __DataStore.fetch({
                    'apiURL': 'manage.article.read.details',
                    'articleIdOrUid' : articleIdOrUid
                }).success(function(responseData) {
        
                    appServices.processResponse(responseData, null, function(reactionCode) {
        
                        //this method calls when the require        
                        //work has completed successfully        
                        //and results are returned to client        
                        defferedObject.resolve(responseData.data);
                    }); 
                });
        
                //return promise to caller          
                return defferedObject.promise; 
            };

            /*
            Get Articles
            -------------------------------------------------------------- */
            this.getArticles = function(projectUid, versionUid) {
        
                //create a differed object
                var defferedObject = $q.defer();
        
                __DataStore.fetch({
                    'apiURL'    : 'manage.article.read.list',
                    'projectUid?' : projectUid,
                    'versionUid?' : versionUid,
                }).success(function(responseData) {
        
                    appServices.processResponse(responseData, null, function(reactionCode) {
        
                        //this method calls when the require        
                        //work has completed successfully        
                        //and results are returned to client        
                        defferedObject.resolve(responseData.data);
                    }); 
                });
        
                //return promise to caller          
                return defferedObject.promise; 
            };


        };

})(window, window.angular);;
/*!
*  Component  : Article
*  File       : Article.js  
*  Engine     : Article 
----------------------------------------------------------------------------- */

(function(window, angular, undefined) {

    'use strict';

    angular
        .module('app.ArticleEngine', [])
        
         
        /**
        * Article List Controller
        *
        * inject object $scope
        * inject object __DataStore
        * inject object __Form
        * inject object $state
        * inject object appServices
        * inject object $rootScope
        * inject object ArticleDataService
        *
        * @return  void
        *---------------------------------------------------------------- */

        .controller('ArticleListController', [
            '$scope',                
            '__DataStore',                
            '__Form',                
            '$state',                
            'appServices',
            '$rootScope',                
            'appNotify',
            'GetVersionDetails',
            'GetArticles',
        function ( $scope, __DataStore, __Form, $state, appServices, $rootScope, appNotify, GetVersionDetails, GetArticles) {

            var scope   = this;

                scope.projectUid  = $state.params.projectUid;
                scope.versionUid  = $state.params.versionUid;
                
                scope.version_info = GetVersionDetails.versionData;
                scope.project_info = GetVersionDetails.project_info;
                scope.articles = GetArticles.articles;
                scope.projectSlug  = GetArticles.projectSlug;
				scope.versionSlug  = GetArticles.versionSlug;
                scope.languages__id = GetArticles.languages__id;
                /**
                 * Get Articles Tree Data
                 * @return 
                 */
                scope.getArticles = function() 
                {
                    __DataStore.fetch({
                        'apiURL'    : 'manage.article.read.list',
                        'projectUid?' : scope.projectUid,
                        'versionUid?' : scope.versionUid,
                    }).success(function(responseData) {
            
                        appServices.processResponse(responseData, null, function(reactionCode) {

                            scope.articles = responseData.data.articles;
							scope.projectSlug  = responseData.data.projectSlug;
							scope.versionSlug  = responseData.data.versionSlug;
                            scope.languages__id = responseData.data.languages__id;
                        }); 
                    });
                };

                
                // when add new record 
                $scope.$on('article_added_or_updated', function (data) {
                    
                    if (data) {
                        scope.reloadDT();
                    }

                });


                /*
                Project Add
                ------------------------------------------------------------ */
				scope.addArticle = function() {

					appServices.showDialog({}, {
	                    templateUrl     : "article.add-dialog",
	                    controller : 'ArticleAddController as articleAddCtrl',
	                    resolve : {
			                articleAddData : ['ArticleDataService', function(ArticleDataService) {
								return ArticleDataService.getAddSupportData();
			                }]
			            }
	                }, function(promiseObj) {

	                    if (_.has(promiseObj.value, 'article_added_or_updated') && promiseObj.value.article_added_or_updated) {

	                        $rootScope.$broadcast('article_added_or_updated', true);
	                    }
	                });
				}

				scope.showArticleContentDetails = function(contentUid, articleIdOrUid) {

					appServices.showDialog({
						'articleIdOrUid' : articleIdOrUid,
						'contentUid' : contentUid
					}, {
	                    templateUrl     : "article.article-content-details-dialog",
	                    controller : 'ArticleContentDetailsController as ArticleContentDetailsCtrl',
	                    resolve : {
			                ArticleContentData : ['ArticleDataService', function(ArticleDataService) {
								return ArticleDataService.getArticleContentDetails(contentUid, articleIdOrUid);
							}]
			            }
	                }, function(promiseObj) {
	                });
				}

               /**
                * project delete 
                *
                * inject projectIdUid
                *
                * @return    void
                *---------------------------------------------------------------- */

                scope.delete = function(articleIdOrUid, articleName) {

                	scope.articleName = articleName;

                	_.defer(function(){

                        var lwArticleDeleteConfirmTextMsg = $('#lwArticleDeleteConfirmTextMsg');
 	 					 
                        __globals.showConfirmation({
                            html                : lwArticleDeleteConfirmTextMsg .attr('data-message'),
                            confirmButtonText   : lwArticleDeleteConfirmTextMsg .attr('data-delete-button-text')
                        },
                        function() {

                        	__DataStore.post({
								'apiURL' : 'manage.article.write.delete',
                            	'articleIdOrUid' : articleIdOrUid
	                        }).success(function(responseData) {
	                        
								var message = responseData.data.message;

                                appServices.processResponse(responseData, {

                                        error : function() {

                                            __globals.showConfirmation({
                                                title   : 'Deleted!',
                                                text    : message,
                                                type    : 'error'
                                            });

                                        }
                                    },
                                    function() {

                                        __globals.showConfirmation({
                                            title   : 'Deleted!',
                                            text    : lwArticleDeleteConfirmTextMsg .attr('success-msg'),
                                            type    : 'success'
                                        });
                                        
                                        scope.getArticles(); // reload content

                                    }
                                );    


	                        });

                        })

                   });

                };

				scope.showArticleEmbedScriptDialog = function(projectSlug, versionSlug, articleSlug, lang) {
                    
					appServices.showDialog({
						'projectSlug' : projectSlug,
                        'versionSlug' : versionSlug,
                        'articleSlug' : articleSlug,
                        'language': lang,
                        'embededType': 3
					}, {
	                    templateUrl : "embed.embed-script-dialog",
	                    controller  : 'EmbedScriptDialogController as EmbedScriptDialogCtrl',
	                }, function(promiseObj) {
	                });
                }                

                $scope.toggle = function (scope) {
                    scope.toggle();
                };
            
                $scope.treeOptions = {
                    accept: function(sourceNodeScope, destNodesScope, destIndex) {
                        return true;
                    },
                    beforeDrop : function (e) {

                        var sourceValue  = e.source.nodeScope.$modelValue,
                            parentData   = e.dest.nodesScope.node ? 
                                            e.dest.nodesScope.node 
                                            : undefined,
                            newParentId = !_.isUndefined(parentData) ? parentData._id : '',
                            nodesScopeArr = e.dest.nodesScope.$modelValue;

                            _.delay(function() {
                                if (sourceValue) {
                                    if (nodesScopeArr.length > 0) {
                                        var listOrderData = [];
    
                                        _.forEach(_.compact(nodesScopeArr), function(item, key) {
                                            
                                            listOrderData.push({
                                                _id         : item._id,
                                                list_order  : key + 1,
                                                previous_articles__id : newParentId
                                            });
                                          
                                        });
            
                                        __DataStore.post({
                                            'apiURL'         : 'manage.article.write.update_parent',
                                            'articleIdOrUid' : sourceValue._uid
                                        }, {
                                            'parentId'      : newParentId,
                                            'listOrderData' : listOrderData
                                        }).success(function(responseData) {
                    
                                            appServices.processResponse(responseData, null, function(reactionCode) {
                                                scope.getArticles();
                                            }); 
                    
                                        });
                                    }
                                }
                            }, 50);
                        
                    }
                };
        }
        ])
        // Article List Controller ends here

        /**
        * Embed Script Dialog Controller
        *
        * inject object $scope
        * inject object __DataStore
        * inject object __Form
        * inject object $state
        * inject object appServices
        * inject object $rootScope
        *
        * @return  void
        *---------------------------------------------------------------- */

        .controller('EmbedScriptDialogController', [
            '$scope',                 
            '__DataStore',                 
            '__Form',                 
            '$state',                 
            'appServices',                 
            '$rootScope',
        function ( $scope,  __DataStore,  __Form,  $state,  appServices,  $rootScope) {

                var scope = this,
 				    ngDialogData  = $scope.ngDialogData,
                    embededType = ngDialogData.embededType;
 				scope.articleTitle = ngDialogData.title;

                // Prepare data for json stringify
                scope.convertToString = function(embededParams) {
                    var newEmbededData = [];
                    _.forEach(embededParams, function(value) {
                        newEmbededData.push(JSON.stringify(value));
                    });
                    return newEmbededData;
                }

                if (embededType == 1) { // Projects
                    scope.scriptContent = scope.convertToString([
                        ngDialogData.projectSlug
                    ]);
                } else if (embededType == 2) { // Version
                    scope.scriptContent = scope.convertToString([
                        ngDialogData.projectSlug, ngDialogData.versionSlug
                    ]);
                } else if (embededType == 3) { // Article
                    scope.scriptContent = scope.convertToString([
                        ngDialogData.projectSlug, ngDialogData.versionSlug, ngDialogData.articleSlug
                    ]);
                }				

 				/* for article */
 				scope.loadArticleScript = function() {
 					return "\n<script type='text/javascript'>Docsyard.load("+scope.scriptContent+");</script>";
 				}

                /**
                  * Close dialog
                  *
                  * @return  void
                  *---------------------------------------------------------------- */

                scope.closeDialog = function() {
                    $scope.closeThisDialog();
                };
            }
        ])
        // ArticleAddController ends here

        /**
        * Article Controller
        *
        * inject object $scope
        * inject object __DataStore
        * inject object __Form
        * inject object $state
        * inject object appServices
        * inject object $rootScope
        *
        * @return  void
        *---------------------------------------------------------------- */

        .controller('ArticleAddController', [
            '$scope',                 
            '__DataStore',                 
            '__Form',                 
            '$state',                 
            'appServices',                 
            '$rootScope',
            'articleAddData',
            'appNotify',
        function ( $scope,  __DataStore,  __Form,  $state,  appServices,  $rootScope, articleAddData, appNotify) {

                var scope = this;

                scope.showLoader = true;
                scope.primaryArticleExist = true;
                scope.isPrimaryLanguageSelected = true;
                scope  = __Form.setup(scope, 'article_form', 'articleData', {
                    secured : false,
                    unsecuredFields : []
                });

                scope.projectSelectize  = __globals.getSelectizeOptions({
                    valueField  : 'id',
                    labelField  : 'name',
                    searchField : ['name']  
                });

                scope.prevArticle  = __globals.getSelectizeOptions({
                    valueField  : '_id',
                    labelField  : 'title',
                    searchField : ['title'],
                    render: {
                        option: function(item, escape) {
                            var whitespace = '&nbsp',
                            spaceCount = item.count,
                            titleSpace = (item.count > 1) ? whitespace.repeat(spaceCount) + '&#8627 ' : '';
                            return '<div>' +
                                '<span class="title">'+ titleSpace + escape(item.title) +'</span>' 
                                +
                            '</div>';
                        }
                    } 
                });

               // scope.primaryArticleExist = articleAddData.primaryArticleExist;
                scope.articleData.articles_content = articleAddData.articles_content;
                scope.version_info = articleAddData.versionData;
                scope.project_info = articleAddData.projectName;
                scope.articleData.projectUid = $state.params.projectUid;
                scope.articleData.article_type = 1;
                scope.articleData.doc_versions__id = scope.version_info._id;
                scope.projectUid  = $state.params.projectUid;
                scope.articlesList = articleAddData.articles;
                
                //for parent/child differentiation
                scope.requestType = $state.params.requestType;
                scope.projectName = articleAddData.projectName;
 				scope.primaryLanguage = articleAddData.primaryLanguage; 

                //check is prev article exist then set it
                if (_.has($state.params, 'prevArticle')) {
                	scope.articleData.previous_articles__id = $state.params.prevArticle;
                }

                //by default set primary language tab
                //set selected tab id for title validation
                scope.selectTabId = scope.primaryLanguage;

                // Select tab / click on tab
				scope.selectedTab = function(event, primaryLanguageId, languageId) {
					event.preventDefault();
                    //set selected tab id for title validation
                    scope.selectTabId = languageId;

                    if (primaryLanguageId != languageId) {
                        scope.isPrimaryLanguageSelected = false;
                    } else {
                        scope.isPrimaryLanguageSelected = true;
                    }
                    scope.checkPrimaryArticleExist();
				}

                // Check if primary article exist
                scope.checkPrimaryArticleExist = function() {
                    var articleExist = false;
                    if (!scope.isPrimaryLanguageSelected) {
                        _.forEach(scope.articleData.articles_content, function(item) {
                            if ((item.is_primary == 1) && ((_.isEmpty(item.description)) || (_.isEmpty(item.title)))) {
                                articleExist = true;
                            }
                        });
                    }
                    // Check if article exist
                    if (articleExist) {
                        scope.primaryArticleExist = false;
                    } else {
                        scope.primaryArticleExist = true;
                    }
                }

                /**
                  * Submit form
                  *
                  * @return  void
                  *---------------------------------------------------------------- */
 
                scope.submit = function(status) {
                    scope.checkPrimaryArticleExist();
                    // Check if primary language exist
                    if (!scope.primaryArticleExist) {
                        return appNotify.error('You have to add primary language article first.');                       
                    }

                	scope.articleData.status = status;

                    __Form.process({
                        'apiURL'     : 'manage.article.write.create',
                        'requestType' : scope.requestType,
                        'projectUid' : (scope.projectUid) ? scope.projectUid : null
                    }, scope).success(function(responseData) {

                        appServices.processResponse(responseData, null, function() {
                            if (status == 1) {
                                $state.go('project_articles', {'projectUid': scope.projectUid, 'versionUid' : scope.version_info._uid });
                            } else if (status == 2 || status == 3) {
                                $state.go('project_article_edit', {'projectUid': scope.projectUid, 'versionUid' : scope.version_info._uid, 'articleIdOrUid': responseData.data.article_uid });
                            }
                        	
                        });    

                    });
                };

                /**
                  * Close dialog
                  *
                  * @return  void
                  *---------------------------------------------------------------- */

                scope.closeDialog = function() {
                    $scope.closeThisDialog();
                };
            }
        ])
        // ArticleAddController ends here

        /**
        * Article Edit Controller
        *
        * inject object $scope
        * inject object __DataStore
        * inject object __Form
        * inject object $state
        * inject object appServices
        * inject object $rootScope
        *
        * @return  void
        *---------------------------------------------------------------- */

        .controller('ArticleEditController', [
            '$scope',                
            '__DataStore',                
            '__Form',                
            '$state',                
            'appServices',                
            '$rootScope',
            'articleEditData',
        function ( $scope,  __DataStore,  __Form,  $state,  appServices,  $rootScope, articleEditData) {

	            var scope = this;
	            scope.showLoader = true;

                scope  = __Form.setup(scope, 'article_form', 'articleData', {
                    secured : false,
                    unsecuredFields : []
                });
               
                var requestData = articleEditData;
                scope.articlesList = requestData.articles;
                scope.projectName  = requestData.projectName;
                scope.requestType = 2;
                if (_.isNull(requestData.articleData.previous_articles__id)) {
                	scope.requestType = 1;
                }
                scope.version_info = articleEditData.versionData;
                scope.project_info = articleEditData.projectName;
                
                scope = __Form.updateModel(scope, requestData.articleData);

                scope.showLoader = false;

                var articleIdOrUid  = $state.params.articleIdOrUid;
                scope.projectUid = $state.params.projectUid;

                if (!_.isUndefined($state.params.projectUid)) {
 					scope.projectUid  = $state.params.projectUid;
                } else {
                	scope.projectUid = 'null';
                }
                
                scope.article_status = requestData.articleData.article_status;

                scope.prevArticle  = __globals.getSelectizeOptions({
                    valueField  : '_id',
                    labelField  : 'title',
                    searchField : ['title'],
                    render: {
                        option: function(item, escape) {
                            var whitespace = '&nbsp',
                            spaceCount = item.count,
                            titleSpace = (item.count > 1) ? whitespace.repeat(spaceCount) + '&#8627 ' : '';
                            return '<div>' +
                                '<span class="title">'+ titleSpace + escape(item.title) +'</span>' 
                                +
                            '</div>';
                        }
                    } 
                });

               	scope.primaryLanguage = articleEditData.primaryLanguage;

                //by default set primary language tab
                //set selected tab id for title validation
                scope.selectTabId = scope.primaryLanguage;
				scope.selectedTab = function(event, languageId) {
					event.preventDefault();
                    scope.selectTabId = languageId;
				}

				/**
				  * Generate slug
				  *
				  * @return  void
				  *---------------------------------------------------------------- */
				/*scope.generateSlug = function(string, language_id, primary_language) {
					if (language_id == primary_language) {
						scope.articleData.slug = __globals.slug(string);
					}
				}*/

                /**
                * Submit form
                *
                * @return  void
                *---------------------------------------------------------------- */

                scope.submit = function(status) {

                	scope.articleData.status = status;
                    __Form.process({
                        'apiURL': 'manage.article.write.update',
                        'articleIdOrUid'   : articleIdOrUid,
                        'projectUid' : scope.projectUid,
                        'requestType' : scope.requestType
                    }, scope).success(function(responseData) {

                        appServices.processResponse(responseData, null, function() {
                            if (status == 3) {
                                $state.go('project_articles', {'projectUid': scope.projectUid, versionUid : scope.version_info._uid });
                            }
                        });    
                    });

                };

                /**
                * Close dialog
                *
                * @return  void
                *---------------------------------------------------------------- */

                scope.closeDialog = function() {
                    $scope.closeThisDialog();
                };

            }

        ])
        // Article Edit Controller ends here

        /**
          * Article Content Details Controller 
          *
          * inject object $scope
          * inject object __DataStore
          * inject object __Form
          * inject object $stateParams
          *
          * @return  void
          *---------------------------------------------------------------- */

        .controller('ArticleContentDetailsController', [
            '$scope',
            'ArticleContentData',
            function ($scope, ArticleContentData) {

                var scope = this;
                scope.article_content = ArticleContentData.article_content;
            

                /**
                  * Close dialog
                  *
                  * @return  void
                  *---------------------------------------------------------------- */

                scope.closeDialog = function() {
                    $scope.closeThisDialog();
                };
            }
        ])         
    ;

})(window, window.angular);;
/*!
*  Component  : Language
*  File       : LanguageDataServices.js  
*  Engine     : LanguageServices 
----------------------------------------------------------------------------- */

(function(window, angular, undefined) {

    'use strict';

    angular
        .module('app.LanguageDataServices', [])
        .service('LanguageDataService',[
            '$q', 
            '__DataStore',
            'appServices',
            LanguageDataService
        ])

        /*!
         This service use for to get the promise on data
        ----------------------------------------------------------------------------- */

        function LanguageDataService($q, __DataStore, appServices) {

            
            /*
            Get Add Support Data
            -------------------------------------------------------------- */
            this.getAddSupportData = function() {
        
                //create a differed object
                var defferedObject = $q.defer();
        
                __DataStore.fetch('manage.language.read.support_data')
                    .success(function(responseData) {
        
                    appServices.processResponse(responseData, null, function(reactionCode) {
        
                        //this method calls when the require        
                        //work has completed successfully        
                        //and results are returned to client        
                        defferedObject.resolve(responseData.data);
                    }); 
                });
        
                //return promise to caller          
                return defferedObject.promise; 
            };
            /*
            Get Edit Support Data
            -------------------------------------------------------------- */
            this.getEditSupportData = function(languageIdOrUid) {
        
                //create a differed object
                var defferedObject = $q.defer();
        
                __DataStore.fetch({
                    'apiURL': 'manage.language.read.update.data',
                    'languageIdOrUid'   : languageIdOrUid
                }).success(function(responseData) {
        
                    appServices.processResponse(responseData, null, function(reactionCode) {
        
                        //this method calls when the require        
                        //work has completed successfully        
                        //and results are returned to client        
                        defferedObject.resolve(responseData.data);
                    }); 
                });
        
                //return promise to caller          
                return defferedObject.promise; 
            };


        };

})(window, window.angular);;
/*!
*  Component  : Language
*  File       : Language.js
*  Engine     : Language
----------------------------------------------------------------------------- */

(function(window, angular, undefined) {

    'use strict';

    angular
        .module('app.LanguageEngine', [])

        /**
          * Language Controller
          *
          * inject object $scope
          * inject object __DataStore
          * inject object __Form
          * inject object $stateParams
          *
          * @return  void
          *---------------------------------------------------------------- */

        .controller('LanguageController', [
            '$scope',
            '__DataStore',
            '__Form',
            '$stateParams',
            function ($scope, __DataStore, __Form, $stateParams) {

                var scope = this;

            }
        ])

        /**
        * Language List Controller
        *
        * inject object $scope
        * inject object __DataStore
        * inject object __Form
        * inject object $state
        * inject object appServices
        * inject object $rootScope
        * inject object LanguageDataService
        *
        * @return  void
        *---------------------------------------------------------------- */

        .controller('LanguageListController', [
            '$scope',
            '__DataStore',
            '__Form',
            '$state',
            'appServices',
            '$rootScope',
            'LanguageDataService',
        function ( $scope, __DataStore, __Form, $state, appServices, $rootScope, LanguageDataService) {
            var dtColumnsData = [
                    {
                        "name"      : "name",
                        "orderable" : true,
                    },
                    {
                        "name"      : "_id",
                        "orderable" : true,
                    },
                    {
                        "name"      : "is_rtl",
                        "orderable" : true,
                    },
                    {
                        "name"      : null,
                        "template"  : "#languageActionColumnTemplate"
                    }
                ],
                scope   = this;

                /**
                * Get general user test as a datatable source object
                *
                * @return  void
                *---------------------------------------------------------- */

                scope.languageDataTable = __DataStore.dataTable('#lwLanguageList', {
                    url         : 'manage.language.read.list',
                    dtOptions   : {
                        "searching": true
                    },
                    columnsData : dtColumnsData,
                    scope       : $scope
                });

                /*
                Reload current datatable
                ------------------------------------------------------------ */
                scope.reloadDT = function() {
                    __DataStore.reloadDT(scope.languageDataTable);
                };

                // when add new record
                $scope.$on('language_added_or_updated', function (data) {

                    if (data) {
                        scope.reloadDT();
                    }

                });

               /**
                * Language delete
                *
                * inject LanguageIdUid
                *
                * @return    void
                *---------------------------------------------------------------- */

                scope.delete = function(languageIdOrUid, languagename) {

                	scope.languagename = languagename;

                	_.defer(function(){

                        var lwLanguageDelete = $('#lwLanguageDelete');

                        __globals.showConfirmation({
                            html                : lwLanguageDelete .attr('data-message'),
                            confirmButtonText   : lwLanguageDelete .attr('data-delete-button-text')
                        },
                        function() {

                        	__DataStore.post({
	                            'apiURL' : 'manage.language.write.delete',
	                            'languageIdOrUid' : languageIdOrUid
	                        }).success(function(responseData) {

								var message = responseData.data.message;

                                appServices.processResponse(responseData, {

                                        error : function() {

                                            __globals.showConfirmation({
                                                title   : 'Deleted!',
                                                text    : message,
                                                type    : 'error'
                                            });

                                        }
                                    },
                                    function() {

                                        __globals.showConfirmation({
                                            title   : 'Deleted!',
                                            text    : lwLanguageDelete .attr('success-msg'),
                                            type    : 'success'
                                        });
                                        scope.reloadDT();   // reload datatable

                                    }
                                );
	                        });
                        })
                   });
                };

            /*
            add dialog
            ------------------------------------------------------------ */
            scope.openAddDialog = function() {

                appServices.showDialog(scope, {
                    templateUrl     : __globals.getTemplateURL("language.add-dialog"),
                    controller : 'LanguageAddController as languageAddCtrl',
                }, function(promiseObj) {

                    if (_.has(promiseObj.value, 'language_added_or_updated') && promiseObj.value.language_added_or_updated) {

                        scope.reloadDT();
                    }

                });

            };

            /*
            edit dialog
            ------------------------------------------------------------ */
            scope.openEditDialog = function(languageIdOrUid) {

                appServices.showDialog({
                    'languageIdOrUid' : languageIdOrUid
                }, {
                    templateUrl     : __globals.getTemplateURL("language.edit-dialog"),
                    controller : 'LanguageEditController as languageEditCtrl',
                    resolve : {
                        languageEditData : function() {
                            return LanguageDataService
                                .getEditSupportData(languageIdOrUid);
                        }
                    }
                }, function(promiseObj) {

                    if (_.has(promiseObj.value, 'language_added_or_updated') && promiseObj.value.language_added_or_updated) {

                        scope.reloadDT();
                    }


                });
            };


        }
        ])
        // Language List Controller ends here

        /**
        * Language Controller
        *
        * inject object $scope
        * inject object __DataStore
        * inject object __Form
        * inject object $state
        * inject object appServices
        * inject object $rootScope
        *
        * @return  void
        *---------------------------------------------------------------- */

        .controller('LanguageAddController', [
            '$scope',
            '__DataStore',
            '__Form',
            '$state',
            'appServices',
            '$rootScope',
        function ( $scope,  __DataStore,  __Form,  $state,  appServices,  $rootScope ) {

                var scope = this;

                scope.showLoader = true;
                scope  = __Form.setup(scope, 'language_form', 'languageData', {
                    secured : true,
                    unsecuredFields : ['name', 'code']
                });
                scope.languageData.status = 1;
                scope.languageData.is_rtl = 2;

                /**
                  * Submit form
                  *
                  * @return  void
                  *---------------------------------------------------------------- */

                scope.submit = function() {

                    __Form.process('manage.language.write.create', scope)
                    .success(function(responseData) {

                        appServices.processResponse(responseData, null, function() {
                            $scope.closeThisDialog({
                                'language_added_or_updated': true,
                                'add_language_data': responseData.data.addLanguageData
                            });
                        });

                    });
                };

                /**
                  * Close dialog
                  *
                  * @return  void
                  *---------------------------------------------------------------- */

                scope.closeDialog = function() {
                    $scope.closeThisDialog();
                };
            }
        ])
        // LanguageAddController ends here

        /**
        * Language Edit Controller
        *
        * inject object $scope
        * inject object __DataStore
        * inject object __Form
        * inject object $state
        * inject object appServices
        * inject object $rootScope
        * inject object languageEditData
        *
        * @return  void
        *---------------------------------------------------------------- */

        .controller('LanguageEditController', [
            '$scope',
            '__DataStore',
            '__Form',
            '$state',
            'appServices',
            '$rootScope',
            'languageEditData',
        function ( $scope,  __DataStore,  __Form,  $state,  appServices,  $rootScope , languageEditData) {

            var scope = this;
            scope.showLoader = true;

            scope  = __Form.setup(scope, 'language_form', 'languageData', {
                    secured : true,
                    unsecuredFields : ['name']
            });

            var requestData = languageEditData;
            scope = __Form.updateModel(scope, requestData.editData);
            scope.showLoader = false;

            var languageIdOrUid  = $scope.ngDialogData.languageIdOrUid;

            /**
            * Submit form
            *
            * @return  void
            *---------------------------------------------------------------- */

            scope.submit = function() {

                __Form.process({
                    'apiURL': 'manage.language.write.update',
                    'languageIdOrUid'   : languageIdOrUid
                }, scope).success(function(responseData) {

                        appServices.processResponse(responseData, null, function() {
                            $scope.closeThisDialog( {'language_added_or_updated' : true} );
                        });
                });

            };

            /**
            * Close dialog
            *
            * @return  void
            *---------------------------------------------------------------- */

            scope.closeDialog = function() {
                $scope.closeThisDialog();
            };
        }
    ])
    // Language Edit Controller ends here

    ;

})(window, window.angular);;
/*!
*  Component  : Version
*  File       : VersionDataServices.js  
*  Engine     : VersionServices 
----------------------------------------------------------------------------- */

(function(window, angular, undefined) {

    'use strict';

    angular
        .module('app.VersionDataServices', [])
        .service('VersionDataService',[
            '$q', 
            '__DataStore',
            'appServices',
            VersionDataService
        ])

        /*!
         This service use for to get the promise on data
        ----------------------------------------------------------------------------- */

        function VersionDataService($q, __DataStore, appServices) {

            /*
            Get Project Info Support Data
            -------------------------------------------------------------- */
            this.getProjectInfo = function(projectIdOrUid) {
        
                //create a differed object
                var defferedObject = $q.defer();
        
                __DataStore.fetch({
                    'apiURL'          : 'manage.project.version.read.project_info',
                    'projectIdOrUid'  : projectIdOrUid
                }).success(function(responseData) {
        
                    appServices.processResponse(responseData, null, function(reactionCode) {
                        //this method calls when the require        
                        //work has completed successfully        
                        //and results are returned to client        
                        defferedObject.resolve(responseData.data);
                    }); 
                    
                });
        
                //return promise to caller          
                return defferedObject.promise; 
            };

            /*
            Get Versions
            -------------------------------------------------------------- */
            this.getVersions = function(projectIdOrUid) {
        
                //create a differed object
                var defferedObject = $q.defer();
        
                __DataStore.fetch({
                    'apiURL'          : 'manage.project.version.read.list',
                    'projectIdOrUid'  : projectIdOrUid
                }).success(function(responseData) {
        
                    appServices.processResponse(responseData, null, function(reactionCode) {
                        //this method calls when the require        
                        //work has completed successfully        
                        //and results are returned to client        
                        defferedObject.resolve(responseData.data);
                    }); 
                    
                });
        
                //return promise to caller          
                return defferedObject.promise; 
            };

            
            /*
            Get Add Support Data
            -------------------------------------------------------------- */
            this.getAddSupportData = function(projectIdOrUid) {

                //create a differed object
                var defferedObject = $q.defer();

                __DataStore.fetch({
                    'apiURL': 'manage.project.version.read.support_data',
                    'projectIdOrUid'   : projectIdOrUid
                }).success(function(responseData) {

                    appServices.processResponse(responseData, null, function(reactionCode) {

                        //this method calls when the require        
                        //work has completed successfully        
                        //and results are returned to client        
                        defferedObject.resolve(responseData.data);
                    }); 
                });

                //return promise to caller          
                return defferedObject.promise; 
            };

            /*
            Get Edit Support Data
            -------------------------------------------------------------- */
            this.getEditSupportData = function(projectIdOrUid, versionUid) {

                //create a differed object
                var defferedObject = $q.defer();

                __DataStore.fetch({
                    'apiURL': 'manage.project.version.read.update.data',
                    'projectIdOrUid'   : projectIdOrUid,
                    'versionUid'       : versionUid
                }).success(function(responseData) {

                    appServices.processResponse(responseData, null, function(reactionCode) {

                        //this method calls when the require        
                        //work has completed successfully        
                        //and results are returned to client        
                        defferedObject.resolve(responseData.data);
                    }); 
                });

                //return promise to caller          
                return defferedObject.promise; 
            };

            /*
            Get version support Data
            -------------------------------------------------------------- */
            this.getVersionSupportData = function(projectIdOrUid, versionUid) {

                //create a differed object
                var defferedObject = $q.defer();

                __DataStore.fetch({
                    'apiURL': 'manage.project.version.read.get_support_data',
                    'projectIdOrUid'   : projectIdOrUid,
                    'versionUid'       : versionUid
                }).success(function(responseData) {

                    appServices.processResponse(responseData, null, function(reactionCode) {

                        //this method calls when the require        
                        //work has completed successfully        
                        //and results are returned to client        
                        defferedObject.resolve(responseData.data);
                    }); 
                });

                //return promise to caller          
                return defferedObject.promise; 
            };
        };

})(window, window.angular);
;
/*!
*  Component  : Version
*  File       : Version.js  
*  Engine     : Version 
----------------------------------------------------------------------------- */

(function(window, angular, undefined) {

    'use strict';

    angular
        .module('app.VersionEngine', [])

         
        /**
        * Version List Controller
        *
        * inject object $scope
        * inject object __DataStore
        * inject object __Form
        * inject object $state
        * inject object appServices
        * inject object $rootScope
        * inject object VersionDataService
        *
        * @return  void
        *---------------------------------------------------------------- */

        .controller('VersionListController', [
            '$scope',                
            '__DataStore',                
            '__Form',                
            '$state',                
            'appServices',                
            '$rootScope',                
            'ProjectInfo',
            'GetVersions',
        function ( $scope, __DataStore, __Form, $state, appServices, $rootScope, ProjectInfo, GetVersions){

            var scope = this;
            scope.projectInfo = ProjectInfo.info;
            scope.versions    = GetVersions.versionList;
            scope.projectUid  = scope.projectInfo._uid;
            scope.projectIdOrUid  = $state.params.projectIdOrUid;

            scope.getVersions = function() {

                __DataStore.fetch({
                    'apiURL'         : 'manage.project.version.read.list',
                    'projectIdOrUid' : $state.params.projectIdOrUid
                }).success(function(responseData) {
                
                    appServices.processResponse(responseData, null, function() {
                        scope.versions = responseData.data.versionList;
                    }); 

                });
            };

            /**
            * version delete 
            *
            * inject versionIdUid
            *
            * @return    void
            *---------------------------------------------------------------- */

            scope.delete = function(versionUid, version) {

                scope.deletingTagName = _.unescape(version);

                _.defer(function(){

                    var $lwVersionDeleteConfirm = $('#lwVersionDeleteConfirm');
                    scope.deleteText = $lwVersionDeleteConfirm .attr('data-message');
                    scope.deleteConfirmBtnText = $lwVersionDeleteConfirm .attr('data-delete-button-text');
                    scope.successMsgText = $lwVersionDeleteConfirm .attr('success-msg');

                    __globals.showConfirmation({
                        html                : scope.deleteText,
                        confirmButtonText   : scope.deleteConfirmBtnText
                    },
                    function() {

                        __DataStore.post({
                            'apiURL' : 'manage.project.version.write.delete',
                            'projectIdOrUid' : scope.projectUid,
                            'versionUid' : versionUid
                        }).success(function(responseData) {
                        
                            var message = responseData.data.message;

                            appServices.processResponse(responseData, {

                                error : function() {

                                    __globals.showConfirmation({
                                        title   : 'Deleted!',
                                        text    : message,
                                        type    : 'error'
                                    });
                                }
                            },
                            function() {

                                __globals.showConfirmation({
                                    title   : 'Deleted!',
                                    text    : scope.successMsgText,
                                    type    : 'success'
                                });
                                
                                scope.getVersions();   // reload datatable

                            });

                        });
                    })

                });
    
            }; 
            
            /*
            add dialog
            ------------------------------------------------------------ */
            scope.openAddDialog = function(projectIdOrUid) {

                appServices.showDialog({
                    'projectUid' : projectIdOrUid
                }, {
                    templateUrl : __globals.getTemplateURL("version.add-dialog"),
                    controller  : 'VersionAddController as versionAddCtrl',
                    resolve : {
                        versionAddData : ['VersionDataService', function(VersionDataService) {
                            return VersionDataService.getAddSupportData(projectIdOrUid);
                        }]
                    }
                }, function(promiseObj) {

                    if (_.has(promiseObj.value, 'version_added') 
                    && promiseObj.value.version_added) {

                        scope.getVersions();
                    }

                });

            };

            $scope.$on('load_versions_list', function(event, data) {
            	scope.getVersions();
        	});

            /*
            edit dialog
            ------------------------------------------------------------ */
            scope.openEditDialog = function(versionUid) {
 
                appServices.showDialog({
                    'projectIdOrUid' : scope.projectUid,
                    'projectId'    : scope.projectInfo._id
                }, {
                    templateUrl : __globals.getTemplateURL("version.edit-dialog"),
                    controller : 'VersionEditController as versionEditCtrl',
                    resolve : {
                        versionEditData : ['VersionDataService', function(VersionDataService) {
                            return VersionDataService.getEditSupportData(scope.projectUid, versionUid);
                        }]
                    }
                }, function(promiseObj) {

                    if (_.has(promiseObj.value, 'version_added') && promiseObj.value.version_added) {
                        scope.getVersions();
                    }


                });
            };

            scope.showEmbedScriptDialog = function(projectSlug, versionSlug, title) {

				appServices.showDialog({
					'projectSlug' : projectSlug,
					'versionSlug' : versionSlug,
                    'embededType': 2
				}, {
                    templateUrl : "embed.embed-script-dialog",
                    controller  : 'EmbedScriptDialogController as EmbedScriptDialogCtrl',
                }, function(promiseObj) {
                });

			}

        }
        ])
        // Version List Controller ends here

        /**
        * Version Controller
        *
        * inject object $scope
        * inject object __DataStore
        * inject object __Form
        * inject object $state
        * inject object appServices
        * inject object $rootScope
        *
        * @return  void
        *---------------------------------------------------------------- */

        .controller('VersionAddController', [
            '$scope',
            '__DataStore',
            '__Form',
            '$state',
            'appServices',
            '$rootScope',
            'versionAddData',
        function ( $scope,  __DataStore,  __Form,  $state,  appServices,  $rootScope, versionAddData) {

                var scope = this;
                scope.projectUid = $scope.ngDialogData.projectUid;
                scope.existingVersions = versionAddData.existing_versions;
                scope.existing_versions_count = versionAddData.existing_versions_count;
                scope.showLoader = true;
                scope  = __Form.setup(scope, 'version_form', 'versionData', {
                    secured : false,
                    unsecuredFields : ['version']
                });
                
                scope.versionData.status = 1;
                scope.versionData.projects__id = versionAddData.projectId;
                
                if (parseInt(scope.existing_versions_count) == 0) {
                	scope.versionData.is_primary = 1;
                }

                /**
                  * Submit form
                  *
                  * @return  void
                  *---------------------------------------------------------------- */

                scope.submit = function() {
                    __Form.process({
                        'apiURL' : 'manage.project.version.write.create',
                        'projectIdOrUid' : scope.projectUid
                    }, scope)
                    .success(function(responseData) {

                        appServices.processResponse(responseData, null, function() {
                            $scope.closeThisDialog( {'version_added' : true} );
                        });    

                    });
                };

				scope.changeStatus = function(isprimary) {
					if (isprimary == '1') {
						scope.versionData.status = 1;
					}
				}

                /**
                  * Close dialog
                  *
                  * @return  void
                  *---------------------------------------------------------------- */

                scope.closeDialog = function() {
                    $scope.closeThisDialog();
                };
            }
        ])
        // VersionAddController ends here

        /**
        * Version Edit Controller
        *
        * inject object $scope
        * inject object __DataStore
        * inject object __Form
        * inject object $state
        * inject object appServices
        * inject object $rootScope
        *
        * @return  void
        *---------------------------------------------------------------- */

        .controller('VersionEditController', [
            '$scope',                
            '__DataStore',                
            '__Form',                
            '$state',                
            'appServices',                
            '$rootScope',
            'versionEditData',           
        function ( $scope,  __DataStore,  __Form,  $state,  appServices,  $rootScope, versionEditData) {

            var scope = this;
                scope.showLoader = true;
                scope.projectId = $scope.ngDialogData.projectId;

                scope  = __Form.setup(scope, 'version_form', 'versionData', {
                    secured : false,
                    unsecuredFields : []
                });

                var versionData = versionEditData.versionData;

                scope = __Form.updateModel(scope, versionData);

                scope.showLoader = false;

                /**
                * Submit form
                *
                * @return  void
                *---------------------------------------------------------------- */

                scope.submit = function() {

                    __Form.process({
                        'apiURL'           : 'manage.project.version.write.update',
                        'projectIdOrUid'   : scope.projectId,
                        'versionUid'       : versionData._uid
                    }, scope).success(function(responseData) {

                        appServices.processResponse(responseData, null, function() {
                            $scope.closeThisDialog( {'version_added' : true} );
                        });    
                    });

                };

				scope.changeStatus = function(isprimary) {
					if (isprimary == '1') {
						scope.versionData.status = 1;
					}
				}
				
                /**
                * Close dialog
                *
                * @return  void
                *---------------------------------------------------------------- */

                scope.closeDialog = function() {
                    $scope.closeThisDialog();
                };

            }

        ])
        // Version Edit Controller ends here


         
    ;

})(window, window.angular);
//# sourceMappingURL=../source-maps/manage-app.src.js.map
