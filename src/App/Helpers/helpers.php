<?php

/*
 * App instance
 */
$vps_manager = null;

function vpsManagerPath()
{
    return '/etc/vpsmanager';
}

function vpsManager()
{
    global $vpsmanager;

    //If vps manager has been already booted
    if ( $vpsmanager )
        return $vpsmanager;

    return $vpsmanager = new Gogol\VpsManager\App\Application;
}

function isValidDomain(string $domain)
{
    //We want at least one domain name
    if ( strpos($domain, '.') === false )
        return false;

    return filter_var($domain, FILTER_VALIDATE_DOMAIN, FILTER_FLAG_HOSTNAME);
}

if ( ! function_exists('trim_end') ) {
    function trim_end($string, $trim)
    {
        while (substr($string, -strlen($trim)) == $trim) {
            $string = substr($string, 0, -strlen($trim));
        }

        return $string;
    }
}

/*
 * Return password
 */
function getRandomPassword($length = 16)
{
    $pool = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ?:!._';

    return substr(str_shuffle(str_repeat($pool, 5)), 0, $length);
}