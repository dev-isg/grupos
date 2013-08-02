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