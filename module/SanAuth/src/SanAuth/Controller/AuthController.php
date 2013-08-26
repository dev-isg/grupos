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

// SanAuth\Controller\UpdatepassForm;
// use SanAuth\Model\User;
class AuthController extends AbstractActionController {

    protected $form;
    protected $storage;
    protected $authservice;
    protected $usuarioTable;
    protected $grupoTable;

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
         $token = $this->params()->fromQuery('token');
     //var_dump($token);exit;
        if($token)
        {$usuario = $this->getUsuarioTable()->usuario($token);
        if(count($usuario)>0)
         {$this->getUsuarioTable()->cambiarestado($usuario[0]['in_id']);
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
                    foreach ($result->getMessages() as $message) {
                        $this->flashmessenger()->addMessage($message);
                    }

                    if ($result->isValid()) {
//                        var_dump($usuario);
                        $accion = $request->getPost('accion');
                        if ($accion == 'detalleevento') {
                            $redirect = 'evento';
                        } elseif ($accion == 'detallegrupo') {
                            $redirect = 'grupo';
                        } elseif ($accion == 'index') {
                            $redirect = 'elegir-grupo';//'agregar-grupo';
                        }
                        $storage = $this->getAuthService()->getStorage();
                        $storage->write($this->getServiceLocator()
                                        ->get('TableAuthService')
                                        ->getResultRowObject(array(
                                            'in_id',
                                            'va_nombre',
                                            'va_contrasena',
                                            'va_email'
                                        )));
                       
                    }
                    } 
                }
                else{return $this->redirect()->toUrl($this->getRequest()->getBaseUrl().'/auth');}
              
            }
        

        return $this->redirect()->toRoute($redirect, array('action' => 'agregargrupo')); //$this->redirect()->toUrl($this->getRequest()->getBaseUrl().$redirect); //
    }
    
    public function validarAction(){
        $request = $this->getRequest();
        if ($request->isPost()) {
                $correo = $this->params()->fromPost('va_email','cesar@yopmail.com');
                $contrasena = $this->params()->fromPost('va_contrasena','321654987');
                $usuario = $this->getUsuarioTable()->usuario1($correo);
                if ($usuario[0]['va_estado'] == 'activo') {
                    $password=$this->getUsuarioTable()->getUsuario($usuario[0]['in_id']);
                    if ($password) {
                        if($password===$contrasena){
                            return;
                        }else{
                           $mensaje='El correo no concide con la contrasena';
                           $result = new JsonModel(array(
                            'mensaje' =>$mensaje,
                            ));
                            return $result;
                        }
                    }else{
                        return;
                    }
                }else{
                    $mensaje='El correo no se encuentra registrado';
                    $result = new JsonModel(array(
                            'mensaje' =>$mensaje,
                        ));
                     return $result;
                }
        }
    
    }

    public function logoutAction() {
        if ($this->getAuthService()->hasIdentity()) {
            $this->getSessionStorage()->forgetMe();
            $this->getAuthService()->clearIdentity();
            $this->flashmessenger()->addMessage("You've been logged out");
        }
        return $this->redirect()->toRoute('home');
        // return $this->redirect()->toRoute('login');
    }

    public function changeemailAction() {
        $renderer = $this->serviceLocator->get('Zend\View\Renderer\RendererInterface');
        $renderer->inlineScript()
        ->setScript('$(document).ready(function(){if($("#usuario").length){valregistro("#usuario");}});')
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
                                                     Su clave es: <a href="' . $config['host']['base'] . '/cambio-contrasena?contrasena=' . utf8_decode($results) . '">
                                                     <strong style="color:#133088; font-weight: bold;">' . utf8_decode($results) . '</strong></a><br />
                                              
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