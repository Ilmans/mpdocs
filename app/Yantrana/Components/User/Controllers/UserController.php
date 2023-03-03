<?php
/*
* UserController.php - Controller file
*
* This file is part of the User component.
*-----------------------------------------------------------------------------*/

namespace App\Yantrana\Components\User\Controllers;

use JavaScript;
use App\Yantrana\Support\CommonPostRequest as Request;
use App\Yantrana\Base\BaseController;
use App\Yantrana\Components\User\UserEngine;

use App\Yantrana\Components\User\Requests\{
    AddUserRequest,
    EditUserRequest,
    AddCountryRequest,
    UserLoginRequest,
    UserForgotPasswordRequest,
    UserResetPasswordRequest,
    UserUpdatePasswordRequest,
    UserChangeEmailRequest,
    UserProfileUpdateRequest,
    UserResendActivationEmailRequest,
    UserChangePasswordRequest,
    UserDynamicAccessRequest,
    UserContactRequest
};

use Auth;

use Session;

class UserController extends BaseController
{
    /**
     * @var UserEngine - User Engine
     */
    protected $userEngine;

    /**
     * Constructor.
     *
     * @param UserEngine $userEngine - User Engine
     *-----------------------------------------------------------------------*/
    public function __construct(UserEngine $userEngine)
    {
        $this->userEngine = $userEngine;
    }

    /**
     * Handle datatable source data request.
     *
     * @param number $status
     *
     * @return json object
     *---------------------------------------------------------------- */
    public function index($status)
    {
        return $this->userEngine->prepareUsersList($status);
    }

    /**
     * Get login attempts for this client ip.
     *
     * @return json object
     *---------------------------------------------------------------- */
    public function loginAttempts()
    {
        $processReaction = $this->userEngine->prepareLoginAttempts();

        return __processResponse(
            $processReaction,
            [],
            $processReaction['data']
        );
    }

