<?php

namespace Modules\SiteManager\Pages;

use Lightning\Pages\Table;
use Lightning\Tools\ClientUser;
use Lightning\Tools\Configuration;
use Lightning\Tools\Database;
use Lightning\Tools\Request;
use Lightning\Tools\Scrub;
use Source\Model\Permissions;
use Source\Model\Site;

class Emails extends Table {

    const TABLE = 'mailbox';
    const PRIMARY_KEY = 'username';

    protected $preset = [
        'username' => [
            'editable' => false,
            'insertable' => true,
        ],
        'password' => [
            'unlisted' => true,
            'edit_value' => '',
        ],
        'storagebasedirectory' => [
            'type' => 'hidden',
            'default' => '/var/mail'
        ],
        'storagenode' => [
            'type' => 'hidden',
            'default' => 'vmail1',
        ],
        'maildir' => ['type' => 'hidden'],
        'language' => ['type' => 'hidden'],
        'quota' => ['type' => 'hidden'],
        'transport' => ['type' => 'hidden'],
        'employeeid' => ['type' => 'hidden'],
        'department' => ['type' => 'hidden'],
        'isadmin' => ['type' => 'hidden'],
        'isglobaladmin' => ['type' => 'hidden'],
        'rank' => ['type' => 'hidden'],
        'enablesmtp' => ['type' => 'hidden'],
        'enablesmtpsecured' => ['type' => 'hidden'],
        'enablepop3' => ['type' => 'hidden'],
        'enablepop3secured' => ['type' => 'hidden'],
        'enableimap' => ['type' => 'hidden'],
        'enableimapsecured' => ['type' => 'hidden'],
        'enabledeliver' => ['type' => 'hidden'],
        'enablelda' => ['type' => 'hidden'],
        'enablemanagesieve' => ['type' => 'hidden'],
        'enablemanagesievesecured' => ['type' => 'hidden'],
        'enablesieve' => ['type' => 'hidden'],
        'enablesievesecured' => ['type' => 'hidden'],
        'enableinternal' => ['type' => 'hidden'],
        'enabledoveadm' => ['type' => 'hidden'],
        'enablelib-storage' => ['type' => 'hidden'],
        'enableindexer-worker' => ['type' => 'hidden'],
        'enablelmtp' => ['type' => 'hidden'],
        'enabledsync' => ['type' => 'hidden'],
        'allow_nets' => ['type' => 'hidden'],
        'lastlogindate' => ['editable' => false],
        'lastloginipv4' => ['type' => 'hidden'],
        'lastloginprotocol' => ['type' => 'hidden'],
        'disclaimer' => ['type' => 'hidden'],
        'allowedsenders' => ['type' => 'hidden'],
        'rejectedsenders' => ['type' => 'hidden'],
        'allowedrecipients' => ['type' => 'hidden'],
        'rejectedrecipients' => ['type' => 'hidden'],
        'settings' => ['type' => 'hidden'],
        'passwordlastchange' => ['editable' => false],
        'created' => ['editable' => false],
        'modified' => ['editable' => false],
        'expired' => ['type' => 'hidden'],
        'active' => ['type' => 'hidden'],
        'local_part' => ['type' => 'hidden'],
        'domain' => [
            'type' => 'select',
            'insertable' => true,
            'editable' => false,
        ],
    ];

    public function initSettings(){
        $this->database = new Database(Configuration::get('database_emails'));
        $this->preset['username']['insert_function'] = [$this, 'setUsername'];
        $this->preset['password']['submit_function'] = [$this, 'setPassword'];
        $this->preset['maildir']['insert_function'] = [$this, 'setMaildir'];
        $this->preset['domain']['insert_function'] = [$this, 'setDomain'];
        $this->preset['domain']['display_value'] = [$this, 'getDomain'];
        if (ClientUser::getInstance()->isAdmin()) {
            $this->preset['transport']['type'] = 'string';
        }

        $db = Database::getInstance();
        $query = [
            'from' => 'site',
            'select' => ['site_id', 'domain']
        ];
        if (ClientUser::getInstance()->isAdmin()) {
            $domains = $db->selectColumnQuery($query);
        }
        $this->preset['domain']['options'] = $domains;
    }

    public function hasAccess() {
        ClientUser::requireLogin();
        if (ClientUser::getInstance()->hasGroupPermission(Permissions::EDIT_SITE) || ClientUser::getInstance()->isAdmin()) {
            $site_id = $this->getSiteId();
            if (empty($site_id)) {
                if (!ClientUser::getInstance()->isAdmin()) {
                    return false;
                }
            } else {
                $site = Site::loadById($site_id);
                $this->accessControl['domain'] = ['LIKE', $site->domain];
            }
            return true;
        }
        return false;
    }

    protected function getSiteId() {
        if ($this->siteId == null) {
            // If there is a row ID, that takes precedence.
            $email = Scrub::email($this->id);
            if (!empty($email)) {
                $this->getRow();
                $this->siteId = Database::getInstance()->selectField('site_id', 'site', ['domain' => ['LIKE', $this->list['domain']]]);
            }
            if (empty($this->siteId)) {
                $this->siteId = Request::get('site_id', Request::TYPE_INT) ?: Request::get('domain', Request::TYPE_INT);
            }
        }
        return $this->siteId;
    }

    protected function loadSite() {
        if (!isset($this->site)) {
            $this->site = Site::loadByID($this->getSiteId());
        }
    }

    protected function setUsername(&$row) {
        $this->loadSite();
        $name = strtolower(Request::post('username'));
        if (strlen($name) == 0) {
            throw new \Exception('You must enter a valid username');
        }
        $domain = $this->site->domain;
        $row['username'] = $name . '@' . $domain;
        $row['localpart'] = $name;
        if (!Scrub::email($row['username'])) {
            throw new \Exception('You must enter a valid username');
        }
    }

    protected function getDomain(&$row) {
        $this->loadSite();
        return $this->site->domain;
    }

    protected function setDomain(&$row) {
        $site_id = $this->getSiteId();
        $row['domain'] = $this->preset['domain']['options'][$site_id];
    }

    protected function setMaildir(&$row) {
        $this->loadSite();
        $name = strtolower(Request::post('username'));

        $row['maildir'] = $this->site->domain .
            '/' . $name[0] .
            (strlen($name) > 1 ? '/' . $name[1] : '') .
            (strlen($name) > 2 ? '/' . $name[2] : '') .
            '/' . $name;
    }

    protected function setPassword(&$row) {
        if ($pass = Request::get('password')) {
            // Encode the password.
            $row['password'] = $this->hashPassword($pass);
        } else if ($this->action == 'insert') {
            // Populate a random password if the account is just being created.
            $row['password'] = $this->hashPassword(random_bytes(30));
        }
    }

    protected function hashPassword($pass) {
        $salt = random_bytes(8);
        return '{SSHA512}' . base64_encode(hash('sha512', $pass . $salt, true) . $salt);
    }
}
