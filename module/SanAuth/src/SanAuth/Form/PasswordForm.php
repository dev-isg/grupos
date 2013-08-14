<?php
namespace SanAuth\Form;

use Zend\Form\Form;
use Zend\InputFilter\InputFilterProviderInterface;
use Zend\InputFilter\InputFilter;



class PasswordForm extends Form
{
     public function __construct($name = null)
    {
        parent::__construct('cambio');
        $this->setAttribute('method', 'post');
        
         $this->add(array(
            'name' => 'va_email',
            'type' => 'Text',
            'attributes' => array(               
                'placeholder'=>'yourmail@email.com'
            ),
        ));  
         

        $this->add(array(
            'name' => 'submit',
            'type' => 'Submit',
            'attributes' => array(
                'value' => 'Ingresar',
                'class' => 'btn btn-primary'
            ),
        ));
        
       $this->setInputFilter($this->validadores());
    }
    public function validadores(){
        
        $inputFilter = new InputFilter();
        
        $inputFilter->add(array(
            'name' => 'va_email',
            'required' => true,
            'validators' => array(
                array(
                    'name' => 'EmailAddress'
                )
            ),
        ));
        
        return $inputFilter;
    }
}

