<?php

namespace lightningsdk\sitemanager\Pages\Admin;

use lightningsdk\core\Tools\ClientUser;
use lightningsdk\core\Tools\Configuration;
use Source\Model\Permissions;
use lightningsdk\sitemanagerr\Model\Site;

class Pages extends \lightningsdk\core\Pages\Admin\Pages {

    public function hasAccess() {
        ClientUser::requireLogin();
        $user = ClientUser::getInstance();
        return $user->hasPermission(Permissions::EDIT_PAGES) || $user->hasGroupPermission(Permissions::EDIT_PAGES);
    }

    protected function initSettings() {
        parent::initSettings();
        $site = Site::getInstance();
        $this->accessControl['site_id'] = $site->id;
        $this->preset['site_id'] = [
            'type' => 'hidden',
            'default' => $site->id,
            'force_default_new' => true,
        ];

        $this->preset['template'] = [
            'type' => 'select',
            'options' => Configuration::get('page_templates'),
        ];
    }
}
