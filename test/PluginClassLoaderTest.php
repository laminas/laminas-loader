<?php

namespace LaminasTest\Loader;

use ArrayObject;
use Laminas\Loader\Exception\InvalidArgumentException;
use Laminas\Loader\PluginClassLoader;
use LaminasTest\Loader\TestAsset\ExtendedPluginClassLoader;
use LaminasTest\Loader\TestAsset\TestPluginMap;
use PHPUnit\Framework\TestCase;
use stdClass;

/**
 * @group      Loader
 */
class PluginClassLoaderTest extends TestCase
{
    /** @var PluginClassLoader */
    public $loader;

    public function setUp(): void
    {
        // Clear any static maps
        PluginClassLoader::addStaticMap(null);
        ExtendedPluginClassLoader::addStaticMap(null);

        // Create a loader instance
        $this->loader = new PluginClassLoader();
    }

    public function testPluginClassLoaderHasNoAssociationsByDefault()
    {
        $plugins = $this->loader->getRegisteredPlugins();
        $this->assertEmpty($plugins);
    }

    public function testRegisterPluginRegistersShortNameClassNameAssociation()
    {
        $this->loader->registerPlugin('loader', self::class);
        $plugins = $this->loader->getRegisteredPlugins();
        $this->assertArrayHasKey('loader', $plugins);
        $this->assertEquals(self::class, $plugins['loader']);
    }

    public function testCallingRegisterPluginWithAnExistingPluginNameOverwritesThatMapAssociation()
    {
        $this->testRegisterPluginRegistersShortNameClassNameAssociation();
        $this->loader->registerPlugin('loader', PluginClassLoader::class);
        $plugins = $this->loader->getRegisteredPlugins();
        $this->assertArrayHasKey('loader', $plugins);
        $this->assertEquals(PluginClassLoader::class, $plugins['loader']);
    }

