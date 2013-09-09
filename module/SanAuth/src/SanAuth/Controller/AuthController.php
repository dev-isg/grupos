<?php

namespace SanAuth\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\Form\Annotation\AnnotationBuilder;
use Zend\View\Model\ViewModel;
use SanAuth\Form\UserForm;
use SanAuth\Form\PasswordForm;
use SanAuth\Form\UpdatepassForm;
use Zend\Session\SessionManager;
use Zend\Session\Container;
use Zend\Mail\Message;
use Usuario\Model\Usuario;
use Zend\View\Model\JsonModel;
use Grupo\Controller\IndexController;

// SanAuth\Controller\UpdatepassForm;
// use SanAuth\Model\User;
class AuthController extends AbstractActionController {

    protected $form;
    protected $storage;
    protected $authservice;
    protected $usuarioTable;
    protected $grupoTable;

    
    public function __construct() {
        $this->_options = new \Zend\Config\Config(include APPLICATION_PATH . '/config/autoload/global.php');     
    }

    public function getAuthService() {
        if (!$this->authservice) {
            $this->authservice = $this->getServiceLocator()->get('AuthService');
        }

        return $this->authservice;
    }

    public function getSessionStorage() {
        if (!$this->storage) {
            $this->storage = $this->getServiceLocator()->get('SanAuth\Model\MyAuthStorage');
        }

        return $this->storage;
    }

    public function getForm() {
        if (!$this->form) {
            // $user = new User();
            // $builder = new AnnotationBuilder();

            $this->form = new \SanAuth\Form\UserForm(); // $builder->createForm($user);
        }

        return $this->form;
    }


    public function loginAction()
    {  
        $renderer = $this->serviceLocator->get('Zend\View\Renderer\RendererInterface');
        $renderer->inlineScript()
        ->prependFile($this->_options->host->base . '/js/main.js');
        $categorias =  $this->getGrupoTable()->tipoCategoria();
        $this->layout()->categorias = $categorias;
        $storage = new \Zend\Authentication\Storage\Session('Auth');
        $session=$storage->read();
        if (!isset($session)) {
        $face = new \Grupo\Controller\IndexController();
        $facebook = $face->facebook();
        $this->layout()->login = $facebook['loginUrl'];
        $this->layout()->user = $facebook['user']; }
         $token = $this->params()->fromQuery('token');
     //var_dump($token);exit;
        if($token)
        {$usuario = $this->getUsuarioTable()->usuario($token);
        if(count($usuario)>0)
           
         {$this->getUsuarioTable()->cambiarestado($usuario[0]['in_id']);
//var_dump($usuario[0]['in_id']);exit;
         $mensaje='tu cuenta ya esta activada';}
         else{return $this->redirect()->toUrl($this->getRequest()->getBaseUrl().'/auth');}}

         $form = $this->getForm();
        return array(
            'form' => $form,
            'mensaje' => $mensaje,
            'messages' => $this->flashmessenger()->getMessages()
        );
    }



