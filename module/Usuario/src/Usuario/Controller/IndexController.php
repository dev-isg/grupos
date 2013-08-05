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
use Zend\Form\Element;
use Zend\Validator\File\Size;
use Zend\Http\Header\Cookie;
use Zend\Http\Header;
use Zend\Db\Sql\Sql;
use Zend\Mail\Message;

class IndexController extends AbstractActionController
{
    
    protected $usuarioTable;
    protected $_options;
    public function __construct()
	{
		$this->_options = new \Zend\Config\Config ( include APPLICATION_PATH . '/config/autoload/global.php' );
	}
    
    
    public function indexAction()
    {
        
        
//        return array();
    }
    
      public function grupoparticipoAction()
    {
    return new ViewModel;
    }
    
    public function misgruposAction()
    {
      $valor = $this->headerAction();

      return array('grupo'=>$valor);
      /*return new ViewModel(
        array('grupo'=>$valor);
      );*/
    }
    
    
    public function agregarusuarioAction(){
      //AGREGAR CSS
      $renderer = $this->serviceLocator->get('Zend\View\Renderer\RendererInterface');
      $renderer->headLink()->prependStylesheet($this->_options->host->base .'/css/datetimepicker.css');


      //AGREGAR LIBRERIAS JAVASCRIPT EN EL FOOTER
      $renderer->inlineScript()->setScript('if( $("#registro").length){valregistro("#registro");};')
                              ->prependFile($this->_options->host->base .'/js/main.js')                              
                              ->prependFile($this->_options->host->base .'/js/map/ju.img.picker.js')
                              ->prependFile($this->_options->host->base .'/js/bootstrap-fileupload/bootstrap-fileupload.min.js')
                              ->prependFile($this->_options->host->base .'/js/jquery.validate.min.js');

//        $user_info = $this->getUsuarioTable()->usuariox(1);
//        var_dump($user_info);Exit;
        $adpter = $this->getServiceLocator()->get('Zend\Db\Adapter\Adapter');
        
//        $form = new UsuarioForm($adpter);
        $form = new UsuarioForm();
        $form->get('submit')->setValue('Crear Usuario');
        $request = $this->getRequest();
        
        if ($request->isPost()) {
          $File    = $this->params()->fromFiles('va_foto');
          $nonFile = $this->params()->fromPost('va_nombre');
          
            $data    = array_merge_recursive(
                        $this->getRequest()->getPost()->toArray(),          
                        $this->getRequest()->getFiles()->toArray()
                        ); 
            $usuario = new Usuario();
            $form->setInputFilter($usuario->getInputFilter());
            $form->setData($data);//$request->getPost()
            if ($form->isValid()) {
               
                $usuario->exchangeArray($form->getData());
                if($this->redimensionarFoto($File,$nonFile)){
                    $this->getUsuarioTable()->guardarUsuario($usuario);
                    return $this->redirect()->toRoute('usuario');
                }
                else{
                    echo 'problemas con el redimensionamiento';exit;
                }

            }else{
                    foreach ($form->getInputFilter()->getInvalidInput() as $error) {
                        print_r ($error->getMessages());//$inputFilter->getInvalidInput()
                    }
            }
        }
 
        return array('form'=>$form);
//         return array();
    }

