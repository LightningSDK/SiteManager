<?php

namespace lightningsdk\sitemanager\Pages\Admin\Config;

use lightningsdk\core\Pages\Table;
use lightningsdk\core\Tools\ClientUser;
use lightningsdk\core\Tools\Request;
use Source\Model\Permissions;
use lightningsdk\sitemanagerr\Model\Site;

class Subdomains extends Table {

    const TABLE = 'site_subdomain';
    const PRIMARY_KEY = 'subdomain_id';

    protected $preset = [
        'site_id' => [
            'type' => 'lookup',
            'lookuptable' => 'site',
            'lookupkey' => 'site_id',
            'display_column' => 'domain',
        ],
    ];

    public function hasAccess() {
        ClientUser::requireLogin();
        $user = ClientUser::getInstance();

        if ($site_id = Request::get('site_id', Request::TYPE_INT)) {
            $this->accessControl['site_id'] = $site_id;
            $this->parentId = $site_id;
            if ($site_id == Site::getInstance()->id && $user->hasPermission(Permissions::EDIT_SITE)) {
                return true;
            }
            $this->parentLink = 'site_id';
        }

        if ($user->isAdmin()) {
            return true;
        }

        return false;
    }
}
