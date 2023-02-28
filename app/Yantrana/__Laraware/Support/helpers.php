<?php

/**
 * Core Helper - 1.6.13 - 15 JUN 2020.
 * 
 * Common helper functions for Laravel applications
 *
 *
 * Dependencies:
 * 
 * Laravel     5.0 +     - http://laravel.com
 *-------------------------------------------------------- */

/**
 * State route for StateViaRoute function
 *
 *-------------------------------------------------------- */
Route::get('/state-via-route/{stateRouteInfo}', [
    'as'    => '__laraware.state_via_route',
    'uses'  => 'App\Yantrana\__Laraware\Support\CommonSupport@stateViaRoute'
]);

/**
 * State route for StateViaRoute function
 *
 *-------------------------------------------------------- */
Route::get('/redirect-via-post/{redirectPostData}', [
    'as'    => '__laraware.redirect_via_post',
    'uses'  => 'App\Yantrana\__Laraware\Support\CommonSupport@redirectViaPost'
]);

/**
 * Enabaling Debug modes for the specific ips
 * @since 1.4.3 - 19 SEP 2018
 *-------------------------------------------------------- */
if (config('app.debug') == false) {
    if ($debugIps = env('APP_DEBUG_IPS', false)) {
        if ($debugIps) {
            $debugIps = array_map('trim', explode(',', $debugIps));
            if (in_array(request()->getClientIp(), $debugIps)) {
                config([
                    'app.debug' => true
                ]);
                unset($debugIps);
            }
        }
    }
}

/**
 * Redirect using post
 *
 * @param  string routeData url or route name
 * @param  array postData data to post

 *-------------------------------------------------------- */
if (!function_exists('redirectViaPost')) {
    function redirectViaPost($routeData, $postData = [], $tempRedirectData = [])
    {

        if (is_string($routeData) === false) {
            throw new Exception('route id should be string');
        }

        if (is_array($postData) === false) {
            throw new Exception('post data should be array');
        }

        if (starts_with($routeData, ['http://', 'https://'])) {
            $redirectRoute = $routeData;
        } else {
            $redirectRoute = route($routeData);
        }

        $postFieldString = '';

        foreach ($postData as $key => $value) {
            if (is_numeric($value) or is_string($value)) {
                $postFieldString .= '<input type="hidden" name="' . $key . '" value="' . $value . '">';
            } else {
                throw new Exception('value should be numeric or string');
            }
        }

        $tempRedirectData = json_encode($tempRedirectData);

        return <<<EOL
<!DOCTYPE html>
<html>
<head>
    <title>Redirecting ...</title>
</head>
    <body>
        Redirecting... please wait
        <form id="my_form" action="$redirectRoute" method="post">
        $postFieldString;
        </form>
        <script type="text/javascript">
            var tempRedirectData = `$tempRedirectData`;
            
            if(tempRedirectData) {
                window.localStorage.setItem('temp_redirect_data', tempRedirectData);
            }
           
            function redirectPostForm() {
                document.getElementById('my_form').submit();
            }
            window.onload = redirectPostForm;
        </script>
    </body>
</html>
EOL;
    }
}

/**
 * State via route function
 *
 * @param  routeData
 * @param  stateData

 *-------------------------------------------------------- */
if (!function_exists('stateViaRoute')) {
    function stateViaRoute($routeData, $stateData)
    {

        $routeId = $routeData;
        $routeParams = [];

        if (is_array($routeData) and isset($routeData[0]) and is_string($routeData[0])) {

            $routeId = $routeData[0];

            if (isset($routeData[1])) {

                if (is_array($routeData[1])) {
                    $routeParams = $routeData[1];
                } else {
                    $routeParams[] = $routeData[1];
                }
            }
        }

        $stateId = $stateData;
        $stateParams = [];

        if (is_array($stateData) and isset($stateData[0]) and is_string($stateData[0])) {

            $stateId = $stateData[0];

            if (isset($stateData[1]) and is_array($stateData[1])) {

                $stateParams = array_only($stateData[1], array_filter(array_keys($stateData[1]), 'is_string'));
            }
        }

        if (is_string($routeId) === false) {
            throw new Exception('route id should be string');
        }

        if (is_string($stateId) === false) {
            throw new Exception('route id should be string');
        }

        $stateViaRouteInfo = [
            'routeId' => $routeId,
            'routeParams' => $routeParams,
            'stateName' => $stateId,
            'stateParams' => $stateParams
        ];

        return route('__laraware.state_via_route', base64_encode(json_encode($stateViaRouteInfo)));
    }
}

