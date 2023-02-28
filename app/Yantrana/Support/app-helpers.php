<?php

use Carbon\Carbon;
use App\Yantrana\Components\User\Models\UserAuthorityModel;
use App\Yantrana\Components\User\Models\User;
use App\Yantrana\Components\Project\Models\ProjectModel;

/*
    |--------------------------------------------------------------------------
    | App Helpers
    |--------------------------------------------------------------------------
    |
    */

/*
      * Get the technical items from tech items
      *
      * @param string   $key
      * @param mixed    $requireKeys
      *
      * @return mixed
      *-------------------------------------------------------- */

if (!function_exists('configItem')) {
    function configItem($key, $requireKeys = null)
    {
        if (!__isEmpty($requireKeys) and !is_array($requireKeys)) {
            return config('__tech.' . $key . '.' . $requireKeys);
        }

        $geItem = array_get(config('__tech'), $key);

        if (!__isEmpty($requireKeys) and is_array($requireKeys)) {
            return array_intersect_key($geItem, array_flip($requireKeys));
        }

        return $geItem;
    }
}

/*
    * Get Auth User Info
    *
    * @return number.
    *-------------------------------------------------------- */
if (!function_exists('getAuthUser')) {

    function getAuthUser()
    {
        return Auth::user();
    }
}

/*
      * Get the auth id
      *
      * @return mixed
      *-------------------------------------------------------- */

if (!function_exists('authUID')) {
    function authUID()
    {
        return getAuthUser()->_uid;
    }
}

/*
      * Get user ID
      *
      * @return number.
      *-------------------------------------------------------- */

if (!function_exists('getUserID')) {
    function getUserID()
    {
        return getAuthUser()->_id;
    }
}

/*
      * Get user Authority Id
      *
      * @return number.
      *-------------------------------------------------------- */

