<?php

namespace lightningsdk\sitemanager\Pages;

use lightningsdk\sitemanager\Model\Site;

class Contact extends \lightningsdk\core\Pages\Contact {
    public function getContactFields() {
        return parent::getContactFields() + ['site_id' => Site::getInstance()->id];
    }
}
