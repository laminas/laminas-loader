<?php

namespace LaminasTest\Loader;

use Laminas\Loader\ClassMapAutoloader;
use Laminas\Loader\Exception\InvalidArgumentException;
use LaminasTest\Loader\StandardAutoloaderTest;
use PHPUnit\Framework\TestCase;
use stdClass;

use function array_shift;
use function class_exists;
use function count;
use function get_include_path;
use function is_array;
use function set_include_path;
use function spl_autoload_functions;
use function spl_autoload_register;
use function spl_autoload_unregister;

/**
 * @group      Loader
 */
class ClassMapAutoloaderTest extends TestCase
{
    public function setUp(): void
    {
        // Store original autoloaders
        $this->loaders = spl_autoload_functions();
        if (! is_array($this->loaders)) {
            // spl_autoload_functions does not return empty array when no
            // autoloaders registered...
            $this->loaders = [];
        }

        // Store original include_path
        $this->includePath = get_include_path();

        $this->loader = new ClassMapAutoloader();
    }

    public function tearDown(): void
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

    public function testRegisteringNonExistentAutoloadMapRaisesInvalidArgumentException()
    {
        $dir = __DIR__ . '__foobar__';
        $this->expectException(InvalidArgumentException::class);
        $this->loader->registerAutoloadMap($dir);
    }

    public function testValidMapFileNotReturningMapRaisesInvalidArgumentException()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->loader->registerAutoloadMap(__DIR__ . '/_files/badmap.php');
    }

    public function testAllowsRegisteringArrayAutoloadMapDirectly()
    {
        $map = [
            // @codingStandardsIgnoreStart
            'Laminas\Loader\Exception\ExceptionInterface' => __DIR__ . '/../../../library/Laminas/Loader/Exception/ExceptionInterface.php',
            // @codingStandardsIgnoreEnd
        ];
        $this->loader->registerAutoloadMap($map);
        $test = $this->loader->getAutoloadMap();
        $this->assertSame($map, $test);
    }

    public function testAllowsRegisteringArrayAutoloadMapViaConstructor()
    {
        $map = [
            // @codingStandardsIgnoreStart
            'Laminas\Loader\Exception\ExceptionInterface' => __DIR__ . '/../../../library/Laminas/Loader/Exception/ExceptionInterface.php',
            // @codingStandardsIgnoreEnd
        ];
        $loader = new ClassMapAutoloader([$map]);
        $test   = $loader->getAutoloadMap();
        $this->assertSame($map, $test);
    }

    public function testRegisteringValidMapFilePopulatesAutoloader()
    {
        $this->loader->registerAutoloadMap(__DIR__ . '/_files/goodmap.php');
        $map = $this->loader->getAutoloadMap();
        $this->assertIsArray($map);
        $this->assertCount(2, $map);
        // Just to make sure nothing changes after loading the same map again
        // (loadMapFromFile should just return)
        $this->loader->registerAutoloadMap(__DIR__ . '/_files/goodmap.php');
        $map = $this->loader->getAutoloadMap();
        $this->assertIsArray($map);
        $this->assertCount(2, $map);
    }

    public function testRegisteringMultipleMapsMergesThem()
    {
        $map = [
            // @codingStandardsIgnoreStart
            'Laminas\Loader\Exception\ExceptionInterface' => __DIR__ . '/../../../library/Laminas/Loader/Exception/ExceptionInterface.php',
            // @codingStandardsIgnoreEnd
            StandardAutoloaderTest::class => 'some/bogus/path.php',
        ];
        $this->loader->registerAutoloadMap($map);
        $this->loader->registerAutoloadMap(__DIR__ . '/_files/goodmap.php');

        $test = $this->loader->getAutoloadMap();
        $this->assertIsArray($test);
        $this->assertCount(3, $test);
        $this->assertNotEquals(
            $map[StandardAutoloaderTest::class],
            $test[StandardAutoloaderTest::class]
        );
    }

    public function testCanRegisterMultipleMapsAtOnce()
    {
        $map  = [
            // @codingStandardsIgnoreStart
            'Laminas\Loader\Exception\ExceptionInterface' => __DIR__ . '/../../../library/Laminas/Loader/Exception/ExceptionInterface.php',
            // @codingStandardsIgnoreEnd
            StandardAutoloaderTest::class => 'some/bogus/path.php',
        ];
        $maps = [$map, __DIR__ . '/_files/goodmap.php'];
        $this->loader->registerAutoloadMaps($maps);
        $test = $this->loader->getAutoloadMap();
        $this->assertIsArray($test);
        $this->assertCount(3, $test);
    }

    public function testRegisterMapsThrowsExceptionForNonTraversableArguments()
    {
        $tests = [true, 'string', 1, 1.0, new stdClass()];
        foreach ($tests as $test) {
            try {
                $this->loader->registerAutoloadMaps($test);
                $this->fail('Should not register non-traversable arguments');
            } catch (InvalidArgumentException $e) {
                $this->assertStringContainsString('array or implement Traversable', $e->getMessage());
            }
        }
    }

    public function testAutoloadLoadsClasses()
    {
        $map = ['LaminasTest\UnusualNamespace\ClassMappedClass' => __DIR__ . '/TestAsset/ClassMappedClass.php'];
        $this->loader->registerAutoloadMap($map);
        $loaded = $this->loader->autoload('LaminasTest\UnusualNamespace\ClassMappedClass');
        $this->assertSame('LaminasTest\UnusualNamespace\ClassMappedClass', $loaded);
        $this->assertTrue(class_exists('LaminasTest\UnusualNamespace\ClassMappedClass', false));
    }

    public function testIgnoresClassesNotInItsMap()
    {
        $map = ['LaminasTest\UnusualNamespace\ClassMappedClass' => __DIR__ . '/TestAsset/ClassMappedClass.php'];
        $this->loader->registerAutoloadMap($map);
        $this->assertFalse($this->loader->autoload('LaminasTest\UnusualNamespace\UnMappedClass'));
        $this->assertFalse(class_exists('LaminasTest\UnusualNamespace\UnMappedClass', false));
    }

    public function testRegisterRegistersCallbackWithSplAutoload()
    {
        $this->loader->register();
        $loaders = spl_autoload_functions();
        $this->assertGreaterThan(count($this->loaders), count($loaders));
        $test = array_shift($loaders);
        $this->assertEquals([$this->loader, 'autoload'], $test);
    }

    public function testCanLoadClassMapFromPhar()
    {
        // @codingStandardsIgnoreStart
        $map = 'phar://' . str_replace('/', DIRECTORY_SEPARATOR, __DIR__ . '/_files/classmap.phar/test/.//../autoload_classmap.php');
        // @codingStandardsIgnoreEnd
        $this->loader->registerAutoloadMap($map);
        $loaded = $this->loader->autoload('some\loadedclass');
        $this->assertSame('some\loadedclass', $loaded);
        $this->assertTrue(class_exists('some\loadedclass', false));

        // will not register duplicate, even with a different relative path
        // @codingStandardsIgnoreStart
        $map = 'phar://' . str_replace('/', DIRECTORY_SEPARATOR, __DIR__ . '/_files/classmap.phar/test/./foo/../../autoload_classmap.php');
        // @codingStandardsIgnoreEnd
        $this->loader->registerAutoloadMap($map);
        $test = $this->loader->getAutoloadMap();
        $this->assertCount(1, $test);
    }
}
