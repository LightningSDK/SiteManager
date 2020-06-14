<?php

namespace lightningsdk\sitemanager\Model;

use lightningsdk\core\Model\BaseObject;

class Config extends BaseObject {

    const TABLE = 'site_config';
    const PRIMARY_KEY = 'site_id';

    protected $__json_encoded_fields = ['config' => ['type' => 'array']];
}
