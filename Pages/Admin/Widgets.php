<?php

namespace lightningsdk\sitemanager\Pages\Admin;

use lightningsdk\core\Tools\ClientUser;
use lightningsdk\sitemanager\Model\Site;
use Source\Model\Permissions;

class Widgets extends \lightningsdk\core\Pages\Admin\Widgets {

    use TableSiteIdTrait;

    protected function initSettings() {
        parent::initSettings();
        $this->restrictToSite();
    }

    public function hasAccess() {
        ClientUser::requireLogin();
        $user = ClientUser::getInstance();
        return $user->hasPermission(Permissions::EDIT_PAGES) || $user->hasGroupPermission(Permissions::EDIT_PAGES);
    }
}
