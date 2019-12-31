<?php

/**
 * @see       https://github.com/laminas/laminas-loader for the canonical source repository
 * @copyright https://github.com/laminas/laminas-loader/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-loader/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\Loader\TestAsset;

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
    public function getIterator()
    {
        return new \ArrayIterator($this->map);
    }
}
