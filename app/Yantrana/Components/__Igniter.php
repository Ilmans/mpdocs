<?php namespace App\Yantrana\Components;

use App\Yantrana\Base\BaseController;

use Lang;
use JavaScript;
use Illuminate\Support\Facades\Route;

use YesSecurity;
use Illuminate\Http\Request;
use Captcha;

class __Igniter extends BaseController
{

    /*
    |--------------------------------------------------------------------------
    | Welcome Controller
    |--------------------------------------------------------------------------
    |
    | This controller renders the "marketing page" for the application and
    | is configured to only allow guests. Like most of the other sample
    | controllers, you are free to modify or remove it as you desire.
    |
    */

    /**
     * @var array - Protected Views
     */
    protected $protectedViews = [
        //'user.change-email',
    ];

    /**
     * Get manage application master view template.
     *---------------------------------------------------------------- */
    public function manageIndex()
    {   
        JavaScript::put($this->prepareForBrowser());
        
        return $this->loadView('manage-master');
    }

    

    /**
     * Get not found error view template.
     *---------------------------------------------------------------- */
    public function errorNotFound()
    {
        return $this->loadPublicView('errors.public-not-found');
    }

    /**
     * Get not found error view template.
     *---------------------------------------------------------------- */
    public function getTemplate($viewName)
    {
        if (in_array($viewName, $this->protectedViews)) {
            if (isLoggedIn() === false) {
                return __apiResponse([
                        'message' => __tr('Please login to complete request.'),
                        'auth_info' => getUserAuthInfo(9),
                    ], 9);
            }
        }

        if (array_key_exists($viewName, $this->protectedViews)) {
            if ($this->protectedViews[$viewName] == 1) {
                if (isAdmin() === false) {
                    return __apiResponse([
                        'message' => __tr('Unauthorized.'),
                        'auth_info' => getUserAuthInfo(11),
                    ], 11);
                }
            }
        }

        return view($viewName);
    }

    /**
      * Get email template
      *
      * @param string $viewName
      *
      * @return void
      *---------------------------------------------------------------- */
    
    public function emailTemplate($viewName)
    {
        return view('emails.index', [
                    'emailsTemplate' => $viewName,
                ]);
    }

    /**
     * ChangeLocale - It also managed from index.php.
     *---------------------------------------------------------------- */
    public function cssStyle()
    {
        return response(getConfigurationSettings('custom_css'))->header('Content-Type', 'text/css');
    }

    /**
      * Get ac master view template
      *
      * @return void
      *---------------------------------------------------------------- */
    
    public function baseData()
    {   
        return __processResponse(1, [], array_merge($this->prepareForBrowser(), [
             'auth_info' => getUserAuthInfo()
        ]));
    }

    /**
      * get Request Initialization
      *
      * @return void
      *---------------------------------------------------------------- */
    function getRequestInitialization() {
        return __processResponse(1, []);
    }

    /**
     * Change Theme Color
     *---------------------------------------------------------------- */
    public function changeThemeColor($colorName)
    {
        $themeColors = config('__tech.theme_colors');
        $default = [
            'background' => getConfigurationSettings('header_background_color'),
            'text' => getConfigurationSettings('header_text_link_color')
        ];        
        $themeColors = array_add($themeColors, 'default', $default);

        if (!__isEmpty($colorName) 
            and is_string($colorName)
            and array_key_exists($colorName, $themeColors)) {
            $colorNames = $themeColors[$colorName];
            session([
                'background_theme_color' => $colorNames['background'],
                'text_theme_color' => $colorNames['text']
            ]);
            return $colorNames;
        }

        return false;
    }
}
