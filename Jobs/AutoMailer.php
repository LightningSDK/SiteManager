<?php

namespace lightningsdk\sitemanager\Jobs;

use lightningsdk\core\Jobs\Mailer;
use lightningsdk\core\Tools\Configuration;
use lightningsdk\core\Tools\Mailer as MailerTool;
use lightningsdk\sitemanagerr\Model\Site;

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
