<?php

namespace Modules\SiteManager\Pages\Checkout;

use Source\Model\Site;

class AffiliateSales extends \Modules\Checkout\Pages\AffiliateSales {
    protected function getOrdersQuery($user) {
        $query = parent::getOrdersQuery($user);
        $query['where']['site_id'] = Site::getInstance()->id;
        return $query;
    }
}
