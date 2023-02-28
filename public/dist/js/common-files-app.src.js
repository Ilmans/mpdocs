/*!
*  Component  : Common User
*  File       : CommonUserEngine.js  
*  Engine     : CommonUserEngine 
----------------------------------------------------------------------------- */
(function(window, angular, undefined) {

    'use strict';

    angular
        .module('CommonApp.users', [])

        /**
          * UserProfileController - edit user profile
          *
          * @inject __Form
          * @inject appServices
          * 
          * @return void
          *-------------------------------------------------------- */

        .controller('UserProfileController',   [
            '__Form', 
            'appServices',
            'CommonUserDataService',
            function (__Form, appServices, CommonUserDataService) {

                var scope   = this;

                scope = __Form.setup(scope, 'user_profile__form', 'profileData');

                scope.request_completed = false;

                CommonUserDataService
                    .getUserProfileDetails()
                    .then(function(responseData) {
                        __Form.updateModel(scope, responseData.data.profile);
                        scope.existingProfilePictureURL = responseData.data.existingProfilePictureURL;
                        scope.request_completed = true;
                    });

            }

        ])

        /**
          * UserProfileEditController - edit user profile
          *
          * @inject __Form
          * @inject appServices
          * 
          * @return void
          *-------------------------------------------------------- */

        .controller('UserProfileEditController',   [
            '$scope',
            '__Form', 
            'appServices',
            'lwFileUploader',
            '__Utils',
            '$state',
            'CommonUserDataService',
			'$rootScope',
            function ($scope, __Form, appServices, lwFileUploader, __Utils, $state, CommonUserDataService, $rootScope) {

                var scope   = this;

                scope = __Form.setup(scope, 'user_profile_edit_form', 'profileData');

                scope.request_completed = false;
                scope.countries         = [];

                CommonUserDataService
                    .getUserProfileEditData()
                    .then(function(responseData) {
                        var requestData = responseData.data;

                        __Form.updateModel(scope, requestData.profile);

                        scope.existingProfilePictureURL = requestData.existingProfilePictureURL;
                        scope.countries                 = requestData.countries;

                        scope.request_completed = true;
                    });

                scope.countrySelectConfig    = __globals.getSelectizeOptions();

                scope.imagesSelectConfig     = __globals.getSelectizeOptions({
                    valueField  : 'name',
                    labelField  : 'name',
					
                    render      : {
                        item: function(item, escape) {
                            return  __Utils.template('#imageListItemTemplate', item);
                        },
                        option: function(item, escape) {
                           return  __Utils.template('#imageListOptionTemplate', item);
                        }
                    }, 
                    searchField : ['name']  
                });

                scope.typeSelectConfig     = __globals.getSelectizeOptions({
                    valueField  : 'id',
                    labelField  : 'title',
                    searchField : ['title']  
                });
                
                /**
                  * Retrieve files required for account logo
                  *
                  * @return void
                  *---------------------------------------------------------------- */

                scope.retrieveSpecificFiles  =  function() {

                    lwFileUploader.getTempUploadedFiles(scope, {
                        'url' : __Utils.apiURL('media.upload.read_user_profile')
                    }, function(uploadedFile) {
                        scope.profileFiles      = uploadedFile;
						
                        scope.profileFilesCount = uploadedFile.length;
                    });
                };
                scope.retrieveSpecificFiles();
				

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
						
                            'url' : __Utils.apiURL('media.upload.write.user_profile')
							
                        }, function(response) {
                        scope.retrieveSpecificFiles();

                    });
                };

                /**
                  * Show uploaded media files
                  *
                  * @return void
                  *---------------------------------------------------------------- */

                $scope.showUploadedMediaDialog = function() {
					
                    lwFileUploader.openDialog(scope, {
                        'url' : __Utils.apiURL('media.upload.read_user_profile')
                    }, 
                    function(promiseObject) {
                        scope.retrieveSpecificFiles();
                    });

                };


                /**
                  * Submit profile edit form action
                  *
                  * @return void
                  *---------------------------------------------------------------- */

                scope.submit = function() {

                    __Form.process('user.profile.update', scope)
                    .success(function(responseData) {
                        var requestData = responseData.data
                        appServices.processResponse(responseData, null, function() {
                            $rootScope.$broadcast('auth_info_updated', requestData);
                            
                            // if (__globals.isPublicApp()) {
                            //     window.location = __Utils.apiURL('user.profile');
                            // } else {
                            //     $state.go('profile');
                            // }
                            $state.go('profile');
                        });    

                    });

                };

            }
             
        ])

        /**
          * UserChangePasswordController - change user password
          *
          * @inject __Form
          * @inject appServices
          * 
          * @return void
          *-------------------------------------------------------- */

        .controller('UserChangePasswordController',   [
            '__Form', 
            'appServices',
            '__Utils',
            '$state',
            function (__Form, appServices, __Utils, $state) {

                var scope   = this;

                scope = __Form.setup(scope, 'user_password_update_form', 'userData', {
                    secured : true
                });

                /**
                  * Submit update password form action
                  *
                  * @return void
                  *---------------------------------------------------------------- */

                scope.submit = function() {

                    __Form.process('user.change_password.process', scope)
                    .success(function(responseData) {

                        appServices.processResponse(responseData, null, function() {
                            scope.userData = {};
                            
                            if (document.location.href == responseData.data.passwordRoute) {
                                window.location = window.appConfig.appBaseURL;
                            } else {
                                $state.go('dashboard');
                            }
                            
                        });    

                    });

                };

            } 
        ])

        /**
          * UserChangeEmailController - handle chnage email form view js scope
          *
          * @inject __Form
          * @inject appServices
          * 
          * @return void
          *-------------------------------------------------------- */

        .controller('UserChangeEmailController',   [
            '__Form', 
            'appServices',
            'CommonUserDataService',
            function (__Form, appServices, CommonUserDataService) {

                var scope               = this;

                scope.requestSuccess    = false;

                scope = __Form.setup(scope, 'user_change_email_form', 'userData', {
                    secured : true
                });

                /**
                  * Fetch support data
                  *
                  * @return void
                  *---------------------------------------------------------------- */
                  CommonUserDataService
                    .getChangeEmailSupportData()
                    .then(function(responseData) {
                        var requestData   = responseData.data;
                        scope.changeEmail = requestData.newEmail;
                        scope.current_email = requestData.current_email;
                    });

                /**
                  * Submit change email form action
                  *
                  * @return void
                  *---------------------------------------------------------------- */

                scope.submit = function() {

                    __Form.process('user.change_email.process', scope)
                    .success(function(responseData) {

                        var requestData = responseData.data;

                        appServices.processResponse(responseData, null,
                        function() {

                            scope.activationRequired = requestData.activationRequired;

                            scope.requestSuccess = true;

                            $('.lw-form').slideUp();
                            
                        });    

                    });

                };

            }
            
        ])

		/**
          * UserLoginDialogController - login a user in application
          *
          * @inject __Form
          * @inject __Auth
          * @inject appServices
          * @inject __Utils
          * 
          * @return void
          *-------------------------------------------------------- */

        .controller('UserLoginDialogController',   [
            '__Form', 
            '__Auth', 
            'appServices',
            '__Utils',
            '$scope',
            'CommonUserDataService',
            function (__Form, __Auth, appServices, __Utils, $scope, CommonUserDataService) {

                var scope   = this,
                ngDialogData = $scope.ngDialogData;
                scope = __Form.setup(scope, 'user_login_form', 'loginData', {
                    secured : true
                });


                if (_.has(ngDialogData, 'from')) {
                    scope.loginData.from = ngDialogData.from;
                }
              
                scope.show_captcha      = false;
                scope.request_completed = false;

                /**
                  * Get login attempts for this client ip
                  *
                  * @return void
                  *---------------------------------------------------------------- */

                CommonUserDataService.getLoginAttempts()
                    .then(function(responseData) {
                        scope.show_captcha      = responseData.data.show_captcha;
                        scope.request_completed = true;
                });

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

                    scope.isInActive         = false;
                    scope.accountDeleted     = false;

                    __Form.process('user.login', scope).success(function(responseData) {

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

                            }
                        },
                        function() {

                            __Auth.checkIn(requestData.auth_info, function() {

                                if(requestData.availableRoutes) {
                                    __globals.appImmutable('availableRoutes', 
                                        requestData.availableRoutes);
                                }

                            });

							$scope.closeThisDialog({'login_success' : true});

                        });    

                    });

                };

                /**
                  * Close dialog
                  *
                  * @return void
                  *---------------------------------------------------------------- */

                scope.closeDialog = function() {
                    $scope.closeThisDialog({'login_success' : false});
                };

            }

        ])
        

})(window, window.angular);;
/*!
*  Component  : Manage Users
*  File       : CommonUserDataService.js  
*  Engine     : CommonUserDataService 
----------------------------------------------------------------------------- */

