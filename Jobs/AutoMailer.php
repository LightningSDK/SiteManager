<?php

namespace Modules\SiteManager\Jobs;

use Lightning\Jobs\Mailer;
use Lightning\Tools\Configuration;
use Lightning\Tools\Mailer as MailerTool;
use Source\Model\Site;

class AutoMailer extends Mailer {

    const NAME = 'SiteManager - Mailer';

    protected $currentSite;

    protected function sendMessage($message) {
        // Load the correct site configuration
        if ($this->currentSite != $message->site_id) {
            $this->switchToSite($message->site_id);
            $this->currentSite = $message->site_id;
            $this->mailer = new MailerTool();
        }

        // Proceed as normal
        parent::sendMessage($message);
    }

    protected function switchToSite($site_id) {
        $site = Site::loadByID($site_id);
        $this->out('Loading config for: ' . $site->domain);
        Site::setInstance($site);
        Configuration::reload();
        $site->updateConfig();
    }
}
