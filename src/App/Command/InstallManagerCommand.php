<?php

namespace Gogol\VpsManager\App\Command;

use Gogol\VpsManager\App\Nginx\Nginx;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Question\Question;

class InstallManagerCommand extends Command
{
    private $input;
    private $output;

    protected function configure()
    {
        $this->setName('install')
             ->setDescription('Install VPS Manager')
             ->addOption('dev', null, InputOption::VALUE_OPTIONAL, 'Use dev version of installation', null)
             ->addOption('vpsmanager_path', null, InputOption::VALUE_OPTIONAL, 'Set absolute path of VPS Manager web interface', null)
             ->addOption('open_basedir', null, InputOption::VALUE_OPTIONAL, 'Allow open_basedir path for VPS Manager web interface', null)
             ->addOption('no_chmod', null, InputOption::VALUE_OPTIONAL, 'Disable change of chmod settings of web directory', null);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->input = $input;
        $this->output = $output;

        vpsManager()->setOutput($output);

        $output->writeln('');

        $helper = $this->getHelper('question');

        $this->setConfig($input, $output, $helper);

        //Reset settings for manager web interface
        if ( $this->isDev($input) )
            $this->resetManagerHosting();

        $this->generateManagerHosting($input, $output);
    }

    public function isDev($input)
    {
        return $input->getOption('dev') == 1;
    }

    public function setConfig($input, $output, $helper)
    {
        $config = [];

        //Set config properties
        foreach ([
            'setNginxPath' => [
                'config_key' => 'nginx_path',
                'default' => '/etc/nginx'
            ],
            'setPHPPath' => [
                'config_key' => 'php_path',
                'default' => '/etc/php'
            ],
            'setDefaultPHPVersion' => [
                'config_key' => 'php_version',
                'default' => '7.2'
            ],
            'setVpsManagerPath' => [
                'config_key' => 'vpsmanager_path',
                'default' => $input->getOption('vpsmanager_path') ?: null,
            ],
            'setWWWPath' => [
                'config_key' => 'www_path',
                'default' => '/var/www'
            ],
            'setHost' => [
                'config_key' => 'host',
                'default' => 'vpsmanager.example.com'
            ]
        ] as $method => $data)
        {
            //Use default config values
            if ( $this->isDev($input) )
                $config[$data['config_key']] = $data['default'];

            //Get config inputs
            else {
                $output->writeln('');
                $this->{$method}($input, $output, $helper, $config[$data['config_key']], $data['default']);
            }
        }

        if ( ! file_put_contents(vpsManagerPath().'/config.php', "<?php \n\nreturn " . var_export($config, true) . ';') )
            throw new \Exception('Installation failed. Config could not be saved into '.vpsManagerPath().'/config.php');
    }

    private function setNginxPath($input, $output, $helper, &$config, $default)
    {
        $output->writeln('<info>Please set NGINX path.</info>');

        //Nginx path
        $question = new Question('Type new path or press enter for using default <comment>'.$default.'</comment> path: ', null);
        $question->setValidator(function($path) {
            if ( $path && ! file_exists($path) )
                throw new \Exception('Please fill valid existing path.');

            return trim_end($path, '/');
        });

        $value = $config = $helper->ask($input, $output, $question) ?: $default;

        $output->writeln('Used path: <comment>' . $value . '</comment>');
    }

    private function setPHPPath($input, $output, $helper, &$config, $default)
    {
        $output->writeln('<info>Please set PHP path.</info>');

        //Nginx path
        $question = new Question('Type new path or press enter for using default <comment>'.$default.'</comment> path: ', null);
        $question->setValidator(function($path) {
            if ( $path && ! file_exists($path) )
                throw new \Exception('Please fill valid existing path.');

            return trim_end($path, '/');
        });

        $value = $config = $helper->ask($input, $output, $question) ?: $default;

        $output->writeln('Used path: <comment>' . $value . '</comment>');
    }

