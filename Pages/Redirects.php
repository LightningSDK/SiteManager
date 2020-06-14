<?php

namespace lightningsdk\sitemanager\Pages;

use lightningsdk\core\Pages\Table;
use lightningsdk\core\Tools\ClientUser;

class Redirects extends Table {
    public function hasAccess() {
        return ClientUser::requireLogin();
    }
}