    public function testCallingRegisterPluginsWithInvalidStringMapRaisesException()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->loader->registerPlugins('__foobar__');
    }

    public function testCallingRegisterPluginsWithStringMapResolvingToNonTraversableClassRaisesException()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->loader->registerPlugins('stdClass');
    }

    public function testCallingRegisterPluginsWithValidStringMapResolvingToTraversableClassRegistersPlugins()
    {
        $this->loader->registerPlugins(TestPluginMap::class);
        $pluginMap = new TestPluginMap();
        $this->assertEquals($pluginMap->map, $this->loader->getRegisteredPlugins());
    }

    /**
     * @dataProvider invalidMaps
     * @param mixed $arg
     */
    public function testCallingRegisterPluginsWithNonArrayNonStringNonTraversableValueRaisesException($arg)
    {
        $this->expectException(InvalidArgumentException::class);
        $this->loader->registerPlugins($arg);
    }

    /** @psalm-return array<array-key, array{0: mixed}> */
    public function invalidMaps(): array
    {
        return [
            [null],
            [true],
            [1],
            [1.0],
            [new stdClass()],
        ];
    }

    public function testCallingRegisterPluginsWithArrayRegistersMap()
    {
        $map = ['test' => self::class];
        $this->loader->registerPlugins($map);
        $test = $this->loader->getRegisteredPlugins();
        $this->assertEquals($map, $test);
    }

    public function testCallingRegisterPluginsWithTraversableObjectRegistersMap()
    {
        $map = new TestPluginMap();
        $this->loader->registerPlugins($map);
        $test = $this->loader->getRegisteredPlugins();
        $this->assertEquals($map->map, $test);
    }

    public function testUnregisterPluginRemovesPluginFromMap()
    {
        $map = new TestPluginMap();
        $this->loader->registerPlugins($map);

        $this->loader->unregisterPlugin('test');

        $test = $this->loader->getRegisteredPlugins();
        $this->assertArrayNotHasKey('test', $test);
    }

    public function testIsLoadedReturnsFalseIfPluginIsNotInMap()
    {
        $this->assertFalse($this->loader->isLoaded('test'));
    }

    public function testIsLoadedReturnsTrueIfPluginIsInMap()
    {
        $this->loader->registerPlugin('test', self::class);
        $this->assertTrue($this->loader->isLoaded('test'));
    }

    public function testGetClassNameReturnsFalseIfPluginIsNotInMap()
    {
        $this->assertFalse($this->loader->getClassName('test'));
    }

    public function testGetClassNameReturnsClassNameIfPluginIsInMap()
    {
        $this->loader->registerPlugin('test', self::class);
        $this->assertEquals(self::class, $this->loader->getClassName('test'));
    }

    public function testLoadReturnsFalseIfPluginIsNotInMap()
    {
        $this->assertFalse($this->loader->load('test'));
    }

    public function testLoadReturnsClassNameIfPluginIsInMap()
    {
        $this->loader->registerPlugin('test', self::class);
        $this->assertEquals(self::class, $this->loader->load('test'));
    }

    public function testIteratingLoaderIteratesPluginMap()
    {
        $map = new TestPluginMap();
        $this->loader->registerPlugins($map);
        $test = [];
        foreach ($this->loader as $name => $class) {
            $test[$name] = $class;
        }

        $this->assertEquals($map->map, $test);
    }

    public function testPluginRegistrationIsCaseInsensitive()
    {
        $map = [
            'foo' => self::class,
            'FOO' => __NAMESPACE__ . '\TestAsset\TestPluginMap',
        ];
        $this->loader->registerPlugins($map);
        $this->assertEquals($map['FOO'], $this->loader->getClassName('foo'));
    }

    public function testAddingStaticMapDoesNotAffectExistingInstances()
    {
        PluginClassLoader::addStaticMap([
            'test' => self::class,
        ]);
        $this->assertFalse($this->loader->getClassName('test'));
    }

    public function testAllowsSettingStaticMapForSeedingInstance()
    {
        PluginClassLoader::addStaticMap([
            'test' => self::class,
        ]);
        $loader = new PluginClassLoader();
        $this->assertEquals(self::class, $loader->getClassName('test'));
    }

    public function testPassingNullToStaticMapClearsMap()
    {
        $this->testAllowsSettingStaticMapForSeedingInstance();
        PluginClassLoader::addStaticMap(null);
        $loader = new PluginClassLoader();
        $this->assertFalse($loader->getClassName('test'));
    }

    public function testAllowsPassingTraversableObjectToStaticMap()
    {
        $map = new ArrayObject([
            'test' => self::class,
        ]);
        PluginClassLoader::addStaticMap($map);
        $loader = new PluginClassLoader();
        $this->assertEquals(self::class, $loader->getClassName('test'));
    }

    public function testMultipleCallsToAddStaticMapMergeMap()
    {
        PluginClassLoader::addStaticMap([
            'test' => self::class,
        ]);
        PluginClassLoader::addStaticMap([
            'loader' => PluginClassLoader::class,
        ]);
        $loader = new PluginClassLoader();
        $this->assertEquals(self::class, $loader->getClassName('test'));
        $this->assertEquals(PluginClassLoader::class, $loader->getClassName('loader'));
    }

    public function testStaticMapUsesLateStaticBinding()
    {
        ExtendedPluginClassLoader::addStaticMap(['test' => self::class]);
        $loader = new PluginClassLoader();
        $this->assertFalse($loader->getClassName('test'));
        $loader = new ExtendedPluginClassLoader();
        $this->assertEquals(self::class, $loader->getClassName('test'));
    }

    public function testMapPrecedenceIsExplicitTrumpsConstructorTrumpsStaticTrumpsInternal()
    {
        $loader = new ExtendedPluginClassLoader();
        $this->assertEquals(PluginClassLoader::class, $loader->getClassName('loader'));

        ExtendedPluginClassLoader::addStaticMap(['loader' => self::class]);
        $loader = new ExtendedPluginClassLoader();
        $this->assertEquals(self::class, $loader->getClassName('loader'));

        $loader = new ExtendedPluginClassLoader(
            ['loader' => ExtendedPluginClassLoader::class]
        );
        $this->assertEquals(ExtendedPluginClassLoader::class, $loader->getClassName('loader'));

        $loader->registerPlugin('loader', self::class);
        $this->assertEquals(self::class, $loader->getClassName('loader'));
    }

    public function testRegisterPluginsCanAcceptArrayElementWithClassNameProvidingAMap()
    {
        $pluginMap = new TestPluginMap();
        $this->loader->registerPlugins([TestPluginMap::class]);
        $this->assertEquals($pluginMap->map, $this->loader->getRegisteredPlugins());
    }

    public function testRegisterPluginsCanAcceptArrayElementWithObjectProvidingAMap()
    {
        $pluginMap = new TestPluginMap();
        $this->loader->registerPlugins([$pluginMap]);
        $this->assertEquals($pluginMap->map, $this->loader->getRegisteredPlugins());
    }
}
