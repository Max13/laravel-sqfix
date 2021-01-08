<?php

namespace Mx\Sqfix;

use Illuminate\Database\Connection;
use Illuminate\Support\ServiceProvider;
use Mx\Sqfix\Connection as SqfixConnection;

class SqfixServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        Connection::resolverFor('sqlite', function ($connection, $database, $prefix, $config) {
            return new SqfixConnection($connection, $database, $prefix, $config);
        });
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }
}
