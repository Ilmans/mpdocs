<?php
/*
* RolePermissionRepository.php - Repository file
*
* This file is part of the User component.
*-----------------------------------------------------------------------------*/

namespace App\Yantrana\Components\User\Repositories;

use App\Yantrana\Base\BaseRepository;
 
use App\Yantrana\Components\User\Models\UserRole;
use App\Yantrana\Components\User\Interfaces\RolePermissionRepositoryInterface;

class RolePermissionRepository extends BaseRepository implements RolePermissionRepositoryInterface
{
    
    /**
      * Fetch the record of RolePermission
      *
      * @param    int || string $idOrUid
      *
      * @return    eloquent collection object
      *---------------------------------------------------------------- */

    public function fetch($idOrUid)
    {
        if (is_numeric($idOrUid)) {
            return UserRole::where('_id', $idOrUid)->first();
        }

        return UserRole::where('_uid', $idOrUid)->first();
    }

    
    /**
      * Fetch rolePermission datatable source
      *
      * @return  mixed
      *---------------------------------------------------------------- */
 
    public function fetchRolePermissionDataTableSource()
    {
        $dataTableConfig = [
            'searchable' => [
                'title'
            ]
        ];

        return UserRole::dataTables($dataTableConfig)
                    ->toArray();
    }

    /**
      * Delete record and return response
      *
      * @param array $rolePermission
      *
      * @return  mixed
      *---------------------------------------------------------------- */

    public function deleteRolePermission($rolePermission)
    {
        // Check if $rolePermission deleted
        if ($rolePermission->delete()) {
            activityLog(3, $rolePermission->_id, 3, $rolePermission->title);
            return true;
        }

        return false;
    }

    /**
      * Store New User Role
      *
      * @param array $inputData
      *
      * @return  mixed
      *---------------------------------------------------------------- */
    public function store($inputData)
    {
        $keyValues = [
            'status' => 1,
            'title',
            '__permissions'
        ];

        $userRole = new UserRole;

        // Check if new user store
        if ($userRole->assignInputsAndSave($inputData, $keyValues)) {
            activityLog(3, $userRole->_id, 1, $userRole->title);
            return true;
        }

        return false;
    }

    /**
    * Update User Role
    *
    * @param model $userRole
    * @param array $updateData
    *
    * @return Eloquent collection object
    *-----------------------------------------------------------------------*/
    public function updateUserRole($userRole, $updateData)
    {
        if ($userRole->modelUpdate($updateData)) {
            activityLog(3, $userRole->_id, 2, $userRole->title);
            return true;
        }

        return false;
    }

    /**
    * Fetch all role permissions
    *
    * @return Eloquent collection object
    *-----------------------------------------------------------------------*/
    public function fetchAll()
    {
        return UserRole::where('status', 1)
                        ->get();
    }

    /**
     * Fetch Users count related to user role
     *
     * @param number $user_roles__id
     *
     * @return Eloquent collection object
     *-----------------------------------------------------------------------*/
    public function fetchUserCount($roleId)
    {
        return UserRole::where('_id', $roleId)
                        ->withCount(['users' => function ($query) use ($roleId) {
                            $query->where('user_roles__id', $roleId);
                        }])
                        ->first();
    }
}
