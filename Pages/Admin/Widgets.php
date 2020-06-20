<?php

namespace lightningsdk\sitemanager\Pages\Admin;

use lightningsdk\core\Model\Permissions;
use lightningsdk\core\Tools\ClientUser;

class Widgets extends \lightningsdk\core\Pages\Admin\Widgets {

    use TableSiteIdTrait;

    protected function initSettings() {
        parent::initSettings();
        $this->restrictToSite();
    }

    public function hasAccess() {
        ClientUser::requireLogin();
        return ClientUser::getInstance()->hasPermission(Permissions::EDIT_PAGES);
    }
}
