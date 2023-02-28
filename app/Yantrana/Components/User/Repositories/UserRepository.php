<?php
/*
* UserRepository.php - Repository file
*
* This file is part of the User component.
*-----------------------------------------------------------------------------*/

namespace App\Yantrana\Components\User\Repositories;

use Request;
use DB;
use YesSecurity;
use App\Yantrana\Base\BaseRepository;
use App\Yantrana\Services\YesTokenAuth\TokenRegistry\Models\TokenRegistryModel;
use App\Yantrana\Components\User\Blueprints\UserRepositoryBlueprint;

use App\Yantrana\Components\User\Models\{
    User as UserModel, 
    UserAuthorityModel,
    UserProfile as UserProfileModel, 
    UserRole as UserRoleModel,
    LoginAttempt, PasswordReset, 
    EmailChangeRequest
};

class UserRepository extends BaseRepository implements UserRepositoryBlueprint
{
    /**
    * Fetch the record of ActivityLog
    *
    * @param    int || string $idOrUid
    *
    * @return    eloquent collection object
    *---------------------------------------------------------------- */

    public function fetch($idOrUid)
    {   
        if (is_numeric($idOrUid)) {

            return UserModel::where('_id', $idOrUid)->first();
        }

        return UserModel::where('_uid', $idOrUid)->first();
    }

    /**
      * Fetch the records of Invoices
      *
      * @return  eloquent collection object
      *---------------------------------------------------------------- */

    public function fetchUsersWithOptions($options = [])
    {   
        $userQuery = UserModel::query();

        if (__isEmpty($options)) {
            return $userQuery->get();
        }

        // current month wise
        if (array_has($options, 'current_month') and $options['current_month'] !== false) {
            $userQuery->where('created_at', '>=', now()->startOfMonth());
        }
        
        // Current Day wise
        if (array_has($options, 'current_day') and !__isEmpty($options['current_day'])) {
            $userQuery->where('created_at', '=', now()->startOfDay());
        }

        if (array_has($options, 'status') and !__isEmpty($options['status'])) {
            $userQuery->where('status', '=', $options['status']);
        }

        return $userQuery->get();
    }

    /**
     * Fetch users for manage section.
     *
     * @param number status
     *
     * @return eloquent collection object
     *---------------------------------------------------------------- */
    public function fetchUsers($status)
    {
        $dataTableConfig = [
            'fieldAlias' => [
                'name'          => 'users.first_name',
                'creation_date' => 'users.created_at',
                'user_role'     => 'user_authorities.user_roles__id'
            ],
            'searchable' => [
                'users.first_name',
                'users.last_name',
                'users.email',
                'username'
            ],
        ];

        return  UserModel::join('user_authorities', 'users._id', 'user_authorities.users__id')
                    ->join('user_roles', 'user_authorities.user_roles__id', '=', 'user_roles._id')
                    ->with([
                        'profile'    => function ($profile) {
                            $profile->select('_id', 'users__id', 'profile_picture');
                        }
                    ])
                    ->where('users.status', $status)
                    ->select(
                        'users._id',
                        'users._uid',
                        'user_authorities.user_roles__id',
                        'user_authorities._id AS user_authority_id',
                        'users.status',
                        DB::raw('CONCAT(users.first_name, " ", users.last_name) AS name'),
                        'users.email', 'username',
                        'users.created_at',
                        'users.updated_at',
                        'user_roles.title'
                    )
                    ->dataTables($dataTableConfig)
                    ->toArray();
    }

