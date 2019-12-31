<?php

/**
 * @see       https://github.com/laminas/laminas-loader for the canonical source repository
 * @copyright https://github.com/laminas/laminas-loader/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-loader/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\Loader;

use Laminas\Loader\PluginClassLoader;

/**
 * @category   Laminas
 * @package    Loader
 * @subpackage UnitTests
 * @group      Loader
 */
class PluginClassLoaderTest extends \PHPUnit_Framework_TestCase
{
    /** @var PluginClassLoader */
    public $loader;

    public function setUp()
    {
        // Clear any static maps
        PluginClassLoader::addStaticMap(null);
        TestAsset\ExtendedPluginClassLoader::addStaticMap(null);

        // Create a loader instance
        $this->loader = new PluginClassLoader();
    }

    public function testPluginClassLoaderHasNoAssociationsByDefault()
    {
        $plugins = $this->loader->getRegisteredPlugins();
        $this->assertTrue(empty($plugins));
    }

    public function testRegisterPluginRegistersShortNameClassNameAssociation()
    {
        $this->loader->registerPlugin('loader', __CLASS__);
        $plugins = $this->loader->getRegisteredPlugins();
        $this->assertArrayHasKey('loader', $plugins);
        $this->assertEquals(__CLASS__, $plugins['loader']);
    }

    public function testCallingRegisterPluginWithAnExistingPluginNameOverwritesThatMapAssociation()
    {
        $this->testRegisterPluginRegistersShortNameClassNameAssociation();
        $this->loader->registerPlugin('loader', 'Laminas\Loader\PluginClassLoader');
        $plugins = $this->loader->getRegisteredPlugins();
        $this->assertArrayHasKey('loader', $plugins);
        $this->assertEquals('Laminas\Loader\PluginClassLoader', $plugins['loader']);
    }

    public function testCallingRegisterPluginsWithInvalidStringMapRaisesException()
    {
        $this->setExpectedException('Laminas\Loader\Exception\InvalidArgumentException');
        $this->loader->registerPlugins('__foobar__');
    }

    public function testCallingRegisterPluginsWithStringMapResolvingToNonTraversableClassRaisesException()
    {
        $this->setExpectedException('Laminas\Loader\Exception\InvalidArgumentException');
        $this->loader->registerPlugins('stdClass');
    }

    public function testCallingRegisterPluginsWithValidStringMapResolvingToTraversableClassRegistersPlugins()
    {
        $this->loader->registerPlugins('LaminasTest\Loader\TestAsset\TestPluginMap');
        $pluginMap = new TestAsset\TestPluginMap;
        $this->assertEquals($pluginMap->map, $this->loader->getRegisteredPlugins());
    }

    /**
     * @dataProvider invalidMaps
     */
    public function testCallingRegisterPluginsWithNonArrayNonStringNonTraversableValueRaisesException($arg)
    {
        $this->setExpectedException('Laminas\Loader\Exception\InvalidArgumentException');
        $this->loader->registerPlugins($arg);
    }

    public function invalidMaps()
    {
        return array(
            array(null),
            array(true),
            array(1),
            array(1.0),
            array(new \stdClass),
        );
    }

    public function testCallingRegisterPluginsWithArrayRegistersMap()
    {
        $map = array('test' => __CLASS__);
        $this->loader->registerPlugins($map);
        $test = $this->loader->getRegisteredPlugins();
        $this->assertEquals($map, $test);
    }

    public function testCallingRegisterPluginsWithTraversableObjectRegistersMap()
    {
        $map = new TestAsset\TestPluginMap();
        $this->loader->registerPlugins($map);
        $test = $this->loader->getRegisteredPlugins();
        $this->assertEquals($map->map, $test);
    }

    public function testUnregisterPluginRemovesPluginFromMap()
    {
        $map = new TestAsset\TestPluginMap();
        $this->loader->registerPlugins($map);

        $this->loader->unregisterPlugin('test');

        $test = $this->loader->getRegisteredPlugins();
        $this->assertFalse(array_key_exists('test', $test));
    }

    public function testIsLoadedReturnsFalseIfPluginIsNotInMap()
    {
        $this->assertFalse($this->loader->isLoaded('test'));
    }

    public function testIsLoadedReturnsTrueIfPluginIsInMap()
    {
        $this->loader->registerPlugin('test', __CLASS__);
        $this->assertTrue($this->loader->isLoaded('test'));
    }

    public function testGetClassNameReturnsFalseIfPluginIsNotInMap()
    {
        $this->assertFalse($this->loader->getClassName('test'));
    }

