<?php

namespace SanAuth\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

class SuccessController extends AbstractActionController
{
    public function indexAction()
    {
//        if (! $this->getServiceLocator()
//                 ->get('AuthService')->hasIdentity()){
//            return $this->redirect()->toRoute('login');
//        }
//          $container = new Container('usuario');
//          $container->init = 'kevin';
//        var_dump($this->getAuthService()->getIdentity());exit;
        $storage = $this->getAuthService()->getStorage();
        $data = $storage->read();
        var_dump($data);exit;

        return new ViewModel();
    }
        public function getAuthService()
    {
        if (! $this->authservice) {
            $this->authservice = $this->getServiceLocator()
                                      ->get('AuthService');
        }
        
        return $this->authservice;
    }
}