<?php

/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/ZendSkeletonModule for the canonical source repository
 * @copyright Copyright (c) 2005-2012 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Usuario\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\Http\Request;
use Zend\Json\Json;
use Usuario\Model\Usuario;
use SanAuth\Controller\AuthController; 
use Zend\Session\Container;

use Usuario\Model\UsuarioTable;
use Usuario\Form\UsuarioForm;
use Usuario\Form\NotificacionForm;
use Zend\Form\Element;
use Zend\Validator\File\Size;
use Zend\Http\Header\Cookie;
use Zend\Http\Header;
use Zend\Db\Sql\Sql;
use Zend\Mail\Message;



class IndexController extends AbstractActionController {

    protected $usuarioTable;
    static $usuarioTableStatic;
    protected $ruta;
    static $rutaStatic;
    protected $_options;
   protected $storage;
    protected $authservice;

    public function __construct() {
        $this->_options = new \Zend\Config\Config(include APPLICATION_PATH . '/config/autoload/global.php');     
    }

    public function indexAction() {
        
        
//        $ch = curl_init('https://fbcdn-profile-a.akamaihd.net/hprofile-ak-ash2/1116961_1561721285_1332327023_q.jpg');
//        $cuenta = $this->_options->upload->images . '/usuario/cuenta/1561721285.jpg';
//                                $fp = fopen($cuenta, 'wb');
//                                curl_setopt($ch, CURLOPT_FILE, $fp);
//                                curl_setopt($ch, CURLOPT_HEADER, 0);
//                                curl_exec($ch);
//                                curl_close($ch);
//                                fclose($fp);
//                                exit();

    }

    public function grupoparticipoAction() {
        $renderer = $this->serviceLocator->get('Zend\View\Renderer\RendererInterface');
        $renderer->inlineScript()->prependFile($this->_options->host->base . '/js/main.js')
        ->prependFile($this->_options->host->base . '/js/masonry/post-like.js')
        ->prependFile($this->_options->host->base . '/js/masonry/superfish.js')
        ->prependFile($this->_options->host->base . '/js/masonry/prettify.js')
        ->prependFile($this->_options->host->base . '/js/masonry/retina.js')
        ->prependFile($this->_options->host->base . '/js/masonry/jquery.masonry.min.js')
        ->prependFile($this->_options->host->base . '/js/masonry/jquery.infinitescroll.min.js')
        ->prependFile($this->_options->host->base . '/js/masonry/custom.js');
        $categoria = $this->getGrupoTable()->tipoCategoria();
        $this->layout()->categorias = $categoria;
        $id = $this->params()->fromQuery('id');
        $storage = new \Zend\Authentication\Storage\Session('Auth');
        $id = $storage->read()->in_id; // $this->params()->fromQuery('id');
        $valor = $this->headerAction($id);
        $usuariosgrupos = $this->getUsuarioTable()->usuariosgrupos($id);
        if(count($usuariosgrupos)==0)
        {$mensaje= 'Aún no participas en ningún grupo, Que esperas para crear uno';}
//       $categorias = $this->getUsuarioTable()
//                        ->categoriasunicas($id)->toArray();
//        for ($i = 0; $i < count($categorias); $i++) {
//            $otrosgrupos = $this->getUsuarioTable()->grupossimilares($categorias[$i]['idcategoria'], $categorias[$i]['id']);
//        }
        
            $paginator = new \Zend\Paginator\Paginator(new \Zend\Paginator\Adapter\Iterator($usuariosgrupos));
            $paginator->setCurrentPageNumber((int) $this->params()->fromQuery('page', 1));
            $paginator->setItemCountPerPage(12);
        return array(
            'grupo' => $valor,
            'grupospertenece' => $paginator,
            //'otrosgrupos' => $otrosgrupos,
            'mensaje'=>$mensaje
        );
    }

    public function misgruposAction() {
        $renderer = $this->serviceLocator->get('Zend\View\Renderer\RendererInterface');
        $renderer->inlineScript()->prependFile($this->_options->host->base . '/js/main.js')
        ->prependFile($this->_options->host->base . '/js/masonry/post-like.js')
                ->prependFile($this->_options->host->base . '/js/masonry/superfish.js')
                ->prependFile($this->_options->host->base . '/js/masonry/prettify.js')
                ->prependFile($this->_options->host->base . '/js/masonry/retina.js')
                ->prependFile($this->_options->host->base . '/js/masonry/jquery.masonry.min.js')
                ->prependFile($this->_options->host->base . '/js/masonry/jquery.infinitescroll.min.js')
                ->prependFile($this->_options->host->base . '/js/masonry/custom.js');

        $categorias = $this->getGrupoTable()->tipoCategoria();
        $this->layout()->categorias = $categorias;
        $renderer = $this->serviceLocator->get('Zend\View\Renderer\RendererInterface');
        $renderer->inlineScript()->prependFile($this->_options->host->base . '/js/main.js');
        $storage = new \Zend\Authentication\Storage\Session('Auth');
        $id = $storage->read()->in_id;
        $misgrupos = $this->getGrupoTable()->misgrupos($id);
          if(count($misgrupos)==0)
        {$mensaje= 'Aún no has creado ningún grupo, Que esperas para crear uno';}
        $valor = $this->headerAction($id);
         $paginator = new \Zend\Paginator\Paginator(new \Zend\Paginator\Adapter\Iterator($misgrupos));
            $paginator->setCurrentPageNumber((int) $this->params()->fromQuery('page', 1));
            $paginator->setItemCountPerPage(12); 
        return array(
            'grupo' => $valor,
            'misgrupos' => $paginator,
            'mensaje' =>$mensaje
        );
    }
    
