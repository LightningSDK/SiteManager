<?php

namespace lightningsdk\sitemanager\Model;

use lightningsdk\core\Model\BaseObject;
use lightningsdk\core\Model\ObjectDotReference;
use lightningsdk\core\Tools\ClientUser;
use lightningsdk\core\Tools\Messenger;
use lightningsdk\core\View\JS;

class Config extends BaseObject {

    const TABLE = 'site_config';
    const PRIMARY_KEY = 'site_id';

    use ObjectDotReference;
    const DOT_REFERENCE_FIELD = 'config';

    protected $__json_encoded_fields = ['config' => ['type' => 'array']];

    public function generateMissingCriticalElementAlerts() {
        if (ClientUser::getInstance()->isAdmin()) {
            JS::startup('lightning.modules.sitemanager.initAdmin();', ['lightningsdk/sitemanager' => 'js/config.js']);
            $configs = $this->getMissingCriticalElements();
            foreach ($configs as $config) {
                Messenger::error("Critical configuration missing: {$config} <a href='#' class='set-configuration' data-configuration='{$config}'>Configure</a>");
            }
        }
    }

    public function getMissingCriticalElements() {
        $criticalElements = [];
        $missing = [];
        foreach ($criticalElements as $element) {
            if (!$this->get($element)) {
                $missing[] = $element;
            }
        }

        return $missing;
    }
}
