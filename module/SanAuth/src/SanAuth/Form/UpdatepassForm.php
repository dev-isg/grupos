<?php
namespace SanAuth\Form;

use Zend\Form\Form;
use Zend\InputFilter\InputFilterProviderInterface;
use Zend\InputFilter\InputFilter;


class UpdatepassForm extends Form
{
     public function __construct($name = null)
    {
        parent::__construct('cambio');
        $this->setAttribute('method', 'post');
        $this->setAttribute('endtype', 'multipart/form-data');
   
         $this->add(array(
            'name' => 'va_contrasena',
            'type' => 'password',
//            'options' => array(
//                'label' => 'Nombre de usario:',          
//            ),
            'attributes' => array(               
               'class' => 'span10',
                'placeholder'=>'Ingrese su contraseña'
            ),
        ));  
         
         $this->add(array(
             'name' => 'verificar_contrasena',
             'type' => 'password',
         
             'options' => array(
                 'label' => '',
             ),
             'attributes' => array(
                 'class' => 'span10',
                 'placeholder'=>'Vuelva a ingresar su contraseña'
             ),
         ));
         

        $this->add(array(
            'name' => 'submit',
            'type' => 'Submit',
            'attributes' => array(
                'value' => 'Cambiar',
                'class' => 'btn btn-primary'
            ),
        ));
        $this->setInputFilter($this->validadores());
    }
    public function validadores(){
    
        $inputFilter = new InputFilter();
  
        $inputFilter->add(array(
            'name' => 'va_contrasena',
            'required' => true,
            'filters' => array( array('name' => 'StringTrim'), ),
            'validators' => array(
                array(
                    'name' => 'StringLength',
                    'options' => array(
                        'encoding' => 'UTF-8',
                        'min'      => 6,
                        'max'      => 128,
                    ),
                ),
            ),
        ));
         
        $inputFilter->add(array(
            'name' => 'verificar_contrasena',
            'required' => true,
            'filters' => array ( array('name' => 'StringTrim'), ),
            'validators' => array(
                array(
                    'name'    => 'StringLength',
                    'options' => array( 'min' => 6 ),
                ),
                array(
                    'name' => 'identical',
                    'options' => array('token' => 'va_contrasena' )
                ),
            ),
        ));
    
        return $inputFilter;
    }
}