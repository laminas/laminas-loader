<?php

namespace LaminasTest\Loader\TestAsset;

use ReturnTypeWillChange;

/**
 * @group      Loader
 */
class TestPluginMap implements \IteratorAggregate
{
    /**
     * Plugin map
     *
     * @var array
     */
    public $map = array(
        'map'    => __CLASS__,
        'test'   => 'LaminasTest\Loader\PluginClassLoaderTest',
        'loader' => 'Laminas\Loader\PluginClassLoader',
    );

    /**
     * Return iterator
     *
     * @return Traversable
     */
    #[ReturnTypeWillChange]
    public function getIterator()
    {
        return new \ArrayIterator($this->map);
    }
}
