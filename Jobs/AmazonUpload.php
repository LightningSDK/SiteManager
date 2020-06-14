<?php

namespace lightningsdk\sitemanager\Jobs;

class AmazonUpload extends \lightningsdk\checkout\Jobs\AmazonUpload {

    use SiteIteratorTrait;

    const NAME = 'SiteManager - Sync Amazon Products';
}
