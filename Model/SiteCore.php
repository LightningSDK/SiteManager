<?php

namespace lightningsdk\sitemanager\Model;

use lightningsdk\core\Model\ObjectDatabaseStorage;
use lightningsdk\core\Tools\Cache\Cache;
use lightningsdk\core\Tools\Configuration;
use lightningsdk\core\Tools\Database;
use lightningsdk\core\Tools\Logger;
use lightningsdk\core\Tools\Navigation;
use lightningsdk\core\Tools\Output;
use lightningsdk\core\Tools\Request;
use lightningsdk\core\Tools\Singleton;

class SiteCore extends Singleton {

    use ObjectDatabaseStorage;

    const TABLE = 'site';
    const PRIMARY_KEY = 'site_id';

    /**
     * @var Config
     */
    protected $config;

    public function __construct($data) {
        $this->__data = $data;
        $this->initJSONEncodedFields();
    }

    public static function loadByDomain($domain) {
        return Database::getInstance()->selectRow(static::TABLE, ['domain' => $domain, 'enabled' => 1]);
    }

    public static function loadRedirectByDomain($domain) {
        return Database::getInstance()->selectRowQuery([
            'from' => 'site_redirect',
            'join' => [
                'join' => static::TABLE,
                'using' => static::PRIMARY_KEY,
            ],
            'select' => ['site.domain', 'site.requires_ssl'],
            'where' => ['site_redirect.domain' => $domain],
        ]);
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
        if ($site_data = static::loadByDomain($domain)) {
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
            if ($requestDomain = Request::query('domain')) {
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

    protected function getCookieDomain() {
        $domain = strtolower(Request::getDomain());

        // get test domain
        $testdomain = Configuration::get('modules.sitemanager.testdomain');

        if (Configuration::get('debug') || $domain == $testdomain) {
            return preg_replace('/:.*/', '', Request::getDomain());
        } else {
            return $this->domain;
        }
    }

    public function clearCache() {
        Configuration::clearCachedConfiguration();
    }

    public function getConfig() {
        /**
         * @var Config
         */
        if ($this->config == null) {
            $this->loadConfig();
        }

        return $this->config;
    }

    protected function loadConfig() {
        if ($this->config = Config::loadByID($this->id)) {

            $overrides = [
                'lightningsdk/blog' => 'lightningsdk/sitemanager-blog',
                'lightningsdk/checkout' => 'lightningsdk/sitemanager-checkout',
            ];

            // Override any customizable configs with the sitemanager version
            foreach ($this->config->get('modules.include') as $key => $include) {
                if (!empty($overrides[$include])) {
                    $this->config->set('modules.include.' . $key, $overrides[$include]);
                }
            }

            if (file_exists(HOME_PATH . '/css/domain/' . $this->domain . '.css')) {
                $this->config->push('page.css.include.site', '/css/domain/' . $this->domain . '.css');
            }

            $this->config->set('cookie_domain', $this->getCookieDomain());
        } else {
            $this->config = new Config([]);
        }
    }

    protected static function checkRedirect($domain) {
        // If it's not a site, see if it's a redirect
        if ($redirect = static::loadRedirectByDomain($domain)) {
            $source = Request::getURLWithParams();
            $params = $_GET;
            unset($params['request']);
            $proto = $redirect['requires_ssl'] == 1 ? 'https://' : 'http://';
            $destination = $proto . $redirect['domain'] . Request::get('request') . (!empty($params) ? '?' . http_build_query($params) : '');
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
