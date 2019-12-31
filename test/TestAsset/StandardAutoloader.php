<?php

/**
 * @see       https://github.com/laminas/laminas-loader for the canonical source repository
 * @copyright https://github.com/laminas/laminas-loader/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-loader/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\Loader\TestAsset;

use Laminas\Loader\StandardAutoloader as Psr0Autoloader;

/**
 * @group      Loader
 */
class StandardAutoloader extends Psr0Autoloader
{
    /**
     * Get registered namespaces
     *
     * @return array
     */
    public function getNamespaces()
    {
        return $this->namespaces;
    }

    /**
     * Get registered prefixes
     *
     * @return array
     */
    public function getPrefixes()
    {
        return $this->prefixes;
    }
}
