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
use Zend\Mail\Message;
use Zend\View\Model\JsonModel;
use Grupo\Form\ComentarioForm;
//use Grupo\Controller\IndexController;

use Usuario\Controller\IndexController as metodo;

class EventoController extends AbstractActionController
{

    protected $eventoTable;

    protected $usuarioTable;
    
    protected $grupoTable;

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
        $flashMessenger = $this->flashMessenger();
        if ($flashMessenger->hasMessages()) {
            $mensajes = $flashMessenger->getMessages();
        }

        $categorias = $this->getGrupoTable()->tipoCategoria();
        $this->layout()->categorias = $categorias;
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
            ->setScript('crearevento();cargarMapa();cargarFecha();CKEDITOR.replace ("editor1");')
            ->prependFile($this->_options->host->base . '/js/main.js')
            /*->prependFile($this->_options->host->base . '/js/jquery.ui.addresspicker.js')*/
            ->prependFile($this->_options->host->base . '/js/map/locale-es.js')
            ->prependFile($this->_options->host->base . '/js/map/ju.google.map.js')
            ->prependFile('https://maps.googleapis.com/maps/api/js?v=3.exp&sensor=false')
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
            
            require './vendor/Classes/Filter/Alnum.php';
            $imf = $File['name'];
            $info = pathinfo($File['name']);
            $valor = uniqid();
            $nom = $nonFile;
            $imf2 = $valor . '.' . $info['extension'];
            $filter = new \Filter_Alnum();
            $filtered = $filter->filter($nom);
            $imagen = $filtered . '-' . $imf2;
            $data = array_merge_recursive($this->getRequest()
                ->getPost()
                ->toArray(), $this->getRequest()
                ->getFiles()
                ->toArray());
            //
            $evento = new Evento();
            $form->setInputFilter($evento->getInputFilter());
            $form->setData($data); 
            if ($form->isValid()) {
                $evento->exchangeArray($form->getData());
   
                if ($this->redimensionarImagen($File, $nonFile,$imagen)) {
                 $idevento =   $this->getEventoTable()->guardarEvento($evento, $idgrupo,$imagen,$storage->read()->in_id);
                    return $this->redirect()->toRoute('evento',array('in_id'=>$idevento));
                } else 
                    {
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
            'formevento' => $form,
            'idgrupo'=>$idgrupo,
            'mensajes'=>$mensajes
        );
    }

    public function editareventoAction()
    {
//        $idgrupo = $this->params()->fromRoute('in_id');
        // AGREGAR LIBRERIAS JAVASCRIPT EN EL FOOTER
        $renderer = $this->serviceLocator->get('Zend\View\Renderer\RendererInterface');
        $categorias = $this->getGrupoTable()->tipoCategoria();
        $this->layout()->categorias = $categorias;
        $renderer->headLink()->prependStylesheet($this->_options->host->base . '/css/datetimepicker.css');
        $renderer->headLink()->prependStylesheet($this->_options->host->base . '/css/themes/base/jquery.ui.all.css');
        $renderer->inlineScript()
            ->setScript('crearevento();cargarMapa();cargarFecha();CKEDITOR.replace("editor1");')
            ->prependFile($this->_options->host->base . '/js/main.js')
            ->prependFile($this->_options->host->base . '/js/map/locale-es.js')
            ->prependFile($this->_options->host->base . '/js/map/ju.google.map.js')
            ->prependFile('https://maps.googleapis.com/maps/api/js?v=3.exp&sensor=false')
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
        
            $fecha_esp=preg_replace('/\s+/',' ', $evento->va_fecha);
            $fecha=date('d F Y - H:i', strtotime($fecha_esp));
            $evento->va_fecha=$fecha;
            
        $adpter = $this->getServiceLocator()->get('Zend\Db\Adapter\Adapter');
        $form = new EventoForm($adpter);
        $evento->va_tipo=($evento->va_tipo=='publico')?1:2;
        $form->bind($evento);

        $form->get('submit')->setAttribute('value', 'Editar');
        $imagen=$this->_options->host->images.'/eventos/general/'.$evento->va_imagen;       
        $request = $this->getRequest();
        
                    
        if ($request->isPost()) {
            $File = $this->params()->fromFiles('va_imagen');
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
                $imagen = $evento->va_imagen;
            }
            
            $data = array_merge_recursive($this->getRequest()
                ->getPost()
                ->toArray(), $this->getRequest()
                ->getFiles()
                ->toArray());
            $form->setInputFilter($evento->getInputFilter());
            $form->setData($data);
            
            if ($form->isValid()) {
                if ($this->redimensionarImagen($File, $nonFile,$imagen)) {
                $this->getEventoTable()->guardarEvento($evento, $idgrupo,$imagen);
                $this->flashMessenger()->addMessage('Evento editado correctamente');
                 return $this->redirect()->toRoute('evento',array('in_id'=>$id));
//                return $this->redirect()->toRoute('grupo');
                 } else {
                    echo 'problemas con el redimensionamiento';
                    exit();
                }
            } else {
                foreach ($form->getInputFilter()->getInvalidInput() as $error) {
                    print_r($error->getMessages());
                }
            }
        }
        
