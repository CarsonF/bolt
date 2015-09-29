<?php

namespace Bolt;

use Silex;

class NewApplication extends Silex\Application
{
    public function __construct(array $values, Services $services = null)
    {
        parent::__construct($values);

        $services = $services ?: ServiceBuilder::createDefault();
        $services->commit($this);
    }
}
