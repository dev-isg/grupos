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
use Usuario\Model\UsuarioTable;
use Usuario\Form\UsuarioForm;
use Usuario\Form\NotificacionForm;
use Zend\Form\Element;
use Zend\Validator\File\Size;
use Zend\Http\Header\Cookie;
use Zend\Http\Header;
use Zend\Db\Sql\Sql;
use Zend\Mail\Message;
class IndexController extends AbstractActionController
{

    protected $usuarioTable;

    static $usuarioTableStatic;

    protected $_options;

    public function __construct()
    {
        $this->_options = new \Zend\Config\Config(include APPLICATION_PATH . '/config/autoload/global.php');
    }

    public function indexAction()
    {
        
        // return array();
    }

    public function grupoparticipoAction()
    {
        $renderer = $this->serviceLocator->get('Zend\View\Renderer\RendererInterface');
        $renderer->inlineScript()->prependFile($this->_options->host->base . '/js/main.js');
        $categoria = $this->getGrupoTable()->tipoCategoria();
        $this->layout()->categorias = $categoria;
        $id = $this->params()->fromQuery('id');
        $storage = new \Zend\Authentication\Storage\Session('Auth');
        $id = $storage->read()->in_id; // $this->params()->fromQuery('id');
        $valor = $this->headerAction($id);
        $usuariosgrupos = $this->getUsuarioTable()->usuariosgrupos($id);
        $categorias = $this->getUsuarioTable()
            ->categoriasunicas($id)->toArray();
        for ($i = 0; $i < count($categorias); $i ++) {
            $otrosgrupos = $this->getUsuarioTable()->grupossimilares($categorias[$i]['idcategoria'], $categorias[$i]['id']);
        }
             
        return array(
            'grupo' => $valor,
            'grupospertenece' => $usuariosgrupos,
            'otrosgrupos' => $otrosgrupos,
        );
    }

    public function misgruposAction()
    {
        $renderer = $this->serviceLocator->get('Zend\View\Renderer\RendererInterface');
        $renderer->inlineScript()->prependFile($this->_options->host->base . '/js/main.js');
        
        $categorias = $this->getGrupoTable()->tipoCategoria();
        $this->layout()->categorias = $categorias;
        $renderer = $this->serviceLocator->get('Zend\View\Renderer\RendererInterface');
        $renderer->inlineScript()->prependFile($this->_options->host->base . '/js/main.js');
        $storage = new \Zend\Authentication\Storage\Session('Auth');
        $id = $storage->read()->in_id;
        $misgrupos = $this->getGrupoTable()->misgrupos($id);
        $valor = $this->headerAction($id);
        
        return array(
            'grupo' => $valor,
            'misgrupos'=>$misgrupos,
        );
    }

     public function correo($correo,$usuario,$valor)
                
