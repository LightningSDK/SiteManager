<?php

namespace lightningsdk\sitemanager\Pages\Admin\Mailing;

use lightningsdk\sitemanager\Model\Site;

class Templates extends \lightningsdk\core\Pages\Mailing\Templates {
    protected function initSettings() {
        parent::initSettings();
        $site = Site::getInstance();
        $this->preset['site_id'] = [
            'type' => 'hidden',
            'default' => $site->id,
            'force_default_new' => true,
        ];

        $this->accessControl['site_id'] = $site->id;
    }
}
