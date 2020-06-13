<?php

namespace Source\SiteAdmin;

use lightningsdk\core\Model\Message;
use lightningsdk\core\Tools\ClientUser;
use lightningsdk\core\Tools\Database;
use lightningsdk\core\Tools\Request;
use lightningsdk\core\View\Field\BasicHTML;
use lightningsdk\core\View\Field\Text;
use Source\Model\Site;

class Users extends \lightningsdk\core\Pages\Admin\Users {
    protected function initSettings() {
        if (!ClientUser::getInstance()->isTLMAdmin()) {
            $this->editable = false;
            $this->addable = false;
            $this->deleteable = false;
            $this->action_fields = [];
            $this->custom_buttons = null;
            unset($this->links['user_tag']);
        }

        parent::initSettings();
        $this->accessControl = ['message_list_id' => ['IN', array_keys(Message::getAllLists())]];
        $this->accessTable = 'message_list_user';
        $this->accessTableJoin = [
            'join' => 'message_list_user',
            'using' => 'user_id',
        ];
    }

    public function importPostProcess(&$values, &$ids) {
        static $mailing_list_id;
        $db = Database::getInstance();

        $site = Site::getInstance();

        if (!isset($mailing_list_id)) {
            if (!$mailing_list_id = Request::get('message_list_id', 'int')) {
                // No default list was selected
                if ($new_list = trim(Request::get('new_message_list'))) {
                    $mailing_list_id = $db->insert('message_list', ['name' => $new_list, 'site_id' => $site->id]);
                } else {
                    $mailing_list_id = false;
                }
            }
        }

        $time = time();

        // This will only update users that were just added.
        $db->update('user', ['created' => $time], ['user_id' => ['IN', $ids]]);

        // This will add all the users to the mailing list.
        if (!empty($mailing_list_id)) {
            $user_ids = $db->selectColumn('user', 'user_id', ['email' => ['IN', $values['email']]]);
            $db->insertMultiple('message_list_user', [
                'user_id' => $user_ids,
                'message_list_id' => $mailing_list_id,
                'time' => $time,
            ], true);
        }
    }

    public function customImportFields() {
        $all_lists = ['' => ''] + Database::getInstance()->selectColumn('message_list', 'name', ['site_id' => Site::getInstance()->id], 'message_list_id');
        $output = 'Add all imported users to this mailing list: ' . BasicHTML::select('message_list_id', $all_lists);
        $output .= 'Or add them to a new mailing list: ' . Text::textField('new_message_list', '');
        return $output;
    }

    public function getImpersonate() {
        return;
    }
}
