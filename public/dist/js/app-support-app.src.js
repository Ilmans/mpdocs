(function() {
'use strict';

  angular.module('app.service', []).
    service("appServices", [
        '$rootScope', '$state', 'appNotify', 'ngDialog','__DataStore', appServices ])

    .service('BaseDataService', ['$rootScope', '$q', '$state', '__DataStore', 'appServices', '__Auth', function($rootScope, $q, $state, __DataStore, appServices, __Auth) {
        /*
         Get Subscriptions
        -------------------------------------------------------------------------- */
        this.getBaseData = function(userLoggedInState) {

            var def = $q.defer(),
            /* baseDataUrl = window.location.protocol+'//'+window.location.hostname;

            if(window.location.hostname == 'localhost') {
                baseDataUrl += (window.location.pathname).split('/public/')[0];
            }

            baseDataUrl += '/base-data'; */

            baseDataUrl = window.appConfig.appBaseURL+'base-data';
               
            __DataStore.fetch(baseDataUrl)
                .success(function(responseData) {

                appServices.processResponse(responseData, null, function(reactionCode) {
                    
                    if(responseData.data.__appImmutables) {
                        window.__appImmutables = responseData.data.__appImmutables;
                        window.__appTemps = responseData.data.__appTemps;
                        window.appConfig = responseData.data.appConfig;
                        window.auth_info = responseData.data.auth_info;

                        $rootScope.isBaseDataLoaded = true;
                        
                        $rootScope.$broadcast('auth_info_updated', { auth_info: window.auth_info });

                        __Auth.refresh(function(authInfo) {
                            $rootScope.auth_info = authInfo;
                        });

                        if(userLoggedInState == 'account_logged') {
                            if(window.__appImmutables.auth_info.authorized == false) {
                                $state.go('login');
                                // return __globals.redirectBrowser(window.__appImmutables.auth_info.gateway_url);
                            }
                        }
                    }

                    def.resolve(responseData.data);

                });

            });

             // Return the parentData promise
            return def.promise;
        };
    }]);


    /**
      * Various App services.
      *
      *
      * @inject $rootScope
      *
      * @return void
      *-------------------------------------------------------- */

    function appServices( $rootScope, $state, appNotify, ngDialog, __DataStore ) {


    	/**
	      * Delay action for particuler time
	      *
	      * @return object
	      *---------------------------------------------------------------- */

	    this.delayAction = function( callbackFunction, delayInitialLoading) {

	      var delayInitialLoading = (delayInitialLoading
	                                  && _.isNumber(delayInitialLoading) )
	                                  ? delayInitialLoading
	                                  : __globals.delayInitialLoading;


	        setTimeout(function(){

	            callbackFunction.call( this );

	      }, delayInitialLoading);
	    };


        /**
          * Actions on Response (improved version of doReact) - 03 Sep 2015
          *
          * @return void
          *---------------------------------------------------------------- */

        this.processResponse = function( responseData, callback, successCallback ) {

            var message,
              preventRedirect,
              preventRedirectOn,
              options      = responseData.options,
              reactionCode = responseData.reaction;

            if (responseData.data && responseData.data.message) {
                message = responseData.data.message;
            }

            if ( _.isString(options) ) {
                message = options;
            }

            if ( _.isObject(options) && _.has(options, 'message')) {

                message = options.message;

                preventRedirect   =  options.preventRedirect ? options.preventRedirect : null;
                preventRedirectOn =  options.preventRedirectOn ? options.preventRedirectOn : null;

            }

            if ( !options || !options.preventRedirect ) {

                switch ( reactionCode ) {

                    case 8:
                        if( preventRedirectOn !== 8  ) {
                          $state.go('not_found');
                        }
                        break;

                    case 7:
                        if( preventRedirectOn !== 7  ) {
                          $state.go('invalid_request');
                        }
                        break;

                    case 5:
                    if( preventRedirectOn !== 5  ) {
                          $state.go('unauthorized');
                        }
                        break;

                    case 18:
                        if( preventRedirectOn !== 18  ) {
                          $state.go('not_exist');
                        }
                        break;

                }
            }


            if ( message &&  ( reactionCode === 1 ) ) {

              appNotify.success( message );

            } else if( message &&  ( reactionCode === 14 ) ) {

              appNotify.warn( message );

            } else if( message &&  ( reactionCode != 1 ) ) {

              appNotify.error( message );

            }

            var callBackReturn = {};

            if (callback) {

                if (_.isFunction(callback)) {

                    callBackReturn.then =
                            callback.call( this, reactionCode );

                } else if(_.isObject(callback)) {

                    if (_.has(callback, 'then') && _.isFunction(callback.then)) {
                        callBackReturn.then =
                            callback.then.call( this, reactionCode );
                    }

                    if (_.has(callback, 'error') && _.isFunction(callback.error)) {

                        if (reactionCode === 2) {
                            callBackReturn.error =
                                callback.error.call(this, reactionCode);
                        }
                    }

                    if (_.has(callback, 'success') && _.isFunction(callback.success)) {

                        if (reactionCode === 1) {
                            callBackReturn.success =
                                callback.success.call(this, reactionCode);
                        }
                    }

                    if (_.has(callback, 'otherError') && _.isFunction(callback.otherError)) {

                        if (reactionCode !== 1) {
                            callBackReturn.otherError =
                                callback.otherError.call(this, reactionCode);
                        }
                    }

                }

            }

            if (successCallback && _.isFunction(successCallback)) {

                if (reactionCode === 1) {
                    callBackReturn.success = successCallback.call(this, reactionCode);
                }
            }

            return callBackReturn;

        };


        /**
      	  * Close all dialog
      	  *
      	  * @return void
      	  *---------------------------------------------------------------- */

	    this.closeAllDialog = function() {
	        ngDialog.closeAll();
	    };

	    /**
	      * Handle dialog show & close methods
	      *
	      * @param object transmitedData
	      * @param object options
	      * @param object closeCallback
	      *
	      * @return object
	      *---------------------------------------------------------------- */

	    this.showDialog = function( transmitedData, options , closeCallback ) {

            var templateUrl;

            if ((options.templateUrl.search("http") >= 0) || (options.templateUrl.search("https") >= 0)) {
                templateUrl = options.templateUrl;
            } else {
                templateUrl = __globals.getTemplateURL(options.templateUrl);
            }

	        return ngDialog.open({

                template        : templateUrl,
                controller      : options.controller,
                controllerAs    : options.controllerAs,
                closeByEscape   : true,
                closeByDocument : true,
                overlay         : true,
                data            : transmitedData,
                appendClassName : 'lw-dialog',
                resolve         : options.resolve,
                onOpenCallback : function(text) {
                    /* var headerHeight = $(this).find('.modal-header').outerHeight();
                    if(headerHeight > 70) {
                        $(this).find('.modal-body').css({'padding-top':headerHeight+'px'});
                    } */
                }

	        }).closePromise.then(function ( data ) {

	            return closeCallback.call( this, data );

	        });

	    };

        /**
          * Handle Login required dialog show & close methods
          *
          * @param object string
          * @param object callback
          *
          * @return object
          *---------------------------------------------------------------- */

        this.loginRequiredDialog = function(from, options, callback) {

            this.showDialog(
            {
                'from' : from
            },
            {
                templateUrl : __globals.getTemplateURL('user.login-dialog')
            },
            function(promiseObj) {
				// __dd('login dialog Promise', promiseObj);
                if (_.has(promiseObj.value, 'login_success')
                    && promiseObj.value.login_success === true) {

                    callback.call(this, true);

                    $('.guest-user').css({"display": "none"});
                }

				callback.call(this, false);

            });

        };

	    /**
	      * Handle dialog show & close methods
	      *
	      * @param object options
	      * @param object closeCallback
	      *
	      * @return object
	      *---------------------------------------------------------------- */

	    this.confirmDialog = function( options , closeCallback ) {

	        return ngDialog.openConfirm({

	            template  : options.templateUrl,
	            className : 'ngdialog-theme-default',
	            showClose : true

	        }, function( value ) {

	            return closeCallback.call( this, value );

	        });

        };
		

	 	/**
          * Check if user allowed given authority ID permission of not
          *
          * @param string authorityId
          *
          * @return boolean
          *---------------------------------------------------------------- */

        $rootScope.canAccess = function(str) {

			var arr = __globals.appImmutable('availableRoutes');

	        // If there are no items in the array, return an empty array
	        if(typeof arr === 'undefined' || arr.length === 0) return false;
	        // If the string is empty return all items in the array
	        if(typeof arr === 'str' || str.length === 0) return false;

	        // Create a new array to hold the results.
	        var res = [];
	     
	        // Check where the start (*) is in the string
	        var starIndex = str.indexOf('*');
	    		
	        // If the star is the first character...
	        if(starIndex === 0) {
	            
	            // Get the string without the star.
	            str = str.substr(1);
	            for(var i = 0; i < arr.length; i++) {
	                
	                // Check if each item contains an indexOf function, if it doesn't it's not a (standard) string.
	                // It doesn't necessarily mean it IS a string either.
	                if(!arr[i].indexOf) continue;
	                
	                // Check if the string is at the end of each item.
	                if(arr[i].indexOf(str) === arr[i].length - str.length) {                    
	                    // If it is, add the item to the results.
	                    return true;
	                }
	            }
	        }
	        // Otherwise, if the star is the last character
	        else if(starIndex === str.length - 1) {
	            // Get the string without the star.
	            str = str.substr(0, str.length - 1);
	            for(var i = 0; i < arr.length; i++){
	                // Check indexOf function                
	                if(!arr[i].indexOf) continue;
	                // Check if the string is at the beginning of each item
	                if(arr[i].indexOf(str) === 0) {
	                    // If it is, add the item to the results.
	                    return true;
	                }
	            }
	        }
	        // In any other case...
	        else {            
	            for(var i = 0; i < arr.length; i++){
	                // Check indexOf function
	                if(!arr[i].indexOf) continue;
	                // Check if the string is anywhere in each item
	                if(arr[i].indexOf(str) !== -1) {
	                    // If it is, add the item to the results
	                    return true;
	                }
	            }
	        }
	        
	        // Return the results as a new array.
	        return false;

        };
    }

})();;
(function() {
'use strict';

  angular.module('app.http', []).

    // register the interceptor as a service, intercepts -
    // all angular ajax http reuest called
    config([ 
      '$httpProvider', 
      function ($httpProvider) {

        $httpProvider.interceptors.push('errorInterceptor');
        var proccessSubmit = function (data, headersGetter) {

           return data;

        };

        $httpProvider.defaults.transformRequest.push( proccessSubmit );
        $httpProvider.interceptors.push('loadingHttpInterceptor');
    }]).
    factory('errorInterceptor', [ 
      	'$q',
      	'__Auth',
      	'__Utils',
      	'$rootScope',
    		'$state',
        '$window',
      	errorInterceptor
    ]). 
    factory('loadingHttpInterceptor', [
        '$q',
        '$rootScope', function($q, $rootScope) {
      return {
        request: function(config) {


          $('.lw-disabling-block').addClass('lw-disabled-block');
          $('html').addClass('lw-has-disabled-block');
          return config || $q.when(config);
        },
        response: function(response) {

            $('.lw-disabling-block').removeClass('lw-disabled-block lw-has-processing-window');
            $('html').removeClass('lw-has-disabled-block');
            return response || $q.when(response);

        },
        responseError: function(rejection) {
            $('.lw-disabling-block').removeClass('lw-disabled-block lw-has-processing-window');
            $('html').removeClass('lw-has-disabled-block');

          return $q.reject(rejection);
        }
      };
}]);

  
  /**
   * errorInterceptor factory.
   * 
   * Make a response for all http request
   *
   * @inject $q - for return promise
   * @inject __Auth - for set authentication object
   * @inject $location - for redirect on another page
   *
   * @return void
   *-------------------------------------------------------- */
   
  function errorInterceptor($q, __Auth, __Utils, $rootScope, $state, $window) {

		var isNotificationRequest = false;

      	return {

          request: function (config) {

            return config || $q.when(config);

          },
          requestError: function( request ) {
			 
              return request;

          },
          response: function ( response ) {

           var requestData = response.data,
            publicApp   = __globals.isPublicApp();
	
      try {
				if (_.isObject(requestData)) {

					// If is Public App & Server return Not foun Reaction Then Redirect on Not Found Page
		            if (publicApp == true && requestData.reaction == 18) {

		            	$state.go('not_exist');
		                //window.location = __Utils.apiURL('error.public-not-found');
		            }
					
					var additional  = requestData.data.additional,
						newResponse = requestData.data,
						params = [];	
				
						if ($state.params) {

							_.each($state.params, function(val, key) {

								if (key != '#' && !_.isNull(val))
								params[key] = val;
								
							});
						}
  
					if (_.has(newResponse, 'auth_info')) {	
						
						var authObj       = newResponse.auth_info,
            				reactionCode  = authObj.reaction_code;
							
						__Auth.checkIn(authObj, function() {
						
		                    switch (reactionCode) {

			                    case 11:  // access denied
		                            // Check if current app is public the redirect to Home View
		                            $state.go("unauthorized");

			                        break;

			                    case 9:  // if unauthorized
	
		                            // Check if current app is public the redirect to Login View
  									//It Open when tit unauthenticated & also is not notification request
  									__Auth.registerIntended( {
  		                                name    : $state.current.name,
  		                                params  : params,
  		                                except  : [ 'login', 'logout', 'reset_password']
  		                            }, function() {
  										$state.go('login');
  									}); 
									
			                        break;

			                    case 6:  // if invalid request                        
			                        $state.go("invalid_request");

			                        break;

			                    case 10:  
                                        // $state.go("dashboard");
                                        // if($tate.current.name == 'forgot_password') {
                                        //     $state.go("dashboard");
                                        // }
                                        if (__Auth.isLoggedIn()) {
    			                        	__Auth.registerIntended("project");             
                                        } else {
                                        	$state.go("project");
                                        }
    									
			                        break;

		                    }

		                });

					}

				}

			} catch(error) {}
			
            return response || $q.reject(response);
            
          },
          responseError: function ( response ) {
		
            if (response.status == 403 ) {
              return;
            }

            return $q.reject(response);

          }

      };

  };

})();;
(function() {
'use strict';

  angular.module('app.notification', [])
	.service("appNotify", ['ngNotify', appNotify ])
	.service("appToastNotify", appToastNotify);

  
  /**
     * appNotify service.
     *
     * Show notification
     *
     * @inject ngNotify
     *
     * @return object
     *-------------------------------------------------------- */
   
  function appNotify( ngNotify ) {


      /*
       Notification Default Option Object
      -------------------------------------------------------------------------- */
      
      this.optionsObj = {
        position      : 'botttom',
        type          : 'success',
        theme         : 'pure',
        dismissQueue  : true,
        duration      : 3000,
        sticky        : false
      };

      /**
        * Show success notification message
        *
        * @param string - message
        * @param object - options  
        *
        * @return object
        *---------------------------------------------------------------- */

      this.success  =  function( message, options ) {

          if ( _.isEmpty( options ) ) {  // Check for if options empty
              var options = {};
          }

          options.type = 'success';

          this.notify( message, options );

      };

        /**
          * Show error notification message
          *
          * @param string - message
          * @param object - options 
          *
          * @return object
          *---------------------------------------------------------------- */

        this.error  =  function( message, options ) {

            if ( _.isEmpty( options ) ) {  // Check for if options empty
                var options = {};
            }

            options.type = 'error';

            this.notify( message, options );

        };

        /**
          * Show information notification message
          *
          * @param string - message
          * @param object - options  
          *
          * @return object
          *---------------------------------------------------------------- */

        this.info  =  function( message, options ) {

            if ( _.isEmpty( options ) ) {  // Check for if options empty
                var options = {};
            }

            options.type = 'info';

            this.notify( message, options );

        };

        /**
          * Show warning notification message
          *
          * @param string - message
          * @param object - options  
          *
          * @return object
          *---------------------------------------------------------------- */

        this.warn  =  function( message, options ) {

            if ( _.isEmpty( options ) ) {  // Check for if options empty
                  var options = {};
            }

            options.type = 'warn';

            this.notify( message, options );

        };

        /**
          * Show notification
          *
          * @param string msg
          * @param object options
          *
          * @return void
          *---------------------------------------------------------------- */

        this.notify = function( message, options ) {
          
            // show notification
            ngNotify.set( message, _.assign( this.optionsObj, options ) );

        };
      
  };

	/**
     * appNotify service.
     *
     * Show notification
     *
     * @return object
     *-------------------------------------------------------- */
   
  	function appToastNotify() {


		/*
		Notification Default Option Object
		-------------------------------------------------------------------------- */

		this.optionsObj = {
			styling: 'fontawesome',
			width  : 'auto',
			desktop:true,
			hide: false,
			icon : false,
			history: {
        		history: false
    		},
			buttons: {
		        closer  : false,
		        sticker : false
		    },
			animate: {
		        animate   : true,
		        in_class  : 'fadeInRight',
		        out_class : 'fadeOutRight'
		    }
		};

		/**
	    * Show success notification message
	    *
	    * @param string - message
	    * @param object - options  
	    *
	    * @return object
	    *---------------------------------------------------------------- */

	  	this.success  =  function( message, options ) {

			if ( _.isEmpty( options ) ) {  // Check for if options empty
                var options = {};
            }

            options.type = 'success';

			if (!_.isObject(message)) {

				_.assign( this.optionsObj, {
					text : message
				});

			} else if(_.isObject(message)) {

				_.assign( this.optionsObj, message );
			}

			var notice = new PNotify( _.assign( this.optionsObj, options ));

			notice.get().click(function() {
			    notice.remove();
			});

	  	};

		/**
	    * Show success notification message
	    *
	    * @param string - message
	    * @param object - options  
	    *
	    * @return object
	    *---------------------------------------------------------------- */

	  	this.error  =  function( message, options ) {

			if ( _.isEmpty( options ) ) {  // Check for if options empty
                var options = {};
            }

            options.type = 'danger';

			if (!_.isObject(message)) {

				_.assign( this.optionsObj, {
					text : message
				});

			} else if(_.isObject(message)) {

				_.assign( this.optionsObj, message );
			}

			var notice = new PNotify( _.assign( this.optionsObj, options ));

			notice.get().click(function() {
			    notice.remove();
			});

	  	};

		/**
	    * Show success notification message
	    *
	    * @param string - message
	    * @param object - options  
	    *
	    * @return object
	    *---------------------------------------------------------------- */

	  	this.warn  =  function( message, options ) {

			if ( _.isEmpty( options ) ) {  // Check for if options empty
                var options = {};
            }

            options.type = 'warning';

			if (!_.isObject(message)) {

				_.assign( this.optionsObj, {
					text : message
				});

			} else if(_.isObject(message)) {

				_.assign( this.optionsObj, message );
			}

			var notice = new PNotify( _.assign( this.optionsObj, options ));

			notice.get().click(function() {
			    notice.remove();
			});

	  	};


		/**
	    * Show success notification message
	    *
	    * @param string - message
	    * @param object - options  
	    *
	    * @return object
	    *---------------------------------------------------------------- */

	  	this.info  =  function( message, options ) {

			if ( _.isEmpty( options ) ) {  // Check for if options empty
                var options = {};
            }

            options.type = 'info';

			if (!_.isObject(message)) {

				_.assign( this.optionsObj, {
					text : message
				});

			} else if(_.isObject(message)) {

				_.assign( this.optionsObj, message );
			}

			var notice = new PNotify( _.assign( this.optionsObj, options ));

			notice.get().click(function() {
			    notice.remove();
			});
	  	};
      
  	};

})();;
(function () {
    'use strict';

    angular.module('app.directives', [])
        .directive("lwColorPicker", lwColorPicker)
        .directive("lwPopover", lwPopover)
        .directive('lwDatePicker', [
            '__Utils', '$compile', lwDatePicker
        ])
        .directive('lwEditor', ['__Utils', '$q', lwEditor])
        .directive('lwFilterList', ['$timeout', function ($timeout) {
            return {
                link: function (scope, element, attrs) {

                    var li = Array.prototype.slice.call(element[0].children),
                        searchTerm = attrs.lwFilterList;

                    function filterBy(value) {

                        li.forEach(function (el) {

                            var $ele = $(el),
                                searchTags = $ele.attr('data-tags'),
                                existClass = $ele.attr('class');

                            existClass = existClass.replace('ng-hide', '');

                            el.className = searchTags.toLowerCase().indexOf(value.toLowerCase()) !== -1 ? existClass : existClass + ' ng-hide';

                        });

                    }

                    scope.$watch(attrs.lwFilterList, function (newVal, oldVal) {
                        if (newVal !== oldVal) {
                            filterBy(newVal);
                        }
                    });

                }
            };
        }]);

    /**
     * Custom directive for falt picker
     *
     * @return void
     *---------------------------------------------------------------- */

    function lwDatePicker(__Utils, $compile) {

        return {
            restrict: 'A',
            replace: false,
            require: '?ngModel',
            scope: {
                setmindate: '='
            },
            link: function (scope, elem, attrs, ctrls) {

                var datePickerOp = {
                    clearButton: false,
                    closeButton: true,
                    enableTime: false,
                    dateFormat: "Y-m-d", // Displays: 2017-01-22Z
                    onReady: function (selectedDates, dateStr, instance) {

                        instance.setDate(ctrls.$modelValue);

                    },
                    onOpen: [
                        function (selectedDates, dateStr, instance) {

                            var isPresentNewMinDate = (_.has(scope, 'setmindate') && !_.isUndefined(scope.setmindate));

                            if (isPresentNewMinDate) { // if setmindate is on
                                instance.set({ minDate: scope.setmindate });
                            }

                            instance.setDate(dateStr);

                            var $cal = angular.element(instance.calendarContainer);

                            if ($cal.find('.flatpickr-action').length < 1) {

                                $cal.append($compile(__Utils.template('#lwFlatpickrAction', {
                                    pickerOptions: datePickerOp
                                }))(scope));

                                $cal.find('.flatpickr-clear').on('click', function () {
                                    instance.clear();
                                });

                                $cal.find('.flatpickr-close').on('click', function () {
                                    instance.close();
                                });

                            }
                        }
                    ]

                };

                if (attrs.options) {
                    _.assign(datePickerOp,
                        eval('(' + attrs.options + ')')
                    );
                }

                _.delay(function () {
                    angular.element(elem).flatpickr(datePickerOp);
                }, 500);
            }

        }
    };


    /**
    * lwColorPicker Directive.
    *
    * For apply jquery color box property on element
    *
    * @return void
    *-------------------------------------------------------- */
    function lwColorPicker() {

        return {
            restrict: 'A',
            scope: {
                ngModel: "="
            },
            link: function (scope, element, attrs) {

                element.click(function (e) {

                    e.preventDefault();

                    $(element).colpick({
                        flat: false,
                        layout: 'hex',
                        submit: true,
                        onChange: function (hsb, hex, rgb, el, bySetColor) {
                            // Fill the text box just if the color was set using the picker, and not the colpickSetColor function.

                            scope.$evalAsync(function () {
                                scope.ngModel = hex;
                                if (el.id == 'logo_background_color') {
                                    $('#lwchangeBgHeaderColor').css('background', "#" + hex);
                                }
                            });
                        },
                        onSubmit: function () {

                            $(element).colpickHide();
                        }

                    });

                });

            }
        };
        // return end

    };

    /**
      * lwPopup Directive.
      *
      * For apply jquery expander property on attribute
      *
      * @return void
      *-------------------------------------------------------- */

    function lwPopover() {

        return {
            restrict: 'A',
            link: function (scope, element, attrs) {
                $(element).popover({
                    html: true,
                    trigger: 'focus',
                    content: function () {
                        return attrs.message;
                    }
                });
            }
        };
    };


    function lwEditor(__Utils, $q) {


        return {
            require: '?ngModel',
            scope: {
                'options': '='
            },
            link: function (scope, element, attrs, ngModel) {

                var domElement = angular.element(element)[0];

                var ckConfig = _.assign({
                    filebrowserImageBrowseUrl: window.__appImmutables.ckeditor.filebrowserImageBrowseUrl,
                    filebrowserImageUploadUrl: window.__appImmutables.ckeditor.filebrowserImageUploadUrl,
                    filebrowserBrowseUrl: window.__appImmutables.ckeditor.filebrowserBrowseUrl,
                    filebrowserUploadUrl: window.__appImmutables.ckeditor.filebrowserUploadUrl,
                    // Define the toolbar: http://docs.ckeditor.com/ckeditor4/docs/#!/guide/dev_toolbar
                    // The full preset from CDN which we used as a base provides more features than we need.
                    // Also by default it comes with a 3-line toolbar. Here we put all buttons in a single row.
                    toolbar: [
                        {
                            name: 'styles',
                            items: ['Format', 'Font', 'FontSize']
                        },
                        {
                            name: 'basicstyles',
                            items: ['Bold', 'Italic', 'Underline', 'Strike', 'RemoveFormat', 'CopyFormatting']
                        },
                        {
                            name: 'colors',
                            items: ['TextColor', 'BGColor']

                        },
                        {
                            name: 'align',
                            items: ['JustifyLeft', 'JustifyCenter', 'JustifyRight', 'JustifyBlock']
                        },
                        {
                            name: 'links',
                            items: ['Link', 'Unlink', 'CodeSnippet', 'Embed']
                        },
                        {
                            name: 'paragraph',
                            items: ['NumberedList', 'BulletedList', '-', 'Outdent', 'Indent', '-', 'Blockquote']
                        },
                        {
                            name: 'insert',
                            items: ['Image', 'Table']
                        },
                        {
                            name: 'editing',
                            items: ['Scayt']
                        },
                        {
                            name: 'document',
                            items: ['Print']
                        },
                        {
                            name: 'clipboard',
                            items: ['PasteFromWord', '-', 'Undo', 'Redo']
                        },
                        {
                            name: 'document',
                            items: ['Source']
                        },
                        {
                            name: 'tools',
                            items: ['Maximize']
                        }
                    ],
                    // Since we define all configuration options here, let's instruct CKEditor to not load config.js which it does by default.
                    // One HTTP request less will result in a faster startup time.
                    // For more information check http://docs.ckeditor.com/ckeditor4/docs/#!/api/CKEDITOR.config-cfg-customConfig
                    customConfig: '',
                    // Sometimes applications that convert HTML to PDF prefer setting image width through attributes instead of CSS styles.
                    // For more information check:
                    //  - About Advanced Content Filter: http://docs.ckeditor.com/ckeditor4/docs/#!/guide/dev_advanced_content_filter
                    //  - About Disallowed Content: http://docs.ckeditor.com/ckeditor4/docs/#!/guide/dev_disallowed_content
                    //  - About Allowed Content: http://docs.ckeditor.com/ckeditor4/docs/#!/guide/dev_allowed_content_rules
                    // disallowedContent: 'img{width,height,float}',
                    // extraAllowedContent: 'img[width,height,align]',
                    extraAllowedContent: 'iframe[*]',
                    // Enabling extra plugins, available in the full-all preset: http://ckeditor.com/presets-all
                    //extraPlugins: 'tableresize,uploadimage,uploadfile',
                    extraPlugins: 'colorbutton,font,justify,print,tableresize,embed,autoembed,widget,image2,uploadimage,uploadfile,pastefromword,liststyle,codesnippet,embed,autoembed,autogrow',
                    /*********************** File management support ***********************/
                    // In order to turn on support for file uploads, CKEditor has to be configured to use some server side
                    // solution with file upload/management capabilities, like for example CKFinder.
                    // For more information see http://docs.ckeditor.com/ckeditor4/docs/#!/guide/dev_ckfinder_integration
                    // Uncomment and correct these lines after you setup your local CKFinder instance.
                    // filebrowserBrowseUrl: 'http://example.com/ckfinder/ckfinder.html',
                    // filebrowserUploadUrl: 'http://example.com/ckfinder/core/connector/php/connector.php?command=QuickUpload&type=Files',
                    /*********************** File management support ***********************/
                    // Make the editing area bigger than default.
                    // height: 800,
                    // width : 800,					
                    autoGrow_minHeight: 600,
                    // autoGrow_maxHeight : 1000,
                    autoGrow_onStartup: true,
                    // enterMode : CKEDITOR.ENTER_BR,
                    // fullPage: true,
                    autoGrow_bottomSpace: 50,
                    // readOnly :true,
                    // An array of stylesheets to style the WYSIWYG area.
                    // Note: it is recommended to keep your own styles in a separate file in order to make future updates painless.
                    contentsCss: [
                        // 'https://cdn.ckeditor.com/4.8.0/full-all/contents.css',  
                        'dist/libs/document-editor/document-editor.css',
                        'dist/libs/ckeditor/skins/moono-lisa/editor.css',
                    ],
                    // This is optional, but will let us define multiple different styles for multiple editors using the same CSS file.

                    embed_provider: '//ckeditor.iframe.ly/api/oembed?url={url}&callback={callback}',

                    bodyClass: 'document-editor',
                    // Reduce the list of block elements listed in the Format dropdown to the most commonly used.
                    format_tags: 'p;h1;h2;h3;pre',
                    // Simplify the Image and Link dialog windows. The "Advanced" tab is not needed in most cases.
                    removeDialogTabs: 'image:advanced;link:advanced',
                    removePlugins: 'image,resize',
                    // Define the list of styles which should be available in the Styles dropdown list.
                    // If the "class" attribute is used to style an element, make sure to define the style for the class in "mystyles.css"
                    // (and on your website so that it rendered in the same way).
                    // Note: by default CKEditor looks for styles.js file. Defining stylesSet inline (as below) stops CKEditor from loading
                    // that file, which means one HTTP request less (and a faster startup).
                    // For more information see http://docs.ckeditor.com/ckeditor4/docs/#!/guide/dev_styles
                    stylesSet: [
                        /* Inline Styles */
                        { name: 'Marker', element: 'span', attributes: { 'class': 'marker' } },
                        { name: 'Cited Work', element: 'cite' },
                        { name: 'Inline Quotation', element: 'q' },
                        /* Object Styles */
                        {
                            name: 'Special Container',
                            element: 'div',
                            styles: {
                                padding: '5px 10px',
                                background: '#eee',
                                border: '1px solid #ccc'
                            }
                        },
                        {
                            name: 'Compact table',
                            element: 'table',
                            attributes: {
                                cellpadding: '5',
                                cellspacing: '0',
                                border: '1',
                                bordercolor: '#ccc'
                            },
                            styles: {
                                'border-collapse': 'collapse'
                            }
                        },
                        { name: 'Borderless Table', element: 'table', styles: { 'border-style': 'hidden', 'background-color': '#E6E6FA' } },
                        { name: 'Square Bulleted List', element: 'ul', styles: { 'list-style-type': 'square' } }
                    ]
                }, scope.options);

                var ckElement = CKEDITOR.replace(domElement, ckConfig);

                if (!_.isNull(ngModel.$modelValue)) {
                    ckElement.setData(ngModel.$modelValue);
                }

                ckElement.on("change", function (event) {
                    scope.$apply(function () {
                        if (!_.trim($(ckElement.getData()).text())) {
                            ngModel.$setViewValue('');
                        } else {
                            ngModel.$setViewValue(ckElement.getData());
                        }
                    });
                });

                ckElement.on('pasteState', function () {
                    scope.$apply(function () {
                        if (!_.trim($(ckElement.getData()).text())) {
                            ngModel.$setViewValue('');
                        } else {
                            ngModel.$setViewValue(ckElement.getData());
                        }
                    });
                });

                ckElement.on("instanceReady", function (event) {
                    if (!_.trim($(ckElement.getData()).text())) {
                        ckElement.setData('');
                    } else {
                        ckElement.setData(ngModel.$modelValue);
                    }
                });
            }
        };

    };

})();;
(function() {
'use strict';

	angular.module('app.form', [])
	  	.directive("lwFormSelectizeField", [ 
            '__Form', lwFormSelectizeField
        ])
        .directive("lwFormCheckboxField", [ 
            '__Form', lwFormCheckboxField
        ])
        .directive("lwRecaptcha", lwRecaptcha)
        .directive('lwSelectAllCheckbox', function () {
            return {
                replace: true,
                restrict: 'E',
                scope: {
                    checkboxes: '=',
                    allselected: '=allSelected',
                    allclear: '=allClear'
                },
                templateUrl:'lw-select-all-checkbox-field.ngtemplate',
                link: function ($scope, $element) {

                    $scope.masterChange = function () {
                        if ($scope.master) {
                            angular.forEach($scope.checkboxes, function (cb, index) {
                                cb.isSelected = true;
                            });
                        } else {
                            angular.forEach($scope.checkboxes, function (cb, index) {
                                cb.isSelected = false;
                            });
                        }
                    };

                    $scope.$watch('checkboxes', function () {
                        var allSet = true,
                            allClear = true;
                        angular.forEach($scope.checkboxes, function (cb, index) {
                            if (cb.isSelected) {
                                allClear = false;
                            } else {
                                allSet = false;
                            }
                        });

                        if ($scope.allselected !== undefined) {
                            $scope.allselected = allSet;
                        }
                        if ($scope.allclear !== undefined) {
                            $scope.allclear = allClear;
                        }

                        $element.prop('indeterminate', false);
                        if (allSet) {
                            $scope.master = true;
                        } else if (allClear) {
                            $scope.master = false;
                        } else {
                            $scope.master = false;
                            $element.prop('indeterminate', true);
                        }

                    }, true);
                }
            };
        })
        /**
          * lwFormRadioField Directive.
          * 
          * Form Field Radio Directive -
          * App Level Customise Directive
          *
          * @inject __Form
          *
          * @return void
          *-------------------------------------------------------- */

        .directive("lwFormRadioField", [
            '__Form',
            function ( __Form ) {

              return {

                restrict    : 'E',
                replace     : true,
                transclude  : true,
                scope       : {
                    fieldFor : '@'
                },
                templateUrl     : 'lw-form-radio-field.ngtemplate',
                link            : function(scope, elem, attrs, transclude) {

                    if(elem.hasClass('lw-remove-transclude-tag')) {
                        elem.find('ng-transclude').children().unwrap();
                    }

                    var formData    = elem.parents('form.lw-ng-form')
                                        .data('$formController'),
                    inputElement    = elem.find('.lw-form-field ');

                    //inputElement.prop('id', scope.fieldFor);

                    scope.formField                 = {};
                    scope.formField[scope.fieldFor] = attrs;

                    scope.lwFormData = { formCtrl:formData };

                    // get validation message
                    scope.getValidationMsg = function( key, labelName ) {

                        return __Form.getMsg(key, labelName);

                    };

                }

                }

            }
        ])
    
    /**
     * lwFormSelectizeField Directive.
     * 
     * App level customise directive for angular selectize as form field
     *
     * @inject __Form
     *
     * @return void
     *-------------------------------------------------------- */

    function lwFormSelectizeField(__Form) {

        return {

            restrict    : 'E',
            replace     : true,
            transclude  : true,
            scope       : {
                fieldFor : '@'
            },
            templateUrl : 'lw-form-selectize.ngtemplate',
            link        : function(scope, elem, attrs, transclude) {

                var formData        = elem.parents('form.lw-ng-form')
                                      .data('$formController'),
                    selectElement   = elem.find('.lw-form-field');

                selectElement.prop('id', scope.fieldFor);
              
                scope.formField                 = {};
                scope.formField[scope.fieldFor] = attrs;

                scope.lwFormData = { formCtrl : formData };

                // get validation message
                scope.getValidationMsg = function(key, labelName) {

                    return __Form.getMsg(key, labelName);

                };

            }

        };

    };

    /**
      * Custom directive for bootstrap-material-datetimepicker
      *
      * @return void
      *---------------------------------------------------------------- */
    
    function lwRecaptcha() {
    	
    	 return {
                restrict: 'AE',
                scope   : {
                    sitekey : '='
                },
                require : 'ngModel',
                link : function(scope, elm, attrs, ngModel) {
                    var id;
                    ngModel.$validators.captcha = function(modelValue, ViewValue) {
                        // if the viewvalue is empty, there is no response yet,
                        // so we need to raise an error.
                        return !!ViewValue;
                    };
 
                    function update(response) {
                        ngModel.$setViewValue(response);
                        ngModel.$render();
                    }
                    
                    function expired() {
                        grecaptcha.reset(id);
                        ngModel.$setViewValue('');
                        ngModel.$render();
                        // do an apply to make sure the  empty response is 
                        // proaganded into your models/view.
                        // not really needed in most cases tough! so commented by default
                        // scope.$apply();
                    }

                    function iscaptchaReady() {
                        if (typeof grecaptcha !== "object") {
                            // api not yet ready, retry in a while
                            return setTimeout(iscaptchaReady, 0);
                        }
                        id = grecaptcha.render(
                            elm[0], {
                                // put your own sitekey in here, otherwise it will not
                                // function.
                                "sitekey": attrs.sitekey,
                                callback: update,
                                "expired-callback": expired
                            }
                        );
                    }
                    iscaptchaReady();
                }
            };

    }

    /**
     * lwFormCheckboxField Directive.
     * 
     * App level customise directive for checkbox form field
     *
     * @inject __Form
     *
     * @return void
     *-------------------------------------------------------- */

    function lwFormCheckboxField(__Form) {

        return {

            restrict    : 'E',
            replace     : true,
            transclude  : true,
            scope       : {
                fieldFor : '@'
            },
            templateUrl : 'lw-form-checkbox-field.ngtemplate',
            link        : function(scope, elem, attrs, transclude) {

                var formData        = elem.parents('form.lw-ng-form')
                                      .data('$formController'),
                    selectElement   = elem.find('.lw-form-field');

                selectElement.prop('id', scope.fieldFor);
              
                scope.formField                 = {};
                scope.formField[scope.fieldFor] = attrs;

                scope.lwFormData = { formCtrl : formData };

                // get validation message
                scope.getValidationMsg = function(key, labelName) {

                    return __Form.getMsg(key, labelName);

                };

            }

        };

    };

    
})(); ;
"use strict";
// App Global Resources

