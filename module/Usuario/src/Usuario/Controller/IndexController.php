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
    
    public function agregarusuarioAction(){
      //AGREGAR CSS
      $renderer = $this->serviceLocator->get('Zend\View\Renderer\RendererInterface');
      $renderer->headLink()->prependStylesheet($this->_options->host->base .'/css/datetimepicker.css');

      //AGREGAR LIBRERIAS JAVASCRIPT EN EL FOOTER
      $renderer->inlineScript()->setScript('crearevento();')
                              ->prependFile($this->_options->host->base .'/js/main.js')
                              ->prependFile($this->_options->host->base .'/js/map/locale-es.js')
                              ->prependFile($this->_options->host->base .'/js/map/ju.google.map.js')
                              ->prependFile('https://maps.googleapis.com/maps/api/js?key=AIzaSyA2jF4dWlKJiuZ0z4MpaLL_IsjLqCs9Fhk&sensor=true')
                              ->prependFile($this->_options->host->base .'/js/map/ju.img.picker.js')
                              ->prependFile($this->_options->host->base .'/js/bootstrap-datetimepicker.js')
                              ->prependFile($this->_options->host->base .'/js/mockjax/jquery.mockjax.js')
                              ->prependFile($this->_options->host->base .'/js/bootstrap-fileupload/bootstrap-fileupload.min.js')
                              ->prependFile($this->_options->host->base .'/js/jquery.validate.min.js')
                              ->prependFile($this->_options->host->base .'/js/ckeditor/ckeditor.js');

//        $user_info = $this->getUsuarioTable()->usuariox(1);
//        var_dump($user_info);Exit;
        $adpter = $this->getServiceLocator()->get('Zend\Db\Adapter\Adapter');
//        $form = new UsuarioForm($adpter);
        $form = new UsuarioForm();
        $form->get('submit')->setValue('Crear Usuario');
//        $request = $this->getRequest();
        
//        if ($request->isPost()) {
//          $File    = $this->params()->fromFiles('va_foto');
//          $nonFile = $this->params()->fromPost('va_nombre');
//          
//            $data    = array_merge_recursive(
//                        $this->getRequest()->getPost()->toArray(),          
//                        $this->getRequest()->getFiles()->toArray()
//                        ); 
//            $usuario = new Usuario();
//            $form->setInputFilter($usuario->getInputFilter());
//            $form->setData($data);//$request->getPost()
//            if ($form->isValid()) {
//               
//                $usuario->exchangeArray($form->getData());
//                if($this->redimensionarFoto($File,$nonFile)){
//                    $this->getUsuarioTable()->guardarUusario($usuario);
//                    return $this->redirect()->toRoute('usuario');
//                }
//                else{
//                    echo 'problemas con el redimensionamiento';exit;
//                }
//
//            }else{
//                    foreach ($form->getInputFilter()->getInvalidInput() as $error) {
//                        print_r ($error->getMessages());//$inputFilter->getInvalidInput()
//                    }
//            }
//        }
 
        return array('form'=>$form);
//         return array();
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
}
