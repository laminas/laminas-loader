<?php

/**
 * @see       https://github.com/laminas/laminas-loader for the canonical source repository
 * @copyright https://github.com/laminas/laminas-loader/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-loader/blob/master/LICENSE.md New BSD License
 */

Laminas_Loader::registerAutoload();

set_include_path(get_include_path() . PATH_SEPARATOR . __DIR__ . '/_files');

$parseError = new ParseError();
