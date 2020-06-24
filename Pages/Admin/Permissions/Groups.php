<?php

namespace lightningsdk\sitemanager\Pages\Admin\Permissions;

use lightningsdk\core\Pages\Table;
use lightningsdk\core\Tools\ClientUser;
use lightningsdk\sitemanager\Model\Permissions;

class Groups extends Table {
    const TABLE = 'site_group';
    const PRIMARY_KEY = 'site_group_id';

    protected $action_fields = [
        'Users' => [
            'type' => 'link',
            'url' => '/admin/sites/groups/users?group_id=',
        ],
    ];

    public function hasAccess() {
        return ClientUser::requirePermission(Permissions::EDIT_SITES);
    }
}
