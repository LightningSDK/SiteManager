<?php

namespace Modules\SiteManager\Pages;

use Lightning\Pages\Table;
use Lightning\Tools\ClientUser;

class Redirects extends Table {
    public function hasAccess() {
        return ClientUser::requireLogin();
    }
}
