<?php

namespace Source\SiteAdmin;

use lightningsdk\core\Pages\Table;
use lightningsdk\core\Tools\ClientUser;
use lightningsdk\sitemanager\Model\Site;

class Settings extends Table {
    const TABLE = 'site';
    const PRIMARY_KEY = 'site_id';

    protected $singularity = true;
    protected $fieldOrder = ['contact_emails', 'facebook', 'youtube', 'google', 'twitter', 'linkedin', 'linkedin_id', 'instagram', 'pinterest', 'title', 'name'];
    protected $preset = [
        'contact_emails' => [
            'type' => 'json',
            'note' => 'When a user submits a contact request, an email will be sent to these emails. You can use multiple emails separated by a comma.'
        ],
        'title' => ['display_name' => 'site_title'],
        'name' => ['display_name' => 'Owner Name', 'note' => 'This is who mailer messages will come from.'],
        'instagram' => ['note' => 'The full URL to the linstagram page.'],
        'pinterest' => ['note' => 'The full URL to the pinterest page.'],
        'youtube' => ['note' => 'The full URL to the youtube page.'],
        'facebook' => ['note' => 'The full URL to the facbeook page.'],
        'google' => ['note' => 'The full URL to the Google business page.'],
        'twitter' => ['note' => 'The twitter name only (no @ symbol).'],
        'linkedin' => ['note' => 'The full URL to the LinkedIn page.'],
        'linkedin_id' => ['note' => 'The linked in page ID (numeric value)'],
    ];

    protected $savedMessage = 'Your settings have been saved.';

    public function hasAccess() {
        ClientUser::requireLogin();
        $user = ClientUser::getInstance();
        return $user->hasPermission(Permissions::EDIT_SITE);
    }

    protected function initSettings() {
        $this->singularityID = Site::getInstance()->id;
    }
}
