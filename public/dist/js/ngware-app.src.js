"use strict";
/**
  * Release Version - 0.5.x
  *
  * __globals : 0.5.14 - 15 MAY 2020
  *
  *-------------------------------------------------------- */

// a container to hold underscore template data
_.templateSettings.variable = "__tData";

var __globals = {

    /**
    * Laravel get template route name
    *
    *-------------------------------------------------------- */

    templateRouteName: 'template.get',

    /**
    * Get laravel template url 
    * @param string {viewName} - laravel view file name
    *
    * return url string
    *-------------------------------------------------------- */

    getTemplateURL: function (viewName) {


        var templateURL = window.appConfig['appBaseURL']
            + window.__appImmutables.routes[this.templateRouteName];

        // Replace viewName parameter to actual view file name and return new url
        return templateURL.replace('{viewName}', viewName);

    },

    /**
    * Process Redirect state via route
    * @param obj {state} - state
    *
    * return void
    *-------------------------------------------------------- */

    processStateViaRoute: function (state, elseCallback) {
        var stateInfo = window.localStorage.getItem('state_via_route');
        var redirectState = JSON.parse(stateInfo ? stateInfo : '{}');
        window.localStorage.setItem('state_via_route', '');
        if (_.has(redirectState, 'stateName')
            && !_.isUndefined(redirectState.stateName)
            && !_.isEmpty(redirectState.stateName)) {

            _.defer(function () {
                if (_.isEmpty(redirectState.stateParams)) {
                    state.go(redirectState.stateName);
                } else {
                    state.go(redirectState.stateName, redirectState.stateParams);
                }
            });

        } else {
            if (_.isString(elseCallback)) {
                state.go(elseCallback);
            } else {
                elseCallback.call(this, state, redirectState);
            }
        }
    },

    /**
    * Get qualified State Configuration Object 
    * @param string {url} - URL for state
    * @param string {templateRouteID} - laravel template route ID for this.getTemplateURL
    * @param string {addtionalOptions} - additional object parameters 
    *
    * return Object
    *-------------------------------------------------------- */

    stateConfig: function (url, templateRouteID, addtionalOptions) {

        // check if url not sent
        if (!url) {
            console.error("Error: URL is missing");
            throw new Error("URL is missing");
        }

        // if templateURL not sent check for template markup
        if (!templateRouteID && addtionalOptions
            && !_.has(addtionalOptions, 'template')) {
            //  console.error( "Error: templateRouteID is missing" );
            //  throw new Error( "templateRouteID is missing" );

            addtionalOptions.template = "";
        }

        var stateOptions = {
            url: url
        };

        if (templateRouteID) {
            stateOptions.templateUrl
                = this.getTemplateURL(templateRouteID.replace(/\//g, "."));
        }

        // assign additional options if available
        if (addtionalOptions && _.isObject(addtionalOptions)) {
            _.assign(stateOptions, addtionalOptions);
        }

        return stateOptions;

    },

    /**
     * Store application messages.
     *
     *-------------------------------------------------------- */

    messages: (window.__appImmutables && window.__appImmutables.messages) ? window.__appImmutables.messages : [],

    getReactionMessage: function (reactionCode) {

        var reactionMessage = this.messages.validation.reactions[reactionCode];

        if (reactionMessage && _.isArray(reactionMessage)) {
            reactionMessage = _.sample(reactionMessage);
        }

        return reactionMessage ? reactionMessage : false;
    },
    __clogCount: 0,
    /**
     * Console the items requested from __clog Laraware helper function
     *
     *-------------------------------------------------------- */
    clog: function (clogData) {

        var clCount = 1,
            clogType = clogData.__clogType ? clogData.__clogType : '';
        _.forEach(clogData.__clog, function (__clogValue) {
            _.forEach(__clogValue, function (value) {
                console.log('%c __clog ' + clogType + ' ' + clCount + " --------------------------------------------------", 'color: #bada55');
                console.log('%c ' + value, 'color: #9c9c9c');
                clCount++;
                __globals.__clogCount++;
            });
        });

        console.log("%c ------------------------------------------------------------ __clog " + clogType + " items end." + ' TotalCount: ' + __globals.__clogCount, 'color: #bada55');
    },

    /**
    * detect IE
    * returns version of IE or false, if browser is not Internet Explorer
    * 
    */
    detectIE: function () {

        var ua = window.navigator.userAgent;

        // Test values; Uncomment to check result …

        // IE 10
        // ua = 'Mozilla/5.0 (compatible; MSIE 10.0; Windows NT 6.2; Trident/6.0)';

        // IE 11
        // ua = 'Mozilla/5.0 (Windows NT 6.3; Trident/7.0; rv:11.0) like Gecko';

        // Edge 12 (Spartan)
        // ua = 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/39.0.2171.71 Safari/537.36 Edge/12.0';

        // Edge 13
        // ua = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/46.0.2486.0 Safari/537.36 Edge/13.10586';

        var msie = ua.indexOf('MSIE ');
        if (msie > 0) {
            // IE 10 or older => return version number
            return parseInt(ua.substring(msie + 5, ua.indexOf('.', msie)), 10);
        }

        var trident = ua.indexOf('Trident/');
        if (trident > 0) {
            // IE 11 => return version number
            var rv = ua.indexOf('rv:');
            return parseInt(ua.substring(rv + 3, ua.indexOf('.', rv)), 10);
        }

        var edge = ua.indexOf('Edge/');
        if (edge > 0) {
            // Edge (IE 12+) => return version number
            return parseInt(ua.substring(edge + 5, ua.indexOf('.', edge)), 10);
        }

        // other browser
        return false;
    },

    /**
     * Access the appImutable items
     *
     * @param string {itemName} - itemName string
     * @param string {itemValue} - update itemValue string     
     *     
     * @return url-friendly mixed
     *-------------------------------------------------------- */

    appImmutable: function (itemName, itemValue) {

        if (!itemName) {
            return window.__appImmutables;
        } else if (!itemValue) {
            return window.__appImmutables[itemName];
        } else {
            return window.__appImmutables[itemName] = itemValue;
        }
    },

    /**
     * Access the appTemps items
     *
     * @param string {itemName} - itemName string
     * @param string {itemValue} - update itemValue string     
     *     
     * @return url-friendly mixed
     *-------------------------------------------------------- */

    appTemps: function (itemName, itemValue) {

        if (!window.__appTemps) {
            return false
        } else if (!itemName) {
            return window.__appTemps;
        } else if (!itemValue) {
            return window.__appTemps[itemName];
        } else {
            return window.__appTemps[itemName] = itemValue;
        }
    }

};
/*----------------------DIRECT GLOBALS ---------------------------------------------------------------------------------- */
/**
* Dump and die  
* @param n number of parameters
*
* return void
*-------------------------------------------------------- */
window.__dd = function (arg1, arg2) {

    if (window.appConfig && window.appConfig.debug) {

        console.error("JS __dd --------------------------------------------------");

        var args = Array.prototype.slice.call(arguments);

        for (var i = 0; i < args.length; ++i) {
            console.log(args[i]);
        }

        throw new Error("-------------------------------------------------- JS __dd END");
    }
}

/**
* Print data
* @param n number of parameters
*
* return void
*-------------------------------------------------------- */
window.__pr = function () {

    if (window.appConfig && window.appConfig.debug) {

        console.info("JS __pr --------------------------------------------------");

        var args = Array.prototype.slice.call(arguments);

        for (var i = 0; i < args.length; ++i) {
            console.log(args[i]);
        }

        console.groupCollapsed("-------------------------------------------------- JS __pr END");
        console.trace();
        console.groupEnd();
        //console.info( "-------------------------------------------------- JS __pr END" );
    }
}
// @since 0.5.14 - 15 MAY 2020
// suppress Datatable error messages if debug mode is off 
if (window.appConfig && (window.appConfig.debug == false)) {
    if ($.fn.dataTable) {
        $.fn.dataTable.ext.errMode = 'none';
    }
};
(function () {
  'use strict';
  /**
    * Release Version - 0.5.x
    *
    * __Utils : 0.9.15 - 05 JAN 2018
    *
    *-------------------------------------------------------- */

  angular.module("lw.core.utils", []).
    service("__Utils", [__Utils]);

  function __Utils() {

    this.log = function (text, textStyle) {

      if (window.appConfig && window.appConfig.debug) {

        var consoleTextStyle = '',
          prependForStyle = '';

        if (textStyle && _.isString(text)) {
          consoleTextStyle = textStyle;
          prependForStyle = '%c ';

          console.log(prependForStyle + text, consoleTextStyle);
        } else {
          console.log(text);
        }

      }
    };

    this.syntaxHighlight = function (json) {
      json = json.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
      return json.replace(/("(\\u[a-zA-Z0-9]{4}|\\[^u]|[^\\"])*"(\s*:)?|\b(true|false|null)\b|-?\d+(?:\.\d*)?(?:[eE][+\-]?\d+)?)/g, function (match) {
        var cls = 'color: darkorange;'; /*number*/
        if (/^"/.test(match)) {
          if (/:$/.test(match)) {
            cls = 'color: red;'; /*key*/
          } else {
            cls = 'color: green;'; /*string*/
          }
        } else if (/true|false/.test(match)) {
          cls = 'color: blue;'; /*boolean*/
        } else if (/null/.test(match)) {
          cls = 'color: magenta;'; /*null*/
        }
        return '<span style="' + cls + '">' + match + '</span>';
      });
    };

    this.displayInTabWindow = function (text) {

      if (window.appConfig && window.appConfig.debug) {
        if (text) {
          var textToPrint = '';
          if (_.isObject(text)) {

            if (_.has(text, 'data')) {
              textToPrint = '<pre style="font-size:14px; outline: 1px solid #ccc; padding: 10px; margin: 0px;"><strong>URL: </strong>' + text.config.url + ' <strong><br>Method: </strong>' + text.config.method + ' <strong><br>statusText: </strong>' + text.statusText + ' (' + text.status + ') <strong style="color:red"><br>Error Message: ' + text.data.message + '</strong></pre>';
            }
            textToPrint += '<pre style="outline: 1px solid #ccc; padding: 5px; margin: 0px;">' + this.syntaxHighlight(JSON.stringify(text, null, 4)) + '</pre>';
          } else {
            textToPrint = text;
          }


          var dynamicTabWindow = window.open('', '_blank');
          dynamicTabWindow.document.write(textToPrint);
          dynamicTabWindow.document.close(); // necessary for IE >= 10
          dynamicTabWindow.focus(); // necessary for IE >= 10
        } else {
          console.log("__Utils: Text not found for window.")
        }
      }
    };

    this.openEmailDebugView = function (url) {

      if (window.appConfig && window.appConfig.debug) {
        window.open(url, "__emailDebugView");
        this.info("Request Sent to open Email Debug View.");
      }
    };

    this.error = function (text) {

      if (window.appConfig && window.appConfig.debug) {
        console.error(text);
      }
    };

    this.info = function (text) {

      if (window.appConfig && window.appConfig.debug) {
        console.info(text);
      }
    };

    this.warn = function (text) {

      if (window.appConfig && window.appConfig.debug) {
        console.warn(text);
      }
    };

    this.throwError = function (text) {

      if (window.appConfig && window.appConfig.debug) {
        throw new Error(text);
      }
    };

    this.jsdd = function (response) {

      if (window.appConfig && window.appConfig.debug) {

        if (response.__dd && response.__pr) {
          if (!response.__prExecuted) {
            var prCount = 1;
            _.forEach(response.__pr, function (__prValue) {

              var debugBacktrace = '';

              console.log('%c Server __pr ' + prCount + " --------------------------------------------------", 'color:#f0ad4e');

              _.forEach(__prValue, function (value, key) {

                if (key !== 'debug_backtrace') {
                  console.log(value);

                } else {

                  debugBacktrace = value;
                }
              });

              console.log('%c Reference  --------------------------------------------------', 'color:#f0ad4e');
              console.log(debugBacktrace);

              prCount++;
            });
            response.__prExecuted = true;
            console.log("%c ------------------------------------------------------------ __pr end", 'color: #f0ad4e');
          }
        }

        if (response.__dd && response.__clog) {
          if (!response.__clogExecuted) {
            __globals.clog(response);

            response.__clogExecuted = true;
          }
        }

        if (response.__dd && response.__dd === '__dd') {
          if (!response.__ddExecuted) {
            console.log('%c Server __dd  --------------------------------------------------', 'color:#ff0000');
            var ddCount = 1;
            _.forEach(response.data, function (value, key) {

              if (key !== 'debug_backtrace') {
                //  console.log('%c __dd item '+ ddCount+" --------------------------------------------------", 'color:#ff0000');
                console.log(value);
                ddCount++;
              } else {
                console.log('%c Reference  --------------------------------------------------', 'color:#ff0000');
                console.log(value);
              }
            });
            response.__ddExecuted = true;
          }

          console.log("%c ------------------------------------------------------------ __dd end", 'color: #ff0000');

          throw '------------------------------------------------------------ __dd end.';
        }
      }
    };

    this.time = function (text) {

      if (window.appConfig && window.appConfig.debug && (__globals.detectIE() >= 11 || __globals.detectIE() == false)) {
        console.time(text);
      }
    };

    this.timeEnd = function (text) {

      if (window.appConfig && window.appConfig.debug && (__globals.detectIE() >= 11 || __globals.detectIE() == false)) {
        console.timeEnd(text);
      }
    };


    /**
     * Get URL string based on Laravel Routes.
     *
     * @param  string/object route
     * @param  params object  
     *     
     * @return string
     *-------------------------------------------------------- */

    this.apiURL = function (route, params) {

      var strNewURL = window.appConfig['appBaseURL'];

      if (_.isObject(route) && _.has(route, 'apiURL')) {

        strNewURL = strNewURL + window.__appImmutables.routes[route.apiURL];

        if (_.isObject(route)) {

          _.forEach(route, function (value, key) {

            if (key !== 'apiURL') {
              strNewURL = strNewURL.replace("{" + key + "}", value);
            }

          });

        }

      } else if (_.isString(route)) {

        strNewURL = strNewURL + window.__appImmutables.routes[route];

        if (_.isObject(params)) {

          _.forEach(params, function (value, key) {

            strNewURL = strNewURL.replace("{" + key + "}", value);

          });

        }

      } else {

        this.error("__Utils:: Invalid API url");

      }

      return strNewURL;

    };

    /**
     * Get URL template url for Laravel views/template.
     *
     * @param  string viewName
     *     
     * @return string
     *-------------------------------------------------------- */
    this.getTemplateURL = function (viewName) {

      var strNewURL,
        viewName = viewName.replace(/\//g, ".");
      var params = [
        { find: "viewName", replace: viewName }
      ];
      strNewURL = window.appConfig['appBaseURL'] + window.__appImmutables.routes["template.get"];

      // replace routename from strNewURL to get valid template URL
      for (var i = params.length - 1; i >= 0; i--) {

        strNewURL = strNewURL.replace("{" + params[i].find + "}", params[i].replace);

      }

      return strNewURL;

    };

    /**
     * Underscore template compilation utility
     *
     * @param string {templateName} - html template identifier including (# for id or . for class)
     * @param object {dataObj}
     *     
     * @return formatted html
     *-------------------------------------------------------- */

    this.template = function (templateName, dataObj) {

      var $templateHtml = $("script" + templateName).html();

      if ($templateHtml) {

        var _template = _.template($templateHtml);

        return _template(dataObj);

      } else {
        return dataObj;
      }
    };

    /**
     * Convert String to slug (url-friendly) utility
     *
     * @param string {rawText} - row string
     *     
     * @return url-friendly string
     *-------------------------------------------------------- */

    this.convertToSlug = function (rawText) {

      if (rawText) {

        return rawText
          .toLowerCase()
          .replace(/[^\w ]+/g, '')
          .replace(/ +/g, '-')
          ;
      }
    };

    /**
     * 
     * convert number values of object to string
     *
     * @param object {object} - object
     *     
     * @return object
     *-------------------------------------------------------- */

    this.objectNumberValuesToString = function (object) {

      return _.mapValues(object, function (value) {

        if (_.isNumber(value)) {

          return String(value);

        } else {

          return value;

        }
      });
    };

    /**
     * 
     * object/array to json string
     *
     * @param object {value} - object
     *     
     * @return string
     *-------------------------------------------------------- */

    this.jsonStringify = function (value) {

      return JSON.stringify(value);

    };

    /**
     * 
     * Stringify all object/array values
     *
     * @param object/array {object}
     *     
     * @return object/array
     *-------------------------------------------------------- */

    this.stringifyObjectValues = function (object) {

      var _utils = this;

      return _.mapValues(object, function (value) {

        if (_.isNumber(value)) {

          return String(value);

        } else if (_.isArray(value) || _.isObject(value)) {

          return _utils.stringifyObjectValues(value);

        } else {

          return value;

        }

      });
    };

    /**
      * Delay action for particuler time
      *
      * @return object
      *---------------------------------------------------------------- */

    this.delayAction = function (callbackFunction, delayInitialLoading) {

      var delayInitialLoading = (delayInitialLoading
        && _.isNumber(delayInitialLoading))
        ? delayInitialLoading
        : 300;
      setTimeout(function () {

        callbackFunction.call(this);

      }, delayInitialLoading);
    };

    /**
     * 
     * generate random string id
     *     
     * @return object
     *-------------------------------------------------------- */
    this.generateUID = function () {
      return Math.random().toString(36).substr(2, 9);
    }

    /**
     * 
     * Get XSRF token from cookie
     *     
     * @return object
     *-------------------------------------------------------- */
    this.getXSRFToken = function () {

      var xsrfValue = "",
        bb = "",
        pCOOKIES = [];
      pCOOKIES = document.cookie.split('; ');

      for (bb = 0; bb < pCOOKIES.length; bb++) {

        var rawCookieVal = [];
        rawCookieVal = pCOOKIES[bb].split('=');

        if (rawCookieVal[0] == 'XSRF-TOKEN') {
          xsrfValue = unescape(rawCookieVal[1]);
        }

      }

      __appImmutables.security_token = xsrfValue;

      return xsrfValue;

    };

  };

})();
;
(function () {
	'use strict';

	/**
	 * __Auth Service - 0.3.2 - 22 APR 2019
	 * 
	 * User Authentication Service.
     *
     *   * Internal Dependencies:
     *
     * __Utils
     *
     * External Dependencies:
     * 
     * AngularJS     1.3.0   +     - http://angularjs.org
     * lodash        3.9.3   +     - http://lodash.com
     *
     *-------------------------------------------------------- */

	angular.module("lw.auth", []).
		service("__Auth", [
			'__Utils', '$rootScope', '$transitions', __Auth
		]);

	function __Auth(__Utils, $rootScope, $transitions) {

		this.authConfig = {
			redirects: {
				guestOnly: 'dashboard',
				authorized: 'login',
				accessDenied: 'unauthorized'
			}
		};

		// check if app lavel auth config is there
		if (__globals && __globals.authConfig) {

			_.assign(this.authConfig, __globals.authConfig);

		};

		/**
	      * Check if user logged in or not
	      *
	      * @return boolean
	      *---------------------------------------------------------------- */

		this.isLoggedIn = function () {

			return (this.authInfo('authorized') === true) ? true : false;

		};

	    /**
	      * Get user auth information
	      *
	      * @param string key 
	      *
	      * @return object value / object
	      *---------------------------------------------------------------- */

		this.authInfo = function (key) {

			if (key && _.has(window.__appImmutables.auth_info, key)) {

				return window.__appImmutables.auth_info[key];

			} else {

				return window.__appImmutables.auth_info;

			}

		};

	    /**
	      * Get user auth information
	      *
	      * @param string key 
	      *
	      * @return object value / object
	      *---------------------------------------------------------------- */

		this.resetAuthInfo = function (authInfo) {

			if (authInfo && _.isObject(authInfo)) {

				window.__appImmutables.auth_info = authInfo;

			} else {

				window.__appImmutables.auth_info = {};
			}

			$rootScope.$broadcast('lw.auth.event.reset', authInfo);
		}

    	/**
	      * Verify route for access
	      *
	      * @param object state - $state provider
	      *
	      * @return boolean
	      *---------------------------------------------------------------- */

		this.verifyRoute = function (state) {

			this.state = state;

			var redirects = this.authConfig.redirects,
				_auth = this;

			$transitions.onSuccess({}, function (transition) {

				var stateObj = transition.to(),
					paramObj = transition.params();

				$rootScope.$broadcast('lw.events.state.change_start', stateObj, paramObj);

				var accessObject = _.has(stateObj, 'access') ? stateObj.access : false;

				if (accessObject == false) { // check if page public or not

					return false;

				}

				if (accessObject && _.has(accessObject, 'guestOnly') && _auth.isLoggedIn()) {

					__Utils.log('__Auth:: Access denied for ' + stateObj.name + ' ( guest only access ) state');

					state.go(redirects.guestOnly);

					event.preventDefault();

					return false;
				}

				var accessKeys = _auth.authInfo();

				if (accessObject && _.has(accessObject, 'designation')) {

					// check if access to multiple designations
					if (_.isArray(accessObject.designation)
						&& _.includes(accessObject.designation, accessKeys.designation) === false) {

						__Utils.log('__Auth:: Access denied for ' + stateObj.name + ' state ( not allowed )');

						state.go(redirects.accessDenied);

						event.preventDefault();

						return false;
						// if not check access for single
					} else if (!_.isArray(accessObject.designation) && accessObject.designation !== accessKeys.designation) {

						__Utils.log('__Auth:: Access denied for single' + stateObj.name + ' state ( not allowed )');

						state.go(redirects.accessDenied);

						event.preventDefault();

						return false;

					}

				}

				if (accessObject && _.has(accessObject, 'authority')) {

					// check if routes available for access
					if (_.includes(__globals.appImmutable('availableRoutes'), accessObject.authority) === false) {

						__Utils.log('__Auth:: Access denied for ' + stateObj.name + ' state ( not allowed )');

						state.go(redirects.accessDenied);

						event.preventDefault();

						return false;

					}

				}

				if (accessObject && !_.has(accessObject, 'guestOnly') && !_auth.isLoggedIn()) {

					__Utils.log('__Auth:: Access denied for ' + stateObj.name + ' ( authorized only ) state');

					_auth.registerIntended({
						name: stateObj.name,
						params: paramObj,
						except: ['login', 'logout']
					}, function () {

						state.go(redirects.authorized);

					});

					event.preventDefault();

					return false;
				}

			});


		};

        /**
          * Get intended state object
          *
          *
          * @return object
          *---------------------------------------------------------------- */

		this.getIntendedState = function () {

			return __globals.intended;
		};

        /**
          * Check if any child state exist
          *
          *
          * @return object
          *---------------------------------------------------------------- */

		this.isIntendedStateParentRegistered = function (stateObj) {

			return __globals.intended ? _.includes(__globals.intended.name + '.', stateObj.name) : false;
		};

        /**
          * Register intended state
          *
          * @param object stateObj - object containing required values for name & options are params, except, preventOverride
          *
          * @return void
          *---------------------------------------------------------------- */

		this.registerIntended = function (stateObj, callbackFunction) {

			var exceptStates = (_.has(stateObj, 'except') && _.isArray(stateObj.except)
				&& _.includes(stateObj.except, stateObj.name)) ? true : false;

			if (!exceptStates && !this.isIntendedStateParentRegistered(stateObj)) {

				__globals.intended = stateObj;

			}

			if (callbackFunction && _.isFunction(callbackFunction)) {

				callbackFunction.call(this, __globals.intended);

			}

		};

        /**
          * redirect to intended state
          *
          * @param string state - fallbackState state
          *
          * @return void
          *---------------------------------------------------------------- */

		this.redirectIntended = function (fallbackState) {

			if (__globals.intended && __globals.intended.name) {

				this.state.go(__globals.intended.name, __globals.intended.params);

			} else if (fallbackState) {

				this.state.go(fallbackState);

			}

			__globals.intended = {};

		};

    	/**
	      * Set authentication information
	      *
	      * @param object authInfo 
	      *
	      * @return void
	      *---------------------------------------------------------------- */

		/*
			  Notes: authInfo should have following properties
				  authorization ( token string) ,
				  authorized ( boolean )
			  designation ( numeric access level code ),
			  reaction_code ( standard reaction code )
		*/

		this.checkIn = function (authInfo, callbackFunction) {

			this.resetAuthInfo(authInfo);

			if (callbackFunction && _.isFunction(callbackFunction)) {

				callbackFunction.call(this, authInfo);

			}

		};

		/**
		  * Reset authentication information
		  *
		  * @param function callbackFunction 
		  *
		  * @return void
		  *---------------------------------------------------------------- */

		this.refresh = function (callbackFunction) {

			var authInfo = window.__appImmutables.auth_info;

			this.resetAuthInfo(authInfo);

			if (callbackFunction && _.isFunction(callbackFunction)) {

				callbackFunction.call(this, authInfo);

			}

		};

	    /**
		  * flush authentication information
		  *
		  * @param object authInfo 
		  * @param function callbackFunction 
		  *
		  * @return void
		  *---------------------------------------------------------------- */

		this.checkOut = function (authInfo, callbackFunction) {

			var authInfo = (authInfo && _.isObject(authInfo)) ? authInfo : {};

			this.resetAuthInfo(authInfo);

			if (callbackFunction && _.isFunction(callbackFunction)) {

				callbackFunction.call(this);
			}

		};

	};

})(); ;
(function () {
  'use strict';
  /**
    * Common Services Modules Version - 0.5.x
    *
    * __DataTable     : 0.5.2 - 23 APR 2015
    *
    *-------------------------------------------------------- */

  /**
   * DataTable Utilities & Services.
   *-------------------------------------------------------- */

  //Datatables Defaults
  $.extend($.fn.dataTable.defaults, {
    "serverSide": true,
    "iCookieDuration": 60,
    "paging": true,
    "processing": true,
    "responsive": true,
    "destroy": true,
    "retrieve": true,
    "lengthChange": false,
    "language": {
      "emptyTable": "There are no records to display."
    },
    searching: false,
    "ajax": {
      // any additional data to send
      "data": function (additionalData) {
        additionalData.page = (additionalData.start / additionalData.length) + 1;
      }/*,
              "dataSrc": function ( responseJson ) {
                 console.log(responseJson);
                if( responseJson.userAuth && (!responseJson.userAuth.isAuthenticated)) {

                  // set user auth information
                  AuthService.setUserAuthenticated(responseJson.userAuth);
                  
                  $state.go('login');

                  return false;
                }

                return responseJson;
              }*/
    }
  });


  /**
     * DataTables Services
     *
     * @inject $rootScope
     * @inject $compile
     * @inject __Utils     
     *     
     *-------------------------------------------------------- */
  angular.module("lw.data.datatable", []).
    service("__DataTable", [
      "$rootScope", "$compile", "__Utils", '__appCommonService', __DataTable
    ]);

  function __DataTable($rootScope, $compile, __Utils, __appCommonService) {

    /**
     * DataTable Custom Configuration generation based on provided data
     *
     * @param object {options} - Object  
     *                         url          (required)
     *                         scope        (required)
     *                         columnsData  (required)
     *                         dtOptions    (optional)
     *     
     * @return array
     *-------------------------------------------------------- */

    this.dtConfig = function (options) {

      var dtOptionsCollection = {
        "ajax": {
          "url": options.url
        },
        "columns": [],
        "createdRow": function (nRow) {
          $compile(nRow)(options.scope);
        }
      };

      if (options.dtOptions) {
        $.extend(dtOptionsCollection, options.dtOptions);
      }

      if (__appCommonService && __appCommonService.hasOwnProperty('appDataTableAjaxConfig')) {

        var appDataTableAjaxConfig = __appCommonService.appDataTableAjaxConfig();

        if (appDataTableAjaxConfig) {
          $.extend(dtOptionsCollection.ajax, appDataTableAjaxConfig);
        }

      }

      dtOptionsCollection.columns = _.map(options.columnsData, function (dtColumnData) {
        return {
          "data": dtColumnData.name ? dtColumnData.name : null,
          "orderable": dtColumnData.orderable ? true : false,
          "render": function (subject, data, obj, settings) {

            if (!dtColumnData.name) {
              obj = subject;
            } else {
              obj.dtSubject = dtColumnData.name;
            }
            // if template given
            if (dtColumnData.template) {
              // compile data using underscore template
              return __Utils.template(
                dtColumnData.template, obj
              );
            } else {
              return obj[dtColumnData.name];
            }

          }

        };
      });

      return dtOptionsCollection;
    };


    // a container to store temp datatables references while using tabs
    this.tabDtReferences = {};

    /**
    * DataTable intialization for Tab based tables
    *
    * @param string {tableID} - html table selector
    * @param object {options} - additional configurations items may need (eg. options.config, options.selectedTab)
    *     
    * @return DataTable
    *-------------------------------------------------------- */

    this.tabDataTable = function (tableID, options) {

      var dTable = $(tableID + options.selectedTab).DataTable(this.dtConfig(options.config)),
        tabPropertyTitle = "tab_" + options.selectedTab;

      // Check if user logged in unless redirect to the login window
      // Note:  be sure you have injected AuthService & $state
      /*dTable.on( 'xhr', function () {
        var responseJson = dTable.ajax.json();
        if( responseJson && responseJson.userAuth && (!responseJson.userAuth.isAuthenticated)) {
          AuthService.setUserAuthenticated(responseJson.userAuth);
          $state.go('login');
        }
      });*/

      if ((options.selectedTab !== undefined)) {
        if (this.tabDtReferences.hasOwnProperty(tabPropertyTitle)) {
          dTable.ajax.reload();
        } else {
          this.tabDtReferences[tabPropertyTitle] = options.selectedTab;
        }

      }

      return dTable;
    };

    this.dtSelfUpdate = function () {
      this.dataTableInstance.ajax.reload(null, false);
    };

    this.dataTable = function (tableID, options) {

      var dTable = $(tableID).DataTable(this.dtConfig(options.config));

      // Check if user logged in unless redirect to the login window
      // Note:  be sure you have injected AuthService & $state
      /*dTable.on( 'xhr', function () {
        var responseJson = dTable.ajax.json();
        if( responseJson && responseJson.userAuth && (!responseJson.userAuth.isAuthenticated)) {
          AuthService.setUserAuthenticated(responseJson.userAuth);
          $state.go('login');
        }
      });*/

      dTable.ajax.reload();
      this.dataTableInstance = dTable;
      return dTable;
    };

  };

})();
;
(function () {
  'use strict';
  /**
    * Common Services Modules Version - 0.5.x
    *
    * __DataStore 0.5.64 - 17 JUL 2020
    *
    * Includes support for datatable
    *
    * Internal Dependencies:
    *
    * __Utils
    *
    * External Dependencies:
    * 
    * AngularJS     1.3.0   +     - http://angularjs.org
    * jQuery        1.11.0  +     - http://jquery.com
    * lodash        3.9.3   +     - http://lodash.com
    * datatables    1.10.7  +     - http://datatables.net
    *
    *-------------------------------------------------------- */

  angular.module("lw.data.datastore", []).
    service('__DataStore', [
      '$http', '$compile', '$window', '__Utils', '__Security', '$rootScope', '$q', __DataStore
    ]).
    service('__appCommonService', [__appCommonService])
    // 17 MAR 2020
    // with help of https://stackoverflow.com/questions/16797209/how-can-i-extend-q-promise-in-angularjs-with-a-success-and-error
    .config(['$provide', function ($provide) {
      $provide.decorator('$q', ['$delegate', function ($delegate) {
        var defer = $delegate.defer;
        $delegate.defer = function () {
          var deferred = defer();
          deferred.promise.success = function (fn) {
            deferred.promise.then(function (response) {
              fn(response);
            });
            return deferred.promise;
          };
          deferred.promise.error = function (fn) {
            deferred.promise.then(null, function (response) {
              fn(response);
            });
            return deferred.promise;
          };
          return deferred;
        };
        return $delegate;
      }]);
    }]);

  function __DataStore($http, $compile, $window, __Utils, __Security, $rootScope, $q) {

    $window.__dataStorage = {
      fetch_request_counts: 1
    };
    __dataStorage.data = {};

    var requestURL = '',
      dataItemID = '',
      myFreshData = {};

    this.datatableCollection = {};

    /**
    * Reset Data Storage
    *
    * @return void
    *-------------------------------------------------------- */

    this.reset = function () {

      __dataStorage.data = {};

      __Utils.log("__DataStore:: Reset done");

    };

    /**
    * DataStore Post method
    *
    * @param requestURL {string} - url
    * @param dataObj  {object} - post data
    *     
    * @return datatable instance
    *-------------------------------------------------------- */

    this.post = function (requestURL, dataObj, options) {


      // Execution Ends
      __Utils.time("__DataStore.post");

      var dataStoreInstance = this;

      if (!requestURL) {
        __Utils.throwError("__DataStore:: URL is missing");
      }

      // generate url if its not
      if (_.isObject(requestURL) || !_.includes(requestURL, '://')) {

        requestURL = __Utils.apiURL(requestURL);

      }

      $rootScope.$broadcast('lw.datastore.event.post.started');

      __Utils.log('__DataStore:: post URL: ' + requestURL);

      if (dataObj && !_.isEmpty(dataObj)) {

        var newDataObj = {};

        if (options && options.secured == true) {

          _.forEach(dataObj, function (value, key) {
            if ((!options.unsecuredFields ||
              (_.includes(options.unsecuredFields, key) === false))
              && _.isArray(value) !== true
              && _.isObject(value) !== true) {

              if (value || value == false) {
                if (_.isBoolean(value) || _.isNumber(value)) {
                  value = String(value);
                }

                var securedValue = __Security.rsaEncrypt(value);
                // if cannot be encrypt may long a long string and needs to be concat.
                if (securedValue == false) {

                  var splitedValues = value.match(/.{1,30}/g),
                    splitedValueString = '';

                  for (var i = 0; i < splitedValues.length; i++) {

                    var securedSplitedValue = __Security.rsaEncrypt(splitedValues[i]);

                    if (securedSplitedValue == false) {

                      __Utils.throwError("Encryption Failed for { " + key + " } VALUE due to length");

                      splitedValueString = false;
                      break;

                    } else {

                      splitedValueString = splitedValueString + securedSplitedValue + '__==__';
                    }

                  }

                  securedValue = splitedValueString;
                }

                var securedKey = __Security.rsaEncrypt(key);

                if (securedKey == false) {
                  __Utils.throwError("Encryption Failed for { " + securedKey + " } KEY due to length");
                }

                newDataObj[securedKey] = securedValue;
              }
            } else {
              newDataObj[key] = value;
            }
          });

        } else {
          newDataObj = dataObj;
        }

      }

      var httpRequest = $http.post(requestURL, newDataObj);

      this.success = function (pseudoCallback) {

        httpRequest.then(function onSuccess(response) {
          $rootScope.$broadcast('lw.datastore.event.post.finished');

          var processedData = __Security.processSecuredData(response.data);
          if (processedData !== false) {
            var responseData = processedData;
          } else {
            var responseData = response.data;
          }

          __Utils.log("__DataStore:: Post request processed");

          dataStoreInstance.reset();

          pseudoCallback.call(this, responseData);

          // Execution Ends
          __Utils.timeEnd("__DataStore.post");

          // open email debug view if available
          if (responseData && responseData.__emailDebugView) {
            __Utils.openEmailDebugView(responseData.__emailDebugView);
          }

          // check if __dd 
          if (responseData && responseData.__dd) {
            __Utils.jsdd(responseData);
          }

        }, function onError(errorResponse) {

          $rootScope.$broadcast('lw.datastore.event.post.finished');
          $rootScope.$broadcast('lw.datastore.event.post.error');

          // Show the serverside errors in New Tab
          // commented as error already catches below
          /* __Utils.displayInTabWindow(_.isObject(errorResponse.data) ? errorResponse : errorResponse.data);

           __Utils.error( "__DataStore:: Ooops... Something went wrong" );

         // Execution Ends
         __Utils.timeEnd("__DataStore.post");*/

        });

        return this;

      };

      this.error = function (pseudoCallback) {

        httpRequest.catch(function onError(errorResponse) {

          var processedData = __Security.processSecuredData(errorResponse.data);

          $rootScope.$broadcast('lw.datastore.event.post.finished');
          $rootScope.$broadcast('lw.datastore.event.post.error');

          pseudoCallback.call(this, (processedData !== false) ? processedData : errorResponse.data);

          // Show the serverside errors in New Tab
          __Utils.displayInTabWindow(_.isObject(errorResponse.data) ? errorResponse : errorResponse.data);

          __Utils.error("__DataStore:: Ooops... Something went wrong");

          // Execution Ends
          __Utils.timeEnd("__DataStore.post");

        });

        return this;

      };

      return this;

    };


    /**
    * DataStore data fetch functionality
    *
    * @param string {urlID} - URL
    * @param object {options} - additional configurations items may need (eg. options.params, options.selectedTab)
    *       {
    *         params:{} // object containg addtional parameters to you want to send to the server.
    *         persist: 2 // data expiry in seconds
    *         fresh: true // need fresh data or not
    *       }
    *     
    * @return DataTable
    *-------------------------------------------------------- */
    this.fetch = function (urlID, options) {

      // Start timing now
      __Utils.time("__DataStore.fetch");

      // generate url if its not
      if (_.isObject(urlID) || !_.includes(urlID, '://')) {

        urlID = __Utils.apiURL(urlID);

      }

      $rootScope.$broadcast('lw.datastore.event.fetch.started');

      var date = new Date(),
        dateTime = date.getTime();

      // Remove expired items from DataStore
      _.each(__dataStorage.data, function (dtStoreItem, dtStoreItemKey) {

        if (dtStoreItem.data && (dtStoreItem.data.persist !== true)
          && (dateTime >= dtStoreItem.data.persist)) {

          __Utils.log("__DataStore:: Expired DataItem " + dtStoreItem.data.response_token + " discarded");

          delete __dataStorage.data[dtStoreItemKey];

        }
      });

      if (!options || !_.isObject(options)) {
        options = {};
      }

      var requestURL = urlID;

      if (options.params) {

        requestURL = urlID + '?' + $.param(options.params);

        if (_.has(options.params, 'draw')) {

          delete options.params['draw'];

        }

        urlID = urlID + '?' + $.param(options.params);

      }

      var dataItemID = __Utils.convertToSlug(urlID);

      // Check if data available for this ID
      if (_.has(__dataStorage.data, dataItemID) && __dataStorage.data[dataItemID]['data']
        && (!options.fresh || !options.persist)) {

        $rootScope.$broadcast('lw.datastore.event.fetch.finished');

        var deferred = $q.defer();

        this.dataFromCache = _.cloneDeep(__dataStorage.data[dataItemID]['data']);
        this.dataFromCache['cache'] = true;

        deferred.resolve(this.dataFromCache);

        __Utils.log("__DataStore:: Cached DataItem " + this.dataFromCache.response_token + ' - ' + requestURL);

        delete this.dataFromCache;

        // Execution Ends
        __Utils.timeEnd("__DataStore.fetch");

        return deferred.promise;

      } else { // Request for fresh fresh data from server

        var date = new Date(),
          dateTime = date.getTime(),
          resetOn = Number(dateTime + 45000); // default persist data for 45 seconds

        if (__globals.dataStore && _.has(__globals.dataStore, 'persist')) {

          var globalDataStorePersisit = __globals.dataStore.persist;

          if (_.isNumber(globalDataStorePersisit) && (globalDataStorePersisit > 0)) {

            resetOn = Number(dateTime + (globalDataStorePersisit * 1000));

          } else if (_.isBoolean(globalDataStorePersisit)) {

            resetOn = globalDataStorePersisit;

          }
        }

        // configure persist duration
        if (_.has(options, 'persist')) {

          var dsOptionPersisit = options.persist;

          if (_.isNumber(dsOptionPersisit) && (dsOptionPersisit > 0)) {

            resetOn = Number(dateTime + (dsOptionPersisit * 1000));

          } else if (_.isBoolean(dsOptionPersisit)) {

            resetOn = dsOptionPersisit;

          }
        }

        // check if dataitem has pending request
        if (__dataStorage.data[dataItemID] && __dataStorage.data[dataItemID].request_status === 'pending') {

          // Execution Ends
          __Utils.timeEnd("__DataStore.fetch");

          __Utils.warn("__DataStore:: Multiple simultaneous requests initiated!!");
          // removed throwing error for  Multiple simultaneous requests on 13 APR 2017
          // throw new Error( '__DataStore:: Multiple simultaneous requests' );
        }

        __dataStorage.data[dataItemID] = {
          'request_token': __dataStorage.fetch_request_counts,
          'request_status': 'pending'
        }

        requestURL = requestURL + (_.includes(requestURL, '?') ? '&' : '?') + "fresh=" + __dataStorage.fetch_request_counts;

        // update request count
        __dataStorage.fetch_request_counts++;

        var httpRequest = $http.get(requestURL);

        this.success = function (pseudoCallback) {

          httpRequest.then(function onSuccess(mainResponse) {

            var response = mainResponse.data;

            var processedData = __Security.processSecuredData(response);
            if (processedData !== false) {
              response = processedData;
            }

            pseudoCallback.call(this, response);

            // open email debug view if available
            if (response && response.__emailDebugView) {
              __Utils.openEmailDebugView(response.__emailDebugView);
            }

            // check if __dd
            if (response && response.__dd) {

              if (response.__dd === '__dd' || response.__pr) {

                options.fresh = true;
                // removed data item as request is force to be freshed
                delete __dataStorage.data[dataItemID];
              }

              __Utils.jsdd(response);
            }

            if (!options.fresh && resetOn && (__dataStorage.data[dataItemID] && (__dataStorage.data[dataItemID].request_token == response.response_token))) {

              // store response data
              __dataStorage.data[dataItemID]['data'] = _.cloneDeep(response);

              // store metadata
              __dataStorage.data[dataItemID]['data']['metadata'] = {
                'receivedOn': dateTime
              };

              // store dataitem expiry
              __dataStorage.data[dataItemID]['data']['persist'] = resetOn;

            }

            if (__dataStorage.data[dataItemID]) {
              // mark request as completed
              __dataStorage.data[dataItemID]['request_status'] = 'completed';
            }

            __Utils.log("__DataStore:: Fresh DataItem " + response.response_token + ' - ' + requestURL);

            // fire events
            $rootScope.$broadcast('lw.datastore.event.fetch.finished');

            // Execution Ends
            __Utils.timeEnd("__DataStore.fetch");

          }, function onError(errorResponse) {

            var processedData = __Security.processSecuredData(errorResponse.data);
            if (processedData !== false) {
              errorResponse.data = processedData;
            }

            // Show the serverside errors in New Tab
            __Utils.displayInTabWindow(_.isObject(errorResponse.data) ? errorResponse : errorResponse.data);

            __Utils.error("__DataStore:: Request for DataItem discarded due to the ServerSide error");

            // remove dataitem entry from storage
            delete __dataStorage.data[dataItemID];

            __Utils.error("__DataStore:: Ooops... Something went wrong");

            // fire events
            $rootScope.$broadcast('lw.datastore.event.fetch.finished');
            $rootScope.$broadcast('lw.datastore.event.fetch.error');

            // Execution Ends
            __Utils.timeEnd("__DataStore.fetch");

          });

          return this;
        };

        this.error = function (pseudoCallback) {

          httpRequest.catch(function onError(errorResponse) {

            var processedData = __Security.processSecuredData(errorResponse.data);
            if (processedData !== false) {
              errorResponse.data = processedData;
            }

            pseudoCallback.call(this, errorResponse.data);

            // Show the serverside errors in New Tab
            __Utils.displayInTabWindow(errorResponse);

            __Utils.error("__DataStore:: Request for DataItem discarded due to the ServerSide error");

            // remove dataitem entry from storage
            delete __dataStorage.data[dataItemID];

            __Utils.error("__DataStore:: Ooops... Something went wrong");

            // fire events
            $rootScope.$broadcast('lw.datastore.event.fetch.finished');
            $rootScope.$broadcast('lw.datastore.event.fetch.error');

            // Execution Ends
            __Utils.timeEnd("__DataStore.fetch");

          });

          return this;
        }

        this.then = function (pseudoCallback) {

          httpRequest.then(function onSuccess(mainResponse) {

            var response = mainResponse.data;

            var processedData = __Security.processSecuredData(response);
            if (processedData !== false) {
              response = processedData;
            }

            pseudoCallback.call(this, response);

            // open email debug view if available
            if (response && response.__emailDebugView) {
              __Utils.openEmailDebugView(response.__emailDebugView);
            }

            // check if __dd
            if (response && response.__dd) {

              if (response.__dd === '__dd' || response.__pr) {

                options.fresh = true;
                // removed data item as request is force to be freshed
                delete __dataStorage.data[dataItemID];
              }

              __Utils.jsdd(response);
            }

            if (!options.fresh && resetOn && (__dataStorage.data[dataItemID] && (__dataStorage.data[dataItemID].request_token == response.response_token))) {

              // store response data
              __dataStorage.data[dataItemID]['data'] = _.cloneDeep(response);

              // store metadata
              __dataStorage.data[dataItemID]['data']['metadata'] = {
                'receivedOn': dateTime
              };

              // store dataitem expiry
              __dataStorage.data[dataItemID]['data']['persist'] = resetOn;

            }

            if (__dataStorage.data[dataItemID]) {
              // mark request as completed
              __dataStorage.data[dataItemID]['request_status'] = 'completed';
            }

            __Utils.log("__DataStore:: Fresh DataItem " + response.response_token + ' - ' + requestURL);

            // fire events
            $rootScope.$broadcast('lw.datastore.event.fetch.finished');

            // Execution Ends
            __Utils.timeEnd("__DataStore.fetch");

          }).catch(function onError(errorResponse) {

            var processedData = __Security.processSecuredData(errorResponse.data);
            if (processedData !== false) {
              errorResponse.data = processedData;
            }

            // Show the serverside errors in New Tab
            __Utils.displayInTabWindow(_.isObject(errorResponse.data) ? errorResponse : errorResponse.data);

            __Utils.error("__DataStore:: Request for DataItem discarded due to the ServerSide error");

            // remove dataitem entry from storage
            delete __dataStorage.data[dataItemID];

            __Utils.error("__DataStore:: Ooops... Something went wrong");

            // fire events
            $rootScope.$broadcast('lw.datastore.event.fetch.finished');
            $rootScope.$broadcast('lw.datastore.event.fetch.error');

            // Execution Ends
            __Utils.timeEnd("__DataStore.fetch");

          });

          return this;
        };

        return this;

      }

    };

    /**
    * DataTable Custom Configuration generation based on provided data
    *
    * @param object {options} - Object  
    *                         url          (required)
    *                         scope        (required)
    *                         columnsData  (required)
    *                         dtOptions    (optional)
    *     
    * @return array
    *-------------------------------------------------------- */

    this.dtConfig = function (options, dsOptions) {

      var dataStoreInstance = this;

      if (!dsOptions || !_.isObject(dsOptions)) {

        dsOptions = {};

      }

      var dtOptionsCollection = {

        "ajax": function (data, callback, settings) {

          // for laravel 5 paginate
          data.page = (data.start / data.length) + 1;

          var drawID = data.draw ? data.draw : false,
            optionsSendToFetch = {
              params: data,
              fresh: dsOptions.fresh ? dsOptions.fresh : false
            };

          if (_.has(dsOptions, 'persist')) {
            optionsSendToFetch['persist'] = dsOptions.persist;
          }

          dataStoreInstance.fetch(options.url, optionsSendToFetch).then(function (response) {

            // open email debug view if available
            if (response && response.__emailDebugView) {
              __Utils.openEmailDebugView(response.__emailDebugView);
            }

            // check if __dd is performed
            if (response.data && response.data.__dd) {
              __Utils.jsdd(response.data);
            }

            response.draw = drawID;

            // callback for datatable after data fetched
            if (options.callbackFunction && _.isFunction(options.callbackFunction)) {
              options.callbackFunction.call(this, response);
            }

            callback(response);
          });
        },
        "drawCallback": function (settings) {
          var api = this.api(),
            $thisDataTable = api.table();
            // Add class to the datatable container if the table is empty
          if (!$thisDataTable.rows().count()) {
            $($thisDataTable.container()).addClass('lw-empty-datatable')
          }

          if (_.has($thisDataTable.columns, 'adjust') && _.has($thisDataTable.responsive, 'recalc')) {
            // responsive fix for datatable for cached datastore item 
            _.delay(function () {
              $thisDataTable.columns.adjust().responsive.recalc()
            }, 180);
          }
        },
        "columns": [],
        "createdRow": function (nRow) {

          $compile(nRow)(options.scope);

        },
        // added on 19 MAY 2016 - 0.4.0
        "responsive": {
          "details": {
            "renderer": function (api, rowIdx, columns) {
              var data = _.map(columns, function (col, i) {
                return col.hidden ?
                  '<li data-dtr-index="' + col.columnIndex + '" data-dt-row="' + col.rowIndex + '" data-dt-column="' + col.columnIndex + '">' +
                  '<span class="dtr-title">' +
                  col.title +
                  '</span> ' +
                  '<span class="dtr-data">' +
                  col.data +
                  '</span>' +
                  '</li>' :
                  '';
              }).join('');

              return data ? $('<ul data-dtr-index="' + rowIdx + '"/>').append($compile(data)(options.scope)) : false;
            }
          }
        }
      };

      if (options.dtOptions) {

        _.assign(dtOptionsCollection, options.dtOptions);

      }

      dtOptionsCollection.columns = _.map(options.columnsData, function (dtColumnData) {

        return {

          "data": dtColumnData.name ? dtColumnData.name : null,
          "orderable": dtColumnData.orderable ? true : false,
          "render": function (subject, data, obj, settings) {

            if (!dtColumnData.name) {

              obj = subject;

            } else {

              obj.dtSubject = dtColumnData.name;

            }
            // if template given
            if (dtColumnData.template) {

              // compile data using underscore template
              return __Utils.template(dtColumnData.template, obj);

            } else {

              return obj[dtColumnData.name];
            }

          }

        };

      });

      return dtOptionsCollection;

    };

    /**
    * Initilize DataTable
    *
    * @param tableID {string} - table id
    * @param dtOptions {object} - datatable options
    * @param dsOptions {object} - datastore options
    *     
    * @return datatable instance
    *-------------------------------------------------------- */

    this.dataTable = function (tableID, dtOptions, dsOptions, callbackFunction) {

      if (callbackFunction) {
        dtOptions.callbackFunction = callbackFunction;
      }

      return $(tableID).DataTable(this.dtConfig(dtOptions, dsOptions));

    };

    /**
    * Ajax Reload DataTable
    *
    * @param dataTableInstance {object} - datatable instance
    *     
    * @return datatable instance
    *-------------------------------------------------------- */

    this.reloadDT = function (dataTableInstance) {

      dataTableInstance.ajax.reload(null, false);

      __Utils.log("__DataStore:: DataTable reloaded");

    };

  };

  function __appCommonService() {

    this.appDataTableAjaxConfig = function () {
    };

  };

})();
;
(function () {
	'use strict';
	/**
	 * __Form Service - 0.5.21 - 20 FEB 2019
	 * 
	 * Form Setup & Validation Service.
	 *
	 * @return void
	 *-------------------------------------------------------- */

	angular.module("lw.form.main", []).
		config(["$httpProvider", function ($httpProvider) {

			// to detect ajax request at server side;
			$httpProvider.defaults.headers.common["X-Requested-With"] = "XMLHttpRequest";

		}]).
		service("__Form", [
			'__Utils', '__Security', '__DataStore', '$rootScope', __Form
		]).
		directive("lwFormField", [
			'__Form', lwFormField
		]).
		directive("lwFormMessages", [
			'__Form', lwFormMessages
		]);

	function __Form(__Utils, __Security, __DataStore, $rootScope) {

		this.messages = __globals.messages.validation;
		this.NOTHING_CHANGED_MSG = 14; //this.messages.reactions['14']; // No changes
		this.INVALID_FORM_MSG = 4;  //this.messages.reactions['4']; // Client Side Validation Error
		this.REQUEST_SUCCESS_MSG = 1;  //this.messages.reactions['1']; // Success 
		this.YES_SECURITY_ID = __Security.getSecurityID();
		this.OLD_FORMDATA_ID_PREFIX = 'OFD_';

		var _form = this;

		// get validation message
		this.getMsg = function (key, labelName) {

			return this.messages[key].replace(":attribute", labelName.toLowerCase());

		};

		// get size related validation messages
		this.getSizeMsg = function (key, labelName, value) {

			var msg = this.messages[key]['string'];
			msg = msg.replace(":attribute", labelName.toLowerCase());

			// key is min    
			if (key === "min") {

				return msg.replace(":min", value);

			} else {

				return msg.replace(":max", value);

			}

		};

		// get number field size related validation message
		this.getNumberMsg = function (key, labelName, value) {

			var msg = this.messages[key]['numeric'];
			msg = msg.replace(":attribute", labelName.toLowerCase());

			// key is min    
			if (key === "min") {

				return msg.replace(":min", value);

			} else {

				return msg.replace(":max", value);

			}

		};

	    /**
		 * Update Model 
		 * 
		 * @param object {scope} 			- current controller scope
		 * @param string {ngFormName} 		- form name
		 * @param string {ngFormModelName} 	- model name
		 *
		 * return Object
		 *-------------------------------------------------------- */

		this.setup = function (scope, ngFormName, ngFormModelName, options) {

			scope.ngFormName = ngFormName;
			scope.ngFormModelName = ngFormModelName;
			scope[ngFormModelName] = {};
			scope[ngFormName + '_modelUpdateWatcher'] = true;

			if (options) {

				// option to encrypt form or not
				if (options.secured === true) {
					scope[ngFormName + this.YES_SECURITY_ID] = true;
					// check & recored if some form fields are exempted from encryption
					if (_.has(options, 'unsecuredFields') && (_.isArray(options.unsecuredFields) === true)) {
						scope[ngFormName + 'UNSECURED_FORM_FIELDS'] = options.unsecuredFields;
					}
				}

				// option to disable modelUpdate check for form submission
				if (options.modelUpdateWatcher === false) {
					scope[ngFormName + '_modelUpdateWatcher'] = false;
				}

			}

			return scope;
		};

	    /**
		 * Update Model 
		 * 
		 * @param object {scope} 	- current controller scope
		 * @param object {dataObj} 	- data object
		 *
		 * return Object
		 *-------------------------------------------------------- */

		this.updateModel = function (scope, dataObj) {

			scope[scope.ngFormModelName] = dataObj;
			scope[this.OLD_FORMDATA_ID_PREFIX + scope.ngFormModelName]
				= __Utils.stringifyObjectValues(dataObj);
			return scope;
		};

	    /**
		 * The process methods validate form and sent -
		 * post request to server and response back.
		 * If form invalidated, give the notification.
		 * 
		 * @param object {scope} - current controller scope
		 * @param string {actionURL} - action url
		 *
		 * return void
		 *-------------------------------------------------------- */

		this.process = function (actionURL, scope) {

			// Execution Starts
			__Utils.time("__Form.process");

			var formModelData = {},
				modelUpdateWatcher = scope[scope.ngFormName + '_modelUpdateWatcher'],
				postOptions = [];

			// check if this form needs encryption if yes encrypt values
			if (scope[scope.ngFormName + this.YES_SECURITY_ID]) {

				postOptions.unsecuredFields = scope[scope.ngFormName + 'UNSECURED_FORM_FIELDS'];
				postOptions.secured = true;
				// NOTE: Encryption logic moved to __DataStore.post - 06 JAN 2017

			}

			formModelData = scope[scope.ngFormModelName];

			var formScope = scope[scope.ngFormName],
				isModelUpdated = true,
				$currentFormElement = angular.element('form[name="' + formScope.$name + '"]');

			if ($currentFormElement.hasClass('lw-ng-form-processing')) {

				__Utils.warn("__Form:: Already in process");

				return {
					success: function (callbackFunction) {

						callbackFunction.call(this, {
							reaction: 14, // treat as a clientside validation error
							data: {
								message: "Already in process"
							}
						});

						// Execution Ends
						__Utils.timeEnd("__Form.process");

					},
					error: function (callbackFunction) {

						callbackFunction.call(this, false);

						// Execution Ends
						__Utils.timeEnd("__Form.process");
					}
				};
			}

			$currentFormElement.addClass('lw-ng-form-processing');

			if (modelUpdateWatcher === true) {
				// check if model updated or not
				isModelUpdated = _.isEqual(
					__Utils.stringifyObjectValues(scope[scope.ngFormModelName]),
					scope[this.OLD_FORMDATA_ID_PREFIX + scope.ngFormModelName]
				) ? false : true;

			}

			scope.errorMessage = '';
			scope.successMessage = '';

			if (formScope.$valid === true && isModelUpdated === true) {

				__Utils.info("__Form:: ClientSide validation passed");

				var _validation = this;

				$rootScope.$broadcast('lw.form.event.process.started');

				return __DataStore.post(actionURL, formModelData, postOptions)
					.success(function (responseData) {

						scope.successMessage = __globals.getReactionMessage(
							_validation.REQUEST_SUCCESS_MSG);

						var requestData = responseData.data;

						// if request success
						if (responseData.reaction === 1) {

							__Utils.info("__Form:: ServerSide validation/check passed");
							scope.errorMessage = "";

							if (requestData.message) {
								scope.successMessage = requestData.message;
							}

							// update form validation messages
							_validation.update(formScope, null);

							// update old model info
							_validation.updateModel(scope, scope[scope.ngFormModelName]);

							$currentFormElement.removeClass('lw-ng-form-processing');

						} else {

							__Utils.warn("__Form:: Serverside validation/check failed");
							// if request data wrong
							scope.errorMessage = requestData.message;
							scope.successMessage = '';

							// update form validation messages
							_validation.update(formScope, requestData.validation);

							_.defer(function () {
								$currentFormElement.removeClass('lw-ng-form-processing');

								// find the first invalid element
								var firstInvalid = angular.element('form').find('.ng-invalid')[0];
								// if we find one, set focus
								if (firstInvalid) {
									if (angular.element(firstInvalid).hasClass('selectized')) {
										$(firstInvalid).parent().find('input').focus();
									} else {
										firstInvalid.focus();
									}
								}
							})

						}

						__Utils.info("__Form:: Request processed");

						// fire events
						$rootScope.$broadcast('lw.form.event.process.finished', responseData);
						$rootScope.$broadcast('lw.form.event.process.success', responseData);

						// Execution Ends
						__Utils.timeEnd("__Form.process");

					}).error(function () {

						__Utils.error("__Form:: Serverside error");

						$currentFormElement.removeClass('lw-ng-form-processing');
						// fire events
						$rootScope.$broadcast('lw.form.event.process.finished', false);
						$rootScope.$broadcast('lw.form.event.process.error');

						// Execution Ends
						__Utils.timeEnd("__Form.process");
					});

			} else if (formScope.$valid === true && isModelUpdated === false) {

				scope.errorMessage = __globals.getReactionMessage(
					this.NOTHING_CHANGED_MSG);

				__Utils.warn("__Form:: Nothing new to process");

				$currentFormElement.removeClass('lw-ng-form-processing');

				return {
					success: function (callbackFunction) {

						callbackFunction.call(this, {
							reaction: 14, // treat as a clientside validation error
							data: {
								message: scope.errorMessage
							}
						});

						// Execution Ends
						__Utils.timeEnd("__Form.process");
						$currentFormElement.removeClass('lw-ng-form-processing');

					},
					error: function (callbackFunction) {

						callbackFunction.call(this, false);

						// Execution Ends
						__Utils.timeEnd("__Form.process");
						$currentFormElement.removeClass('lw-ng-form-processing');
					}
				};

			} else {

				// if form invalid, set form submitted true for show error message
				scope[scope.ngFormName].submitted = true;

				scope.errorMessage = __globals.getReactionMessage(
					this.INVALID_FORM_MSG);

				return {
					success: function (callbackFunction) {

						$currentFormElement.removeClass('lw-ng-form-processing').addClass('lw-form-has-errors');

						// find the first invalid element
						var firstInvalid = angular.element('form').find('.ng-invalid')[0];
						// if we find one, set focus
						if (firstInvalid) {
							if (angular.element(firstInvalid).hasClass('selectized')) {
								$(firstInvalid).parent().find('input').focus();
							} else {
								firstInvalid.focus();
							}
						}

						__Utils.warn("__Form:: ClientSide validation failed");

						callbackFunction.call(this,
							{
								reaction: 4,
								data: { message: scope.errorMessage }
							});

						// Execution Ends
						__Utils.timeEnd("__Form.process");

					},
					error: function (callbackFunction) {

						$currentFormElement.removeClass('lw-ng-form-processing');

						__Utils.error("__DataStore:: Ooops... Something went wrong");
						callbackFunction.call(this, false);

						// Execution Ends
						__Utils.timeEnd("__Form.process");

					}
				};
			}
		};

	    /**
		 * Alias to __DataStore.fetch
		 * including event broadcast for fetch started 
		 * 
		 * @param string {urlID} - urlID
		 * @param object {options} 
		 *
		 * return object
		 *-------------------------------------------------------- */

		this.fetch = function (urlID, options) {

			$rootScope.$broadcast('lw.form.event.fetch.started');

			return __DataStore.fetch(urlID, options);
		};

	    /**
		 * Update Current Form Validation Messages 
		 * 
		 * @param object {formObj} - current form object
		 * @param object {validationMsgs} - validation messages
		 *
		 * return void
		 *-------------------------------------------------------- */

		this.update = function (formObj, validationMsgs) {

			var fields = [];

			_.each(formObj, function (obj, key) {

				if (key[0] !== "$") {

					fields.push(key);

					if ((obj.hasOwnProperty("$error")) && (obj.$error.hasOwnProperty("server"))) {
						formObj[key].$error = {};
						angular.element('form').find('[name="' + key + '"]').removeClass('ng-invalid');
					}

				}

			});

			if (!$.isEmptyObject(validationMsgs)) {

				_.each(fields, function (key) {

					if (validationMsgs.hasOwnProperty(key)) {
						angular.element('form').find('[name="' + key + '"]').addClass('ng-invalid');
						formObj[key].$error = { 'server': validationMsgs[key][0] };
					}

				});
			}

		};

	};

	/**
	 * lwFormField Directive - 0.1.0 - 12 JUN 2015
	 * 
	 * Form Input Field Directive.
	 *
	 * @inject __Form
	 *
	 * @return void
	 *-------------------------------------------------------- */

	function lwFormField(__Form) {

		return {
			restrict: 'E',
			replace: true,
			transclude: true,
			scope: {
				fieldFor: '@'
			},
			templateUrl: 'lw-form-text.ngtemplate',
			link: function (scope, elem, attrs, ctrls, transclude) {

				// remove ng-transclude for 
				// angular material inputs as per 0.9.5
				if (elem.hasClass('lw-remove-transclude-tag')) {
					elem.find('ng-transclude').children().unwrap();
				}

				// field form data
				var formData = elem.parents('form.lw-ng-form')
					.data('$formController'),

					inputElement = elem.find('.lw-form-field');

				inputElement.prop('id', scope.fieldFor);
				// form field object 
				scope.formField = {};
				scope.formField[scope.fieldFor] = attrs;

				scope.lwFormData = {
					formCtrl: formData
				};

				// get validation message
				scope.getValidationMsg = function (key, labelName) {
					return __Form.getMsg(key, labelName);
				};

				// get size relared validation message
				scope.getSizeValidationMsg = function (key, labelName) {

					var value;
					if (key === "min") {

						value = inputElement.attr('ng-minlength');

					} else {

						value = inputElement.attr('ng-maxlength');

					}

					return __Form.getSizeMsg(key, labelName, value);

				};

				// get number field size related validation messages
				scope.getMinMaxValidationMsg = function (key, labelName) {


					if (key === "min") {

						return __Form.getNumberMsg(key, labelName, inputElement.attr('min'));

					} else {

						return __Form.getNumberMsg(key, labelName, inputElement.attr('max'));

					}

				};

			}
		};

	}

    /**
     * lwFormMessages Directive - 0.1.0 - 17 AUG 2015
     * 
     * Show form messages
     *
     * @return void
     *-------------------------------------------------------- */

	function lwFormMessages() {
		return {
			restrict: 'E',
			replace: true,
			transclude: true,
			scope: true,
			templateUrl: 'lw-form-messages.ngtemplate',
			link: function (scope, elem, attrs, transclude) {
				scope.ctrlName = attrs.ctrlName;
			}

		};
	};

})(); ;
/*
Updated by Vinod on 11 MAY 2017
PLEASE NOTE: THIS FILE CONSIST OF THE FOLLOWING FILES

jsbn.js - old one
prng4.js
rng.js
*/
/*
------------------------------------------------------------------------------------------------------------------------
jsbn.js - old one
------------------------------------------------------------------------------------------------------------------------
*/

// Copyright (c) 2005  Tom Wu
// All Rights Reserved.
// See "LICENSE" for details.
// Basic JavaScript BN library - subset useful for RSA encryption.

// Bits per digit
var dbits;

// JavaScript engine analysis
var canary = 0xdeadbeefcafe;
var j_lm = ((canary & 0xffffff) == 0xefcafe);

// (public) Constructor

function BigInteger(a, b, c) {
    if (a != null) if ("number" == typeof a) this.fromNumber(a, b, c);
    else if (b == null && "string" != typeof a) this.fromString(a, 256);
    else this.fromString(a, b);
}

// return new, unset BigInteger

function nbi() {
    return new BigInteger(null);
}

// am: Compute w_j += (x*this_i), propagate carries,
// c is initial carry, returns final carry.
// c < 3*dvalue, x < 2*dvalue, this_i < dvalue
// We need to select the fastest one that works in this environment.
// am1: use a single mult and divide to get the high bits,
// max digit bits should be 26 because
// max internal value = 2*dvalue^2-2*dvalue (< 2^53)

function am1(i, x, w, j, c, n) {
    while (--n >= 0) {
        var v = x * this[i++] + w[j] + c;
        c = Math.floor(v / 0x4000000);
        w[j++] = v & 0x3ffffff;
    }
    return c;
}
// am2 avoids a big mult-and-extract completely.
// Max digit bits should be <= 30 because we do bitwise ops
// on values up to 2*hdvalue^2-hdvalue-1 (< 2^31)

function am2(i, x, w, j, c, n) {
    var xl = x & 0x7fff,
        xh = x >> 15;
    while (--n >= 0) {
        var l = this[i] & 0x7fff;
        var h = this[i++] >> 15;
        var m = xh * l + h * xl;
        l = xl * l + ((m & 0x7fff) << 15) + w[j] + (c & 0x3fffffff);
        c = (l >>> 30) + (m >>> 15) + xh * h + (c >>> 30);
        w[j++] = l & 0x3fffffff;
    }
    return c;
}
// Alternately, set max digit bits to 28 since some
// browsers slow down when dealing with 32-bit numbers.

function am3(i, x, w, j, c, n) {
    var xl = x & 0x3fff,
        xh = x >> 14;
    while (--n >= 0) {
        var l = this[i] & 0x3fff;
        var h = this[i++] >> 14;
        var m = xh * l + h * xl;
        l = xl * l + ((m & 0x3fff) << 14) + w[j] + c;
        c = (l >> 28) + (m >> 14) + xh * h;
        w[j++] = l & 0xfffffff;
    }
    return c;
}
if (j_lm && (navigator.appName == "Microsoft Internet Explorer")) {
    BigInteger.prototype.am = am2;
    dbits = 30;
}
else if (j_lm && (navigator.appName != "Netscape")) {
    BigInteger.prototype.am = am1;
    dbits = 26;
}
else { // Mozilla/Netscape seems to prefer am3
    BigInteger.prototype.am = am3;
    dbits = 28;
}

BigInteger.prototype.DB = dbits;
BigInteger.prototype.DM = ((1 << dbits) - 1);
BigInteger.prototype.DV = (1 << dbits);

var BI_FP = 52;
BigInteger.prototype.FV = Math.pow(2, BI_FP);
BigInteger.prototype.F1 = BI_FP - dbits;
BigInteger.prototype.F2 = 2 * dbits - BI_FP;

// Digit conversions
var BI_RM = "0123456789abcdefghijklmnopqrstuvwxyz";
var BI_RC = new Array();
var rr, vv;
rr = "0".charCodeAt(0);
for (vv = 0; vv <= 9; ++vv) BI_RC[rr++] = vv;
rr = "a".charCodeAt(0);
for (vv = 10; vv < 36; ++vv) BI_RC[rr++] = vv;
rr = "A".charCodeAt(0);
for (vv = 10; vv < 36; ++vv) BI_RC[rr++] = vv;

function int2char(n) {
    return BI_RM.charAt(n);
}

function intAt(s, i) {
    var c = BI_RC[s.charCodeAt(i)];
    return (c == null) ? -1 : c;
}

// (protected) copy this to r

function bnpCopyTo(r) {
    for (var i = this.t - 1; i >= 0; --i) r[i] = this[i];
    r.t = this.t;
    r.s = this.s;
}

// (protected) set from integer value x, -DV <= x < DV

function bnpFromInt(x) {
    this.t = 1;
    this.s = (x < 0) ? -1 : 0;
    if (x > 0) this[0] = x;
    else if (x < -1) this[0] = x + DV;
    else this.t = 0;
}

// return bigint initialized to value

function nbv(i) {
    var r = nbi();
    r.fromInt(i);
    return r;
}

// (protected) set from string and radix

function bnpFromString(s, b) {
    var k;
    if (b == 16) k = 4;
    else if (b == 8) k = 3;
    else if (b == 256) k = 8; // byte array
    else if (b == 2) k = 1;
    else if (b == 32) k = 5;
    else if (b == 4) k = 2;
    else {
        this.fromRadix(s, b);
        return;
    }
    this.t = 0;
    this.s = 0;
    var i = s.length,
        mi = false,
        sh = 0;
    while (--i >= 0) {
        var x = (k == 8) ? s[i] & 0xff : intAt(s, i);
        if (x < 0) {
            if (s.charAt(i) == "-") mi = true;
            continue;
        }
        mi = false;
        if (sh == 0) this[this.t++] = x;
        else if (sh + k > this.DB) {
            this[this.t - 1] |= (x & ((1 << (this.DB - sh)) - 1)) << sh;
            this[this.t++] = (x >> (this.DB - sh));
        }
        else this[this.t - 1] |= x << sh;
        sh += k;
        if (sh >= this.DB) sh -= this.DB;
    }
    if (k == 8 && (s[0] & 0x80) != 0) {
        this.s = -1;
        if (sh > 0) this[this.t - 1] |= ((1 << (this.DB - sh)) - 1) << sh;
    }
    this.clamp();
    if (mi) BigInteger.ZERO.subTo(this, this);
}

// (protected) clamp off excess high words

function bnpClamp() {
    var c = this.s & this.DM;
    while (this.t > 0 && this[this.t - 1] == c) --this.t;
}

// (public) return string representation in given radix

function bnToString(b) {
    if (this.s < 0) return "-" + this.negate().toString(b);
    var k;
    if (b == 16) k = 4;
    else if (b == 8) k = 3;
    else if (b == 2) k = 1;
    else if (b == 32) k = 5;
    else if (b == 64) k = 6;
    else if (b == 4) k = 2;
    else return this.toRadix(b);
    var km = (1 << k) - 1,
        d, m = false,
        r = "",
        i = this.t;
    var p = this.DB - (i * this.DB) % k;
    if (i-- > 0) {
        if (p < this.DB && (d = this[i] >> p) > 0) {
            m = true;
            r = int2char(d);
        }
        while (i >= 0) {
            if (p < k) {
                d = (this[i] & ((1 << p) - 1)) << (k - p);
                d |= this[--i] >> (p += this.DB - k);
            }
            else {
                d = (this[i] >> (p -= k)) & km;
                if (p <= 0) {
                    p += this.DB;
                    --i;
                }
            }
            if (d > 0) m = true;
            if (m) r += int2char(d);
        }
    }
    return m ? r : "0";
}

// (public) -this

function bnNegate() {
    var r = nbi();
    BigInteger.ZERO.subTo(this, r);
    return r;
}

// (public) |this|

function bnAbs() {
    return (this.s < 0) ? this.negate() : this;
}

// (public) return + if this > a, - if this < a, 0 if equal

function bnCompareTo(a) {
    var r = this.s - a.s;
    if (r != 0) return r;
    var i = this.t;
    r = i - a.t;
    if (r != 0) return r;
    while (--i >= 0) if ((r = this[i] - a[i]) != 0) return r;
    return 0;
}

// returns bit length of the integer x

function nbits(x) {
    var r = 1,
        t;
    if ((t = x >>> 16) != 0) {
        x = t;
        r += 16;
    }
    if ((t = x >> 8) != 0) {
        x = t;
        r += 8;
    }
    if ((t = x >> 4) != 0) {
        x = t;
        r += 4;
    }
    if ((t = x >> 2) != 0) {
        x = t;
        r += 2;
    }
    if ((t = x >> 1) != 0) {
        x = t;
        r += 1;
    }
    return r;
}

// (public) return the number of bits in "this"

function bnBitLength() {
    if (this.t <= 0) return 0;
    return this.DB * (this.t - 1) + nbits(this[this.t - 1] ^ (this.s & this.DM));
}

// (protected) r = this << n*DB

function bnpDLShiftTo(n, r) {
    var i;
    for (i = this.t - 1; i >= 0; --i) r[i + n] = this[i];
    for (i = n - 1; i >= 0; --i) r[i] = 0;
    r.t = this.t + n;
    r.s = this.s;
}

// (protected) r = this >> n*DB

function bnpDRShiftTo(n, r) {
    for (var i = n; i < this.t; ++i) r[i - n] = this[i];
    r.t = Math.max(this.t - n, 0);
    r.s = this.s;
}

// (protected) r = this << n

function bnpLShiftTo(n, r) {
    var bs = n % this.DB;
    var cbs = this.DB - bs;
    var bm = (1 << cbs) - 1;
    var ds = Math.floor(n / this.DB),
        c = (this.s << bs) & this.DM,
        i;
    for (i = this.t - 1; i >= 0; --i) {
        r[i + ds + 1] = (this[i] >> cbs) | c;
        c = (this[i] & bm) << bs;
    }
    for (i = ds - 1; i >= 0; --i) r[i] = 0;
    r[ds] = c;
    r.t = this.t + ds + 1;
    r.s = this.s;
    r.clamp();
}

// (protected) r = this >> n

function bnpRShiftTo(n, r) {
    r.s = this.s;
    var ds = Math.floor(n / this.DB);
    if (ds >= this.t) {
        r.t = 0;
        return;
    }
    var bs = n % this.DB;
    var cbs = this.DB - bs;
    var bm = (1 << bs) - 1;
    r[0] = this[ds] >> bs;
    for (var i = ds + 1; i < this.t; ++i) {
        r[i - ds - 1] |= (this[i] & bm) << cbs;
        r[i - ds] = this[i] >> bs;
    }
    if (bs > 0) r[this.t - ds - 1] |= (this.s & bm) << cbs;
    r.t = this.t - ds;
    r.clamp();
}

// (protected) r = this - a

function bnpSubTo(a, r) {
    var i = 0,
        c = 0,
        m = Math.min(a.t, this.t);
    while (i < m) {
        c += this[i] - a[i];
        r[i++] = c & this.DM;
        c >>= this.DB;
    }
    if (a.t < this.t) {
        c -= a.s;
        while (i < this.t) {
            c += this[i];
            r[i++] = c & this.DM;
            c >>= this.DB;
        }
        c += this.s;
    }
    else {
        c += this.s;
        while (i < a.t) {
            c -= a[i];
            r[i++] = c & this.DM;
            c >>= this.DB;
        }
        c -= a.s;
    }
    r.s = (c < 0) ? -1 : 0;
    if (c < -1) r[i++] = this.DV + c;
    else if (c > 0) r[i++] = c;
    r.t = i;
    r.clamp();
}

// (protected) r = this * a, r != this,a (HAC 14.12)
// "this" should be the larger one if appropriate.

function bnpMultiplyTo(a, r) {
    var x = this.abs(),
        y = a.abs();
    var i = x.t;
    r.t = i + y.t;
    while (--i >= 0) r[i] = 0;
    for (i = 0; i < y.t; ++i) r[i + x.t] = x.am(0, y[i], r, i, 0, x.t);
    r.s = 0;
    r.clamp();
    if (this.s != a.s) BigInteger.ZERO.subTo(r, r);
}

// (protected) r = this^2, r != this (HAC 14.16)

function bnpSquareTo(r) {
    var x = this.abs();
    var i = r.t = 2 * x.t;
    while (--i >= 0) r[i] = 0;
    for (i = 0; i < x.t - 1; ++i) {
        var c = x.am(i, x[i], r, 2 * i, 0, 1);
        if ((r[i + x.t] += x.am(i + 1, 2 * x[i], r, 2 * i + 1, c, x.t - i - 1)) >= x.DV) {
            r[i + x.t] -= x.DV;
            r[i + x.t + 1] = 1;
        }
    }
    if (r.t > 0) r[r.t - 1] += x.am(i, x[i], r, 2 * i, 0, 1);
    r.s = 0;
    r.clamp();
}

// (protected) divide this by m, quotient and remainder to q, r (HAC 14.20)
// r != q, this != m.  q or r may be null.

function bnpDivRemTo(m, q, r) {
    var pm = m.abs();
    if (pm.t <= 0) return;
    var pt = this.abs();
    if (pt.t < pm.t) {
        if (q != null) q.fromInt(0);
        if (r != null) this.copyTo(r);
        return;
    }
    if (r == null) r = nbi();
    var y = nbi(),
        ts = this.s,
        ms = m.s;
    var nsh = this.DB - nbits(pm[pm.t - 1]); // normalize modulus
    if (nsh > 0) {
        pm.lShiftTo(nsh, y);
        pt.lShiftTo(nsh, r);
    }
    else {
        pm.copyTo(y);
        pt.copyTo(r);
    }
    var ys = y.t;
    var y0 = y[ys - 1];
    if (y0 == 0) return;
    var yt = y0 * (1 << this.F1) + ((ys > 1) ? y[ys - 2] >> this.F2 : 0);
    var d1 = this.FV / yt,
        d2 = (1 << this.F1) / yt,
        e = 1 << this.F2;
    var i = r.t,
        j = i - ys,
        t = (q == null) ? nbi() : q;
    y.dlShiftTo(j, t);
    if (r.compareTo(t) >= 0) {
        r[r.t++] = 1;
        r.subTo(t, r);
    }
    BigInteger.ONE.dlShiftTo(ys, t);
    t.subTo(y, y); // "negative" y so we can replace sub with am later
    while (y.t < ys) y[y.t++] = 0;
    while (--j >= 0) {
        // Estimate quotient digit
        var qd = (r[--i] == y0) ? this.DM : Math.floor(r[i] * d1 + (r[i - 1] + e) * d2);
        if ((r[i] += y.am(0, qd, r, j, 0, ys)) < qd) { // Try it out
            y.dlShiftTo(j, t);
            r.subTo(t, r);
            while (r[i] < --qd) r.subTo(t, r);
        }
    }
    if (q != null) {
        r.drShiftTo(ys, q);
        if (ts != ms) BigInteger.ZERO.subTo(q, q);
    }
    r.t = ys;
    r.clamp();
    if (nsh > 0) r.rShiftTo(nsh, r); // Denormalize remainder
    if (ts < 0) BigInteger.ZERO.subTo(r, r);
}

// (public) this mod a

function bnMod(a) {
    var r = nbi();
    this.abs().divRemTo(a, null, r);
    if (this.s < 0 && r.compareTo(BigInteger.ZERO) > 0) a.subTo(r, r);
    return r;
}

// Modular reduction using "classic" algorithm

function Classic(m) {
    this.m = m;
}

function cConvert(x) {
    if (x.s < 0 || x.compareTo(this.m) >= 0) return x.mod(this.m);
    else return x;
}

function cRevert(x) {
    return x;
}

function cReduce(x) {
    x.divRemTo(this.m, null, x);
}

function cMulTo(x, y, r) {
    x.multiplyTo(y, r);
    this.reduce(r);
}

function cSqrTo(x, r) {
    x.squareTo(r);
    this.reduce(r);
}

Classic.prototype.convert = cConvert;
Classic.prototype.revert = cRevert;
Classic.prototype.reduce = cReduce;
Classic.prototype.mulTo = cMulTo;
Classic.prototype.sqrTo = cSqrTo;

// (protected) return "-1/this % 2^DB"; useful for Mont. reduction
// justification:
//         xy == 1 (mod m)
//         xy =  1+km
//   xy(2-xy) = (1+km)(1-km)
// x[y(2-xy)] = 1-k^2m^2
// x[y(2-xy)] == 1 (mod m^2)
// if y is 1/x mod m, then y(2-xy) is 1/x mod m^2
// should reduce x and y(2-xy) by m^2 at each step to keep size bounded.
// JS multiply "overflows" differently from C/C++, so care is needed here.

function bnpInvDigit() {
    if (this.t < 1) return 0;
    var x = this[0];
    if ((x & 1) == 0) return 0;
    var y = x & 3; // y == 1/x mod 2^2
    y = (y * (2 - (x & 0xf) * y)) & 0xf; // y == 1/x mod 2^4
    y = (y * (2 - (x & 0xff) * y)) & 0xff; // y == 1/x mod 2^8
    y = (y * (2 - (((x & 0xffff) * y) & 0xffff))) & 0xffff; // y == 1/x mod 2^16
    // last step - calculate inverse mod DV directly;
    // assumes 16 < DB <= 32 and assumes ability to handle 48-bit ints
    y = (y * (2 - x * y % this.DV)) % this.DV; // y == 1/x mod 2^dbits
    // we really want the negative inverse, and -DV < y < DV
    return (y > 0) ? this.DV - y : -y;
}

// Montgomery reduction

function Montgomery(m) {
    this.m = m;
    this.mp = m.invDigit();
    this.mpl = this.mp & 0x7fff;
    this.mph = this.mp >> 15;
    this.um = (1 << (m.DB - 15)) - 1;
    this.mt2 = 2 * m.t;
}

// xR mod m

function montConvert(x) {
    var r = nbi();
    x.abs().dlShiftTo(this.m.t, r);
    r.divRemTo(this.m, null, r);
    if (x.s < 0 && r.compareTo(BigInteger.ZERO) > 0) this.m.subTo(r, r);
    return r;
}

// x/R mod m

function montRevert(x) {
    var r = nbi();
    x.copyTo(r);
    this.reduce(r);
    return r;
}

// x = x/R mod m (HAC 14.32)

function montReduce(x) {
    while (x.t <= this.mt2) // pad x so am has enough room later
        x[x.t++] = 0;
    for (var i = 0; i < this.m.t; ++i) {
        // faster way of calculating u0 = x[i]*mp mod DV
        var j = x[i] & 0x7fff;
        var u0 = (j * this.mpl + (((j * this.mph + (x[i] >> 15) * this.mpl) & this.um) << 15)) & x.DM;
        // use am to combine the multiply-shift-add into one call
        j = i + this.m.t;
        x[j] += this.m.am(0, u0, x, i, 0, this.m.t);
        // propagate carry
        while (x[j] >= x.DV) {
            x[j] -= x.DV;
            x[++j]++;
        }
    }
    x.clamp();
    x.drShiftTo(this.m.t, x);
    if (x.compareTo(this.m) >= 0) x.subTo(this.m, x);
}

// r = "x^2/R mod m"; x != r

function montSqrTo(x, r) {
    x.squareTo(r);
    this.reduce(r);
}

// r = "xy/R mod m"; x,y != r

function montMulTo(x, y, r) {
    x.multiplyTo(y, r);
    this.reduce(r);
}

Montgomery.prototype.convert = montConvert;
Montgomery.prototype.revert = montRevert;
Montgomery.prototype.reduce = montReduce;
Montgomery.prototype.mulTo = montMulTo;
Montgomery.prototype.sqrTo = montSqrTo;

// (protected) true iff this is even

function bnpIsEven() {
    return ((this.t > 0) ? (this[0] & 1) : this.s) == 0;
}

// (protected) this^e, e < 2^32, doing sqr and mul with "r" (HAC 14.79)

function bnpExp(e, z) {
    if (e > 0xffffffff || e < 1) return BigInteger.ONE;
    var r = nbi(),
        r2 = nbi(),
        g = z.convert(this),
        i = nbits(e) - 1;
    g.copyTo(r);
    while (--i >= 0) {
        z.sqrTo(r, r2);
        if ((e & (1 << i)) > 0) z.mulTo(r2, g, r);
        else {
            var t = r;
            r = r2;
            r2 = t;
        }
    }
    return z.revert(r);
}

// (public) this^e % m, 0 <= e < 2^32

function bnModPowInt(e, m) {
    var z;
    if (e < 256 || m.isEven()) z = new Classic(m);
    else z = new Montgomery(m);
    return this.exp(e, z);
}

// protected
BigInteger.prototype.copyTo = bnpCopyTo;
BigInteger.prototype.fromInt = bnpFromInt;
BigInteger.prototype.fromString = bnpFromString;
BigInteger.prototype.clamp = bnpClamp;
BigInteger.prototype.dlShiftTo = bnpDLShiftTo;
BigInteger.prototype.drShiftTo = bnpDRShiftTo;
BigInteger.prototype.lShiftTo = bnpLShiftTo;
BigInteger.prototype.rShiftTo = bnpRShiftTo;
BigInteger.prototype.subTo = bnpSubTo;
BigInteger.prototype.multiplyTo = bnpMultiplyTo;
BigInteger.prototype.squareTo = bnpSquareTo;
BigInteger.prototype.divRemTo = bnpDivRemTo;
BigInteger.prototype.invDigit = bnpInvDigit;
BigInteger.prototype.isEven = bnpIsEven;
BigInteger.prototype.exp = bnpExp;

// public
BigInteger.prototype.toString = bnToString;
BigInteger.prototype.negate = bnNegate;
BigInteger.prototype.abs = bnAbs;
BigInteger.prototype.compareTo = bnCompareTo;
BigInteger.prototype.bitLength = bnBitLength;
BigInteger.prototype.mod = bnMod;
BigInteger.prototype.modPowInt = bnModPowInt;

// "constants"
BigInteger.ZERO = nbv(0);
BigInteger.ONE = nbv(1);


function bnClone() {
    var r = nbi();
    this.copyTo(r);
    return r;
}

// (public) return value as integer

function bnIntValue() {
    if (this.s < 0) {
        if (this.t == 1) return this[0] - this.DV;
        else if (this.t == 0) return -1;
    }
    else if (this.t == 1) return this[0];
    else if (this.t == 0) return 0;
    // assumes 16 < DB < 32
    return ((this[1] & ((1 << (32 - this.DB)) - 1)) << this.DB) | this[0];
}

// (public) return value as byte

function bnByteValue() {
    return (this.t == 0) ? this.s : (this[0] << 24) >> 24;
}

// (public) return value as short (assumes DB>=16)

function bnShortValue() {
    return (this.t == 0) ? this.s : (this[0] << 16) >> 16;
}

// (protected) return x s.t. r^x < DV

function bnpChunkSize(r) {
    return Math.floor(Math.LN2 * this.DB / Math.log(r));
}

// (public) 0 if this == 0, 1 if this > 0

function bnSigNum() {
    if (this.s < 0) return -1;
    else if (this.t <= 0 || (this.t == 1 && this[0] <= 0)) return 0;
    else return 1;
}

// (protected) convert to radix string

function bnpToRadix(b) {
    if (b == null) b = 10;
    if (this.signum() == 0 || b < 2 || b > 36) return "0";
    var cs = this.chunkSize(b);
    var a = Math.pow(b, cs);
    var d = nbv(a),
        y = nbi(),
        z = nbi(),
        r = "";
    this.divRemTo(d, y, z);
    while (y.signum() > 0) {
        r = (a + z.intValue()).toString(b).substr(1) + r;
        y.divRemTo(d, y, z);
    }
    return z.intValue().toString(b) + r;
}

// (protected) convert from radix string

function bnpFromRadix(s, b) {
    this.fromInt(0);
    if (b == null) b = 10;
    var cs = this.chunkSize(b);
    var d = Math.pow(b, cs),
        mi = false,
        j = 0,
        w = 0;
    for (var i = 0; i < s.length; ++i) {
        var x = intAt(s, i);
        if (x < 0) {
            if (s.charAt(i) == "-" && this.signum() == 0) mi = true;
            continue;
        }
        w = b * w + x;
        if (++j >= cs) {
            this.dMultiply(d);
            this.dAddOffset(w, 0);
            j = 0;
            w = 0;
        }
    }
    if (j > 0) {
        this.dMultiply(Math.pow(b, j));
        this.dAddOffset(w, 0);
    }
    if (mi) BigInteger.ZERO.subTo(this, this);
}

// (protected) alternate constructor

function bnpFromNumber(a, b, c) {
    if ("number" == typeof b) {
        // new BigInteger(int,int,RNG)
        if (a < 2) this.fromInt(1);
        else {
            this.fromNumber(a, c);
            if (!this.testBit(a - 1)) // force MSB set
                this.bitwiseTo(BigInteger.ONE.shiftLeft(a - 1), op_or, this);
            if (this.isEven()) this.dAddOffset(1, 0); // force odd
            while (!this.isProbablePrime(b)) {
                this.dAddOffset(2, 0);
                if (this.bitLength() > a) this.subTo(BigInteger.ONE.shiftLeft(a - 1), this);
            }
        }
    }
    else {
        // new BigInteger(int,RNG)
        var x = new Array(),
            t = a & 7;
        x.length = (a >> 3) + 1;
        b.nextBytes(x);
        if (t > 0) x[0] &= ((1 << t) - 1);
        else x[0] = 0;
        this.fromString(x, 256);
    }
}

// (public) convert to bigendian byte array

function bnToByteArray() {
    var i = this.t,
        r = new Array();
    r[0] = this.s;
    var p = this.DB - (i * this.DB) % 8,
        d, k = 0;
    if (i-- > 0) {
        if (p < this.DB && (d = this[i] >> p) != (this.s & this.DM) >> p) r[k++] = d | (this.s << (this.DB - p));
        while (i >= 0) {
            if (p < 8) {
                d = (this[i] & ((1 << p) - 1)) << (8 - p);
                d |= this[--i] >> (p += this.DB - 8);
            }
            else {
                d = (this[i] >> (p -= 8)) & 0xff;
                if (p <= 0) {
                    p += this.DB;
                    --i;
                }
            }
            if ((d & 0x80) != 0) d |= -256;
            if (k == 0 && (this.s & 0x80) != (d & 0x80)) ++k;
            if (k > 0 || d != this.s) r[k++] = d;
        }
    }
    return r;
}

function bnEquals(a) {
    return (this.compareTo(a) == 0);
}

function bnMin(a) {
    return (this.compareTo(a) < 0) ? this : a;
}

function bnMax(a) {
    return (this.compareTo(a) > 0) ? this : a;
}

// (protected) r = this op a (bitwise)

function bnpBitwiseTo(a, op, r) {
    var i, f, m = Math.min(a.t, this.t);
    for (i = 0; i < m; ++i) r[i] = op(this[i], a[i]);
    if (a.t < this.t) {
        f = a.s & this.DM;
        for (i = m; i < this.t; ++i) r[i] = op(this[i], f);
        r.t = this.t;
    }
    else {
        f = this.s & this.DM;
        for (i = m; i < a.t; ++i) r[i] = op(f, a[i]);
        r.t = a.t;
    }
    r.s = op(this.s, a.s);
    r.clamp();
}

// (public) this & a

function op_and(x, y) {
    return x & y;
}

function bnAnd(a) {
    var r = nbi();
    this.bitwiseTo(a, op_and, r);
    return r;
}

// (public) this | a

function op_or(x, y) {
    return x | y;
}

function bnOr(a) {
    var r = nbi();
    this.bitwiseTo(a, op_or, r);
    return r;
}

// (public) this ^ a

function op_xor(x, y) {
    return x ^ y;
}

function bnXor(a) {
    var r = nbi();
    this.bitwiseTo(a, op_xor, r);
    return r;
}

// (public) this & ~a

function op_andnot(x, y) {
    return x & ~y;
}

function bnAndNot(a) {
    var r = nbi();
    this.bitwiseTo(a, op_andnot, r);
    return r;
}

// (public) ~this

function bnNot() {
    var r = nbi();
    for (var i = 0; i < this.t; ++i) r[i] = this.DM & ~this[i];
    r.t = this.t;
    r.s = ~this.s;
    return r;
}

// (public) this << n

function bnShiftLeft(n) {
    var r = nbi();
    if (n < 0) this.rShiftTo(-n, r);
    else this.lShiftTo(n, r);
    return r;
}

// (public) this >> n

function bnShiftRight(n) {
    var r = nbi();
    if (n < 0) this.lShiftTo(-n, r);
    else this.rShiftTo(n, r);
    return r;
}

// return index of lowest 1-bit in x, x < 2^31

function lbit(x) {
    if (x == 0) return -1;
    var r = 0;
    if ((x & 0xffff) == 0) {
        x >>= 16;
        r += 16;
    }
    if ((x & 0xff) == 0) {
        x >>= 8;
        r += 8;
    }
    if ((x & 0xf) == 0) {
        x >>= 4;
        r += 4;
    }
    if ((x & 3) == 0) {
        x >>= 2;
        r += 2;
    }
    if ((x & 1) == 0) ++r;
    return r;
}

// (public) returns index of lowest 1-bit (or -1 if none)

function bnGetLowestSetBit() {
    for (var i = 0; i < this.t; ++i)
        if (this[i] != 0) return i * this.DB + lbit(this[i]);
    if (this.s < 0) return this.t * this.DB;
    return -1;
}

// return number of 1 bits in x

function cbit(x) {
    var r = 0;
    while (x != 0) {
        x &= x - 1;
        ++r;
    }
    return r;
}

// (public) return number of set bits

function bnBitCount() {
    var r = 0,
        x = this.s & this.DM;
    for (var i = 0; i < this.t; ++i) r += cbit(this[i] ^ x);
    return r;
}

// (public) true iff nth bit is set

function bnTestBit(n) {
    var j = Math.floor(n / this.DB);
    if (j >= this.t) return (this.s != 0);
    return ((this[j] & (1 << (n % this.DB))) != 0);
}

// (protected) this op (1<<n)

function bnpChangeBit(n, op) {
    var r = BigInteger.ONE.shiftLeft(n);
    this.bitwiseTo(r, op, r);
    return r;
}

// (public) this | (1<<n)

function bnSetBit(n) {
    return this.changeBit(n, op_or);
}

// (public) this & ~(1<<n)

function bnClearBit(n) {
    return this.changeBit(n, op_andnot);
}

// (public) this ^ (1<<n)

function bnFlipBit(n) {
    return this.changeBit(n, op_xor);
}

// (protected) r = this + a

function bnpAddTo(a, r) {
    var i = 0,
        c = 0,
        m = Math.min(a.t, this.t);
    while (i < m) {
        c += this[i] + a[i];
        r[i++] = c & this.DM;
        c >>= this.DB;
    }
    if (a.t < this.t) {
        c += a.s;
        while (i < this.t) {
            c += this[i];
            r[i++] = c & this.DM;
            c >>= this.DB;
        }
        c += this.s;
    }
    else {
        c += this.s;
        while (i < a.t) {
            c += a[i];
            r[i++] = c & this.DM;
            c >>= this.DB;
        }
        c += a.s;
    }
    r.s = (c < 0) ? -1 : 0;
    if (c > 0) r[i++] = c;
    else if (c < -1) r[i++] = this.DV + c;
    r.t = i;
    r.clamp();
}

// (public) this + a

function bnAdd(a) {
    var r = nbi();
    this.addTo(a, r);
    return r;
}

// (public) this - a

function bnSubtract(a) {
    var r = nbi();
    this.subTo(a, r);
    return r;
}

// (public) this * a

function bnMultiply(a) {
    var r = nbi();
    this.multiplyTo(a, r);
    return r;
}

// (public) this^2

function bnSquare() {
    var r = nbi();
    this.squareTo(r);
    return r;
}

// (public) this / a

function bnDivide(a) {
    var r = nbi();
    this.divRemTo(a, r, null);
    return r;
}

// (public) this % a

function bnRemainder(a) {
    var r = nbi();
    this.divRemTo(a, null, r);
    return r;
}

// (public) [this/a,this%a]

function bnDivideAndRemainder(a) {
    var q = nbi(),
        r = nbi();
    this.divRemTo(a, q, r);
    return new Array(q, r);
}

// (protected) this *= n, this >= 0, 1 < n < DV

function bnpDMultiply(n) {
    this[this.t] = this.am(0, n - 1, this, 0, 0, this.t);
    ++this.t;
    this.clamp();
}

// (protected) this += n << w words, this >= 0

function bnpDAddOffset(n, w) {
    if (n == 0) return;
    while (this.t <= w) this[this.t++] = 0;
    this[w] += n;
    while (this[w] >= this.DV) {
        this[w] -= this.DV;
        if (++w >= this.t) this[this.t++] = 0;
        ++this[w];
    }
}

// A "null" reducer

function NullExp() { }

function nNop(x) {
    return x;
}

function nMulTo(x, y, r) {
    x.multiplyTo(y, r);
}

function nSqrTo(x, r) {
    x.squareTo(r);
}

NullExp.prototype.convert = nNop;
NullExp.prototype.revert = nNop;
NullExp.prototype.mulTo = nMulTo;
NullExp.prototype.sqrTo = nSqrTo;

// (public) this^e

function bnPow(e) {
    return this.exp(e, new NullExp());
}

// (protected) r = lower n words of "this * a", a.t <= n
// "this" should be the larger one if appropriate.

function bnpMultiplyLowerTo(a, n, r) {
    var i = Math.min(this.t + a.t, n);
    r.s = 0; // assumes a,this >= 0
    r.t = i;
    while (i > 0) r[--i] = 0;
    var j;
    for (j = r.t - this.t; i < j; ++i) r[i + this.t] = this.am(0, a[i], r, i, 0, this.t);
    for (j = Math.min(a.t, n); i < j; ++i) this.am(0, a[i], r, i, 0, n - i);
    r.clamp();
}

// (protected) r = "this * a" without lower n words, n > 0
// "this" should be the larger one if appropriate.

function bnpMultiplyUpperTo(a, n, r) {
    --n;
    var i = r.t = this.t + a.t - n;
    r.s = 0; // assumes a,this >= 0
    while (--i >= 0) r[i] = 0;
    for (i = Math.max(n - this.t, 0); i < a.t; ++i)
        r[this.t + i - n] = this.am(n - i, a[i], r, 0, 0, this.t + i - n);
    r.clamp();
    r.drShiftTo(1, r);
}

// Barrett modular reduction

function Barrett(m) {
    // setup Barrett
    this.r2 = nbi();
    this.q3 = nbi();
    BigInteger.ONE.dlShiftTo(2 * m.t, this.r2);
    this.mu = this.r2.divide(m);
    this.m = m;
}

function barrettConvert(x) {
    if (x.s < 0 || x.t > 2 * this.m.t) return x.mod(this.m);
    else if (x.compareTo(this.m) < 0) return x;
    else {
        var r = nbi();
        x.copyTo(r);
        this.reduce(r);
        return r;
    }
}

function barrettRevert(x) {
    return x;
}

// x = x mod m (HAC 14.42)

function barrettReduce(x) {
    x.drShiftTo(this.m.t - 1, this.r2);
    if (x.t > this.m.t + 1) {
        x.t = this.m.t + 1;
        x.clamp();
    }
    this.mu.multiplyUpperTo(this.r2, this.m.t + 1, this.q3);
    this.m.multiplyLowerTo(this.q3, this.m.t + 1, this.r2);
    while (x.compareTo(this.r2) < 0) x.dAddOffset(1, this.m.t + 1);
    x.subTo(this.r2, x);
    while (x.compareTo(this.m) >= 0) x.subTo(this.m, x);
}

// r = x^2 mod m; x != r

function barrettSqrTo(x, r) {
    x.squareTo(r);
    this.reduce(r);
}

// r = x*y mod m; x,y != r

function barrettMulTo(x, y, r) {
    x.multiplyTo(y, r);
    this.reduce(r);
}

Barrett.prototype.convert = barrettConvert;
Barrett.prototype.revert = barrettRevert;
Barrett.prototype.reduce = barrettReduce;
Barrett.prototype.mulTo = barrettMulTo;
Barrett.prototype.sqrTo = barrettSqrTo;

// (public) this^e % m (HAC 14.85)

function bnModPow(e, m) {
    var i = e.bitLength(),
        k, r = nbv(1),
        z;
    if (i <= 0) return r;
    else if (i < 18) k = 1;
    else if (i < 48) k = 3;
    else if (i < 144) k = 4;
    else if (i < 768) k = 5;
    else k = 6;
    if (i < 8) z = new Classic(m);
    else if (m.isEven()) z = new Barrett(m);
    else z = new Montgomery(m);

    // precomputation
    var g = new Array(),
        n = 3,
        k1 = k - 1,
        km = (1 << k) - 1;
    g[1] = z.convert(this);
    if (k > 1) {
        var g2 = nbi();
        z.sqrTo(g[1], g2);
        while (n <= km) {
            g[n] = nbi();
            z.mulTo(g2, g[n - 2], g[n]);
            n += 2;
        }
    }

    var j = e.t - 1,
        w, is1 = true,
        r2 = nbi(),
        t;
    i = nbits(e[j]) - 1;
    while (j >= 0) {
        if (i >= k1) w = (e[j] >> (i - k1)) & km;
        else {
            w = (e[j] & ((1 << (i + 1)) - 1)) << (k1 - i);
            if (j > 0) w |= e[j - 1] >> (this.DB + i - k1);
        }

        n = k;
        while ((w & 1) == 0) {
            w >>= 1;
            --n;
        }
        if ((i -= n) < 0) {
            i += this.DB;
            --j;
        }
        if (is1) { // ret == 1, don't bother squaring or multiplying it
            g[w].copyTo(r);
            is1 = false;
        }
        else {
            while (n > 1) {
                z.sqrTo(r, r2);
                z.sqrTo(r2, r);
                n -= 2;
            }
            if (n > 0) z.sqrTo(r, r2);
            else {
                t = r;
                r = r2;
                r2 = t;
            }
            z.mulTo(r2, g[w], r);
        }

        while (j >= 0 && (e[j] & (1 << i)) == 0) {
            z.sqrTo(r, r2);
            t = r;
            r = r2;
            r2 = t;
            if (--i < 0) {
                i = this.DB - 1;
                --j;
            }
        }
    }
    return z.revert(r);
}

// (public) gcd(this,a) (HAC 14.54)

function bnGCD(a) {
    var x = (this.s < 0) ? this.negate() : this.clone();
    var y = (a.s < 0) ? a.negate() : a.clone();
    if (x.compareTo(y) < 0) {
        var t = x;
        x = y;
        y = t;
    }
    var i = x.getLowestSetBit(),
        g = y.getLowestSetBit();
    if (g < 0) return x;
    if (i < g) g = i;
    if (g > 0) {
        x.rShiftTo(g, x);
        y.rShiftTo(g, y);
    }
    while (x.signum() > 0) {
        if ((i = x.getLowestSetBit()) > 0) x.rShiftTo(i, x);
        if ((i = y.getLowestSetBit()) > 0) y.rShiftTo(i, y);
        if (x.compareTo(y) >= 0) {
            x.subTo(y, x);
            x.rShiftTo(1, x);
        }
        else {
            y.subTo(x, y);
            y.rShiftTo(1, y);
        }
    }
    if (g > 0) y.lShiftTo(g, y);
    return y;
}

// (protected) this % n, n < 2^26

function bnpModInt(n) {
    if (n <= 0) return 0;
    var d = this.DV % n,
        r = (this.s < 0) ? n - 1 : 0;
    if (this.t > 0) if (d == 0) r = this[0] % n;
    else for (var i = this.t - 1; i >= 0; --i) r = (d * r + this[i]) % n;
    return r;
}

// (public) 1/this % m (HAC 14.61)

function bnModInverse(m) {
    var ac = m.isEven();
    if ((this.isEven() && ac) || m.signum() == 0) return BigInteger.ZERO;
    var u = m.clone(),
        v = this.clone();
    var a = nbv(1),
        b = nbv(0),
        c = nbv(0),
        d = nbv(1);
    while (u.signum() != 0) {
        while (u.isEven()) {
            u.rShiftTo(1, u);
            if (ac) {
                if (!a.isEven() || !b.isEven()) {
                    a.addTo(this, a);
                    b.subTo(m, b);
                }
                a.rShiftTo(1, a);
            }
            else if (!b.isEven()) b.subTo(m, b);
            b.rShiftTo(1, b);
        }
        while (v.isEven()) {
            v.rShiftTo(1, v);
            if (ac) {
                if (!c.isEven() || !d.isEven()) {
                    c.addTo(this, c);
                    d.subTo(m, d);
                }
                c.rShiftTo(1, c);
            }
            else if (!d.isEven()) d.subTo(m, d);
            d.rShiftTo(1, d);
        }
        if (u.compareTo(v) >= 0) {
            u.subTo(v, u);
            if (ac) a.subTo(c, a);
            b.subTo(d, b);
        }
        else {
            v.subTo(u, v);
            if (ac) c.subTo(a, c);
            d.subTo(b, d);
        }
    }
    if (v.compareTo(BigInteger.ONE) != 0) return BigInteger.ZERO;
    if (d.compareTo(m) >= 0) return d.subtract(m);
    if (d.signum() < 0) d.addTo(m, d);
    else return d;
    if (d.signum() < 0) return d.add(m);
    else return d;
}

var lowprimes = [2, 3, 5, 7, 11, 13, 17, 19, 23, 29, 31, 37, 41, 43, 47, 53, 59, 61, 67, 71, 73, 79, 83, 89, 97, 101, 103, 107, 109, 113, 127, 131, 137, 139, 149, 151, 157, 163, 167, 173, 179, 181, 191, 193, 197, 199, 211, 223, 227, 229, 233, 239, 241, 251, 257, 263, 269, 271, 277, 281, 283, 293, 307, 311, 313, 317, 331, 337, 347, 349, 353, 359, 367, 373, 379, 383, 389, 397, 401, 409, 419, 421, 431, 433, 439, 443, 449, 457, 461, 463, 467, 479, 487, 491, 499, 503, 509, 521, 523, 541, 547, 557, 563, 569, 571, 577, 587, 593, 599, 601, 607, 613, 617, 619, 631, 641, 643, 647, 653, 659, 661, 673, 677, 683, 691, 701, 709, 719, 727, 733, 739, 743, 751, 757, 761, 769, 773, 787, 797, 809, 811, 821, 823, 827, 829, 839, 853, 857, 859, 863, 877, 881, 883, 887, 907, 911, 919, 929, 937, 941, 947, 953, 967, 971, 977, 983, 991, 997];
var lplim = (1 << 26) / lowprimes[lowprimes.length - 1];

// (public) test primality with certainty >= 1-.5^t

function bnIsProbablePrime(t) {
    var i, x = this.abs();
    if (x.t == 1 && x[0] <= lowprimes[lowprimes.length - 1]) {
        for (i = 0; i < lowprimes.length; ++i)
            if (x[0] == lowprimes[i]) return true;
        return false;
    }
    if (x.isEven()) return false;
    i = 1;
    while (i < lowprimes.length) {
        var m = lowprimes[i],
            j = i + 1;
        while (j < lowprimes.length && m < lplim) m *= lowprimes[j++];
        m = x.modInt(m);
        while (i < j) if (m % lowprimes[i++] == 0) return false;
    }
    return x.millerRabin(t);
}

// (protected) true if probably prime (HAC 4.24, Miller-Rabin)

function bnpMillerRabin(t) {
    var n1 = this.subtract(BigInteger.ONE);
    var k = n1.getLowestSetBit();
    if (k <= 0) return false;
    var r = n1.shiftRight(k);
    t = (t + 1) >> 1;
    if (t > lowprimes.length) t = lowprimes.length;
    var a = nbi();
    for (var i = 0; i < t; ++i) {
        //Pick bases at random, instead of starting at 2
        a.fromInt(lowprimes[Math.floor(Math.random() * lowprimes.length)]);
        var y = a.modPow(r, this);
        if (y.compareTo(BigInteger.ONE) != 0 && y.compareTo(n1) != 0) {
            var j = 1;
            while (j++ < k && y.compareTo(n1) != 0) {
                y = y.modPowInt(2, this);
                if (y.compareTo(BigInteger.ONE) == 0) return false;
            }
            if (y.compareTo(n1) != 0) return false;
        }
    }
    return true;
}

// protected
BigInteger.prototype.chunkSize = bnpChunkSize;
BigInteger.prototype.toRadix = bnpToRadix;
BigInteger.prototype.fromRadix = bnpFromRadix;
BigInteger.prototype.fromNumber = bnpFromNumber;
BigInteger.prototype.bitwiseTo = bnpBitwiseTo;
BigInteger.prototype.changeBit = bnpChangeBit;
BigInteger.prototype.addTo = bnpAddTo;
BigInteger.prototype.dMultiply = bnpDMultiply;
BigInteger.prototype.dAddOffset = bnpDAddOffset;
BigInteger.prototype.multiplyLowerTo = bnpMultiplyLowerTo;
BigInteger.prototype.multiplyUpperTo = bnpMultiplyUpperTo;
BigInteger.prototype.modInt = bnpModInt;
BigInteger.prototype.millerRabin = bnpMillerRabin;

// public
BigInteger.prototype.clone = bnClone;
BigInteger.prototype.intValue = bnIntValue;
BigInteger.prototype.byteValue = bnByteValue;
BigInteger.prototype.shortValue = bnShortValue;
BigInteger.prototype.signum = bnSigNum;
BigInteger.prototype.toByteArray = bnToByteArray;
BigInteger.prototype.equals = bnEquals;
BigInteger.prototype.min = bnMin;
BigInteger.prototype.max = bnMax;
BigInteger.prototype.and = bnAnd;
BigInteger.prototype.or = bnOr;
BigInteger.prototype.xor = bnXor;
BigInteger.prototype.andNot = bnAndNot;
BigInteger.prototype.not = bnNot;
BigInteger.prototype.shiftLeft = bnShiftLeft;
BigInteger.prototype.shiftRight = bnShiftRight;
BigInteger.prototype.getLowestSetBit = bnGetLowestSetBit;
BigInteger.prototype.bitCount = bnBitCount;
BigInteger.prototype.testBit = bnTestBit;
BigInteger.prototype.setBit = bnSetBit;
BigInteger.prototype.clearBit = bnClearBit;
BigInteger.prototype.flipBit = bnFlipBit;
BigInteger.prototype.add = bnAdd;
BigInteger.prototype.subtract = bnSubtract;
BigInteger.prototype.multiply = bnMultiply;
BigInteger.prototype.divide = bnDivide;
BigInteger.prototype.remainder = bnRemainder;
BigInteger.prototype.divideAndRemainder = bnDivideAndRemainder;
BigInteger.prototype.modPow = bnModPow;
BigInteger.prototype.modInverse = bnModInverse;
BigInteger.prototype.pow = bnPow;
BigInteger.prototype.gcd = bnGCD;
BigInteger.prototype.isProbablePrime = bnIsProbablePrime;

// JSBN-specific extension
BigInteger.prototype.square = bnSquare;

/*
------------------------------------------------------------------------------------------------------------------------
prng4.js
------------------------------------------------------------------------------------------------------------------------
*/
// prng4.js - uses Arcfour as a PRNG

function Arcfour() {
    this.i = 0;
    this.j = 0;
    this.S = new Array();
}

// Initialize arcfour context from key, an array of ints, each from [0..255]
function ARC4init(key) {
    var i, j, t;
    for (i = 0; i < 256; ++i)
        this.S[i] = i;
    j = 0;
    for (i = 0; i < 256; ++i) {
        j = (j + this.S[i] + key[i % key.length]) & 255;
        t = this.S[i];
        this.S[i] = this.S[j];
        this.S[j] = t;
    }
    this.i = 0;
    this.j = 0;
}

function ARC4next() {
    var t;
    this.i = (this.i + 1) & 255;
    this.j = (this.j + this.S[this.i]) & 255;
    t = this.S[this.i];
    this.S[this.i] = this.S[this.j];
    this.S[this.j] = t;
    return this.S[(t + this.S[this.i]) & 255];
}

Arcfour.prototype.init = ARC4init;
Arcfour.prototype.next = ARC4next;

// Plug in your RNG constructor here
function prng_newstate() {
    return new Arcfour();
}

// Pool size must be a multiple of 4 and greater than 32.
// An array of bytes the size of the pool will be passed to init()
var rng_psize = 256;

/*
------------------------------------------------------------------------------------------------------------------------
rng.js
------------------------------------------------------------------------------------------------------------------------
*/
// Random number generator - requires a PRNG backend, e.g. prng4.js

// For best results, put code like
// <body onClick='rng_seed_time();' onKeyPress='rng_seed_time();'>
// in your main HTML document.

var rng_state;
var rng_pool;
var rng_pptr;

// Mix in a 32-bit integer into the pool
function rng_seed_int(x) {
    rng_pool[rng_pptr++] ^= x & 255;
    rng_pool[rng_pptr++] ^= (x >> 8) & 255;
    rng_pool[rng_pptr++] ^= (x >> 16) & 255;
    rng_pool[rng_pptr++] ^= (x >> 24) & 255;
    if (rng_pptr >= rng_psize) rng_pptr -= rng_psize;
}

// Mix in the current time (w/milliseconds) into the pool
function rng_seed_time() {
    rng_seed_int(new Date().getTime());
}

// Initialize the pool with junk if needed.
if (rng_pool == null) {
    rng_pool = new Array();
    rng_pptr = 0;
    var t;
    if (window.crypto && window.crypto.getRandomValues) {
        // Use webcrypto if available
        var ua = new Uint8Array(32);
        window.crypto.getRandomValues(ua);
        for (t = 0; t < 32; ++t)
            rng_pool[rng_pptr++] = ua[t];
    }
    if (navigator.appName == "Netscape" && navigator.appVersion < "5" && window.crypto) {
        // Extract entropy (256 bits) from NS4 RNG if available
        var z = window.crypto.random(32);
        for (t = 0; t < z.length; ++t)
            rng_pool[rng_pptr++] = z.charCodeAt(t) & 255;
    }
    while (rng_pptr < rng_psize) {  // extract some randomness from Math.random()
        t = Math.floor(65536 * Math.random());
        rng_pool[rng_pptr++] = t >>> 8;
        rng_pool[rng_pptr++] = t & 255;
    }
    rng_pptr = 0;
    rng_seed_time();
    //rng_seed_int(window.screenX);
    //rng_seed_int(window.screenY);
}

function rng_get_byte() {
    if (rng_state == null) {
        rng_seed_time();
        rng_state = prng_newstate();
        rng_state.init(rng_pool);
        for (rng_pptr = 0; rng_pptr < rng_pool.length; ++rng_pptr)
            rng_pool[rng_pptr] = 0;
        rng_pptr = 0;
        //rng_pool = null;
    }
    // TODO: allow reseeding after first request
    return rng_state.next();
}

function rng_get_bytes(ba) {
    var i;
    for (i = 0; i < ba.length; ++i) ba[i] = rng_get_byte();
}

function SecureRandom() { }

SecureRandom.prototype.nextBytes = rng_get_bytes;
;
/* 
Modifications are done by vinod 01 MAY 2018
https://stackoverflow.com/questions/13472782/openssl-decryption-in-javascript?lq=1
https://pastebin.com/GfhuDwj5
*/

var RSAPublicKey = function ($modulus, $encryptionExponent) {
    this.modulus = new BigInteger(Hex.encode($modulus), 16);
    this.encryptionExponent = new BigInteger(Hex.encode($encryptionExponent), 16);
}

var UTF8 = {
    encode: function ($input) {
        $input = $input.replace(/\r\n/g, "\n");
        var $output = "";
        for (var $n = 0; $n < $input.length; $n++) {
            var $c = $input.charCodeAt($n);
            if ($c < 128) {
                $output += String.fromCharCode($c);
            } else if (($c > 127) && ($c < 2048)) {
                $output += String.fromCharCode(($c >> 6) | 192);
                $output += String.fromCharCode(($c & 63) | 128);
            } else {
                $output += String.fromCharCode(($c >> 12) | 224);
                $output += String.fromCharCode((($c >> 6) & 63) | 128);
                $output += String.fromCharCode(($c & 63) | 128);
            }
        }
        return $output;
    },
    decode: function ($input) {
        var $output = "";
        var $i = 0;
        var $c = $c1 = $c2 = 0;
        while ($i < $input.length) {
            $c = $input.charCodeAt($i);
            if ($c < 128) {
                $output += String.fromCharCode($c);
                $i++;
            } else if (($c > 191) && ($c < 224)) {
                $c2 = $input.charCodeAt($i + 1);
                $output += String.fromCharCode((($c & 31) << 6) | ($c2 & 63));
                $i += 2;
            } else {
                $c2 = $input.charCodeAt($i + 1);
                $c3 = $input.charCodeAt($i + 2);
                $output += String.fromCharCode((($c & 15) << 12) | (($c2 & 63) << 6) | ($c3 & 63));
                $i += 3;
            }
        }
        return $output;
    }
};

var Base64 = {
    base64: "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/=",
    encode: function ($input) {
        if (!$input) {
            return false;
        }
        //$input = UTF8.encode($input);
        var $output = "";
        var $chr1, $chr2, $chr3;
        var $enc1, $enc2, $enc3, $enc4;
        var $i = 0;
        do {
            $chr1 = $input.charCodeAt($i++);
            $chr2 = $input.charCodeAt($i++);
            $chr3 = $input.charCodeAt($i++);
            $enc1 = $chr1 >> 2;
            $enc2 = (($chr1 & 3) << 4) | ($chr2 >> 4);
            $enc3 = (($chr2 & 15) << 2) | ($chr3 >> 6);
            $enc4 = $chr3 & 63;
            if (isNaN($chr2)) $enc3 = $enc4 = 64;
            else if (isNaN($chr3)) $enc4 = 64;
            $output += this.base64.charAt($enc1) + this.base64.charAt($enc2) + this.base64.charAt($enc3) + this.base64.charAt($enc4);
        } while ($i < $input.length);
        return $output;
    },
    decode: function ($input) {
        if (!$input) return false;
        $input = $input.replace(/[^A-Za-z0-9\+\/\=]/g, "");
        var $output = "";
        var $enc1, $enc2, $enc3, $enc4;
        var $i = 0;
        do {
            $enc1 = this.base64.indexOf($input.charAt($i++));
            $enc2 = this.base64.indexOf($input.charAt($i++));
            $enc3 = this.base64.indexOf($input.charAt($i++));
            $enc4 = this.base64.indexOf($input.charAt($i++));
            $output += String.fromCharCode(($enc1 << 2) | ($enc2 >> 4));
            if ($enc3 != 64) $output += String.fromCharCode((($enc2 & 15) << 4) | ($enc3 >> 2));
            if ($enc4 != 64) $output += String.fromCharCode((($enc3 & 3) << 6) | $enc4);
        } while ($i < $input.length);
        return $output; //UTF8.decode($output);
    }
};

var Hex = {
    hex: "0123456789abcdef",
    encode: function ($input) {
        if (!$input) return false;
        var $output = "";
        var $k;
        var $i = 0;
        do {
            $k = $input.charCodeAt($i++);
            $output += this.hex.charAt(($k >> 4) & 0xf) + this.hex.charAt($k & 0xf);
        } while ($i < $input.length);
        return $output;
    },
    decode: function ($input) {
        if (!$input) return false;
        $input = $input.replace(/[^0-9abcdef]/g, "");
        var $output = "";
        var $i = 0;
        do {
            $output += String.fromCharCode(((this.hex.indexOf($input.charAt($i++)) << 4) & 0xf0) | (this.hex.indexOf($input.charAt($i++)) & 0xf));
        } while ($i < $input.length);
        return $output;
    }
};

var ASN1Data = function ($data) {
    this.error = false;
    this.parse = function ($data) {
        if (!$data) {
            this.error = true;
            return null;
        }
        var $result = [];
        while ($data.length > 0) {
            // get the tag
            var $tag = $data.charCodeAt(0);
            $data = $data.substr(1);
            // get length
            var $length = 0;
            // ignore any null tag
            if (($tag & 31) == 0x5) $data = $data.substr(1);
            else {
                if ($data.charCodeAt(0) & 128) {
                    var $lengthSize = $data.charCodeAt(0) & 127;
                    $data = $data.substr(1);
                    if ($lengthSize > 0) $length = $data.charCodeAt(0);
                    if ($lengthSize > 1) $length = (($length << 8) | $data.charCodeAt(1));
                    if ($lengthSize > 2) {
                        this.error = true;
                        return null;
                    }
                    $data = $data.substr($lengthSize);
                } else {
                    $length = $data.charCodeAt(0);
                    $data = $data.substr(1);
                }
            }
            // get value
            var $value = "";
            if ($length) {
                if ($length > $data.length) {
                    this.error = true;
                    return null;
                }
                $value = $data.substr(0, $length);
                $data = $data.substr($length);
            }
            if ($tag & 32)
                $result.push(this.parse($value)); // sequence
            else
                $result.push(this.value(($tag & 128) ? 4 : ($tag & 31), $value));
        }
        return $result;
    };
    this.value = function ($tag, $data) {
        if ($tag == 1)
            return $data ? true : false;
        else if ($tag == 2) //integer
            return $data;
        else if ($tag == 3) //bit string
            return this.parse($data.substr(1));
        else if ($tag == 5) //null
            return null;
        else if ($tag == 6) { //ID
            var $res = [];
            var $d0 = $data.charCodeAt(0);
            $res.push(Math.floor($d0 / 40));
            $res.push($d0 - $res[0] * 40);
            var $stack = [];
            var $powNum = 0;
            var $i;
            for ($i = 1; $i < $data.length; $i++) {
                var $token = $data.charCodeAt($i);
                $stack.push($token & 127);
                if ($token & 128)
                    $powNum++;
                else {
                    var $j;
                    var $sum = 0;
                    for ($j = 0; $j < $stack.length; $j++)
                        $sum += $stack[$j] * Math.pow(128, $powNum--);
                    $res.push($sum);
                    $powNum = 0;
                    $stack = [];
                }
            }
            return $res.join(".");
        }
        return null;
    }
    this.data = this.parse($data);
};

/* var RSA = {
    getPublicKey: function($pem) {
        if($pem.length<50) return false;
        if($pem.substr(0,26)!="-----BEGIN PUBLIC KEY-----") return false;
        $pem = $pem.substr(26);
        if($pem.substr($pem.length-24)!="-----END PUBLIC KEY-----") return false;
        $pem = $pem.substr(0,$pem.length-24);
        $pem = new ASN1Data(Base64.decode($pem));
        if($pem.error) return false;
        $pem = $pem.data;
        if($pem[0][0][0]=="1.2.840.113549.1.1.1")
            return new RSAPublicKey($pem[0][1][0][0], $pem[0][1][0][1]);
        return false;
    },
    encrypt: function($data, $pubkey) {
        if (!$pubkey) return false;
        var bytes = ($pubkey.modulus.bitLength()+7)>>3;
        $data = this.pkcs1pad2($data,bytes);
        if(!$data) return false;
        $data = $data.modPowInt($pubkey.encryptionExponent, $pubkey.modulus);
        if(!$data) return false;
        $data = $data.toString(16);
        while ($data.length < bytes*2)
            $data = '0' + $data;
        return Base64.encode(Hex.decode($data));
    },
    pkcs1pad2: function(s, n) { // $data, $keysize
 
        if(n < s.length + 11) { // TODO: fix for utf-8
            console.error("Too long for RSA");
            return null;
          }
          
          var ba = new Array();
          var i = s.length - 1;
          while(i >= 0 && n > 0) {
            var c = s.charCodeAt(i--);
            if(c < 128) { // encode using utf-8
              ba[--n] = c;
            }
            else if((c > 127) && (c < 2048)) {
              ba[--n] = (c & 63) | 128;
              ba[--n] = (c >> 6) | 192;
            }
            else {
              ba[--n] = (c & 63) | 128;
              ba[--n] = ((c >> 6) & 63) | 128;
              ba[--n] = (c >> 12) | 224;
            }
          }
          ba[--n] = 0;
          var rng = new SecureRandom();
          var x = new Array();
          while(n > 2) { // random non-zero pad
            x[0] = 0;
            while(x[0] == 0) rng.nextBytes(x);
            ba[--n] = x[0];
          }
          ba[--n] = 2;
          ba[--n] = 0;
          return new BigInteger(ba);
    }
}
 */

var RSA = {
    getPublicKey: function ($pem) {
        if ($pem.length < 50)
            return false;
        if ($pem.substr(0, 26) != "-----BEGIN PUBLIC KEY-----")
            return false;
        $pem = $pem.substr(26);
        if ($pem.substr($pem.length - 24) != "-----END PUBLIC KEY-----")
            return false;
        $pem = $pem.substr(0, $pem.length - 24);
        $pem = new ASN1Data(Base64.decode($pem));
        if ($pem.error)
            return false;
        $pem = $pem.data;
        if ($pem[0][0][0] == "1.2.840.113549.1.1.1")
            return new RSAPublicKey($pem[0][1][0][0], $pem[0][1][0][1]);
        return false;
    },
    encrypt: function ($data, $pubkey) {
        if (!$pubkey)
            return false;
        var bytes = ($pubkey.modulus.bitLength() + 7) >> 3;
        $data = this.pkcs1pad2($data, bytes);
        if (!$data)
            return false;
        $data = $data.modPowInt($pubkey.encryptionExponent, $pubkey.modulus);
        if (!$data)
            return false;
        $data = $data.toString(16);
        while ($data.length < bytes * 2)
            $data = '0' + $data;
        return Base64.encode(Hex.decode($data));
    },
    decrypt: function ($text, $pubkey) {
        if (!$pubkey)
            return false;
        $text = Hex.encode(Base64.decode($text)).toString(16);
        while ($text[0] == '0')
            $text = $text.replace('0', '');
        $text = new BigInteger($text, 16);
        $text = $text.modPowInt($pubkey.encryptionExponent, $pubkey.modulus);
        if (!$text)
            return false;
        var bytes = ($pubkey.modulus.bitLength() + 7) >> 3;
        $text = this.pkcs1unpad2($text, bytes);

        return $text;
    },
    pkcs1pad2: function pkcs1pad2(s, n) {
        if (n < s.length + 11) { // TODO: fix for utf-8
            console.error("Too long for RSA");
            return null;
        }
        var ba = new Array();
        var i = s.length - 1;
        while (i >= 0 && n > 0) {
            var c = s.charCodeAt(i--);
            if (c < 128) { // encode using utf-8
                ba[--n] = c;
            }
            else if ((c > 127) && (c < 2048)) {
                ba[--n] = (c & 63) | 128;
                ba[--n] = (c >> 6) | 192;
            }
            else {
                ba[--n] = (c & 63) | 128;
                ba[--n] = ((c >> 6) & 63) | 128;
                ba[--n] = (c >> 12) | 224;
            }
        }
        ba[--n] = 0;
        var rng = new SecureRandom();
        var x = new Array();
        while (n > 2) { // random non-zero pad
            x[0] = 0;
            while (x[0] == 0)
                rng.nextBytes(x);
            ba[--n] = x[0];
        }
        ba[--n] = 2;
        ba[--n] = 0;
        return new BigInteger(ba);
    },
    pkcs1unpad2: function pkcs1unpad2(d, n) {
        var b = d.toByteArray();
        var i = 0;
        while (i < b.length && b[i] == 0)
            ++i;
        /*if (b.length - i != n - 1 || b[i] != 2)
         return null;*/
        ++i;
        while (b[i] != 0)
            if (++i >= b.length)
                return null;
        var ret = "";
        while (++i < b.length) {
            var c = b[i] & 255;
            if (c < 128) { // utf-8 decode
                ret += String.fromCharCode(c);
            }
            else if ((c > 191) && (c < 224)) {
                ret += String.fromCharCode(((c & 31) << 6) | (b[i + 1] & 63));
                ++i;
            }
            else {
                ret += String.fromCharCode(((c & 15) << 12) | ((b[i + 1] & 63) << 6) | (b[i + 2] & 63));
                i += 2;
            }
        }
        return ret;
    }
};
(function () {
    'use strict';
    /**
      * Release Version - 0.5.x
      *
      * __Security : 0.4.6 - 28 AUG 2018
      *
      *-------------------------------------------------------- */

    angular.module("lw.security.main", []).
        service("__Security", ['__Utils', __Security]);

    function __Security(__Utils) {

        /**
         * 
         * Decrypt string using RSA with Public Key
         *     
         * @return object
         *-------------------------------------------------------- */
        this.rsaDecrypt = function (encryptedString) {
            return RSA.decrypt(encryptedString, this.getPublicRSA());
        };

        /**
         * 
         * Encrypt string using RSA with Public Key
         *     
         * @return object
         *-------------------------------------------------------- */
        this.rsaEncrypt = function (plainString) {

            return RSA.encrypt(plainString, this.getPublicRSA());
        }
        /**
         * 
         * Encrypt string using AES
         *     
         * @return string
         *-------------------------------------------------------- */
        this.aesEncrypt = function (plainString) {

            return window.btoa(CryptoJS.AES.encrypt(JSON.stringify(plainString),
                this.getToken(), { format: CryptoJSAesJson })
                .toString());
        }

        /**
         * 
         * Decrypt string using AES
         *     
         * @return object
         *-------------------------------------------------------- */
        this.aesDecrypt = function (encryptedString) {

            return JSON.parse(CryptoJS.AES.decrypt(window.atob(encryptedString),
                this.getToken(), { format: CryptoJSAesJson })
                .toString(CryptoJS.enc.Utf8));
        }

        /**
         * 
         * get security token
         *     
         * @return string
         *-------------------------------------------------------- */
        this.getToken = function () {
            return __Utils.getXSRFToken();
        }

        /**
         * 
         * get security token
         *     
         * @return string
         *-------------------------------------------------------- */
        this.getPublicRSA = function () {
            return RSA.getPublicKey(__appImmutables.public_encryption_token);
        }

        /**
         * 
         * get secured form identifier
         *     
         * @return object
         *-------------------------------------------------------- */
        this.getSecurityID = function () {
            return __appImmutables.form_security_id;
        };

        /**
         * Process encrypted data
         *
         * @return void
         *-------------------------------------------------------- */

        this.processSecuredData = function (responseData) {
            if (!responseData || !responseData['__maskedData']) {
                return false;
            } else {
                var splitedValues = (responseData['__maskedData']).split('__==__');
                var splitedValueString = '';
                for (var i = 0; i < splitedValues.length; i++) {
                    if (splitedValues[i]) {
                        splitedValueString += this.rsaDecrypt(splitedValues[i]);
                    }
                }
                return JSON.parse(splitedValueString);
            }

        };

        /**
       * process response data whatever it is secured or not returns decrypted data
       *
       * @return void
       *-------------------------------------------------------- */

        this.processResponseData = function (responseData) {
            var processedData = this.processSecuredData(responseData);
            if (processedData == false) {
                return responseData;
            } else {
                return processedData;
            }
        };
    };

})();

//# sourceMappingURL=../source-maps/ngware-app.src.js.map
