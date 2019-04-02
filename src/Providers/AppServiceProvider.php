<?php

namespace Gogol\VpsManager\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Foundation\Http\Kernel;
use Admin;

class AppServiceProvider extends ServiceProvider
{
    protected $providers = [
        PublishServiceProvider::class,
    ];

    protected $facades = [

    ];

    protected $routeMiddleware = [

    ];

    /**
     * Bootstrap the application events.
     *
     * @return void
     */
    public function boot()
    {
        $this->mergeAdminConfigs();

        Admin::addModelPath('Gogol\VpsManager\Model', __dir__ . '/../Model/**');

        //Boot providers after this provider boot
        $this->bootProviders([
            ViewServiceProvider::class
        ]);
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom(
            __DIR__.'/../Config/config.php', 'vpsmanager'
        );

        $this->bootFacades();

        $this->bootProviders();

        $this->bootRouteMiddleware();

        //Load routes
        $this->loadRoutesFrom(__DIR__.'/../Routes/routes.php');
    }

    public function bootFacades()
    {
        $this->app->booting(function()
        {
            $loader = \Illuminate\Foundation\AliasLoader::getInstance();

            foreach ($this->facades as $alias => $facade)
            {
                $loader->alias($alias, $facade);
            }

        });
    }

    public function bootProviders($providers = null)
    {
        foreach ($providers ?: $this->providers as $provider)
        {
            app()->register($provider);
        }
    }

    public function bootRouteMiddleware()
    {
        foreach ($this->routeMiddleware as $name => $middleware)
        {
            $router = $this->app['router'];

            $router->aliasMiddleware($name, $middleware);
        }
    }

    /*
     * Merge crudadmin config with esolutions config
     */
    private function mergeAdminConfigs($key = 'admin')
    {
        $admin_config = require __DIR__.'/../Config/admin.php';

        $config = $this->app['config']->get($key, []);

        $this->app['config']->set($key, array_merge($admin_config, $config));

        //Merge selected properties with two dimensional array
        foreach (['groups', 'models', 'author', 'passwords', 'gettext_source_paths', 'styles', 'scripts', 'components'] as $property) {
            if ( ! array_key_exists($property, $admin_config) || ! array_key_exists($property, $config) )
                continue;

            $attributes = array_merge($admin_config[$property], $config[$property]);

            //If is not multidimensional array
            if ( count($attributes) == count($attributes, COUNT_RECURSIVE) )
                $attributes = array_unique($attributes);

            $this->app['config']->set($key . '.' . $property, $attributes);
        }
    }
}