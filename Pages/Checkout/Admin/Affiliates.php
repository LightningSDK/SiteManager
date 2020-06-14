<?php

namespace lightningsdk\sitemanager\Pages\Checkout\Admin;

use lightningsdk\sitemanagerr\Model\Site;

class Affiliates extends \lightningsdk\checkout\Pages\Admin\Affiliates {
    protected function getAffiliatesDueQuery() {
        $query = parent::getAffiliatesDueQuery();
        $query['from']['where'] = [
            'site_id' => Site::getInstance()->id
        ];
        return $query;
    }
}
