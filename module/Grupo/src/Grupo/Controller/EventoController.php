<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/ZendSkeletonModule for the canonical source repository
 * @copyright Copyright (c) 2005-2012 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */
namespace Grupo\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\Http\Request;
use Zend\Json\Json;
use Grupo\Model\Evento;
use Grupo\Model\EventoTable;
use Grupo\Form\EventoForm;
use Zend\Form\Element;
use Zend\Validator\File\Size;
use Zend\Http\Header\Cookie;
use Zend\Http\Header;
use Zend\Db\Sql\Sql;
 use Usuario\Controller\IndexController;
class EventoController extends AbstractActionController
{

    protected $eventoTable;

    protected $usuarioTable;

    protected $_options;

    public function __construct()
    {
        $this->_options = new \Zend\Config\Config(include APPLICATION_PATH . '/config/autoload/global.php');
    }

    public function indexAction()
    {
        // $listagrupos=$this->getGrupoTable()->fetchAll();
        return array();
    }

    public function agregareventoAction()
    {
        $idgrupo = $this->params()->fromRoute('in_id');
        $storage = new \Zend\Authentication\Storage\Session('Auth');
        if (! $storage) {
            return $this->redirect()->toRoute('grupo');
        }
        // print_r($storage->read()->in_id);exit;
        
        // AGREGAR LIBRERIAS JAVASCRIPT EN EL FOOTER
        $renderer = $this->serviceLocator->get('Zend\View\Renderer\RendererInterface');
        $renderer->headLink()->prependStylesheet($this->_options->host->base . '/css/datetimepicker.css');
        $renderer->inlineScript()
            ->setScript('crearevento();')
            ->prependFile($this->_options->host->base . '/js/main.js')
            ->prependFile($this->_options->host->base . '/js/map/locale-es.js')
            ->prependFile($this->_options->host->base . '/js/map/ju.google.map.js')
            ->prependFile('https://maps.googleapis.com/maps/api/js?key=AIzaSyA2jF4dWlKJiuZ0z4MpaLL_IsjLqCs9Fhk&sensor=true')
            ->prependFile($this->_options->host->base . '/js/map/ju.img.picker.js')
            ->prependFile($this->_options->host->base . '/js/bootstrap-datetimepicker.js')
            ->prependFile($this->_options->host->base . '/js/mockjax/jquery.mockjax.js')
            ->prependFile($this->_options->host->base . '/js/bootstrap-fileupload/bootstrap-fileupload.min.js')
            ->prependFile($this->_options->host->base . '/js/jquery.validate.min.js')
            ->prependFile($this->_options->host->base . '/js/ckeditor/ckeditor.js');
        // $local = (int) $this->params()->fromQuery('id');
        $adpter = $this->getServiceLocator()->get('Zend\Db\Adapter\Adapter');
        $form = new EventoForm($adpter);
        $form->get('submit')->setValue('Crear Evento');
        // var_dump($storage->read());
        $form->get('ta_usuario_in_id')->setValue($storage->read()->in_id);
        // $form->get('ta_grupo_in_id')->setValue($storage->read()->in_id);
        $request = $this->getRequest();
        
        if ($request->isPost()) {
            $File = $this->params()->fromFiles('va_imagen');
            $nonFile = $this->params()->fromPost('va_nombre');
            $data = array_merge_recursive($this->getRequest()
                ->getPost()
                ->toArray(), $this->getRequest()
                ->getFiles()
                ->toArray());
            //
            $evento = new Evento();
            $form->setInputFilter($evento->getInputFilter());
            $form->setData($data); // $request->getPost()
            
            if ($form->isValid()) {
                // var_dump($data);Exit;
                $evento->exchangeArray($form->getData());
                if ($this->redimensionarImagen($File, $nonFile)) {
                    $this->getEventoTable()->guardarEvento($evento,$idgrupo);
                    
                    return $this->redirect()->toRoute('grupo');
                } else {
                    echo 'problemas con el redimensionamiento';
                    exit();
                }
            } else {
                
                foreach ($form->getInputFilter()->getInvalidInput() as $error) {
                    print_r($error->getMessages());
                }
                // return $this->redirect()->toRoute('grupo/index/agregargrupo');
            }
        }
        return array(
            'formevento' => $form
        );
    }

