<?php

namespace LaminasTest\Loader\TestAsset;

/**
 * @group      Loader
 */
class SamplePlugin
{
    public $options;

    public function __construct($options = null)
    {
        $this->options = $options;
    }
}
