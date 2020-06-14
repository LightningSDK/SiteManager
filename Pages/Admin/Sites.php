<?php

namespace lightningsdk\sitemanager\Pages\Admin;

use lightningsdk\core\Pages\Table;
use lightningsdk\core\Tools\Database;
use lightningsdk\core\Tools\Request;
use Source\Model\Permissions;
use lightningsdk\core\Tools\ClientUser;
use lightningsdk\sitemanager\Model\Site;

class Sites extends Table {

    protected $table = 'site';
    protected $key = 'site_id';

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
        'home_handler' => [
            'type' => 'select',
            'allow_blank' => false,
            'options' => [
                'lightningsdk\core\Pages\Splash' => 'Default Splash Page',
                'lightningsdk\core\Pages\Blog' => 'Blog Roll',
                'Source\Pub\Home' => 'Home Splash (Requires template)',
            ],
        ],
        'contact' => 'checkbox',
        'directions' => 'checkbox',
        'contact_emails' => 'json',
        'site_menu' => 'json',
        'site_group_id' => 'hidden',
    ];

    public function hasAccess() {
        ClientUser::requireLogin();
        $user = ClientUser::getInstance();
        if ($user->hasGroupPermission(Permissions::EDIT_SITES)) {
            $this->accessControl = ['site_group_id' => Site::getInstance()->site_group_id];
            return true;
        } elseif ($user->hasPermission(Permissions::EDIT_SITES)) {
            return true;
        } else
            return false;
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
