<?php

namespace Modules\SiteManager\Commands;

use Lightning\CLI\CLI;
use Lightning\Tools\Configuration;
use Lightning\Tools\Database;
use Lightning\Tools\Template;

class Nginx extends CLI {

    protected $server_prefix;
    protected $cert_path;
    protected $template;
    /**
     * @var Template
     */
    protected $server_template;

    public function executeUpdate () {
        // This only updates SSL sites because they need a custom entry for an ssl file
        // This assumes the certificate has been created with: certbot-auto ^Crtonly --webroot -w /var/www/sites -d concurrency.me -d www.concurrency.me
        $this->cert_path = Configuration::get('modules.site-manager.cert-path');
        $compiled_config_file = Configuration::get('modules.site-manager.nginx-config-file');
        $domains = Database::getInstance()->selectAll('site', ['requires_ssl' => 1]);
        $this->template = new Template();
        $this->template->setDirectory('');
        $this->server_template = Configuration::get('modules.site-manager.domain-template');

        $compiled_nginx = '';
        foreach ($domains as $d) {
            $compiled_nginx .= $this->configureDomain($d);
            $subdomains = Database::getInstance()->selectAll('site_redirect', ['site_id' => $d['site_id']]);
            foreach ($subdomains as $sd) {
                if  (file_exists("{$this->cert_path}{$sd['domain']}/fullchain.pem")) {
                    echo "{$sd['domain']} =>  {$d['domain']} \n";
                    $compiled_nginx .= $this->configureDomain($sd);
                } else {
                    echo "no certificate for {$sd['domain']} =>  {$d['domain']} \n";
                }
            }
        }

        if (Configuration::get('debug')) {
            echo "-------------------- FILE " . $compiled_config_file . " ----------------------\n";
            echo $compiled_nginx;
            echo "\n\n";
        } else {
            file_put_contents($compiled_config_file, $compiled_nginx);
        }
    }

    protected function configureDomain($domain) {
        $domainConfig = "server  {\n";
        if ($this->server_template) {
            $this->template->set('domain', $domain);
            $domainConfig .= $this->template->render($this->server_template, true);
        }
        $domainConfig .= "    server_name {$domain['domain']} www.{$domain['domain']};\n";
        $domainConfig .= "    ssl_certificate {$this->cert_path}{$domain['domain']}/fullchain.pem;\n";
        $domainConfig .= "    ssl_certificate_key {$this->cert_path}{$domain['domain']}/privkey.pem;\n";
        $domainConfig .= "}\n\n";
        return $domainConfig;
    }
}