    public function authenticateAction()
    {  
        $form = $this->getForm();
        $redirect = 'login';
        $request = $this->getRequest();
        
        if ($request->isPost()) {
            
            $form->setData($request->getPost());
            if ($form->isValid()) {
                $correo = $request->getPost('va_email');
                $contrasena = $request->getPost('va_contrasena');
                $this->getAuthService()
                        ->getAdapter()
                        ->setIdentity($correo)
                        ->setCredential($contrasena);

                $usuario = $this->getUsuarioTable()->usuario1($correo);               
                if ($usuario[0]['va_estado'] == 'activo') {
                    $result = $this->getAuthService()->authenticate();
//                    foreach ($result->getMessages() as $message) {
//                        $this->flashmessenger()->addMessage($message);
//                    }

                    if ($result->isValid()) {
                        $urlorigen=$this->getRequest()->getHeader('Referer')->uri()->getPath();
                        $arrurl=explode('/',$urlorigen);
                        $id=end($arrurl);
                        $accion = $request->getPost('accion');
                        $origen = $request->getPost('origen','evento');
                        if ($accion == 'detalleevento') {
                            $redirect = 'evento';
                        } elseif ($accion == 'detallegrupo') {
                            $redirect = 'detalle-grupo';
                        } elseif ($accion == 'index' && $origen!='ingresarPrin') {
                            $redirect = 'elegir-grupo';//'agregar-grupo';
                        } elseif($accion=='index' && $origen=='ingresarPrin'){
                            $redirect = 'home';
                        }
                            
                        $storage = $this->getAuthService()->getStorage();
                        $storage->write($this->getServiceLocator()
                                        ->get('TableAuthService')
                                        ->getResultRowObject(array(
                                            'in_id',
                                            'va_nombre',
                                            'va_contrasena',
                                            'va_email',
                                            'va_foto',
                                            'va_logout',
                                            'id_facebook'
                                        )));
                       
                    }
                    } 
                }
                else{return $this->redirect()->toUrl($this->getRequest()->getBaseUrl().'/auth');}
              
            }
//            echo $id;
//            echo $origen;
//            echo $redirect;exit;
            if($id){
                 return $this->redirect()->toRoute($redirect, array('in_id' => $id));
            }else{
                
                 return $this->redirect()->toRoute($redirect);
            }
    }
    
    
       public function sessionfacebook($email,$pass)
    {  
       
                $correo = $email;
                $contrasena = $pass;
                $this->getAuthService()
                        ->getAdapter()
                        ->setIdentity($correo)
                        ->setCredential($contrasena);

                $usuario = $this->getUsuarioTable()->usuario1($correo);               
                if ($usuario[0]['va_estado'] == 'activo') {
                    $result = $this->getAuthService()->authenticate();
                    foreach ($result->getMessages() as $message) {
                        $this->flashmessenger()->addMessage($message);
                    }

                    if ($result->isValid()) {                           
                        $storage = $this->getAuthService()->getStorage();
                        $storage->write($this->getServiceLocator()
                                        ->get('TableAuthService')
                                        ->getResultRowObject(array(
                                            'in_id',
                                            'va_nombre',
                                            'va_contrasena',
                                            'va_email',
                                            'va_foto',
                                            'va_logout',
                                            'id_facebook'
                                        )));
                       
                    }
                    } 
                
                else{return $this->redirect()->toUrl($this->getRequest()->getBaseUrl().'/auth');}
              
            
//            echo $id;
//            echo $origen;
//            echo $redirect;exit;
            if($id){
                 return $this->redirect()->toRoute($redirect, array('in_id' => $id));
            }else{
                
                 return $this->redirect()->toRoute($redirect);
            }
    }
    
    
    
    
    
    
    
    
    
    
    
    public function validarcontrasenaAction(){
        $request = $this->getRequest();
        if ($request->isPost()) {
                $correo = $this->params()->fromPost('va_email');
                $contrasena = sha1($this->params()->fromPost('va_contrasena'));  
                $usuario = $this->getUsuarioTable()->usuario1($correo);
                if ($usuario[0]['va_estado'] == 'activo') {
                    $password=$this->getUsuarioTable()->getUsuario($usuario[0]['in_id'])->va_contrasena;
                    if ($password) {
                        if($password===$contrasena){
                            return new JsonModel(array(
                            'success'=>true
                            ));
                        }else{
                           $mensaje='El correo no concide con la contrasena';
                           $result = new JsonModel(array(
                            'menssage' =>$mensaje,
                            'success'=>false
                            ));
                            return $result;
                        }
                    }else{
                            return new JsonModel(array(
                            'success'=>false
                            ));
                    }
                }else{
                    $mensaje='El correo no se encuentra registrado';
                    $result = new JsonModel(array(
                            'menssage' =>$mensaje,
                            'success'=>false
                        ));
                     return $result;
                }
        }
    
    }
    
      public function validarcorreoAction(){
        $request = $this->getRequest();
        if ($request->isPost()) {
                $correo = $this->params()->fromPost('va_email');     
                $usuario = $this->getUsuarioTable()->usuario1($correo);
                if($usuario){
                     if ($usuario[0]['va_estado'] == 'activo') {
                            return new JsonModel(array(
                            'success'=>true
                            ));
                    }else{
                       $mensaje='El correo no se encuentra registrado';
                       $result = new JsonModel(array(
                               'menssage' =>$mensaje,
                               'success'=>false
                           ));
                        return $result;
                   }                   
                }else{
                    $mensaje='El correo no se encuentra registrado';
                      return new JsonModel(array(
                          'menssage' =>$mensaje,
                           'success'=>false
                            ));
                    
                }

        }
    
    }

    public function logoutAction() {
        session_destroy();
        $finsesion=  $this->params()->fromRoute('in_id_face');

        if ($this->getAuthService()->hasIdentity()) {
            $this->getSessionStorage()->forgetMe();
            $this->getAuthService()->clearIdentity();
//            $this->flashmessenger()->addMessage("You've been logged out");
        if($finsesion){
            return $this->redirect()->toUrl($finsesion);
         }
        }
        return $this->redirect()->toRoute('home');
        // return $this->redirect()->toRoute('login');
    }

