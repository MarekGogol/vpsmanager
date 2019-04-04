<?php

namespace Gogol\VpsManager\App\Helpers;

use Gogol\VpsManager\App\Application;

class Nginx extends Application
{
    /*
     * Check if domain exists
     */
    public function exists(string $domain)
    {
        if ( ! isValidDomain($domain) )
            return false;

        $path = $this->config('nginx_path').'/sites-available/'.$domain;

        return file_exists($path);
    }

    public function getAvailablePath($domain)
    {
        return $this->config('nginx_path').'/sites-available/'.$domain;
    }

    public function getEnabledPath($domain)
    {
        return $this->config('nginx_path').'/sites-enabled/'.$domain;
    }

    /**
     * Create new host
     * @param  string $domain
     * @param  array  $config [php_version]
     * @return [type]
     */
    public function createHost(string $domain, array $config = [])
    {
        if ( ! isValidDomain($domain) )
            return $this->response()->wrongDomainName();

        //Check if is correct setted php verion
        if ( ! in_array($php_version = $config['php_version'], $this->php()->getVersions()) )
            return $this->response()->error('Zadali ste nesprávnu verziu PHP');

        $www_path = isset($config['www_path'])
                        ? $config['www_path'].'/public'
                        : ($this->getWebPath($domain).'/web/public');

        $stub = $this->getStub('nginx.template.conf');
        $stub->replace('{host}', $domain);
        $stub->replace('{path}', $www_path);
        $stub->replace('{php_version}', $php_version);
        $stub->replace('{php_sock_name}', $this->php()->getSocketName($domain, $php_version));

        if ( ! $stub->save($this->getAvailablePath($domain)) )
            return $this->response()->error('Súbor NGINX host sa nepodarilo uložiť.');

        if ( ! $this->allowHost($domain) )
            return $this->response()->error('Nepodarilo sa vytvoriť odkaz na host v priečinku sites-enabled.');

        return $this->response()->success('NGINX host '.$domain.' bol úspešne vytvorený.');
    }

    public function removeHost($domain)
    {
        if ( file_exists($this->getAvailablePath($domain)) && !@unlink($this->getAvailablePath($domain)) )
            return false;

        if ( file_exists($this->getEnabledPath($domain)) && !@unlink($this->getEnabledPath($domain)) )
            return false;

        return true;
    }

    /*
     * Allow domain host
     */
    public function allowHost(string $domain)
    {
        if ( ! isValidDomain($domain) )
            return false;

        if ( file_exists($this->getEnabledPath($domain)) )
            return true;

        exec('ln -s '.$this->getAvailablePath($domain).' '.$this->getEnabledPath($domain), $output, $return_var);

        return $return_var == 0 ? true : false;
    }
}

?>