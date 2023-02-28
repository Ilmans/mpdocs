<?php
/*
* UserEngine.php - Main component file
*
* This file is part of the User component.
*-----------------------------------------------------------------------------*/

namespace App\Yantrana\Components\User;

use App\Yantrana\Base\BaseEngine;

use Auth;
use Hash;
use Session;
use App\Yantrana\Base\BaseMailer as MailService;
use App\Yantrana\Components\Media\MediaEngine;
use App\Yantrana\Components\User\Repositories\UserRepository;
use App\Yantrana\Services\YesTokenAuth\TokenRegistry\Repositories\TokenRegistryRepository;
use App\Yantrana\Components\User\Blueprints\UserEngineBlueprint;
use App\Yantrana\Support\CommonPostRequest;
use YesAuthority;
use YesSecurity;
use YesTokenAuth;

use App\Yantrana\Support\Country\Repositories\CountryRepository;

class UserEngine extends BaseEngine implements UserEngineBlueprint
{
    /**
     * @var UserRepository - User Repository
     */
    protected $userRepository;

    /**
     * @var MailService
     */
    protected $mailService;

    /**
     * @var MediaEngine - Media Engine
     */
    protected $mediaEngine;

    /**
     * @var CountryRepository - Country Repository
     */
    protected $countryRepository;

    /**
     * Token Registry Repository.
     *
     *-----------------------------------------*/
    protected $tokenRegistryRepository;

    /**
     * Constructor.
     *
     * @param UserRepository  $userRepository  - User Repository
     * @param MailService     $mailService     - Mail Service
     * @param MediaEngine 				$mediaEngine 				- Media Engine
     * @param TokenRegistryRepository    $tokenRegistryRepository     - Token Registry Repository
     *-----------------------------------------------------------------------*/
    public function __construct(
        UserRepository $userRepository,
        MailService $mailService,
        MediaEngine $mediaEngine,
        CountryRepository $countryRepository,
        TokenRegistryRepository $tokenRegistryRepository
    ) {
        $this->userRepository        = $userRepository;
        $this->mailService            = $mailService;
        $this->mediaEngine            = $mediaEngine;
        $this->countryRepository    = $countryRepository;
        $this->tokenRegistryRepository = $tokenRegistryRepository;
    }

    /**
     * Prepare users list.
     *
     * @param number $status
     *
     * @return eloquent collection object
     *---------------------------------------------------------------- */
    public function prepareUsersList($status)
    {

        $userCollection = $this->userRepository->fetchUsers($status);

        $requireColumns = [
            'creation_date' => function ($key) {
                return formatDateTime($key['created_at']);
            },
            'human_readable_creation_date' => function ($key) {
                return formatDateTime($key['created_at'], false)->diffForHumans();
            },
            'updated_date' => function ($key) {
                return formatDateTime($key['updated_at']);
            },
            'human_readable_updated_date' => function ($key) {
                return formatDateTime($key['updated_at'], false)->diffForHumans();
            },
            'profile_img_url' => function ($key) {
                $profilePicture = !__isEmpty($key['profile'])
                    ? !__isEmpty($key['profile']['profile_picture']) ? $key['profile']['profile_picture'] : 'no-image.png'
                    : 'no-image.png';

                return getProfileImage($profilePicture, $key['_uid']);
            },
            'role' => function ($key) {
                return $key['user_roles__id'];
            },
            'user_role' => function ($key) {
                return $key['title']; //configItem('user.roles', $key['user_roles__id']);
            },
            '_id',
            '_uid',
            'status',
            'name',
            'email',
            'username',
            'updated_at',
            'user_authority_id',
            'canViewDetails' => function () {
                if (canAccess('manage.user.read.detail.data')) {
                    return true;
                } else {
                    return false;
                }
            },
            'can_update' => function () {
                return canAccess('edit_user');
            },
            'can_delete' => function () {
                return canAccess('delete_and_restore_user');
            },
            'can_assign' => function () {
                return canAccess('manage.location.write.location_assign_process');
            }
        ];

        return $this->dataTableResponse($userCollection, $requireColumns);
    }

    /**
     * Show captcha.
     *
     * @return bool
     *---------------------------------------------------------------- */
    public function showCaptcha()
    {
        // Check if count greater than 5
        if ($this->userRepository->fetchLoginAttemptsCount() >= getConfigurationSettings('show_captcha')) {
            return true;
        }

        return false;
    }

