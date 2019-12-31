<?php

/**
 * @see       https://github.com/laminas/laminas-loader for the canonical source repository
 * @copyright https://github.com/laminas/laminas-loader/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-loader/blob/master/LICENSE.md New BSD License
 */

/**
 * Static methods for loading classes and files.
 */
class Laminas_Loader_MyOverloader extends Laminas_Loader
{
    public static function loadClass($class, $dirs = null)
    {
        parent::loadClass($class, $dirs);
    }

    public static function autoload($class)
    {
        try {
            self::loadClass($class);
            return $class;
        } catch (Exception $e) {
            return false;
        }
    }
}
