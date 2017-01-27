<?php

namespace Bolt\Twig;

interface ListLoaderInterface
{
    /**
     * @return \Iterator|\Twig_Source[]
     */
    public function listTemplates();
}
