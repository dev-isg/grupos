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
use Zend\Mail\Message;

class IndexController extends AbstractActionController
{
    protected $grupoTable;
    protected $_options;
    public function __construct()
	{
		$this->_options = new \Zend\Config\Config ( include APPLICATION_PATH . '/config/autoload/global.php' );
	}
        
    public function indexAction()
    {
//   $container = new \Zend\Session\Container('Grupo\Controller');
//    $container->idgrupo = $this->getGrupoTable()->usuarioxGrupo(1);
        $listagrupos=$this->getGrupoTable()->fetchAll();
        $categorias=$this->getGrupoTable()->tipoCategoria();
//        var_dump($categorias);exit;
        $submit=$this->params()->fromPost('submit');
        $tipo=$this->params()->fromQuery('categoria');
        $nombre=$this->params()->fromPost('nombre');
        
       $renderer = $this->serviceLocator->get('Zend\View\Renderer\RendererInterface');

        $renderer->headScript()->appendFile('/js/somejs.js');//$renderer->baseUrl() .
        

        
        if(isset($submit) || isset($tipo)){
         if($tipo){
            $listagrupos=$this->getGrupoTable()->buscarGrupo(null,$tipo);
  

        }else if($nombre){
            $listagrupos=$this->getGrupoTable()->buscarGrupo($nombre);
        }
         
        }


        return array('grupos'=>$listagrupos,'categorias'=>$categorias);
    }
    