/**
 * Debuging function for debugging javascript side.
 *
 * @param  N numbers of params can be sent 
 *-------------------------------------------------------- */
if (!function_exists('__dd')) {
    function __dd()
    {
        if (config('app.debug', false) == false) {
            throw new Exception('Something went wrong!!');
        }

        $args = func_get_args();

        if (empty($args)) {
            throw new Exception('__dd() No arguments are passed!!');
        }

        $backtrace = debug_backtrace();

        if (isset($backtrace[0])) {
            $args['debug_backtrace'] = str_replace(base_path(), '', $backtrace[0]['file']) . ':' . $backtrace[0]['line'];
        }

        if (Request::ajax() === false) {
            // Editors Supported: "phpstorm", "vscode", "vscode-insiders","sublime", "atom"
            $editor = config('ignition.editor', 'vscode');
            echo '<a style="background: lightcoral;font-family: monospace;padding: 4px 8px;border-radius: 4px;font-size: 12px;color: white;text-decoration: none;" href="' . $editor . '://file' . $backtrace[0]['file'] . ':' . $backtrace[0]['line'] . '">Open in Editor (' . $editor . ')</a>';
            // call for dd
            call_user_func_array('dd', $args);
            exit();
        }

        exit(json_encode(array_merge(__response([], 23), [ // debug reaction
            '__dd' => '__dd',
            'data' => array_map(function ($argument) {
                return print_r($argument, true);
            }, $args),
        ])));
    }
}

/*
    * Debugging function for debugging javascript as well as PHP side, work as likely print_r but accepts unlimited parameters
    *
    * @param  N numbers of params can be sent 
    * @return void
    *-------------------------------------------------------- */

if (!function_exists('__pr')) {
    function __pr()
    {
        if (config('app.debug', false) == false) {
            return false;
        }

        $args = func_get_args();

        if (empty($args)) {
            throw new Exception('__pr() No arguments are passed!!');
        }

        $backtrace = debug_backtrace();

        if (isset($backtrace[0])) {
            $args['debug_backtrace'] = str_replace(base_path(), '', $backtrace[0]['file']) . ':' . $backtrace[0]['line'];
        }

        if (Request::ajax() === false) {

            $editor = config('ignition.editor', 'vscode');
            echo '<a style="background: lightcoral;font-family: monospace;padding: 4px 8px;border-radius: 4px;font-size: 12px;color: white;text-decoration: none;" href="' . $editor . '://file' . $backtrace[0]['file'] . ':' . $backtrace[0]['line'] . '">Open in Editor (' . $editor . ')</a>';

            if (class_exists('\Illuminate\Support\Debug\Dumper')) {
                return array_map(function ($argument) {
                    (new \Illuminate\Support\Debug\Dumper())->dump($argument, false);
                }, $args);
            } else if (function_exists('dump')) {
                return dump($args);
            } else {
                return array_map(function ($argument) {
                    print_r($argument, false);
                }, $args);
            }
        }

        return config([
            'app.__pr.' . count(config('app.__pr', [])) => array_map(function ($argument) {
                return print_r($argument, true);
            }, $args)
        ]);
    }
}

/*
    * Log helper
    *
    * @param  N numbers of params can be sent 
    * @return void
    * @since - 1.5.3 - 20 SEP 2018
    *-------------------------------------------------------- */

if (!function_exists('__logDebug')) {
    function __logDebug()
    {
        if (config('app.debug', false) == false) {
            return false;
        }
        $args = func_get_args();

        if (empty($args)) {
            throw new Exception('__logDebug() No arguments are passed!!');
        }

        $backtrace = debug_backtrace();

        if (isset($backtrace[0])) {
            $args['debug_backtrace'] = " logged @ file --------------->  " . str_replace(base_path(), '', $backtrace[0]['file']) . ':' . $backtrace[0]['line'];
        }

        return array_map(function ($argument) {
            if (is_object($argument)) {
                Log::info('Following array is converted from Object for log.');
                Log::debug((array) $argument);
            } else {
                Log::debug($argument);
            }
        }, $args);

        return Log::debug($args);
    }
}

/*
    * Debugging function for debugging javascript
    *
    * @param  N numbers of params can be sent 
    * @return void
    *-------------------------------------------------------- */