    /**
     * Prepare login attempts for this client ip.
     *
     * @return array
     *---------------------------------------------------------------- */
    public function prepareLoginAttempts()
    {
        $showCaptcha = false;

        $site_key = configItem('recaptcha.site_key');
        // Check if count exist
        if ($this->showCaptcha() && getConfigurationSettings('enable_login_attempt') == true && !__isEmpty($site_key)) {
            $showCaptcha = true;
        }

        return $this->engineReaction(1, [
            'show_captcha' => $showCaptcha,
            'site_key' => $site_key
        ]);
    }

    /**
     * Process user login request using user repository & return
     * engine reaction.
     *
     * @param array $input
     *
     * @return array
     *---------------------------------------------------------------- */
    public function processLogin($input)
    {
        $user = $this->userRepository->varifyUsernameOrEmail($input['emailOrUsername']);

        // check the email is non-active
        if (__isEmpty($user)) {
            return $this->engineReaction(2, null, __tr('Sorry..!!, Your are not a member of this system, Please Contact Administrator.'));
        }

        $loginCredentials = [
            'username'  => $user->username,
            'password'  => $input['password'],
        ];

        $status = (int) $user->status;

        if ($status === 2 or $status === 12) { // Inactive

            return $this->engineReaction(2, null, __tr('Your Account Seems To Be Inactive,  Please Contact Administrator.'));
        } else if ($status === 5) { // deleted

            return $this->engineReaction(2, null, __tr('Your Account Seems To Be Deleted,  Please Contact Administrator.'));
        }

        // Check if user authenticated
        if (!Auth::attempt($loginCredentials)) {

            // If authentication failed
            $this->userRepository->updateLoginAttempts();   // update login attempts

            $showCaptcha = false;

            // Check if count exist
            if ($this->showCaptcha() && getConfigurationSettings('enable_login_attempt') == true && configItem('recaptcha.site_key')) {

                $showCaptcha = true;
            }

            return $this->engineReaction(2, ['show_captcha' => $showCaptcha]);
        }

        $userId = $user->_id;

        $userFullName = $user->first_name . ' ' . $user->last_name;

        if (isset($input['remember_me'])) {
            YesTokenAuth::setExpiration((60 * 60 * 24 * 7));
        }

        // user login in activity
        activityLog(1, $userId, 6, $userFullName);

        $this->userRepository->clearLoginAttempts(); // make login log entry

        $getUserAuthInfo = getUserAuthInfo();

        $authToken = YesTokenAuth::issueToken([
            'aud'  => $userId,
            'uaid' => $getUserAuthInfo['user_authority_id'] ?? ''
        ]);

        setAuthToken($authToken);

        return $this->engineReaction(1, [
            'auth_info'         => $getUserAuthInfo,
            'access_token'    => $authToken,
            'availableRoutes' => YesAuthority::availableRoutes(),
        ], __tr("Welcome, you are logged in successfully."));
    }

    /**
     * Process user logout action.
     *
     * @return array
     *---------------------------------------------------------------- */
    public function processLogout()
    {
        $user = getAuthUser();
        $userName = $user->first_name . ' ' . $user->last_name;

        activityLog(1, $user->_id, 7, $userName);

        processLogoutAction();

        return $this->engineReaction(1, ['auth_info' => getUserAuthInfo()]);
        return $this->engineReaction(1, ['auth_info' => []]);
    }

    /**
     * Process forgot password request based on passed email address &
     * send password reminder on enter email address.
     *
     * @param string $email
     *
     * @return array
     *---------------------------------------------------------------- */
    public function sendPasswordReminder($userEmail)
    {
        $user = $this->userRepository->fetchActiveUserByEmail(strtolower($userEmail), true);

        // Check if user record exist
        if (__isEmpty($user)) {
            return $this->engineReaction(2);
        }
        // Check the request to account email is there if not it throw error...
        if (__isEmpty($user->email)) {
            return $this->engineReaction(2, null, __tr("You don't have email registered with us, Please contact system administrator."));
        }

        $email = $user->email;

        // Delete old password reminder for this user
        $this->userRepository->deleteOldPasswordReminder($email);

        $token = YesSecurity::generateUid();

        // Check for if password reminder added
        if (!$this->userRepository->storePasswordReminder($email, $token)) {
            return $this->engineReaction(2);
        }

        $messageData = [
            'firstName' => $user->first_name,
            'lastName' => $user->last_name,
            'email' => $user->email,
            'fullName' => $user->first_name . ' ' . $user->last_name,
            'expirationTime' => configItem('account.password_reminder_expiry'),
            'userId' => $user->_id,
            'token' => $token,
        ];

        // if reminder mail has been sent
        if ($this->mailService->notifyCustomer('Password Reminder', 'account.password-reminder', $messageData, $email)) {
            return $this->engineReaction(1); // success reaction
        }

        return $this->engineReaction(2); // error reaction
    }

