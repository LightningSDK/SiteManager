<?php

return [
    'routes' => [
        'static' => [
            'admin/sites' => 'Modules\\SiteManager\\Pages\\Sites',
            'admin/sites/emails' => 'Modules\\SiteManager\\Pages\\Emails',
            'admin/sites/subdomains' => 'Modules\\SiteManager\\Pages\\Subdomains',
            'admin/sites/redirects' => 'Modules\\SiteManager\\Pages\\Redirects',
            'affiliate/mysales' => 'Modules\\SiteManager\\Pages\\Checkout\\AffiliateSales',
            'admin/affiliates' => 'Modules\\SiteManager\\Pages\\Checkout\\Admin\\Affiliates',
        ],
        'cli_only' => [
            'site-domains' => 'Modules\\SiteManager\\Commands\\Domains',
            'site-nginx' => 'Modules\\SiteManager\\Commands\\Nginx',
            'site-certificates' => 'Modules\\SiteManager\\Commands\\Certificates',
        ]
    ],
    'jobs' => [
        'checkout-mailer' => [
            // Override the checkout mailer
            'class' => \Modules\SiteManager\Jobs\CheckoutMail::class,
        ],
        'auto-mailer' => [
            // Override the auto mailer
            'class' => \Modules\SiteManager\Jobs\AutoMailer::class,
        ],
    ],
    'modules' => [
        'site-manager' => [
            'cert-path' => '/etc/letsencrypt/live/',
            'nginx-config-file' => '/etc/nginx/sites-available/lightning-site-manager',
            'dns' => [
                'ipv4' => '127.0.0.1',
                'ipv6' => '0000:0000',
                'dmarc' => [
                    'postmaster' => 'postmaster@localhost',
                ],
                'bind9' => [
                    'compiled-directory' => 'compiled-directory/test',
                    'generic-domain-config' => '/etc/bind/compiled.default.zone.wildcard',
                    'compiled-zones-master-file' => '/etc/bind/named.conf.compiled-zones.test',
                ],
            ]
        ],
    ],
];
