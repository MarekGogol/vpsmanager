<?php

namespace Gogol\VpsManager\App;

use Gogol\VpsManager\App\Nginx\Nginx;

class Application
{
    protected $config = [
        'app_path' => '/etc/vpsmanager',
    ];

    public function nginx()
    {
        return new Nginx($this->config);
    }
}