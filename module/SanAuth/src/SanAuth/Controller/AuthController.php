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
//SanAuth\Controller\UpdatepassForm;
// use SanAuth\Model\User;
class AuthController extends AbstractActionController
{

    protected $form;

    protected $storage;

    protected $authservice;

    protected $usuarioTable;

    public function getAuthService()
    {
        if (! $this->authservice) {
            $this->authservice = $this->getServiceLocator()->get('AuthService');
        }
        
        return $this->authservice;
    }

    public function getSessionStorage()
    {
        if (! $this->storage) {
            $this->storage = $this->getServiceLocator()->get('SanAuth\Model\MyAuthStorage');
        }
        
        return $this->storage;
    }

    public function getForm()
    {
        if (! $this->form) {
            // $user = new User();
            // $builder = new AnnotationBuilder();
            
            $this->form = new \SanAuth\Form\UserForm(); // $builder->createForm($user);
        }
        
        return $this->form;
    }

    public function loginAction()
    {
        // if already login, redirect to success page
        // if ($this->getAuthService()->hasIdentity()){
        // return $this->redirect()->toRoute('success');
        // }
        $form = $this->getForm();
        
        return array(
            'form' => $form,
            'messages' => $this->flashmessenger()->getMessages()
        );
    }

    public function authenticateAction()
    {
        $form = $this->getForm();
        $redirect = 'login';
        
        $request = $this->getRequest();
        if ($request->isPost()) {
            
//             var_dump($request->getPost('accion'));exit;
            $form->setData($request->getPost());
            if ( $form->isValid()) { //
                       
                // check authentication...
                $nombre = $request->getPost('va_nombre');
                $contrasena = $request->getPost('va_contrasena');
                $this->getAuthService()
                    ->getAdapter()
                    ->setIdentity($nombre)
                    ->setCredential($contrasena);
                
                $result = $this->getAuthService()->authenticate();
                foreach ($result->getMessages() as $message) {
                    // save message temporary into flashmessenger
                    $this->flashmessenger()->addMessage($message);
                }
                
                if ($result->isValid()) {
//                     $redirect = 'success';
                    $accion=$request->getPost('accion');
                    if($accion=='detalleevento'){
                        $redirect = 'evento';
                    }elseif($accion=='detallegrupo'){
                        $redirect = 'grupo';
                    }
                    elseif($accion=='index'){
//                         var_dump($accion);exit;
                        $redirect = 'agregar-grupo';
                    }
                    
                    
                    // check if it has rememberMe :
                    // if ($request->getPost('rememberme') == 1 ) {
                    // $this->getSessionStorage()
                    // ->setRememberMe(1);
                    // //set storage again
                    // $this->getAuthService()->setStorage($this->getSessionStorage());
                    // }
                    // $this->getAuthService()->setStorage(new \Zend\Authentication\Storage\Session('Auth'));
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
       
        return $this->redirect()->toRoute($redirect, array('action'=>'agregargrupo'));//$this->redirect()->toUrl($this->getRequest()->getBaseUrl().$redirect); //
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

    public function changeemailAction()
    {
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
                        ->setSubject('RecuperaciÃ³n de contraseÃ±a');
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

    public function recuperarAction()
    {
        $password = $this->params()->fromQuery('contrasena');
        $form=new UpdatepassForm();
        try {
            $results = $this->getUsuarioTable()->consultarPassword($password);
        } catch (\Exception $e) {
//             echo 'aka es';exit;
            $this->flashMessenger()->addMessage('Este contraseña temporal no existe...');
        }

        if ($results) {
            $request = $this->getRequest();

//             $usuario = new Usuario();
//             $form = new UsuarioForm();
//             $form->setInputFilter($usuario->getInputFilter());
            $form->setData($request->getPost());
            if ($request->isPost()) {
                if($form->isValid())
                {
            
                    $nuevopass = $this->params()->fromPost('va_contrasena');
                    if ($this->getUsuarioTable()->cambiarPassword($nuevopass, $results->in_id)) {
                        // $this->flashmessenger()->addMessage('La contraseña se actualizo correctamente...');
                    } else {
                        // $this->flashmessenger()->addMessage('La contraseña se no se pudo actualizar correctamente...');
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

    public function getUsuarioTable()
    {
        if (! $this->usuarioTable) {
            $sm = $this->getServiceLocator();
            $this->usuarioTable = $sm->get('Usuario\Model\UsuarioTable');
        }
        return $this->usuarioTable;
    }
}