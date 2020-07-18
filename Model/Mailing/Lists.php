<?php

namespace lightningsdk\sitemanager\Model\Mailing;

use lightningsdk\core\Model\Mailing\ListsCore;
use lightningsdk\core\Tools\Database;
use lightningsdk\sitemanager\Model\Site;

class Lists extends ListsCore {
    public static function loadOptions($name_field, $where = []) {
        return Database::getInstance()->selectColumn(static::TABLE, $name_field, [
            'site_id' => Site::getInstance()->id,
        ], static::PRIMARY_KEY);
    }

    public static function getOptions() {
        return Database::getInstance()->selectColumn('message_list', 'name', ['site_id' => Site::getInstance()->id], 'message_list_id');
    }


    public static function getAllIDs() {
        return Database::getInstance()->selectColumn('message_list', 'message_list_id', ['site_id' => Site::getInstance()->id]);
    }

}