    /**
     * Process reset password request.
     *
     * @param array  $input
     * @param string $reminderToken
     *
     * @return array
     *---------------------------------------------------------------- */
    public function processResetPassword($input, $reminderToken)
    {
        $email = strtolower($input['email']);

        $count = $this->userRepository
            ->fetchPasswordReminderCount($reminderToken, $email);

        // Check if reminder count not exist on 0
        if (!$count > 0) {
            return $this->engineReaction(18);
        }

        $user = $this->userRepository->fetchActiveUserByEmail($email);

        // Check if user record exist
        if (__isEmpty($user)) {
            return $this->engineReaction(18);
        }

        // Check if user password updated
        if ($this->userRepository
            ->resetPassword($user, $input['password'])
        ) {
            return $this->engineReaction(1);
        }

        return $this->engineReaction(2);
    }



    /**
     * Process user update password request.
     *
     * @param array $inputData
     *
     * @return array
     *---------------------------------------------------------------- */
    public function processUpdatePassword($inputData)
    {
        $user = getAuthUser();

        if (
            !__isEmpty($user)
            and !__isEmpty($user->username)
        ) {

            if (!Hash::check($inputData['current_password'], $user->password)) {
                return $this->engineReaction(3);
            }
        }

        // Check if user password updated
        if ($this->userRepository->updatePassword($user, $inputData['new_password'])) {
            $getRoute = [];

            $getRoute = ['passwordRoute' => route('user.change_password')];

            return $this->engineReaction(1, $getRoute);
        }

        return $this->engineReaction(14);
    }

    /**
     * Get temp email to user.
     *
     * @return array
     *---------------------------------------------------------------- */
    public function getChangeRequestedEmail()
    {
        $tempMailData = $this->userRepository->fetchChangeEmailRequested();
        $user = $this->userRepository->fetchProfile();
        $newEmail = null;

        // Check if new email is exist
        if (!__isEmpty($tempMailData)) {
            $newEmail = $tempMailData->new_email;
        }

        return $this->engineReaction(1, [
            'newEmail' => $newEmail,
            'current_email' => $user->email ?? null
        ]);
    }

    /**
     * Send new email activation reminder.
     *
     * @param array $inputData
     *
     * @return array
     *---------------------------------------------------------------- */
    public function sendNewEmailActivationReminder($inputData)
    {
        $user = getAuthUser();

        if (
            !__isEmpty($user)
            and !__isEmpty($user->email)
        ) {

            if (!Hash::check($inputData['current_password'], $user->password)) {
                return $this->engineReaction(3, [], __tr('Authentication Failed. Please Check Your Password.'));
            }
        }

        // delete olde new email request
        $this->userRepository->deleteOldEmailChangeRequest();

        // Check if user email updated
        if (getConfigurationSettings('restrict_user_email_update') && !isAdmin()) {
            return $this->engineReaction(2, [], __tr('Cannot Update Email Address, Please contact System Administrator.'));
        }

        // Check if user email updated
        if ($this->userRepository->updateEmail($inputData['new_email'])) {
            return $this->engineReaction(1, ['activationRequired' => false], __tr('Email Address Updated Successfully.'));
        }

        return $this->engineReaction(2, [], __tr('Email Address Not Updated.'));
    }

    /**
     * Activate new email.
     *
     * @param number $userID
     * @param string $activationKey
     *
     * @return array
     *---------------------------------------------------------------- */
    public function newEmailActivation($userID, $activationKey)
    {
        // Fetch temporary email
        $tempEmail = $this->userRepository
            ->fetchTempEmail($userID, $activationKey);

        // Check if temp email exist for this activation key
        if (empty($tempEmail)) {
            return $this->engineReaction(18);
        }

        // Check if user email updated
        if ($this->userRepository->updateEmail($tempEmail->new_email)) {
            return $this->engineReaction(1);
        }

        return $this->engineReaction(2);
    }


