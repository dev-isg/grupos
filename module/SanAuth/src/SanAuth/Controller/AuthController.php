<?php

namespace SanAuth\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\Form\Annotation\AnnotationBuilder;
use Zend\View\Model\ViewModel;
use SanAuth\Form\UserForm;
use Zend\Session\Container;

//use SanAuth\Model\User;

class AuthController extends AbstractActionController
{
    protected $form;
    protected $storage;
    protected $authservice;
    
    public function getAuthService()
    {
        if (! $this->authservice) {
            $this->authservice = $this->getServiceLocator()
                                      ->get('AuthService');
        }
        
        return $this->authservice;
    }
    
    public function getSessionStorage()
    {
        if (! $this->storage) {
            $this->storage = $this->getServiceLocator()
                                  ->get('SanAuth\Model\MyAuthStorage');
        }
        
        return $this->storage;
    }
    
    public function getForm()
    {
        if (! $this->form) {
//            $user       = new User();
//            $builder    = new AnnotationBuilder();
                  
            $this->form = new \SanAuth\Form\UserForm();//$builder->createForm($user);
     
        }
        
        return $this->form;
    }
    
    public function loginAction()
    {
        //if already login, redirect to success page 
//        if ($this->getAuthService()->hasIdentity()){
//            return $this->redirect()->toRoute('success');
//        }
                
        $form       = $this->getForm();
        
        return array(
            'form'      => $form,
            'messages'  => $this->flashmessenger()->getMessages()
        );
    }
    
    public function authenticateAction()
    {
        $form       = $this->getForm();
        $redirect = 'login';
        
        $request = $this->getRequest();
        if ($request->isPost()){
            
            $form->setData($request->getPost());
            if (true){//$form->isValid()
//                echo 'entro';exit;
                //check authentication...
//                var_dump($request->getPost('va_nombre'));
//                var_dump($request->getPost('va_contrasena'));
                $this->getAuthService()->getAdapter()
                                       ->setIdentity($request->getPost('va_nombre'))
                                       ->setCredential($request->getPost('va_contrasena'));
                                       
                $result = $this->getAuthService()->authenticate();
                foreach($result->getMessages() as $message)
                {
                    //save message temporary into flashmessenger
                    $this->flashmessenger()->addMessage($message);
                }
                
                if ($result->isValid()) {
                    $redirect = 'success';
                    //check if it has rememberMe :
                    if ($request->getPost('rememberme') == 1 ) {
                        $this->getSessionStorage()
                             ->setRememberMe(1);
                        //set storage again
                  $user_session = new Container('user');
                $user_session->username = 'andy124';
                        $this->getAuthService()->setStorage($this->getSessionStorage());
                    }
                    $this->getAuthService()->setStorage($this->getSessionStorage());
                    $this->getAuthService()->getStorage()->write($request->getPost('va_nombre'));
                }
            }
        }
        
        return $this->redirect()->toRoute($redirect);
    }
    
    public function logoutAction()
    {
        if ($this->getAuthService()->hasIdentity()) {
            $this->getSessionStorage()->forgetMe();
            $this->getAuthService()->clearIdentity();
            $this->flashmessenger()->addMessage("You've been logged out");
        }
        
        return $this->redirect()->toRoute('login');
    }
    
    public function changeemailAction(){
        return array();
    }
}