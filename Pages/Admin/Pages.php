<?php

namespace lightningsdk\sitemanager\Pages\Admin;

use lightningsdk\core\Tools\ClientUser;
use lightningsdk\core\Tools\Configuration;
use lightningsdk\sitemanager\Model\Permissions;
use lightningsdk\sitemanager\Model\Site;

class Pages extends \lightningsdk\core\Pages\Admin\Pages {
    use TableSiteIdTrait;

    public function hasAccess() {
        ClientUser::requireLogin();
        $user = ClientUser::getInstance();
        return $user->hasPermission(Permissions::EDIT_PAGES) || $user->hasGroupPermission(Permissions::EDIT_PAGES);
    }

    public function initSettings() {
        parent::initSettings();
        $this->restrictToSite();
    }
}
