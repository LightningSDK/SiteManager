<?php

namespace Modules\SiteManager\Jobs;

class AmazonUpload extends \Modules\Checkout\Jobs\AmazonUpload {

    use SiteIteratorTrait;

    const NAME = 'SiteManager - Sync Amazon Products';
}
