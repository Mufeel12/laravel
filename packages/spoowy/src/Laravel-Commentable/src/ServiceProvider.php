<?php

namespace Spoowy\Commentable;

use Spoowy\ServiceProvider\ServiceProvider as BaseProvider;

class ServiceProvider extends BaseProvider
{
    protected $packageName = 'commentable';

    public function boot()
    {
        $this->setup(__DIR__)
             ->publishMigrations();
    }
}
