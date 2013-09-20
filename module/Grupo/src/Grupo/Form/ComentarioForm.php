<?php
namespace Grupo\Form;

use Zend\Form\Form;
use Zend\InputFilter\InputFilterProviderInterface;
use Zend\InputFilter\InputFilter;

class ComentarioForm extends Form
{
     public function __construct($name = null)
    {
        parent::__construct('comentario');
        $this->setAttribute('method', 'post');
   
         $this->add(array(
            'name' => 'va_descripcion',
            'type' => 'TextArea',
             'attributes' => array(
                 'class' => '',
                 'id' => 'texto-comentar',
                 'placeholder' => 'Ingrese su comentario aquí',
                 'cols' => 30,
                 'rows' => 5
            ),
        ));  

        $this->add(array(
            'name' => 'submit',
            'type' => 'Submit',
            'attributes' => array(
                'value' => 'Comentar',
                'class' => 'btn btn-primary btn-enviar-mensaje'
            ),
        ));
        
        $this->setInputFilter($this->validadores());
    }
    
    public function validadores(){
        $inputFilter = new InputFilter();
        
        $inputFilter->add(array(
            'name' => 'va_descripcion',
            'required' => true,
        ));
        
        
        
        return $inputFilter;
        
    }
}

