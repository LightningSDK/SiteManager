<?php

namespace lightningsdk\sitemanager\Jobs;

use lightningsdk\checkout\Jobs\Mail;
use lightningsdk\checkout\Model\Order;
use Source\Model\Site;

class CheckoutMail extends Mail {

    use SiteIteratorTrait;

    const NAME = 'SiteManager - Checkout Mailer';

    /**
     * Override function to make sure that carts loaded are restricted
     * to the current site in operation.
     *
     * @return array
     * @throws \Exception
     */
    protected function getCarts() {
        // Load the abandoned carts
        return Order::loadAll([
            'user_id' => ['>', 0],
            'locked' => 0,
            'time' => ['>', time() - (30 * 24 * 60 * 60)],
            'site_id' => Site::getInstance()->id,
        ]);
    }
}
