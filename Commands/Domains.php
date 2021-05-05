<?php

namespace lightningsdk\sitemanager\Commands;

use lightningsdk\core\CLI\CLI;
use lightningsdk\core\Tools\Configuration;
use lightningsdk\core\Tools\Database;
use lightningsdk\core\Tools\Template;

class Domains extends CLI {
    public function executeUpdate () {
        $domains = Database::getInstance()->selectAll('site', ['enabled' => 1]);
        $this->compiled_zones_content = '';
        $this->ipv4 = Configuration::get('modules.sitemanager.dns.ipv4');
        $this->ipv6 = Configuration::get('modules.sitemanager.dns.ipv6');
        $this->postmaster = Configuration::get('modules.sitemanager.dns.dmarc.postmaster');
        $dmarc_admin_domain = Configuration::get('modules.sitemanager.dns.dmarc.admin-domain');
        $this->dmarc_admin_secondary_domains = [];
        $this->zone_dns_header = file_get_contents(Configuration::get('modules.sitemanager.dns.bind9.zone-template-dns-header'));
        $zone_mail_file = Configuration::get('modules.sitemanager.dns.bind9.zone-template-mail');
        $template = Template::getInstance();
        $this->zone_mail = $template->adminBuild($zone_mail_file);
        $this->compiled_directory = Configuration::get('modules.sitemanager.dns.bind9.compiled-directory');
        $this->generic_domain = Configuration::get('modules.sitemanager.dns.bind9.generic-domain-config');
        $compiled_zones_master_file = Configuration::get('modules.sitemanager.dns.bind9.compiled-zones-master-file');
        if (!Configuration::get('debug')) {
            if (!is_dir($this->compiled_directory)) {
                mkdir($this->compiled_directory, 755, true);
            }
        }
        foreach ($domains as $d) {
            if (strtolower($dmarc_admin_domain) == strtolower($d['domain'])) {
                // skip this domain for now
                $dmarc_admin_domain_data = $d; continue;
            }
            $this->process_domain($d);
        }

        // return to the dkim admin domain
        if (!empty($dmarc_admin_domain_data)) {
            $dmarc_admin_domain_data['aggregate_dmarc'] = $this->dmarc_admin_secondary_domains;
            $this->process_domain($dmarc_admin_domain_data);
        }

        foreach (Database::getInstance()->select('site_redirect') as $redirect) {
            $this->compiled_zones_content .= '
                    zone "' . $redirect['domain'] . '" in {
                        type master;
                        file "' . $this->generic_domain . '";
                    };
                    ';
        }

        if (Configuration::get('debug')) {
            $this->out("-------------------- FILE " . $compiled_zones_master_file . " ----------------------");
            $this->out($this->compiled_zones_content);
        }  else {
            $this->out('Writing to: ' . $compiled_zones_master_file);
            file_put_contents($compiled_zones_master_file, $this->compiled_zones_content);
        }
    }

