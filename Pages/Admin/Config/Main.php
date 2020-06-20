<?php

namespace lightningsdk\sitemanager\Pages\Admin\Config;

use lightningsdk\core\Tools\Database;
use lightningsdk\core\Tools\Messenger;
use lightningsdk\core\Pages\JSONEditor;
use lightningsdk\sitemanager\Model\Permissions;
use lightningsdk\core\Tools\ClientUser;
use lightningsdk\sitemanager\Model\Site;

class Main extends JSONEditor {

    protected $rightColumn = false;

    public function hasAccess() {
        ClientUser::requireLogin();
        $user = ClientUser::getInstance();
        return $user->hasPermission(Permissions::EDIT_SITE) || $user->hasGroupPermission(Permissions::EDIT_SITE);
    }

    public function getJSONData() {
        $value = Database::getInstance()->selectField('config', 'site_config', ['site_id' => Site::getInstance()->id]);
        return json_decode($value, true) ?: [];
    }

    public function post() {
        $config = $this->postedData();
        $site = Site::getInstance();
        Database::getInstance()->insert('site_config', ['config' => $config, 'site_id' => $site->id], ['config' => $config]);
        Messenger::message('The new configuration has been applied.');
        $site->clearCache();

        $this->redirect();
    }
}