    private function setDefaultPHPVersion($input, $output, $helper, &$config, $default)
    {
        $output->writeln('<info>Please set default PHP version.</info>');

        //Nginx path
        $question = new ChoiceQuestion('Set default PHP Version of your server. Default is <comment>'.$default.'</comment>: ', vpsManager()->php()->getVersions(), $default);

        $version = $config = $helper->ask($input, $output, $question) ?: $default;

        $output->writeln('Used version for new websites: <comment>' . $version . '</comment>');

        //Check if is PHP Version installed
        if ( ($php = vpsManager()->php())->isInstalled($version) )
        {
            if ( $php->changeDefaultPHP($version) )
                $output->writeln('Updated php alias to: <comment>' . $php->getPhpBinPath($version) . '</comment>');
            else
                $output->writeln('<error>PHP symlink could not be updated on path ' . $php->getPhpBinPath($version) . '</error>');
        }
        else {

        }
    }

    private function setVpsManagerPath($input, $output, $helper, &$config, $default)
    {
        $output->writeln('<info>Please set VPSManager web interface path (path to Laravel app without /public).</info>');

        //Nginx path
        $question = new Question('Type new path or press enter for using default <comment>'.$default.'</comment> path: ', null);
        $question->setValidator(function($path) use ($default) {
            if ( $path && ! file_exists($path) || ! $path && ! $default )
                throw new \Exception('Please fill valid existing path.');

            return trim_end($path, '/');
        });

        $value = $config = $helper->ask($input, $output, $question) ?: $default;

        $output->writeln('Used path: <comment>' . $value . '</comment>');
    }

    private function setWWWPath($input, $output, $helper, &$config, $default)
    {
        $output->writeln('<info>Please set WWW path of your websites.</info>');

        //Nginx path
        $question = new Question('Type new path or press enter for using default <comment>'.$default.'</comment> path: ', null);
        $question->setValidator(function($path) {
            if ( $path && ! file_exists($path) )
                throw new \Exception('Please fill valid existing path.');

            return trim_end($path, '/');
        });

        $value = $config = $helper->ask($input, $output, $question) ?: $default;

        $output->writeln('Used path: <comment>' . $value . '</comment>');
    }

    private function setHost($input, $output, $helper, &$config, $default)
    {
        $output->writeln('<info>Please set host of your VPSManager admin panel.</info>');

        //Nginx path
        $question = new Question("eg. vpsmanager.example.com: ", null);

        $question->setValidator(function($host) {
            if ( ! $host || ! isValidDomain($host) )
                throw new \Exception('Please fill valid host name.');

            return $host;
        });

        $value = $config = $helper->ask($input, $output, $question) ?: $default;

        $output->writeln('Used host: <comment>' . $value . '</comment>');
    }

    /*
     * Returns manager host
     */
    private function getManagerHost()
    {
        return 'vps-manager-hs.test' ?: vpsManager()->config('host');
    }

    /*
     * Return path of manager web interface
     */
    private function getManagerPath()
    {
        return vpsManager()->config('vpsmanager_path');
    }

    /*
     * Reset uneccessary settings for creating new hosting with same name
     * Just for dev purposes
     */
    private function resetManagerHosting()
    {
        $domain = $this->getManagerHost();
        $php_version = vpsManager()->config('php_version');

        vpsManager()->nginx()->removeHost($domain);
        vpsManager()->php()->removePool($domain, $php_version);
        vpsManager()->server()->deleteUser($domain);
    }

    /*
     * Set host
     */
    private function generateManagerHosting($input, $output)
    {
        $host_name = $this->getManagerHost();

        if ( ($response = vpsManager()->hosting()->create($host_name, [
            'www_path' => $this->getManagerPath(),
            'open_basedir' => $input->getOption('open_basedir'),
            'no_chmod' => $input->getOption('no_chmod'),
        ]))->isError() )
            throw new \Exception($response->message);

        $output->writeln('<info>'.$response->message.'</info>');
    }
}