    /**
    * Fetch User by id
    *
    * @param int $userId
    *
    * @return void
    *-----------------------------------------------------------------------*/
    public function fetchUser($userId)
    { 
       return UserModel::with([
                        'profile'    => function ($profile) {
                            $profile->select('_id', 'users__id', 'address_line_1', 'address_line_2', 'countries__id');
                        }
                    ])
              ->join('user_authorities', 'users._id', '=', 'user_authorities.users__id')
              ->join('user_roles', 'user_authorities.user_roles__id', '=', 'user_roles._id')
              ->select(
                        'users._id',
                        'users._uid',
                        'user_authorities.user_roles__id',
                        'users.status',
                        DB::raw('CONCAT(users.first_name, " ", users.last_name) AS name'),
                        'users.email', 'username',
                        'users.created_at',
                        'users.updated_at',
                        'users.first_name',
                        'users.last_name',
                        'user_roles.title AS userRoleTitle'
                    )
              ->where('users._id', $userId)
              ->first();
      
    }


    /**
     * Update login attempts.
     *---------------------------------------------------------------- */
    public function updateLoginAttempts()
    {
        $ipAddress = Request::getClientIp();
        $loginAttempt = LoginAttempt::where('ip_address', $ipAddress)
                                        ->first();

        // Check if login attempt record exist for this ip address
        if (!empty($loginAttempt)) {
            $loginAttempt->attempts = $loginAttempt->attempts + 1;
            $loginAttempt->save();
        } else {
            $newLoginAttempt = new LoginAttempt();

            $newLoginAttempt->ip_address = $ipAddress;
            $newLoginAttempt->attempts = 1;
            //$newLoginAttempt->created_at = currentDateTime();
            $newLoginAttempt->save();
            activityLog('User Login Attempt', $newLoginAttempt->_id, 'Update');
        }
    }

    /**
     * Clear login attempts.
     *---------------------------------------------------------------- */
    public function clearLoginAttempts()
    {
        LoginAttempt::where('ip_address', Request::getClientIp())->delete();
    }

    /**
     * Fetch login attempts based on ip address.
     *
     * @return number
     *---------------------------------------------------------------- */
    public function fetchLoginAttemptsCount()
    {
        $loginAttempt = LoginAttempt::where(
            'ip_address',
                                        Request::getClientIp()
                                    )
                                    ->select('attempts')
                                    ->first();

        if (!empty($loginAttempt)) {
            return $loginAttempt->attempts;
        }

        return 0;
    }

    
    /**
     * Fetch active user using email address & return response.
     *
     * @param string $email
     * @param bool   $selectRecord
     *
     * @return eloquent collection object
     *---------------------------------------------------------------- */
    public function fetchActiveUserByEmail($usernameOremail, $selectRecord = false)
    {
        $activeUser = UserModel::where('status', 1)
              ->where('email', $usernameOremail)
              ->orWhere('username', $usernameOremail);

        if ($selectRecord) { 

            $activeUser->select(
                            '_id',
                            'first_name',
                            'last_name',
              'email',
              'username'
                        );
        }

        return $activeUser->first();
    }

    /**
     * Store password reminder & return response.
     *
     * @param string $email
     * @param string $token
     *
     * @return bool
     *---------------------------------------------------------------- */
    public function storePasswordReminder($email, $token)
    {
        $passwordReminder = new PasswordReset();

        $passwordReminder->email = $email;
        $passwordReminder->token = $token;

        return $passwordReminder->save();
    }

    /**
     * Delete old password reminder.
     *
     * @param string $email
     *
     * @return bool
     *---------------------------------------------------------------- */
    public function deleteOldPasswordReminder($email)
    {
        $expiryTime = time() - configItem('account.password_reminder_expiry')
                                * 60 * 60;

        return PasswordReset::where('email', $email)
                            ->orWhere(
                                DB::raw('UNIX_TIMESTAMP(created_at)'),
                             '<',
                                $expiryTime
                            )
                            ->delete();
    }

    /**
     * Fetch password reminder count.
     *
     * @param string $reminderToken
     * @param string $email
     *
     * @return eloquent collection object
     *---------------------------------------------------------------- */
    public function fetchPasswordReminderCount($reminderToken, $email = null)
    {
        return PasswordReset::where(function ($query) use ($reminderToken, $email) {
            $query->where('token', $reminderToken);

            if (!__isEmpty($email)) {
                $query->where('email', $email);
            }
        })->count();
    }

