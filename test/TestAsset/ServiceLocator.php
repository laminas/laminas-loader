<?php

/**
 * @see       https://github.com/laminas/laminas-loader for the canonical source repository
 * @copyright https://github.com/laminas/laminas-loader/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-loader/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\Loader\TestAsset;

use Laminas\ServiceManager\ServiceLocatorInterface;

class ServiceLocator implements ServiceLocatorInterface
{
    protected $services = array();

    public function get($name, array $params = array())
    {
        if (!isset($this->services[$name])) {
            return null;
        }

        return $this->services[$name];
    }

    public function has($name)
    {
        return (isset($this->services[$name]));
    }

    public function set($name, $object)
    {
        $this->services[$name] = $object;
    }
}
