<?php

namespace lightningsdk\sitemanager\Pages\Admin;

use lightningsdk\core\Tools\ClientUser;
use lightningsdk\sitemanager\Model\Permissions;
use lightningsdk\sitemanager\Model\Site;

trait TableSiteIdTrait {
    protected function restrictToSite() {
        $site = Site::getInstance();
        if (ClientUser::getInstance()->hasPermission(Permissions::EDIT_SITE)) {
            $this->accessControl['site_id'] = ['IN', [$site->id, 0]];
            $this->preset['site_id'] = [
                'type' => 'select',
                'default' => $site->id,
                'options' => [
                    0 => 'All Sites',
                    $site->id => 'This Site',
                ]
            ];
        } else {
            $this->accessControl['site_id'] = $site->id;
            $this->preset['site_id'] = [
                'type' => 'hidden',
                'default' => $site->id,
                'force_default_new' => true,
            ];
        }
    }

}
