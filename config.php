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
];