  public function eventosparticipoAction()
    {
        $renderer = $this->serviceLocator->get('Zend\View\Renderer\RendererInterface');
        $renderer->inlineScript()
        ->prependFile($this->_options->host->base . '/js/main.js')
        ->prependFile($this->_options->host->base . '/js/masonry/post-like.js')
                ->prependFile($this->_options->host->base . '/js/masonry/superfish.js')
                ->prependFile($this->_options->host->base . '/js/masonry/prettify.js')
                ->prependFile($this->_options->host->base . '/js/masonry/retina.js')
                ->prependFile($this->_options->host->base . '/js/masonry/jquery.masonry.min.js')
                ->prependFile($this->_options->host->base . '/js/masonry/jquery.infinitescroll.min.js')
                ->prependFile($this->_options->host->base . '/js/masonry/custom.js');
        $categorias = $this->getGrupoTable()->tipoCategoria();
        $this->layout()->categorias = $categorias;
        $id = $this->params()->fromQuery('id');
        $storage = new \Zend\Authentication\Storage\Session('Auth');
        $id = $storage->read()->in_id;

         $eventosusuario = $this->getEventoTable()->usuarioseventos($id);
         if(count($eventosusuario)==0)
        {$mensaje= 'Aún no participas en ningún evento, Que esperas para crear uno';}
//         $index=new \Usuario\Controller\IndexController();
        $valor = $this->headerAction($id);
           $paginator = new \Zend\Paginator\Paginator(new \Zend\Paginator\Adapter\Iterator($eventosusuario));
            $paginator->setCurrentPageNumber((int) $this->params()->fromQuery('page', 1));
            $paginator->setItemCountPerPage(12);     
        return array(
            'grupo' => $valor,
            'eventos'=>$paginator,
            'mensaje' =>$mensaje
        );
    }
    
        public function miseventosAction()
    {   
            
         
        $renderer = $this->serviceLocator->get('Zend\View\Renderer\RendererInterface');
        $renderer->inlineScript()
        ->prependFile($this->_options->host->base . '/js/main.js')
        ->prependFile($this->_options->host->base . '/js/masonry/post-like.js')
                ->prependFile($this->_options->host->base . '/js/masonry/superfish.js')
                ->prependFile($this->_options->host->base . '/js/masonry/prettify.js')
                ->prependFile($this->_options->host->base . '/js/masonry/retina.js')
                ->prependFile($this->_options->host->base . '/js/masonry/jquery.masonry.min.js')
                ->prependFile($this->_options->host->base . '/js/masonry/jquery.infinitescroll.min.js')
                ->prependFile($this->_options->host->base . '/js/masonry/custom.js');
        $categorias = $this->getGrupoTable()->tipoCategoria();
        $this->layout()->categorias = $categorias;
        $id = $this->params()->fromQuery('id');
        $storage = new \Zend\Authentication\Storage\Session('Auth');
//           var_dump($storage->read()->va_imagen);exit;
        $id = $storage->read()->in_id;
        $miseventos = $this->getEventoTable()->miseventos($id);
        if(count($miseventos)==0)
        {$mensaje= 'Aún no has creado ningún evento, Que esperas para crear uno';}
        $valor = $this->headerAction($id);
        $paginator = new \Zend\Paginator\Paginator(new \Zend\Paginator\Adapter\Iterator($miseventos));
            $paginator->setCurrentPageNumber((int) $this->params()->fromQuery('page', 1));
            $paginator->setItemCountPerPage(12); 
        
        return array
      (
            'grupo' => $valor,
        'miseventos'=> $paginator,
            'mensaje' =>$mensaje
       );
    }

