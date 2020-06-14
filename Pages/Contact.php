<?php

namespace Source\SiteAdmin;

use lightningsdk\sitemanagerr\Model\Site;

class Contact extends \lightningsdk\core\Pages\Admin\Contact {
    protected function initSettings() {
        $site = Site::getInstance();
        $this->accessControl['site_id'] = $site->id;
        $this->preset['site_id']['value'] = $site->id;
    }
}
