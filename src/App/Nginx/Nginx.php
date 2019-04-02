<?php

namespace Gogol\VpsManager\App\Nginx;

class Nginx
{
    protected $config;

    public function __construct($config = [])
    {
        $this->config = $config;
    }

    /*
     * Check if domain exists
     */
    public function exists(string $domain)
    {
        if ( ! isValidDomain($domain) )
            return false;

        dd('domain', $domain, exec('ls -la'));
    }
}

?>