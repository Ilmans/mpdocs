<?php
/*
* RolePermissionController.php - Controller file
*
* This file is part of the User component.
*-----------------------------------------------------------------------------*/

namespace App\Yantrana\Components\User\Controllers;

use App\Yantrana\Base\BaseController;

use App\Yantrana\Components\User\RolePermissionEngine;

use App\Yantrana\Components\User\Requests\{
    RoleDynamicAccessRequest, AddRoleRequest
};

class RolePermissionController extends BaseController
{
    /**
     * @var  RolePermissionEngine $rolePermissionEngine - RolePermission Engine
     */
    protected $rolePermissionEngine;

    /**
      * Constructor
      *
      * @param  RolePermissionEngine $rolePermissionEngine - RolePermission Engine
      *
      * @return  void
      *-----------------------------------------------------------------------*/

    public function __construct(RolePermissionEngine $rolePermissionEngine)
    {
        $this->rolePermissionEngine = $rolePermissionEngine;
    }
    
    /**
      * Get Add Support Data
      *
      * @return  json object
      *---------------------------------------------------------------- */
    public function getAddSuppotData()
    {
        $processReaction = $this->rolePermissionEngine
                                ->prepareAddSupportData();

        return __processResponse($processReaction, [], [], true);
    }

    /**
      * Get Permission by Role If
      *
      * @param number $roleId
      *
      * @return  json object
      *---------------------------------------------------------------- */
    public function getPermissionById($roleId)
    {
        $processReaction = $this->rolePermissionEngine
                                ->preparePermissionByRoleId($roleId);

        return __secureProcessResponse($processReaction, [], [], true);
    }

    /**
      * Add New Role Permissions
      *
      * @return  json object
      *---------------------------------------------------------------- */
    public function addNewRole(AddRoleRequest $request)
    {
        $processReaction = $this->rolePermissionEngine
                                ->processAddNewRole($request->all());

        return __secureProcessResponse($processReaction, [], [], true);
    }

    /**
      * Get Role Permission edit support data
      *
      * @param number $roleId
      *
      * @return  json object
      *---------------------------------------------------------------- */
    public function getUpdateData($roleId)
    {
        $processReaction = $this->rolePermissionEngine
                                ->prepareEditSupportData($roleId);

        return __processResponse($processReaction, [], [], true);
    }
    
    /**
      * list of RolePermission
      *
      * @return  json object
      *---------------------------------------------------------------- */

    public function prepareRolePermissionList()
    {
        return $this->rolePermissionEngine
                      ->prepareRolePermissionDataTableSource();
    }
     
    /**
      * Role Permission process delete
      *
      * @param  mix $rolePermissionIdOrUid
      *
      * @return  json object
      *---------------------------------------------------------------- */

    public function processRolePermissionDelete($rolePermissionIdOrUid)
    {
        $processReaction = $this->rolePermissionEngine
                                ->processRolePermissionDelete($rolePermissionIdOrUid);

        return __processResponse($processReaction, [], [], true);
    }

    /**
      * Get Role Permissions
      *
      * @param number $roleId
      *
      * @return  json object
      *---------------------------------------------------------------- */
    public function getPermissions($roleId)
    {
        $processReaction = $this->rolePermissionEngine->preparePermissions($roleId);

        return __secureProcessResponse($processReaction, null, null, true);
    }

    /**
      * Process Dynamic role permission process
      *
      * @param object RoleDynamicAccessRequest $request
      * @param number $roleId
      *
      * @return  json object
      *---------------------------------------------------------------- */
    public function processDynamicRolePermission($roleId, RoleDynamicAccessRequest $request)
    {
        $processReaction = $this->rolePermissionEngine
                                ->processCreateRolePermission($roleId, $request->all());

        return __processResponse($processReaction, [], [], true);
    }
}
