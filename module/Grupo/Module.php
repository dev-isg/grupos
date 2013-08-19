<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/ZendSkeletonModule for the canonical source repository
 * @copyright Copyright (c) 2005-2012 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Grupo;

use Zend\ModuleManager\Feature\AutoloaderProviderInterface;
use Zend\Mvc\ModuleRouteListener;
use Zend\Mvc\MvcEvent;
use Grupo\Model\Grupo;
use Grupo\Model\GrupoTable;
use Grupo\Model\Evento;
use Grupo\Model\EventoTable;
use Zend\Db\ResultSet\ResultSet;
use Zend\Db\TableGateway\TableGateway;

class Module implements AutoloaderProviderInterface
{
    public function getAutoloaderConfig()
    {
        return array(
            'Zend\Loader\ClassMapAutoloader' => array(
                __DIR__ . '/autoload_classmap.php',
            ),
            'Zend\Loader\StandardAutoloader' => array(
                'namespaces' => array(
		    // if we're in a namespace deeper than one level we need to fix the \ in the path
                    __NAMESPACE__ => __DIR__ . '/src/' . str_replace('\\', '/' , __NAMESPACE__),
                ),
            ),
        );
    }

    public function getConfig()
    {
        return include __DIR__ . '/config/module.config.php';
    }
    
    public function getServiceConfig()
    {
        return array(
            'factories' => array(
                'Grupo\Model\GrupoTable' =>  function($sm) {
                    $tableGateway = $sm->get('GrupoTableGateway');
                    $table = new GrupoTable($tableGateway);
                    return $table;
                },
                'GrupoTableGateway' => function ($sm) {
                    $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
                    $resultSetPrototype = new ResultSet();
                    $resultSetPrototype->setArrayObjectPrototype(new Grupo());
                    return new TableGateway('ta_grupo', $dbAdapter, null, $resultSetPrototype);//
                },
               'Grupo\Model\EventoTable' =>  function($sm) {
                    $tableGateway = $sm->get('EventoTableGateway');
                    $table = new EventoTable($tableGateway);
                    return $table;
                },
                'EventoTableGateway' => function ($sm) {
                    $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
                    $resultSetPrototype = new ResultSet();
                    $resultSetPrototype->setArrayObjectPrototype(new Evento());
                    return new TableGateway('ta_evento', $dbAdapter, null, $resultSetPrototype);//
                },
                'mail.transport' => function ($sm) {
                $config = $sm->get('config'); 
                $transport = new \Zend\Mail\Transport\Smtp();   
                $transport->setOptions(new \Zend\Mail\Transport\SmtpOptions($config['mail']['transport']['options']));

                return $transport;
            },
                     
                        
            ),
        );
    }

    public function onBootstrap(MvcEvent $e)
    {
        // You may not need to do this if you're doing it elsewhere in your
        // application
        $eventManager        = $e->getApplication()->getEventManager();
        $moduleRouteListener = new ModuleRouteListener();
        $moduleRouteListener->attach($eventManager);
     
        
        
         //   $categorias->layout()->categorias = new \Grupo\Controller\IndexController();
            //$categorias->layout()->categorias =$actionName;


    }
}


