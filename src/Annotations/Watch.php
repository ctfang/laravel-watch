<?php


namespace Ctfang\LaravelWatch\Annotations;

/**
 * Class Watch
 * @package Ctfang\LaravelWatch\Annotations
 * @Annotation
 * @Target({"METHOD"})
 * @Attributes({
 *   @Attribute("class", type = "SomeAnnotationClass"),
 *   @Attribute("func",  type = "string"),
 * })
 */
class Watch
{
    /** @var SomeAnnotationClass */
    public $class;

    /** @var string */
    public $func;

    public function __construct(array $values)
    {
        $this->class = $values['class'] ?? $values["value"][0];
        $this->func  = $values['func'] ?? $values["value"][1];
    }
}