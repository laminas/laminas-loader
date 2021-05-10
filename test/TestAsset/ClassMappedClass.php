<?php

namespace LaminasTest\UnusualNamespace;

/**
 * @group      Loader
 */
class ClassMappedClass
{
    public $options;

    public function __construct($options = null)
    {
        $this->options = $options;
    }
}
