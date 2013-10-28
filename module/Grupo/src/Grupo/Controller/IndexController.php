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
use Grupo\Model\Grupo;
use Grupo\Model\GrupoTable;
use Grupo\Form\GruposForm;
use Zend\Form\Element;
use Zend\Validator\File\Size;
use Zend\Http\Header\Cookie;
use Zend\Http\Header;
use Zend\Db\Sql\Sql;
use Application\Model\EventoTable;
use Zend\Mail\Message;
use Zend\Session\Container;
use Zend\View\Model\JsonModel;
use SanAuth\Controller\AuthController; 
class IndexController extends AbstractActionController
{

    protected $grupoTable;
   // protected $categorias;
    protected $usuarioTable;
    static $rutaStatic;
    protected $authservice;

    protected $_options;

    public function __construct()
    {
        $this->_options = new \Zend\Config\Config(include APPLICATION_PATH . '/config/autoload/global.php');
    }
 
    
    public function indexAction() {
        $renderer = $this->serviceLocator->get('Zend\View\Renderer\RendererInterface');
        $renderer->inlineScript()
                ->setScript('jQuery(document).ready(function(){valUsuario();});')
                ->prependFile($this->_options->host->base . '/js/main.js')
                ->prependFile($this->_options->host->base . '/js/masonry/jquery.infinitescroll.min.js')
                ->prependFile($this->_options->host->base . '/js/masonry/jquery.masonry.min.js')
                ->prependFile($this->_options->host->base . '/js/masonry/retina.js')
                ->prependFile($this->_options->host->base . '/js/masonry/prettify.js')
                ->prependFile($this->_options->host->base . '/js/masonry/superfish.js')
                ->prependFile($this->_options->host->base . '/js/masonry/custom.js')
                ->prependFile($this->_options->host->base . '/js/jquery.validate.min.js');
         $view= new ViewModel();
        $categorias = $this->categorias();
//        $this->layout()->categorias = $categorias;
        $storage = new \Zend\Authentication\Storage\Session('Auth');
        $session = $storage->read();
        if (!isset($session)) {
            $facebook = $this->facebook();
            $this->layout()->login = $facebook['loginUrl'];
            $this->layout()->user = $facebook['user'];
        }
        $buscar = $this->params()->fromQuery('dato');
        $filter = new \Zend\I18n\Filter\Alnum(true);
        $nombre = trim($filter->filter($buscar));
  
        setcookie('dato', $nombre);
        $valor = $this->params()->fromQuery('tipo');
        setcookie('tipo', $valor);
        $tipo = $this->params()->fromQuery('categoria');
        $rango = $this->params()->fromQuery('valor');
        $request = $this->getRequest();
//        $this->layout()->search = 'group-header';
        $search = 'group-header';
        if ($valor || $tipo || $nombre) {
            if ($nombre) {
                if (isset($nombre)) {
                    $busqueda = $this->params()->fromQuery('valor');

                    if ($busqueda) {
                        if ($busqueda == 'Eventos') {
                            $listaEvento = $this->getEventoTable()->listado2Evento($nombre);
                            $dd = $listaEvento->toArray();
                            if ($dd[0]["va_nombre"] != null) {
                                $listaEventos = $listaEvento;
                            } else {
                                return $this->redirect()->toUrl($this->getRequest()->getBaseUrl() . '?tipo=' . $busqueda . '&m=3');
                            }
                        } elseif ($busqueda == 'Grupos') {
                            $grupo = $this->getGrupoTable()->buscarGrupo($nombre);
                            if (count($grupo->toArray()) > 0) {
                                $listagrupos = $this->getGrupoTable()->buscarGrupoPag($nombre);//buscarGrupo($nombre);
                            } else {
                                return $this->redirect()->toUrl($this->getRequest()->getBaseUrl() . '?tipo=' . $busqueda . '&m=4');
                            }
                        } else {

                            $grupo = $this->getGrupoTable()->buscarGrupo($nombre);
                            if (count($grupo->toArray()) > 0) {
                                $listagrupos = $this->getGrupoTable()->buscarGrupoPag($nombre);//buscarGrupo($nombre);
                            } else {
                                return $this->redirect()->toUrl($this->getRequest()->getBaseUrl() . '?tipo=' . $busqueda . '&m=4');
                            }
                        }
                    } else {
//                        echo 'aka';exit;
                        $grupo = $this->getGrupoTable()->buscarGrupo($nombre);
                        if (count($grupo->toArray()) > 0) {
                             $search = 'group-header';
                                   $active = 'active';
                            $listagrupos = $this->getGrupoTable()->buscarGrupoPag($nombre);//buscarGrupo($nombre);
                            
                        } else {
                            $listaEventos = $this->getEventoTable()->listado2Evento($nombre);
                            if (count($listaEventos) > 0) {
                                $search = 'event-header';
                                      $active1 = 'active';
                                $listaEventos = $this->getEventoTable()->listado2Evento($nombre);
                            } else {
                                return $this->redirect()->toUrl($this->getRequest()->getBaseUrl() . '?tipo=Grupos&m=4');
                            }
                        }
                    }
                }
            }

            if ($tipo) {
                if (isset($tipo)) {
                    if (!empty($rango)) {
                        if ($rango == 'Grupos') {
                            $listagrupos = $this->getGrupoTable()->buscarGrupoPag(null, $tipo);//buscarGrupo(null, $tipo);
                            if (count($listagrupos) <= 0) {
                                return $this->redirect()->toUrl($this->getRequest()->getBaseUrl() . '?tipo=' . $rango . '&m=2');
                            }
                        } else {
//                            
                            $listaEventos=(!$storage) ? $this->getEventoTable()->eventocategoria($tipo) : $this->getEventoTable()->listadoEvento($session->in_id,$tipo);
                            
                            if (count($listaEventos) <= 0) {//echo'ma';exit;
                                return $this->redirect()->toUrl($this->getRequest()->getBaseUrl() . '?tipo=' . $rango . '&m=1');
                            }
                        }
                    } else {
                        $listagrupos = $this->getGrupoTable()->buscarGrupoPag(null, $tipo);//buscarGrupo(null, $tipo);
                    }
                }
            }
            if ($valor) {
                if (isset($valor)) {
                    if ($valor == 'Grupos') {
                        $listagrupos = $this->getGrupoTable()->fetchAllGrupo();//fetchAll();
                        //$this->layout()->search = 'group-header';
                        $search = 'group-header';
                  
                    } else {
//                    if ($session->in_id) { 
//                       $listaEventosPriva = $this->getEventoTable()->listadoEvento($session->in_id);
//                       $listaEventos2 = $this->getEventoTable()->listadoEvento();
//                       $listaEventos=  array_merge_recursive($listaEventosPriva->toArray(),$listaEventos2->toArray());
//                     $this->layout()->search='event-header';
//                    }else{
//                        $listaEventos = $this->getEventoTable()->listadoEvento();
//                        $this->layout()->search='event-header';
//                    }

                        $listaEventos = (!$storage) ? $this->getEventoTable()->listadoEvento() : $this->getEventoTable()->listadoEvento($session->in_id);
//                        var_dump($listaEventos->toArray());Exit;
//                        $this->layout()->search = 'event-header';
                        $search = 'event-header';
                        
                    }
                }
            }
        } else {
            $listagrupos = $this->getGrupoTable()->fetchAllGrupo();//$this->getGrupoTable()->fetchAll();
            $active = 'active';
        }
        

        if (count($listaEventos) > 0) {
            $page1 = (int) $this->params()->fromQuery('page', 1);
            $paginator = new \Zend\Paginator\Paginator(new \Zend\Paginator\Adapter\Iterator($listaEventos));// $listaEventos
            $paginator->setCurrentPageNumber($page1);
            $paginator->setItemCountPerPage(12);
            $cante=count($listaEventos->toArray());
            if(ceil($cante/12) <$page1){
                $view->setTemplate('layout/layout-error');
            }
                

        } elseif (count($listagrupos) > 0) { //echo 'we';exit;
            $page2 = (int) $this->params()->fromQuery('page', 1); 
            $paginator2 = new \Zend\Paginator\Paginator($listagrupos);//new \Zend\Paginator\Adapter\Iterator($listagrupos)
            $paginator2->setCurrentPageNumber($page2);
            $paginator2->setItemCountPerPage(12);
//            $cantg=count($listagrupos->toArray());
//            if(ceil($cantg/12) <$page2){
//                $view->setTemplate('layout/layout-error');
//            }

        } else {
              $mensaje_data='No se encontro data';
//            return $this->redirect()->toUrl('?m=2');
//            return $this->redirect()->toUrl($this->getRequest()->getBaseUrl() . '/auth');
        }

        $url = $_SERVER['REQUEST_URI'];
        if ($url != '/') {
            $buscatipo = strpos($url, 'tipo');
            $buscavalor = strpos($url, 'valor');
            $buscacateg = strpos($url, 'categoria');
            $buscadata = strpos($url, 'dato');
            if ($buscatipo || $buscavalor || $buscadata || $buscacateg) {
                $page = strpos($url, 'page=');
                if ($page) {
                    $urlf = substr($url, 0, $page - 1);
                } else {
                    $urlf = $url;
                }
                $urlf = $urlf . '&';
            } else {
                $urlf = $urlf . '?';
            }
        } else {
            $auxurl = strpos($url, '/');
            $urlf = substr($url, 0, $auxurl);
            $urlf = $urlf . '?';
        }
         
//        if($paginator){
//         $tot = $paginator->getPages()->pageCount;
//                if ($tot < $page1) {
//                    $view->setTerminal(true);
//                    $view->setTemplate('layout/layout-error');
//                }
//        }
        
//        if($paginator2){
//       $tot2 = $paginator2->getPages()->pageCount;
//   
//                if ($tot2 < $page2) {
//                    $view->setTerminal(true);
//                    $view->setTemplate('layout/layout-error');
//                }
//        }
        
//        foreach($paginator as $pag){
//            var_dump($pag->in_id);
//        }
//        exit;
        $view->setVariables(         array(
                    'grupos' => $paginator2,
                    'eventos' => $paginator,
                    'dato' => $valor,
                    'urlac' => $urlf,
                    'categorias'=>$categorias,
                    'search'=>$search,
                    'active'=>$active,
                    'active1'=>$active1,
                    'session'=>$session,
                    'cantgroup'=>$cantg,
                    'cantevent'=>$cante,
                    'mensaje_data'=>$mensaje_data
                ));
        return $view;

    }
    
