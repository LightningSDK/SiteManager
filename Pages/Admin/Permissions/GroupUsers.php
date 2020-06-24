<?php

namespace lightningsdk\sitemanager\Pages\Admin\Permissions;

use lightningsdk\core\Pages\Table;
use lightningsdk\core\Tools\ClientUser;
use lightningsdk\core\Tools\Request;
use lightningsdk\core\View\TablePresets;
use lightningsdk\sitemanager\Model\Permissions;

class GroupUsers extends Table {
    const TABLE = 'user_group_role';

    protected $preset = [
        'site_group_id' => [
            'display_name'  => 'Site Group',
            'type' => 'lookup',
            'lookuptable' => 'site_group',
            'display_column' => 'group_name',
        ],
        'role_id' => [
            'display_name'  => 'Role',
            'type' => 'lookup',
            'lookuptable' => 'role',
            'display_column' => 'name',
        ],
    ];

    public function hasAccess() {
        return ClientUser::requirePermission(Permissions::EDIT_SITES);
    }

    public function initSettings() {
        parent::initSettings();

        $this->accessControl['site_group_id'] = Request::get('group_id');

        $this->preset['user_id'] = TablePresets::userSearch();
    }

    public function getRow($force=true) {
        $id = explode('-', Request::get('id'));
        $this->list = $this->database->selectRowQuery([
            'from' => static::TABLE,
            'where' => [
                'site_group_id' => $id[0],
                'user_id' => $id[1],
            ],
        ]);
    }

    public function getRowId($row) {
        return $row['site_group_id'] . '-' . $row['user_id'];
    }
}