    /**
     * Prepare user profile information.
     *
     * @return array
     *---------------------------------------------------------------- */
    public function prepareProfileDetails()
    {
        $user = $this->userRepository->fetchProfile();

        $data = [
            'first_name'    => $user->first_name,
            'last_name'     => $user->last_name,
            'email'         => $user->email,
            'userRole'      => $user->title,
        ];

        $userUid    = $user->_uid;

        $existingProfilePictureURL = '';

        $profile = $this->userRepository->fetchProfileData();

        // Check if profile empty then set profile
        if (!__isEmpty($profile)) {
            $data['address_line_1'] = $profile->address_line_1;
            $data['address_line_2'] = $profile->address_line_2;


            $country                = $this->countryRepository->fetchById($profile->countries__id);

            $profilePicture        = $profile->profile_picture;

            $data['country']        = !__isEmpty($country) ? $country->name : '';

            $profileMedia            = mediaStorage('user_photo', ['{_uid}'    => $userUid]) . '/' . $profilePicture;

            $existingProfilePictureURL = file_exists($profileMedia)
                ? mediaUrl('user_photo', ['{_uid}'    => $userUid]) . '/' . $profilePicture
                : noUserThumbImageURL();
        } else {
            $existingProfilePictureURL = noUserThumbImageURL();
        }

        return $this->engineReaction(1, [
            'profile'                    => $data,
            'existingProfilePictureURL' => $existingProfilePictureURL

        ]);
    }

    /**
     * Update user profile & return response.
     *
     * @param array $input
     *
     * @return array
     *---------------------------------------------------------------- */
    public function processUpdateProfile($input)
    {
        $profileUpdated = false;

        $updateProfile = $this->userRepository->updateProfile($input);

        $userData = [];
        $userFullName = '';

        // Check if profile updated
        if ($updateProfile) {

            $userData = [
                '_id'   =>  $updateProfile->_id,
                'userFullName' => $updateProfile->first_name . ' ' . $updateProfile->last_name
            ];

            $profileUpdated = true;
        }

        if (__ifIsset($input['profile_picture'])) {

            // Check if selected item image exist
            if (!$this->mediaEngine->isUserTempMedia($input['profile_picture'])) {
                return $this->engineReaction(2, null, __tr('Selected Image Not Exist.'));
            }
        }

        $profile = $this->userRepository->fetchProfileData();

        $userAuthInfo = getUserAuthInfo('profile');
        $input['userFullName'] = $userAuthInfo['full_name'];

        // Check if profile empty then set profile
        if (__isEmpty($profile)) {

            // store profile
            if ($this->userRepository->storeProfile($input)) {
                if (__ifIsset($input['profile_picture'])) {

                    // Check if item thumbnail not stored exist
                    if (!$this->mediaEngine->storeUserProfile($input['profile_picture'])) {
                        return $this->engineReaction(2, null, __tr('Profile Not Updated.'));
                    }
                }

                $profileUpdated = true;
            }
        } else {

            // store profile
            if ($this->userRepository->updateProfileData($profile, $input)) {
                if (__ifIsset($input['profile_picture'])) {

                    // Check if item thumbnail not stored exist
                    if (!$this->mediaEngine->storeUserProfile($input['profile_picture'])) {
                        return $this->engineReaction(2, null, __tr('Profile Not Updated.'));
                    }
                }

                $profileUpdated = true;
            }
        }

        if ($profileUpdated) {
            return $this->engineReaction(1, [
                'auth_info' => getUserAuthInfo(),
                'userData'  => $userData
            ], __tr('Profile Updated Successfully.'));
        }

        return $this->engineReaction(14, null, __tr('Profile Not Updated'));
    }

