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

class IndexController extends AbstractActionController
{

    protected $grupoTable;
   // protected $categorias;
    protected $usuarioTable;

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
                ->prependFile($this->_options->host->base . '/js/masonry/post-like.js')
                ->prependFile($this->_options->host->base . '/js/masonry/superfish.js')
                ->prependFile($this->_options->host->base . '/js/masonry/prettify.js')
                ->prependFile($this->_options->host->base . '/js/masonry/retina.js')
                ->prependFile($this->_options->host->base . '/js/masonry/jquery.masonry.min.js')
                ->prependFile($this->_options->host->base . '/js/masonry/jquery.infinitescroll.min.js')
                ->prependFile($this->_options->host->base . '/js/masonry/custom.js')
                ->prependFile($this->_options->host->base . '/js/jquery.validate.min.js');
        $categorias = $this->categorias();
        $this->layout()->categorias = $categorias;
        $buscar = $this->params()->fromPost('dato');
        $filter = new \Zend\I18n\Filter\Alnum(true);
        $nombre = trim($filter->filter($buscar));


        setcookie('dato', $nombre);
        $submit = $this->params()->fromPost('submit');

        $valor = $this->params()->fromQuery('tipo');
        setcookie('tipo', $valor);
        $tipo = $this->params()->fromQuery('categoria');
        $this->params()->fromQuery('nombre');

        $rango = $this->params()->fromQuery('valor');
        $request = $this->getRequest();

        if (empty($valor) and empty($tipo) and !$request->isPost()) {

            $listaEventos = $this->getEventoTable()->listadoEvento();
        }
        if ($request->isPost()) {
            if ($nombre) {
                $grupo = $this->getGrupoTable()->buscarGrupo($nombre);
                if (count($grupo) > 0) {
                    $listagrupos = $this->getGrupoTable()->buscarGrupo($nombre);
                } else {
                    $listaEventos = $this->getEventoTable()->listado2Evento($nombre);

                    if (count($listaEventos) > 0) {
                        $listaEventos = $this->getEventoTable()->listado2Evento($nombre);
                    } else {
                        return $this->redirect()->toUrl($this->getRequest()->getBaseUrl() . '/');
                    }
                }
            } else {
                return $this->redirect()->toUrl($this->getRequest()->getBaseUrl() . '/');
            }
        }

        if ($tipo) {//var_dump($rango);exit;
            if (!empty($rango)) {
                if ($rango == 'Grupos') {
                    $listagrupos = $this->getGrupoTable()->buscarGrupo(null, $tipo);
                    if (count($listagrupos) <= 0) {
                        return $this->redirect()->toUrl($this->getRequest()->getBaseUrl() . '/');
                    }
                } else {
                    $listaEventos = $this->getEventoTable()->eventocategoria($tipo);
                    if (count($listaEventos) <= 0) {
                        return $this->redirect()->toUrl($this->getRequest()->getBaseUrl() . '/');
                    }
                }
            } else {
                $listagrupos = $this->getGrupoTable()->buscarGrupo(null, $tipo);
            }
        }
        if ($valor) {
            if ($valor == 'Grupos') {
                $listagrupos = $this->getGrupoTable()->fetchAll();
            } else {
                $listaEventos = $this->getEventoTable()->listadoEvento();
            }
        }
        if (count($listaEventos) > 0) {
            $paginator = new \Zend\Paginator\Paginator(new \Zend\Paginator\Adapter\Iterator($listaEventos));
            $paginator->setCurrentPageNumber((int) $this->params()->fromQuery('page', 1));
            $paginator->setItemCountPerPage(12);
        } elseif (count($listagrupos) > 0) { //echo 'we';exit;
            $paginator2 = new \Zend\Paginator\Paginator(new \Zend\Paginator\Adapter\Iterator($listagrupos));
            $paginator2->setCurrentPageNumber((int) $this->params()->fromQuery('page', 1));
            $paginator2->setItemCountPerPage(12);
        } else {
            return $this->redirect()->toUrl($this->getRequest()->getBaseUrl() . '/auth');
        }
//var_dump($listagrupos->toArray());exit;
        return array(
            'grupos' => $paginator2,
            'eventos' => $paginator,
            'dato' => $valor
        );
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
            ->prependFile($this->_options->host->base . '/js/bootstrap-datetimepicker.js')
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
            ->setScript('$(document).ready(function(){crearevento();});')
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
        $user_info = $this->getGrupoTable()->misgrupos($storage->read()->in_id);//usuarioxGrupo($storage->read()->in_id);
        // var_dump($user_info);Exit;
        $adpter = $this->getServiceLocator()->get('Zend\Db\Adapter\Adapter');
        $form = new GruposForm($adpter);
        $form->get('submit')->setValue('Crear Grupo');
        $request = $this->getRequest();
        
