<?php

namespace lightningsdk\sitemanager\API;

use lightningsdk\core\Tools\ClientUser;
use lightningsdk\core\Tools\Form as FormTool;
use lightningsdk\core\Tools\Output;
use lightningsdk\core\Tools\Request;
use lightningsdk\core\Tools\Template;
use lightningsdk\core\View\API;
use lightningsdk\sitemanager\Model\Site;

class Configurations extends API {
    public function hasAccess(){
        return ClientUser::requirePermission('admin');
    }

    public function get() {
        $template = Template::getInstance();
        $template->setTemplate(['config_field', 'lightningsdk/sitemanager']);
        $field = Request::get('field');
        $template->set('configField', $field);
        $template->set('configValue', Site::getInstance()->getConfig()->get($field));
        echo $template->render();
        exit;
    }

    public function post() {
        FormTool::validateToken();

        $field = Request::post('field');
        $value = Request::post('value');
        $config = Site::getInstance()->getConfig();
        $config->set($field, $value);
        $config->save();
        return Output::SUCCESS;
    }
}
