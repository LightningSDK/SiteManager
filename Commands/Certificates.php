<?php

namespace lightningsdk\sitemanager\Commands;

use lightningsdk\core\CLI\CLI;
use lightningsdk\core\Tools\Configuration;
use lightningsdk\core\Tools\Database;
use lightningsdk\core\Tools\Template;

class Certificates extends CLI {

    protected $web_root;
    protected $cert_path;
    protected $template;

    public function executeUpdate () {
        // This only updates SSL sites because they need a custom entry for an ssl file
        // This assumes the certificate has been created with: certbot-auto ^Crtonly --webroot -w /var/www/sites -d concurrency.me -d www.concurrency.me
        $this->cert_path = Configuration::get('modules.sitemanager.cert-path');
        $this->webroot = HOME_PATH;
        $domains = Database::getInstance()->selectAll('site', ['requires_ssl' => 1, 'enabled' => 1]);

        foreach ($domains as $d) {
            $this->ensureCertsExist($d['domain']);
            $subdomains = Database::getInstance()->selectAll('site_redirect', ['site_id' => $d['site_id']]);
            foreach ($subdomains as $sd) {
                $this->ensureCertsExist($sd['domain']);
            }
        }
    }

    protected function ensureCertsExist($domain) {
        if  (!file_exists("{$this->cert_path}{$domain}/fullchain.pem")) {
            $this->out("Creating cert for domain: $domain");
            $command = "certbot-auto certonly --webroot -w {$this->webroot} -d {$domain} -d www.{$domain}";
            $this->out('Running command: ' . $command);
            if (!Configuration::get('debug')) {
                exec($command, $resp);
                $this->out(implode("\n", $resp));
            }
        } else {
            $this->out("Cert already exists for domain: $domain");
        }
    }
}
