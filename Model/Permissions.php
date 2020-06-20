<?php

namespace lightningsdk\sitemanager\Model;

use lightningsdk\core\Model\PermissionsCore;
use lightningsdk\core\Tools\Database;

class Permissions extends PermissionsCore {
    const EDIT_MENU = 102;
    const EDIT_LOCATIONS = 103;
    const EDIT_SITES = 104;
    const EDIT_SITE = 105;
    const TRANSCRIBE_AUDIO = 106;
    const FACEBOOK_SEARCH = 107;

    protected $groupPermissions;

    /**
     * @var \lightningsdk\sitemanager\Model\Site
     */
    protected $site;

    public function __construct($userid) {
        $this->site = Site::getInstance();
        parent::__construct($userid);
        $this->loadGroupPermissions();
    }

    protected function loadPermissions() {
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
                ['user.user_id' => $this->userid],
                ['user_role.site_id' => ['IN', [0, $this->site->id]]],
            ],
            'select' => ['permission.permission_id', 'permission.permission_id'],
        ]);
    }

    protected function loadGroupPermissions() {
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
                    ['user.user_id' => $this->userid],
                ],
                'select' => ['permission.permission_id', 'permission.permission_id'],
            ]);
        }
    }

    public function hasPermission($permissionID) {
        if ($permissionID == static::EDIT_SITES) {
            return !empty($this->permissions[$permissionID]) || !empty($this->groupPermissions[$permissionID]);
        }
        return !empty($this->permissions[$permissionID]) || !empty($this->permissions[Permissions::ALL])
            || !empty($this->groupPermissions[$permissionID]) || !empty($this->groupPermissions[Permissions::ALL]);
    }
}
