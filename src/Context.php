<?php


namespace Ctfang\LaravelWatch;


class Context
{
    protected $input;

    protected $values = [];

    public function __construct($input)
    {
        $this->input = $input;
    }

    public function getInputs(): array
    {
        return $this->input;
    }

    public function getInput(string $key)
    {
        return $this->input[$key] ?? null;
    }

    public function set(string $key, $val)
    {
        $this->values[$key] = $val;
    }

    public function get(string $key)
    {
        return $this->values[$key] ?? null;
    }
}