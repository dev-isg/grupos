<?php
namespace SanAuth\Form;

use Zend\Form\Form;
use Zend\Db\Sql\Sql;
use Zend\Db\Adapter\AdapterInterface;

use Zend\ServiceManager\ServiceManager;
use Zend\ServiceManager\ServiceManagerAwareInterface;


class UserForm extends Form
{
     public function __construct($name = null)
    {
        parent::__construct('usuario');
        $this->setAttribute('method', 'post');
        $this->setAttribute('endtype', 'multipart/form-data');
   
         $this->add(array(
            'name' => 'va_nombre',
            'type' => 'Text',
          
//            'options' => array(
//                'label' => 'Nombre de usario:',          
//            ),
            'attributes' => array(               
//                'class' => 'span12',
                'placeholder'=>'Ingrese el nombre de usario…'
            ),
        ));  
         
         $this->add(array(
            'name' => 'va_contrasena',
            'type' => 'Password',
          
//            'options' => array(
//                'label' => 'Password:',          
//            ),
            'attributes' => array(
                'id'=>'inputPassword',
//                'class' => 'span12',
                'placeholder'=>'Ingrese la contraseña…'
            ),
        ));
          
        $this->add(array(
            'name' => 'Remenber',
            'type' => 'MultiCheckbox',
             'attributes' => array(               
                'class' => 'checkbox inline',
                'id'   => 'en_destaque',
                 'placeholder'=>'Ingrese su destaque'
            ),
            'options' => array(
                   'label' => 'Remember Me ?:',
                  'value_options' => array(0=>'recordar'),
                )
        ));
          
        

        $this->add(array(
            'name' => 'submit',
            'type' => 'Submit',
            'attributes' => array(
                'value' => 'Ingresar',
                'class' => 'btn btn-primary'//'btn btn-info btn-large',
//                'id' => 'submitbutton',
            ),
        ));
    }
}

