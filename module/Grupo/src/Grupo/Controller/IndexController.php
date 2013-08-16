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

class IndexController extends AbstractActionController
{

    protected $grupoTable;

    protected $usuarioTable;

    protected $authservice;

    protected $_options;

    public function __construct()
    {
        $this->_options = new \Zend\Config\Config(include APPLICATION_PATH . '/config/autoload/global.php');
    }

    public function indexAction()
    {
        $renderer = $this->serviceLocator->get('Zend\View\Renderer\RendererInterface');
        $renderer->inlineScript()->prependFile($this->_options->host->base . '/js/main.js');      
        $categorias = $this->getGrupoTable()->tipoCategoria();
        $this->layout()->categoria = $categorias;
        $buscar = $this->params()->fromPost('dato');
        $filter = new \Zend\I18n\Filter\Alnum(true);
        $nombre = trim($filter->filter($buscar));
        setcookie('dato', $nombre);
        $submit = $this->params()->fromPost('submit');
        $valor = $this->params()->fromQuery('tipo');
        $tipo = $this->params()->fromQuery('categoria');
        $request = $this->getRequest();
        if (empty($valor) and empty($tipo) and ! $request->isPost()) {
            $listaEventos = $this->getEventoTable()->listadoEvento();}
        if ($request->isPost()) {
            if ($nombre) {
                $grupo = $this->getGrupoTable()->buscarGrupo($nombre);
                $listaEventos = $this->getEventoTable()->listado2Evento($nombre);
                if(count($grupo)===0 and count($listaEventos)===0)
                { return $this->redirect()->toUrl($this->getRequest()->getBaseUrl().'/'); }
                if (count($grupo)>0) {
                    $listagrupos = $this->getGrupoTable()->buscarGrupo($nombre);
               } else { $listaEventos = $this->getEventoTable()->listado2Evento($nombre); }} }
        if ($tipo) {
            if ($tipo) {
                $listagrupos = $this->getGrupoTable()->buscarGrupo(null, $tipo);
            } else { $listagrupos = $this->getGrupoTable()->fetchAll();  }  }
        if ($valor) {
            if ($valor == 'Grupos') {
                $listagrupos = $this->getGrupoTable()->fetchAll();
            } else {  $listaEventos = $this->getEventoTable()->listadoEvento(); } }
        if(count($listaEventos)>0)
        { $paginator = new \Zend\Paginator\Paginator(new \Zend\Paginator\Adapter\Iterator($listaEventos));
        $paginator->setCurrentPageNumber((int) $this->params()->fromQuery('page', 1));
        $paginator->setItemCountPerPage(12);}
        else{
        $paginator2 = new \Zend\Paginator\Paginator(new \Zend\Paginator\Adapter\Iterator($listagrupos));
        $paginator2->setCurrentPageNumber((int) $this->params()->fromQuery('page', 1));
        $paginator2->setItemCountPerPage(12);}
        return array(
            'grupos' => $paginator2,
            'eventos' => $paginator,
            'dato' => $valor );
    }

    public function getEventoTable()
    {
        if (! $this->eventoTable) {
            $sm = $this->getServiceLocator();
            $this->eventoTable = $sm->get('Grupo\Model\EventoTable');
        }
        return $this->eventoTable;
    }

