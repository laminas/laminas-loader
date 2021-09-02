<?php

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
