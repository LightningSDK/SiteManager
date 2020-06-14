<?php

namespace lightningsdk\sitemanager\Pages\Admin;

use lightningsdk\core\Tools\ClientUser;
use lightningsdk\sitemanager\Model\Site;
use Source\Model\Permissions;

class Widgets extends \lightningsdk\core\Pages\Admin\Widgets {
    protected function initSettings() {
        parent::initSettings();
        $site = Site::getInstance();
        $this->accessControl['site_id'] = $site->id;
        $this->preset['site_id'] = [
            'type' => 'hidden',
            'default' => $site->id,
            'force_default_new' => true,
        ];
    }

    public function hasAccess() {
        ClientUser::requireLogin();
        $user = ClientUser::getInstance();
        return $user->hasPermission(Permissions::EDIT_PAGES) || $user->hasGroupPermission(Permissions::EDIT_PAGES);
    }
}