    public function agregargrupoAction(){
           
//        $local = (int) $this->params()->fromQuery('id');
        $adpter = $this->getServiceLocator()->get('Zend\Db\Adapter\Adapter');
        $form = new GruposForm($adpter);
        $form->get('submit')->setValue('Crear Grupo');
        $request = $this->getRequest();
        
        if ($request->isPost()) {
          $File    = $this->params()->fromFiles('va_imagen');
          $nonFile = $this->params()->fromPost('va_nombre');
          
            $data    = array_merge_recursive(
                        $this->getRequest()->getPost()->toArray(),          
                        $this->getRequest()->getFiles()->toArray()
                        ); 
            $grupo = new Grupo();
            $form->setInputFilter($grupo->getInputFilter());
            $form->setData($data);//$request->getPost()
            $notificacion = $this->params()->fromPost('tipo_notificacion', 0);
            if ($form->isValid()) {
               
                $grupo->exchangeArray($form->getData());
                if($this->redimensionarImagen($File,$nonFile)){
                    $this->getGrupoTable()->guardarGrupo($grupo,$notificacion);
                    return $this->redirect()->toRoute('grupo');
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
    }
    
    public function editargrupoAction(){
       $id = (int) $this->params()->fromRoute('in_id', 0);    
        if (!$id) {
            return $this->redirect()->toRoute('grupo', array(
                'action' => 'agregargrupo'
            ));
        }
        
        try {
            $grupo = $this->getGrupoTable()->getGrupo($id);
        }
        catch (\Exception $ex) {
            
            return $this->redirect()->toRoute('grupo', array(
                'action' => 'index'
            ));
        }

        $adpter = $this->getServiceLocator()->get('Zend\Db\Adapter\Adapter');
        $form = new GruposForm($adpter);
        $form->bind($grupo);
        
        $var=$this->getGrupoTable()->getNotifiaciones($id)->toArray();
        $aux = array();
        foreach($var as $y){
            $aux[]=$y['ta_notificacion_in_id'];
        }
        $form->get('tipo_notificacion')->setValue($aux);
        
        $form->get('submit')->setAttribute('value', 'Editar');
        
        $request = $this->getRequest();
        
        if ($request->isPost()) {
            $File    = $this->params()->fromFiles('va_imagen');
            $nonFile = $this->params()->fromPost('va_nombre');
            
            $data    = array_merge_recursive(
            $this->getRequest()->getPost()->toArray(),          
            $this->getRequest()->getFiles()->toArray()
            ); 
            $form->setInputFilter($grupo->getInputFilter());
            $form->setData($data);
            $notificacion = $this->params()->fromPost('tipo_notificacion', 0);
//            var_dump($form->setData($data));
            
            if ($form->isValid()) {
                if($this->redimensionarImagen($File,$nonFile)){
                    $this->getGrupoTable()->guardarGrupo($grupo,$notificacion);
                    return $this->redirect()->toRoute('grupo');
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
            'form' => $form,
        );
        
    }
    
    public function eliminargrupoAction(){
        
    }
    
    public function unirAction(){
        $iduser=1;
        $idgrup=48;
      if($this->getGrupoTable()->unirseGrupo($idgrup,$iduser)){
//                  $user_info = $this->getGrupoTable()->usuarioxGrupo(1);
                  $user_info['nom_grup']=  $this->getGrupoTable()->getGrupo($idgrup)->va_nombre;
                  $bodyHtml='<!DOCTYPE html><html xmlns="http://www.w3.org/1999/xhtml">
                                               <head>
                                               <meta http-equiv="Content-type" content="text/html;charset=UTF-8"/>
                                               </head>
                                               <body>
                                                    <div style="color: #7D7D7D"><br />
                                                     Uds. se ha unido al grupo <strong style="color:#133088; font-weight: bold;">'.utf8_decode($user_info['nom_grup']).'</strong><br />
                                              
                                                     </div>
                                               </body>
                                               </html>';
    
        $message = new Message();
        $message->addTo('ola@yopmail.com', $nombre)
        ->setFrom('listadelsabor@innovationssystems.com', 'listadelsabor.com')
        ->setSubject('Se ha unido al grupo');
        //->setBody($bodyHtml);
            $bodyPart = new \Zend\Mime\Message();
            $bodyMessage = new \Zend\Mime\Part($bodyHtml);
            $bodyMessage->type = 'text/html';
            $bodyPart->setParts(array($bodyMessage));
            $message->setBody($bodyPart);
            $message->setEncoding('UTF-8');
            
        $transport = $this->getServiceLocator()->get('mail.transport');//new SendmailTransport();//$this->getServiceLocator('mail.transport')
        $transport->send($message);
        $this->redirect()->toUrl('/grupo');
      } 
    }
    
    public function dejarAction(){
        $iduser=1;
        $idgrup=50;
      if( $this->getGrupoTable()->retiraGrupo($idgrup,$iduser)){
                  $user_info['nom_grup']=  $this->getGrupoTable()->getGrupo($idgrup)->va_nombre;
                  $bodyHtml='<!DOCTYPE html><html xmlns="http://www.w3.org/1999/xhtml">
                                               <head>
                                               <meta http-equiv="Content-type" content="text/html;charset=UTF-8"/>
                                               </head>
                                               <body>
                                                    <div style="color: #7D7D7D"><br />
                                                     Uds. se ha dejado al grupo <strong style="color:#133088; font-weight: bold;">'.utf8_decode($user_info['nom_grup']).'</strong><br />
                                              
                                                     </div>
                                               </body>
                                               </html>';
    
        $message = new Message();
        $message->addTo('ola@yopmail.com', $nombre)
        ->setFrom('listadelsabor@innovationssystems.com', 'listadelsabor.com')
        ->setSubject('Ha dejado un grupo');
        //->setBody($bodyHtml);
            $bodyPart = new \Zend\Mime\Message();
            $bodyMessage = new \Zend\Mime\Part($bodyHtml);
            $bodyMessage->type = 'text/html';
            $bodyPart->setParts(array($bodyMessage));
            $message->setBody($bodyPart);
            $message->setEncoding('UTF-8');
            
        $transport = $this->getServiceLocator()->get('mail.transport');
        $transport->send($message);
        $this->redirect()->toUrl('/grupo');
      } 
    }
     public function uploadAction(){
         
         
     }

    public function fooAction()
    {
        // This shows the :controller and :action parameters in default route
        // are working when you browse to /module-specific-root/skeleton/foo
        return array();
    }
    
        public function getGrupoTable() {
        if (!$this->grupoTable) {
            $sm = $this->getServiceLocator();
            $this->grupoTable = $sm->get('Grupo\Model\GrupoTable');
        }
        return $this->grupoTable;
    }
    
    private function redimensionarImagen($File,$nonFile){
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
                            $viejaimagen=  imagecreatefromjpeg($File['tmp_name']);
                            $nuevaimagen = imagecreatetruecolor($anchura, $altura);
                            $generalimagen = imagecreatetruecolor($generalx, $altura);
                            imagecopyresized($nuevaimagen, $viejaimagen, 0, 0, 0, 0, $anchura, $altura, $ancho, $alto);
                            imagecopyresized($generalimagen, $viejaimagen, 0, 0, 0, 0, $generalx, $altura, $ancho, $alto);
                            $copia = $this->_options->upload->images . '/grupos/principal/' . $name;
                            $origen = $this->_options->upload->images . '/grupos/original/' . $name;
                            $general=$this->_options->upload->images . '/grupos/general/' . $name;
                                 imagejpeg($nuevaimagen,$copia);
                                 imagejpeg($viejaimagen,$origen);
                                 imagejpeg($generalimagen,$general);
                       }else{
                            $viejaimagen=  imagecreatefrompng($File['tmp_name']);
                           $nuevaimagen = imagecreatetruecolor($anchura, $altura);
                           $generalimagen = imagecreatetruecolor($generalx, $altura);
                            imagecopyresized($nuevaimagen, $viejaimagen, 0, 0, 0, 0, $anchura, $altura, $ancho, $alto);
                            imagecopyresized($generalimagen, $viejaimagen, 0, 0, 0, 0, $generalx, $altura, $ancho, $alto);
                            $copia = $this->_options->upload->images . '/grupos/principal/' . $name;
                            $origen = $this->_options->upload->images . '/grupos/original/' . $name;
                            $general=$this->_options->upload->images . '/grupos/general/' . $name;
                                 imagepng($nuevaimagen,$copia);
                                 imagepng($viejaimagen,$origen);
                                 imagepng($generalimagen,$general);
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
                            $viejaimagen=  imagecreatefromjpeg($File['tmp_name']);
                            $nuevaimagen = imagecreatetruecolor($anchura, $altura);
                            $generalimagen = imagecreatetruecolor($generalx, $altura);
                            imagecopyresized($nuevaimagen, $viejaimagen, 0, 0, 0, 0, $anchura, $altura, $ancho, $alto);
                            imagecopyresized($generalimagen, $viejaimagen, 0, 0, 0, 0, $generalx, $altura, $ancho, $alto);
                            $copia = $this->_options->upload->images . '/grupos/principal/' . $name;
                            $origen = $this->_options->upload->images . '/grupos/original/' . $name;
                            $general=$this->_options->upload->images . '/grupos/general/' . $name;
                                 imagejpeg($nuevaimagen,$copia);
                                 imagejpeg($viejaimagen,$origen);
                                 imagejpeg($generalimagen,$general);
                       }else{
                            $viejaimagen=  imagecreatefrompng($File['tmp_name']);
                           $nuevaimagen = imagecreatetruecolor($anchura, $altura);
                           $generalimagen = imagecreatetruecolor($generalx, $altura);
                            imagecopyresized($nuevaimagen, $viejaimagen, 0, 0, 0, 0, $anchura, $altura, $ancho, $alto);
                            imagecopyresized($generalimagen, $viejaimagen, 0, 0, 0, 0, $generalx, $altura, $ancho, $alto);
                            $copia = $this->_options->upload->images . '/grupos/principal/' . $name;
                            $origen = $this->_options->upload->images . '/grupos/original/' . $name;
                            $general=$this->_options->upload->images . '/grupos/general/' . $name;
                                 imagepng($nuevaimagen,$copia);
                                 imagepng($viejaimagen,$origen);
                                 imagepng($generalimagen,$general);
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
