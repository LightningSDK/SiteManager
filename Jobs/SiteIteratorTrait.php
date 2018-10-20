<?php

namespace Modules\SiteManager\Jobs;

use Lightning\Tools\Configuration;
use Source\Model\Site;

trait SiteIteratorTrait {
    public function execute($job) {
        $sites = Site::loadAll();
        foreach ($sites as $site) {
            Site::setInstance($site);
            Configuration::reload();
            $site->updateConfig();
            if (Configuration::get('stripe.public')) {
                $this->out('Sending checkout mail for: ' . $site->domain);
                parent::execute($job);
            }
        }
    }
}
