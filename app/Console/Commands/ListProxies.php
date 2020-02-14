<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class ListProxies extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'proxies:list {site?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Show addresses list';

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
        $site = $this->argument('site');
        var_dump($site);
    }
}
