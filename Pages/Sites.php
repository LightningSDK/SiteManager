<?php

namespace lightningsdk\sitemanager\Pages;

use lightningsdk\core\Pages\Table;
use lightningsdk\core\Tools\Database;
use lightningsdk\core\Tools\Request;

class Sites extends Table {

    const TABLE = 'site';
    const PRIMARY_KEY = 'site_id';

    protected $duplicatable = true;

    protected $preset = [
        'menu' => 'checkbox',
        'reservations' => 'checkbox',
        'blog' => 'checkbox',
        'instagram' => ['note' => 'The full URL to the linstagram page.'],
        'pinterest' => ['note' => 'The full URL to the pinterest page.'],
        'youtube' => ['note' => 'The full URL to the youtube page.'],
        'facebook' => ['note' => 'The full URL to the facbeook page.'],
        'google' => ['note' => 'The full URL to the Google business page.'],
        'twitter' => ['note' => 'The twitter name only (no @ symbol).'],
        'linkedin' => ['note' => 'The full URL to the LinkedIn page.'],
        'linkedin_id' => ['note' => 'The linked in page ID (numeric value)'],
        'contact' => 'checkbox',
        'directions' => 'checkbox',
        'contact_emails' => 'json',
        'site_menu' => 'json',
        'site_group_id' => 'hidden',
    ];

    protected $action_fields = [
        'subdomains' => [
            'display_name' => 'Subdomains',
            'url' => '/admin/sites/subdomains?site_id=',
            'type' => 'link',
        ],
        'redirects' => [
            'display_name' => 'Domain Redirects',
            'url' => '/admin/sites/redirects?site_id=',
            'type' => 'link',
        ],
        'emails' => [
            'display_name' => 'Email Accounts',
            'url' => '/admin/sites/emails?site_id=',
            'type' => 'link',
        ],
    ];

    protected $searchable = true;
    protected $search_fields = ['domain'];

    public function hasAccess() {
//        ClientUser::requireLogin();
//        $user = ClientUser::getInstance();
//        if ($user->hasPermission(Permissions::EDIT_SITES)) {
//            $this->accessControl = ['site_group_id' => Site::getInstance()->site_group_id];
//            return true;
//        } elseif ($user->hasPermission(Permissions::EDIT_SITES)) {
//            return true;
//        } else {
            return false;
//        }
    }

    protected function afterDuplicate() {
        // Load the original site.
        $db = Database::getInstance();
        $original_id = Request::get('lightning_table_duplicate', 'int');
        $original_row = $db->selectRow('site', ['site_id' => $original_id]);

        // Copy the image directory
        $new_dir = Request::post('imagedir');
        if (!empty($new_dir) && !empty($original_row['imagedir'])) {
            exec('cp -r ' . HOME_PATH . '/images/' . $original_row['imagedir'] . ' ' . HOME_PATH . '/images/' . $new_dir);
        }

        // Copy pages
        $db->duplicateRowsQuery([
            'from' => 'page',
            'where' => ['site_id' => $original_id],
            'set' => ['site_id' => $this->id, 'page_id' => null],
        ]);

        // Copy CMS
        $db->duplicateRowsQuery([
            'from' => 'cms',
            'where' => ['site_id' => $original_id],
            'set' => ['site_id' => $this->id, 'cms_id' => null],
        ]);

        // Make sure the site group is the same.
        $db->update('site', ['site_group_id' => $original_row['site_group_id']], ['site_id' => $this->id]);
    }
}
