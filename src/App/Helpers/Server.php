<?php

namespace Gogol\VpsManager\App\Helpers;

use Gogol\VpsManager\App\Application;

class Server extends Application
{
    /*
     * Check if user exists
     */
    public function existsUser($user)
    {
        return shell_exec('getent passwd '.$user) ? true : false;
    }

    /*
     * Create linux user
     */
    public function createUser(string $user)
    {
        //Check if is user in valid format
        if ( ! isValidDomain($user) )
            return $this->response()->wrongDomainName();

        //Check if user exists
        if ( $this->existsUser($user) )
            return $this->response()->error('LINUX používateľ '.$user.' už existuje.');

        //Web path
        $web_path = $this->getWebPath($user);

        $password = getRandomPassword(16);

        //Create new linux user
        exec('useradd -s /bin/bash -d '.$web_path.' -U '.$user.' -p $(openssl passwd -1 '.$password.')', $output, $return_var);
        if ( $return_var != 0 )
            return $this->response()->error('Používateľa nebolo možné vytvoriť.');

        return $this->response()
                    ->success(
                        '<info>Linuxový používateľ bol úspešne vytvorený.</info>'."\n".
                        'Meno: <comment>'.$user.'</comment>'."\n".
                        'Heslo: <comment>'.$password.'</comment>'
                   );
    }

    /*
     * Delete user
     */
    public function deleteUser(string $user)
    {
        //Check if is user in valid format
        if ( ! isValidDomain($user) )
            return false;

        if ( ! $this->existsUser($user) )
            return true;

        //Create new linux user
        exec('userdel '.$user, $output, $return_var);

        return $return_var == 0 ? true : false;
    }

    /*
     * Return if domain directory exists
     */
    public function existsDomainTree($domain, $config = null)
    {
        if ( isset($config['www_path']) )
            return false;

        return file_exists($this->getWebPath($domain, $config));
    }

    /*
     * Create directory tree for domain
     */
    public function createDomainTree($user, $config = null)
    {
        if ( ! isValidDomain($user) )
            return $this->response()->wrongDomainName();

        $web_path = $this->getWebPath($user, $config);

        //If is given www path from config, validate if exists
        if ( isset($config['www_path']) && $this->existsDomainTree($user, $config) )
            return $this->response()->error('Priečiok '.$web_path.' už neexistuje.');

        //If we want create new web directory, first check if path exists
        if ( !isset($config['www_path']) && $this->existsDomainTree($user) )
            return $this->response()->error('Priečiok '.$web_path.' už existuje.');

        //Check if can change permissions of directory
        $with_permissions = ! isset($config['no_chmod']);

        //If web path has been given, then just change permissions ang group for correct user
        if ( isset($config['www_path']) && $with_permissions ){
            shell_exec('chmod -R 710 '.$web_path.' && chown -R '.$user.':www-data '.$web_path);
        }

        //Create new folders
        else if ( $with_permissions ){
            foreach ([
                $web_path => 710,
                $web_path.'/web' => 710,
                $web_path.'/sub' => 710,
                $web_path.'/logs' => 700,
            ] as $path => $permissions)
            {
                if ( ! file_exists($path) )
                    shell_exec('mkdir '.$path.' -m '.$permissions.' && chown -R '.$user.':www-data '.$path);

                if ( $output = vpsManager()->getOutput() )
                    $output->writeln('Cesta vytvorená: <comment>'.$path.'</comment>');
            }

        }

        return $this->response()->success('Priečinok webu <info>'.$web_path.'</info> a jeho práva boli úspešne vytvorené a nastavené.');
    }

    /*
     * Remove domain tree
     */
    public function deleteDomainTree($domain)
    {
        if ( ! isValidDomain($domain) )
            return false;

        $web_path = vpsManager()->getWebPath($domain);

        return system('rm -rf '.$web_path) == 0;
    }
}

?>