// check if browser is ie - http://hsrtech.com/psd-to-html/conditional-comments-ie11/
var isInternetExplorer = false;
var ua = window.navigator.userAgent;
var oldIE = ua.indexOf('MSIE ');
var newIE = ua.indexOf('Trident/');

if ((oldIE > -1) || (newIE > -1)) {
    isInternetExplorer = true;
}

// Promise Polyfill for IE - https://github.com/taylorhakes/promise-polyfill
if(isInternetExplorer) {

	!function(t){function e(){}function n(t,e){return function(){t.apply(e,arguments)}}function o(t){if("object"!=typeof this)throw new TypeError("Promises must be constructed via new");if("function"!=typeof t)throw new TypeError("not a function");this._state=0,this._handled=!1,this._value=void 0,this._deferreds=[],s(t,this)}function r(t,e){for(;3===t._state;)t=t._value;return 0===t._state?void t._deferreds.push(e):(t._handled=!0,void a(function(){var n=1===t._state?e.onFulfilled:e.onRejected;if(null===n)return void(1===t._state?i:f)(e.promise,t._value);var o;try{o=n(t._value)}catch(r){return void f(e.promise,r)}i(e.promise,o)}))}function i(t,e){try{if(e===t)throw new TypeError("A promise cannot be resolved with itself.");if(e&&("object"==typeof e||"function"==typeof e)){var r=e.then;if(e instanceof o)return t._state=3,t._value=e,void u(t);if("function"==typeof r)return void s(n(r,e),t)}t._state=1,t._value=e,u(t)}catch(i){f(t,i)}}function f(t,e){t._state=2,t._value=e,u(t)}function u(t){2===t._state&&0===t._deferreds.length&&a(function(){t._handled||d(t._value)});for(var e=0,n=t._deferreds.length;n>e;e++)r(t,t._deferreds[e]);t._deferreds=null}function c(t,e,n){this.onFulfilled="function"==typeof t?t:null,this.onRejected="function"==typeof e?e:null,this.promise=n}function s(t,e){var n=!1;try{t(function(t){n||(n=!0,i(e,t))},function(t){n||(n=!0,f(e,t))})}catch(o){if(n)return;n=!0,f(e,o)}}var l=setTimeout,a="function"==typeof setImmediate&&setImmediate||function(t){l(t,0)},d=function(t){"undefined"!=typeof console&&console&&console.warn("Possible Unhandled Promise Rejection:",t)};o.prototype["catch"]=function(t){return this.then(null,t)},o.prototype.then=function(t,n){var o=new this.constructor(e);return r(this,new c(t,n,o)),o},o.all=function(t){var e=Array.prototype.slice.call(t);return new o(function(t,n){function o(i,f){try{if(f&&("object"==typeof f||"function"==typeof f)){var u=f.then;if("function"==typeof u)return void u.call(f,function(t){o(i,t)},n)}e[i]=f,0===--r&&t(e)}catch(c){n(c)}}if(0===e.length)return t([]);for(var r=e.length,i=0;i<e.length;i++)o(i,e[i])})},o.resolve=function(t){return t&&"object"==typeof t&&t.constructor===o?t:new o(function(e){e(t)})},o.reject=function(t){return new o(function(e,n){n(t)})},o.race=function(t){return new o(function(e,n){for(var o=0,r=t.length;r>o;o++)t[o].then(e,n)})},o._setImmediateFn=function(t){a=t},o._setUnhandledRejectionFn=function(t){d=t},"undefined"!=typeof module&&module.exports?module.exports=o:t.Promise||(t.Promise=o)}(this);

}

