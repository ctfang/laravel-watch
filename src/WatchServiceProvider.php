<?php

namespace Ctfang\LaravelWatch;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\ServiceProvider;
use Ctfang\LaravelWatch\Command\WatchCommand;

/**
 * Class WatchServiceProvider
 * @package Ctfang\LaravelWatch
 */
class WatchServiceProvider extends ServiceProvider
{
    /** @var string */
    protected $path;

    public function __construct($app)
    {
        parent::__construct($app);

        $this->path = base_path(LoadLogic::watchCachePath);
    }

    /**
     * 注册委托组件
     */
    public function register()
    {
        if (!$this->app->runningInConsole()) {
            if ($this->app->isLocal()) {
                $this->commands([WatchCommand::class]);
                Artisan::call('watch:cache --force=false');
            }
        } else {
            $this->commands([WatchCommand::class]);
        }

        $this->init();
    }

    /**
     * 自动全量映射
     * @param  string  $save
     */
    public function init(string $save = 'singletons')
    {
        foreach (scandir($this->path) as $file) {
            if (!in_array($file, ['.', '..'])) {
                $watch = pathinfo($file, PATHINFO_FILENAME);
                $class = str_replace('_', '\\', $watch);

                $this->{$save}[$class] = $watch;
            }
        }
    }
}