        $urlorigen=$this->getRequest()->getHeader('Referer')->uri()->getPath();
       
        if ($request->isPost()) {

            $File = $this->params()->fromFiles('va_imagen');
            $nonFile = $this->params()->fromPost('va_nombre');
  
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
                if ($this->redimensionarImagen($File, $nonFile,$imagen)) {
                    // obtiene el identity y consulta el
                   $idgrupo=$this->getGrupoTable()->guardarGrupo($grupo, $notificacion, $storage->read()->in_id,$imagen);
                   $this->flashMessenger()->addMessage('Su grupo ha sido registrado correctamente');
                   if($this->params()->fromPost('url')=='/usuario/index/misgrupos' ||$this->params()->fromPost('url')=='/cuenta/grupoparticipo'){
                       return $this->redirect()->toRoute('detalle-grupo',array('in_id'=>$idgrupo));
                       
                   }else{
                       return $this->redirect()->toRoute('agregar-evento',array('in_id'=>$idgrupo));
                   }
                    
//                    $invoiceWidget = $this->forward()->dispatch('Grupo\Controller\Evento', array( 
//                                'action' => 'agregarevento'
//                            ));
//                     $mainViewModel->addChild($invoiceWidget, 'invoiceWidget');
                    
                } else {
                    echo 'problemas con el redimensionamiento';
                    exit();
                }
            } else {
                foreach ($form->getInputFilter()->getInvalidInput() as $error) {
                    print_r($error->getMessages()); // $inputFilter->getInvalidInput()
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
            ->setScript('$(document).ready(function(){crearevento();});')
            ->prependFile($this->_options->host->base . '/js/main.js')
            ->prependFile($this->_options->host->base . '/js/bootstrap-fileupload/bootstrap-fileupload.min.js');
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
        $form->bind($grupo);
//        var_dump($grupo->va_imagen);Exit;
        // $var=$this->getGrupoTable()->getNotifiaciones($id)->toArray();
        // $aux = array();
        // foreach($var as $y){
        // $aux[]=$y['ta_notificacion_in_id'];
        // }
        // $form->get('tipo_notificacion')->setValue($aux);
//        $form->get('va_imagen')->setAttribute('value', '');
        $form->get('submit')->setAttribute('value', 'Editar');
        $imagen=$this->_options->host->images.'/grupos/general/'.$grupo->va_imagen;
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
//            var_dump($data);Exit;
            if ($form->isValid()) {
                if ($this->redimensionarImagen($File, $nonFile,$imagen)) {
                    $this->getGrupoTable()->guardarGrupo($grupo, $notificacion,$storage->read()->in_id,$imagen);
                    $this->flashMessenger()->addMessage('Grupo editado correctamente');
                    return $this->redirect()->toRoute('detalle-grupo',array('in_id'=>$id));
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
        
//        $flashMessenger = $this->flashMessenger();
//        if ($flashMessenger->hasMessages()) {
//            $mensajes = $flashMessenger->getMessages();
//        }
        
        return array(
            'in_id' => $id,
            'form' => $form,
            'imagen'=>$imagen
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
        $eventospasados = $this->getEventoTable()->eventospasados($id);
        $eventosfuturos = $this->getEventoTable()->eventosfuturos($id);
        $usuarios = $this->getGrupoTable()->usuariosgrupo($id);
        $proximos_eventos = $this->getGrupoTable()->eventosgrupo($id);
        $paginator2 = new \Zend\Paginator\Paginator(new \Zend\Paginator\Adapter\Iterator($proximos_eventos));
        $paginator2->setCurrentPageNumber((int) $this->params()->fromQuery('page', 1));
        $paginator2->setItemCountPerPage(4); 
        $storage = new \Zend\Authentication\Storage\Session('Auth');
        $session=$storage->read();
        if ($session) {            
            $participa=$this->getGrupoTable()->compruebarUsuarioxGrupo($session->in_id,$id);
            $activo=$participa->va_estado=='activo'?true:false;
        }
        
        $flashMessenger = $this->flashMessenger();
        if ($flashMessenger->hasMessages()) {
            $mensajes = $flashMessenger->getMessages();
        }
        
        return array(
            'grupo' => $grupo,
            'eventosfuturos' => $eventosfuturos,
            'eventospasados' => $eventospasados,
            'usuarios' => $usuarios,
            'proximos_eventos' => $paginator2,
            'session'=>$session,
            'in_id'=>$id,
            'participa'=>$activo,
            'mensajes'=>$mensajes
        );
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

    public function unirAction()
    {
        $storage = new \Zend\Authentication\Storage\Session('Auth');
        if (! $storage) {
            return $this->redirect()->toRoute('grupo');
        }
        
        $iduser = $storage->read()->in_id; // 1;
        $idgrup = $this->params()->fromQuery('idE'); // 48;
        $unir = $this->params()->fromQuery('act');
        $idusergrup=$this->getGrupoTable()->getGrupo($idgrup)->ta_usuario_in_id;
        
       $usuariosesion = $this->getGrupoTable()->getNotifiacionesxUsuario($iduser)->toArray();
       $usuariocrear= $this->getGrupoTable()->getNotifiacionesxUsuario($idusergrup)->toArray();
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
            if ($this->getGrupoTable()->unirseGrupo($idgrup, $iduser)) {           
               if($correoe){
                $user_info['nom_grup'] = $this->getGrupoTable()->getGrupo($idgrup)->va_nombre;
                $bodyHtml = '<!DOCTYPE html><html xmlns="http://www.w3.org/1999/xhtml">
                                               <head>
                                               <meta http-equiv="Content-type" content="text/html;charset=UTF-8"/>
                                               </head>
                                               <body>
                                                    <div style="color: #7D7D7D"><br />
                                                     Uds. se ha unido al grupo <strong style="color:#133088; font-weight: bold;">' . utf8_decode($user_info['nom_grup']) . '</strong><br />
    
                                                     </div>
                                               </body>
                                               </html>';
                
                $this->mensaje($storage->read()->va_email, $bodyHtml, 'Se ha unido al grupo');
               }
                if($correoec){
                $usuario = $this->getGrupoTable()
                    ->grupoxUsuario($idgrup)
                    ->toArray();
                $bodyHtmlAdmin= '<!DOCTYPE html><html xmlns="http://www.w3.org/1999/xhtml">
                                               <head>
                                               <meta http-equiv="Content-type" content="text/html;charset=UTF-8"/>
                                               </head>
                                               <body>
                                                    <div style="color: #7D7D7D"><br />
                                                     El siguiente usuario se ha unido a tu grupo <strong style="color:#133088; font-weight: bold;">' . utf8_decode($user_info['nom_grup']) . '</strong><br />
    
                                                     </div>
                                               </body>
                                               </html>';
                if ($usuario) {
                    $this->mensaje($usuario[0]['va_email'], $bodyHtmlAdmin, 'Se unieron a tu grupo');
                }
               }
                $activo=1;
                $userestado=$this->getGrupoTable()->usuariosgrupo($idgrup, $iduser);//getGrupoUsuario($idgrup, $iduser);
              }
        
        } elseif ($unir == 0) {
                if ($this->getGrupoTable()->retiraGrupo($idgrup, $iduser)) {
                    if($correos){
                    $user_info['nom_grup'] = $this->getGrupoTable()->getGrupo($idgrup)->va_nombre;
                    $bodyHtml = '<!DOCTYPE html><html xmlns="http://www.w3.org/1999/xhtml">
                                               <head>
                                               <meta http-equiv="Content-type" content="text/html;charset=UTF-8"/>
                                               </head>
                                               <body>
                                                    <div style="color: #7D7D7D"><br />
                                                     Uds. se ha dejado al grupo <strong style="color:#133088; font-weight: bold;">' . utf8_decode($user_info['nom_grup']) . '</strong><br />
            
                                                     </div>
                                               </body>
                                               </html>';
                    $this->mensaje($storage->read()->va_email, $bodyHtml, 'Ha dejado un grupo');
                    }
                    if($correosc){
                    $usuario = $this->getGrupoTable()
                    ->grupoxUsuario($idgrup)
                    ->toArray();
                    $bodyHtmlAdmin= '<!DOCTYPE html><html xmlns="http://www.w3.org/1999/xhtml">
                                               <head>
                                               <meta http-equiv="Content-type" content="text/html;charset=UTF-8"/>
                                               </head>
                                               <body>
                                                    <div style="color: #7D7D7D"><br />
                                                     El siguiente usuario ha abandonado a tu grupo <strong style="color:#133088; font-weight: bold;">' . utf8_decode($storage->read()->va_nombre) . '</strong><br />
                    
                                                     </div>
                                               </body>
                                               </html>';
                    if ($usuario) {
                        $this->mensaje($usuario[0]['va_email'], $bodyHtmlAdmin, 'Dejaron a tu grupo');
                    }
                    }
                     $activo=0;
                     $userestado=$this->getGrupoTable()->usuariosgrupo($idgrup, $iduser); //getGrupoUsuario($idgrup, $iduser);
                    }               
            }
           $userestado=$userestado->current();
           $arruser['id']=$iduser;
           setlocale(LC_TIME, "es_ES.UTF-8"); 
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
                                                     Uds. se ha dejado al grupo <strong style="color:#133088; font-weight: bold;">' . utf8_decode($user_info['nom_grup']) . '</strong><br />
                                              
                                                     </div>
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

    private function redimensionarImagen($File, $nonFile,$imagen)
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
                        imagecopyresized($nuevaimagen, $viejaimagen, 0, 0, 0, 0, $anchura, $altura, $ancho, $alto);
                        imagecopyresized($generalimagen, $viejaimagen, 0, 0, 0, 0, $generalx, $altura, $ancho, $alto);
                        $copia = $this->_options->upload->images . '/grupos/principal/' . $name;
                        $origen = $this->_options->upload->images . '/grupos/original/' . $name;
                        $general = $this->_options->upload->images . '/grupos/general/' . $name;
                        imagejpeg($nuevaimagen, $copia);
                        imagejpeg($viejaimagen, $origen);
                        imagejpeg($generalimagen, $general);
                    } else {
                        $viejaimagen = imagecreatefrompng($File['tmp_name']);
                        $nuevaimagen = imagecreatetruecolor($anchura, $altura);
                        $generalimagen = imagecreatetruecolor($generalx, $altura);
                        imagecopyresized($nuevaimagen, $viejaimagen, 0, 0, 0, 0, $anchura, $altura, $ancho, $alto);
                        imagecopyresized($generalimagen, $viejaimagen, 0, 0, 0, 0, $generalx, $altura, $ancho, $alto);
                        $copia = $this->_options->upload->images . '/grupos/principal/' . $name;
                        $origen = $this->_options->upload->images . '/grupos/original/' . $name;
                        $general = $this->_options->upload->images . '/grupos/general/' . $name;
                        imagepng($nuevaimagen, $copia);
                        imagepng($viejaimagen, $origen);
                        imagepng($generalimagen, $general);
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
                        imagecopyresized($nuevaimagen, $viejaimagen, 0, 0, 0, 0, $anchura, $altura, $ancho, $alto);
                        imagecopyresized($generalimagen, $viejaimagen, 0, 0, 0, 0, $generalx, $altura, $ancho, $alto);
                        $copia = $this->_options->upload->images . '/grupos/principal/' . $name;
                        $origen = $this->_options->upload->images . '/grupos/original/' . $name;
                        $general = $this->_options->upload->images . '/grupos/general/' . $name;
                        imagejpeg($nuevaimagen, $copia);
                        imagejpeg($viejaimagen, $origen);
                        imagejpeg($generalimagen, $general);
                    } else {
                        $viejaimagen = imagecreatefrompng($File['tmp_name']);
                        $nuevaimagen = imagecreatetruecolor($anchura, $altura);
                        $generalimagen = imagecreatetruecolor($generalx, $altura);
                        imagecopyresized($nuevaimagen, $viejaimagen, 0, 0, 0, 0, $anchura, $altura, $ancho, $alto);
                        imagecopyresized($generalimagen, $viejaimagen, 0, 0, 0, 0, $generalx, $altura, $ancho, $alto);
                        $copia = $this->_options->upload->images . '/grupos/principal/' . $name;
                        $origen = $this->_options->upload->images . '/grupos/original/' . $name;
                        $general = $this->_options->upload->images . '/grupos/general/' . $name;
                        imagepng($nuevaimagen, $copia);
                        imagepng($viejaimagen, $origen);
                        imagepng($generalimagen, $general);
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
