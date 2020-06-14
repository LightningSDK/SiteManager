<?php

return [
    'routes' => [
        'static' => [
            'admin/sites' => lightningsdk\sitemanager\Pages\Sites::class,
            'admin/sites/emails' => lightningsdk\sitemanager\Pages\Emails::class,
            'admin/sites/subdomains' => lightningsdk\sitemanager\Pages\Subdomains::class,
            'admin/sites/redirects' => lightningsdk\sitemanager\Pages\Redirects::class,
            'affiliate/mysales' => lightningsdk\sitemanager\Pages\Checkout\AffiliateSales::class,
            'admin/affiliates' => lightningsdk\sitemanager\Pages\Checkout\Admin\Affiliates::class,

            'admin/contact' => \Source\SiteAdmin\Contact::class,
            'admin/mailing/lists' => \Source\SiteAdmin\Mailing\Lists::class,
            'admin/mailing/messages' => \Source\SiteAdmin\Mailing\Messages::class,
            'admin/mailing/stats' => \Source\SiteAdmin\Mailing\Stats::class,
            'admin/mailing/templates' => \Source\SiteAdmin\Mailing\Templates::class,
            'admin/pages' => lightningsdk\sitemanager\Pages\Admin\Pages::class,
            'admin/widgets' => lightningsdk\sitemanager\Pages\Admin\Pages::class,
            'admin/users' => lightningsdk\sitemanager\Pages\Admin\Users::class,

            'admin/config' => lightningsdk\sitemanager\Pages\Admin\Config\Main::class,
            'admin/sites' => \Source\Admin\Sites::class,
        ],
        'cli_only' => [
            'site-domains' => lightningsdk\sitemanager\Commands\Domains::class,
            'site-nginx' => \c\Commands\Nginx::class,
            'site-certificates' => lightningsdk\sitemanager\Commands\Certificates::class,
        ]
    ],
    'classes' => [
        lightningsdk\core\Model\Mailing\Lists::class => lightningsdk\sitemanager\Model\Mailing\Lists::class,
        lightningsdk\core\Model\Page::class => lightningsdk\sitemanager\Model\Page::class,
    ],
    'jobs' => [
        'checkout-mailer' => [
            // Override the checkout mailer
            'class' => \lightningsdk\sitemanager\Jobs\CheckoutMail::class,
        ],
        'auto-mailer' => [
            // Override the auto mailer
            'class' => \lightningsdk\sitemanager\Jobs\AutoMailer::class,
        ],
    ],
    'modules' => [
        'sitemanager' => [
            'cert-path' => '/etc/letsencrypt/live/',
            'nginx-config-file' => '/etc/nginx/sites-available/lightning-sitemanager',
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
