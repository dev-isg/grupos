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
//                       'class' => 'checkbox inline',
                       'id'   => 'en_destaque',
                       'placeholder'=>'Ingrese su destaque'
                   ),
                   'options' => array(
                       'label_attributes' => array('class' => 'checkbox'),
                         'value_options' => array(
                            '1'=>'Recibir avisos por correo electrónico cuando un usuario se une a uno de tus eventos o grupos.',
                            '2'=>'Recibir avisos por correo electrónico cuando un usuario de tu evento o grupo se retira.',
                         ),
                       ),
                   
               ));
         

        $this->add(array(
            'name' => 'submit',
            'type' => 'Submit',
            'attributes'=>array(
                'class'=>'btn btn-info btn-large')
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