    public function editareventoAction()
    {
        $idgrupo = $this->params()->fromRoute('in_id');
        // AGREGAR LIBRERIAS JAVASCRIPT EN EL FOOTER
        $renderer = $this->serviceLocator->get('Zend\View\Renderer\RendererInterface');
        $renderer->headLink()->prependStylesheet($this->_options->host->base . '/css/datetimepicker.css');
        $renderer->inlineScript()
        ->setScript('crearevento();')
        ->prependFile($this->_options->host->base . '/js/main.js')
        ->prependFile($this->_options->host->base . '/js/map/locale-es.js')
        ->prependFile($this->_options->host->base . '/js/map/ju.google.map.js')
        ->prependFile('https://maps.googleapis.com/maps/api/js?key=AIzaSyA2jF4dWlKJiuZ0z4MpaLL_IsjLqCs9Fhk&sensor=true')
        ->prependFile($this->_options->host->base . '/js/map/ju.img.picker.js')
        ->prependFile($this->_options->host->base . '/js/bootstrap-datetimepicker.js')
        ->prependFile($this->_options->host->base . '/js/mockjax/jquery.mockjax.js')
        ->prependFile($this->_options->host->base . '/js/bootstrap-fileupload/bootstrap-fileupload.min.js')
        ->prependFile($this->_options->host->base . '/js/jquery.validate.min.js')
        ->prependFile($this->_options->host->base . '/js/ckeditor/ckeditor.js');
        
        $id = (int) $this->params()->fromRoute('in_id', 0);
        if (! $id) {
            return $this->redirect()->toRoute('grupo', array(
                'action' => 'agregargrupo'
            ));
        }
        
        try {
            $evento = $this->getEventoTable()->getEvento($id);
        } catch (\Exception $ex) {
            
            return $this->redirect()->toRoute('grupo', array(
                'action' => 'index'
            ));
        }
        
        $adpter = $this->getServiceLocator()->get('Zend\Db\Adapter\Adapter');
        $form = new EventoForm($adpter);
        $form->bind($evento);
//         $value=$form->get('va_fecha')->getValue();
//         var_dump($value);
//         $value2=$form->get('va_fecha')->setAttribute('value', '');
//         var_dump($value2);exit;
        $form->get('submit')->setAttribute('value', 'Editar');
        
        $request = $this->getRequest();
        
        if ($request->isPost()) {
            
            $data = array_merge_recursive($this->getRequest()
                ->getPost()
                ->toArray(), $this->getRequest()
                ->getFiles()
                ->toArray());
            $form->setInputFilter($evento->getInputFilter());
            $form->setData($data);
            
            if ($form->isValid()) {
                $this->$this->getEventoTable()->guardarEvento($evento,$idgrupo);
                return $this->redirect()->toRoute('grupo');
            } else {
                foreach ($form->getInputFilter()->getInvalidInput() as $error) {
                    print_r($error->getMessages());
                }
            }
        }
        
        return array(
            'in_id' => $id,
            'formevento' => $form
        );
    }

    public function eliminareventoAction()
    {}

    public function uploadAction()
    {}

    
    

    public function miseventosAction()
    {
        $id = $this->params()->fromQuery('id');
        $miseventos = $this->getEventoTable()->miseventos($id);
        $valor = IndexController::headerAction($id);
        
        return array
      (
            'grupo' => $valor,
        'miseventos'=> $miseventos,
       );
    }

    public function misgruposAction()
    {
        return new ViewModel();
    }

