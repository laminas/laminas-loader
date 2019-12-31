<?php

/**
 * @see       https://github.com/laminas/laminas-loader for the canonical source repository
 * @copyright https://github.com/laminas/laminas-loader/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-loader/blob/master/LICENSE.md New BSD License
 */

/**
 * Static methods for loading classes and files.
 *
 * @category   Laminas
 * @package    Laminas_Loader
 * @subpackage UnitTests
 */
class Laminas_Loader_MyLoader extends Laminas_Loader
{
    public static function loadClass($class, $dirs = null)
    {
        parent::loadClass($class, $dirs);
    }
}
