<?php

namespace lightningsdk\sitemanager\Pages\Admin;

use lightningsdk\sitemanager\Model\Site;

trait TableSiteIdTrait {
    protected function restrictToSite() {
        $site = Site::getInstance();
        $this->accessControl['site_id'] = $site->id;
        $this->preset['site_id'] = [
            'type' => 'hidden',
            'default' => $site->id,
            'force_default_new' => true,
        ];
    }

}