    public function correo($correo, $usuario, $valor) {
        $message = new Message();
        $message->addTo($correo, $usuario)
                ->setFrom('listadelsabor@innovationssystems.com', 'juntate.pe')
                ->setSubject('Confirmación de Registro en Juntate.pe');
        $bodyHtml = '<!DOCTYPE html><html xmlns="http://www.w3.org/1999/xhtml">
                                               <head>
                                               <meta http-equiv="Content-type" content="text/html;charset=UTF-8"/>
                                               </head>
                                               <body>
                                                    <div style="color: #7D7D7D"><br />
                                                     Hola  <strong style="color:#133088; font-weight: bold;">' . $usuario . '</strong><br />
            Tu cuenta en <a href="' . $this->_options->host->ruta . '">juntate.pe</a> está casi lista para usar. Activa tu cuenta haciendo click en el enlace <br>
            <a href="' . $this->_options->host->ruta . '/auth?token=' . $valor . ' ">' . $this->_options->host->ruta . '/auth?token=' . $valor . '</a><br />
             <img src="'.$this->_options->host->img.'/juntate.png" title="juntate.pe"/>
                                                     </div>
                                               </body>
                                               </html>';
        $bodyPart = new \Zend\Mime\Message();
        $bodyMessage = new \Zend\Mime\Part($bodyHtml);
        $bodyMessage->type = 'text/html';
        $bodyPart->setParts(array(
            $bodyMessage
        ));
        $message->setBody($bodyPart);
        $message->setEncoding('UTF-8');

        $transport = $this->getServiceLocator()->get('mail.transport');
        $transport->send($message);
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
    
    public function coneccion($hola)
    {
        
        
      //  var_dump($hola);exit;
        require './vendor/facebook/facebook.php';
               $facebook = new \Facebook(array(
                 'appId'  => $this->_options->facebook->appId,
                 'secret' => $this->_options->facebook->secret,
                 'cookie' => true ,
                 'scope'  => 'email,publish_stream',
//                  'redirect_uri'=>  'http://dev.juntate.pe/'
               ));
               
     //  var_dump($hola);
            $user = $facebook->getUser();
            if ($user) {
             try {
                   $user_profile = $facebook->api('/me');
                 } 
             catch (FacebookApiException $e) {
                           error_log($e);
                           $user = null; }
                       }
                       if ($user) {
                         $logoutUrl = $facebook->getLogoutUrl();
                         $id_facebook = $user_profile['id'];
                         $name = $user_profile['name']; 
                         $email = $user_profile['email'];
                       } else {
                         $loginUrl = $facebook->getLoginUrl
                      (array('scope'=>'email,publish_stream,read_friendlists',
//                      'redirect_uri' => 'http://dev.juntate.pe/'
                          )); 
                       }
                       $naitik = $facebook->api('/naitik');
                       $storage = new \Zend\Authentication\Storage\Session('Auth');
                       $session=$storage->read();
                       if(!$session){
                       if($user_profile==''){}
                       else
                        {    
                         $correo=$this->getUsuarioTable()->usuariocorreo($email);  
                         if(count($correo)>0)
                            {if($correo[0]['id_facebook']=='')  
                                { $this->getUsuarioTable()->idfacebook($correo[0]['in_id'],$id_facebook,$logoutUrl);
                                 AuthController::sessionfacebook($email,$this->_options->facebook->pass);
                                 return $this->redirect()->toUrl($this->getRequest()->getBaseUrl().'/');
                                }     
                             else
                                {
                                 $this->getUsuarioTable()->idfacebook($correo[0]['in_id'],'',$logoutUrl);
                                  AuthController::sessionfacebook($email,$this->_options->facebook->pass); 
                                  return $this->redirect()->toUrl($this->getRequest()->getBaseUrl().'/');
                                }
                            }
                         else
                          { 
                              $imagen = 'https://graph.facebook.com/'.$user.'/picture';
                              $this->getUsuarioTable()->insertarusuariofacebbok($name,$email,$id_facebook,$imagen,$logoutUrl); 
                              AuthController::sessionfacebook($email,$this->_options->facebook->pass);
                              return $this->redirect()->toUrl($this->getRequest()->getBaseUrl().'/');
                          }                       
                        }
                      }else{  }}
                      
                      
                      
                      
                      
     public function agregarusuarioAction() {
        // AGREGAR CSS       
          
        $renderer = $this->serviceLocator->get('Zend\View\Renderer\RendererInterface');
        $renderer->headLink()->prependStylesheet($this->_options->host->base . '/css/datetimepicker.css');
        $categorias = $this->getGrupoTable()->tipoCategoria();
        $this->layout()->categorias = $categorias;
        // AGREGAR LIBRERIAS JAVASCRIPT EN EL FOOTER
        $renderer->inlineScript()
                ->setScript('if( $("#registro").length){valregistro("#registro");}valUsuario();')
                ->prependFile($this->_options->host->base . '/js/main.js')
                ->prependFile($this->_options->host->base . '/js/map/ju.img.picker.js')
                ->prependFile($this->_options->host->base . '/js/bootstrap-fileupload/bootstrap-fileupload.min.js')
                ->prependFile($this->_options->host->base . '/js/jquery.validate.min.js');

        $adpter = $this->getServiceLocator()->get('Zend\Db\Adapter\Adapter');
        $form = new UsuarioForm();
        $form->get('submit')->setValue('Crear Usuario');
        
        $request = $this->getRequest(); 
         require './vendor/facebook/facebook.php';
               $facebook = new \Facebook(array(
                 'appId'  => $this->_options->facebook->appId,
                 'secret' => $this->_options->facebook->secret,
                 'cookie' => true ,
                 'scope'  => 'email,publish_stream',
                  'redirect_uri'=>  'http://dev.juntate.pe/'
               ));
            $user = $facebook->getUser();
            if ($user) {
             try {
                   $user_profile = $facebook->api('/me');
                 } 
             catch (FacebookApiException $e) {
                           error_log($e);
                           $user = null; }
                       }
                       if ($user) {
                         $logoutUrl = $facebook->getLogoutUrl();
                         $id_facebook = $user_profile['id'];
                         $name = $user_profile['name']; 
                         $email = $user_profile['email'];
                       } else {
                         $loginUrl = $facebook->getLoginUrl
                      (array('scope'=>'email,publish_stream,read_friendlists',
//                      'redirect_uri' => 'http://dev.juntate.pe/'
                          )); 
                       }
                       $naitik = $facebook->api('/naitik');
                       $storage = new \Zend\Authentication\Storage\Session('Auth');
                       $session=$storage->read();
                       if(!$session){
                       if($user_profile==''){}
                       else
                        {    
                         $correo=$this->getUsuarioTable()->usuariocorreo($email);  
                         if(count($correo)>0)
                            {if($correo[0]['id_facebook']=='')  
                                { $this->getUsuarioTable()->idfacebook($correo[0]['in_id'],$id_facebook,$logoutUrl);
                                 AuthController::sessionfacebook($email,$this->_options->facebook->pass);
                                 return $this->redirect()->toUrl($this->getRequest()->getBaseUrl().'/');
                                }     
                             else
                                {
                                 $this->getUsuarioTable()->idfacebook($correo[0]['in_id'],'',$logoutUrl);
                                  AuthController::sessionfacebook($email,$this->_options->facebook->pass); 
                                  return $this->redirect()->toUrl($this->getRequest()->getBaseUrl().'/');
                                }
                            }
                         else
                          { 
                              $imagen = 'https://graph.facebook.com/'.$user.'/picture';
                              $this->getUsuarioTable()->insertarusuariofacebbok($name,$email,$id_facebook,$imagen,$logoutUrl); 
                              AuthController::sessionfacebook($email,$this->_options->facebook->pass);
                              return $this->redirect()->toUrl($this->getRequest()->getBaseUrl().'/');
                          }                       
                        }
                      }else{  }
                      
        if ($request->isPost()) {
            $File = $this->params()->fromFiles('va_foto');
            $nonFile = $this->params()->fromPost('va_nombre');
            if ($File['name'] != '') {
                //   codigo de guardar imagen con apodo
                require './vendor/Classes/Filter/Alnum.php';
                $imf = $File['name'];
                $info = pathinfo($File['name']);
                $valor = uniqid();
                $nom = $nonFile;
                $imf2 = $valor . '.' . $info['extension'];
                $filter = new \Filter_Alnum();
                $filtered = $filter->filter($nom);
                $imagen = $filtered . '-' . $imf2;
            } else {
                $imagen = 'foto-carnet.jpg';
            }

            $data = array_merge_recursive($this->getRequest()
                            ->getPost()
                            ->toArray(), $this->getRequest()
                            ->getFiles()
                            ->toArray());
            $usuario = new Usuario();
            $form->setInputFilter($usuario->getInputFilter());
            $form->setData($data); // $request->getPost()
            if ($form->isValid()) {
                $usuario->exchangeArray($form->getData());
               
                $email = $this->getUsuarioTable()->usuariocorreo($request->getPost('va_email'));
                if (count($email) <= 0) {
                    if ($File['name'] != '') {
                        if ($this->redimensionarFoto($File, $nonFile, $imagen, $id = null)) {
                            $this->getUsuarioTable()->guardarUsuario($usuario, $imagen, md5($usuario->va_nombre));
                            $this->correo($usuario->va_email, $usuario->va_nombre, md5($usuario->va_nombre));

                            return $this->redirect()->toUrl($this->getRequest()->getBaseUrl() . '/registrarse?m=1');
                        } else {
                            echo 'problemas con el redimensionamiento';
                            exit();
                        }
                    } else {
                        $this->getUsuarioTable()->guardarUsuario($usuario, $imagen, md5($usuario->va_nombre));
                        $this->correo($usuario->va_email, $usuario->va_nombre, md5($usuario->va_nombre));

                        return $this->redirect()->toUrl($this->getRequest()->getBaseUrl() . '/registrarse?m=1');
                    }
                } else {

                    $mensaje = 'el correo electrónico ' . $request->getPost('va_email') . ' ya esta asociado a un usuario';
                    // return $this->redirect()->toUrl($this->getRequest()->getBaseUrl().'/usuario/index/agregarusuario');
                }
            } else {
                foreach ($form->getInputFilter()->getInvalidInput() as $error) {
                    print_r($error->getMessages()); // $inputFilter->getInvalidInput()
                }
            }
        }

        return array(
            'form' => $form,
            'mensaje' => $mensaje,
            'user_profile' => $user_profile,
            'user' => $user,
            'logoutUrl'  =>$logoutUrl,
            'loginUrl'  =>$loginUrl,
            'naitik' =>$naitik
            
        );
        // return array();
    }


    public function editarusuarioAction() {
        $storage = new \Zend\Authentication\Storage\Session('Auth');
        $categorias = $this->getGrupoTable()->tipoCategoria();
        $this->layout()->categorias = $categorias;
        $renderer = $this->serviceLocator->get('Zend\View\Renderer\RendererInterface');
        $renderer->inlineScript()
                ->setScript('actualizarDatos();if($("#editarusuario").length){valactualizar("#editarusuario");};')
                ->prependFile($this->_options->host->base . '/js/main.js')
                ->prependFile($this->_options->host->base . '/js/bootstrap-fileupload/bootstrap-fileupload.min.js')
                ->prependFile($this->_options->host->base . '/js/jquery.validate.min.js');

        $id = $storage->read()->in_id; //(int) $this->params()->fromRoute('in_id', 0);
        if (!$id) {
            return $this->redirect()->toRoute('usuario', array(
                        'action' => 'agregarusuario'
                    ));
        }

        try {
            $usuario = $this->getUsuarioTable()->getUsuario($id);
        } catch (\Exception $ex) {
            return $this->redirect()->toRoute('usuario', array(
                        'action' => 'index'
                    ));
        }
        $valor = $this->headerAction($id);
        $adpter = $this->getServiceLocator()->get('Zend\Db\Adapter\Adapter');
        $form = new UsuarioForm($adpter);
        $form->bind($usuario);
        $form->get('submit')->setAttribute('value', 'Editar');

        //formulario para la notificacion
        $formNotif = new NotificacionForm();
        $formNotif->get('submit')->setAttribute('value', 'Editar');
        //populate elementos del check
        $not = $this->getGrupoTable()->getNotifiacionesxUsuario($storage->read()->in_id)->toArray();
        $aux = array();
        foreach ($not as $value) {
            $aux[$value['ta_notificacion_in_id']] = $value['ta_notificacion_in_id'];
            $formNotif->get('tipo_notificacion')->setAttribute('value', $aux);
        }


        $request = $this->getRequest();

        if ($request->isPost()) {
            $File = $this->params()->fromFiles('va_foto');
            $nonFile = $this->params()->fromPost('va_nombre');

            if ($File['name'] != '') {
                require './vendor/Classes/Filter/Alnum.php';
                $imf = $File['name'];
                $info = pathinfo($File['name']);
                $valor = uniqid();
                $nom = $nonFile;
                $imf2 = $valor . '.' . $info['extension'];
                $filter = new \Filter_Alnum();
                $filtered = $filter->filter($nom);
                $imagen = $filtered . '-' . $imf2;
            } else {
                $idusuario = $this->getUsuarioTable()->getUsuario($id);
                $imagen = $idusuario->va_foto;
            }

            $data = array_merge_recursive($this->getRequest()
                            ->getPost()
                            ->toArray(), $this->getRequest()
                            ->getFiles()
                            ->toArray());
            $form->setInputFilter($usuario->getInputFilter2());
            $form->setData($data);
            if ($form->isValid()) {

                if ($this->params()->fromPost('va_contrasena') == '') {
                    $dataa = $this->getUsuarioTable()->getUsuario($id);
                    $pass = $dataa->va_contrasena;
                    $nombre=$this->params()->fromPost('va_nombre');
                    if ($File['name'] != '') {//echo 'mamaya';exit;
                        if ($this->redimensionarFoto($File, $nonFile, $imagen, $id)) {
                 
                            $this->getUsuarioTable()->guardarUsuario($usuario, $imagen, '', $pass);
                            $obj= $storage->read();
                            $obj->va_foto=$imagen;
                            $obj->va_nombre=$nombre;
                            $storage->write($obj);
                            
                            return $this->redirect()->toUrl($this->getRequest()->getBaseUrl() . '/usuario/index/editarusuario?m=1');
                        } else {
                            echo 'problemas con el redimensionamiento';
                            exit();
                        }
                    } else {
                        $this->getUsuarioTable()->guardarUsuario($usuario, $imagen, '', $pass);
                            $obj= $storage->read();
                            $obj->va_foto=$imagen;
                            $obj->va_nombre=$nombre;
                            $storage->write($obj);

                        return $this->redirect()->toUrl($this->getRequest()->getBaseUrl() . '/usuario/index/editarusuario?m=1');
                    }
                } else {

                    if ($File['name'] != '') {//echo 'mamaya';exit;
                        if ($this->redimensionarFoto($File, $nonFile, $imagen, $id)) {
                            $this->getUsuarioTable()->guardarUsuario($usuario, $imagen);
                            return $this->redirect()->toUrl($this->getRequest()->getBaseUrl() . '/usuario/index/editarusuario?m=1');
                        } else {
                            echo 'problemas con el redimensionamiento';
                            exit();
                        }
                    } else {
                        $this->getUsuarioTable()->guardarUsuario($usuario, $imagen);

                        return $this->redirect()->toUrl($this->getRequest()->getBaseUrl() . '/usuario/index/editarusuario?m=1');
                    }
                }
            } else {
//                 var_dump($form->isValid());exit;
                foreach ($form->getInputFilter()->getInvalidInput() as $error) {
                    print_r($error->getMessages());
                }
            }
        }

        return array(
            'in_id' => $id,
            'form' => $form,
            'usuario' => $usuario,
            'valor' => $valor,
            'formnotif' => $formNotif
        );
    }

    public function notificarAction() {
        $storage = new \Zend\Authentication\Storage\Session('Auth');
        $request = $this->getRequest();
        if ($request->isPost()) {
//             $formNotif->setData($request->getPost());
//             if($formNotif->isValid()){
            $data = $request->getPost('tipo_notificacion');
            $this->getGrupoTable()->updateNotificacion($data, $storage->read()->in_id);
            return $this->redirect()->toUrl($this->getRequest()->getBaseUrl() . '/usuario/index/editarusuario');
//             }
        }

        return array();
    }
//    public function getServicio(){
//        $config=$this->getServiceLocator()->get('Config');  
//        self::$rutaStatic=$config['host']['images'];
//    }

    public function headerAction($id) {
        $storage = new \Zend\Authentication\Storage\Session('Auth');
        $nombre = $storage->read()->va_nombre;
        $config = self::$rutaStatic;
        $imagen =$config.'/usuario/cuenta/'.$storage->read()->va_foto;
        $accion=$this->params('action');
         if($accion=='misgrupos'){
           $class='<li class="center-li"><a href=" ' . $ruta . '/cuenta/grupoparticipo "><i class="hh icon-myevent"></i><p>Grupos donde participo</p></a></li>  
            <li class="center-li"><a href=" ' . $ruta . '/cuenta/eventosparticipo "><i class="hh icon-mygroup"> </i><p>Eventos donde participo</p></a></li>
            <li class="center-li"><a href=" ' . $ruta . '/cuenta/miseventos "><i class="hh icon-event"> </i><p>Mis Eventos</p></a></li>
            <li class="center-li"><a href=" ' . $ruta . '/cuenta/misgrupos"  class="activomenu"><i class="hh icon-group"> </i><p>Mis Grupos</p></a></li>
            <li class="center-li"><a href=" ' . $ruta . '/micuenta"><i class="hh icon-cuenta"></i><p>Mi cuenta</p></a></li>';
         }elseif($accion=='miseventos'){
           $class='<li class="center-li"><a href=" ' . $ruta . '/cuenta/grupoparticipo "><i class="hh icon-myevent"></i><p>Grupos donde participo</p></a></li>  
            <li class="center-li"><a href=" ' . $ruta . '/cuenta/eventosparticipo "><i class="hh icon-mygroup"> </i><p>Eventos donde participo</p></a></li>
            <li class="center-li"><a href=" ' . $ruta . '/cuenta/miseventos" class="activomenu"><i class="hh icon-event"> </i><p>Mis Eventos</p></a></li>
            <li class="center-li"><a href=" ' . $ruta . '/cuenta/misgrupos"><i class="hh icon-group"> </i><p>Mis Grupos</p></a></li>
            <li class="center-li"><a href=" ' . $ruta . '/micuenta"><i class="hh icon-cuenta"></i><p>Mi cuenta</p></a></li>';
         
         }elseif($accion=='eventosparticipo'){
             $class='<li class="center-li"><a href=" ' . $ruta . '/cuenta/grupoparticipo "><i class="hh icon-myevent"></i><p>Grupos donde participo</p></a></li>  
            <li class="center-li"><a href=" ' . $ruta . '/cuenta/eventosparticipo"  class="activomenu"><i class="hh icon-mygroup"> </i><p>Eventos donde participo</p></a></li>
            <li class="center-li"><a href=" ' . $ruta . '/cuenta/miseventos"><i class="hh icon-event"> </i><p>Mis Eventos</p></a></li>
            <li class="center-li"><a href=" ' . $ruta . '/cuenta/misgrupos"><i class="hh icon-group"> </i><p>Mis Grupos</p></a></li>
            <li class="center-li"><a href=" ' . $ruta . '/micuenta"><i class="hh icon-cuenta"></i><p>Mi cuenta</p></a></li>';
         
         }elseif($accion=='grupoparticipo'){
            $class='<li class="center-li"><a href=" ' . $ruta . '/cuenta/grupoparticipo"  class="activomenu"><i class="hh icon-myevent"></i><p>Grupos donde participo</p></a></li>  
            <li class="center-li"><a href=" ' . $ruta . '/cuenta/eventosparticipo "><i class="hh icon-mygroup"> </i><p>Eventos donde participo</p></a></li>
            <li class="center-li"><a href=" ' . $ruta . '/cuenta/miseventos"><i class="hh icon-event"> </i><p>Mis Eventos</p></a></li>
            <li class="center-li"><a href=" ' . $ruta . '/cuenta/misgrupos"><i class="hh icon-group"> </i><p>Mis Grupos</p></a></li>
            <li class="center-li"><a href=" ' . $ruta . '/micuenta"><i class="hh icon-cuenta"></i><p>Mi cuenta</p></a></li>';
         
         }elseif($accion=='editarusuario'){
             $class='<li class="center-li"><a href=" ' . $ruta . '/cuenta/grupoparticipo"><i class="hh icon-myevent"></i><p>Grupos donde participo</p></a></li>  
            <li class="center-li"><a href=" ' . $ruta . '/cuenta/eventosparticipo "><i class="hh icon-mygroup"> </i><p>Eventos donde participo</p></a></li>
            <li class="center-li"><a href=" ' . $ruta . '/cuenta/miseventos"><i class="hh icon-event"> </i><p>Mis Eventos</p></a></li>
            <li class="center-li"><a href=" ' . $ruta . '/cuenta/misgrupos"><i class="hh icon-group"> </i><p>Mis Grupos</p></a></li>
            <li class="center-li"><a href=" ' . $ruta . '/micuenta"  class="activomenu"><i class="hh icon-cuenta"></i><p>Mi cuenta</p></a></li>';
         }
        $estados = '<div class="span12 menu-login">
          <img src="'.$imagen.'" alt="" class="img-user"> <span>Bienvenido<br> ' . $nombre . '</span>
          <div class="logincuenta">
          <ul>'.$class.'<li class="center-li"><a href="/auth//logout"><i class="hh icon-salir"></i><p>Cerrar Sesion</p></a></li>
          </ul> 
          </div>                            
        </div>';
        return $estados;
    }

    public function fooAction() {
        // This shows the :controller and :action parameters in default route
        // are working when you browse to /module-specific-root/skeleton/foo
        return array();
    }

    public function getUsuarioTable() {
        if (!$this->usuarioTable) {
            $sm = $this->getServiceLocator();
            $this->usuarioTable = $sm->get('Usuario\Model\UsuarioTable');
            self::$usuarioTableStatic = $this->usuarioTable;
        }
        return $this->usuarioTable;
    }

    public function getGrupoTable() {
        if (!$this->grupoTable) {
            $sm = $this->getServiceLocator();
            $this->grupoTable = $sm->get('Grupo\Model\GrupoTable');
            $config=$sm->get('Config');  
            self::$rutaStatic=$config['host']['images'];
        }
        return $this->grupoTable;
    }
    
        public function getEventoTable()
    {
        if (! $this->eventoTable) {
            $sm = $this->getServiceLocator();
            $this->eventoTable = $sm->get('Grupo\Model\EventoTable');
        }
        return $this->eventoTable;
    }

    private function redimensionarFoto($File, $nonFile, $imagen, $id = null) {//echo $imagen;exit;
        try {

            $anchura = 248;
            $altura = 500; // 143;
            $anchax = 73;
            $altay = 68;
            $comentariosx = 80;
            $comentariosy = 75;
            $perfilx = 27;
            $perfily = 27;
            $cuentax = 70;
            $cuentay = 60;
            $generalx = 270;
            $imf = $File['name'];
            $info = pathinfo($File['name']);
            $tamanio = getimagesize($File['tmp_name']);
            $ancho = $tamanio[0];
            $alto = $tamanio[1];


            if ($id != null) {
                $idusuario = $this->getUsuarioTable()->getUsuario($id);
                $imog = $idusuario->va_foto;
                $eliminar1 = $this->_options->upload->images . '/usuario/general/' . $imog;
                $eliminar2 = $this->_options->upload->images . '/usuario/original/' . $imog;
                $eliminar3 = $this->_options->upload->images . '/usuario/principal/' . $imog;
                $eliminar4 = $this->_options->upload->images . '/usuario/detalle/' . $imog;
                $eliminar5 = $this->_options->upload->images . '/usuario/comentarios/' . $imog;
                $eliminar6 = $this->_options->upload->images . '/usuario/perfil/' . $imog;
                $eliminar7 = $this->_options->upload->images . '/usuario/cuenta/' . $imog;
                unlink($eliminar1);
                unlink($eliminar2);
                unlink($eliminar3);
                unlink($eliminar4);
                unlink($eliminar5);
                unlink($eliminar6);
                unlink($eliminar7);
            }
            if ($ancho > $alto) {
                $altura = (int) ($alto * $anchura / $ancho);
                $anchura = (int) ($ancho * $altura / $alto);
                if ($info['extension'] == 'jpg' or $info['extension'] == 'JPG' or $info['extension'] == 'jpeg' or $info['extension'] == 'png' or $info['extension'] == 'PNG') {
                    $name = $imagen;
                    if ($info['extension'] == 'jpg' or $info['extension'] == 'JPG' or $info['extension'] == 'jpeg') {
                        $viejafoto = imagecreatefromjpeg($File['tmp_name']);
                        $nuevafoto = imagecreatetruecolor($anchura, $altura);
                        $generalfoto = imagecreatetruecolor($generalx, $altura);
                        $detallefoto = imagecreatetruecolor($anchax, $altay);
                        $comentariosfoto = imagecreatetruecolor($comentariosx, $comentariosy);
                        $perfilfoto = imagecreatetruecolor($perfilx, $perfily);
                        $cuentafoto = imagecreatetruecolor($cuentax, $cuentay);
                        imagecopyresized($nuevafoto, $viejafoto, 0, 0, 0, 0, $anchura, $altura, $ancho, $alto);
                        imagecopyresized($generalfoto, $viejafoto, 0, 0, 0, 0, $generalx, $altura, $ancho, $alto);
                        imagecopyresized($detallefoto, $viejafoto, 0, 0, 0, 0, $anchax, $altay, $ancho, $alto);
                        imagecopyresized($comentariosfoto, $viejafoto, 0, 0, 0, 0, $comentariosx, $comentariosy, $ancho, $alto);
                        imagecopyresized($perfilfoto, $viejafoto, 0, 0, 0, 0, $perfilx, $perfily, $ancho, $alto);
                        imagecopyresized($cuentafoto, $viejafoto, 0, 0, 0, 0, $cuentax, $cuentay, $ancho, $alto);
                        $copia = $this->_options->upload->images . '/usuario/principal/' . $name;
                        $origen = $this->_options->upload->images . '/usuario/original/' . $name;
                        $general = $this->_options->upload->images . '/usuario/general/' . $name;
                        $detalle = $this->_options->upload->images . '/usuario/detalle/' . $name;
                        $comentario = $this->_options->upload->images . '/usuario/comentarios/' . $name;
                        $perfil = $this->_options->upload->images . '/usuario/perfil/' . $name;
                        $cuenta = $this->_options->upload->images . '/usuario/cuenta/' . $name;
                        imagejpeg($nuevafoto, $copia);
                        imagejpeg($viejafoto, $origen);
                        imagejpeg($generalfoto, $general);
                        imagejpeg($detallefoto, $detalle);
                        imagejpeg($comentariosfoto, $comentario);
                        imagejpeg($perfilfoto, $perfil);
                        imagejpeg($cuentafoto, $cuenta);
                    } else {
                        $viejafoto = imagecreatefrompng($File['tmp_name']);
                        $nuevafoto = imagecreatetruecolor($anchura, $altura);
                        $generalfoto = imagecreatetruecolor($generalx, $altura);
                        $detallefoto = imagecreatetruecolor($anchax, $altay);
                        $comentariosfoto = imagecreatetruecolor($comentariosx, $comentariosy);
                        $perfilfoto = imagecreatetruecolor($perfilx, $perfily);
                        $cuentafoto = imagecreatetruecolor($cuentax, $cuentay);
                        imagecopyresized($nuevafoto, $viejafoto, 0, 0, 0, 0, $anchura, $altura, $ancho, $alto);
                        imagecopyresized($generalfoto, $viejafoto, 0, 0, 0, 0, $generalx, $altura, $ancho, $alto);
                        imagecopyresized($detallefoto, $viejafoto, 0, 0, 0, 0, $anchax, $altay, $ancho, $alto);
                        imagecopyresized($comentariosfoto, $viejafoto, 0, 0, 0, 0, $comentariosx, $comentariosy, $ancho, $alto);
                        imagecopyresized($perfilfoto, $viejafoto, 0, 0, 0, 0, $perfilx, $perfily, $ancho, $alto);
                        imagecopyresized($cuentafoto, $viejafoto, 0, 0, 0, 0, $cuentax, $cuentay, $ancho, $alto);
                        $copia = $this->_options->upload->images . '/usuario/principal/' . $name;
                        $origen = $this->_options->upload->images . '/usuario/original/' . $name;
                        $general = $this->_options->upload->images . '/usuario/general/' . $name;
                        $detalle = $this->_options->upload->images . '/usuario/detalle/' . $name;
                        $comentario = $this->_options->upload->images . '/usuario/comentarios/' . $name;
                        $perfil = $this->_options->upload->images . '/usuario/perfil/' . $name;
                        $cuenta = $this->_options->upload->images . '/usuario/cuenta/' . $name;
                        imagepng($nuevafoto, $copia);
                        imagepng($viejafoto, $origen);
                        imagepng($generalfoto, $general);
                        imagejpeg($detallefoto, $detalle);
                        imagejpeg($comentariosfoto, $comentario);
                        imagejpeg($perfilfoto, $perfil);
                        imagejpeg($cuentafoto, $cuenta);
                    }
                    return true;
                }
            }
            if ($ancho < $alto) {
                $altura = (int) ($alto * $anchura / $ancho);
                if ($info['extension'] == 'jpg' or $info['extension'] == 'JPG' or $info['extension'] == 'jpeg' or $info['extension'] == 'png' or $info['extension'] == 'PNG') {
                    $name = $imagen;

                    if ($info['extension'] == 'jpg' or $info['extension'] == 'JPG' or $info['extension'] == 'jpeg') {
                        $viejafoto = imagecreatefromjpeg($File['tmp_name']);
                        $nuevafoto = imagecreatetruecolor($anchura, $altura);
                        $generalfoto = imagecreatetruecolor($generalx, $altura);
                        $detallefoto = imagecreatetruecolor($anchax, $altay);
                        $comentariosfoto = imagecreatetruecolor($comentariosx, $comentariosy);
                        $perfilfoto = imagecreatetruecolor($perfilx, $perfily);
                        $cuentafoto = imagecreatetruecolor($cuentax, $cuentay);
                        imagecopyresized($nuevafoto, $viejafoto, 0, 0, 0, 0, $anchura, $altura, $ancho, $alto);
                        imagecopyresized($generalfoto, $viejafoto, 0, 0, 0, 0, $generalx, $altura, $ancho, $alto);
                        imagecopyresized($detallefoto, $viejafoto, 0, 0, 0, 0, $anchax, $altay, $ancho, $alto);
                        imagecopyresized($comentariosfoto, $viejafoto, 0, 0, 0, 0, $comentariosx, $comentariosy, $ancho, $alto);
                        imagecopyresized($perfilfoto, $viejafoto, 0, 0, 0, 0, $perfilx, $perfily, $ancho, $alto);
                        imagecopyresized($cuentafoto, $viejafoto, 0, 0, 0, 0, $cuentax, $cuentay, $ancho, $alto);
                        $copia = $this->_options->upload->images . '/usuario/principal/' . $name;
                        $origen = $this->_options->upload->images . '/usuario/original/' . $name;
                        $general = $this->_options->upload->images . '/usuario/general/' . $name;
                        $detalle = $this->_options->upload->images . '/usuario/detalle/' . $name;
                        $comentario = $this->_options->upload->images . '/usuario/comentarios/' . $name;
                        $perfil = $this->_options->upload->images . '/usuario/perfil/' . $name;
                        $cuenta = $this->_options->upload->images . '/usuario/cuenta/' . $name;
                        imagejpeg($nuevafoto, $copia);
                        imagejpeg($viejafoto, $origen);
                        imagejpeg($generalfoto, $general);
                        imagejpeg($detallefoto, $detalle);
                        imagejpeg($comentariosfoto, $comentario);
                        imagejpeg($perfilfoto, $perfil);
                        imagejpeg($cuentafoto, $cuenta);
                    } else {
                        $viejafoto = imagecreatefrompng($File['tmp_name']);
                        $nuevafoto = imagecreatetruecolor($anchura, $altura);
                        $generalfoto = imagecreatetruecolor($generalx, $altura);
                        $comentariosfoto = imagecreatetruecolor($comentariosx, $comentariosy);
                        $perfilfoto = imagecreatetruecolor($perfilx, $perfily);
                        $cuentafoto = imagecreatetruecolor($cuentax, $cuentay);
                        imagecopyresized($nuevafoto, $viejafoto, 0, 0, 0, 0, $anchura, $altura, $ancho, $alto);
                        imagecopyresized($generalfoto, $viejafoto, 0, 0, 0, 0, $generalx, $altura, $ancho, $alto);
                        imagecopyresized($detallefoto, $viejafoto, 0, 0, 0, 0, $anchax, $altay, $ancho, $alto);
                        imagecopyresized($comentariosfoto, $viejafoto, 0, 0, 0, 0, $comentariosx, $comentariosy, $ancho, $alto);
                        imagecopyresized($perfilfoto, $viejafoto, 0, 0, 0, 0, $perfilx, $perfily, $ancho, $alto);
                        imagecopyresized($cuentafoto, $viejafoto, 0, 0, 0, 0, $cuentax, $cuentay, $ancho, $alto);
                        $copia = $this->_options->upload->images . '/usuario/principal/' . $name;
                        $origen = $this->_options->upload->images . '/usuario/original/' . $name;
                        $general = $this->_options->upload->images . '/usuario/general/' . $name;
                        $detalle = $this->_options->upload->images . '/usuario/detalle/' . $name;
                        $comentario = $this->_options->upload->images . '/usuario/comentarios/' . $name;
                        $perfil = $this->_options->upload->images . '/usuario/perfil/' . $name;
                        $cuenta = $this->_options->upload->images . '/usuario/cuenta/' . $name;
                        imagepng($nuevafoto, $copia);
                        imagepng($viejafoto, $origen);
                        imagepng($generalfoto, $general);
                        imagejpeg($detallefoto, $detalle);
                        imagejpeg($comentariosfoto, $comentario);
                        imagejpeg($perfilfoto, $perfil);
                        imagejpeg($cuentafoto, $cuenta);
                    }

                    return true;
                }
            }

            return true;
        } catch (Exception $e) {
            return false;
        }
    }

}