// a container to hold underscore template data
_.templateSettings.variable = "__tData";

_.assign(__globals, {

      authConfig  : {
        redirects   : {
          	guestOnly     : 'dashboard',
          	// authorized    : 'login',
          	accessDenied  : 'unauthorized'
        }
      },

/*      dataStore : {
        persist:false
      },*/

      getScrollOffsets : function() {
            var doc = document, w = window;
            var x, y, docEl;
            
            if ( typeof w.pageYOffset === 'number' ) {
                x = w.pageXOffset;
                y = w.pageYOffset;
            } else {
                docEl = (doc.compatMode && doc.compatMode === 'CSS1Compat')?
                        doc.documentElement: doc.body;
                x = docEl.scrollLeft;
                y = docEl.scrollTop;
            }
            return {x:x, y:y};
        },

        getAuthorizationToken : function() {
            return window.__appImmutables.auth_info.authorization_token;
        },


        getAppImmutables: function(immutableID) {

            if (immutableID) {
                return window.__appImmutables[immutableID];
            } else {
                return window.__appImmutables;
            }
        },

        getAppJSItem: function(key) {

            return window[key];
        },

        getJSString : function(stringID) {

            var messages = this.getAppImmutables('messages');
            return messages.js_string[stringID];

        },

        getReplacedString : function(element, replaceKey, replaceValue) {

            return element.attr('data-message')
                    .replace(replaceKey , '<strong>'+unescape(replaceValue)+'</strong>');

        },

        /**
          * check if user logged in
          *
          * @return bool
          *---------------------------------------------------------------- */

        isLoggedIn : function() {
            return window.__appImmutables.auth_info.authorized;
        },

        // Check is Numeric vlaue or pure number
        isNumeric: function(n) {
            return !isNaN(parseFloat(n)) && isFinite(n);
        },

        /**
          * Show action confirmation
          *
          * @param object options
          * @param function callback
          * @param function closeCallback
          *
          * @return void
          *---------------------------------------------------------------- */

        showConfirmation : function(options, callback, closeCallback) {

            var defaultOptions       = {
                title              : 'Are you sure?',
                showCancelButton   : true,
                cancelButtonText   : 'Cancel',
                //closeOnConfirm     : false,
           //     showCancelButton   : true,
                allowEscapeKey     : false,
  				allowOutsideClick  : false,
                confirmButtonColor :  "#c9302c",
                confirmButtonClass : 'btn-success',
                onOpen: function() {
                    $('html').addClass('lw-disable-scroll');
                },
                onClose: function() {
                    $('html').removeClass('lw-disable-scroll');
                }
            };

            // Check if callback exist
            if (callback && _.isFunction(callback)) {

                _.assign(defaultOptions, options);

                swal.fire(defaultOptions).then(function(result) {
				    // handle Confirm button click
				    // result is an optional parameter, needed for modals with input
				    if (result.value) {
                        return callback.call(this);
                    }

				  }, function(dismiss) {

				    	// dismiss can be 'cancel', 'overlay', 'close', 'timer'
                        if (closeCallback && _.isFunction(closeCallback)) {
                            return closeCallback.call( this, dismiss );
                        }

				  });

            } else {

                // show only simple confirmation
                swal.fire(options.title, options.text, options.type);
            }

        },

        getSelectizeOptions : function (options) {

            this.defaultOptions = {  
                maxItems        : 1,
                valueField      : 'id',
                labelField      : 'name',
                searchField     : ['name'],
                onInitialize    : function(selectize) {

					var currentValue = selectize.getValue();

					if (_.isEmpty(currentValue) === false &&
					    (_.isArray(currentValue) === false &&
					        _.isObject(currentValue) === false &&
					        _.isString(currentValue) === true)) {
					   
					        if (_.includes(currentValue, ',')) {

					            var currentValues = currentValue.split(",");
					           
					            for(var a in currentValues) {
					               
					                currentValues[a] = (__globals.isNumeric(currentValues[a])) ? Number(currentValues[a]) : currentValues[a];
					            }

					            selectize.setValue(currentValues);

					        } else {

					            if (__globals.isNumeric(currentValue)) {

					                selectize.setValue(Number(currentValue));

					            } else {

					                selectize.setValue(currentValue);
					            }
					        }
					    }
					}
            };

            return _.assign(this.defaultOptions, options);
        },

        /**
         * Check if current app is Public or manage app
         *
         *-------------------------------------------------------- */
        isPublicApp : function() {

            return window.__appImmutables['publicApp'];
        },
		/**
	     * slug text
	     *
	     *-------------------------------------------------------- */
	    slug : function(str) {

			var $slug   = '';
		    var trimmed = $.trim(str);
		    	$slug = trimmed.replace(/[^a-z0-9-]/gi, '-').
		    	replace(/-+/g, '-').
		    	replace(/^-|-$/g, '');
		    return $slug.toLowerCase();
		},

		/**
	      * get config items
	      *
	      * @return bool
	      *---------------------------------------------------------------- */

	    configItem : function(key) {
	        return window.__appImmutables.config[key];
	    },

	    /**
	     * Generate key vlaue page formate for
	     *
	     * @param array $data
	     *-------------------------------------------------------- */

	     generateKeyValueOption : function(configKey) {

			var items = window.__appImmutables.config[configKey];
	        var option   = [];

	      _.forEach(items, function(value, key) {

            option.push({
                id : parseInt(key),
                name : value
	        });

	      });

	      return option;
	     },

		/**
	     * Generate key vlaue page formate for
	     *
	     * @param array $data
	     *-------------------------------------------------------- */

	     generateValueAsKeyOption : function(items) {

	        var option   = [];

	      _.forEach(items, function(value, key) {

            option.push({
                id   : value,
                name : value
	        });

	      });

	      return option;
	     },

	   /**
	     * Generate key value option for items
	     *
	     * @param array $items
	     *-------------------------------------------------------- */

	     generateKeyValueItems : function(items) {

			var option   = [];

			_.forEach(items, function(value, key) {

            option.push({
                id : parseInt(key),
                name : value
	        });

	      });

	      return option;
	     },
        /**
         * Redirect browser
         *
         * @param array $url
         *-------------------------------------------------------- */

         showProcessingDialog : function(url) {

            $('html').addClass('lw-has-disabled-block');
            $('.lw-disabling-block').addClass('lw-disabled-block lw-has-processing-window');
         },

        /**
         * Redirect browser
         *
         * @param array $url
         *-------------------------------------------------------- */

         hideProcessingDialog : function(url) {

            $('html').removeClass('lw-has-disabled-block');
            $('.lw-disabling-block').removeClass('lw-disabled-block lw-has-processing-window');
         },

        /**
         * Show Button Loader
         *
         * @param array $url
         *-------------------------------------------------------- */

         showButtonLoader : function(url) {

            $('.lw-btn-loading').append(' <span class="fa fa-refresh fa-spin"></span>').prop("disabled", true);
         },

        /**
         * Hide Button Loader
         *
         * @param array $url
         *-------------------------------------------------------- */

         hideButtonLoader : function(url) {

            $('.lw-btn-loading span').removeClass('fa fa-refresh fa-spin');
            $('.lw-btn-loading').prop("disabled", false);
         },

        /**
         * Redirect browser
         *
         * @param array $url
         *-------------------------------------------------------- */

         redirectBrowser : function(url) {

            __globals.showProcessingDialog();

            window.location = url;
         },

        setCookie : function(cname, cvalue, exdays) {
            window.localStorage.setItem(cname, cvalue);
        },

        getCookie : function(cname) {

            var name = cname + "=";

            var ca = document.cookie.split(';');

            for( var i = 0; i < ca.length; i++) {

                var c = ca[i];

                while (c.charAt(0) == ' ') {
                    c = c.substring(1);
                }

                if (c.indexOf(name) == 0) {
                    return c.substring(name.length, c.length);
                }
            }

            var getItem =  window.localStorage.getItem(cname);
           
            if (getItem === 'undefined') {
                
                window.localStorage.removeItem(cname);

                return '';
            }

            if (_.isNull(getItem)) {

                localStorage.removeItem(cname);

                return false;
            }

            return localStorage.getItem(cname);
        }

});