(function(window, angular, undefined) {

    'use strict';

    angular
        .module('CommonApp.CommonUserDataService', [])

        /**
          Manage User Data Service  
        ---------------------------------------------------------------- */
        .service('CommonUserDataService',[
            '$q', 
            '__DataStore', 
            '__Form',
            'appServices',
            '__Utils',
            CommonUserDataService
        ])

        function CommonUserDataService($q, __DataStore,__Form, appServices, __Utils) {

            /*
            Get User Details
            -----------------------------------------------------------------*/
            this.getUserProfileDetails = function() {

                //create a differed object          
                var defferedObject = $q.defer();   
   
                __DataStore.fetch('user.profile.details')
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
            Get User Profile Edit Data
            -----------------------------------------------------------------*/
            this.getUserProfileEditData = function() {

                //create a differed object          
                var defferedObject = $q.defer();   
   
                __Form.fetch('user.profile.edit_support_data')
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
            Get Temp images Data
            -----------------------------------------------------------------*/
            this.getTempImagesData = function() {

                //create a differed object          
                var defferedObject = $q.defer();   
   
                __Form.fetch(__Utils.apiURL('media.uploaded.images')+'?fileRequestType=1', { fresh : true })
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
            Get Change email support data
            -----------------------------------------------------------------*/
            this.getChangeEmailSupportData = function() {

                //create a differed object          
                var defferedObject = $q.defer();   
   
                __Form.fetch('user.change_email.support_data')
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
        }
    ;

})(window, window.angular);;
/*!
*  Component  : Uploader
*  File       : UploaderDataService.js  
*  Engine     : UploaderDataService 
----------------------------------------------------------------------------- */

(function(window, angular, undefined) {

    'use strict';

    angular
        .module('app.UploaderDataService', [])
        .service('UploaderDataService',[
            '$q', 
            '__Utils',
            '__DataStore', 
            'appServices',
            UploaderDataService
        ])

        /*!
         This service use for to get the promise on data
        ----------------------------------------------------------------------------- */

        function UploaderDataService($q, __Utils, __DataStore, appServices) {

            /*
            Get the data of configuration
            -----------------------------------------------------------------*/

            this.readUploadedMedia = function(fileRequestType) {

                //create a differed object          
                var defferedObject = $q.defer();   
   
                __DataStore.fetch(__Utils.apiURL('media.uploaded.images')+'?fileRequestType='+fileRequestType, { fresh : true })
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
            Get the data of configuration
            -----------------------------------------------------------------*/

            this.readUploadedImages = function(fileRequestType) {

                //create a differed object          
                var defferedObject = $q.defer();   
   
                __DataStore.fetch('media.uploaded.images.detail', { fresh : true })
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

        };

    
})(window, window.angular);
;
/*!
*  Component  : Uploader
*  File       : UploaderEngine.js  
*  Engine     : Uploader 
----------------------------------------------------------------------------- */
(function(window, angular, undefined) {

    'use strict';

    /*
     UploadedMediaController
    -------------------------------------------------------------------------- */
    
    angular.module('app.UploaderEngine', [])

    /**
          * Uploaded File Dialog Controller 
          *
          * @inject object $scope
          * @inject object __DataStore
          * @inject object appServices
          * @inject object appNotify
          *
          * @return void
          *---------------------------------------------------------------- */

        .controller('uploadedFileDialogController', [
            '$scope',
            '__DataStore',
            'appServices', 
            'appNotify',
            '__Utils',
            'lwFileUploader',
            '$rootScope',
            function ($scope, __DataStore, appServices, appNotify, __Utils, lwFileUploader, $rootScope) {

                var scope = this;
				
                scope.showLoader   = true;
                scope.ngDialogData = $scope.ngDialogData;
				
              /**
                * Get temp media list
                *
                * @return void
                *-----------------------------------------------------------------------*/
            
                scope.getTempUploadedMedia = function()
                {
                    lwFileUploader.mediaDataService(scope.ngDialogData.url)
                        .then(function(responseData) {
                            
                        scope.uploadedFiles = responseData.data.files; 
                        scope.showLoader    = false; 

                    });

                };
                  

                /**
                  * Delete media file 
                  *
                  * @param string fileName
                  *
                  * @return void
                  *---------------------------------------------------------------- */

                scope.delete = function(fileName) {

                    __DataStore.post({
                        'apiURL'   : 'media.upload.delete',
                        'fileName' : fileName
                    }, scope)
                    .success(function(responseData) {

                        appServices.processResponse(responseData, null, function() {
                            
                            // use for slowly remove address from list
                            $('#lw_'+fileName).fadeOut('slow',function() {
                                $(this).remove();
                            });

                            $rootScope.$emit('lw.media.deleted', {
                                'fileName' : fileName
                            });

                            scope.getTempUploadedMedia();

                        });    

                    });

                };

                /**
                  * Delete multiple uploaded temparary media Files
                  *
                  * @return void
                  *---------------------------------------------------------------- */
                
                scope.deleteMultipleFiles = function() {

                    __DataStore.post('media.upload.delete_multiple', scope)
                        .success(function(responseData) {
                        
                        appServices.processResponse(responseData, null, function() {
                            scope.getTempUploadedMedia();
                            $rootScope.$emit('lw.multiple.media.deleted', {
                                'uploadedFiles' : scope.uploadedFiles
                            });
                        });  

                    });

                };

                /**
                  * Select Files
                  *
                  * @return void
                  *---------------------------------------------------------------- */
                scope.selectFiles = function() { 

                    __DataStore.post('media.upload.select_files', scope)
                        .success(function(responseData) {
                            
                        appServices.processResponse(responseData, null, function() {
                            $scope.closeThisDialog({'selectedData' : responseData.data.selectedFiles});
                        });  

                    });
                }

                scope.getTempUploadedMedia();


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


})(window, window.angular);
//# sourceMappingURL=../source-maps/common-files-app.src.js.map
