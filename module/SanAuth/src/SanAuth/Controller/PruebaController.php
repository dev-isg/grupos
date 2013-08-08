<?php

namespace SanAuth\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\Form\Annotation\AnnotationBuilder;
use Zend\View\Model\ViewModel;
use Zend\Session\SessionManager;
use Zend\Session\Container;
use Zend\Mail\Message;
use Zend\Authentication\Adapter;

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of PruebaController
 *
 * @author isg-0001
 */
class PruebaController extends AbstractActionController {

    //put your code here
    function indexAction() {
        
    }

    function loginAction() {
        $this->coreAuth($adapter, $authService);
        $authService->setIdentity($_GET['u'])
                ->setCredential($_GET['p']);
        $result = $adapter->authenticate();
        if ($result->isValid()) {
            $storage = $adapter->getStorage();
            $storage->write($authService->getResultRowObject(array('in_id','va_nombre', 'va_contrasena','va_email')));
        }
    }

    function validarAction() {

        $this->coreAuth($adapter, $authService);
        if ($adapter->hasIdentity()) {
            echo 'logueado';
            $storage = $adapter->getStorage();
            $data = $storage->read();
            print_r($data);
        } else
            echo 'sin login';
        exit;
    }

    function coreAuth(&$adapter, &$authService) {
        $dbAdapter = new \Zend\Db\Adapter\Adapter(array(
                    'driver' => 'Pdo',
                    'username' => 'kevin',
                    'password' => '123456',
                    'dsn' => 'mysql:dbname=bd_grupos;host=192.168.1.50',
                    'driver_options' => array(
                    )
                ));
        $adapter = new \Zend\Authentication\AuthenticationService();
        $authService = new \Zend\Authentication\Adapter\DbTable($dbAdapter,
                        'ta_usuario',
                        'va_nombre',
                        'va_contrasena'
        );


        $adapter->setStorage(new \Zend\Authentication\Storage\Session('Auth'));
        
        $adapter->setAdapter($authService);
    }

}

?>
