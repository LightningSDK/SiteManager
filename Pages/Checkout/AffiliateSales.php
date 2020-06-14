<?php

namespace lightningsdk\sitemanager\Pages\Checkout;

use lightningsdk\sitemanager\Model\Site;

class AffiliateSales extends \lightningsdk\checkout\Pages\AffiliateSales {
    protected function getOrdersQuery($user) {
        $query = parent::getOrdersQuery($user);
        $query['where']['site_id'] = Site::getInstance()->id;
        return $query;
    }
}