    protected function process_domain($d) {
        $custom_zone_contents = '';

        $has_custom_mx = false;
        $has_default_mx = false;
        $custom_dmarc = false;
        $custom_root = false;
        $custom_www = false;

        $subdomains = Database::getInstance()->selectAll('site_subdomain', ['site_id' => $d['site_id']]);
        if ($subdomains) {
            $this->out("Processing domain: {$d['domain']} using a custom zone");
            // Use a custom zone
            $this->compiled_zones_content .= '
                    zone "' . $d['domain'] . '" in {
                        type master;
                        file "/etc/bind/zones/compiled/db.' . $d['domain'] . '";
                    };
                    ';

            foreach ($subdomains as $s) {
                switch ($s['type']) {
                    case 'MX':
                        $has_custom_mx = true;
                        if ($s['subdomain'] == '@') {
                            // hads a default MX and no others are needed
                            $has_default_mx = true;
                        }
                        $priority = !empty($s['priority']) ? $s['priority'] : 10;
                        $custom_zone_contents .= "\n" . $s['subdomain'] . ' IN MX ' . $priority . ' ' . $s['location'] . "\n";
                        break;
                    case 'SRV':
                        $custom_zone_contents .= "\n" . $s['subdomain'] . ' IN SRV '
                            . (!empty($s['priority']) ? $s['priority'] : 0) . ' '
                            . (!empty($s['weight']) ? $s['weight'] : 0) . ' '
                            . $s['port'] . ' ' . $s['location'] . "\n";
                        break;
                    case 'CNAME':
                    case 'A':
                        if ($s['subdomain'] == '@') {
                            $custom_root = true;
                        } elseif ($s['subdomain'] == 'www') {
                            $custom_www = true;
                        }
                        $custom_zone_contents .= "\n" . $s['subdomain'] . ' IN ' . $s['type'] . ' ' . $s['location'];
                        break;
                    case 'LOCAL':
                        if ($this->ipv4) {
                            $custom_zone_contents .= "\n{$s['subdomain']} IN A $this->ipv4";
                        }
                        if ($this->ipv6) {
                            $custom_zone_contents .= "\n{$s['subdomain']} IN AAAA $this->ipv6";
                        }
                        break;
                    case 'TXT':
                        if (strpos($s['location'], '"') !== false && strlen($s['location']) > 100) {
                            $split = chunk_split($s['location'], 100, "\"\n\"");
                            $split = str_replace("\"\"\n\"", '"', $split);
                            $s['location'] = '(' . $split . ')';
                        }
                    default:
                        if ($s['subdomain'] == '_dmarc') {
                            $custom_dmarc = true;
                        }
                        $custom_zone_contents .= "\n" . $s['subdomain'] . ' IN ' . $s['type'] . ' ' . $s['location'];
                        break;
                }
            }

            if (!$custom_root) {
                if ($this->ipv4) {
                    $custom_zone_contents .= "\n" . '@ IN A ' . $this->ipv4;
                }
                if ($this->ipv6) {
                    $custom_zone_contents .= "\n" . '@ IN AAAA ' . $this->ipv6;
                }
            }
            if (!$custom_www) {
                if ($this->ipv4) {
                    $custom_zone_contents .= "\n" . 'www IN A ' . $this->ipv4;
                }
                if ($this->ipv6) {
                    $custom_zone_contents .= "\n" . 'www IN AAAA ' . $this->ipv6;
                }
            }

            if (!$custom_dmarc) {
                $this->dmarc_admin_secondary_domains[] = $d['domain'];
                $custom_zone_contents .= "\n" . '_dmarc 14400   IN    TXT     "v=DMARC1;p=quarantine;pct=100;ruf=mailto:' . $this->postmaster . ';rua=mailto:' . $this->postmaster . ';sp=reject;adkim=r;aspf=r"';
            }
            if (!empty($d['aggregate_dmarc'])) {
                foreach ($d['aggregate_dmarc'] as $add) {
                    $custom_zone_contents .= "\n" . $add . '._report._dmarc 14400   IN    TXT     "v=DMARC1"';
                }
            }

            // if the domain did not have a default mail MX record
            if (!$has_custom_mx) {
                // No MX records, add the defaults
                $custom_zone_contents .= "\n" . $this->zone_mail;
            }
            if (!$has_default_mx) {

                // There is a custom MX record but not for the default zone
                $custom_zone_contents .= "\n" . '@       IN       MX  10   mail' . "\n";
            }

            $full_file = $this->compiled_directory . '/db.' . $d['domain'];
            if (Configuration::get('debug')) {
                $this->out("-------------------- FILE " . $full_file . " ----------------------");
                $this->out($this->zone_dns_header . $custom_zone_contents);
            }  else {
                $this->out('Writing to: ' . $full_file);
                file_put_contents($full_file, $this->zone_dns_header . $custom_zone_contents);
            }
        } else {
            // Use the default zone
            $this->out("Processing domain: {$d['domain']} using the default zone.");
            $this->dmarc_admin_secondary_domains[] = $d['domain'];
            $this->compiled_zones_content .= '
                    zone "' . $d['domain'] . '" in {
                        type master;
                        file "' . $this->generic_domain . '";
                    };
                    ';
        }
    }
}