if (!function_exists('getUserAuthorityId')) {
    function getUserAuthorityId()
    {
        $userAuthInfo = getUserAuthInfo();

        if ($userAuthInfo['authorized']) {
            return $userAuthInfo['user_authority_id'];
        }

        return null;
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

if (!function_exists('__tr')) {
    function __tr($string, $replaceValues = [])
    {
        if (configItem('gettext_fallback')) {
            $string = T_gettext($string);
        }

        // Check if replaceValues exist
        if (!empty($replaceValues) and is_array($replaceValues)) {
            $string = strtr($string, $replaceValues);
        }

        return $string;
    }
}




/*
      * Get user authentication
      *
      * @return array
      *---------------------------------------------------------------- */

if (!function_exists('getUserAuthInfo')) {
    function getUserAuthInfo($itemKey = null)
    {
        $userAuthInfo = [
            'authorized' => false,
            'reaction_code' => 9,
        ];

        if (Auth::check()) {
            $userInfo = User::where('users._id', '=', getUserID())
                ->leftJoin('user_authorities', 'users._id', 'user_authorities.users__id')
                ->leftJoin('user_roles', 'user_authorities.user_roles__id', 'user_roles._id')
                ->leftJoin('user_profiles', 'users._id', 'user_profiles.users__id')
                ->leftJoin('countries', 'user_profiles.countries__id', 'countries._id')
                ->select(
                    __nestedKeyValues([
                        'users' => [
                            '_id', '_uid', 'email', 'status', 'first_name', 'last_name', 'designation',
                        ],
                        'user_roles' => ['title as user_role_title'],
                        'user_authorities' => ['user_roles__id', '_id as user_authority_id'],
                        'user_profiles' => [
                            'address_line_1', 'address_line_2', 'profile_picture'
                        ],
                        'countries' => ['name as country_name']
                    ])
                )
                ->first();

            $userId = $userInfo->_id;
            $profileImg = getProfileImage($userInfo->profile_picture, $userInfo->_uid);
            $authenticationToken = md5(uniqid(true));

            $userAuthInfo = [
                'authorization_token' => $authenticationToken,
                'authorized'          => true,
                'reaction_code'       => (!__isEmpty($itemKey) and is_numeric($itemKey))
                    ? $itemKey : 10,
                'profile'   => [
                    'full_name' => $userInfo->first_name . ' ' . $userInfo->last_name,
                    'email'     => $userInfo->email,
                    'username'  => $userInfo->username
                ],
                'personnel'     => $userId,
                'designation'   => (isset($userInfo->user_roles__id)) ? $userInfo->user_roles__id : null,
                'user_authority_id' => $userInfo->user_authority_id,
                'designation_title'   => (isset($userInfo->user_role_title)) ? $userInfo->user_role_title : '',
                'profile_image'        =>  $profileImg,
                'country_name'        => $userInfo->country_name
            ];

            if ($itemKey and array_key_exists($itemKey, $userAuthInfo)) {
                return $userAuthInfo[$itemKey];
            }
        }

        return $userAuthInfo;
    }
}

/*
      * Check if logged in user is admin
      *
      * @return boolean
      *-------------------------------------------------------- */

if (!function_exists('isAdmin')) {
    function isAdmin()
    {
        // Check if user logged in
        if (isLoggedIn()) {
            $userRole = UserAuthorityModel::where('users__id', '=', getUserID())->first();
            if ($userRole->user_roles__id === 1) {

                return true;
            }
        }

        return false;
    }
}


/*
      * Get user ID
      *
      * @return number.
      *-------------------------------------------------------- */

if (!function_exists('isActiveUser')) {
    function isActiveUser()
    {
        if (!empty(getAuthUser())) {
            if (Auth::user()->status != 1) {
                Session::flash(
                    'invalidUserMessage',
                    __tr('Invalid request please contact administrator.')
                );

                Auth::logout();

                return true;
            }
        }

        return false;
    }
}

/*
      * Check if user logged in application
      *
      * @return boolean
      *-------------------------------------------------------- */

if (!function_exists('isLoggedIn')) {
    function isLoggedIn()
    {
        isActiveUser();

        return Auth::check();
    }
}


/**
 * Convert date with setting time zone
 *
 * @param string $rawDate
 *
 * @return date
 *-------------------------------------------------------- */

if (!function_exists('storeTimezone')) {
    function storeTimezone($rawDate)
    {
        $carbonDate = Carbon::parse($rawDate);

        return $carbonDate;
    }
}



/*
      * Get formatted date time from passed raw date using timezone
      *
      * @param string $rawDateTime
      *
      * @return date
      *-------------------------------------------------------- */

if (!function_exists('formatDateTime')) {
    function formatDateTime($rawDateTime, $format = 'l jS F Y g:i:s a')
    {
        if (__isEmpty($rawDateTime)) {
            return '';
        }

        $dateUpdatedTimezone = storeTimezone($rawDateTime);

        // if format set as false then return carbon object
        if ($format === false) {
            return $dateUpdatedTimezone;
        }

        return $dateUpdatedTimezone->format($format);
    }
}

/*
      * Get formatted date time from passed raw date using timezone
      *
      * @param string $rawDateTime
      *
      * @return date
      *-------------------------------------------------------- */

if (!function_exists('humanReadableFormat')) {
    function humanReadableFormat($rawDateTime)
    {
        if (__isEmpty($rawDateTime)) {
            return '';
        }

        $dateUpdatedTimezone = storeTimezone($rawDateTime);

        return $dateUpdatedTimezone->diffForHumans();
    }
}

/*
    * Add activity log entry
    *
    * @param string $activity
    *
    * @return void.
    *-------------------------------------------------------- */

if (!function_exists('activityLog')) {
    function activityLog($entityType, $entityId, $actionType, $itemName = null, $description = '')
    {
        $userAuthInfo = getUserAuthInfo();
        $userId       = Auth::user();

        if (!__isEmpty($userId)) {

            $userAuthority = UserAuthorityModel::where('users__id', $userId->_id)->first();

            $activity = [
                'user_info'      => [
                    'id'        =>  getUserID(),
                    'full_name' => (isset($userAuthInfo['profile']['full_name']))
                        ? $userAuthInfo['profile']['full_name']
                        : null,
                    'email'     => (isset($userAuthInfo['profile']['email']))
                        ? $userAuthInfo['profile']['email']
                        : null,
                    'username'  =>  $userAuthInfo['profile']['username']
                ],
                'ip'             => Request::ip(),
                'itemName'         => $itemName,
                'description'    => $description
            ];

            App\Yantrana\Components\ActivityLog\Models\ActivityLogModel::create([
                '__data'        => $activity,
                'created_at'    => Carbon::now(),
                'action_type'    => $actionType,
                'entity_type'    => $entityType,
                'entity_id'        => $entityId,
                'user_id'       => getUserID(),
                'user_role_id'  => $userAuthority->user_roles__id,
                'eo_uid'        => entityOwnershipId()
            ]);
        }
    }
}



/*
      * Get no thumb image URL
      *
      * @return string
      *-------------------------------------------------------- */

if (!function_exists('noUserThumbImageURL')) {
    function noUserThumbImageURL()
    {
        return url('/dist/imgs/no-user-thumb-icon.png');
    }
}

/*
      * Get the technical items from tech items
      *
      * @param string   $key
      * @param array    $requireKeys
      * @param array    $options.
      *
      * @return mixed
      *-------------------------------------------------------- */

if (!function_exists('techItem')) {
    function techItem($key, $requireKeys, $options = [])
    {
        $techItems = config('__tech.tech_items');

        $requestedItems = config('__tech.' . $key);

        // if requested items key not exist then return blank array
        if (__isEmpty($requestedItems)) {
            return [];
        }

        $requestedTechItems = array_only($techItems, $requestedItems);

        if (
            !__isEmpty($options)
            and array_key_exists('only', $options)
            and is_array($options['only'])
        ) {
            $requestedTechItems = array_only($requestedTechItems, $options['only']);
        }

        $items = [];

        if (!__isEmpty($requestedTechItems)) {
            foreach ($requestedTechItems as $key => $item) {
                $items[] = array_only($item, $requireKeys);
            }
        }

        return $items;
    }
}

/*
      * Get the technical item string using passed item
      *
      * @param string   $itemKey
      * @param string   $stringKey
      *
      * @return mixed
      *-------------------------------------------------------- */

if (!function_exists('techItemString')) {
    function techItemString($itemKey, $stringKey = 'title')
    {
        $techItem = config('__tech.tech_items.' . $itemKey);

        return array_get($techItem, $stringKey);

        // if requested item not found then return blank string
        //  return !__isEmpty($techItem) ? $techItem->get($stringKey) : '';
    }
}


/*
      * Get shop items media storage path
      *
      * @return string path.
      *-------------------------------------------------------- */

if (!function_exists('mediaStorage')) {
    function mediaStorage($item, $dynamicItems = null, $generateUrl = false)
    {
        $storagePaths = __nestedKeyValues(
            config('__tech.storage_paths'),
            '/'
        );

        $itemPath = $storagePaths[$item];

        if ($itemPath) {
            if ($dynamicItems and !is_array($dynamicItems)) {
                $itemPath = strtr($itemPath, ['{_uid}' => $dynamicItems]);
            } elseif ($dynamicItems and is_array($dynamicItems)) {
                $itemPath = strtr($itemPath, $dynamicItems);
            }

            if ($generateUrl) {
                // return str_replace(config('__tech.ignore_storage_paths'),'', url($itemPath));
                return url($itemPath);
            }

            return public_path($itemPath);
        }
    }
}

/*
      * Get shop items media storage URL
      *
      * @return string path.
      *-------------------------------------------------------- */

if (!function_exists('mediaUrl')) {
    function mediaUrl($item, $dynamicItems = null, $generateUrl = false)
    {
        return mediaStorage($item, $dynamicItems, true);
    }
}

/*
      * Get Static Assets Path
      *
      * @param string $fileName
      * @param string $folderName
      *
      * @return string path.
      *-------------------------------------------------------- */

if (!function_exists('getStaticAssetsPath')) {
    function getStaticAssetsPath($fileName = null, $folderName = null)
    {
        $staticAssetsUrl = url('static-assets/imgs/');

        if (__isEmpty($fileName) and __isEmpty($folderName)) {
            return $staticAssetsUrl;
        }

        if ($folderName) {
            return $staticAssetsUrl . '/' . $folderName . '/' . $fileName;
        }

        return $staticAssetsUrl . '/' . $fileName;
    }
}

/*
    * get setting items
    *
    * @param string $name
    *
    * @return void
    *---------------------------------------------------------------- */

if (!function_exists('getConfigurationSettings')) {
    function getConfigurationSettings($name, $details = false)
    {
        $configurationNames = config('__settings.items');
        $logoDirUrl = mediaUrl('logo');
        $mediaLogoStoragePath = mediaStorage('logo');
        $faviconDirUrl = mediaUrl('favicon');
        $mediaFaviconStoragePath = mediaStorage('favicon');

        $settings = Cache::rememberForever('cache.all.configurations', function () {
            $getSettings = App\Yantrana\Components\Configuration\Models\Configuration::all();

            $storeSettings = [];

            foreach ($getSettings as $setting) {
                $value = getDataType($setting);

                $storeSettings[$setting->name] = $value;
            }

            unset($getSettings);

            return $storeSettings;
        });

        $settings['selected_background_theme_color'] = session('background_theme_color');
        $settings['selected_text_theme_color'] = session('text_theme_color');


        // Set here dynamic logo
        if (__ifIsset($settings['logo_image']) and File::exists($mediaLogoStoragePath . '/' . $settings['logo_image'])) {
            $logoImage = $settings['logo_image'];

            $settings['logo_image_url'] = $logoDirUrl . '/' . $logoImage . '?logover=' . @filemtime($mediaLogoStoragePath . '/' . $logoImage);
        }

        // Set here dynamic favicon
        if (__ifIsset($settings['favicon_image']) and  File::exists($mediaFaviconStoragePath . '/' . $settings['favicon_image'])) {
            $faviconImage = $settings['favicon_image'];

            // $settings['favicon_image']     = $faviconDirUrl.'/'.$value;

            $settings['favicon_image_url'] = $faviconDirUrl . '/' . $faviconImage . '?faviconver=' . @filemtime($mediaLogoStoragePath . '/' . $faviconImage);
        }


        if (array_key_exists($name, $settings)) {
            return $settings[$name];
        }

        // For Default Logo
        if (($name == 'logo_image') or ($name == 'logo_image_url')) {
            $logoName = $configurationNames['logo_image']['default'];

            $fullLogoPath = getStaticAssetsPath($logoName);

            $defaultSettings['logo_image']     = $fullLogoPath;
            $defaultSettings['logo_image_url'] = $fullLogoPath . '?logover=' . @filemtime($fullLogoPath);

            return $defaultSettings[$name];
        }

        // For Default Favicon
        if (($name == 'favicon_image') or ($name == 'favicon_image_url')) {
            $faviconName = $configurationNames['favicon_image']['default'];

            $fullFaviconPath = getStaticAssetsPath($faviconName);

            $defaultSettings['favicon_image']     = $fullFaviconPath;
            $defaultSettings['favicon_image_url'] = $fullFaviconPath . '?logover=' . @filemtime($fullFaviconPath);

            return $defaultSettings[$name];
        }

        return $configurationNames[$name]['default'];
    }
}

/*
      * get profile path of user
      *
      * @return string
      *---------------------------------------------------------------- */
if (!function_exists('getProfileImage')) {
    function getProfileImage($profileFile, $uid = null)
    {
        $userUid = isset($uid) ? $uid : Auth::user()->_uid;

        if (__isEmpty($profileFile)) {
            return noUserThumbImageURL();
        }

        $profileMedia = mediaStorage('user_photo', [
            '{_uid}'  => $userUid
        ]) . '/' . $profileFile;

        return (file_exists($profileMedia) === true)
            ? mediaUrl('user_photo', ['{_uid}' => $userUid]) . '/' . $profileFile
            : noUserThumbImageURL();
    }
}


/*
      * Get demo mode for Demo of site
      *
      * @return boolean.
      *-------------------------------------------------------- */

if (!function_exists('isDemo')) {
    function isDemo()
    {
        return (env('IS_DEMO_MODE', false)) ? true : false;
    }
}

/*
      * Get entity ownership id
      *
      * @return string.
      *-------------------------------------------------------- */

if (!function_exists('entityOwnershipId')) {
    function entityOwnershipId()
    {
        return configItem('entity_ownership_id');
    }
}

/*
      * Get the data-type of each item
      *
      * @param int $itemId
      *
      * @return string path.
      *-------------------------------------------------------- */

if (!function_exists('getDataType')) {
    function getDataType($setting)
    {
        $configurationNames = config('__settings.items');
        $name  = $setting->name;
        $value = $setting->value;

        if (!__isEmpty($name) and array_key_exists($name, $configurationNames)) {
            $datTypeId = $configurationNames[$name]['data_type'];

            switch ($datTypeId) {
                case 1:
                    return (string) $value;
                    break;
                case 2:
                    return (bool) $value;
                    break;
                case 3:
                    return (int) $value;
                    break;
                case 4:
                    return json_decode($value, true);
                    break;
                default:
                    return $value;
            }
        }
    }
}
/**
 * Get account by subdomain/domain
 *
 * @return boolean
 *---------------------------------------------------------------- */

if (!function_exists('accountDomainId')) {

    function accountDomainId()
    {

        if (isset($_SERVER["HTTP_HOST"]) === false) {
            return null;
        }

        $fullDomain = trim(strip_tags($_SERVER["HTTP_HOST"]));
        $apiDomain = env('API_DOMAIN', '');

        if ($apiDomain and strpos($fullDomain, $apiDomain) !== false) {
            $domainParts = explode('.', $fullDomain);

            return $domainParts[0];
        }

        return $fullDomain;
    }
}

/*
      * sets the Authentication token (jwt)
      *
      * @return boolean.
      *-------------------------------------------------------- */

if (!function_exists('setAuthToken')) {
    function setAuthToken($token)
    {
        if ($token == '') {
            $minutes = time() - 3600;
        } else {
            $minutes = YesTokenAuth::getExpirationPeriod();
        }

        Cookie::queue(Cookie::make('auth_access_token', $token, $minutes));
    }
}

/*
    * Find item string
    *
    * @return string.
    *-------------------------------------------------------- */

if (!function_exists('configItemString')) {

    function configItemString($configData, $identifier, $returnColumn = 'title')
    {
        if (!is_array($configData) and is_string($configData)) {
            $configData = configItem($configData);
        }

        $foundedRecord = array_where($configData, function ($value, $key) use ($identifier) {

            return ($value['id'] === $identifier) ? $value : [];
        });

        if (__isEmpty($foundedRecord)) {
            return false;
        }

        return array_get(array_values($foundedRecord)[0], $returnColumn);
    }
}

/*
    * Find item string
    *
    * @return string.
    *-------------------------------------------------------- */

if (!function_exists('getClientAppUrl')) {

    function getClientAppUrl($identifier)
    {
        $urls = configItem('client_urls');

        return route('manage.app') . array_get($urls, $identifier);
    }
}

/*
    * generate meta 
    *
    * @return string.
    *-------------------------------------------------------- */

if (!function_exists('getUniqueWords')) {

    function getUniqueWords($string, $length = 160, $char = ' ')
    {
        $array = array_filter(explode(' ', preg_replace('/[^A-Za-z0-9\-]/', ' ', $string)));

        $result = implode($char, array_unique($array));

        if (strlen($result) > 160) {
            return str_limit($result, $length);
        }

        return $result;
    }
}

/*
      * sets the cookie
      *
      * @return boolean.
      *-------------------------------------------------------- */

if (!function_exists('setServerCookie')) {
    function setServerCookie($name, $value, $minutes = (24 * 24 * 60))
    {
        if (__isEmpty($minutes)) {
            $minutes = time() - 3600;
        }

        if (is_array($value)) {
            $value = json_encode($value);
        }

        $path = '';
        $domain = accountDomainId();
        $secure = false;
        $httpOnly = true;
        Cookie::queue(Cookie::make($name, $value, $minutes, $path, $domain, $secure, $httpOnly));
    }
}

/*
      * sets the cookie
      *
      * @return boolean.
      *-------------------------------------------------------- */

if (!function_exists('getCookie')) {
    function getCookie($name)
    {
        $cookieData = request()->cookie($name);
        if (!__isEmpty($cookieData)) {
            return $cookieData;
        }

        return false;
    }
}

/*
      * sets the cookie
      *
      * @return boolean.
      *-------------------------------------------------------- */

if (!function_exists('embedIframe')) {
    function embedIframe()
    {
        $scriptUrl = route('embed.script');
        return "<script type='text/javascript' src='" . $scriptUrl . "'></script>";
    }
}

/*
      * sets the cookie
      *
      * @return boolean.
      *-------------------------------------------------------- */

if (!function_exists('articleEmbedIframe')) {
    function articleEmbedIframe()
    {
        $version = uniqid();
        $scriptUrl = route('article.embed.script') . '?version=' . $version;
        return "<script type='text/javascript' src='" . $scriptUrl . "'></script>";
    }
}


if (!function_exists('prepareDropdown')) {
    function prepareDropdown()
    {
        $activeProjects =  ProjectModel::orderBy('updated_at', 'DESC')
            ->with('activeVersions')
            ->get();

        $dropdownContent = [];
        $versions = [];
        $articles = [];

        if (!__isEmpty($activeProjects)) {
            foreach ($activeProjects as $project) {

                $versions = [];
                if (!__isEmpty($project->activeVersions)) {

                    foreach ($project->activeVersions as $ver) {

                        $articles = [];
                        if (!__isEmpty($ver->activeArticles)) {
                            foreach ($ver->activeArticles as $art) {
                                if (!__isEmpty($art->contents)) {
                                    foreach ($art->contents as $key => $content) {
                                        if ($content->languages__id === $project->languages__id) {
                                            $articles[] = [
                                                'slug' => $art->slug,
                                                'title' => $content->title,
                                                'url' => route('doc.view', ['type' => 2, 'projectSlug' => $project->slug, 'versionSlug' => $ver->slug])
                                            ];
                                        }
                                    }
                                }
                            }
                        }

                        //check if articles empty
                        if (!__isEmpty($articles)) {
                            $versions[] = [
                                'version' => $ver->version,
                                'slug' => $ver->slug,
                                'articles' => $articles,
                                'url' => route('doc.view', ['type' => 2, 'projectSlug' => $project->slug, 'versionSlug' => $ver->slug])
                            ];
                        }
                    }
                }

                //check if version empty
                if (!__isEmpty($versions)) {
                    $dropdownContent[] = [
                        'title' => $project->name,
                        'slug' => $project->slug,
                        'versions' => $versions,
                        'url' => route('doc.view', ['type' => 1, 'projectSlug' => $project->slug])
                    ];
                }
            }
        }

        return $dropdownContent;
    }
}

/*
      * Get the technical items from tech items
      *
      * @param string   $key
      * @param mixed    $requireKeys
      *
      * @return mixed
      *-------------------------------------------------------- */

if (!function_exists('buildTree')) {
    function buildTree($pages, $prevId = null)
    {
        if (__isEmpty($pages)) {
            return [];
        }

        $collectData = [];
        $count = 0;

        foreach ($pages as $page) {
            if ($page['previous_articles__id'] === $prevId) {

                if (is_array($page)) {
                    $collectData[] = $page;
                } else {
                    $collectData[] = $page->toArray();
                }

                $children = buildTree($pages, $page['_id']);

                if (!__isEmpty($children)) {
                    $collectData[$count]['children'] = $children;
                }

                $count++;
            }
        }

        return $collectData;
    }
}

/*
      * Get the technical items from tech items
      *
      * @param string   $key
      * @param mixed    $requireKeys
      *
      * @return mixed
      *-------------------------------------------------------- */

if (!function_exists('slugIt')) {
    function slugIt($title, $separator = '-')
    {
        return str_slug($title, $separator);
    }
}

/*
    * find all active parents recursively
    * and also active parents
    *
    * @param (object) $itemCollection.
    * @param (int) $itemID.
    * @param (array) $activeItemsContainer.
    *
    * @return integer
    *------------------------------------------------------------------------ */
if (!function_exists('findArticleParents')) {
    function findArticleParents($itemCollection, $itemID = null, $activeItemsContainer = [], $processedIds = [])
    {
        foreach ($itemCollection as $item) {
            if (($item['_id'] === (int) $itemID) and !in_array($itemID, $processedIds)) {
                $processedIds[] =  $itemID;
                $activeItemsContainer[] = [
                    '_id' => $item['_id'],
                    'slug' => $item['slug']
                ];

                if ($item['previous_articles__id']) {
                    $activeItemsContainer = findArticleParents(
                        $itemCollection,
                        $item['previous_articles__id'],
                        $activeItemsContainer,
                        $processedIds
                    );
                }
            }
        }

        return $activeItemsContainer;
    }
}

/*
    * find all active parents recursively
    * and also active parents
    *
    * @param (object) $itemCollection.
    * @param (int) $itemID.
    * @param (array) $activeItemsContainer.
    *
    * @return integer
    *------------------------------------------------------------------------ */
if (!function_exists('findParents')) {
    function findParents($itemCollection, $itemID = null, $activeItemsContainer = [], $processedIds = [])
    {
        foreach ($itemCollection as $item) {
            if (($item->_id === (int) $itemID )and !in_array($itemID, $processedIds)) {
                $processedIds[] =  $itemID;
                $activeItemsContainer[] = [
                    '_id' => $item->_id,
                    'slug' => $item->slug
                ];

                if ($item->previous_articles__id) {
                    $activeItemsContainer = findParents(
                        $itemCollection,
                        $item->previous_articles__id,
                        $activeItemsContainer,
                        $processedIds
                    );
                }
            }
        }

        return $activeItemsContainer;
    }
}

/*
      * Check if current seeing from embed view
      *
      * @return boolean.
      *-------------------------------------------------------- */

if (!function_exists('isFromEmbedView')) {
    function isFromEmbedView($string)
    {
        if (strlen($string) == 36) {
            return true;
        }

        return false;
    }
}

/*
    * Get article access permission via edit article permission
    *
    * @return boolean value.
    *-------------------------------------------------------- */
if (!function_exists('canThisArticleAccess')) {

    function canThisArticleAccess($articleId = null)
    {
        if (canAccess('manage.article.read.update.data')) {
            return true;
        }

        return false;
    }
}

/*
    * Get article access permission via edit article permission
    *
    * @return boolean value.
    *-------------------------------------------------------- */
if (!function_exists('processLogoutAction')) {

    function processLogoutAction()
    {
        YesTokenAuth::destroyTokenSession();
        config([
            'app.yestoken.jti' => null
        ]);
        \Cookie::queue(\Cookie::forget('auth_access_token'));
        Auth::logout();
    }
}
if (!function_exists('strip_selected_tags')) {
    // http://qnimate.com/remove-html-tags-from-string-using-php/
    function strip_selected_tags($string, $tags = array())
    {
        // $tags = array("p", "i");
        foreach ($tags as $tag) {
            $string = preg_replace("/<\\/?" . $tag . "(.|\\s)*?>/", '', $string);
        }
        echo $string;
    }
}
