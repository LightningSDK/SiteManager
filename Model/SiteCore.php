<?php

namespace lightningsdk\sitemanager\Model;

use lightningsdk\core\Model\ObjectDatabaseStorage;
use lightningsdk\core\Tools\Cache\Cache;
use lightningsdk\core\Tools\ClassLoader;
use lightningsdk\core\Tools\Configuration;
use lightningsdk\core\Tools\Database;
use lightningsdk\core\Tools\Logger;
use lightningsdk\core\Tools\Navigation;
use lightningsdk\core\Tools\Output;
use lightningsdk\core\Tools\Request;
use lightningsdk\core\Tools\Singleton;
use lightningsdk\core\View\CSS;

class SiteCore extends Singleton {

    use ObjectDatabaseStorage;

    const TABLE = 'site';
    const PRIMARY_KEY = 'site_id';

    public function __construct($data) {
        $this->__data = $data;
        $this->initJSONEncodedFields();
    }

    /**
     * When the site singleton is initiated, we also check for and handle redirects.
     * Rediercts might also be handled by the router.
     * @return Site
     * @throws \Exception
     */
    public static function createInstance() {
        if (Request::isCLI()) {
            // If it's not found, just return a new object with only the domain.
            return new static(['domain' => 'cli']);
        }

        $domain = static::getDomain();

        // Load the site settings
        if ($site_data = Database::getInstance()->selectRow(static::TABLE, ['domain' => $domain])) {
            // HTTPS Redirect if required
            static::SSLRedirect($site_data);
            return new static($site_data);
        }

        // If he domain does not exist, see if there is a redirect entry and forward it.
        static::checkRedirect($domain);

        throw new \Exception("Domain {$domain} not configured");
    }

    protected static function getDomain() {
        $domain = strtolower(Request::getDomain());

        // get test domain
        $testdomain = Configuration::get('modules.sitemanager.testdomain');

        // Load the domain from a cookie in debug mode
        if (Configuration::get('debug') || $domain == $testdomain) {
            if ($requestDomain = Request::get('domain')) {
                $domain = $requestDomain;
                Output::setCookie('domain', $requestDomain);
            }
            elseif ($cookieDomain = Request::cookie('domain')) {
                $domain = $cookieDomain;
            }
        }

        // Remove the www prefix
        $domain = preg_replace('/^www\./', '', $domain);

        return $domain;
    }

    public function clearCache() {
        if (!Configuration::get('debug')) {
            // Not debug mode, save the cache.
            $cache = Cache::get(Cache::PERMANENT);
            $cache->unset($this->domain . '_config');
        }
    }

    protected function getConfig() {
        if ($config = Config::loadByID($this->id)) {
            $config = $config->config;

            $overrides = [
                'lightningsdk/blog' => 'lightningsdk/sitemanager-blog',
                'lightningsdk/checkout' => 'lightningsdk/sitemanager-checkout',
            ];

            // Override any customizable configs with the sitemanager version
            foreach ($config['modules']['include'] as $key => $include) {
                if (!empty($overrides[$include])) {
                    $config['modules']['include'][$key] = $overrides[$include];
                }
            }

            if (file_exists(HOME_PATH . '/css/domain/' . $this->domain . '.css')) {
                Configuration::push('page.css.include.site', '/css/domain/' . $this->domain . '.css');
            }

            $config['cookie_domain'] = preg_replace('/:.*/', '', $this->domain);

            return $config;
        }

        return [];
    }

    protected static function checkRedirect($domain) {
        // If it's not a site, see if it's a redirect
        if ($redirect = Database::getInstance()->selectRowQuery([
            'from' => 'site_redirect',
            'join' => [
                'join' => static::TABLE,
                'using' => static::PRIMARY_KEY,
            ],
            'select' => ['site.domain'],
            'where' => ['site_redirect.domain' => $domain],
        ])) {
            $source = Request::getURLWithParams();
            $destination = 'http://' . $redirect['domain'];
            Logger::info("Redirecting requested domain from [{$source}] to [{$destination}]");
            Navigation::redirect($destination, [], true);
        }
    }

    protected static function SSLRedirect($site_data) {
        if (!empty($site_data['requires_ssl']) && !Request::isHTTPS() && !Configuration::get('debug')) {
            $params = $_GET;
            unset($params['request']);
            $source = Request::getURLWithParams();
            $destination = str_ireplace('http://', 'https://', Request::getURL());
            Logger::info("Redirecting request for SSL from [{$source}] to [{$destination}]");
            Navigation::redirect($destination, $params, true);
        }
    }
}
