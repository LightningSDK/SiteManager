<?php
/**
 * @file
 * lightningsdk\core\Pages\Mailing\MessageLists
 */

namespace lightningsdk\sitemanager\Pages\Admin\Mailing;

use lightningsdk\sitemanager\Model\Site;

/**
 * A page handler for editing bulk mailer messages.
 *
 * @package lightningsdk\core\Pages\Mailing
 */
class Lists extends \lightningsdk\core\Pages\Mailing\Lists {
    protected $prefixRows = null;
    protected function initSettings() {
        parent::initSettings();
        $site = Site::getInstance();
        $this->preset['site_id'] = [
            'type' => 'hidden',
            'default' => $site->id,
            'force_default_new' => true,
        ];

        $this->accessControl['site_id'] = $site->id;
    }
}
