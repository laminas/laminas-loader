<?php

/**
 * @see       https://github.com/laminas/laminas-loader for the canonical source repository
 * @copyright https://github.com/laminas/laminas-loader/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-loader/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\Loader\TestAsset;

use Laminas\Loader\PluginClassLoader;

/**
 * @group      Loader
 */
class ExtendedPluginClassLoader extends PluginClassLoader
{
    protected $plugins = array(
        'loader' => 'Laminas\Loader\PluginClassLoader',
    );

    protected static $staticMap = array();
}