    /**
     * Process user delete request.
     *
     * @param number $userID
     *
     * @return array
     *---------------------------------------------------------------- */
    public function processUserDelete($userid)
    {
        $user = $this->userRepository->fetch($userid);

        // Check if user exist or we are trying to delete admin user
        if (__isEmpty($user) or $user->role === 1) {
            return $this->engineReaction(18); // not exist record
        }

        $name = $user->first_name . ' ' . $user->last_name;
        $status = $user->status;

        if ($status === 12 or $status === 5) { // Never activated or Deleted

            // Check if user delete successfully
            if ($this->userRepository->delete($user)) {

                activityLog(1, $user->_id, 3, $user);

                return $this->engineReaction(1, [
                    'message' => __tr(
                        '__fullName__ User Deleted Successfully.',
                        [
                            '__fullName__' => $user->fname . ' ' . $user->lname,
                        ]
                    ),
                ]);
            }
        } else if ($this->userRepository->update($user, ['status' => 5])) {

            // if user is active then soft delete it
            activityLog(1, $user->_id, 4, $name);

            return $this->engineReaction(1, [
                'message' => __tr(
                    '__fullName__ User Soft Deleted Successfully.',
                    [
                        '__fullName__' => $name,
                    ]
                ),
            ]);
        }


        return $this->engineReaction(2);
    }

    /**
     * Process user restore request.
     *
     * @param number $userID
     *
     * @return array
     *---------------------------------------------------------------- */
    public function processUserRestore($userID)
    {
        $user = $this->userRepository->fetch($userID);

        // Check if user records exist
        if (__isEmpty($user) or $user->role === 1) {
            return $this->engineReaction(18); // not exist record
        }

        // Check if user restore successfully
        if ($this->userRepository->update($user, ['status' => 1])) {

            $userFullName = $user->first_name . ' ' . $user->last_name;

            activityLog(1, $user->_id, 5, $userFullName);

            return $this->engineReaction(1, [
                'message' => __tr(
                    '__name__ User Restored Successfully.',
                    [
                        '__name__' => $user->first_name . ' ' . $user->last_name,
                    ]
                ),
            ]);
        }

        return $this->engineReaction(2);
    }

    /**
     * Verify password reminder token.
     *
     * @param string $reminderToken
     *
     * @return array
     *---------------------------------------------------------------- */
    public function verifyPasswordReminderToken($reminderToken)
    {
        $count = $this->userRepository
            ->fetchPasswordReminderCount($reminderToken);

        // Check if reminder count not exist on 0
        if (!$count > 0) {
            return $this->engineReaction(18);
        }

        return $this->engineReaction(1);
    }

    /**
     * Process change password by admin.
     *
     * @param number $userID
     * @param array  $input
     *---------------------------------------------------------------- */
    public function processChangePassword($userID, $input)
    {
        $user = $this->userRepository->fetch($userID);

        // check if user exist
        if (__isEmpty($user)) {
            return $this->engineReaction(18);
        }

        $new_password = bcrypt($input['new_password']);

        // Check if user password updated
        if ($this->userRepository->update($user, [
            'password' => $new_password
        ])) {

            $userName = $user->first_name . ' ' . $user->last_name;

            activityLog(5, $user->_id, 2, $userName);

            return $this->engineReaction(1);
        }

        return $this->engineReaction(14);
    }



    /**
     * send mail to the user
     *
     * @param array $input
     *
     * @return void
     *---------------------------------------------------------------- */

    public function prepareInfo($userId)
    {
        $user = $this->userRepository->fetchByID($userId);

        if (__isEmpty($user)) {
            return $this->engineReaction(18, __tr('User Not Found.'));
        }

        return $this->engineReaction(1, [
            'fullName' => $user->first_name . ' ' . $user->last_name,
            'email'    => $user->email,
            'id'       => $user->_id
        ]);
    }

    /**
     * Prepare Add Support Data
     *
     * @return array
     *---------------------------------------------------------------- */
    public function prepareAddSupportData()
    {
        $roleCollection = $this->userRepository->fetchAllRoles();
        $roleData = [];

        // check if role collection exist
        if (!__isEmpty($roleCollection)) {
            foreach ($roleCollection as $key => $role) {
                // check if role admin not exist
                if ($role->_id !== 1) { // admin
                    $roleData[] = [
                        'id'    => $role->_id,
                        'name'  => $role->title
                    ];
                }
            }
        }

        return $this->engineReaction(1, [
            'userRoles' => $roleData
        ]);
    }

