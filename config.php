<?php

return [
    'routes' => [
        'static' => [
            'admin/sites' => 'Modules\\SiteManager\\Pages\\Sites',
            'admin/sites/emails' => 'Modules\\SiteManager\\Pages\\Emails',
            'admin/sites/subdomains' => 'Modules\\SiteManager\\Pages\\Subdomains',
            'admin/sites/redirects' => 'Modules\\SiteManager\\Pages\\Redirects',
        ]
    ]
];