    public function agregargrupoAction()
    {
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
        $user_info = $this->getGrupoTable()->usuarioxGrupo($storage->read()->in_id);
        // var_dump($user_info);Exit;
        $adpter = $this->getServiceLocator()->get('Zend\Db\Adapter\Adapter');
        $form = new GruposForm($adpter);
        $form->get('submit')->setValue('Crear Grupo');
        $request = $this->getRequest();
        
        if ($request->isPost()) {
            $File = $this->params()->fromFiles('va_imagen');
            $nonFile = $this->params()->fromPost('va_nombre');
            
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
                if ($this->redimensionarImagen($File, $nonFile)) {
                    // obtiene el identity y consulta el
                    $this->getGrupoTable()->guardarGrupo($grupo, $notificacion, $storage->read()->in_id);
                    return $this->redirect()->toRoute('grupo');
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
        $invoiceWidget = $this->forward()->dispatch('Grupo\Controller\Evento', array(
            'action' => 'agregarevento'
        ));
        
        $mainViewModel->addChild($invoiceWidget, 'invoiceWidget');
        return $mainViewModel->setVariables(array(
            'form' => $form,
            'grupos' => $user_info
        ));
        // return array('form'=>$form,'grupos'=>$user_info);
    }

    public function editargrupoAction()
    {
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
        
        // $var=$this->getGrupoTable()->getNotifiaciones($id)->toArray();
        // $aux = array();
        // foreach($var as $y){
        // $aux[]=$y['ta_notificacion_in_id'];
        // }
        // $form->get('tipo_notificacion')->setValue($aux);
        
        $form->get('submit')->setAttribute('value', 'Editar');
        
        $request = $this->getRequest();
        
        if ($request->isPost()) {
            $File = $this->params()->fromFiles('va_imagen');
            $nonFile = $this->params()->fromPost('va_nombre');
            
            $data = array_merge_recursive($this->getRequest()
                ->getPost()
                ->toArray(), $this->getRequest()
                ->getFiles()
                ->toArray());
            $form->setInputFilter($grupo->getInputFilter());
            $form->setData($data);
            $notificacion = $this->params()->fromPost('tipo_notificacion', 0);
            // var_dump($form->setData($data));
            
            if ($form->isValid()) {
                if ($this->redimensionarImagen($File, $nonFile)) {
                    $this->getGrupoTable()->guardarGrupo($grupo, $notificacion);
                    return $this->redirect()->toRoute('grupo');
                } else {
                    echo 'problemas con el redimensionamiento';
                    exit();
                }
            } else {
                // var_dump($form->isValid());
                foreach ($form->getInputFilter()->getInvalidInput() as $error) {
                    print_r($error->getMessages());
                }
            }
        }
        
        return array(
            'in_id' => $id,
            'form' => $form
        );
    }

    public function eliminargrupoAction()
    {}

    public function detallegrupoAction()
    {
        $id = $this->params()->fromRoute('in_id');
        $grupo = $this->getEventoTable()->grupoid($id);
        $eventospasados = $this->getEventoTable()->eventospasados($id);
        $eventosfuturos = $this->getEventoTable()->eventosfuturos($id);
        $usuarios = $this->getGrupoTable()->usuariosgrupo($id);
        $proximos_eventos = $this->getGrupoTable()->eventosgrupo($id);
        
        $storage = new \Zend\Authentication\Storage\Session('Auth');
        $session=$storage->read();
        if ($session) {            
            $participa=$this->getGrupoTable()->compruebarUsuarioxGrupo($session->in_id,$id);
            $activo=$participa->va_estado=='activo'?true:false;
 
        }
        return array(
            'grupo' => $grupo,
            'eventosfuturos' => $eventosfuturos,
            'eventospasados' => $eventospasados,
            'usuarios' => $usuarios,
            'proximos_eventos' => $proximos_eventos,
            'in_id'=>$id,
            'session'=>$session,
            'participa'=>$activo
        );
    }

    public function unirAction()
    {
        $storage = new \Zend\Authentication\Storage\Session('Auth');
        if (! $storage) {
            return $this->redirect()->toRoute('grupo');
        }
        
        $iduser = $storage->read()->in_id; // 1;
        $idgrup = $this->params()->fromQuery('idG'); // 48;
        $unir = $this->params()->fromQuery('act');
        if ($unir == 1) {
            if ($this->getGrupoTable()->unirseGrupo($idgrup, $iduser)) {
                    // $user_info = $this->getGrupoTable()->usuarioxGrupo(1);
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
        } elseif ($unir == 0) {
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
                    $this->mensaje($storage->read()->va_email, $bodyHtml, 'Ha dejado un grupo');
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
//                     $message = new Message();
//                     $message->addTo('ola@yopmail.com', $nombre)
//                         ->setFrom('listadelsabor@innovationssystems.com', 'listadelsabor.com')
//                         ->setSubject('Ha dejado un grupo');
//                     // ->setBody($bodyHtml);
//                     $bodyPart = new \Zend\Mime\Message();
//                     $bodyMessage = new \Zend\Mime\Part($bodyHtml);
//                     $bodyMessage->type = 'text/html';
//                     $bodyPart->setParts(array(
//                         $bodyMessage
//                     ));
//                     $message->setBody($bodyPart);
//                     $message->setEncoding('UTF-8');
                    
//                     $transport = $this->getServiceLocator()->get('mail.transport');
//                     $transport->send($message);
//                     $this->redirect()->toUrl('/grupo');
                }
            }
       return array();
    }
    
    
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
