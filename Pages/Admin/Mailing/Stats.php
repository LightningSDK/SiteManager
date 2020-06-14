<?php

namespace lightningsdk\sitemanager\Pages\Admin\Mailing;

use lightningsdk\core\Tools\ClientUser;

class Stats extends \lightningsdk\core\Pages\Mailing\Stats {

    protected $ajax = true;

    protected function hasAccess() {
        ClientUser::requireAdmin();
        return true;
    }

    public function getGetData() {
        // TODO make sure the ID is a message for this site.

        parent::getGetData();
    }
}
