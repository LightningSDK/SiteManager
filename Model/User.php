<?php

namespace lightningsdk\sitemanager\Model;

use lightningsdk\core\Model\UserOverridable;
use lightningsdk\core\Tools\Database;

class User extends UserOverridable {

    protected $groupPermissions;

    protected $__json_encoded_fields = [
        'data'
    ];

    protected $site;

    public function __construct(array $data = [])
    {
        parent::__construct($data);
        $this->site = Site::getInstance();
    }

    public function setSite($site) {
        $this->site = $site;
    }

    /**
     * Remove this user from all mailing lists.
     */
    public function unsubscribeAll() {
        $list_ids = Database::getInstance()->selectColumn('message_list', 'message_list_id', ['site_id' => Site::getInstance()->id]);
        Database::getInstance()->delete('message_list_user', ['user_id' => $this->id, 'message_list_id' => ['IN', $list_ids]]);
    }

    public function loadPermissions($force = false) {
        if (!$force && isset($this->permissions)) {
            return;
        }
        $this->permissions = Database::getInstance()->selectColumnQuery([
            'from' => 'user',
            'join' => [
                [
                    'join' => 'user_role',
                    'on' => ['user_role.user_id' => ['user.user_id']],
                ], [
                    'join' => 'role_permission',
                    'on' => ['role_permission.role_id' => ['user_role.role_id']],
                ], [
                    'join' => 'permission',
                    'on' => ['role_permission.permission_id' => ['permission.permission_id']],
                ], [
                    'join' => 'role',
                    'on' => ['user_role.role_id' => ['role.role_id']],
                ],
            ],
            'where' => [
                ['user.user_id' => $this->id],
                ['user_role.site_id' => ['IN', [0, $this->site->id]]],
            ],
            'select' => ['permission.permission_id', 'permission.permission_id'],
        ]);
    }

    public function hasPermission($permissionID) {
        $this->loadPermissions();
        $this->loadGroupPermissions();
        return !empty($this->permissions[$permissionID]) || !empty($this->permissions[Permissions::ALL])
            || !empty($this->groupPermissions[$permissionID]) || !empty($this->groupPermissions[Permissions::ALL]);
    }

    public function hasGroupPermission($permissionID) {
        $this->loadGroupPermissions();
        return !empty($this->groupPermissions[$permissionID]) || !empty($this->groupPermissions[Permissions::ALL]);
    }

    public function isTLMAdmin() {
        return $this->id == 1;
    }

    public function loadGroupPermissions($force = false) {
        if (!$force && isset($this->groupPermissions)) {
            return;
        }

        if ($this->site->site_group_id) {
            $this->groupPermissions = Database::getInstance()->selectColumnQuery([
                'from' => 'user',

                'join' => [
                    [
                        'JOIN',
                        'user_group_role',
                        'ON user_group_role.user_id = user.user_id AND user_group_role.site_group_id = ' . $this->site->site_group_id
                    ],
                    [
                        'JOIN',
                        'role_permission',
                        'ON role_permission.role_id=user_group_role.role_id',
                    ],
                    [
                        'JOIN',
                        'permission',
                        'ON role_permission.permission_id=permission.permission_id',
                    ],
                    // This is joined just to make sure it still exists
                    [
                        'JOIN',
                        'role',
                        'ON  user_group_role.role_id=role.role_id',
                    ]
                ],
                'where' => [
                    ['user.user_id' => $this->id],
                ],
                'select' => ['permission.permission_id', 'permission.permission_id'],
            ]);
        }
    }
}