    /**
     * Authenticate user based on post form data.
     *
     * @param object UserLoginRequest $request
     *
     * @return json object
     *---------------------------------------------------------------- */
    public function loginProcess(UserLoginRequest $request)
    {
        $processReaction = $this->userEngine->processLogin($request->all());

        return __processResponse($processReaction, [
                1 => __tr('Welcome, you are logged in successfully.'),
                2 => __tr('Authentication failed. Please check your 
                email/password & try again.'),
            ], [], true);
    }

    /**
     * Perform user logout action.
     *
     * @return json object
     *---------------------------------------------------------------- */
    public function logout()
    {
        $processReaction = $this->userEngine->processLogout();

        return __processResponse($processReaction, [], [], true);
    }

    /**
     * Perform user logout action.
     *
     * @return json object
     *---------------------------------------------------------------- */
    public function processLogout()
    {
        $processReaction = $this->userEngine->processLogout();

        return redirect()->route('public.app');
    }

    /**
     * Handle user forgot password request.
     *
     * @param object UserForgotPasswordRequest $request
     *
     * @return json object
     *---------------------------------------------------------------- */
    public function forgotPasswordProcess(UserForgotPasswordRequest $request)
    {
        $processReaction = $this->userEngine
                                ->sendPasswordReminder(
                                    $request->input('usernameOrEmail')
                                );

        return __processResponse($processReaction, [
                1 => __tr('We have e-mailed your password reset link.'),
                2 => __tr('Invalid Request.'),
            ]);
    }

    /**
     * Render reset password view.
     *
     * @param string $reminderToken
     *---------------------------------------------------------------- */
    public function restPassword($reminderToken)
    {
        $processReaction = $this->userEngine
                                ->verifyPasswordReminderToken($reminderToken);

        if ($processReaction['reaction_code'] === 1) {
            Javascript::put(['passwordReminderToken' => $reminderToken]);

            return $this->loadPublicView('user.reset-password');
        }

        // if activation process failed then
        return redirect(configItem('login_url'));
    }

    /**
     * Handle reset password request.
     *
     * @param object UserResetPasswordRequest $request
     * @param string                          $reminderToken
     *
     * @return json object
     *---------------------------------------------------------------- */
    public function restPasswordProcess(
        UserResetPasswordRequest $request,
        $reminderToken
    ) {

        $processReaction = $this->userEngine
                                ->processResetPassword(
                                    $request->all(),
                                    $reminderToken
                                );

        return __processResponse($processReaction, [
                1  => __tr('Password Reset Successfully.'),
                2  => __tr('Password Not Reset.'),
                18 => __tr('Invalid Request.'),
            ]);
    }

    /**
     * Handle change password request.
     *
     * @param object UserUpdatePasswordRequest $request
     *
     * @return json object
     *---------------------------------------------------------------- */
    public function changePasswordProcess(UserUpdatePasswordRequest $request)
    {
        $processReaction = $this->userEngine
                                ->processUpdatePassword(
                                    $request->only(
                                        'new_password',
                                        'current_password'
                                    )
                                );

        return __processResponse($processReaction, [
                1 => __tr('Password updated successfully.'),
                3 => __tr('Current password is incorrect.'),
                14 => __tr('Password not updated.'),
            ], null, true);
    }

    /**
     * Get change email support data.
     *---------------------------------------------------------------- */
    public function getChangeEmailSupportData()
    {
        $processReaction = $this->userEngine
                            ->getChangeRequestedEmail();

        return __processResponse($processReaction, null, null, true);
    }

    /**
     * Handle change email request.
     *
     * @param object UserChangeEmailRequest $request
     *
     * @return json object
     *---------------------------------------------------------------- */
    public function changeEmailProcess(UserChangeEmailRequest $request)
    {
        $processReaction = $this->userEngine
                                ->sendNewEmailActivationReminder(
                                    $request->only(
                                        'new_email',
                                        'current_password'
                                    )
                                );

        return __processResponse($processReaction, [], [], true);
    }

    /**
     * Handle new email activation request.
     *
     * @param number $userID
     * @param string $activationKey
     *
     * @return json object
     *---------------------------------------------------------------- */
    public function newEmailActivation($userID, $activationKey)
    {
        $processReaction = $this->userEngine
                                ->newEmailActivation(
                                    $userID,
                                    $activationKey
                                );

        // Check if activation process succeed
        if ($processReaction['reaction_code'] === 1) {
            return redirect()->route('user.profile')->with([
                'success' => true,
                'message' => __tr('Your new email activated successfully.'),
             ]);
        }

        // if activation process failed then
        return redirect()->route('user.profile')
                        ->with([
                            'error'   => true,
                            'message' => __tr('New email activation link invalid.')
                        ]);
    }


    /**
     * Handle profile details request.
     *
     * @return json object
     *---------------------------------------------------------------- */
    public function profileDetails()
    {
        $processReaction = $this->userEngine->prepareProfileDetails();

        return __processResponse($processReaction, [], null, true);
    }

    /**
     * Handle update profile request.
     *
     * @param object UserProfileUpdateRequest $request
     *
     * @return json object
     *---------------------------------------------------------------- */
    public function updateProfileProcess(UserProfileUpdateRequest $request)
    {
        $processReaction = $this->userEngine
                                ->processUpdateProfile(
                                    $request->all()
                                );
 
        return __processResponse($processReaction, [
                1 => __tr('Profile updated successfully.'),
                14 => __tr('Nothing updated.'),
            ], $processReaction['data'], true);
    }

    /**
     * Handle user delete request.
     *
     * @param number $userID
     *
     * @return json object
     *---------------------------------------------------------------- */
    public function delete($userID, Request $request)
    {
        $processReaction = $this->userEngine->processUserDelete($userID);

        return __processResponse($processReaction, [
                1 => $processReaction['data']['message'],
                2 => __tr('User not deleted.'),
            ]);
    }

    /**
     * Handle user restore request.
     *
     * @param number $userID
     *
     * @return json object
     *---------------------------------------------------------------- */
    public function restore($userID, Request $request)
    {
        $processReaction = $this->userEngine->processUserRestore($userID);

        return __processResponse($processReaction, [
                1 => $processReaction['data']['message'],
                2 => __tr('User not restore.'),
            ]);
    }
    
    /**
     * change user password by admin.
     *
     * @param number                          $userID
     * @param array UserChangePasswordRequest $request
     *---------------------------------------------------------------- */
    public function changePasswordByAdmin($userID, UserChangePasswordRequest $request)
    {
        $processReaction = $this->userEngine
                                ->processChangePassword($userID, $request->all());

        return __processResponse($processReaction, [
                1 => __tr('Password updated successfully.'),
                14 => __tr('Password not updated.'),
                18 => __tr('User not exist.'),
            ]);
    }

    /**
     * Handle process contact request.
     *
     * @param object UserContactRequest $request
     *
     * @return json object
     *---------------------------------------------------------------- */
    public function getInfo($userId)
    {
        $processReaction = $this->userEngine->prepareInfo($userId);

        return __processResponse($processReaction, [], true);
    }

    /**
     * Get Add Support Data.
     *
     * @return json object
     *---------------------------------------------------------------- */
    public function getAddSupportData()
    {
        $processReaction = $this->userEngine->prepareAddSupportData();

        return __processResponse($processReaction, [], true);
    }

    /**
     * Handle add new user request.
     *
     * @param object AddUserRequest $request
     *
     * @return json object
     *---------------------------------------------------------------- */

    public function add(AddUserRequest $request)
    {
        $processReaction = $this->userEngine->processAdd($request->all());

        return __processResponse($processReaction, [], [], true);
    }

    /**
     * Get User Permissions.
     *
     * @param int $userId
     *
     * @return json object
     *---------------------------------------------------------------- */
    public function getUserPermissions($userId)
    {
        $processReaction = $this->userEngine->prepareUserPermissions($userId);

        return __secureProcessResponse($processReaction, [], [], true);
    }

    /**
     * Store User Dynamic Permissions.
     *
     * @param object UserDynamicAccessRequest $request
     * @param int $userId
     *
     * @return json object
     *---------------------------------------------------------------- */
    public function processUserPermissions(UserDynamicAccessRequest $request, $userId)
    {
        $processReaction = $this->userEngine->processAddUserPermission($request->all(), $userId);

        return __secureProcessResponse($processReaction, [], [], true);
    }

    /**
      * Handle profile details request.
      *
      * @return json object
      *---------------------------------------------------------------- */

    public function profileEditSupportData()
    {
        $processReaction = $this->userEngine->prepareProfileEditSupportData();

        return __processResponse($processReaction, [], null, true);
    }


    /**
      * Get User Edit Support Data
      *
      * @return json object
      *---------------------------------------------------------------- */
    public function getUserEditSupportData($userId)
    {
        $processReaction = $this->userEngine->prepareUserEditSupportData($userId);

        return __processResponse($processReaction, [], [], true);
    }

    /**
      * Get User Edit Support Data
      *
      * @return json object
      *---------------------------------------------------------------- */
    public function processUserUpdate($userId, EditUserRequest $request)
    {
        $processReaction = $this->userEngine
                                ->processUpdateUser($userId, $request->all());

        return __processResponse($processReaction, [], [], true);
    }

    /**
      * User get details data
      *
      * @param  mix $userId
      *
      * @return  json object
      *---------------------------------------------------------------- */

    public function userDetailData($userId)
    {   
        $processReaction = $this->userEngine->fetchUserDetails($userId);

        return __processResponse($processReaction, [], [], true);
    }

    /**
      * User contact us form 
      *
      * @param  mix $userId
      *
      * @return  json object
      *---------------------------------------------------------------- */

    public function contactUsForm()
    {   
        return $this->loadPublicView('user.contact-us');
    }
    public function login()
    {   
        return $this->loadPublicView('user.login');
    }

    /**
      * User contact us form 
      *
      * @param  mix $userId
      *
      * @return  json object
      *---------------------------------------------------------------- */

    public function contactUsFormRequest(UserContactRequest $request)
    {
    	$processReaction =  $this->userEngine->processContact($request->all());
    	
        return __processResponse($processReaction, [], [], true);
    }

}
