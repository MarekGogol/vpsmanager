<?php

use Gogol\VpsManager\App\Application;

function vpsManager()
{
    return (new Application);
}

function isValidDomain(string $domain)
{
    return true;
}