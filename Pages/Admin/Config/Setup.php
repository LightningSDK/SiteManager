<?php

namespace lightningsdk\sitemanager\Pages\Admin\Config;

use lightningsdk\core\Model\Permissions;
use lightningsdk\core\Tools\ClientUser;
use lightningsdk\core\Tools\Template;
use lightningsdk\sitemanager\Model\Config;
use lightningsdk\sitemanager\View\Page;

class Setup extends Page {

    protected $page = ['setup', 'lightningsdk/sitemanager'];

    protected function hasAccess() {
        return ClientUser::requirePermission(Permissions::ALL);
    }

    public function get() {

        $settings_list = [
            'metadata.image' => ['type' => 'image'],
        ];

        $settings = [];

        $config = Config::getInstance();

        foreach ($settings_list as $s) {
            $settings[] = [
                'set' => $config->get(),
            ];
        }

        $template = Template::getInstance();
        $template->set('settings', $settings);
    }
}
