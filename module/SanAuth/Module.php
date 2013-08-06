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
         $app = $e->getApplication();
        $locator = $app->getServiceManager();
        $authAdapter = $locator->get('AuthService');
//        $controller = $e->getTarget();
//        $routeMatch = $e->getRouteMatch();
//        $controller = $routeMatch->getParam('action', 'not-found');//$routeMatch->getParam('controller');
//        var_dump($controller);exit;

        if($authAdapter->hasIdentity() === true){
    
        
//             return $controller->redirect()->toRoute('adminwithlang/adminindex');
        }else{
//             echo 'no estas logeado';exit;
//                var_dump($controller->plugin('redirect')->toRoute('login'));exit;
//             return $controller->redirect()->toRoute('login');
        }
}
}