    /**
     * Reset password.
     *
     * @param object $user
     * @param string $newPassword
     *
     * @return bool
     *---------------------------------------------------------------- */
    public function resetPassword($user, $newPassword)
    {
        $user->password = bcrypt($newPassword);

        if ($user->save()) {  // Check for if user password reset

            $this->deleteOldPasswordReminder($user->email);
            activityLog('User', $user->_id, 'Password Reset');
            return true;
        }

        return false;
    }

    /**
     * Delete old email change request.
     *
     * @param string $newEmail
     *
     * @return bool
     */
    public function deleteOldEmailChangeRequest($newEmail = null)
    {
        $expiryTime = time() - configItem('account.change_email_expiry')
                                * 60 * 60;

        return EmailChangeRequest::where([
                                'new_email' => $newEmail,
                                'users__id' => getUserID(),
                            ])
                            ->orWhere(
                                DB::raw('UNIX_TIMESTAMP(created_at)'),
                                '<',
                                $expiryTime
                            )
                            ->orWhere(['users__id' => getUserID()])
                            ->delete();
    }

    /**
     * Fetch temparary email.
     *
     * @param number $userID
     * @param string $activationKey
     *
     * @return eloquent collection object
     *---------------------------------------------------------------- */
    public function fetchTempEmail($userID, $activationKey)
    {
        return EmailChangeRequest::where([
                            'activation_key' => $activationKey,
                            'users__id' => $userID,
                        ])
                        ->select('new_email')
                        ->first();
    }

    /**
     * Fetch change email requested.
     *
     * @return eloquent collection object
     *---------------------------------------------------------------- */
    public function fetchChangeEmailRequested()
    {
        return EmailChangeRequest::where('users__id', getUserID())
                        ->select('new_email')
                        ->first();
    }

    /**
     * Update user email.
     *
     * @param string $newEmail
     *
     * @return bool
     *---------------------------------------------------------------- */
    public function updateEmail($newEmail)
    {
        $user = getAuthUser();

        $user->email = strtolower($newEmail);

        // Check if user email updated
        if ($user->save()) {
            $this->deleteOldEmailChangeRequest($newEmail);
            $userName = $user->first_name.' '.$user->last_name;
            activityLog(15, $user->_id, 2, $userName, $userName.' Change email to '.$user->email);
            return true;
        }

        return false;
    }
    /**
     * Fetch never activated user.
     *
     * @param number $userID
     * @param string $activationKey
     *
     * @return eloquent collection object
     *---------------------------------------------------------------- */
    public function fetchNeverActivatedUser($userID, $activationKey)
    {
        return UserModel::where([
                            'remember_token'    => $activationKey,
                            '_id'                => $userID,
                            'status'            => 12,  // never activated
                        ])
                        ->first();
    }

    /**
     * Fetch user profile.
     *
     * @return eloquent collection object
     *---------------------------------------------------------------- */
    public function fetchProfile()
    {   
        $userId = getUserID();
        return UserModel::leftJoin('user_authorities', 'users._id', '=', 'user_authorities.users__id')
                    ->leftJoin('user_roles', 'user_authorities.user_roles__id', '=', 'user_roles._id')
                    ->select(
                        __nestedKeyValues([
                            'users' => [
                                '_id',
                                '_uid',
                                'first_name',
                                'last_name',
                                'email'
                            ],
                            'user_roles' => [
                                'title'
                            ]
                        ])
                    )
                    ->where('users._id', '=', $userId)
                    ->first();
    }

    /**
     * Update profile.
     *
     * @param array $input
     *
     * @return bool
     *---------------------------------------------------------------- */
    public function updateProfile($input)
    {
        $user = getAuthUser();

        // Check if profile updated
        if ($user->modelUpdate([
                'first_name' => $input['first_name'],
                'last_name'  => $input['last_name'],
            ])) {

          $userFullName = $user->first_name.' '.$user->last_name;
            activityLog(2, $user->_id, 2, $userFullName);
            return $user;
        }

        return false;
    }