if (!function_exists('__clog')) {
    function __clog()
    {
        if (config('app.debug', false) == false) {
            return false;
        }

        $args = func_get_args();

        if (empty($args)) {
            throw new Exception('__clog() No arguments are passed!!');
        }

        $backtrace = debug_backtrace();

        if (isset($backtrace[0])) {
            $args['debug_backtrace'] = str_replace(base_path(), '', $backtrace[0]['file']) . ':' . $backtrace[0]['line'];
        }

        return config([
            'app.__clog.' . count(config('app.__clog', [])) => array_map(function ($argument) {
                return print_r($argument, true);
            }, $args)
        ]);
    }
}

/*
    * Utility function to create array of nested array items strings (Concating parent key in to child key) & assign values to it.
    * 
    * @param  $inputArray raw nested array 
    * @param  $requestedJoiner joiner or word for string concat 
    * @param  $prepend prepend string
    * @param  $allStages if you want to create an array item for every stage 
    * 
    * @return void
    *-------------------------------------------------------- */
if (!function_exists('__nestedKeyValues')) {
    function __nestedKeyValues(array $inputArray, $requestedJoiner = '.', $prepend = null, $allStages = false)
    {
        $formattedArray = [];

        foreach ($inputArray as $key => $value) {
            $joiner = ($prepend == null) ? '' : $requestedJoiner;

            // if array run this again to grab the child items to process
            if (is_array($value)) {
                if ($allStages === true) {
                    array_push($formattedArray, $prepend);
                }

                $formattedArray = array_merge($formattedArray, __nestedKeyValues($value, $requestedJoiner, $prepend . $joiner . $key, $allStages));
            } else {
                // if key is not string push item in to array with required 
                if (is_string($key) === false) {
                    if (is_string($value) === true) {
                        array_push($formattedArray, $prepend . $joiner . $value);
                    } else {
                        array_push($formattedArray, $value);
                    }
                } else {
                    // if want to have specific key
                    if (is_string($value) and substr($value, 0, 4) === 'key@') {
                        $formattedArray[substr($value, 4)] = $prepend . $joiner . $key;
                    } else {
                        $formattedArray[$prepend . $joiner . $key] = $value;
                    }
                }
            }
        }

        unset($prepend, $joiner, $requestedJoiner, $prepend, $allStages, $inputArray);

        return $formattedArray;
    }
}
/*
    * Create JSON object for all HTTP request.
    *
    * @param  array $data 
    * @return JSON Object.
    *-------------------------------------------------------- */
if (!function_exists('__secureApiResponse')) {
    function __secureApiResponse($data, $reactionCode = 1)
    {
        $data['__secureOutput'] = true;
        return __apiResponse($data, $reactionCode);
    }
}
// non encrypted
if (!function_exists('__apiResponse')) {
    function __apiResponse($data, $reactionCode = 1)
    {
        if ($reactionCode === 21 and isset($data['redirect_to'])) {
            // if not ajax redirect from here
            if (!request()->ajax()) {
                return redirect($data['redirect_to']);
            }
        }

        if (
            isset($data['__useNativeJsonEncode'])
            and $data['__useNativeJsonEncode'] === true
        ) {
            return json_encode(__response($data, $reactionCode));
        }

        if (
            isset($data['__secureOutput'])
            and $data['__secureOutput'] === true and !config('app.debug')
        ) {

            array_pull($data, '__secureOutput');

            $data = [
                '__maskedData' => YesSecurity::encryptLongRSA(
                    __response($data, $reactionCode)
                )
            ];

            unset($encryptedString, $reactionCode, $jsonStringsCollection);
        } else {
            $data = __response($data, $reactionCode);
        }

        return Response::json($data);
    }
}

/*
    * Echo JSON API response.
    *
    * @param  array $data 
    * @return JSON Object.
    *-------------------------------------------------------- */

