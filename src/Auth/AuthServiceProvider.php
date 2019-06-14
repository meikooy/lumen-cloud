<?php

namespace Meiko\Lumen\Cloud\Auth;

use Illuminate\Support\ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * Register any application authentication / authorization services.
     */
    public function boot()
    {
        $this->publishes([__DIR__.'/../../config/cloud.php' => getConfigPath('cloud.php')], 'config');

        $this->registerGuard();
    }

    /**
     * Get the configuration path.
     *
     * @param  string $path
     * @return string
     */
    function getConfigPath($path = '')
    {
        return $this->basePath() . '/config' . ($path ? '/' . $path : $path);
    }

    /**
     * Register the token guard.
     *
     * @return void
     */
    protected function registerGuard()
    {
        $this->app['auth']->extend('cloud', function ($app, $name, array $config) {
            return new JWTGuard(
                $app['auth']->createUserProvider($config['provider']),
                $app['request']
            );
        });
    }
}
