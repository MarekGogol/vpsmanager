<?php

namespace Gogol\VpsManager\App\Helpers;

use Gogol\VpsManager\App\Application;

class Hosting extends Application
{
    /*
     * Check if nginx or domain dir exists
     */
    public function checkErrorsBeforeCreate(string $domain, $config)
    {
        if ( ! isValidDomain($domain) )
            return $this->response()->wrongDomainName();

        if ( $this->nginx()->exists($domain) )
            return $this->response()->error('Nginx nastavenia pre dómenu '.$domain.' už existuju.');

        if ( $this->server()->existsUser($domain) )
            return $this->response()->error('LINUX používateľ '.$domain.' už existuje.');

        if ( $this->server()->existsDomainTree($domain, $config) && !isset($config['www_path']) )
            return $this->response()->error('Stromova šruktúra pre web '.$domain.' už existuje.');

        if ( ! $this->php()->isInstalled($config['php_version']) )
            return $this->response()->error('PHP s verziou '.$config['php_version'].' nie je nainštalované.');

        if ( $this->php()->poolExists($domain, $config['php_version']) )
            return $this->response()->error('PHP Pool s názvom '.$domain.'.conf pre verziu PHP '.$config['php_version'].' už existuje.');

        return $this->response();
    }

    /*
     * Get value from hosting
     */
    private function getParam($config, $key, $default = null)
    {
        if ( array_key_exists($key, $config) )
            return $config[$key];

        return $default;
    }

    /**
     * Create new hosting
     * @param  [string] $domain [domain name]
     * @param  [array] $config [hosting configuration]
     * @return [response]
     */
    public function create($domain, array $config = [])
    {
        $config['php_version'] = $this->getParam($config, 'php_version', $this->config('php_version'));

        //Check errors
        if ( ($response = $this->checkErrorsBeforeCreate($domain, $config))->isError() )
            return $response;

        //Create user
        if ( ($response = $this->server()->createUser($domain)->writeln(true))->isError() )
            return $response;

        // Create domain directory tree
        if ( ($response = $this->server()->createDomainTree($domain, $config)->writeln())->isError() )
            return $response;

        // Create php pool
        if ( ($response = $this->php()->createPool($domain, $config)->writeln())->isError() )
            return $response;

        //Create nginx host
        if ( ($response = $this->nginx()->createHost($domain, $config)->writeln())->isError() )
            return $response;

        //Create mysql database
        if ( ($response = $this->mysql()->createDatabase($domain)->writeln(true))->isError() )
            return $response;

        return $this->response()->success('Hosting bol úspešne vytvorený');
    }
}
?>