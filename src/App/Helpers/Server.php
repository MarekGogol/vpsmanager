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

        $user = $this->toUserFormat($user);

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

        $user = $this->toUserFormat($user);

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
    public function createDomainTree($domain, $config = null)
    {
        $user = $this->toUserFormat($domain);

        if ( ! isValidDomain($domain) )
            return $this->response()->wrongDomainName();

        $web_path = $this->getWebPath($domain, $config);

        //Check if can change permissions of directory
        $with_permissions = ! isset($config['no_chmod']);

        $paths = [
            $web_path => 710,
            $web_path.'/web' => 710,
            $web_path.'/sub' => 710,
            $web_path.'/logs' => 700,
        ];

        //Create subdomain
        if ( $sub = $this->getSubdomain($domain) )
            $paths[$web_path.'/sub/'.$sub] = 710;

        //If path has been given
        if ( isset($config['www_path']) )
            $paths = [ $config['www_path'] ];

        //Create new folders
        foreach ($paths as $path => $permissions)
        {
            if ( ! file_exists($path) ){
                shell_exec('mkdir '.$path);

                $this->response()->message('Cesta vytvorená: <comment>'.$path.'</comment>')->writeln();
            }

            //Change permissions
            if ( $with_permissions )
                shell_exec('chmod '.$permissions.' -R '.$path.' && chown -R '.$user.':www-data '.$path);
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