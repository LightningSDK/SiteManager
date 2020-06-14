<?php

namespace lightningsdk\sitemanager\Jobs;

use Exception;
use lightningsdk\core\Tools\Configuration;
use Source\Model\Site;

trait SiteIteratorTrait {
    public function execute($job) {
        $sites = Site::loadAll();
        foreach ($sites as $site) {
            Site::setInstance($site);
            Configuration::reload();
            $site->updateConfig();
            if (Configuration::get('stripe.public')) {
                $this->out('Switching to site: ' . $site->domain);
                try {
                    parent::execute($job);
                } catch (Exception $e) {
                    $this->out('Exception while executing job: ' . $e->getMessage());
                }
            }
        }
    }
}