    public function eventosparticipoAction()
    {
        $id = $this->params()->fromQuery('id');
         $eventosusuario = $this->getEventoTable()->usuarioseventos($id);
   
        $valor = IndexController::headerAction($id);
        
        return array(
            'grupo' => $valor,
            'eventos'=>$eventosusuario
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

    public function getGrupoTable()
    {
        if (! $this->grupoTable) {
            $sm = $this->getServiceLocator();
            $this->grupoTable = $sm->get('Grupo\Model\GrupoTable');
        }
        return $this->grupoTable;
    }

    public function detalleeventoAction(){
      $renderer = $this->serviceLocator->get('Zend\View\Renderer\RendererInterface');
      $id= $this->params()->fromQuery('id');
      $evento=$this->getEventoTable()->Evento($id);
      $id_grupo=$evento[0]['id_grupo'];
      $grupo=$this->getEventoTable()->grupoid($id_grupo);
      $eventospasados=$this->getEventoTable()->eventospasados($id_grupo);
      $eventosfuturos=$this->getEventoTable()->eventosfuturos($id_grupo);
      $usuarios=$this->getEventoTable()->usuariosevento($id);
      $comentarios=$this->getEventoTable()->comentariosevento($id);
      $renderer->inlineScript()->setScript('$(document).ready(function(){$("#map_canvas").juGoogleMap({marker:{lat:'.$evento[0]['va_latitud'].',lng:'.$evento[0]['va_longitud'].',address:"'.$evento[0]['va_direccion'].'",addressRef:"'.$evento[0]['va_referencia'].'"}});});')
                            ->prependFile($this->_options->host->base .'/js/map/locale-es.js')
                            ->prependFile($this->_options->host->base .'/js/map/ju.google.map.js')
                            ->prependFile('https://maps.googleapis.com/maps/api/js?key=AIzaSyA2jF4dWlKJiuZ0z4MpaLL_IsjLqCs9Fhk&sensor=true')
                            ->prependFile($this->_options->host->base .'/js/map/ju.img.picker.js');
      return array('eventos'=>$evento,
                     'grupo'=>$grupo,
            'eventosfuturos'=>$eventosfuturos,
            'eventospasados'=>$eventospasados,
                  'usuarios'=>$usuarios,
               'comentarios'=>$comentarios);
      }
     
     
    public function fooAction()
    {
        // This shows the :controller and :action parameters in default route
        // are working when you browse to /module-specific-root/skeleton/foo
        return array();
    }

    public function getEventoTable()
    {
        if (! $this->eventoTable) {
            $sm = $this->getServiceLocator();
            $this->eventoTable = $sm->get('Grupo\Model\EventoTable');
        }
        return $this->eventoTable;
    }

    private function redimensionarImagen($File, $nonFile)
    {
        try {
            
            $anchura = 248;
            $altura = 500; // 143;
            
            $generalx = 270;
            $imf = $File['name'];
            $info = pathinfo($File['name']);
            $tamanio = getimagesize($File['tmp_name']);
            $ancho = $tamanio[0];
            $alto = $tamanio[1];
            // $altura=$tamanio[1];
            $valor = uniqid();
            if ($ancho > $alto) { // echo 'ddd';exit;
                require './vendor/Classes/Filter/Alnum.php';
                // $altura =(int)($alto*$anchura/$ancho); //($alto*$anchura/$ancho);
                $altura = (int) ($alto * $anchura / $ancho);
                $anchura = (int) ($ancho * $altura / $alto);
                if ($info['extension'] == 'jpg' or $info['extension'] == 'JPG' or $info['extension'] == 'jpeg' or $info['extension'] == 'png' or $info['extension'] == 'PNG') {
                    $nom = $nonFile;
                    $imf2 = $valor . '.' . $info['extension'];
                    $filter = new \Filter_Alnum();
                    $filtered = $filter->filter($nom);
                    $name = $filtered . '-' . $imf2;
                    
                    if ($info['extension'] == 'jpg' or $info['extension'] == 'JPG' or $info['extension'] == 'jpeg') {
                        $viejaimagen = imagecreatefromjpeg($File['tmp_name']);
                        $nuevaimagen = imagecreatetruecolor($anchura, $altura);
                        $generalimagen = imagecreatetruecolor($generalx, $altura);
                        imagecopyresized($nuevaimagen, $viejaimagen, 0, 0, 0, 0, $anchura, $altura, $ancho, $alto);
                        imagecopyresized($generalimagen, $viejaimagen, 0, 0, 0, 0, $generalx, $altura, $ancho, $alto);
                        $copia = $this->_options->upload->images . '/eventos/principal/' . $name;
                        $origen = $this->_options->upload->images . '/eventos/original/' . $name;
                        $general = $this->_options->upload->images . '/eventos/general/' . $name;
                        imagejpeg($nuevaimagen, $copia);
                        imagejpeg($viejaimagen, $origen);
                        imagejpeg($generalimagen, $general);
                    } else {
                        $viejaimagen = imagecreatefrompng($File['tmp_name']);
                        $nuevaimagen = imagecreatetruecolor($anchura, $altura);
                        $generalimagen = imagecreatetruecolor($generalx, $altura);
                        imagecopyresized($nuevaimagen, $viejaimagen, 0, 0, 0, 0, $anchura, $altura, $ancho, $alto);
                        imagecopyresized($generalimagen, $viejaimagen, 0, 0, 0, 0, $generalx, $altura, $ancho, $alto);
                        $copia = $this->_options->upload->images . '/eventos/principal/' . $name;
                        $origen = $this->_options->upload->images . '/eventos/original/' . $name;
                        $general = $this->_options->upload->images . '/eventos/general/' . $name;
                        imagepng($nuevaimagen, $copia);
                        imagepng($viejaimagen, $origen);
                        imagepng($generalimagen, $general);
                    }
                    return true;
                }
            }
            if ($ancho < $alto) {
                require './vendor/Classes/Filter/Alnum.php';
                // $anchura =(int)($ancho*$altura/$alto);
                $altura = (int) ($alto * $anchura / $ancho);
                if ($info['extension'] == 'jpg' or $info['extension'] == 'JPG' or $info['extension'] == 'jpeg' or $info['extension'] == 'png' or $info['extension'] == 'PNG') {
                    $nom = $nonFile;
                    $imf2 = $valor . '.' . $info['extension'];
                    $filter = new \Filter_Alnum();
                    $filtered = $filter->filter($nom);
                    $name = $filtered . '-' . $imf2;
                    
                    if ($info['extension'] == 'jpg' or $info['extension'] == 'JPG' or $info['extension'] == 'jpeg') {
                        $viejaimagen = imagecreatefromjpeg($File['tmp_name']);
                        $nuevaimagen = imagecreatetruecolor($anchura, $altura);
                        $generalimagen = imagecreatetruecolor($generalx, $altura);
                        imagecopyresized($nuevaimagen, $viejaimagen, 0, 0, 0, 0, $anchura, $altura, $ancho, $alto);
                        imagecopyresized($generalimagen, $viejaimagen, 0, 0, 0, 0, $generalx, $altura, $ancho, $alto);
                        $copia = $this->_options->upload->images . '/eventos/principal/' . $name;
                        $origen = $this->_options->upload->images . '/eventos/original/' . $name;
                        $general = $this->_options->upload->images . '/eventos/general/' . $name;
                        imagejpeg($nuevaimagen, $copia);
                        imagejpeg($viejaimagen, $origen);
                        imagejpeg($generalimagen, $general);
                    } else {
                        $viejaimagen = imagecreatefrompng($File['tmp_name']);
                        $nuevaimagen = imagecreatetruecolor($anchura, $altura);
                        $generalimagen = imagecreatetruecolor($generalx, $altura);
                        imagecopyresized($nuevaimagen, $viejaimagen, 0, 0, 0, 0, $anchura, $altura, $ancho, $alto);
                        imagecopyresized($generalimagen, $viejaimagen, 0, 0, 0, 0, $generalx, $altura, $ancho, $alto);
                        $copia = $this->_options->upload->images . '/eventos/principal/' . $name;
                        $origen = $this->_options->upload->images . '/eventos/original/' . $name;
                        $general = $this->_options->upload->images . '/eventos/general/' . $name;
                        imagepng($nuevaimagen, $copia);
                        imagepng($viejaimagen, $origen);
                        imagepng($generalimagen, $general);
                    }
                    
                    return true;
                }
            }
            
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }
}
