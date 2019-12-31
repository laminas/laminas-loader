<?php

/**
 * @see       https://github.com/laminas/laminas-loader for the canonical source repository
 * @copyright https://github.com/laminas/laminas-loader/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-loader/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\Loader;

use Laminas\Loader\Exception\InvalidArgumentException;
use Laminas\Loader\StandardAutoloader;
use ReflectionClass;

/**
 * @category   Laminas
 * @package    Loader
 * @subpackage UnitTests
 * @group      Loader
 */
class StandardAutoloaderTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        // Store original autoloaders
        $this->loaders = spl_autoload_functions();
        if (!is_array($this->loaders)) {
            // spl_autoload_functions does not return empty array when no
            // autoloaders registered...
            $this->loaders = array();
        }

        // Store original include_path
        $this->includePath = get_include_path();
    }

    public function tearDown()
    {
        // Restore original autoloaders
        $loaders = spl_autoload_functions();
        if (is_array($loaders)) {
            foreach ($loaders as $loader) {
                spl_autoload_unregister($loader);
            }
        }

        foreach ($this->loaders as $loader) {
            spl_autoload_register($loader);
        }

        // Restore original include_path
        set_include_path($this->includePath);
    }

    public function testFallbackAutoloaderFlagDefaultsToFalse()
    {
        $loader = new StandardAutoloader();
        $this->assertFalse($loader->isFallbackAutoloader());
    }

    public function testFallbackAutoloaderStateIsMutable()
    {
        $loader = new StandardAutoloader();
        $loader->setFallbackAutoloader(true);
        $this->assertTrue($loader->isFallbackAutoloader());
        $loader->setFallbackAutoloader(false);
        $this->assertFalse($loader->isFallbackAutoloader());
    }

    public function testPassingNonTraversableOptionsToSetOptionsRaisesException()
    {
        $loader = new StandardAutoloader();

        $obj  = new \stdClass();
        foreach (array(true, 'foo', $obj) as $arg) {
            try {
                $loader->setOptions(true);
                $this->fail('Setting options with invalid type should fail');
            } catch (InvalidArgumentException $e) {
                $this->assertContains('array or Traversable', $e->getMessage());
            }
        }
    }

    public function testPassingArrayOptionsPopulatesProperties()
    {
        $options = array(
            'namespaces' => array(
                'Laminas\\'   => dirname(__DIR__) . DIRECTORY_SEPARATOR,
            ),
            'prefixes'   => array(
                'Laminas_'  => dirname(__DIR__) . DIRECTORY_SEPARATOR,
            ),
            'fallback_autoloader' => true,
        );
        $loader = new TestAsset\StandardAutoloader();
        $loader->setOptions($options);
        $this->assertEquals($options['namespaces'], $loader->getNamespaces());
        $this->assertEquals($options['prefixes'], $loader->getPrefixes());
        $this->assertTrue($loader->isFallbackAutoloader());
    }

    public function testPassingTraversableOptionsPopulatesProperties()
    {
        $namespaces = new \ArrayObject(array(
            'Laminas\\' => dirname(__DIR__) . DIRECTORY_SEPARATOR,
        ));
        $prefixes = new \ArrayObject(array(
            'Laminas_' => dirname(__DIR__) . DIRECTORY_SEPARATOR,
        ));
        $options = new \ArrayObject(array(
            'namespaces' => $namespaces,
            'prefixes'   => $prefixes,
            'fallback_autoloader' => true,
        ));
        $loader = new TestAsset\StandardAutoloader();
        $loader->setOptions($options);
        $this->assertEquals((array) $options['namespaces'], $loader->getNamespaces());
        $this->assertEquals((array) $options['prefixes'], $loader->getPrefixes());
        $this->assertTrue($loader->isFallbackAutoloader());
    }

    public function testAutoloadsNamespacedClasses()
    {
        $loader = new StandardAutoloader();
        $loader->registerNamespace('LaminasTest\UnusualNamespace', __DIR__ . '/TestAsset');
        $loader->autoload('LaminasTest\UnusualNamespace\NamespacedClass');
        $this->assertTrue(class_exists('LaminasTest\UnusualNamespace\NamespacedClass', false));
    }

    public function testAutoloadsVendorPrefixedClasses()
    {
        $loader = new StandardAutoloader();
        $loader->registerPrefix('LaminasTest_UnusualPrefix', __DIR__ . '/TestAsset');
        $loader->autoload('LaminasTest_UnusualPrefix_PrefixedClass');
        $this->assertTrue(class_exists('LaminasTest_UnusualPrefix_PrefixedClass', false));
    }

    public function testCanActAsFallbackAutoloader()
    {
        $loader = new StandardAutoloader();
        $loader->setFallbackAutoloader(true);
        set_include_path(__DIR__ . '/TestAsset/' . PATH_SEPARATOR . $this->includePath);
        $loader->autoload('TestNamespace\FallbackCase');
        $this->assertTrue(class_exists('TestNamespace\FallbackCase', false));
    }

    public function testReturnsFalseForUnresolveableClassNames()
    {
        $loader = new StandardAutoloader();
        $this->assertFalse($loader->autoload('Some\Fake\Classname'));
    }

    public function testReturnsFalseForInvalidClassNames()
    {
        $loader = new StandardAutoloader();
        $loader->setFallbackAutoloader(true);
        $this->assertFalse($loader->autoload('Some\Invalid\Classname\\'));
    }

    public function testRegisterRegistersCallbackWithSplAutoload()
    {
        $loader = new StandardAutoloader();
        $loader->register();
        $loaders = spl_autoload_functions();
        $this->assertTrue(count($this->loaders) < count($loaders));
        $test = array_pop($loaders);
        $this->assertEquals(array($loader, 'autoload'), $test);
    }

    public function testAutoloadsNamespacedClassesWithUnderscores()
    {
        $loader = new StandardAutoloader();
        $loader->registerNamespace('LaminasTest\UnusualNamespace', __DIR__ . '/TestAsset');
        $loader->autoload('LaminasTest\UnusualNamespace\Name_Space\Namespaced_Class');
        $this->assertTrue(class_exists('LaminasTest\UnusualNamespace\Name_Space\Namespaced_Class', false));
    }

    public function testLaminasNamespaceIsNotLoadedByDefault()
    {
        $loader = new StandardAutoloader();
        $expected = array();
        $this->assertAttributeEquals($expected, 'namespaces', $loader);
    }

    public function testCanTellAutoloaderToRegisterLaminasNamespaceAtInstantiation()
    {
        $loader = new StandardAutoloader(array('autoregister_laminas' => true));
        $r      = new ReflectionClass($loader);
        $file   = $r->getFileName();
        $expected = array('Laminas\\' => dirname(dirname($file)) . DIRECTORY_SEPARATOR);
        $this->assertAttributeEquals($expected, 'namespaces', $loader);
    }

}