if (!function_exists('__response')) {
    function __response($data, $reactionCode = 1)
    {
        if (Session::has('additional')) {
            $data['additional'] = Session::get('additional');
        }

        if (config('app.additional')) {
            $data['additional'] = config('app.additional');
        }

        $responseData = [
            //  'data' => $data,
            'response_token' => (int) Request::get('fresh'),
            'reaction' => $reactionCode,
            'incident' => isset($data['incident']) ? $data['incident'] : null,
        ];

        if (array_has($data, 'dataTableResponse')) {
            $responseData = array_merge($responseData, $data);
        } else {
            $responseData['data'] = $data;
        }

        if (Session::has('additional')) {
            $responseData['additional'] = Session::get('additional');
        }

        if (config('app.additional')) {
            $responseData['additional'] = config('app.additional');
            config(['app.additional' => null]);
        }

        // __pr() to print in console
        if (config('app.debug', false) == true) {
            $prSessItemName = '__pr';
            if (config('app.' . $prSessItemName)) {
                $responseData['__dd'] = true;
                // set for response              
                $responseData[$prSessItemName] = config('app.' . $prSessItemName, []);
                config(['app.' . $prSessItemName => []]);
            }

            $clogSessItemName = '__clog';

            if (config('app.' . $clogSessItemName)) {
                $responseData['__dd'] = true;
                // set for response              
                $responseData[$clogSessItemName] = config('app.' . $clogSessItemName, []);
                //reset the __clog items in config
                config(['app.' . $clogSessItemName => []]);
            }

            // email view debugging
            if (env('MAIL_VIEW_DEBUG', false) == true) {
                $testEmailViewSessName = '__emailDebugView';
                if (config('app.' . $testEmailViewSessName)) {
                    $responseData[$testEmailViewSessName] = config('app.' . $testEmailViewSessName, []);
                    //reset the testEmailViewSessName items in config
                    config(['app.' . $testEmailViewSessName => []]);
                }
            }
        }

        return $responseData;
    }
}

/*
    * Customized GetText string
    *
    * @param string $string
    * @param array $replaceValues
    * 
    * @return string.
    *-------------------------------------------------------- */

if (!function_exists('__') and !config('__tech.gettext_fallback')) {
    function __($string, $replaceValues = [])
    {
        if (function_exists('gettext') and getenv('LC_ALL') !== false) {
            $string = gettext($string);
        }

        // Check if replaceValues exist
        if (!empty($replaceValues) and is_array($replaceValues)) {
            $string = strtr($string, $replaceValues);
        }

        return $string;
    }
}

/*
    * Generating public js/css links 
    *
    * @param string/array $file - file path from public path
    * @param array $generateTag - if you want to generate script/link tags
    * 
    * @return string.
    *-------------------------------------------------------- */