    /**
     * Process Add New User Request
     *
     * @param array $inputData
     *
     * @return array
     *---------------------------------------------------------------- */
    public function processAdd($inputData)
    {
        $role = $inputData['role'];

        // Check if user role not admin
        if ($role === 1) { // Admin

            return $this->engineReaction(2, null, __tr('You Cannot Create Admin User Role, Please Select Another User Role'));
        }

        // Check if new active user stored successfully then return success reaction
        if ($newUserData = $this->userRepository->storeActive($inputData)) {

            if ($this->userRepository->storeUserAuthority($newUserData->_id, $role)) {

                return $this->engineReaction(1, null, __tr('User Added Successfully.'));
            }
        }

        return $this->engineReaction(2, null, __tr('User Not Added'));
    }

    /*
     * Prepare user Permissions.
     *
     * @param int $userId
     *
     * @return array
     *---------------------------------------------------------------- */
    public function prepareUserPermissions($userId)
    {
        $userData = $this->userRepository->fetchUser($userId);
        $roleId   = $userData->user_roles__id;

        // check if user is exist
        if (__isEmpty($userData)) {
            return $this->engineReaction(2, null, __tr('User Not Found.'));
        }

        $permissions            = [];

        // Get User permissions zones with details
        $userPermissions    = YesAuthority::checkUpto('DB_USER')
            ->withDetails()
            ->getZones($userId);

        // Check if user permissions exist
        if (!__isEmpty($userPermissions)) {
            foreach ($userPermissions as $key => $permission) {

                $result = 1; // Inherited

                if ($permission->resultBy() === 'DB_ROLE') {
                    $result = 1; // Inherited
                }

                if ($permission->resultBy() === 'DB_USER') {
                    // Check if allowed permission
                    if ($permission->isAccess() === true) {
                        $result = 2; // Allow
                    }

                    // Check if denied permission
                    if ($permission->isAccess() !== true) {
                        $result = 3; // Deny
                    }
                }

                $currentInheritStatus   = false; // not available

                // Check if level check array has 'CONFIG_ROLE' key and it is true
                if ((array_has($permission->levelsChecked(), 'CONFIG_ROLE'))
                    and ($permission->levelsChecked()['CONFIG_ROLE'] === true)
                ) {
                    $currentInheritStatus = true; // available
                }

                // Check if level check array has 'CONFIG_ROLE' key and it is true
                if ((array_has($permission->levelsChecked(), 'DB_ROLE'))
                    and ($permission->levelsChecked()['DB_ROLE'] === true)
                ) {
                    $currentInheritStatus = true; // available
                }

                $permissions[] = [
                    "id"            => $permission->access_id_key(),
                    "title"         => str_replace('Read ', '', $permission->title()),
                    "parent"        => $permission->parent(),
                    "folder"        => false,
                    "key"           => $permission->access_id_key(),
                    "result"        => $result,
                    "expanded"      => true,
                    "dependencies"  => $permission->dependencies(),
                    "checkbox"      => false,
                    "inheritStatus" => $currentInheritStatus
                ];
            }
        }


        $allPermissions = $this->buildTree($permissions);
        $allowPermissions = [];
        $denyPermissions = [];
        $inheritPermissions = [];

        if (!__isEmpty($allPermissions)) {
            foreach ($allPermissions as $permission) {
                if (!__isEmpty($permission['children'])) {
                    foreach ($permission['children'] as $child) {
                        if ($child['result'] == 2) {
                            $allowPermissions[] = $child['id'];
                        }
                        if ($child['result'] == 3) {
                            $denyPermissions[] = $child['id'];
                        }
                        if ($child['result'] == 1) {
                            $inheritPermissions[] = $child['id'];
                        }
                    }
                }
                if (isset($permission['children_permission_group'])) {

                    foreach ($permission['children_permission_group'] as $groupchild) {

                        foreach ($groupchild['children'] as $subchild) {

                            if ($subchild['result'] == 2) {
                                $allowPermissions[] = $subchild['id'];
                            }
                            if ($subchild['result'] == 3) {
                                $denyPermissions[] = $subchild['id'];
                            }
                            if ($subchild['result'] == 1) {
                                $inheritPermissions[] = $subchild['id'];
                            }
                        }
                    }
                }
            }
        }

        return $this->engineReaction(1, [
            'permissions' => $allPermissions,
            'allow_permissions' => $allowPermissions,
            'deny_permissions' => $denyPermissions,
            'inherit_permissions' => $inheritPermissions
        ]);
    }

    /*
     * Prepare Nested key value array.
     *
     * @param array $elements
     * @param int $parentId
     *
     * @return array
     *---------------------------------------------------------------- */

