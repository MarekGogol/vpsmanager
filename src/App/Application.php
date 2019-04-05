<?php

namespace Gogol\VpsManager\App;

use Gogol\VpsManager\App\Helpers\Hosting;
use Gogol\VpsManager\App\Helpers\MySQL;
use Gogol\VpsManager\App\Helpers\Nginx;
use Gogol\VpsManager\App\Helpers\PHP;
use Gogol\VpsManager\App\Helpers\Response;
use Gogol\VpsManager\App\Helpers\Server;
use Gogol\VpsManager\App\Helpers\Stub;

class Application
{
    /*
     * Booted classes
     */
    public $booted = [];

    /*
     * Config properties
     */
    protected $config = null;

    /*
     * Console output
     */
    private $output = null;


    /*
     * Return config
     */
    public function config($key = null)
    {
        //Boot config params
        $config = vpsManager()->bootConfig();

        return $key ? (array_key_exists($key, $config) ? $config[$key] : null) : $config;
    }

    /*
     * Boot config data from config file
     */
    public function bootConfig($force = false)
    {
        if ( ! $this->config || $force === true )
            $this->config = require(vpsManagerPath() . '/config.php');

        return $this->config;
    }

    /*
     * Boot console in vpsManager and check correct permissions
     */
    public function bootConsole($output)
    {
        $this->setOutput($output);

        checkPermissions();
    }

    /*
     * Set console output
     */
    public function setOutput($output)
    {
        $this->output = $output;
    }

    /*
     * Get console output
     */
    public function getOutput()
    {
        return $this->output;
    }

    /*
     * Return stub
     */
    public function getStub($name)
    {
        return new Stub($name);
    }

    protected function boot($namespace)
    {
        if ( array_key_exists($namespace, $this->booted) )
            return $this->booted[$namespace];

        $this->booted[$namespace] = new $namespace;
        $this->booted[$namespace]->booted = $this->booted;

        return $this->booted[$namespace];
    }

    /*
     * Return response helper
     */
    public function response()
    {
        return new Response;
    }

    /*
     * Return hosting helper
     */
    public function hosting()
    {
        return $this->boot(Hosting::class);
    }

    /*
     * Return hosting helper
     */
    public function server()
    {
        return $this->boot(Server::class);
    }

    /*
     * Return NGINX helper
     */
    public function nginx()
    {
        return $this->boot(Nginx::class);
    }

    /*
     * Return PHP helper
     */
    public function php()
    {
        return $this->boot(PHP::class);
    }

    /*
     * Return PHP helper
     */
    public function mysql()
    {
        return $this->boot(MySQL::class);
    }

    /*
     * Return web path
     */
    public function getWebPath($domain, $config = null)
    {
        if ( isset($config['www_path']) )
            return $config['www_path'];

        return $this->config('www_path') . '/' . $domain;
    }
}