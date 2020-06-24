<?php

namespace lightningsdk\sitemanager\Tools;

use lightningsdk\core\Tools\Cache\Cache;
use lightningsdk\core\Tools\ConfigurationCore;
use lightningsdk\sitemanager\Model\Site;

class Configuration extends ConfigurationCore {

    protected static function loadCachedConfig() {
        if (!\lightningsdk\core\Tools\Configuration::get('debug')) {
            $cache = Cache::get(Cache::PHP_FILE);
            if ($cached_config = $cache->get(Site::getInstance()->domain . '_config')) {
                // override the entire config
                static::$configuration = $cached_config;
                return true;
            }
        }
        return false;
    }

    protected static function writeCachedConfiguration() {
        if (!Configuration::get('debug')) {
            // Not debug mode, save the cache.
            $cache = Cache::get(Cache::PHP_FILE);
            $cache->set(Site::getInstance()->domain . '_config', static::$configuration);
        }
    }

}
