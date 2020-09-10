<?php

namespace lightningsdk\sitemanager\Commands;

use lightningsdk\core\CLI\CLI;
use lightningsdk\core\Tools\Configuration;
use lightningsdk\core\Tools\Database;

class Certificates extends CLI {

    protected $web_root;
    protected $cert_path;
    protected $template;

    protected $domains = [];

    public function executeList() {
        $this->getDomainList();
        foreach ($this->domains as $domain => $is_subdomain) {
            if (!$is_subdomain && !preg_match('/.*\..*\..*/', $domain)) {
                echo "www.{$domain}\n";
            } else {
                echo "{$domain}\n";
            }
        }
    }

    public function executeUpdate () {
        $this->getDomainList();
        foreach ($this->domains as $domain => $is_subdomain) {
            $this->ensureCertsExist($domain, $is_subdomain);
        }
    }

    protected function getDomainList() {
        // This only updates SSL sites because they need a custom entry for an ssl file
        // This assumes the certificate has been created with: certbot-auto ^Crtonly --webroot -w /var/www/sites -d concurrency.me -d www.concurrency.me
        $this->cert_path = Configuration::get('modules.sitemanager.cert-path');
        $this->webroot = HOME_PATH;
        $domains = Database::getInstance()->selectAll('site', ['enabled' => 1]);

        foreach ($domains as $d) {
            $this->domains[$d['domain']] = false;
            $redirects = Database::getInstance()->selectAll('site_redirect', ['site_id' => $d['site_id']]);
            foreach ($redirects as $r) {
                $this->domains[$r['domain']] = false;
            }

            $subdomains = Database::getInstance()->selectAll('site_subdomain', ['site_id' => $d['site_id'], 'type' => 'LOCAL']);
            foreach ($subdomains as $sd) {
                $this->domains[$d['domain']] = true;
            }
        }
    }

    protected function ensureCertsExist($domain, $is_subdomain = false) {
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
