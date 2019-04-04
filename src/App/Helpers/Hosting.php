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

        //Create mysql database
        if ( ($response = $this->mysql()->createDatabase($domain)->writeln(true))->isError() )
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

        //Test nginx configruation
        $this->rebootNginx();
        $this->rebootPHP($config['php_version']);

        return $this->response()->success("\n".'Hosting bol úspešne vytvorený!');
    }

    private function rebootNginx()
    {
        if ( $this->server()->nginx()->test() )
        {
            if ( $this->server()->nginx()->restart(false) ){
                $this->response()->success('<comment>NGINX bol úspešne reštartovaný.</comment>')->writeln();
            } else {
                $this->response()->message('<error>Došlo k chybe pri reštarte služby NGINX. Spustite službu manuálne.</error>')->writeln();
            }
        } else {
            $this->response()->message('<error>Konfigurácia NGINXU nie je správna, preto nie je možné spustiť reštart služby.</error>')->writeln();
        }
    }

    private function rebootPHP($php_version)
    {
        if ( $this->server()->php()->restart($php_version) ){
            $this->response()->success('<comment>PHP bolo úspešne reštartované.</comment>')->writeln();
        } else {
            $this->response()->message('<error>Došlo k chybe pri reštarte služby PHP. Spustite službu manuálne.</error>')->writeln();
        }
    }

    public function remove($domain, $remove = false)
    {
        vpsManager()->nginx()->removeHost($domain);

        //Remove pools from all php versions
        foreach (vpsManager()->php()->getVersions() as $php_version)
            vpsManager()->php()->removePool($domain, $php_version);

        vpsManager()->server()->deleteUser($domain);

        if ( $remove === true )
            vpsManager()->server()->deleteDomainTree($domain);
    }
}
?>