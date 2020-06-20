<?php

namespace lightningsdk\sitemanager\Model;

use lightningsdk\core\Model\CMSCore;
use lightningsdk\core\Tools\Database;

class CMS extends CMSCore {
    /**
     * @param string $name
     * @param integer $site_id
     * @return CMS
     */
    public static function loadByName($name, $site_id = null) {
        if (empty($site_id)) {
            $site_id = Site::getInstance()->id;
        }
        $content = Database::getInstance()->selectRow('cms', ['name' => $name, 'site_id' => $site_id]);
        if ($content) {
            return new static($content);
        } else {
            return false;
        }
    }

    public static function insertOrUpdate($update_values, $insert_values) {
        $update_values['site_id'] = Site::getInstance()->id;
        $insert_values['site_id'] = Site::getInstance()->id;
        return parent::insertOrUpdate($update_values, $insert_values);
    }
}