    public function buscarAction(){
        
    }

    public function categorias()
    {        
        $categorias = $this->getGrupoTable()->tipoCategoria();
        return $categorias;
    }
     
    public function getEventoTable()
    {
        if (! $this->eventoTable) {
            $sm = $this->getServiceLocator();
            $this->eventoTable = $sm->get('Grupo\Model\EventoTable');
        }
        return $this->eventoTable;
    }
    
    public function elegirgrupoAction() {
        $storage = new \Zend\Authentication\Storage\Session('Auth');
        if (!$storage) {
            return $this->redirect()->toRoute('grupo');
        }
                // AGREGAR LIBRERIAS JAVASCRIPT EN EL FOOTER
        $renderer = $this->serviceLocator->get('Zend\View\Renderer\RendererInterface');
        $renderer->inlineScript()
            ->setScript('$(document).ready(function(){crearevento();});')
            ->prependFile($this->_options->host->base . '/js/main.js')
            ->prependFile($this->_options->host->base . '/js/map/locale-es.js')
            ->prependFile($this->_options->host->base . '/js/map/ju.google.map.js')
            ->prependFile('https://maps.googleapis.com/maps/api/js?key=AIzaSyA2jF4dWlKJiuZ0z4MpaLL_IsjLqCs9Fhk&sensor=true')
            ->prependFile($this->_options->host->base . '/js/map/ju.img.picker.js')
            ->prependFile($this->_options->host->base . '/js/min/bootstrap-datetimepicker.js')
            ->prependFile($this->_options->host->base . '/js/mockjax/jquery.mockjax.js')
            ->prependFile($this->_options->host->base . '/js/bootstrap-fileupload/bootstrap-fileupload.min.js')
            ->prependFile($this->_options->host->base . '/js/jquery.validate.min.js')
            ->prependFile($this->_options->host->base . '/js/ckeditor/ckeditor.js');

        $user_info = $this->getGrupoTable()->misgrupos($storage->read()->in_id);
        return array('grupos' => $user_info);
    }