    public function editarusuarioAction(){
      $renderer = $this->serviceLocator->get('Zend\View\Renderer\RendererInterface');
      $renderer->inlineScript()->setScript('actualizarDatos();if($("#actualizar").length){valregistro("#actualizar");};')
                              ->prependFile($this->_options->host->base .'/js/main.js')                              
                              ->prependFile($this->_options->host->base .'/js/bootstrap-fileupload/bootstrap-fileupload.min.js')
                              ->prependFile($this->_options->host->base .'/js/jquery.validate.min.js');

       $id = (int) $this->params()->fromRoute('in_id', 0);    
        if (!$id) {
            return $this->redirect()->toRoute('usuario', array(
                'action' => 'agregarusuario'
            ));
        }
        
        try {
            $usuario = $this->getUsuarioTable()->getUsuario($id);
        }
        catch (\Exception $ex) {
            
            return $this->redirect()->toRoute('usuario', array(
                'action' => 'index'
            ));
        }
        $valor = $this->headerAction();
        $adpter = $this->getServiceLocator()->get('Zend\Db\Adapter\Adapter');
        $form = new UsuarioForm($adpter);
        $form->bind($usuario);
        
//        $var=$this->getGrupoTable()->getNotifiaciones($id)->toArray();
//        $aux = array();
//        foreach($var as $y){
//            $aux[]=$y['ta_notificacion_in_id'];
//        }
//        $form->get('tipo_notificacion')->setValue($aux);
        
        $form->get('submit')->setAttribute('value', 'Editar');
        
        $request = $this->getRequest();
        
        if ($request->isPost()) {
            $File    = $this->params()->fromFiles('va_foto');
            $nonFile = $this->params()->fromPost('va_nombre');
            
            $data    = array_merge_recursive(
            $this->getRequest()->getPost()->toArray(),          
            $this->getRequest()->getFiles()->toArray()
            ); 
            $form->setInputFilter($usuario->getInputFilter());
            $form->setData($data);
//            var_dump($form->setData($data));
            
            if ($form->isValid()) {
                if($this->redimensionarFoto($File,$nonFile)){
                    $this->getUsuarioTable()->guardarUsuario($usuario);
                    return $this->redirect()->toRoute('usuario');
                }
                else{
                    echo 'problemas con el redimensionamiento';exit;
                }

            }else{
//                var_dump($form->isValid());
                    foreach ($form->getInputFilter()->getInvalidInput() as $error) {
                        print_r ($error->getMessages());
                    }
            }
        }

        return array(
            'in_id' => $id,
            'form' => $form,'valor'=>$valor
        );
        
    }
    private function headerAction()
    {
        //$ruta =  $this->host('ruta');
    
       $estados = '<div class="span12 menu-login">
          <img src="http://lorempixel.com/50/50/people/" alt="" class="img-user"> <span>Bienvenido Usuario</span>
          <div class="logincuenta">
          <ul>
            <li><i class="icon-group"> </i> <a href=" '.$ruta .'/usuario/index/grupoparticipo">Grupos donde participo</a></li>
            <li><i class="icon-group"> </i> <a href="#">Eventos donde participo</a></li>
            <li><i class="icon-group"> </i> <a href="#">Mis Eventos</a></li>
            <li><i class="icon-group"> </i> <a href=" '.$ruta .'/usuario/index/misgrupos">Mis Grupos</a></li>
            <li><i class="icon-cuenta"></i> <a href="#" class="activomenu">Mi cuenta</a></li>
            <li><i class="icon-salir"></i><a href="#">Cerrar Sesion</a></li>                   
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
    
      public function getUsuarioTable() {
        if (!$this->usuarioTable) {
            $sm = $this->getServiceLocator();
            $this->usuarioTable = $sm->get('Usuario\Model\UsuarioTable');
        }
        return $this->usuarioTable;
    }
    
    private function redimensionarFoto($File,$nonFile){
    try{
        
              $anchura = 248;
              $altura = 500;//143; 

              $generalx=270;
              $imf =$File['name'];
              $info =  pathinfo($File['name']);
              $tamanio = getimagesize($File['tmp_name']);
              $ancho =$tamanio[0]; 
              $alto =$tamanio[1]; 
//              $altura=$tamanio[1];
              $valor  = uniqid();
              if($ancho>$alto)
              {//echo 'ddd';exit;
                  require './vendor/Classes/Filter/Alnum.php';
                  //$altura =(int)($alto*$anchura/$ancho);    //($alto*$anchura/$ancho); 
                  $altura =(int)($alto*$anchura/$ancho);
                  $anchura =(int)($ancho*$altura/$alto); 
                  if($info['extension']=='jpg' or $info['extension']=='JPG' or $info['extension']=='jpeg' or $info['extension']=='png'
                          or $info['extension']=='PNG')      
                  {   $nom = $nonFile; 
                  $imf2 =  $valor.'.'.$info['extension'];
                  $filter   = new \Filter_Alnum();
                  $filtered = $filter->filter($nom);
                  $name = $filtered.'-'.$imf2;
               
                       if($info['extension']=='jpg'or $info['extension']=='JPG'or $info['extension']=='jpeg'){
                            $viejafoto=  imagecreatefromjpeg($File['tmp_name']);
                            $nuevafoto = imagecreatetruecolor($anchura, $altura);
                            $generalfoto = imagecreatetruecolor($generalx, $altura);
                            imagecopyresized($nuevafoto, $viejafoto, 0, 0, 0, 0, $anchura, $altura, $ancho, $alto);
                            imagecopyresized($generalfoto, $viejafoto, 0, 0, 0, 0, $generalx, $altura, $ancho, $alto);
                            $copia = $this->_options->upload->images . '/usuario/principal/' . $name;
                            $origen = $this->_options->upload->images . '/usuario/original/' . $name;
                            $general=$this->_options->upload->images . '/usuario/general/' . $name;
                                 imagejpeg($nuevafoto,$copia);
                                 imagejpeg($viejafoto,$origen);
                                 imagejpeg($generalfoto,$general);
                       }else{
                            $viejafoto=  imagecreatefrompng($File['tmp_name']);
                           $nuevafoto = imagecreatetruecolor($anchura, $altura);
                           $generalfoto = imagecreatetruecolor($generalx, $altura);
                            imagecopyresized($nuevafoto, $viejafoto, 0, 0, 0, 0, $anchura, $altura, $ancho, $alto);
                            imagecopyresized($generalfoto, $viejafoto, 0, 0, 0, 0, $generalx, $altura, $ancho, $alto);
                            $copia = $this->_options->upload->images . '/usuario/principal/' . $name;
                            $origen = $this->_options->upload->images . '/usuario/original/' . $name;
                            $general=$this->_options->upload->images . '/usuario/general/' . $name;
                                 imagepng($nuevafoto,$copia);
                                 imagepng($viejafoto,$origen);
                                 imagepng($generalfoto,$general);
                       }
                       return true; 
                  }

               }
                   if($ancho<$alto)
              {require './vendor/Classes/Filter/Alnum.php';
                  //$anchura =(int)($ancho*$altura/$alto); 
                   $altura =(int)($alto*$anchura/$ancho);
                  if($info['extension']=='jpg'or $info['extension']=='JPG'or $info['extension']=='jpeg' or $info['extension']=='png'
                          or $info['extension']=='PNG')      
                  {  $nom = $nonFile; 
                  $imf2 =  $valor.'.'.$info['extension'];
                  $filter   = new \Filter_Alnum();
                  $filtered = $filter->filter($nom); 
                   $name = $filtered.'-'.$imf2;
                            
                       if($info['extension']=='jpg'or $info['extension']=='JPG'or $info['extension']=='jpeg'){
                            $viejafoto =  imagecreatefromjpeg($File['tmp_name']);
                            $nuevafoto = imagecreatetruecolor($anchura, $altura);
                            $generalfoto = imagecreatetruecolor($generalx, $altura);
                            imagecopyresized($nuevafoto, $viejafoto, 0, 0, 0, 0, $anchura, $altura, $ancho, $alto);
                            imagecopyresized($generalfoto, $viejafoto, 0, 0, 0, 0, $generalx, $altura, $ancho, $alto);
                            $copia = $this->_options->upload->images . '/usuario/principal/' . $name;
                            $origen = $this->_options->upload->images . '/usuario/original/' . $name;
                            $general=$this->_options->upload->images . '/usuario/general/' . $name;
                                 imagejpeg($nuevafoto,$copia);
                                 imagejpeg($viejafoto,$origen);
                                 imagejpeg($generalfoto,$general);
                       }else{
                            $viejafoto =  imagecreatefrompng($File['tmp_name']);
                           $nuevafoto = imagecreatetruecolor($anchura, $altura);
                           $generalfoto = imagecreatetruecolor($generalx, $altura);
                            imagecopyresized($nuevafoto, $viejafoto, 0, 0, 0, 0, $anchura, $altura, $ancho, $alto);
                            imagecopyresized($generalfoto, $viejafoto, 0, 0, 0, 0, $generalx, $altura, $ancho, $alto);
                            $copia = $this->_options->upload->images . '/usuario/principal/' . $name;
                            $origen = $this->_options->upload->images . '/usuario/original/' . $name;
                            $general=$this->_options->upload->images . '/usuario/general/' . $name;
                                 imagepng($nuevafoto,$copia);
                                 imagepng($viejafoto,$origen);
                                 imagepng($generalfoto,$general);
                       }

                       return true;
 
                  }

               }

        return true;
            
    }catch(Exception $e){
        return false;
    }         
           
       }
    
}
