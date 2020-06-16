<?php

namespace lightningsdk\sitemanager\Model;

use lightningsdk\core\Model\Permissions as CorePermissions;

class Permissions extends CorePermissions {
    const EDIT_MENU = 102;
    const EDIT_LOCATIONS = 103;
    const EDIT_SITES = 104;
    const EDIT_SITE = 105;
    const TRANSCRIBE_AUDIO = 106;
    const FACEBOOK_SEARCH = 107;
}
