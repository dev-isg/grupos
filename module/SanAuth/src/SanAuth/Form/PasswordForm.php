<?php
namespace SanAuth\Form;

use Zend\Form\Form;
use Zend\Db\Sql\Sql;
use Zend\Db\Adapter\AdapterInterface;

use Zend\ServiceManager\ServiceManager;
use Zend\ServiceManager\ServiceManagerAwareInterface;


class PasswordForm extends Form
{
     public function __construct($name = null)
    {
        parent::__construct('cambio');
        $this->setAttribute('method', 'post');
        $this->setAttribute('endtype', 'multipart/form-data');
   
         $this->add(array(
            'name' => 'va_email',
            'type' => 'Text',
//            'options' => array(
//                'label' => 'Nombre de usario:',          
//            ),
            'attributes' => array(               
//                'class' => 'span12',
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
    }
}

