<?php

namespace App\Services;

use Pagerfanta\View\Template\TwitterBootstrap4Template;

class PagerTemplate extends TwitterBootstrap4Template
{
    protected function linkLi($class, $href, $text, $rel = null)
    {
        $liClass = implode(' ', array_filter(['page-item', $class]));
        $rel     = $rel ? sprintf(' rel="%s"', $rel) : '';

        return sprintf('<li class="%s"><a class="page-link" href="%s"%s>%s</a></li>', $liClass, $href, $rel, $text);
    }

    protected function spanLi($class, $text)
    {
        $liClass = implode(' ', array_filter(['page-item', $class]));

        return sprintf('<li class="%s"><span class="page-link">%s</span></li>', $liClass, $text);
    }
}