        return array(
            'in_id' => $id,
            'formevento' => $form,
            'idgrupo'=>$idgrupo,
            'latitud'=>$evento->va_latitud,
            'longitud'=>$evento->va_longitud,
            'imagen'=>$imagen
        );
    }

    public function eliminareventoAction()
    {}

    public function uploadAction()
    {}

    
    // public function comentariosAction(){
    // $storage = new \Zend\Authentication\Storage\Session('Auth');
    // if (! $storage) {
    // return $this->redirect()->toRoute('grupo');
    // }
    // $idevento = $this->params()->fromQuery('id');
    // // print_r($storage->read()->in_id);exit;
    // $request=$this->getRequest();
    // if($request->isPost()){
    // $form->setData($request->getPost());
    // if ($form->isValid()) {
    // $comentarios=$this->getEventoTable()->guardarComentario($form->getData(),$storage->read()->in_id,$idevento);
    // return $this->redirect()->toUrl($this->getRequest()->getBaseUrl());
    // }
    // }
    // }

    public function misgruposAction()
    {
        return new ViewModel();
    }

//AKA EVENTO PARTICIPO

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

    public function detalleeventoAction()
    {

        $form = new ComentarioForm();
        
        $categorias = $this->getGrupoTable()->tipoCategoria();
        $this->layout()->categorias = $categorias;
        $id = $this->params()->fromRoute('in_id');
        
        $storage = new \Zend\Authentication\Storage\Session('Auth');
        $session=$storage->read();
        
        $evento = $this->getEventoTable()->Evento($id);

        if($evento){
            if($evento[0]['va_tipo']=='privado'){
                   $eventofind = $this->getEventoTable()->getEventoUsuario($id, $session->in_id);
                  
                   if ($eventofind) {
                       if ($eventofind->tipo == 'privado' && $eventofind->va_estado =='activo') {
                           $tipo = true;
                       } else {
                           $tipo = false;      
                       }

                   }else{
                       $tipo = false;
                   } 
            }else{
                $tipo=true;

            }
        }

        $id_grupo = $evento[0]['id_grupo'];
        $grupo = $this->getEventoTable()->grupoid($id_grupo);
        
        $grupoestado=$this->getEventoTable()->getGrupoUsuario($id_grupo,$session->in_id)->va_estado;

        
        $eventospasados = $this->getEventoTable()->eventospasados($id_grupo);
        $eventosfuturos = $this->getEventoTable()->eventosfuturos($id_grupo);
        $usuarios = $this->getEventoTable()->usuariosevento($id);
        
        $comentarios = $this->getEventoTable()->comentariosevento($id);
        
        $renderer = $this->serviceLocator->get('Zend\View\Renderer\RendererInterface');

        $renderer->inlineScript()
            ->setScript('$(document).ready(function(){$(".inlineEventoDet").colorbox({inline:true, width:"500px"});});$(document).ready(function(){$("#map_canvas").juGoogleMap({marker:{lat:' . $evento[0]['va_latitud'] . ',lng:' . $evento[0]['va_longitud'] . ',address:"' . $evento[0]['va_direccion'] . '",addressRef:"' . $evento[0]['va_referencia'] . '"}});});$(document).ready(function(){$(".inlineEventoDet").colorbox({inline:true, width:"500px"});valUsuario();});')
            ->prependFile($this->_options->host->base . '/js/main.js')
            ->prependFile($this->_options->host->base . '/js/map/locale-es.js')
            ->prependFile($this->_options->host->base . '/js/map/ju.google.map.js')
            ->prependFile('https://maps.googleapis.com/maps/api/js?key=AIzaSyA2jF4dWlKJiuZ0z4MpaLL_IsjLqCs9Fhk&sensor=true')
            ->prependFile($this->_options->host->base . '/js/jquery.validate.min.js')
            ->prependFile($this->_options->host->base . '/js/map/ju.img.picker.js');
       

        if (!isset($session)) {
        $face = new \Grupo\Controller\IndexController();
        $facebook = $face->facebook();
        $this->layout()->login = $facebook['loginUrl'];
        $this->layout()->user = $facebook['user']; }
//        $grupocompr=$this->getEventoTable()-> getGrupoUsuario($id_grupo,$session->in_id);
      
        if ($session) {
            $participa=$this->getEventoTable()->compruebarUsuarioxEvento($session->in_id,$id);
            $activo=$participa->va_estado=='activo'?true:false;
        }
        
        $request = $this->getRequest();
        if ($request->isPost()) {
            $form->setData($request->getPost());
            if ($form->isValid()) {
                $this->getEventoTable()->guardarComentario($form->getData(), $storage->read()->in_id, $id);
                return $this->redirect()->toUrl('/evento/' . $id);
            }
        }
        

       
        $paginator = new \Zend\Paginator\Paginator(new \Zend\Paginator\Adapter\Iterator($comentarios));
        $paginator->setCurrentPageNumber((int)$this->params()->fromQuery('page', 1));
        $paginator->setItemCountPerPage(10);
        
        $flashMessenger = $this->flashMessenger();
        if ($flashMessenger->hasMessages()) {
            $mensajes = $flashMessenger->getMessages();
        }

        return array(
            'eventos' => $evento,
            'grupo' => $grupo,
            'eventosfuturos' => $eventosfuturos,
            'eventospasados' => $eventospasados,
            'usuarios' => $usuarios,
            
            'comentarios' => $comentarios,
            'comentarioform' => $form, 
            
            'idevento' => $id,
            'session'=>$session,      
            
//            'grupocomprueba'=>$activo,//$grupocompr,  
            
            'participa'=>$activo,
            'mensajes'=>$mensajes,
            'privado'=>$tipo,
            'grupoestado'=>$grupoestado
//            'tipoprivado'=>$tipoprivado,
//            'tipopublico'=>$tipopublico
        )
        ;
    }
    
    public function comentariosAction() {
      
        $view=new ViewModel();
        $view->setTerminal(true);
        
        $form = new ComentarioForm();
        $id = $this->params()->fromPost('in_id');
        $descript['va_descripcion']=$this->params()->fromPost('va_descripcion');
        $storage = new \Zend\Authentication\Storage\Session('Auth');
        $session = $storage->read();
        $evento = $this->getEventoTable()->Evento($id);
        $id_grupo = $evento[0]['id_grupo'];

        $grupocompr = $this->getEventoTable()->getGrupoUsuario($id_grupo, $session->in_id);
        $comentarios = $this->getEventoTable()->comentariosevento($id);
        $grupoestado=$this->getEventoTable()->getGrupoUsuario($id_grupo,$session->in_id)->va_estado;
         
         if($this->getEventoTable()->guardarComentario($descript,$storage->read()->in_id, $id)){
           $userestado=$this->getEventoTable()->usuariosevento($id, $storage->read()->in_id)->current();
           setlocale(LC_TIME, "es_ES.UTF-8"); 
           $arruser['id']=$storage->read()->in_id;
           foreach($userestado as $key=>$value){
               if($key=='va_fecha'){  
                    $fecha=str_replace("/", "-",$value);
                    $date = strtotime($fecha);     
                   $arruser[$key]='Se unio el '.date("d", $date).' de '.date("F", $date).' del '.date("Y",$date);//                   $arruser[$key]='Se unio el '.date("d", $date).' de '.strftime("%B", $date).' del '.date("Y",$date);//

               }else{
                   $arruser[$key]=$value;
               }
           }
           $result =new JsonModel(array(
                        'descripcion' =>$descript,
                        'userestado'=>$arruser
                    ));
           $view->addChild($result); 
         }
            

        $paginator = new \Zend\Paginator\Paginator(new \Zend\Paginator\Adapter\Iterator($comentarios));
        $paginator->setCurrentPageNumber((int) $this->params()->fromQuery('page', 1));
        $paginator->setItemCountPerPage(10);
        

               
       $view->setVariables(array(
            'idevento' => $id,
            'comentarios' => $comentarios,
            'comentarioform' => $form,
            'grupocomprueba' => $grupocompr,
           'grupoestado'=>$grupoestado,
            'result'=>$result
        ));
       
        return $view;
    }
    
     public function usereventoAction(){
            $request=$this->getRequest();
        $id=$this->params()->fromPost('idevento');
        if($request->isPost()){
         $usuarios = $this->getEventoTable()->usuariosevento($id);
     }
         $result = new JsonModel(array('usuarios'=>$usuarios->toArray()));
         return $result;
     }
    
    
    public function unirAction(){
        $storage = new \Zend\Authentication\Storage\Session('Auth');
        if (! $storage) {
            return $this->redirect()->toRoute('grupo');
        }
           
        $iduser = $storage->read()->in_id; 
        $idevent = $this->params()->fromQuery('idE');
        $unir = $this->params()->fromQuery('act');
        $iduserevent=$this->getEventoTable()->getEvento($idevent)->ta_usuario_in_id;
        $grupoestado=$this->getEventoTable()->getGrupoUsuario($id_grupo,$session->in_id)->va_estado;
         
       $usuariosesion = $this->getGrupoTable()->getNotifiacionesxUsuario($iduser)->toArray();
       $usuariocrear= $this->getGrupoTable()->getNotifiacionesxUsuario($iduserevent)->toArray();
       $arr=array();
        foreach ($usuariosesion as $value) {
            $arr[]=$value['ta_notificacion_in_id'];
        }
            if (in_array(1,$arr)) {
                $correoe = true;
            }
            if (in_array(2,$arr)) {
                $correos = true;
            }
            $arrc=array();
        foreach ($usuariocrear as $values) {
            $arrc[]=$values['ta_notificacion_in_id'];
        }
             if (in_array(1,$arrc)) {
                $correoec = true;
            }
            if (in_array(2,$arrc)) {
                $correosc = true;
            }
            
        if ($unir == 1) {
            if ($this->getEventoTable()->unirseEvento($idevent, $iduser)) {
                if($grupoestado=='activo'){
                if($correoe){
                $user_info['nom_event'] = $this->getEventoTable()->getEvento($idevent)->va_nombre;
                $bodyHtml = '<!DOCTYPE html><html xmlns="http://www.w3.org/1999/xhtml">
                                               <head>
                                               <meta http-equiv="Content-type" content="text/html;charset=UTF-8"/>
                                               </head>
                                               <body>
                                                    <div style="color: #7D7D7D"><br />
                                                     Hola '.ucwords($storage->read()->va_nombre).',
                                                     Usted se ha unido al evento <strong style="color:#133088; font-weight: bold;">' . utf8_decode($user_info['nom_event']) . '</strong><br />
                                                     Si desea mas información del evento dar <a href="'.$this->_options->host->base.'/evento/'.$idevent.'">Clic Aquí</a> <br />Equipo Juntate.pe     
                                                     </div>
                                                     <img src="'.$this->_options->host->base.'/img/juntate.png" title="juntate.pe"/>
                                               </body>
                                               </html>';
                
                $this->mensaje($storage->read()->va_email, $bodyHtml, 'Se ha unido al evento');
                }
                if($correoec){
                $usuario = $this->getEventoTable()->eventoxUsuario($idevent) ->toArray();
                $bodyHtmlAdmin= '<!DOCTYPE html><html xmlns="http://www.w3.org/1999/xhtml">
                                               <head>
                                               <meta http-equiv="Content-type" content="text/html;charset=UTF-8"/>
                                               </head>
                                               <body>
                                                    <div style="color: #7D7D7D"><br />
                                                     El siguiente usuario se ha unido a tu evento <strong style="color:#133088; font-weight: bold;">' . utf8_decode($storage->read()->va_nombre) . '</strong><br />
                
                                                     </div>
                                               </body>
                                               </html>';
                if ($usuario) {
                    $this->mensaje($usuario[0]['va_email'], $bodyHtmlAdmin, 'Se unieron a tu evento');
                }
                }
                            }
                $activo=1;
                $userestado=$this->getEventoTable()->usuariosevento($idevent, $iduser);//getEventoUsuario($idevent, $iduser);

            }  
        }elseif ($unir==0){
            if ($this->getEventoTable()->retiraEvento($idevent, $iduser)) {
                if($grupoestado=='activo'){
                if($correos){
                $user_info['nom_event'] = $this->getEventoTable()->getEvento($idevent)->va_nombre;
                $bodyHtml = '<!DOCTYPE html><html xmlns="http://www.w3.org/1999/xhtml">
                                               <head>
                                               <meta http-equiv="Content-type" content="text/html;charset=UTF-8"/>
                                               </head>
                                               <body>
                                                    <div style="color: #7D7D7D"><br />
                                                    Hola '.ucwords($storage->read()->va_nombre).',
                                                    Usted ha abandonado el evento <strong style="color:#133088; font-weight: bold;">' . utf8_decode($user_info['nom_event']) . '</strong><br />
                                                    Si desea tener información de otros eventos puede buscarlos en <a href="'.$this->_options->host->base.'">Juntate.pe</a> <br />
                                                    Equipo Juntate.pe
                                                     </div>
                                                     <img src="'.$this->_options->host->img.'/juntate.png" title="juntate.pe"/>
                                               </body>
                                               </html>';
                
                $this->mensaje($storage->read()->va_email, $bodyHtml, 'Has abandonado un evento');
                }
                if($correosc){
                $usuario = $this->getEventoTable()
                ->eventoxUsuario($idevent)
                ->toArray();
                $bodyHtmlAdmin= '<!DOCTYPE html><html xmlns="http://www.w3.org/1999/xhtml">
                                               <head>
                                               <meta http-equiv="Content-type" content="text/html;charset=UTF-8"/>
                                               </head>
                                               <body>
                                                    <div style="color: #7D7D7D"><br />
                                                     El siguiente usuario ha dejado tu evento <strong style="color:#133088; font-weight: bold;">' . utf8_decode($storage->read()->va_nombre) . '</strong><br />
                
                                                     </div>
                                               </body>
                                               </html>';
                if ($usuario) {
                    $this->mensaje($usuario[0]['va_email'], $bodyHtmlAdmin, 'Abandonaron tu evento');
                }
                }
            }
                    }
            $activo=0;
            $userestado=$this->getEventoTable()->usuariosevento($idevent, $iduser);//getEventoUsuario($idevent, $iduser);

        }
           $userestado=$userestado->current();
           
           setlocale(LC_TIME, "es_ES.UTF-8"); 
           $arruser['id']=$iduser;
           foreach($userestado as $key=>$value){
               if($key=='va_fecha'){  
                    $fecha=str_replace("/", "-",$value);
                    $date = strtotime($fecha);     
                   $arruser[$key]='Se unio el '.date("d", $date).' de '.date("F", $date).' del '.date("Y",$date);//                   $arruser[$key]='Se unio el '.date("d", $date).' de '.strftime("%B", $date).' del '.date("Y",$date);//

               }else{
                   $arruser[$key]=$value;
               }
           }
        $result = new JsonModel(array(
            'estado' =>$activo,
            'userestado'=>$arruser
        ));
        return $result;
    }
    
