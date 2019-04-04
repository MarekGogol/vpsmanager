<?php

namespace Gogol\VpsManager\App\Helpers;

use Gogol\VpsManager\App\Application;

class Stub extends Application
{
    protected $content;

    public function __construct($name = null)
    {
        if ($name)
            $this->load($name);
    }

    /*
     * Load content of stub
     */
    public function load($name)
    {
        $this->content = file_get_contents(__DIR__ . '/../Stub/' . $name);
    }

    /*
     * Replace bindings
     */
    public function replace($key, $value)
    {
        $this->content = str_replace($key, $value, $this->content);
    }

    public function addLine($line)
    {
        $this->content .= "\n".$line;
    }

    public function render()
    {
        return $this->content;
    }

    public function save($path)
    {
        return file_put_contents($path, $this->render());
    }
}
?>