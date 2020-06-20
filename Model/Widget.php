<?php

namespace lightningsdk\sitemanager\Model;

use lightningsdk\core\Model\WidgetCore;
use lightningsdk\core\Tools\Database;

class Widget extends WidgetCore {
    /**
     * @param string $name
     * @param integer $site_id
     * @return \lightningsdk\core\Model\Widget
     */
    public static function loadByName($name, $site_id = null) {
        if (empty($site_id)) {
            $site_id = Site::getInstance()->id;
        }
        $content = Database::getInstance()->selectRow(static::TABLE, ['name' => $name, 'site_id' => ['IN', [0, $site_id]]]);
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