//     public function eventouserAction(){
//         $id=$this->params()->fromQuery('id');
//         $usuarios = $this->getEventoTable()->usuariosevento($id);
// //         var_dump($usuarios->toArray());Exit;
//         $result = new JsonModel(
//            $usuarios->toArray()
//         );
//         return $result;
        
//     }
    
    
    public function mensaje($mail,$bodyHtml,$subject){
        $message = new Message();
        $message->addTo($mail, $nombre)
        ->setFrom('listadelsabor@innovationssystems.com', 'listadelsabor.com')
        ->setSubject($subject);
        // ->setBody($bodyHtml);
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
    
    

    private function redimensionarImagen($File, $nonFile,$imagen)
    {
        try {
            
            $anchura = 248;
            $altura = 500; // 143;
            $generalx = 270;
            
                $detallex=550;
                $detalley=600;
   
            $imf = $File['name'];
            $info = pathinfo($File['name']);
            $tamanio = getimagesize($File['tmp_name']);
            $ancho = $tamanio[0];
            $alto = $tamanio[1];
            // $altura=$tamanio[1];
          //  $valor = uniqid();
            $name=$imagen;
            if ($ancho > $alto) { 
                $altura = (int) ($alto * $anchura / $ancho);
                $anchura = (int) ($ancho * $altura / $alto);
                $alturadetalleevento = (int) ($alto * $detallex / $ancho);
               if($alturadetalleevento>600){$detalley=600;}
               else{$detalley=$alturadetalleevento;}           
                if ($info['extension'] == 'jpg' or $info['extension'] == 'JPG' or $info['extension'] == 'jpeg' or $info['extension'] == 'png' or $info['extension'] == 'PNG') {   
                    if ($info['extension'] == 'jpg' or $info['extension'] == 'JPG' or $info['extension'] == 'jpeg') {
                        $viejaimagen = imagecreatefromjpeg($File['tmp_name']);
                        $nuevaimagen = imagecreatetruecolor($anchura, $altura);
                        $generalimagen = imagecreatetruecolor($generalx, $altura);
                        $detalleimagen = imagecreatetruecolor($detallex, $detalley);
                        imagecopyresized($nuevaimagen, $viejaimagen, 0, 0, 0, 0, $anchura, $altura, $ancho, $alto);
                        imagecopyresized($generalimagen, $viejaimagen, 0, 0, 0, 0, $generalx, $altura, $ancho, $alto);
                     imagecopyresized($detalleimagen, $viejaimagen, 0, 0, 0, 0, $detallex, $detalley, $ancho, $alto);
                        $copia = $this->_options->upload->images . '/eventos/principal/' . $name;
                        $origen = $this->_options->upload->images . '/eventos/original/' . $name;
                        $general = $this->_options->upload->images . '/eventos/general/' . $name;
                        $detalle = $this->_options->upload->images . '/eventos/detalle/' . $name;
                        imagejpeg($nuevaimagen, $copia);
                        imagejpeg($viejaimagen, $origen);
                        imagejpeg($generalimagen, $general);
                        imagejpeg($detalleimagen, $detalle);
                    } else {
                        $viejaimagen = imagecreatefrompng($File['tmp_name']);
                        $nuevaimagen = imagecreatetruecolor($anchura, $altura);
                        $generalimagen = imagecreatetruecolor($generalx, $altura);
                        $detalleimagen = imagecreatetruecolor($detallex, $detalley);
                        imagecopyresized($nuevaimagen, $viejaimagen, 0, 0, 0, 0, $anchura, $altura, $ancho, $alto);
                        imagecopyresized($generalimagen, $viejaimagen, 0, 0, 0, 0, $generalx, $altura, $ancho, $alto);
                        imagecopyresized($detalleimagen, $viejaimagen, 0, 0, 0, 0, $detallex, $detalley, $ancho, $alto);
                        $copia = $this->_options->upload->images . '/eventos/principal/' . $name;
                        $origen = $this->_options->upload->images . '/eventos/original/' . $name;
                        $general = $this->_options->upload->images . '/eventos/general/' . $name;
                         $detalle = $this->_options->upload->images . '/eventos/detalle/' . $name;
                        imagepng($nuevaimagen, $copia);
                        imagepng($viejaimagen, $origen);
                        imagepng($generalimagen, $general);
                        imagejpeg($detalleimagen, $detalle);
                    }
                    return true;
                }
            }
            if ($ancho < $alto) {
                
                
              $anchodetalleevento = (int) ($ancho * $detalley / $alto);
               if($anchodetalleevento>550){$detallex=550;}
               else{$detallex = $anchodetalleevento; }
                $altura = (int) ($alto * $anchura / $ancho);
                if ($info['extension'] == 'jpg' or $info['extension'] == 'JPG' or $info['extension'] == 'jpeg' or $info['extension'] == 'png' or $info['extension'] == 'PNG') {   
                    if ($info['extension'] == 'jpg' or $info['extension'] == 'JPG' or $info['extension'] == 'jpeg') {
                        $viejaimagen = imagecreatefromjpeg($File['tmp_name']);
                        $nuevaimagen = imagecreatetruecolor($anchura, $altura);
                        $generalimagen = imagecreatetruecolor($generalx, $altura);
                         $detalleimagen = imagecreatetruecolor($detallex, $detalley);
                        imagecopyresized($nuevaimagen, $viejaimagen, 0, 0, 0, 0, $anchura, $altura, $ancho, $alto);
                        imagecopyresized($generalimagen, $viejaimagen, 0, 0, 0, 0, $generalx, $altura, $ancho, $alto);
                        imagecopyresized($detalleimagen, $viejaimagen, 0, 0, 0, 0, $detallex, $detalley, $ancho, $alto);
                        $copia = $this->_options->upload->images . '/eventos/principal/' . $name;
                        $origen = $this->_options->upload->images . '/eventos/original/' . $name;
                        $general = $this->_options->upload->images . '/eventos/general/' . $name;
                        $detalle = $this->_options->upload->images . '/eventos/detalle/' . $name;
                        imagejpeg($nuevaimagen, $copia);
                        imagejpeg($viejaimagen, $origen);
                        imagejpeg($generalimagen, $general);
                        imagejpeg($detalleimagen, $detalle);
                    } else {
                        $viejaimagen = imagecreatefrompng($File['tmp_name']);
                        $nuevaimagen = imagecreatetruecolor($anchura, $altura);
                        $generalimagen = imagecreatetruecolor($generalx, $altura);
                        $detalleimagen = imagecreatetruecolor($detallex, $detalley);
                        imagecopyresized($nuevaimagen, $viejaimagen, 0, 0, 0, 0, $anchura, $altura, $ancho, $alto);
                        imagecopyresized($generalimagen, $viejaimagen, 0, 0, 0, 0, $generalx, $altura, $ancho, $alto);
                        imagecopyresized($detalleimagen, $viejaimagen, 0, 0, 0, 0, $detallex, $detalley, $ancho, $alto);
                        $copia = $this->_options->upload->images . '/eventos/principal/' . $name;
                        $origen = $this->_options->upload->images . '/eventos/original/' . $name;
                        $general = $this->_options->upload->images . '/eventos/general/' . $name;
                        $detalle = $this->_options->upload->images . '/eventos/detalle/' . $name;
                        imagepng($nuevaimagen, $copia);
                        imagepng($viejaimagen, $origen);
                        imagepng($generalimagen, $general);
                        imagejpeg($detalleimagen, $detalle);
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