    public function changeemailAction() {
        $renderer = $this->serviceLocator->get('Zend\View\Renderer\RendererInterface');
        $renderer->inlineScript()
        ->setScript('$(document).ready(function(){valUsuario();});')
        ->prependFile($this->_options->host->base . '/js/main.js')
        ->prependFile($this->_options->host->base . '/js/jquery.validate.min.js');
        $categorias =  $this->getGrupoTable()->tipoCategoria();
        $this->layout()->categorias = $categorias;
        $request = $this->getRequest();
        $form = new PasswordForm();
        if ($request->isPost()) {
            $form->setData($request->getPost());
            if ($form->isValid()) {
                $mail = $this->params()->fromPost('va_email');
                try {
                    $results = $this->getUsuarioTable()->generarPassword($mail);
                    $usuario = $this->getUsuarioTable()->getUsuarioxEmail($mail);
                    $this->flashmessenger()->addMessage('Este correo fue enviado con exito...');
                } catch (\Exception $e) {
                    $this->flashmessenger()->addMessage('Este correo no esta registrado...');
                }

                if ($results) {
                    $config = $this->getServiceLocator()->get('Config');
                    $bodyHtml = '<!DOCTYPE html><html xmlns="http://www.w3.org/1999/xhtml">
                                               <head>
                                               <meta http-equiv="Content-type" content="text/html;charset=UTF-8"/>
                                               </head>
                                               <body>
                                                    <div style="color: #7D7D7D"><br />
                                                    Hola '.ucwords($usuario->va_nombre).', 
                                                    Para recuperar tu contraseña debes hacer <a href="' . $config['host']['base'] . '/cambio-contrasena?contrasena=' . utf8_decode($results) . '">Clic Aquí</a>
                                                    o copiar la siguiente url en su navegador:' . $config['host']['base'] . '/cambio-contrasena?contrasena=' . utf8_decode($results) .'          
                                                     </div>
                                               </body>
                                               </html>';

                    $message = new Message();
                    $message->addTo($mail)
                            ->addFrom('listadelsabor@innovationssystems.com', 'juntate.pe')
                            ->setSubject('Recuperación de contraseña');
                    $bodyPart = new \Zend\Mime\Message();
                    $bodyMessage = new \Zend\Mime\Part($bodyHtml);
                    $bodyMessage->type = 'text/html';
                    $bodyPart->setParts(array(
                        $bodyMessage
                    ));
                    $message->setBody($bodyPart);
                    $message->setEncoding('UTF-8');

                    $transport = $this->getServiceLocator()->get('mail.transport'); // new SendmailTransport();//$this->getServiceLocator('mail.transport')
                    $transport->send($message);
                }
                // var_dump($this->flashMessenger()->getMessages());exit;
            }
        }
        $flashMessenger = $this->flashMessenger();
        if ($flashMessenger->hasMessages()) {
            $mensajes = $flashMessenger->getMessages();
        }
        return array(
            'form' => $form,
            'mensaje' => $mensajes
        );
        // $this->flashmessenger()->clearMessages();
    }

    public function recuperarAction() {
        $password = $this->params()->fromQuery('contrasena');
        $form = new UpdatepassForm();
        try {
            $results = $this->getUsuarioTable()->consultarPassword($password);
        } catch (\Exception $e) {
            // echo 'aka es';exit;
            $this->flashMessenger()->addMessage('Este contrase�a temporal no existe...');
        }

        if ($results) {
            $request = $this->getRequest();

            // $usuario = new Usuario();
            // $form = new UsuarioForm();
            // $form->setInputFilter($usuario->getInputFilter());
            $form->setData($request->getPost());
            if ($request->isPost()) {
                if ($form->isValid()) {

                    $nuevopass = $this->params()->fromPost('va_contrasena');
                    if ($this->getUsuarioTable()->cambiarPassword($nuevopass, $results->in_id)) {
                        // $this->flashmessenger()->addMessage('La contrase�a se actualizo correctamente...');
                    } else {
                        // $this->flashmessenger()->addMessage('La contrase�a se no se pudo actualizar correctamente...');
                    }
                }
            }
        }
        return array(
            'form' => $form,
            'password' => $password,
            'mensaje' => $this->flashmessenger()->getMessages()
        );
    }

    public function getUsuarioTable() {
        if (!$this->usuarioTable) {
            $sm = $this->getServiceLocator();
            $this->usuarioTable = $sm->get('Usuario\Model\UsuarioTable');
        }
        return $this->usuarioTable;
    }
    public function getGrupoTable()
    {
        if (! $this->grupoTable) {
            $sm = $this->getServiceLocator();
            $this->grupoTable = $sm->get('Grupo\Model\GrupoTable');
        }
        return $this->grupoTable;
    }

}