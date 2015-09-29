<?php

namespace Bolt;

class ServiceBuilder
{
    /** @var Services */
    protected $services;

    public static function create()
    {
        $builder = new static();
        $builder->services = new Services();

        return $builder;
    }

    public static function createDefault()
    {
        return self::create()
            ->core()
            ->sessions()
            ->rendering()
            ->getServices()
        ;
    }

    public function core()
    {
        $this->services->setAll([
            'paths'     => [new Provider\PathServiceProvider(), -64],
            'db.schema' => [new Provider\DatabaseSchemaProvider(), -64],
            'config'    => [new Provider\ConfigServiceProvider(), -64],
        ]);

        return $this;
    }

    public function sessions()
    {
        $this->services->setAll([
            'tokens'    => [new Provider\TokenServiceProvider(), -64],
            'session'   => [new Provider\SessionServiceProvider(), -64],
        ]);

        return $this;
    }

    public function rendering()
    {
        $this->services->setAll([
            'render' => [new Provider\RenderServiceProvider(), -62],
        ]);

        return $this;
    }


    //etc


    public function getServices()
    {
        return $this->services;
    }
}
