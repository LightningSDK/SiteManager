<?php

namespace lightningsdk\sitemanager\Model;

use lightningsdk\core\Model\UserCore;
use lightningsdk\core\Tools\Database;

class User extends UserCore {

    protected $groupPermissions;

    protected $__json_encoded_fields = [
        'data'
    ];

    protected $site;

    public function __construct(array $data = [])
    {
        parent::__construct($data);
        $this->site = Site::getInstance();
    }

    public function setSite($site) {
        $this->site = $site;
    }

    /**
     * Remove this user from all mailing lists.
     */
    public function unsubscribeAll() {
        $list_ids = Database::getInstance()->selectColumn('message_list', 'message_list_id', ['site_id' => Site::getInstance()->id]);
        Database::getInstance()->delete('message_list_user', ['user_id' => $this->id, 'message_list_id' => ['IN', $list_ids]]);
    }

    public function isTLMAdmin() {
        return $this->id == 1;
    }

}