    public function agregargrupoAction()
    {
        $categorias = $this->getGrupoTable()->tipoCategoria();
        $this->layout()->categorias = $categorias;
        
        $storage = new \Zend\Authentication\Storage\Session('Auth');
        if (! $storage) {
            return $this->redirect()->toRoute('grupo');
        }
        // print_r($storage->read()->in_id);exit;
        
        // AGREGAR CSS
        $renderer = $this->serviceLocator->get('Zend\View\Renderer\RendererInterface');
        $renderer->headLink()->prependStylesheet($this->_options->host->base . '/css/datetimepicker.css');
        
        // AGREGAR LIBRERIAS JAVASCRIPT EN EL FOOTER
        $renderer->inlineScript()
            ->setScript('$(document).ready(function(){crearevento();if($("#crear-group").length){valCrearEditar("#crear-group");}});')
            ->prependFile($this->_options->host->base . '/js/main.js')
            ->prependFile($this->_options->host->base . '/js/map/ju.img.picker.js')
            ->prependFile($this->_options->host->base . '/js/min/bootstrap-datetimepicker.js')
            ->prependFile($this->_options->host->base . '/js/mockjax/jquery.mockjax.js')
            ->prependFile($this->_options->host->base . '/js/bootstrap-fileupload/bootstrap-fileupload.min.js')
            ->prependFile($this->_options->host->base . '/js/jquery.validate.min.js');
        
        // $local = (int) $this->params()->fromQuery('id');
        $user_info = $this->getGrupoTable()->misgrupos($storage->read()->in_id);//usuarioxGrupo($storage->read()->in_id);
        $adpter = $this->getServiceLocator()->get('Zend\Db\Adapter\Adapter');
        $form = new GruposForm($adpter);
        $form->get('submit')->setValue('Crear Grupo');
        $request = $this->getRequest();
        $urlorigen=($request->getHeader('Referer'))?$request->getHeader('Referer')->uri()->getPath():'/usuario/index/misgrupos';//$this->getRequest()->getHeader('Referer')->uri()->getPath();
        
        if ($request->isPost()) {
           $datos =$this->request->getPost();

            $File = $this->params()->fromFiles('va_imagen');
            $nonFile = $this->params()->fromPost('va_nombre');
           if ($File['name'] != '') {
          require './vendor/Classes/Filter/Alnum.php';
            $imf = $File['name'];
            $info = pathinfo($File['name']);
         //   var_dump($info);exit;
            $valor = uniqid();
            $nom = $nonFile;
            $imf2 = $valor . '.' . $info['extension'];
            $filter = new \Filter_Alnum();        
            $filtered = $filter->filter($nom);
            $imagen = $filtered . '-' . $imf2; 

             } else {
                $imagen = 'defaultd.jpg';
            }
            

           
    
            $data = array_merge_recursive($this->getRequest()
                ->getPost()
                ->toArray(), $this->getRequest()
                ->getFiles()
                ->toArray());
            $grupo = new Grupo();
            $form->setInputFilter($grupo->getInputFilter());
            $form->setData($data); // $request->getPost()
                                   // $notificacion = $this->params()->fromPost('tipo_notificacion', 0);
     if ($form->isValid()) {

                $grupo->exchangeArray($form->getData());
                    if ($File['name'] != '') {
                if ($this->redimensionarImagen($File, $nonFile,$imagen,'')) {
                    // obtiene el identity y consulta el
                   $idgrupo=$this->getGrupoTable()->guardarGrupo($grupo, $notificacion, $storage->read()->in_id,$imagen,$datos->va_ciudad);
                   $this->flashMessenger()->clearMessages();
                   $this->flashMessenger()->addMessage('Su grupo ha sido creado correctamente.');
                   if($this->params()->fromPost('url')=='/cuenta/misgrupos' ||$this->params()->fromPost('url')=='/cuenta/grupoparticipo'){
                       return $this->redirect()->toRoute('detalle-grupo',array('in_id'=>$idgrupo));
                       
                   }else{
                       return $this->redirect()->toRoute('agregar-evento',array('in_id'=>$idgrupo));
                   }
  
                }
                                else {
                    echo 'problemas con el redimensionamiento';
                    exit();
                }
            }
               else {
               $idgrupo=$this->getGrupoTable()->guardarGrupo($grupo, $notificacion, $storage->read()->in_id,$imagen,$datos->va_ciudad);
                   $this->flashMessenger()->clearMessages();
                   $this->flashMessenger()->addMessage('Su grupo ha sido creado correctamente.');
                   if($this->params()->fromPost('url')=='/cuenta/misgrupos' ||$this->params()->fromPost('url')=='/cuenta/grupoparticipo'){
                       return $this->redirect()->toRoute('detalle-grupo',array('in_id'=>$idgrupo));
                       
                   }else{
                       return $this->redirect()->toRoute('agregar-evento',array('in_id'=>$idgrupo));
                   }
                    }
            } else {
                foreach ($form->getInputFilter()->getInvalidInput() as $error) {
                    print_r($error->getMessages()); // $inputFilter->getInvalidInput()
                     print_r($error->getName()); 
                }
            }
        }
        $mainViewModel = new ViewModel();

        return $mainViewModel->setVariables(array(
            'form' => $form,
            'grupos' => $user_info,
            'urlorigen'=>$urlorigen
        ));

    }

    public function editargrupoAction()
    {          
        $storage = new \Zend\Authentication\Storage\Session('Auth');
        if (! $storage) {
            return $this->redirect()->toRoute('grupos');
        }
       $renderer = $this->serviceLocator->get('Zend\View\Renderer\RendererInterface');
        $renderer->inlineScript()
            ->setScript('$(document).ready(function(){crearevento();if($("#grupoEditar").length){valCrearEditar("#grupoEditar");}});')
            ->prependFile($this->_options->host->base . '/js/main.js')
            ->prependFile($this->_options->host->base . '/js/bootstrap-fileupload/bootstrap-fileupload.min.js')
            ->prependFile($this->_options->host->base . '/js/map/ju.img.picker.js')
            ->prependFile($this->_options->host->base . '/js/jquery.validate.min.js');

        $categorias = $this->getGrupoTable()->tipoCategoria();
        $this->layout()->categorias = $categorias;
        $id = (int) $this->params()->fromRoute('in_id', 0);
        if (! $id) {
            return $this->redirect()->toRoute('grupo', array(
                'action' => 'agregargrupo'
            ));
        }
        
        try {
            $grupo = $this->getGrupoTable()->getGrupo($id);
        } catch (\Exception $ex) {
            
            return $this->redirect()->toRoute('grupo', array(
                'action' => 'index'
            ));
        }
        $adpter = $this->getServiceLocator()->get('Zend\Db\Adapter\Adapter');
        $form = new GruposForm($adpter); 
        $ubige=$this->getUsuarioTable()->getPais($grupo->va_pais);
        $array = array();
        foreach ($ubige as $y) {
            $array[$y['ID']] = $y['Name']; }
       $ciudad = $this->getGrupoTable()->getGrupo2($id)->toArray();
        if($ciudad[0]['va_pais']=='PER')
        { $ciudad=$this->getUsuarioTable()->getCiudadPeru($ciudad[0]['va_ciudad']);}
        else
        { $ciudad=$this->getUsuarioTable()->getCiudad('',$ciudad[0]['va_ciudad']);}
       $form->get('va_pais')->setValue($array);
        $form->bind($grupo);
        $form->get('submit')->setAttribute('value', 'Editar');
        $imagen=$grupo->va_imagen;
        $request = $this->getRequest();
        
        if ($request->isPost()) {
            $datos =$this->request->getPost();
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
                $imagen = $grupo->va_imagen;
            }
            
            $data = array_merge_recursive($this->getRequest()
                ->getPost()
                ->toArray(), $this->getRequest()
                ->getFiles()
                ->toArray());
            $form->setInputFilter($grupo->getInputFilter());
            $form->setData($data);
            $notificacion = $this->params()->fromPost('tipo_notificacion', 0);
            if ($form->isValid()) { 
                 if ($File['name'] != '') {  
                if ($this->redimensionarImagen($File, $nonFile,$imagen,$id)) {
                    $this->getGrupoTable()->guardarGrupo($grupo, $notificacion,$storage->read()->in_id,$imagen,$datos->va_ciudad);
                    $this->flashMessenger()->addMessage('Grupo editado correctamente');
                    return $this->redirect()->toRoute('detalle-grupo',array('in_id'=>$id));
                } else {
                    echo 'problemas con el redimensionamiento';
                    exit();
                }
               }
                 else{$this->getGrupoTable()->guardarGrupo($grupo, $notificacion,$storage->read()->in_id,$imagen,$datos->va_ciudad);
                    $this->flashMessenger()->addMessage('Grupo editado correctamente');
                    return $this->redirect()->toRoute('detalle-grupo',array('in_id'=>$id));}
                
            } else {
                foreach ($form->getInputFilter()->getInvalidInput() as $error) {
                    print_r($error->getMessages());
                }
            }
        }
        
//        $flashMessenger = $this->flashMessenger();
//        if ($flashMessenger->hasMessages()) {
//            $mensajes = $flashMessenger->getMessages();
//        }
        
