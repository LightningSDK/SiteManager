<?php

namespace lightningsdk\sitemanager\Model\Mailing;

use lightningsdk\core\Tools\Database;
use lightningsdk\sitemanager\Model\Site;

class Message extends \lightningsdk\core\Model\Mailing\MessageOverridable {
    /**
     * Load the lists that this message can be sent to.
     */
    protected function loadLists() {
        if ($this->lists === null) {
            if (!empty($this->any_list)) {
                $this->lists = static::getAllListIDs();
            } else {
                $this->lists = Database::getInstance()->selectColumnQuery([
                    'select' => 'message_list_id',
                    'from' => 'message_message_list',
                    'join' => [
                        'join' => 'message_list',
                        'using' => 'message_list_id',
                    ],
                    'where' => [
                        'site_id' => Site::getInstance()->id,
                        'message_id' => $this->id,
                    ],
                ]);
            }
        }
    }

    public static function getAllLists() {
        return Database::getInstance()->selectColumn('message_list', 'name', ['site_id' => Site::getInstance()->id], 'message_list_id');
    }

    public static function getAllListIDs() {
        return Database::getInstance()->selectColumn('message_list', 'message_list_id', ['site_id' => Site::getInstance()->id]);
    }

    public static function validateListID($id) {
        return Database::getInstance()->check('message_list', ['message_list_id' => $id, 'site_id' => Site::getInstance()->id]);
    }

    public static function getDefaultListID() {
        $db = Database::getInstance();
        $site = Site::getInstance();
        $list = $db->selectField('message_list_id', 'message_list', ['name' => 'Default', 'site_id' => $site->id]);
        if (!$list) {
            $list = $db->insert('message_list', ['name' => 'Default', 'site_id' => $site->id]);
        }
        return $list;
    }

    public static function getListId($list_name) {
        $db = Database::getInstance();
        $site = Site::getInstance();
        if ($id = $db->selectField('message_list_id', 'message_list', ['site_id' => $site->id, 'name' => $list_name])) {
            return $id;
        }

        else {
            return $db->insert('message_list', ['site_id' => $site->id, 'name' => $list_name]);
        }
    }

    protected function getUsersQuery() {
        $query = parent::getUsersQuery();
        $query['join'][] = ['left_join' => 'message_list', 'on' => ['message_list.message_list_id' => ['message_list_user.message_list_id']]];
        $query['where']['message_list.site_id'] = Site::getInstance()->id;

        return $query;
    }

    protected function replaceCriteriaVariables(&$query, $variables = []) {
        $variables['SITE_ID'] = Site::getInstance()->id;
        return parent::replaceCriteriaVariables($query, $variables);
    }
}
