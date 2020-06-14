<?php

namespace lightningsdk\sitemanager\Commands;

use lightningsdk\core\CLI\CLI;
use lightningsdk\core\Tools\Configuration;
use lightningsdk\core\Tools\Database;
use lightningsdk\core\Tools\Template;

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
        $this->cert_path = Configuration::get('modules.sitemanager.cert-path');
        $compiled_config_file = Configuration::get('modules.sitemanager.nginx-config-file');
        $domains = Database::getInstance()->selectAll('site', ['requires_ssl' => 1]);
        $this->template = new Template();
        $this->template->setDirectory('');
        $this->server_template = Configuration::get('modules.sitemanager.domain-template');

        $compiled_nginx = '';
        foreach ($domains as $d) {
            $this->out("adding site: {$d['domain']}");
            $compiled_nginx .= $this->configureDomain($d);
            $subdomains = Database::getInstance()->selectAll('site_redirect', ['site_id' => $d['site_id']]);
            foreach ($subdomains as $sd) {
                if  (file_exists("{$this->cert_path}{$sd['domain']}/fullchain.pem")) {
                    $this->out("Adding redirect {$sd['domain']} => {$d['domain']}");
                    $compiled_nginx .= $this->configureDomain($sd);
                } else {
                    $this->out("no certificate for {$sd['domain']} =>  {$d['domain']}");
                }
            }
        }

        if (Configuration::get('debug')) {
            $this->out("-------------------- FILE " . $compiled_config_file . " ----------------------");
            $this->out($compiled_nginx);
        } else {
            $this->out("Writing to $compiled_config_file");
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
