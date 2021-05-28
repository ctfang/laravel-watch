<?php

namespace Ctfang\LaravelWatch\Command;

use Illuminate\Console\Command;
use Ctfang\LaravelWatch\Factory;
use Ctfang\LaravelWatch\LoadLogic;

/**
 * Class WatchCommand
 * @package Ctfang\LaravelWatch
 */
class WatchCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'watch:cache {--force=true}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '生产委托缓存';

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
     * @return int
     * @throws \ReflectionException
     */
    public function handle()
    {
        $load    = new LoadLogic();
        $factory = new Factory();
        $force   = $this->option('force');
        if ($force == 'true') {
            $this->info("强制清楚watch缓存");
            $factory->clear($load);
        }

        $load->load();
        $factory->make($load);

        return 0;
    }
}
