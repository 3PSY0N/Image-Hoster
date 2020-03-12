<?php

namespace App\Services;

use Pagerfanta\View\TwitterBootstrapView;

class PagerView extends TwitterBootstrapView
{
    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'twitter_bootstrap4';
    }

    protected function createDefaultTemplate()
    {
        return new PagerTemplate();
    }
}


