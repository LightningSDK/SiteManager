<?php

namespace lightningsdk\sitemanager\View;

use lightningsdk\core\Tools\Template;
use lightningsdk\core\View\PageCore;
use Source\Model\Site;

class Page extends PageCore {
    public function __construct() {
        parent::__construct();

        Template::getInstance()->set('site', Site::getInstance());

        \lightningsdk\sitemanager\Model\Site::getInstance()->getConfig()->generateMissingCriticalElementAlerts();
    }
}
