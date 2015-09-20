<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Application;

use Zend\Mvc\ModuleRouteListener;
use Zend\Mvc\MvcEvent;

class Module implements \Zend\ModuleManager\Feature\ServiceProviderInterface
{
    public function onBootstrap(MvcEvent $e)
    {
        $eventManager        = $e->getApplication()->getEventManager();
        $moduleRouteListener = new ModuleRouteListener();
        $moduleRouteListener->attach($eventManager);
    }

    public function getConfig()
    {
        return include __DIR__ . '/config/module.config.php';
    }

    public function getAutoloaderConfig()
    {
        return array(
            'Zend\Loader\StandardAutoloader' => array(
                'namespaces' => array(
                    __NAMESPACE__ => __DIR__ . '/src/' . __NAMESPACE__,
                ),
            ),
        );
    }
    public function getControllerPluginConfig()
    {
    	return array(
    			'factories'  => array(
    					'JsonGeneratorPlugin' => function() {
    						return new \Application\Controller\Plugin\JsonGeneratorPlugin();
    					},

    			)
    	);
    }
    public function getConsoleUsage(Console $console)
    {
    	return array(
    			// Describe available commands
    			'user generatejson [--verbose|-v]'    => 'Generate JSON dataof sainsbuyrs groccery items for a user',

    			// Describe expected parameters
    			array( '--verbose|-v',     '(optional) turn on verbose mode'        ),
    	);
    }
    public function getServiceConfig()
    {

    }
}