    protected function buildTree($elements = [], $parentId = '')
    {
        $branch = [];
        $permissionStatuses = configItem('user.permission_status');

        foreach ($elements as $element) {
            if ($element['parent'] == $parentId) {
                $children = $this->buildTree($elements, $element['id']);

                if ($children) {
                    foreach ($children as $key => $subParent) {
                        if (__isEmpty($this->buildTree($elements, $subParent['id']))) {

                            $element['children'][] = $subParent;
                            foreach ($permissionStatuses as $statusKey => $status) {

                                $inheritStatus = '';

                                if ($statusKey == 1) {
                                    $inheritStatus = ($subParent['inheritStatus'] == true)
                                        ? __tr(' (Allow)') : __tr(' (Deny)');
                                }

                                $element['children'][$key]['options'][] = [
                                    'title'     => $status . $inheritStatus,
                                    'status'    => $statusKey,
                                    'key'       => $subParent['id'] . '_' . $statusKey,
                                    'id'        => $subParent['id'] . '_' . $statusKey,
                                ];
                            }
                        } else {
                            $element['children_permission_group'][] = $subParent;
                        }

                        $subParent['result'] = $element['result'];
                    }
                }
                $branch[] = $element;
            }
        }

        return $branch;
    }

    /*
     * Store Dynamic User Permission.
     *
     * @param array $inputData
     * @param int $userId
     *
     * @return array
     *---------------------------------------------------------------- */
    public function processAddUserPermission($inputData, $userId)
    {
        $userAuthorities = $this->userRepository->fetchUserAuthorities($userId);

        // check if user is exist
        if (__isEmpty($userAuthorities)) {
            return $this->engineReaction(2, null, __tr('User Not Found.'));
        }

        // Check if permission is allowed
        if (!isset($inputData['allow_permissions'])) {
            return $this->engineReaction(2, null, __tr('Please Select Permission.'));
        }

        $updateData = [
            '__permissions' => [
                'allow' => $inputData['allow_permissions'],
                'deny'  => $inputData['deny_permissions']
            ]
        ];

        $userPermissionData = $this->userRepository->update($userAuthorities, $updateData);

        if ($userPermissionData) {

            $userData = $this->userRepository->fetchUser($userPermissionData->users__id);

            $userName = $userData->first_name . ' ' . $userData->last_name;

            activityLog(6, $userPermissionData->_id, 2, $userName);

            return $this->engineReaction(1, null, __tr('Permission Added Successfully.'));
        }

        return $this->engineReaction(14, null, __tr('Nothing Updated.'));
    }

    /*
     * Prepare user profile information.
     *
     * @return array
     *---------------------------------------------------------------- */

    public function prepareProfileEditSupportData()
    {
        $user = $this->userRepository->fetchProfile();

        $userUid    = $user->_uid;

        $data        = [
            'first_name'    => $user->first_name,
            'last_name'     => $user->last_name,
            'userRole'      => $user->title,
        ];

        $existingProfilePictureURL = '';

        $profile = $this->userRepository->fetchProfileData();

        // Check if profile empty then set profile
        if (!__isEmpty($profile)) {
            $data['address_line_1'] = $profile->address_line_1;
            $data['address_line_2'] = $profile->address_line_2;
            $data['country']        = $profile->countries__id;

            $profilePicture        = $profile->profile_picture;

            $profileMedia            = mediaStorage('user_photo', ['{_uid}'    => $userUid]) . '/' . $profilePicture;

            $existingProfilePictureURL = getProfileImage($profilePicture, $userUid);
        } else {
            $existingProfilePictureURL = noUserThumbImageURL();
        }

        return $this->engineReaction(1, [
            'profile'                    => $data,
            'existingProfilePictureURL' => $existingProfilePictureURL,
            'countries'                    => $this->countryRepository->fetchAll()

        ]);
    }

