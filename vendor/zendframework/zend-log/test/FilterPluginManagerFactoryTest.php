<?php
/**
 * @link      http://github.com/zendframework/zend-log for the canonical source repository
 * @copyright Copyright (c) 2005-2016 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace ZendTest\Log;

use Interop\Container\ContainerInterface;
use PHPUnit_Framework_TestCase as TestCase;
use Zend\Log\Filter\FilterInterface;
use Zend\Log\FilterPluginManager;
use Zend\Log\FilterPluginManagerFactory;
use Zend\ServiceManager\ServiceLocatorInterface;

class FilterPluginManagerFactoryTest extends TestCase
{
    public function testFactoryReturnsPluginManager()
    {
        $container = $this->prophesize(ContainerInterface::class)->reveal();
        $factory = new FilterPluginManagerFactory();

        $filters = $factory($container, FilterPluginManagerFactory::class);
        $this->assertInstanceOf(FilterPluginManager::class, $filters);

        if (method_exists($filters, 'configure')) {
            // zend-servicemanager v3
            $this->assertAttributeSame($container, 'creationContext', $filters);
        } else {
            // zend-servicemanager v2
            $this->assertSame($container, $filters->getServiceLocator());
        }
    }

    /**
     * @depends testFactoryReturnsPluginManager
     */
    public function testFactoryConfiguresPluginManagerUnderContainerInterop()
    {
        $container = $this->prophesize(ContainerInterface::class)->reveal();
        $filter = $this->prophesize(FilterInterface::class)->reveal();

        $factory = new FilterPluginManagerFactory();
        $filters = $factory($container, FilterPluginManagerFactory::class, [
            'services' => [
                'test' => $filter,
            ],
        ]);
        $this->assertSame($filter, $filters->get('test'));
    }

    /**
     * @depends testFactoryReturnsPluginManager
     */
    public function testFactoryConfiguresPluginManagerUnderServiceManagerV2()
    {
        $container = $this->prophesize(ServiceLocatorInterface::class);
        $container->willImplement(ContainerInterface::class);

        $filter = $this->prophesize(FilterInterface::class)->reveal();

        $factory = new FilterPluginManagerFactory();
        $factory->setCreationOptions([
            'services' => [
                'test' => $filter,
            ],
        ]);

        $filters = $factory->createService($container->reveal());
        $this->assertSame($filter, $filters->get('test'));
    }
}
