<?php

namespace lightningsdk\sitemanager\Pages\Admin\Config;

use lightningsdk\core\Tools\ClientUser;
use lightningsdk\sitemanager\Model\Permissions;
use lightningsdk\sitemanager\Tools\Configuration;
use lightningsdk\sitemanager\View\Page;

class GetConfig extends Page {
    public function hasAccess() {
        return ClientUser::requirePermission(Permissions::EDIT_SITES);
    }

    public function get() {
        $config = Configuration::getConfiguration();
        $json_string = json_encode($config, JSON_PRETTY_PRINT);

        echo "<pre>{$json_string}</pre>";
        exit;
    }
}