    /**
     * Fetch user by id.
     *
     * @param number $userID
     *
     * @return eloquent collection object
     *---------------------------------------------------------------- */
    public function fetchByID($userID)
    {
        return UserModel::find($userID);
    }

    /**
    * Delete Request
    *
    * @param object $model
    * @param array $data
    *
    * @return bool
    *---------------------------------------------------------------- */

    public function delete($model, $data = [])
    {
        // Check if user deleted
        if ($model->delete()) {

            return true;
        }

        return false;
    }

    /**
    * Update Request
    *
    * @param object $model
    * @param array $data
    *
    * @return bool
    *---------------------------------------------------------------- */

    public function update($model, $data)
    {
        // Check if user restored
        if ($model->modelUpdate($data)) {

            return $model;
        }

        return false;
    }


    /**
      * Store new active user & return response
      *
      * @param array $inputData
      *
      * @return boolean
      *---------------------------------------------------------------- */
    
    public function storeActive($inputData)
    {
        $keyValues = [
            'email'             => __ifisset($inputData['email']) ? strtolower($inputData['email']) : null,
            'password'          => bcrypt($inputData['password']),
            'status'            => 1,
            'first_name',
            'last_name',
      'username'
        ];

        $newUser = new UserModel;
       
        // Check if new User added
        if ($newUser->assignInputsAndSave($inputData, $keyValues)) {
            $userId = $newUser->_id;
            $userFullName = $newUser->first_name.' '.$newUser->last_name;
            activityLog(1, $userId, 1, $userFullName);
          
            return $newUser;
        }

        return false;   // on failed
    }
    /**
     * Fetch Profile
     *
     *
     * @return Eloquent Collection Object.
     *---------------------------------------------------------------- */

    public function fetchProfileData()
    {
        return UserProfileModel::where('users__id', getUserID())->first();
    }

    /**
      * Store profile
      *
      * @param array $inputData
      *
      * @return boolean
      *---------------------------------------------------------------- */
    
    public function storeProfile($inputData)
    {
        $keyValues = [
            'address_line_1',
            'address_line_2',
            'users__id'            => getUserID(),
            'countries__id'        => $inputData['country']
        ];

        if (__ifIsset($inputData['profile_picture'])) {
            $keyValues['profile_picture']    = $inputData['profile_picture'];
        }

        $userProfileModel = new UserProfileModel;
       
        // Check if new User added
        if ($userProfileModel->assignInputsAndSave($inputData, $keyValues)) {
            activityLog(2, $userProfileModel->_id, 2, $inputData['userFullName']);
            return true;
        }

        return false;   // on failed
    }

    /**
     * Update profile.
     *
     * @param array $input
     *
     * @return bool
     *---------------------------------------------------------------- */
    public function updateProfileData($profile, $input)
    {
        $updateData = [
            'address_line_1'    => $input['address_line_1'],
            'address_line_2'    => $input['address_line_2'],
            'countries__id'        => $input['country']
        ];

        if (__ifIsset($input['profile_picture'])) {
            $updateData['profile_picture']    = $input['profile_picture'];
        }

        // Check if profile updated
        if ($profile->modelUpdate($updateData)) {
          $user = $this->fetchUser($profile->users__id);
          $userFullName = $user->first_name.' '.$user->last_name;
          
            activityLog(2, $profile->_id, 2, $userFullName);
            return true;
        }

        return false;
    }


    /**
      * Verify the email is inactive
      *
      * @param string $email
      *
      * @return Eloquent collection object
      *-----------------------------------------------------------------------*/

    public function varifyUsernameOrEmail($usernameOrEmail)
    {
        return UserModel::where('email', $usernameOrEmail)
                    ->orWhere('username', $usernameOrEmail)
                    ->first();
    }

    /**
    * Fetch all role permissions
    *
    * @return Eloquent collection object
    *-----------------------------------------------------------------------*/
    public function fetchAllRoles()
    {
        return UserRoleModel::where('status', 1)
                    ->get();
    }

