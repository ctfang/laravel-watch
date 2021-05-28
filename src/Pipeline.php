<?php


namespace Ctfang\LaravelWatch;


use Throwable;

class Pipeline extends \Illuminate\Pipeline\Pipeline
{

    /**
     * Get a Closure that represents a slice of the application onion.
     *
     * @return \Closure
     */
    protected function carry()
    {
        return function ($stack, $pipe) {
            return function ($passable) use ($stack, $pipe) {
                try {
                    if (is_array($pipe)) {
                        $name       = $pipe[0];
                        $method     = $pipe[1];
                        $pipe       = $this->getContainer()->make($name);
                        $parameters = [$passable, $stack];

                        $carry = $pipe->{$method}(...$parameters);

                        return $this->handleCarry($carry);
                    } elseif (is_callable($pipe)) {
                        // If the pipe is a callable, then we will call it directly, but otherwise we
                        // will resolve the pipes out of the dependency container and call it with
                        // the appropriate method and arguments, returning the results back out.
                        return $pipe($passable, $stack);
                    }
                    throw new \Exception("不支持的委托");
                } catch (Throwable $e) {
                    return $this->handleException($passable, $e);
                }
            };
        };
    }
}