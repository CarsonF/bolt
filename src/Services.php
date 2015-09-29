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
    public function __construct($services)
    {
        $this->setAll($services);
    }

    public static function createDefault()
    {
        // 0, 8, 16, 32, 64, 128
        return new static(
            [
                'paths'     => [new Provider\PathServiceProvider(), -64],

                'db.schema' => [new Provider\DatabaseSchemaProvider(), -64],
                'config'    => [new Provider\ConfigServiceProvider(), -64],

                'tokens'    => [new Provider\TokenServiceProvider(), -64],
                'session'   => [new Provider\SessionServiceProvider(), -64],

                'translation' => [new Provider\TranslationServiceProvider(), -63],

                'render' => [new Provider\RenderServiceProvider(), -62],

                'profiler' => [new Provider\ProfilerServiceProvider(), -61],

                'database' => [new Provider\DatabaseServiceProvider(), -60], //TBD

                //etc
            ]
        );
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
