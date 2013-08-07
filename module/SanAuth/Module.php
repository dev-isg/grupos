<?php

namespace SanAuth;

use Zend\Mvc\ModuleRouteListener;
use Zend\Mvc\MvcEvent;
use Zend\ModuleManager\Feature\AutoloaderProviderInterface;
use Zend\Authentication\AuthenticationService;
use Zend\Authentication\Adapter\DbTable as DbTableAuthAdapter;

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
	
	// setting db config immediately if necessary, ignore if already defined in global.php    
	//   'db' => array(
	//	'username' => 'YOUR USERNAME HERE',
	//	'password' => 'YOUR PASSWORD HERE',
	//	'driver'         => 'Pdo',
	//	'dsn'            => 'mysql:dbname=zf2tutorial;host=localhost',
	//	'driver_options' => array(
	//	    PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES \'UTF8\''
	//	),
	//    ),
	    
            'factories'=>array(
//		 'Zend\Db\Adapter\Adapter'
  //                  => 'Zend\Db\Adapter\AdapterServiceFactory',
		
		'SanAuth\Model\MyAuthStorage' => function($sm){
		    return new \SanAuth\Model\MyAuthStorage('zf_tutorial');  
		},
		
		'AuthService' => function($sm) {
		    $dbAdapter      = $sm->get('Zend\Db\Adapter\Adapter');
                    $dbTableAuthAdapter  = new DbTableAuthAdapter($dbAdapter, 'ta_usuario','va_nombre','va_contrasena');//, 'MD5(?)'
		    
		    $authService = new AuthenticationService();
		    $authService->setAdapter($dbTableAuthAdapter);
		    $authService->setStorage($sm->get('SanAuth\Model\MyAuthStorage'));
		     
		    return $authService;
		},
            ),
        );
    }
public function onBootstrap(MvcEvent $e)
{
        $eventManager        = $e->getApplication()->getEventManager();
        $moduleRouteListener = new ModuleRouteListener();
        $moduleRouteListener->attach($eventManager);
        
         $app = $e->getApplication();
        $app->getEventManager()->getSharedManager()->attach('Zend\Mvc\Controller\AbstractActionController', 'dispatch', function($e) { 
            $locator = $e->getApplication()->getServiceManager();
             $authAdapter = $locator->get('AuthService');
            $controller = $e->getTarget();
            $routeMatch = $e->getRouteMatch();
//            $config = $e->getApplication()->getServiceManager()->get('config');
            $actionName = $routeMatch->getParam('action', 'not-found');
        
            if($actionName=='login'){
                  if($authAdapter->hasIdentity() === true){
//                             $session = $e->getApplication()
//                                    ->getServiceManager()
//                                    ->get('\Zend\Session\SessionManager');
//                       $session->start();
//
//                       $container = new Container('user');
//                       var_dump($container->username);exit;
                      return $controller->redirect()->toRoute('grupo');
                    }
                    else{
                            return;
                    }
            }else if ($actionName=='agregargrupo'){
                if($authAdapter->hasIdentity() === false){
                    return $controller->redirect()->toRoute('login');
                    }
            }

        }, 100);
}
}