        return array(
            'in_id' => $id,
            'form' => $form,
            'imagen'=>$imagen,
            'nameCiudad'=>$ciudad[0]['Name'],
             'nameID'=>$ciudad[0]['ID'],
        );
    }

    public function eliminargrupoAction()
    {}

    public function detallegrupoAction()
    {
        $renderer = $this->serviceLocator->get('Zend\View\Renderer\RendererInterface');
        $renderer->inlineScript()
        ->setScript('$(document).ready(function(){valUsuario();});')
            ->prependFile($this->_options->host->base . '/js/main.js')
            ->prependFile($this->_options->host->base . '/js/jquery.validate.min.js');
        $id = $this->params()->fromRoute('in_id');
        $grupo = $this->getEventoTable()->grupoid($id);
        $categorias = $this->categorias();
        $this->layout()->categorias = $categorias;
        $this->layout()->active='active';
        $storage = new \Zend\Authentication\Storage\Session('Auth');
        $session=$storage->read();
        if (!isset($session)) {
        $facebook = $this->facebook();
        $this->layout()->login = $facebook['loginUrl'];
        $this->layout()->user = $facebook['user']; }
//        $eventospasados = $this->getEventoTable()->eventospasados($id);
//        $eventosfuturos = $this->getEventoTable()->eventosfuturos($id);
        
//        if($session->in_id){
//        $usuarios = $this->getGrupoTable()->usuariosgrupodetalle($id,$session->in_id);
//        $proximos_eventos = $this->getGrupoTable()->eventosgrupo($id,$session->in_id);
//        } else{
//            $usuarios = $this->getGrupoTable()->usuariosgrupo($id,null);
//            $proximos_eventos = $this->getGrupoTable()->eventosgrupo($id,null);
//        }
//        $paginator2 = new \Zend\Paginator\Paginator(new \Zend\Paginator\Adapter\Iterator($proximos_eventos));
//        $paginator2->setCurrentPageNumber((int) $this->params()->fromQuery('page', 1));
//        $paginator2->setItemCountPerPage(4); 

        if ($session) {            
            $participa=$this->getGrupoTable()->compruebarUsuarioxGrupo($session->in_id,$id);
            $activo=$participa->va_estado;//=='activo'?true:false;
        }
        
        $eventospasados = $this->getEventoTable()->eventospasados($id);
        $eventosfuturos = $this->getEventoTable()->eventosfuturos($id);
        //listar usuarios solo si estas unido al grupo
        if($participa->va_estado=='activo'){
        $usuarios = $this->getGrupoTable()->usuariosgrupodetalle($id);//usuariosgrupo($id);
        }else{
         $usuarios = null;   
        }
        $proximos_eventos = $this->getGrupoTable()->eventosgrupo($id,$session->in_id);
        $paginator2 = new \Zend\Paginator\Paginator(new \Zend\Paginator\Adapter\Iterator($proximos_eventos));
        $paginator2->setCurrentPageNumber((int) $this->params()->fromQuery('page', 1));
        $paginator2->setItemCountPerPage(4); 
        /*
         *compara el estado del usuario
         */
        if($grupo[0]['ta_usuario_in_id']==$session->in_id){
                $usuariosaceptado=$this->getGrupoTable()->estadoUsuariosxGrupo($id,'activo',$grupo[0]['ta_usuario_in_id']);
                $usuariospendiente=$this->getGrupoTable()->estadoUsuariosxGrupo($id,'pendiente',$grupo[0]['ta_usuario_in_id']);
         
        }
  
        $flashMessenger = $this->flashMessenger();
        if ($flashMessenger->hasMessages()) {
            $mensajes = $flashMessenger->getMessages();
        }
        
        return new ViewModel(array(
            'grupo' => $grupo,
            'eventosfuturos' => $eventosfuturos,
            'eventospasados' => $eventospasados,
            'usuarios' => $usuarios,
            'proximos_eventos' => $paginator2,
            'session'=>$session,
            'in_id'=>$id,
            'participa'=>$activo,
            'mensajes'=>$mensajes,
            'usuariosaceptado'=>$usuariosaceptado,
            'usuariospendiente'=>$usuariospendiente
        ));
     
    }
    
    
    public function usergrupoAction(){
        $request=$this->getRequest();
        $id=$this->params()->fromPost('idgrupo');
        if($request->isPost()){
            $usuarios = $this->getGrupoTable()->usuariosgrupo($id);            
        }
         $result = new JsonModel(array(
                'usuarios'=>$usuarios->toArray()
            )); 
            return $result;
    }
    
    public function aprobarAction() {
         $storage = new \Zend\Authentication\Storage\Session('Auth');
        $idgrupo = $this->params()->fromQuery('id_grupo');
        $idusuario = $this->params()->fromQuery('id_usuario');
        $aprobar = $this->params()->fromQuery('act');

//        $usuarioapro = $this->getGrupoTable()->getNotifiacionesxUsuario($idusuario)->toArray();
//        $arr = array();
//        foreach ($usuarioapro as $value) {
//            $arr[] = $value['ta_notificacion_in_id'];
//        }
//        if (in_array(1, $arr)) {
//            $correoe = true;
//        }
//        if (in_array(2, $arr)) {
//            $correos = true;
//        }
        
        $usuariocrear = $this->getGrupoTable()->getNotifiacionesxUsuario($storage->read()->in_id)->toArray();
        $arrc = array();
        foreach ($usuariocrear as $values) {
            $arrc[] = $values['ta_notificacion_in_id'];
        }
        if (in_array(1, $arrc)) {
            $correoec = true;
        }
        if (in_array(2, $arrc)) {
            $correosc = true;
        }

        $usuario = $this->getUsuarioTable()->getUsuario($idusuario); //$this->getGrupoTable()->grupoxUsuario($idgrupo)->toArray();
        $user_info['nom_grup'] = $this->getGrupoTable()->getGrupo($idgrupo)->va_nombre;
        if ($this->getGrupoTable()->aprobarUsuario($idgrupo, $idusuario, $aprobar)) {
            if($aprobar==2){
//            if ($correoe) {
                $bodyHtml = '<!DOCTYPE html><html xmlns="http://www.w3.org/1999/xhtml">
                                               <head>
                                               <meta http-equiv="Content-type" content="text/html;charset=UTF-8"/>
                                               </head>
                                               <body>
                                                    <div style="color: #7D7D7D"><br />                                                       
                                                     Hola ' . ucwords($usuario->va_nombre) . ',<br />
                                                     Usted se ha unido al grupo <a href="'.$this->_options->host->base.'/grupo/'.$idgrupo.'"><strong style="color:#133088; font-weight: bold;">' . utf8_decode($user_info['nom_grup']) . '</strong></a><br />
                                                     Si desea mas información del grupo dar <a href="' . $this->_options->host->base . '/grupo/' . $idgrupo . '">Clic Aquí</a> <br />Equipo Juntate.pe     
                                                     </div>
                                                     <a href="'.$this->_options->host->base.'"><img src="' . $this->_options->host->img . '/juntate.png" title="juntate.pe"/></a>
                                              
                                                     
                                               </body>
                                               </html>';

                $this->mensaje($usuario->va_email, $bodyHtml, 'Se ha unido al grupo');
                
//            }
              if ($correoec) {
//                    $usuario = $this->getGrupoTable()
//                            ->grupoxUsuario($idgrupo)
//                            ->toArray();
                    $bodyHtmlAdmin = '<!DOCTYPE html><html xmlns="http://www.w3.org/1999/xhtml">
                                               <head>
                                               <meta http-equiv="Content-type" content="text/html;charset=UTF-8"/>
                                               </head>
                                               <body>
                                                    <div style="color: #7D7D7D"><br />
                                                     Hola ' . ucwords($storage->read()->va_nombre) . ',<br /><br />
                                                     El siguiente usuario se ha unido a tu grupo <a href="'.$this->_options->host->base.'/grupo/'.$idgrupo.'"><strong style="color:#133088; font-weight: bold;">' . utf8_decode($user_info['nom_grup']) . ':</strong></a><br /><br />'.
                                                           utf8_decode($usuario->va_nombre).'<br /><br />
                                                     Equipo Juntate.pe
                                                     <br /><br /><br />
                                                     </div>
                                                     <a href="'.$this->_options->host->base.'"><img src="' . $this->_options->host->img . '/juntate.png" title="juntate.pe"/></a>                                                     
                                               </body>
                                               </html>';

                        $this->mensaje($storage->read()->va_email, $bodyHtmlAdmin, 'Se unieron a tu grupo');//$usuario[0]['va_email']

                }
                $activo = 1;
            }
           if($aprobar==1){
//            if ($correos) {
                $bodyHtmlAdmin = '<!DOCTYPE html><html xmlns="http://www.w3.org/1999/xhtml">
                                               <head>
                                               <meta http-equiv="Content-type" content="text/html;charset=UTF-8"/>
                                               </head>
                                               <body>
                                                    <div style="color: #7D7D7D"><br />
                                                    Hola ' . ucwords($usuario->va_nombre) . ',<br />
                                                    Lo sentimos pero ha sido retirado del grupo <a href="'.$this->_options->host->base.'/grupo/'.$idgrupo.'"><strong style="color:#133088; font-weight: bold;">' . utf8_decode($user_info['nom_grup']) . '</strong></a><br /><br />
                                                    Si desea tener información de otros grupos puede buscarlos en <a href="' . $this->_options->host->base . '">Juntate.pe</a> <br /><br /> 
                                                    Equipo Juntate.pe <br /><br /><br />
                                                    </div>
                                                    <a href="'.$this->_options->host->base.'"><img src="' . $this->_options->host->img . '/juntate.png" title="juntate.pe"/></a>
                                                    
                                                     
                                               </body>
                                               </html>';

                $this->mensaje($usuario->va_email, $bodyHtmlAdmin, 'Lo retiraron del grupo');
//            }
                
              if ($correosc) {
                    $bodyHtmlAdmin = '<!DOCTYPE html><html xmlns="http://www.w3.org/1999/xhtml">
                                               <head>
                                               <meta http-equiv="Content-type" content="text/html;charset=UTF-8"/>
                                               </head>
                                               <body>
                                                    <div style="color: #7D7D7D"><br />
                                                    Hola ' . ucwords($storage->read()->va_nombre) . ',<br /><br />
                                                    Haz retirado al siguiente usuario de tu grupo <a href="'.$this->_options->host->base.'/grupo/'.$idgrupo.'"><strong style="color:#133088; font-weight: bold;">'.utf8_decode($user_info['nom_grup']).': ' . utf8_decode($usuario->va_nombre) . '</strong></a>
                                                    <br /><br /><br /> 
                                                    </div>
                                                    <a href="'.$this->_options->host->base.'"><img src="' . $this->_options->host->img . '/juntate.png" title="juntate.pe"/></a>

                                               </body>
                                               </html>';
                    if ($usuario) {
                        $this->mensaje($storage->read()->va_email, $bodyHtmlAdmin, 'Dejaron a tu grupo');
                    }
                }
            $activo = 0;
        }
        }

        $userestado = $this->getGrupoTable()->usuariosgrupo($idgrupo, $idusuario)->current();
        $arruser['id'] = $idusuario;
        setlocale(LC_TIME, "es_ES.UTF-8");
        foreach ($userestado as $key => $value) {
            if ($key == 'va_fecha') {
                $fecha = str_replace("/", "-", $value);
                $date = strtotime($fecha);
                $arruser[$key] = 'Se unio el ' . date("d", $date) . ' de ' . date("F", $date) . ' del ' . date("Y", $date); //                   $arruser[$key]='Se unio el '.date("d", $date).' de '.strftime("%B", $date).' del '.date("Y",$date);//
            } else {
                $arruser[$key] = $value;
            }
        }
        $result = new JsonModel(array(
                    'estado' => $activo,
                    'userestado' => $arruser
                ));

        return $result;
    }

    public function unirAction() {
        $storage = new \Zend\Authentication\Storage\Session('Auth');
        if (!$storage) {
            return $this->redirect()->toRoute('grupo');
        }

        $iduser = $storage->read()->in_id; // 1;
        $idgrup = $this->params()->fromQuery('idE'); // 48;
        $unir = $this->params()->fromQuery('act');
        $idusergrup = $this->getGrupoTable()->getGrupo($idgrup)->ta_usuario_in_id;

//        $usuariosesion = $this->getGrupoTable()->getNotifiacionesxUsuario($iduser)->toArray();
        $usuariocrear = $this->getGrupoTable()->getNotifiacionesxUsuario($idusergrup)->toArray();
//        $arr = array();
//        foreach ($usuariosesion as $value) {
//            $arr[] = $value['ta_notificacion_in_id'];
//        }
//        if (in_array(1, $arr)) {
//            $correoe = true;
//        }
//        if (in_array(2, $arr)) {
//            $correos = true;
//        }
        $arrc = array();
        foreach ($usuariocrear as $values) {
            $arrc[] = $values['ta_notificacion_in_id'];
        }
        if (in_array(1, $arrc)) {
            $correoec = true;
        }
        if (in_array(2, $arrc)) {
            $correosc = true;
        }

        if ($unir == 1) {
            if ($this->getGrupoTable()->unirseGrupo($idgrup, $iduser)) {
//                if ($correoe) {
                    $user_info['nom_grup'] = $this->getGrupoTable()->getGrupo($idgrup)->va_nombre;
                    $bodyHtml = '<!DOCTYPE html><html xmlns="http://www.w3.org/1999/xhtml">
                                               <head>
                                               <meta http-equiv="Content-type" content="text/html;charset=UTF-8"/>
                                               </head>
                                               <body>
                                                    <div style="color: #7D7D7D"><br />
                                                     Hola ' . ucwords($storage->read()->va_nombre) . ',<br /><br />
                                                     Usted está pendiente de unirse al grupo: <a href="'.$this->_options->host->base.'/grupo/'.$idgrup.'"><strong style="color:#133088; font-weight: bold;">' . utf8_decode($user_info['nom_grup']) . '</strong></a><br /><br />
                                                     Equipo Juntate.pe  <br /><br /><br /> 
                                                     </div>
                                                     <a href="'.$this->_options->host->base.'"><img src="' . $this->_options->host->img . '/juntate.png" title="juntate.pe"/></a>

                                               </body>
                                               </html>';

                    $this->mensaje($storage->read()->va_email, $bodyHtml, 'Solicitud de unirse al grupo');
//                }
                if ($correoec) {
                    $usuario = $this->getGrupoTable()
                            ->grupoxUsuario($idgrup)
                            ->toArray();
                    $bodyHtmlAdmin = '<!DOCTYPE html><html xmlns="http://www.w3.org/1999/xhtml">
                                               <head>
                                               <meta http-equiv="Content-type" content="text/html;charset=UTF-8"/>
                                               </head>
                                               <body>
                                                    <div style="color: #7D7D7D"><br />
                                                     Hola ' . ucwords($usuario[0]['nombre_usuario']) . ',<br /><br />
                                                     El usuario '.utf8_decode($storage->read()->va_nombre).' está solicitando unirse a tu grupo <a href="'.$this->_options->host->base.'/grupo/'.$idgrup.'"><strong style="color:#133088; font-weight: bold;">' . utf8_decode($user_info['nom_grup']) . '</strong></a><br /><br />
                                                     Equipo Juntate.pe  <br /><br /><br /> 
                                                     </div>
                                                     <a href="'.$this->_options->host->base.'"><img src="' . $this->_options->host->img . '/juntate.png" title="juntate.pe"/></a>
                
                                               </body>
                                               </html>';
                    if ($usuario) {
                        $this->mensaje($usuario[0]['va_email'], $bodyHtmlAdmin, 'Solicitud de unirse a tu grupo');
                    }
                }
                $activo = 1;
                $userestado = $this->getGrupoTable()->usuariosgrupo($idgrup, $iduser); //getGrupoUsuario($idgrup, $iduser);
            }
        } elseif ($unir == 0) {
            if ($this->getGrupoTable()->retiraGrupo($idgrup, $iduser)) {
//                if ($correos) {
                    $user_info['nom_grup'] = $this->getGrupoTable()->getGrupo($idgrup)->va_nombre;
                    $bodyHtml = '<!DOCTYPE html><html xmlns="http://www.w3.org/1999/xhtml">
                                               <head>
                                               <meta http-equiv="Content-type" content="text/html;charset=UTF-8"/>
                                               </head>
                                               <body>
                                                    <div style="color: #7D7D7D"><br />
                                                    Hola ' . ucwords($storage->read()->va_nombre) . ',<br /><br />
                                                    Usted ha abandonado el grupo <a href="'.$this->_options->host->base.'/grupo/'.$idgrup.'"><strong style="color:#133088; font-weight: bold;">' . utf8_decode($user_info['nom_grup']) . '</strong></a><br /><br />
                                                    Si desea tener información de otros grupos puede buscarlos en <a href="' . $this->_options->host->base . '">Juntate.pe</a> <br /><br />
                                                    Equipo Juntate.pe  <br /><br /><br />          
                                                     </div>
                                                     <a href="'.$this->_options->host->base.'"><img src="' . $this->_options->host->img . '/juntate.png" title="juntate.pe"/></a>
                                               </body>
                                               </html>';
                    $this->mensaje($storage->read()->va_email, $bodyHtml, 'Ha dejado un grupo');
//                }
                if ($correosc) {
                    $usuario = $this->getGrupoTable()
                            ->grupoxUsuario($idgrup)
                            ->toArray();
                    $bodyHtmlAdmin = '<!DOCTYPE html><html xmlns="http://www.w3.org/1999/xhtml">
                                               <head>
                                               <meta http-equiv="Content-type" content="text/html;charset=UTF-8"/>
                                               </head>
                                               <body>
                                                    <div style="color: #7D7D7D"><br />
                                                     Hola ' . ucwords($usuario[0]['nombre_usuario']) . ',<br />
                                                     El siguiente usuario ha abandonado a tu grupo <a href="'.$this->_options->host->base.'/grupo/'.$idgrup.'"><strong style="color:#133088; font-weight: bold;">'.utf8_decode($user_info['nom_grup']).':' . utf8_decode($storage->read()->va_nombre) . '</strong></a><br />
                                                     </div>
                                                       <a href="'.$this->_options->host->base.'"><img src="' . $this->_options->host->img . '/juntate.png" title="juntate.pe"/></a>
                                               </body>
                                               </html>';
                    if ($usuario) {
                        $this->mensaje($usuario[0]['va_email'], $bodyHtmlAdmin, 'Dejaron a tu grupo');
                    }
                }
                $activo = 0;
                $userestado = $this->getGrupoTable()->usuariosgrupo($idgrup, $iduser); //getGrupoUsuario($idgrup, $iduser);
            }
        }
        $userestado = $userestado->current();
        $arruser['id'] = $iduser;
        setlocale(LC_TIME, "es_ES.UTF-8");
        foreach ($userestado as $key => $value) {
            if ($key == 'va_fecha') {
                $fecha = str_replace("/", "-", $value);
                $date = strtotime($fecha);
                $arruser[$key] = 'Se unio el ' . date("d", $date) . ' de ' . date("F", $date) . ' del ' . date("Y", $date); //                   $arruser[$key]='Se unio el '.date("d", $date).' de '.strftime("%B", $date).' del '.date("Y",$date);//
            } else {
                $arruser[$key] = $value;
            }
        }
        $result = new JsonModel(array(
                    'estado' => $activo,
                    'userestado' => $arruser
                ));

        return $result;
    }

    public function mensaje($mail,$bodyHtml,$subject){
        $message = new Message();
        $message->addTo($mail, $nombre)
        ->setFrom('listadelsabor@innovationssystems.com', 'listadelsabor.com')
        ->setSubject($subject);
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

    public function dejarAction()
    {
        // $iduser = 1;
        // $idgrup = 50;
        $iduser = $storage->read()->in_id; // 1;
        $idgrup = $this->params()->fromRoute('in_id');
        if ($this->getGrupoTable()->retiraGrupo($idgrup, $iduser)) {
            $user_info['nom_grup'] = $this->getGrupoTable()->getGrupo($idgrup)->va_nombre;
            $bodyHtml = '<!DOCTYPE html><html xmlns="http://www.w3.org/1999/xhtml">
                                               <head>
                                               <meta http-equiv="Content-type" content="text/html;charset=UTF-8"/>
                                               </head>
                                               <body>
                                                    <div style="color: #7D7D7D"><br />
                                                     Uds. se ha dejado al grupo <a href="'.$this->_options->host->base.'/grupo/'.$idgrup.'"><strong style="color:#133088; font-weight: bold;">' . utf8_decode($user_info['nom_grup']) . '</strong></a><br />
                                                     Si desea tener información de otros grupos puede buscarlos en <a href="' . $this->_options->host->base . '">Juntate.pe</a> <br /><br />    
                                                     </div>
                                                     <a href="'.$this->_options->host->base.'"><img src="' . $this->_options->host->img . '/juntate.png" title="juntate.pe"/></a>
                                               </body>
                                               </html>';
            
            $message = new Message();
            $message->addTo('ola@yopmail.com', $nombre)
                ->setFrom('listadelsabor@innovationssystems.com', 'listadelsabor.com')
                ->setSubject('Ha dejado un grupo');
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
            $this->redirect()->toUrl('/grupo');
        }
    }
        public function terminosAction()
    {
        // This shows the :controller and :action parameters in default route
        // are working when you browse to /module-specific-root/skeleton/foo
        return array();
    }
    
        public function nosotrosAction()
    {
        // This shows the :controller and :action parameters in default route
        // are working when you browse to /module-specific-root/skeleton/foo
        return array();
    }

    public function fooAction()
    {
        // This shows the :controller and :action parameters in default route
        // are working when you browse to /module-specific-root/skeleton/foo
        return array();
    }

    public function getGrupoTable()
    {
        if (! $this->grupoTable) {
            $sm = $this->getServiceLocator();
            $this->grupoTable = $sm->get('Grupo\Model\GrupoTable');
            $config=$sm->get('Config');  
            self::$rutaStatic=$config['host']['ruta'];
        }
        return $this->grupoTable;
    }

    public function getUsuarioTable()
    {
        if (! $this->usuarioTable) {
            $sm = $this->getServiceLocator();
            $this->usuarioTable = $sm->get('Usuario\Model\UsuarioTable');
        }
        return $this->usuarioTable;
    }

    public function getAuthService()
    {
        if (! $this->authservice) {
            $this->authservice = $this->getServiceLocator()->get('AuthService');
        }
        return $this->authservice;
    }

    private function redimensionarImagen($File, $nonFile,$imagen,$id=null)
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
              $name =$imagen;
              $verusuariox=79;
              $verusuarioy=79;
               if ($id != null) {
                $grupo = $this->getGrupoTable()->getGrupo($id);
                $imog = $grupo->va_imagen;
                $eliminar1 = $this->_options->upload->images . '/grupo/general/' . $imog;
                $eliminar2 = $this->_options->upload->images . '/grupo/original/' . $imog;
                $eliminar3 = $this->_options->upload->images . '/grupo/principal/' . $imog;
                $eliminar4 = $this->_options->upload->images . '/grupo/verusuario/' . $imog;
                unlink($eliminar1);
                unlink($eliminar2);
                unlink($eliminar3);
                unlink($eliminar4);
            }
            // $altura=$tamanio[1];
          //  $valor = uniqid();
            if ($ancho > $alto) { // echo 'ddd';exit;
              //  require './vendor/Classes/Filter/Alnum.php';
                // $altura =(int)($alto*$anchura/$ancho); //($alto*$anchura/$ancho);
                $altura = (int) ($alto * $anchura / $ancho);
                $anchura = (int) ($ancho * $altura / $alto);
                if ($info['extension'] == 'jpg' or $info['extension'] == 'JPG' or $info['extension'] == 'jpeg' or $info['extension'] == 'png' or $info['extension'] == 'PNG') {
//                    $nom = $nonFile;
//                    $imf2 = $valor . '.' . $info['extension'];
//                    $filter = new \Filter_Alnum();
//                    $filtered = $filter->filter($nom);
//                    $name = $filtered . '-' . $imf2;
                 
                  
                    
                    if ($info['extension'] == 'jpg' or $info['extension'] == 'JPG' or $info['extension'] == 'jpeg') {
                        $viejaimagen = imagecreatefromjpeg($File['tmp_name']);
                        $nuevaimagen = imagecreatetruecolor($anchura, $altura);
                        $generalimagen = imagecreatetruecolor($generalx, $altura);
                         $verusuario = imagecreatetruecolor($verusuariox, $verusuarioy);
                        imagecopyresized($nuevaimagen, $viejaimagen, 0, 0, 0, 0, $anchura, $altura, $ancho, $alto);
                        imagecopyresized($generalimagen, $viejaimagen, 0, 0, 0, 0, $generalx, $altura, $ancho, $alto);
                        imagecopyresized($verusuario, $viejaimagen, 0, 0, 0, 0, $verusuariox, $verusuarioy, $ancho, $alto);
                        $copia = $this->_options->upload->images . '/grupos/principal/' . $name;
                        $origen = $this->_options->upload->images . '/grupos/original/' . $name;
                        $general = $this->_options->upload->images . '/grupos/general/' . $name;
                        $verusuarios = $this->_options->upload->images . '/grupos/verusuario/' . $name;
                        imagejpeg($nuevaimagen, $copia);
                        imagejpeg($viejaimagen, $origen);
                        imagejpeg($generalimagen, $general);
                       imagejpeg($verusuario, $verusuarios);
                    } else {
                        $viejaimagen = imagecreatefrompng($File['tmp_name']);
                        $nuevaimagen = imagecreatetruecolor($anchura, $altura);
                        $generalimagen = imagecreatetruecolor($generalx, $altura);
                        $verusuario = imagecreatetruecolor($verusuariox, $verusuarioy);
                        imagecopyresized($nuevaimagen, $viejaimagen, 0, 0, 0, 0, $anchura, $altura, $ancho, $alto);
                        imagecopyresized($generalimagen, $viejaimagen, 0, 0, 0, 0, $generalx, $altura, $ancho, $alto);
                         imagecopyresized($verusuario, $viejaimagen, 0, 0, 0, 0, $verusuariox, $verusuarioy, $ancho, $alto);
                        $copia = $this->_options->upload->images . '/grupos/principal/' . $name;
                        $origen = $this->_options->upload->images . '/grupos/original/' . $name;
                        $general = $this->_options->upload->images . '/grupos/general/' . $name;
                         $verusuarios = $this->_options->upload->images . '/grupos/verusuario/' . $name;
                        imagepng($nuevaimagen, $copia);
                        imagepng($viejaimagen, $origen);
                        imagepng($generalimagen, $general);
                           imagejpeg($verusuario, $verusuarios);
                    }
                    return true;
                }
            }
            if ($ancho < $alto) {
               // require './vendor/Classes/Filter/Alnum.php';
                // $anchura =(int)($ancho*$altura/$alto);
                $altura = (int) ($alto * $anchura / $ancho);
                if ($info['extension'] == 'jpg' or $info['extension'] == 'JPG' or $info['extension'] == 'jpeg' or $info['extension'] == 'png' or $info['extension'] == 'PNG') {
//                    $nom = $nonFile;
//                    $imf2 = $valor . '.' . $info['extension'];
//                    $filter = new \Filter_Alnum();
//                    $filtered = $filter->filter($nom);
//                    $name = $filtered . '-' . $imf2;
                    
                    if ($info['extension'] == 'jpg' or $info['extension'] == 'JPG' or $info['extension'] == 'jpeg') {
                        $viejaimagen = imagecreatefromjpeg($File['tmp_name']);
                        $nuevaimagen = imagecreatetruecolor($anchura, $altura);
                        $generalimagen = imagecreatetruecolor($generalx, $altura);     
                        $verusuario = imagecreatetruecolor($verusuariox, $verusuarioy);
                        imagecopyresized($nuevaimagen, $viejaimagen, 0, 0, 0, 0, $anchura, $altura, $ancho, $alto);
                        imagecopyresized($generalimagen, $viejaimagen, 0, 0, 0, 0, $generalx, $altura, $ancho, $alto);
                        imagecopyresized($verusuario, $viejaimagen, 0, 0, 0, 0, $verusuariox, $verusuarioy, $ancho, $alto);
                        $copia = $this->_options->upload->images . '/grupos/principal/' . $name;
                        $origen = $this->_options->upload->images . '/grupos/original/' . $name;
                        $general = $this->_options->upload->images . '/grupos/general/' . $name;
                         $verusuarios = $this->_options->upload->images . '/grupos/verusuario/' . $name;
                        imagejpeg($nuevaimagen, $copia);
                        imagejpeg($viejaimagen, $origen);
                        imagejpeg($generalimagen, $general);
                        imagejpeg($verusuario, $verusuarios);
                    } else {
                        $viejaimagen = imagecreatefrompng($File['tmp_name']);
                        $nuevaimagen = imagecreatetruecolor($anchura, $altura);
                        $generalimagen = imagecreatetruecolor($generalx, $altura);  
                        $verusuario = imagecreatetruecolor($verusuariox, $verusuarioy);
                        imagecopyresized($nuevaimagen, $viejaimagen, 0, 0, 0, 0, $anchura, $altura, $ancho, $alto);
                        imagecopyresized($generalimagen, $viejaimagen, 0, 0, 0, 0, $generalx, $altura, $ancho, $alto);
                        imagecopyresized($verusuario, $viejaimagen, 0, 0, 0, 0, $verusuariox, $verusuarioy, $ancho, $alto);
                        $copia = $this->_options->upload->images . '/grupos/principal/' . $name;
                        $origen = $this->_options->upload->images . '/grupos/original/' . $name;
                        $general = $this->_options->upload->images . '/grupos/general/' . $name;
                         $verusuarios = $this->_options->upload->images . '/grupos/verusuario/' . $name;
                        imagepng($nuevaimagen, $copia);
                        imagepng($viejaimagen, $origen);
                        imagepng($generalimagen, $general);
                        imagejpeg($verusuario, $verusuarios);
                    }
                    
                    return true;
                }
            }
            
            return true;
        } catch (Exception $e) {
            return false;
        }
    }
    
    
    public  function facebook()       
   {  
    require './vendor/facebook/facebook.php';
               $facebook = new \Facebook(array(
                 'appId'  => $this->_options->facebook->appId,
                 'secret' => $this->_options->facebook->secret,
                 'cookie' => false ,
                 'scope'  => 'email,publish_stream'
                   ));
            $user = $facebook->getUser();
            if ($user) {
             try { $user_profile = $facebook->api('/me'); } 
             catch (FacebookApiException $e) {
                           error_log($e);
                           $user = null; } }
                       if ($user) {
                         $logoutUrl = $facebook->getLogoutUrl();
                         $id_facebook = $user_profile['id'];
                         $name = $user_profile['name'];
                         $link = $user_profile['link'];
                         $email = $user_profile['email'];
                         $naitik = $facebook->api('/naitik');
                          $generoface = $user_profile['gender'];
                         if($generoface=='male')
                          {$genero=='masculino';}
                     else{$genero=='femenino';}
                       if($user_profile==''){}
                       else
                        { $id_face=$this->getUsuarioTable()->usuariocorreo($id_facebook);  
 var_dump($id_face);exit;
                         if(count($id_face)>0)
                         {   $correo = $id_face[0]['va_email'];
                         if($id_face[0]['id_facebook']=='')  
                                { $this->getUsuarioTable()->idfacebook($id_face[0]['in_id'],$id_facebook,$logoutUrl);
                                 AuthController::sessionfacebook($correo,$id_facebook); }     
                         else{$this->getUsuarioTable()->idfacebook2($id_face[0]['in_id'],$logoutUrl);
                             AuthController::sessionfacebook($correo,$id_facebook); }}
                         else
                          { $imagen = 'https://graph.facebook.com/'.$user.'/picture';
                              $this->getUsuarioTable()->insertarusuariofacebbok($name,$email,$id_facebook,$imagen,$logoutUrl,$genero,$link); 
                              AuthController::sessionfacebook($email,$id_facebook); }
                           //  return $this->redirect()->toUrl($this->getRequest()->getBaseUrl().'/'); 
                                 }
                             
                          //  return $this->redirect()->toUrl($this->getRequest()->getBaseUrl().'/');  
                             } 
                      else {
                         // $url  = $this->redirect()->toUrl($this->getRequest()->getBaseUrl().'/');
                       $loginUrl = $facebook->getLoginUrl(array('scope'=>'email,publish_stream,read_friendlists',  
                    'redirect_uri'=>$this->_options->host->ruta.'/'
                           ));   

                       }   
                     
                 return array(
         //   'user_profile' => $user_profile,
            'user' => $user,
            'logoutUrl'  =>$logoutUrl,
            'loginUrl' => $loginUrl,
          //  'naitik' =>$naitik 
        );
      return $this->redirect()->toUrl($this->getRequest()->getBaseUrl().'/'); 
    }
    
}