if (!function_exists('__yesset')) {
    function __yesset($file, $generateTag = false, $options = [])
    {
        $options = array_merge([
            'random' => false
        ], $options);

        $filesString = '';
        $files = [];

        // if file is not array add it to array
        if (is_array($file) === false) {
            $files = [$file];
        } else {
            $files = $file;
        }


        foreach ($files as $keyFile) {

            $keyFile = strip_tags(trim($keyFile));

            // find actual files on the system based on the file path/name
            $globFiles = glob($keyFile);

            if (empty($globFiles)) {

                // if debug mode on throw an exception
                if (config('app.debug', false) === true) {
                    throw new Exception('Yesset file not found - ' . $keyFile . ' 
                        Check * in file name.');
                } else {
                    // if not just create file name;
                    $getFileName = $keyFile;

                    // generate url based on file name & path
                    $fileString = asset($getFileName);
                }
            } else {
                // we need to get first item out of it.
                $getFileName = $globFiles[0];
                // if randomly any one if required
                if ($options['random'] === true) {
                    $getFileName = $globFiles[rand(0, count($globFiles) - 1)];
                }

                // generate url based on file name & path
                // also append modified date to know the file has been changed.
                $fileString = asset($getFileName) . '?VER=' . md5(filemtime($getFileName));
            }

            // generate tags based on file extension 
            // if file is array or generateTag is true
            if ((is_array($file) === true)
                or ($generateTag === true)
            ) {

                // get last 3 character from file name mostly file extension
                $fileExt = strtolower(substr($getFileName, -3));

                switch ($fileExt) {
                        // script tag generation for JS file
                    case '.js':
                        $filesString .= '<script type="text/javascript" src="' . $fileString . '"></script>' . PHP_EOL;
                        break;
                        // link tag generation for CSS file
                    case 'css':
                        $filesString .= '<link href="' . $fileString . '" rel="stylesheet" type="text/css">' . PHP_EOL;
                        break;

                    default:
                        $filesString .=  $fileString;
                }

                continue;
            }
            // if its string just return it.
            $filesString =  $fileString;
        }

        unset($files, $file, $generateTag);

        return $filesString;
    }
}

/*
      * Process response & send API response
      *
      * @param Integer  $engineReaction - Engine reaction 
      * @param Array    $responses      - Response Messages as per reaction code
      * @param Array    $data           - Additional Data for success
      * 
      * @return array
      *---------------------------------------------------------------- */

if (!function_exists('__secureProcessResponse')) {
    function __secureProcessResponse(
        $engineReaction,
        $messageResponses = [],
        $data = [],
        $appendEngineData = false
    ) {
        $data['__secureOutput'] = true;

        return __processResponse(
            $engineReaction,
            $messageResponses,
            $data,
            $appendEngineData
        );
    }
}

if (!function_exists('__processResponse')) {
    function __processResponse(
        $engineReaction,
        $messageResponses = [],
        $data = [],
        $appendEngineData = false
    ) {
        if (__isValidReactionCode($engineReaction) === true) {
            return __apiResponse($data, $engineReaction);
        }

        if (is_array($engineReaction) === false or (array_key_exists(
                'reaction_code',
                $engineReaction
            ) === false
            and array_key_exists(
                'data',
                $engineReaction
            ) === false
            and array_key_exists(
                'message',
                $engineReaction
            ) === false)) {
            throw new Exception('__processResponse:: Invalid Engine Reaction');
        }

        $reactionCode = $engineReaction['reaction_code'];
        $reactionMessage = $engineReaction['message'];

        // Use message if sent from EngineReaction
        if (__isEmpty($reactionMessage) === false) {
            $data['message'] = $reactionMessage;
            // else use process response messages
        } elseif ($messageResponses and array_key_exists($reactionCode, $messageResponses)) {
            $data['message'] = $messageResponses[$reactionCode];
        }

        $dataFromReaction = isset($engineReaction['data']) ? $engineReaction['data'] : [];

        if ($data === true or $appendEngineData === true) {
            if (is_array($data) === false or empty($data) === true) {
                $data = [];
            }

            if (__isEmpty($dataFromReaction) === false) {
                if (
                    is_array($dataFromReaction)
                    or is_object($dataFromReaction)
                ) {
                    $data = array_merge($data, (array) $dataFromReaction);
                }
            }
        }

        $data['incident'] = isset($dataFromReaction['incident']) ? $dataFromReaction['incident'] : null;

        return __apiResponse($data, $reactionCode);
    }
}

/*
      * Check isset & __isEmpty & return the result based on values sent
      *
      * @param Mixed  $data  - Mixed data - Note: Should no used direct function etc
      * @param Mixed  $ifSetValue  - Value if result is true
      * @param Mixed  $ifNotSetValue  - Value if result is false
      * 
      * @return array
      *---------------------------------------------------------------- */

if (!function_exists('__ifIsset')) {
    function __ifIsset(&$data, $ifSetValue = '', $ifNotSetValue = '')
    {
        // check if value isset & not empty
        if ((isset($data) === true) and (__isEmpty($data) === false)) {
            if (!is_string($ifSetValue) and is_callable($ifSetValue) === true) {
                return call_user_func($ifSetValue, $data);
            } elseif ($ifSetValue === true) {
                return $data;
            } elseif ($ifSetValue !== '') {
                return $ifSetValue;
            }

            return true;
        } else {
            if (!is_string($ifNotSetValue) and is_callable($ifNotSetValue) === true) {
                return call_user_func($ifNotSetValue);
            } elseif ($ifNotSetValue !== '') {
                return $ifNotSetValue;
            }

            return false;
        }
    }
}

/*
      * Customized isEmpty
      *
      * @param Mixed  $data  - Mixed data
      * 
      * @return array
      *---------------------------------------------------------------- */

if (!function_exists('__isEmpty')) {
    function __isEmpty($data)
    {
        if (empty($data) === false) {
            if (($data instanceof Illuminate\Database\Eloquent\Collection
                    or $data instanceof Illuminate\Pagination\Paginator
                    or $data instanceof Illuminate\Pagination\LengthAwarePaginator
                    or $data instanceof Illuminate\Support\Collection)
                and ($data->count() <= 0)
            ) {
                return true;
            } elseif (is_object($data)) {
                $data = (array) $data;

                return empty($data);
            }

            return false;
        }

        return true;
    }
}

/*
      * Customized isEmpty
      *
      * @param Integer  $reactionCode  - Reaction Code
      * 
      * @return bool
      *---------------------------------------------------------------- */

if (!function_exists('__isValidReactionCode')) {
    function __isValidReactionCode($reactionCode)
    {
        if (
            is_integer($reactionCode) === true
            and array_key_exists(
                $reactionCode,
                config('__tech.reaction_codes')
            ) === true
        ) {
            return true;
        }

        return false;
    }
}

/*
    * Re Indexing using array value based on key
    *
    * @param array $array
    * @param string $valueKey 
    * @param closure function $closure
    * @since - 29 JUN 2017
    * @example uses
        __reIndexArray([
            ['id' => '9e0fec39-dd53-4636-b628-f0123f05b318', name= 'xyz'],
            ['id' => '8e0fec39-ed53-5636-c628-f0123f05b618', name= 'abc']
        ], 'id', function($item, $valueKey) {
               $item['name'] =>  strtoupper($item['name']);
               return $item;
        });

        // Result
            [
                '9e0fec39-dd53-4636-b628-f0123f05b318' => [
                    'id' => '9e0fec39-dd53-4636-b628-f0123f05b318',
                    'name' => 'Xyz'
                ],
                '8e0fec39-ed53-5636-c628-f0123f05b618' => [
                    'id' => '8e0fec39-ed53-5636-c628-f0123f05b618',
                    'name' => 'Abc'
                ]
            ]

    * @return array.
    *-------------------------------------------------------- */

if (!function_exists('__reIndexArray')) {
    function __reIndexArray(array $array, $valueKey, $closure = null)
    {
        $newArray = [];
        if (!empty($array)) {
            foreach ($array as $item) {
                if (is_array($item)) {
                    $itemForKey = array_get($item, $valueKey);
                    if ($itemForKey and (is_string($itemForKey)
                        or is_numeric($itemForKey))) {
                        if ($closure and is_callable($closure)) {
                            $newArray[$itemForKey] = call_user_func($closure, $item, $valueKey);
                        } else {
                            $newArray[$itemForKey] = $item;
                        }
                    }
                }
            }
        }
        unset($array, $valueKey, $closure);
        return $newArray;
    }
}

/*
    * Check if access available
    *
    * @param string $accessId
    * 
    * @return bool.
    *-------------------------------------------------------- */

if (!function_exists('__canAccess')) {
    function __canAccess($accessId = null)
    {

        if (
            YesAuthority::check($accessId) === true
            or YesAuthority::isPublicAccess($accessId)
        ) {

            return true;
        }

        return false;
    }
}

/*
    * Check if access available
    *
    * @param string $accessId
    * 
    * @return bool.
    *-------------------------------------------------------- */
if (!function_exists('__canPublicAccess')) {
    function __canPublicAccess($accessId = null)
    {
        return YesAuthority::isPublicAccess($accessId);
    }
}

/*
      * listen Query events 
      *---------------------------------------------------------------- */
if ((config('app.debug', false) == true)
    and env('APP_DB_LOG', false) == true
) {

    Event::listen('Illuminate\Database\Events\QueryExecuted', function ($event) {

        $bindings = $event->bindings;

        if (count($bindings) > 0) {
            // Format binding data for sql insertion
            foreach ($bindings as $i => $binding) {
                if ($binding instanceof \DateTime) {
                    $bindings[$i] = $binding->format('\'Y-m-d H:i:s\'');
                } elseif (is_string($binding)) {
                    $bindings[$i] = "'$binding'";
                }
            }

            $clogItems['SQL__Bindings'] = implode(', ', $bindings);
        }

        // Insert bindings into query
        $query = str_replace(array('%', '?'), array('%%', '%s'), $event->sql);
        $query = vsprintf($query, $bindings);

        $clogItems = ['SQL__Query' => $query];

        $clogItems['SQL__TimeTaken'] = $event->time;

        __clog($clogItems);
    });
}

/*
  * Update config file 
  *
  * @param string $configFile - without .php
  * @param mixed $itemName 
  * @param mixed $itemValue 
  *
  * @return mixed.
  *-------------------------------------------------------- */
if (!function_exists('updateCreateArrayFileItem')) {
    function updateCreateArrayFileItem($configFile, $itemName, $itemValue, $options = [])
    {
        $actualFileName = $configFile . '.php';
        if (!file_exists($actualFileName)) {
            $fh = fopen($actualFileName, 'a');
            fwrite($fh, "<?php
    return [];");
            fclose($fh);
        }

        $options = array_merge([
            'prepend_comment' => ''
        ], $options);

        // $configFile = str_replace('.php', '', $configFile);
        $configFileArray = require $actualFileName;
        $updatedArray = array_set($configFileArray, $itemName, $itemValue);
        $arrayString = '<?php
            ' . $options['prepend_comment'] . '
    return ';
        $arrayString .= var_export($configFileArray, true) . ";";

        /*  config([
                $configFile => $configFileArray
            ]); */

        file_put_contents($actualFileName, $arrayString);

        return $updatedArray;
    }
}
