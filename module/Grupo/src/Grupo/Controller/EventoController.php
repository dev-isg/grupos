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

class EventoController extends AbstractActionController
{
    protected $eventoTable;
    protected $_options;
    public function __construct()
	{
//		$this->_options = new \Zend\Config\Config ( include APPLICATION_PATH . '/config/autoload/global.php' );
	}
        
    public function indexAction()
    {
//       $listagrupos=$this->getGrupoTable()->fetchAll();
        return array();
    }
    
    public function agregareventoAction(){
           
//        $local = (int) $this->params()->fromQuery('id');
        $adpter = $this->getServiceLocator()->get('Zend\Db\Adapter\Adapter');
        $form = new EventoForm($adpter);
        $form->get('submit')->setValue('Crear Evento');
        $request = $this->getRequest();
        
        if ($request->isPost()) {
            
//          $File    = $this->params()->fromFiles('va_imagen');
            $data    = array_merge_recursive(
                        $this->getRequest()->getPost()->toArray(),          
                        $this->getRequest()->getFiles()->toArray()
                        ); 
            $evento = new Evento();
            $form->setInputFilter($evento->getInputFilter());
            $form->setData($data);//$request->getPost()
            
            if ($form->isValid()) {
               
                $evento->exchangeArray($form->getData());
                $this->getEventoTable()->guardarEvento($evento);

                return $this->redirect()->toRoute('grupo');
            }else{
               
                    foreach ($form->getInputFilter()->getInvalidInput() as $error) {
                        print_r ($error->getMessages());//$inputFilter->getInvalidInput()
                    }
            }
        }
        return array('formevento'=>$form);
    }
    
    public function editareventoAction(){
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
            
            $data    = array_merge_recursive(
            $this->getRequest()->getPost()->toArray(),          
            $this->getRequest()->getFiles()->toArray()
            ); 
            $form->setInputFilter($grupo->getInputFilter());
            $form->setData($data);
            $notificacion = $this->params()->fromPost('tipo_notificacion', 0);
//            var_dump($form->setData($data));
            
            if ($form->isValid()) {
                
//                var_dump($grupo);
                $this->getGrupoTable()->guardarEvento($grupo,$notificacion);
                return $this->redirect()->toRoute('grupo');
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
    
    public function eliminareventoAction(){
        
    }
    
     public function uploadAction(){
         
         
     }

    public function fooAction()
    {
        // This shows the :controller and :action parameters in default route
        // are working when you browse to /module-specific-root/skeleton/foo
        return array();
    }
    
        public function getEventoTable() {
        if (!$this->eventoTable) {
            $sm = $this->getServiceLocator();
            $this->eventoTable = $sm->get('Grupo\Model\EventoTable');
        }
        return $this->eventoTable;
    }
}