// $.trumbowyg.svgPath = '../__FRONTFIELD/node_modules/trumbowyg/dist/ui/icons.svg';

//Datatables Defaults
 $.extend( $.fn.dataTable.defaults, {
    "serverSide"      : true,
    "searchDelay"     : 1800,
    "iCookieDuration" : 60,
    "paging"          : true,
    "processing"      : true,
    "responsive"      : true,
   // "pageLength"      : 1,
    "destroy"         : true,
    "retrieve"        : true,
    "lengthChange"    : true,
    "language"        : {
                          "emptyTable": "There are no records to display."
                        },
    "searching"       : false,
    "ajax"            : {
      // any additional data to send
      "data"          : function ( additionalData ) {
        additionalData.page = (additionalData.start / additionalData.length) + 1;
      }
    }
  });;
(function() {
'use strict';

  angular.module('app.fileUploader', []).
    service("lwFileUploader", [
        '$rootScope','__Utils', '__DataStore',
        'appServices', 'appNotify','$q', lwFileUploader ]);


  /**
     * lwFileUploader service.
     *
     * fileUploader
     *
     * @inject $rootScope
     * @inject FileUploader
     * @inject __DataStore
     * @inject appServices
     * @inject appNotify
     *
     * @return object
     *-------------------------------------------------------- */

    function lwFileUploader($rootScope, __Utils,
        __DataStore, appServices, appNotify, $q) {

        var uploader;

        /**
        * Get temp uploaded files
        *
        * @inject scope
        * @inject option
        * @inject callback
        *
        * @return void
        *-----------------------------------------------------------------------*/

        this.upload = function(option, callback)
        {
        	var progress = 0,
                message = '',
                reaction;

            $('#lwFileupload').fileupload({
                url: option.url,
                dataType: 'json',
                headers     : {
                    'X-XSRF-TOKEN': __Utils.getXSRFToken(),
                    "Authorization" : 'Bearer ' + __globals.getCookie('auth_access_token')
                },
                stop: function (e, data) {
                    //callback.call(this, true);	
					$rootScope.$emit('lw-loader-event-stop', true);
                    $("#lw-spinner-widget").hide();
					$("#lwFileupload").attr("disabled", false);

                    if (reaction == 1) {
                        appNotify.success(message, {sticky : false});
                    } else if (reaction != 1) {
                        appNotify.error(message, {sticky : false});
                    }
                    
                },
                done: function (e, data) {

                    message = data.result.data.message;
                    reaction = data.result.reaction;
					
                    callback.call(this, data);
                },
                progressall: function (e, data) {

                    progress = parseInt(data.loaded / data.total * 100, 10);

					//$rootScope.$emit('lw-loader-event-start', true);
                   
                    /*if (progress < 99) {
                        appNotify.info('uploading...'+progress+'%', {sticky : true});
                    } else {
                    	appNotify.info('uploading...'+progress+'%', {sticky : false});
                    }*/
                },
				start : function (e, data) {
					
					$rootScope.$emit('lw-loader-event-start', true);
				   	$("#lw-spinner-widget").show();
					$("#lwFileupload").attr("disabled", true);
				}
            });

        };

        /*
        Get Login attempts
        -----------------------------------------------------------------*/
        this.mediaDataService = function(url) {

            //create a differed object
            var defferedObject = $q.defer();

            __DataStore.fetch(url, { 'fresh' : true } )
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


        /**
        * Get temp uploaded files
        *
        * @inject scope
        * @inject option
        * @inject callback
        *
        * @return void
        *-----------------------------------------------------------------------*/

        this.getTempUploadedFiles = function(scope, option, callback)
        {
            this.mediaDataService(option.url)
                .then(function(responseData) {

                scope.uploadedFile  = responseData.data.files;

                callback.call(this, scope.uploadedFile);

            });

        };


        /**
        * Open temp uploaded Files dialog
        *
        * @inject scope
        * @inject option
        * @inject callback
        *
        * @return void
        *-----------------------------------------------------------------------*/

        this.openDialog = function(scope, option, callback)
        {
            appServices.showDialog(option, {
                templateUrl : __globals.getTemplateURL(
                    'media.uploaded-media'
                )
            }, function(promiseObj) {

               callback.call(this, promiseObj);

            });

        };

        /**
        * Open temp uploaded Files dialog
        *
        * @inject scope
        * @inject option
        * @inject callback
        *
        * @return void
        *-----------------------------------------------------------------------*/

        this.openAttachmentDialog = function(scope, option, callback)
        {
            appServices.showDialog(option, {
                templateUrl : __globals.getTemplateURL(
                    'media.uploaded-attachment'
                )
            }, function(promiseObj) {

               callback.call(this, promiseObj);

            });

        };


    };

})();
//# sourceMappingURL=../source-maps/app-support-app.src.js.map