    /*
     * Prepare User Edit Support Data
     *
     * @return array
     *---------------------------------------------------------------- */
    public function prepareUserEditSupportData($userId)
    {
        $user = $this->userRepository->fetchUserWithAuthority($userId);

        // Check if user exist
        if (__isEmpty($user)) {
            return $this->engineReaction(18, null, __tr('User Does Not Exist'));
        }

        $userUpdateData = [
            'first_name'    => $user->first_name,
            'last_name'     => $user->last_name,
            'role'          => $user->user_roles__id,
            'username'      => $user->username,
            'email'         => $user->email,
            'is_active'     => ($user->status == 1)
                ? true : false
        ];

        $roleCollection = $this->userRepository->fetchAllRoles();
        $roleData = [];

        // check if role collection exist
        if (!__isEmpty($roleCollection)) {
            foreach ($roleCollection as $key => $role) {
                // check if role admin not exist
                if ($role->_id !== 1) { // admin
                    $roleData[] = [
                        'id'    => $role->_id,
                        'name'  => $role->title
                    ];
                }
            }
        }

        return $this->engineReaction(1, [
            'userUpdateData' => $userUpdateData,
            'userRoles' => $roleData
        ]);
    }

    /*
     * Process Update User
     *
     * @params mix $userId
     * @params array $inputData
     *
     * @return array
     *---------------------------------------------------------------- */
    public function processUpdateUser($userId, $inputData)
    {
        $user = $this->userRepository->fetchUser($userId);

        // Check if user exist
        if (__isEmpty($user)) {
            return $this->engineReaction(18, null, __tr('User Does Not Exist'));
        }

        $userAuthority = $this->userRepository->fetchUserAuthorities($user->_id);

        // Check if user authority exist
        if (__isEmpty($userAuthority)) {
            return $this->engineReaction(18, null, __tr('User Does Not Exist'));
        }

        $userUpdated = false;

        $userRoleUpdateData = [
            'user_roles__id' => $inputData['role']
        ];

        if ($this->userRepository->updateUserAuthority($userAuthority, $userRoleUpdateData)) {
            $userUpdated = true;
        }

        $isActive = 2; // Inactive

        if (
            isset($inputData['is_active'])
            and ($inputData['is_active'] == true)
        ) {
            $isActive = 1;
        }

        $updateData = [
            'first_name'    => $inputData['first_name'],
            'last_name'     => $inputData['last_name'],
            'username'      => $inputData['username'],
            'email'         => $inputData['email'],
            'status'        => $isActive
        ];

        if ($this->userRepository->update($user, $updateData)) {

            $userName = $user->first_name . ' ' . $user->last_name;

            activityLog(1, $user->_id, 2, $userName);

            $userUpdated = true;
        }

        if ($userUpdated == true) {
            return $this->engineReaction(1, null, __tr('User Updated Successfully.'));
        }

        return $this->engineReaction(14, null, __tr('User Not Updated.'));
    }

    /**
     * get requirements details 
     *
     * @return  array
     *---------------------------------------------------------------- */

    public function fetchUserDetails($userIdorUid)
    {
        $user = $this->userRepository->fetchUserData($userIdorUid);

        // Check if user exist
        if (__isEmpty($user)) {
            return $this->engineReaction(18, null, __tr('User Does Not Exist'));
        }

        $userData = [
            'id'            =>    $user->_id,
            'userFullName'    =>    $user->name,
            'userName'        =>    $user->username,
            'email'            =>    $user->email,
            'created_at'    =>    formatDateTime($user->created_at),
            'userRoleTitle'    =>    $user->userRoleTitle,
            'status'        =>    configItem('status_codes', $user->status),
            'address_line_1' => isset($user->profile->address_line_1) ?
                $user->profile->address_line_1 : null,
            'address_line_2' => isset($user->profile->address_line_2) ?
                $user->profile->address_line_2 : null,
            'country' => isset($user->profile->country->name) ?
                $user->profile->country->name : null
        ];

        return $this->engineReaction(1, [
            'userData'  => $userData
        ]);
    }

    /**
     * Process user contact request.
     *
     * @param array $inputData
     *
     * @return array
     *---------------------------------------------------------------- */
    public function processContact($inputData)
    {
        // mail subject
        $subject = $inputData['subject'];

        $messageData = [
            'userName'        => $inputData['fullname'],
            'senderEmail'    => $inputData['email'],
            'usermessage'    => $inputData['message'],
        ];

        if (__isEmpty(getConfigurationSettings('contact_email'))) {
            return $this->engineReaction(2, null, __tr('Unable to process your request.'));
        }

        if ($this->mailService->notifyAdmin($subject, 'contact', $messageData, 2)) {
            return $this->engineReaction(1, null, __tr('Your contact request has been submitted successfully we will set back to you shortly.'));
        }

        return $this->engineReaction(14, null, __tr('Invalid request...'));
    }
}
