<?php

namespace Modules\SiteManager\Pages\Checkout\Admin;

use Source\Model\Site;

class Affiliates extends \Modules\Checkout\Pages\Admin\Affiliates {
    protected function getAffiliatesDueQuery() {
        $query = parent::getAffiliatesDueQuery();
        $query['from']['where'] = [
            'site_id' => Site::getInstance()->id
        ];
        return $query;
    }
}
