<?php


namespace Ctfang\LaravelWatch;


use App\Providers\AppServiceProvider;
use Illuminate\Support\Facades\App;

/**
 * Class WatchService
 * @package Ctfang\LaravelWatch
 */
class WatchService
{
    /**
     * @param  \Ctfang\LaravelWatch\Context  $context
     * @param  \Closure  $next
     * @param  array  $met
     * @return mixed
     */
    public static function runWatch(Context $context, \Closure $next, array $met)
    {
        /** @var \Illuminate\Pipeline\Pipeline $pipeline */
        $pipeline = App::make(Pipeline::class);

        return $pipeline->send($context)
            ->through($met)
            ->then($next);
    }
}