    /**
      * Store New user Authority
      *
      * @param number $userId
      *
      * @return Eloquent collection object
      *-----------------------------------------------------------------------*/
    public function storeUserAuthority($userId, $roleId = null)
    {
        $userAuthority = new UserAuthorityModel;

        $userAuthority->status          = 1;
        $userAuthority->users__id       = $userId;
        $userAuthority->user_roles__id  = (!__isEmpty($roleId))
                                            ? $roleId
                                            : 3; // Customer

        // Check if user authority stored successfully.
        if ($userAuthority->save()) {
            //activityLog('User Authority', $userAuthority->_id, 'Create');
            return true;
        }

        return false;
    }

    /**
    * Store New user Authority
    *
    * @param number $userId
    *
    * @return Eloquent collection object
    *-----------------------------------------------------------------------*/
    public function fetchUserAuthorities($userId)
    {
        return UserAuthorityModel::where('users__id', $userId)->first();
    }

    /**
    * Fetch User With Authority
    *
    * @return Eloquent collection object
    *-----------------------------------------------------------------------*/
    public function fetchUserWithAuthority($userId)
    {
        return UserModel::join('user_authorities', 'users._id', '=', 'user_authorities.users__id')
                    ->where('users._id', $userId)
                    ->select(
                        __nestedKeyValues([
                            'users' => [
                                '_id',
                                '_uid',
                                'first_name',
                                'last_name',
                                'username',
                                'email',
                                'status'
                            ],
                            'user_authorities' => [
                                'users__id',
                                'user_roles__id'
                            ]
                        ])
                    )
                    ->first();
    }

    /**
    * Update User Authority
    *
    * @return Eloquent collection object
    *-----------------------------------------------------------------------*/
    public function updateUserAuthority($userAuthority, $updateData)
    {
        if ($userAuthority->modelUpdate($updateData)) {
            return true;
        }

        return false;
    }

    /**
    * Fetch User by id
    *
    * @param int $userIdorUid
    *
    * @return void
    *-----------------------------------------------------------------------*/
    public function fetchUserData($userIdorUid)
    {
        return UserModel::with([
                        'profile'    => function ($profile) {
                            $profile->select('_id', 'users__id', 'address_line_1', 'address_line_2', 'countries__id');
                        }
                    ])
              ->join('user_authorities', 'users._id', '=', 'user_authorities.users__id')
              ->join('user_roles', 'user_authorities.user_roles__id', '=', 'user_roles._id')
              ->select(
                        'users._id',
                        'users._uid',
                        'user_authorities.user_roles__id',
                        'users.status',
                        DB::raw('CONCAT(users.first_name, " ", users.last_name) AS name'),
                        'users.email', 'username',
                        'users.created_at',
                        'users.updated_at',
                        'user_roles.title AS userRoleTitle'
                    )
              ->where('users._uid', $userIdorUid)
              ->first();
    }

    /**
    * Fetch User With Authority
    *
    * @return Eloquent collection object
    *-----------------------------------------------------------------------*/
    public function fetchUsersWithAuthority()
    {
        return UserModel::join('user_authorities', 'users._id', '=', 'user_authorities.users__id')
                    ->select(
                        __nestedKeyValues([
                            'users' => [
                                '_id',
                                '_uid',
                                'first_name',
                                'last_name',
                                'username',
                                'status'
                            ],
                            'user_authorities' => [
                                '_id AS authority_id',
                                'users__id',
                                'user_roles__id'
                            ]
                        ])
                    )
                    ->get();
    }

    /**
     * Update password.
     *
     * @param object $user
     * @param string $newPassword
     *
     * @return bool
     *---------------------------------------------------------------- */
    public function updatePassword($user, $newPassword)
    { 
        $user->password = bcrypt($newPassword);

        if ($user->save()) {
          $userName = $user->first_name.' '.$user->last_name;
            activityLog(5, $user->_id, 2, $userName);
            return true;
        }

        return false;
    }
}
