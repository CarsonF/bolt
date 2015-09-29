<?php

namespace Bolt;

use Bolt\Provider;
use Silex;

class Services
{
    protected $services;

    /**
     * Services constructor.
     *
     * @param array $services
     */
    public function __construct($services = [])
    {
        $this->setAll($services);
    }

    public function setAll($services)
    {
        foreach ($services as $alias => $data) {
            if (is_array($data)) {
                $provider = $data[0];
                $priority = isset($data[1]) ? $data[1] : 0;
            } else {
                $provider = $data;
                $priority = 0;
            }

            $this->set($alias, $provider, $priority);
        }
    }

    public function set($alias, Silex\ServiceProviderInterface $provider, $priority = 0)
    {
        $this->services[$alias] = [$provider, $priority];
    }

    public function commit(Silex\Application $app)
    {
        foreach ($this->sorted() as $provider) {
            $app->register($provider);
        }
    }

    protected function sorted()
    {
        $toSort = [];

        foreach ($this->services as $alias => $data) {
            list($provider, $priority) = $data;
            $toSort[$priority][] = $provider;
        }

        krsort($toSort);
        $sorted = call_user_func_array('array_merge', $toSort);

        return $sorted;
    }
}
