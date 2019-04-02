<?php

namespace Gogol\VpsManager\Commands;

use Illuminate\Console\Command;
use Illuminate\Console\ConfirmableTrait;
use Artisan;

class ManagerInstallCommand extends Command
{
    use ConfirmableTrait;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'vpsmanager:install';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Install VPSManager hosting packpage on server';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {


        $this->line('Installation completed!');
    }
}