    public function testGetClassNameReturnsClassNameIfPluginIsInMap()
    {
        $this->loader->registerPlugin('test', __CLASS__);
        $this->assertEquals(__CLASS__, $this->loader->getClassName('test'));
    }

    public function testLoadReturnsFalseIfPluginIsNotInMap()
    {
        $this->assertFalse($this->loader->load('test'));
    }

    public function testLoadReturnsClassNameIfPluginIsInMap()
    {
        $this->loader->registerPlugin('test', __CLASS__);
        $this->assertEquals(__CLASS__, $this->loader->load('test'));
    }

    public function testIteratingLoaderIteratesPluginMap()
    {
        $map = new TestAsset\TestPluginMap();
        $this->loader->registerPlugins($map);
        $test = array();
        foreach ($this->loader as $name => $class) {
            $test[$name] = $class;
        }

        $this->assertEquals($map->map, $test);
    }

    public function testPluginRegistrationIsCaseInsensitive()
    {
        $map = array(
            'foo' => __CLASS__,
            'FOO' => __NAMESPACE__ . '\TestAsset\TestPluginMap',
        );
        $this->loader->registerPlugins($map);
        $this->assertEquals($map['FOO'], $this->loader->getClassName('foo'));
    }

    public function testAddingStaticMapDoesNotAffectExistingInstances()
    {
        PluginClassLoader::addStaticMap(array(
            'test' => __CLASS__,
        ));
        $this->assertFalse($this->loader->getClassName('test'));
    }

    public function testAllowsSettingStaticMapForSeedingInstance()
    {
        PluginClassLoader::addStaticMap(array(
            'test' => __CLASS__,
        ));
        $loader = new PluginClassLoader();
        $this->assertEquals(__CLASS__, $loader->getClassName('test'));
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
        $map = new \ArrayObject(array(
            'test' => __CLASS__,
        ));
        PluginClassLoader::addStaticMap($map);
        $loader = new PluginClassLoader();
        $this->assertEquals(__CLASS__, $loader->getClassName('test'));
    }

    public function testMultipleCallsToAddStaticMapMergeMap()
    {
        PluginClassLoader::addStaticMap(array(
            'test' => __CLASS__,
        ));
        PluginClassLoader::addStaticMap(array(
            'loader' => 'Laminas\Loader\PluginClassLoader',
        ));
        $loader = new PluginClassLoader();
        $this->assertEquals(__CLASS__, $loader->getClassName('test'));
        $this->assertEquals('Laminas\Loader\PluginClassLoader', $loader->getClassName('loader'));
    }

    public function testStaticMapUsesLateStaticBinding()
    {
        TestAsset\ExtendedPluginClassLoader::addStaticMap(array('test' => __CLASS__));
        $loader = new PluginClassLoader();
        $this->assertFalse($loader->getClassName('test'));
        $loader = new TestAsset\ExtendedPluginClassLoader();
        $this->assertEquals(__CLASS__, $loader->getClassName('test'));
    }

    public function testMapPrecedenceIsExplicitTrumpsConstructorTrumpsStaticTrumpsInternal()
    {
        $loader = new TestAsset\ExtendedPluginClassLoader();
        $this->assertEquals('Laminas\Loader\PluginClassLoader', $loader->getClassName('loader'));

        TestAsset\ExtendedPluginClassLoader::addStaticMap(array('loader' => __CLASS__));
        $loader = new TestAsset\ExtendedPluginClassLoader();
        $this->assertEquals(__CLASS__, $loader->getClassName('loader'));

        $loader = new TestAsset\ExtendedPluginClassLoader(array('loader' => 'LaminasTest\Loader\TestAsset\ExtendedPluginClassLoader'));
        $this->assertEquals('LaminasTest\Loader\TestAsset\ExtendedPluginClassLoader', $loader->getClassName('loader'));

        $loader->registerPlugin('loader', __CLASS__);
        $this->assertEquals(__CLASS__, $loader->getClassName('loader'));
    }

    public function testRegisterPluginsCanAcceptArrayElementWithClassNameProvidingAMap()
    {
        $pluginMap = new TestAsset\TestPluginMap;
        $this->loader->registerPlugins(array('LaminasTest\Loader\TestAsset\TestPluginMap'));
        $this->assertEquals($pluginMap->map, $this->loader->getRegisteredPlugins());
    }

    public function testRegisterPluginsCanAcceptArrayElementWithObjectProvidingAMap()
    {
        $pluginMap = new TestAsset\TestPluginMap;
        $this->loader->registerPlugins(array($pluginMap));
        $this->assertEquals($pluginMap->map, $this->loader->getRegisteredPlugins());
    }
}
