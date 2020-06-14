<?php

namespace lightningsdk\sitemanager\Model;

use lightningsdk\core\Model\PageOverridable;
use lightningsdk\core\Tools\Database;

class Page extends PageOverridable {
    public static function loadByURL($url) {
        return Database::getInstance()->selectRow(
            self::TABLE,
            [
                'site_id' => ['IN', [0, Site::getInstance()->id]],
                'url' => ['LIKE', $url]
            ], [], 'ORDER BY site_id DESC'
        );
    }

    public static function insertOrUpdate($new_values, $update_values) {
        $new_values['site_id'] = Site::getInstance()->id;
        parent::insertOrUpdate($new_values, $update_values);
    }

    public static function selectAllPages() {
        return Database::getInstance()->select(self::TABLE, ['site_map' => 1, 'site_id' => Site::getInstance()->id]);
    }
}
