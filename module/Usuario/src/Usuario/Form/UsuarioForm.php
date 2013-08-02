<?php

namespace Usuario\Form;

use Zend\Form\Form;
use Zend\Db\Sql\Sql;
use Zend\Db\Adapter\AdapterInterface;

use Zend\ServiceManager\ServiceManager;
use Zend\ServiceManager\ServiceManagerAwareInterface;


class UsuarioForm extends Form{
    
    
    public function __construct(AdapterInterface $dbAdapter=null)//$name = null
    {
//        $this->setDbAdapter($dbAdapter);
//        $this->setId($id);
        parent::__construct('usuario');
        $this->setAttribute('method', 'post');
        $this->setAttribute('endtype', 'multipart/form-data');
        
        
       $this->add(array(
            'name' => 'in_id',
            'type' => 'Hidden',
           'attributes' => array(               
                'id'   => 'in_id',         
            ),
        ));
       
        $this->add(array(
            'name' => 'va_nombre',
            'type' => 'Text',
          
            'options' => array(
                'label' => '',          
            ),
            'attributes' => array(               
                'class' => 'span12',
                'placeholder'=>'Ingrese el nombre del usuario…'
            ),
        ));  
        
        $this->add(array(
            'name' => 'va_email',
            'type' => 'email',
          
            'options' => array(
                'label' => '',          
            ),
            'attributes' => array(               
                'class' => 'span12',
                'placeholder'=>'Ingrese un correo electrónico..'
            ),
        ));  
        
        
       
       
        $this->add(array(
            'name' => 'va_foto',
            'type' => 'File',
              'attributes' => array(               
                'class' => '',
                'id'   => 'va_foto',
                'placeholder'=>''
            )

        ));
        
         $this->add(array(
            'name' => 'va_contraseña',
            'type' => 'Text',
          
            'options' => array(
                'label' => '',          
            ),
            'attributes' => array(               
                'class' => 'span12',
                'placeholder'=>'Ingrese la contraseña…'
            ),
        ));  
                    
        
    
               $this->add(array(
            'name' => 'submit',
            'type' => 'Submit',
            'attributes' => array(
                'value' => 'Guardar',
                'class' => 'btn btn-info btn-large',
//                'id' => 'submitbutton',
            ),
        ));
       
       
    }
    
    
//     public function getDbAdapter()
//    {
//        return $this->dbAdapter;
//    }
    
//    public function setId($id){
//        $this->idusu=$id;
//        return $this;
//    }
//    
//        public function getId()
//    {
//        return $this->idusu;
//    }

    
    
}