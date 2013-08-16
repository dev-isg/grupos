<?php
namespace Usuario\Form;

use Zend\Form\Form;
use Zend\InputFilter\InputFilterProviderInterface;
use Zend\InputFilter\InputFilter;


class NotificacionForm extends Form
{
     public function __construct($name = null)
    {
        parent::__construct('cambio');
        $this->setAttribute('method', 'post');
        $this->setAttribute('endtype', 'multipart/form-data');
   
               $this->add(array(
                   'name' => 'tipo_notificacion',
                   'type' => 'MultiCheckbox',
                    'attributes' => array(
                        'value' => array('1', '2'),
                       'class' => 'checkbox inline',
                       'id'   => 'en_destaque',
                        'placeholder'=>'Ingrese su destaque'
                   ),
                   'options' => array(
                         'value_options' => array(
                            '1'=>'Recibir avisos por email cuando ingresa al Grupo ',
                            '2'=>'Recibir avisos por email cuando sale del Grupo',
                         ),
                       ),
                   
               ));
         

        $this->add(array(
            'name' => 'submit',
            'type' => 'Submit',
        ));
        $this->setInputFilter($this->validadores());
    }
    public function validadores(){
    
        $inputFilter = new InputFilter();
  
        $inputFilter->add(array(
            'name' => 'tipo_notificacion',
            'required' => false,

        ));
         
    
        return $inputFilter;
    }
}