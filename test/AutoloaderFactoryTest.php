<?php

namespace LaminasTest\Loader;

use Laminas\Loader\AutoloaderFactory;
use Laminas\Loader\ClassMapAutoloader;
use Laminas\Loader\Exception\InvalidArgumentException;
use Laminas\Loader\StandardAutoloader;
use LaminasTest\Loader\TestAsset\TestPlugins\Foo;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

use function array_shift;
use function class_exists;
use function get_include_path;
use function is_array;
use function set_include_path;
use function spl_autoload_functions;
use function spl_autoload_register;
use function spl_autoload_unregister;

/**
 * @group      Loader
 */
class AutoloaderFactoryTest extends TestCase
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
    }

    public function tearDown(): void
    {
        AutoloaderFactory::unregisterAutoloaders();
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

    public function testRegisteringValidMapFilePopulatesAutoloader()
    {
        AutoloaderFactory::factory([
            ClassMapAutoloader::class => [
                __DIR__ . '/_files/goodmap.php',
            ],
        ]);
        $loader = AutoloaderFactory::getRegisteredAutoloader(ClassMapAutoloader::class);
        $map    = $loader->getAutoloadMap();
        $this->assertIsArray($map);
        $this->assertCount(2, $map);
    }

    /**
     * This tests checks if invalid autoloaders cause exceptions
     */
    public function testFactoryCatchesInvalidClasses()
    {
        include __DIR__ . '/_files/InvalidInterfaceAutoloader.php';
        $this->expectException(InvalidArgumentException::class);
        AutoloaderFactory::factory([
            'InvalidInterfaceAutoloader' => [],
        ]);
    }

    public function testFactoryDoesNotRegisterDuplicateAutoloaders()
    {
        AutoloaderFactory::factory([
            StandardAutoloader::class => [
                'namespaces' => [
                    'TestNamespace' => __DIR__ . '/TestAsset/TestNamespace',
                ],
            ],
        ]);
        $this->assertCount(1, AutoloaderFactory::getRegisteredAutoloaders());
        AutoloaderFactory::factory([
            StandardAutoloader::class => [
                'namespaces' => [
                    'LaminasTest\Loader\TestAsset\TestPlugins' => __DIR__ . '/TestAsset/TestPlugins',
                ],
            ],
        ]);
        $this->assertCount(1, AutoloaderFactory::getRegisteredAutoloaders());
        $this->assertTrue(class_exists('TestNamespace\NoDuplicateAutoloadersCase'));
        $this->assertTrue(class_exists(Foo::class));
    }

    public function testCanUnregisterAutoloaders()
    {
        AutoloaderFactory::factory([
            StandardAutoloader::class => [
                'namespaces' => [
                    'TestNamespace' => __DIR__ . '/TestAsset/TestNamespace',
                ],
            ],
        ]);
        AutoloaderFactory::unregisterAutoloaders();
        $this->assertCount(0, AutoloaderFactory::getRegisteredAutoloaders());
    }

    public function testCanUnregisterAutoloadersByClassName()
    {
        AutoloaderFactory::factory([
            StandardAutoloader::class => [
                'namespaces' => [
                    'TestNamespace' => __DIR__ . '/TestAsset/TestNamespace',
                ],
            ],
        ]);
        AutoloaderFactory::unregisterAutoloader(StandardAutoloader::class);
        $this->assertCount(0, AutoloaderFactory::getRegisteredAutoloaders());
    }

    public function testCanGetValidRegisteredAutoloader()
    {
        AutoloaderFactory::factory([
            StandardAutoloader::class => [
                'namespaces' => [
                    'TestNamespace' => __DIR__ . '/TestAsset/TestNamespace',
                ],
            ],
        ]);
        $autoloader = AutoloaderFactory::getRegisteredAutoloader(StandardAutoloader::class);
        $this->assertInstanceOf(StandardAutoloader::class, $autoloader);
    }

    public function testDefaultAutoloader()
    {
        AutoloaderFactory::factory();
        $autoloader = AutoloaderFactory::getRegisteredAutoloader(StandardAutoloader::class);
        $this->assertInstanceOf(StandardAutoloader::class, $autoloader);
        $this->assertCount(1, AutoloaderFactory::getRegisteredAutoloaders());
    }

    public function testGetInvalidAutoloaderThrowsException()
    {
        $this->expectException(InvalidArgumentException::class);
        $loader = AutoloaderFactory::getRegisteredAutoloader('InvalidAutoloader');
    }

    public function testFactoryWithInvalidArgumentThrowsException()
    {
        $this->expectException(InvalidArgumentException::class);
        AutoloaderFactory::factory('InvalidArgument');
    }

    public function testFactoryWithInvalidAutoloaderClassThrowsException()
    {
        $this->expectException(InvalidArgumentException::class);
        AutoloaderFactory::factory(['InvalidAutoloader' => []]);
    }

    public function testCannotBeInstantiatedViaConstructor()
    {
        $reflection  = new ReflectionClass(AutoloaderFactory::class);
        $constructor = $reflection->getConstructor();
        $this->assertNull($constructor);
    }

    public function testPassingNoArgumentsToFactoryInstantiatesAndRegistersStandardAutoloader()
    {
        AutoloaderFactory::factory();
        $loaders = AutoloaderFactory::getRegisteredAutoloaders();
        $this->assertCount(1, $loaders);
        $loader = array_shift($loaders);
        $this->assertInstanceOf(StandardAutoloader::class, $loader);

        $test  = [$loader, 'autoload'];
        $found = false;
        foreach (spl_autoload_functions() as $function) {
            if ($function === $test) {
                $found = true;
                break;
            }
        }
        $this->assertTrue($found, 'StandardAutoloader not registered with spl_autoload');
    }
}
