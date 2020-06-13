<?php

namespace Modules\SiteManager\Commands;

use Lightning\CLI\CLI;
use Lightning\Tools\Configuration;
use Lightning\Tools\Database;

class Domains extends CLI {
    public function executeUpdate () {
        $domains = Database::getInstance()->selectAll('site', ['enabled' => 1]);
        $compiled_zones_content = '';
        $ipv4 = Configuration::get('modules.site-manager.dns.ipv4');
        $ipv6 = Configuration::get('modules.site-manager.dns.ipv6');
        $postmaster = Configuration::get('modules.site-manager.dns.dmarc.postmaster');
        $zone_dns_header = file_get_contents(Configuration::get('modules.site-manager.dns.bind9.zone-template-dns-header'));
        $zone_mail = file_get_contents(Configuration::get('modules.site-manager.dns.bind9.zone-template-mail'));
        $compiled_directory = Configuration::get('modules.site-manager.dns.bind9.compiled-directory');
        $generic_domain = Configuration::get('modules.site-manager.dns.bind9.generic-domain-config');
        $compiled_zones_master_file = Configuration::get('modules.site-manager.dns.bind9.compiled-zones-master-file');
        if (!Configuration::get('debug')) {
            mkdir($compiled_directory, 755, true);
        }
        foreach ($domains as $d) {
            $subdomains = Database::getInstance()->selectAll('site_subdomain', ['site_id' => $d['site_id']]);
            if ($subdomains) {
                // Use a custom zone
                $compiled_zones_content .= '
                    zone "' . $d['domain'] . '" in {
                        type master;
                        file "/etc/bind/compiled/db.' . $d['domain'] . '";
                    };
                    ';

                $custom_zone_contents = '';

                $has_default_mx = false;
                $custom_dmarc = false;
                $custom_root = false;
                $custom_www = false;
                foreach ($subdomains as $s) {
                    switch ($s['type']) {
                        case 'MX':
                            if ($s['subdomain'] == '@') {
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
                    if ($ipv4) {
                        $custom_zone_contents .= "\n" . '@ IN A ' . $ipv4;
                    }
                    if ($ipv6) {
                        $custom_zone_contents .= "\n" . '@ IN AAAA ' . $ipv6;
                    }
                }
                if (!$custom_www) {
                    if ($ipv4) {
                        $custom_zone_contents .= "\n" . 'www IN ' . $ipv4;
                    }
                    if ($ipv6) {
                        $custom_zone_contents .= "\n" . 'www IN AAAA ' . $ipv6;
                    }
                }

                if (!$custom_dmarc) {
                    $custom_zone_contents .= "\n" . '_dmarc 14400   IN    TXT     "v=DMARC1;pct=100;ruf=mailto:' . $postmaster . '};rua=mailto:' . $postmaster . ';p=quarantine;sp=reject;adkim=r;aspf=r"';
                }

                // if the domain did not have a default mail MX record
                if (!$has_default_mx) {
                    $custom_zone_contents .= "\n" . '@       IN       MX  10   mail' . "\n";
                } else {
                    $custom_zone_contents .= $zone_mail;
                }

                $full_file = $compiled_directory . '/db.' . $d['domain'];
                if (Configuration::get('debug')) {
                    $this->out("-------------------- FILE " . $full_file . " ----------------------");
                    $this->out($zone_dns_header . $custom_zone_contents);
                }  else {
                    file_put_contents($full_file, $zone_dns_header . $custom_zone_contents);
                }
            } else {
                // Use the default zone
                $compiled_zones_content .= '
                    zone "' . $d['domain'] . '" in {
                        type master;
                        file "' . $generic_domain . '";
                    };
                    ';
            }
        }
        foreach (Database::getInstance()->select('site_redirect') as $redirect) {
            $compiled_zones_content .= '
                    zone "' . $redirect['domain'] . '" in {
                        type master;
                        file "' . $generic_domain . '";
                    };
                    ';
        }

        if (Configuration::get('debug')) {
            $this->out("-------------------- FILE " . $compiled_zones_master_file . " ----------------------");
            $this->out($compiled_zones_content);
        }  else {
            file_put_contents($compiled_zones_master_file, $compiled_zones_content);
        }
    }
}
