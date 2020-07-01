<?php

namespace lightningsdk\sitemanager\Pages\Admin;

use lightningsdk\core\Pages\Table;
use lightningsdk\core\Tools\Database;
use lightningsdk\core\Tools\Request;
use lightningsdk\sitemanager\Model\Permissions;
use lightningsdk\core\Tools\ClientUser;

class Sites extends Table {

    const TABLE = 'site';
    const PRIMARY_KEY = 'site_id';

    protected $searchable = true;
    protected $search_fields = ['domain'];

    protected $duplicatable = true;

    protected $preset = [
        'menu' => 'hidden',
        'reservations' => 'hidden',
        'header_image' => 'hidden',
        'template' => 'hidden',
        'home_handler' => 'hidden',
        'custom_config' => 'hidden',
        'splash' => 'hidden',
        'css' => ['note' => 'DEPRECATED - DELETE THIS'],
        'logo' => 'hidden',
        'blog' => 'hidden',
        'contact' => ['note' => 'DEPRECATED - DELETE THIS'],
        'directions' => 'hidden',
        'site_menu' => 'hidden',
        'instagram' => ['note' => 'DEPRECATED - MOVE TO CONFIG - The full URL to the linstagram page.'],
        'pinterest' => ['note' => 'DEPRECATED - MOVE TO CONFIG - The full URL to the pinterest page.'],
        'youtube' => ['note' => 'DEPRECATED - MOVE TO CONFIG - The full URL to the youtube page.'],
        'facebook' => ['note' => 'DEPRECATED - MOVE TO CONFIG - The full URL to the facbeook page.'],
        'google' => ['note' => 'DEPRECATED - MOVE TO CONFIG - The full URL to the Google business page.'],
        'twitter' => ['note' => 'DEPRECATED - MOVE TO CONFIG - The twitter name only (no @ symbol).'],
        'linkedin' => ['note' => 'DEPRECATED - MOVE TO CONFIG - The full URL to the LinkedIn page.'],
        'linkedin_id' => ['note' => 'DEPRECATED - MOVE TO CONFIG - The linked in page ID (numeric value)'],
        'contact_emails' => 'json',
        'site_group_id' => 'hidden',
        'enabled' => 'checkbox',
    ];

    protected $sort = ['site_id' => 'DESC'];

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

    public function hasAccess() {
        ClientUser::requireLogin();
        $user = ClientUser::getInstance();
        if ($user->hasPermission(Permissions::EDIT_SITES)) {
            $siteGroup = $user->getSiteGroupsForPermission(Permissions::EDIT_SITES);
            if (empty($siteGroup)) {
                // This should never happen
                return false;
            }
            if (in_array("0", $siteGroup)) {
                // User has full access for all sites and groups
                return true;
            }
            // There are a limited number of groups.
            $this->accessControl = ['site_group_id' => ['IN', $siteGroup]];
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
