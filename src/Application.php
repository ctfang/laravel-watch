<?php


namespace Ctfang\LaravelWatch;

/**
 * 替换 bootstrap/app.php 文件的Application
 * @package Ctfang\LaravelWatch
 */
class Application extends \Illuminate\Foundation\Application
{
    /**
     * 如果不使用 WatchService::init 全部引入
     * 也可以重载make，使用时候按需代理
     *
     * @param  string  $abstract
     * @param  array  $parameters
     * @return mixed|string
     */
    public function make($abstract, array $parameters = [])
    {
        $watch = str_replace('\\', '_', $abstract);
        if (class_exists($watch)) {
            return parent::make($watch, $parameters);
        }

        return parent::make($abstract, $parameters);
    }
}