       {
        $message = new Message();
        $message->addTo($correo, $usuario)
        ->setFrom('listadelsabor@innovationssystems.com', 'juntate.pe')
        ->setSubject('Confirmación de Registro en Juntate.pe');
         $bodyHtml= '<!DOCTYPE html><html xmlns="http://www.w3.org/1999/xhtml">
                                               <head>
                                               <meta http-equiv="Content-type" content="text/html;charset=UTF-8"/>
                                               </head>
                                               <body>
                                                    <div style="color: #7D7D7D"><br />
                                                     Hola  <strong style="color:#133088; font-weight: bold;">' . $usuario . '</strong><br />
         Tu cuenta en <a href="'.$this->_options->host->ruta.'">juntate.pe</a> está casi lista para usar. Activa tu cuenta haciendo click en el enlace <br>
         <a href="'.$this->_options->host->ruta.'/auth?token='.$valor.' ">'.$this->_options->host->ruta.'/auth?token='.$valor.'</a>
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
    public function agregarusuarioAction()
    {
        // AGREGAR CSS
    
        $renderer = $this->serviceLocator->get('Zend\View\Renderer\RendererInterface');
        $renderer->headLink()->prependStylesheet($this->_options->host->base . '/css/datetimepicker.css');
        $categorias = $this->getGrupoTable()->tipoCategoria();
        $this->layout()->categorias = $categorias;
        // AGREGAR LIBRERIAS JAVASCRIPT EN EL FOOTER
        $renderer->inlineScript()
            ->setScript('if( $("#registro").length){valregistro("#registro");};')
            ->prependFile($this->_options->host->base . '/js/main.js')
            ->prependFile($this->_options->host->base . '/js/map/ju.img.picker.js')
            ->prependFile($this->_options->host->base . '/js/bootstrap-fileupload/bootstrap-fileupload.min.js')
            ->prependFile($this->_options->host->base . '/js/jquery.validate.min.js');
        
        // $user_info = $this->getUsuarioTable()->usuariox(1);
        // var_dump($user_info);Exit;
        $adpter = $this->getServiceLocator()->get('Zend\Db\Adapter\Adapter');
        
        // $form = new UsuarioForm($adpter);
        $form = new UsuarioForm();
        $form->get('submit')->setValue('Crear Usuario');
        $request = $this->getRequest();
        
        if ($request->isPost()) {
            $File = $this->params()->fromFiles('va_foto');
            $nonFile = $this->params()->fromPost('va_nombre');
         if($File['name']!='')
         {
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
             
         }else{$imagen = 'foto-carnet.jpg'; }

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
               if( count($email)<=0)
               { 
                   if($File['name']!='')
                       { 
                   if ($this->redimensionarFoto($File, $nonFile, $imagen, $id=null)) {
                 $this->getUsuarioTable()->guardarUsuario($usuario, $imagen,md5($nom));
                   $this->correo($usuario->va_email, $usuario->va_nombre,md5($nom));
                   $mensaje ='Tu cuenta está casi lista para usuar. Solo tienes que activar tu correo para activarla';
                 } else {
                    echo 'problemas con el redimensionamiento';
                    exit();
                }
                
                }else{ $this->getUsuarioTable()->guardarUsuario($usuario, $imagen,md5($nom));
                   $this->correo($usuario->va_email, $usuario->va_nombre,md5($nom));
                   $mensaje ='Tu cuenta está casi lista para usuar. Solo tienes que activar tu correo para activarla';
                   }  
                }
                else  { 
                    
               $mensaje ='el correo electrónico '.$request->getPost('va_email').' ya esta asociado a un usuario';     
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
            'mensaje'=>$mensaje
        );
        // return array();
    }

    public function editarusuarioAction()
    {
        $storage = new \Zend\Authentication\Storage\Session('Auth');
        $categorias = $this->getGrupoTable()->tipoCategoria();
        $this->layout()->categorias = $categorias;
        $renderer = $this->serviceLocator->get('Zend\View\Renderer\RendererInterface');
        $renderer->inlineScript()
            ->setScript('actualizarDatos();if($("#actualizar").length){valregistro("#actualizar");};')
            ->prependFile($this->_options->host->base . '/js/main.js')
            ->prependFile($this->_options->host->base . '/js/bootstrap-fileupload/bootstrap-fileupload.min.js')
            ->prependFile($this->_options->host->base . '/js/jquery.validate.min.js');
        
        $id = $storage->read()->in_id;//(int) $this->params()->fromRoute('in_id', 0);
        if (! $id) {
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
        $formNotif=new NotificacionForm();
        $formNotif->get('submit')->setAttribute('value', 'Editar');
        //populate elementos del check
        $not=$this->getGrupoTable()->getNotifiacionesxUsuario($storage->read()->in_id)->toArray();
        $aux = array();
        foreach($not as $value){
            $aux[$value['ta_notificacion_in_id']]=$value['ta_notificacion_in_id'];
            $formNotif->get('tipo_notificacion')->setAttribute('value', $aux);
        }


        $request = $this->getRequest();
        
        if ($request->isPost()) {
            $File = $this->params()->fromFiles('va_foto');
            $nonFile = $this->params()->fromPost('va_nombre'); 
            //var_dump($File);exit;
            
            
             if($File['name']!='')
         {
            require './vendor/Classes/Filter/Alnum.php';
            $imf = $File['name'];
            $info = pathinfo($File['name']);
            $valor = uniqid();
            $nom = $nonFile;
            $imf2 = $valor . '.' . $info['extension'];
            $filter = new \Filter_Alnum();
            $filtered = $filter->filter($nom);
            $imagen = $filtered . '-' . $imf2;}
            else{$idusuario = $this->getUsuarioTable()->getUsuario($id);
                $imagen = $idusuario->va_foto;}

            $data = array_merge_recursive($this->getRequest()
                ->getPost()
                ->toArray(), $this->getRequest()
                ->getFiles()
                ->toArray());
            $form->setInputFilter($usuario->getInputFilter2());
            $form->setData($data);
            // $notificacion = $this->params()->fromPost('tipo_notificacion', 0);
//             var_dump($data);
//            exit;
            
            if ($form->isValid()) {
                  if($File['name']!=''){//echo 'mamaya';exit;
                if ($this->redimensionarFoto($File, $nonFile, $imagen, $id)) {
                    $this->getUsuarioTable()->guardarUsuario($usuario, $imagen);
                     $mensaje ='datos actualizados correctamente';
                   
                } else {
                    echo 'problemas con el redimensionamiento';
                    exit();}
         }else{   $this->getUsuarioTable()->guardarUsuario($usuario, $imagen);
               $mensaje ='datos actualizados correctamente';}
                
                
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
            'mensaje'=>$mensaje,
            'usuario' => $usuario,
            'valor' => $valor,
            'formnotif'=>$formNotif
        );
    }
    
    public function notificarAction(){
        $storage = new \Zend\Authentication\Storage\Session('Auth');
        $request=$this->getRequest();
        if($request->isPost()){
//             $formNotif->setData($request->getPost());
//             if($formNotif->isValid()){
                $data=$request->getPost('tipo_notificacion');
                $this->getGrupoTable()->updateNotificacion($data,$storage->read()->in_id); 
                return $this->redirect()->toUrl($this->getRequest()->getBaseUrl().'/usuario/index/editarusuario');
//             }
        }
        
        return array();
    }

    public static function headerAction($id)
    {
        $storage = new \Zend\Authentication\Storage\Session('Auth');
        $nombre = $storage->read()->va_nombre;
        $estados = '<div class="span12 menu-login">
          <img src="http://lorempixel.com/50/50/people/" alt="" class="img-user"> <span>Bienvenido ' . $nombre . '</span>
          <div class="logincuenta">
          <ul>
            <li class="center-li"><a href=" '.$ruta . '/usuario/index/grupoparticipo "><i class="hh icon-myevent"></i><p>Grupos donde participo</p></a></li>  
            <li class="center-li"><a href=" '.$ruta . '/grupo/evento/eventosparticipo "><i class="hh icon-mygroup"> </i><p>Eventos donde participo</p></a></li>
            <li class="center-li"><a href=" '.$ruta . '/grupo/evento/miseventos "><i class="hh icon-event"> </i><p>Mis Eventos</p></a></li>
            <li class="center-li"><a href=" '.$ruta . '/usuario/index/misgrupos "><i class="hh icon-group"> </i><p>Mis Grupos</p></a></li>
            <li class="center-li"><a href=" '.$ruta . '/usuario/index/editarusuario" class="activomenu"><i class="hh icon-cuenta"></i><p>Mi cuenta</p></a></li>

            <li class="center-li"><a href="#"><i class="hh icon-salir"></i><p>Cerrar Sesion</p></a></li>
          </ul> 
          </div>                            
        </div>';
        return $estados;
    }

    public function fooAction()
    {
        // This shows the :controller and :action parameters in default route
        // are working when you browse to /module-specific-root/skeleton/foo
        return array();
    }

    public function getUsuarioTable()
    {
        if (! $this->usuarioTable) {
            $sm = $this->getServiceLocator();
            $this->usuarioTable = $sm->get('Usuario\Model\UsuarioTable');
            self::$usuarioTableStatic=$this->usuarioTable;
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

    private function redimensionarFoto($File, $nonFile, $imagen, $id=null)
    {//echo $imagen;exit;
        try {
            
            $anchura = 248;
            $altura = 500; // 143;
            
            $generalx = 270;
            $imf = $File['name'];
            $info = pathinfo($File['name']);
            $tamanio = getimagesize($File['tmp_name']);
            $ancho = $tamanio[0];
            $alto = $tamanio[1];
            
            
            if ($id != null){
                $idusuario = $this->getUsuarioTable()->getUsuario($id);
                $imog = $idusuario->va_foto;
                
                $eliminar1 = $this->_options->upload->images . '/usuario/general/' . $imog;
//                print_r($eliminar1);
//                exit;
                $eliminar2 = $this->_options->upload->images . '/usuario/original/' . $imog;
                $eliminar3 = $this->_options->upload->images . '/usuario/principal/' . $imog;
                
                  unlink($eliminar1);
                  unlink($eliminar2);
                  unlink($eliminar3);
                
            }
                
            
            
//            print_r($imagen);
//            exit;
            // $altura=$tamanio[1];
//            $valor = uniqid();
            if ($ancho > $alto) { // echo 'ddd';exit;
                
                
//                require './vendor/Classes/Filter/Alnum.php';
                // $altura =(int)($alto*$anchura/$ancho); //($alto*$anchura/$ancho);
                $altura = (int) ($alto * $anchura / $ancho);
                $anchura = (int) ($ancho * $altura / $alto);
                if ($info['extension'] == 'jpg' or $info['extension'] == 'JPG' or $info['extension'] == 'jpeg' or $info['extension'] == 'png' or $info['extension'] == 'PNG') {
//
                    $name = $imagen;
//                    print_r($name);
//                    exit;
                    
                    
                    // $contenido = new \Zend\Session\Container('contenido');
                    
                    if ($info['extension'] == 'jpg' or $info['extension'] == 'JPG' or $info['extension'] == 'jpeg') {
                        $viejafoto = imagecreatefromjpeg($File['tmp_name']);
                        $nuevafoto = imagecreatetruecolor($anchura, $altura);
                        $generalfoto = imagecreatetruecolor($generalx, $altura);
                        imagecopyresized($nuevafoto, $viejafoto, 0, 0, 0, 0, $anchura, $altura, $ancho, $alto);
                        imagecopyresized($generalfoto, $viejafoto, 0, 0, 0, 0, $generalx, $altura, $ancho, $alto);
                        $copia = $this->_options->upload->images . '/usuario/principal/' . $name;
                        $origen = $this->_options->upload->images . '/usuario/original/' . $name;
                        $general = $this->_options->upload->images . '/usuario/general/' . $name;
                        imagejpeg($nuevafoto, $copia);
                        imagejpeg($viejafoto, $origen);
                        imagejpeg($generalfoto, $general);
                    } else {
                        $viejafoto = imagecreatefrompng($File['tmp_name']);
                        $nuevafoto = imagecreatetruecolor($anchura, $altura);
                        $generalfoto = imagecreatetruecolor($generalx, $altura);
                        imagecopyresized($nuevafoto, $viejafoto, 0, 0, 0, 0, $anchura, $altura, $ancho, $alto);
                        imagecopyresized($generalfoto, $viejafoto, 0, 0, 0, 0, $generalx, $altura, $ancho, $alto);
                        $copia = $this->_options->upload->images . '/usuario/principal/' . $name;
                        $origen = $this->_options->upload->images . '/usuario/original/' . $name;
                        $general = $this->_options->upload->images . '/usuario/general/' . $name;
                        imagepng($nuevafoto, $copia);
                        imagepng($viejafoto, $origen);
                        imagepng($generalfoto, $general);
                    }
                    return true;
                }
            }
            if ($ancho < $alto) {//echo 'maa';exit;
//                require './vendor/Classes/Filter/Alnum.php';
// $anchura =(int)($ancho*$altura/$alto);
                $altura = (int) ($alto * $anchura / $ancho);
                if ($info['extension'] == 'jpg' or $info['extension'] == 'JPG' or $info['extension'] == 'jpeg' or $info['extension'] == 'png' or $info['extension'] == 'PNG') {
//                    $nom = $nonFile;
//                    $imf2 = $valor . '.' . $info['extension'];
//                    $filter = new \Filter_Alnum();
//                    $filtered = $filter->filter($nom);
//                    $name = $filtered . '-' . $imf2;
                    $name = $imagen;
                    
                    if ($info['extension'] == 'jpg' or $info['extension'] == 'JPG' or $info['extension'] == 'jpeg') {
                        $viejafoto = imagecreatefromjpeg($File['tmp_name']);
                        $nuevafoto = imagecreatetruecolor($anchura, $altura);
                        $generalfoto = imagecreatetruecolor($generalx, $altura);
                        imagecopyresized($nuevafoto, $viejafoto, 0, 0, 0, 0, $anchura, $altura, $ancho, $alto);
                        imagecopyresized($generalfoto, $viejafoto, 0, 0, 0, 0, $generalx, $altura, $ancho, $alto);
                        $copia = $this->_options->upload->images . '/usuario/principal/' . $name;
                        $origen = $this->_options->upload->images . '/usuario/original/' . $name;
                        $general = $this->_options->upload->images . '/usuario/general/' . $name;
                        imagejpeg($nuevafoto, $copia);
                        imagejpeg($viejafoto, $origen);
                        imagejpeg($generalfoto, $general);
                    } else {
                        $viejafoto = imagecreatefrompng($File['tmp_name']);
                        $nuevafoto = imagecreatetruecolor($anchura, $altura);
                        $generalfoto = imagecreatetruecolor($generalx, $altura);
                        imagecopyresized($nuevafoto, $viejafoto, 0, 0, 0, 0, $anchura, $altura, $ancho, $alto);
                        imagecopyresized($generalfoto, $viejafoto, 0, 0, 0, 0, $generalx, $altura, $ancho, $alto);
                        $copia = $this->_options->upload->images . '/usuario/principal/' . $name;
                        $origen = $this->_options->upload->images . '/usuario/original/' . $name;
                        $general = $this->_options->upload->images . '/usuario/general/' . $name;
                        imagepng($nuevafoto, $copia);
                        imagepng($viejafoto, $origen);
                        imagepng($generalfoto, $general);
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
