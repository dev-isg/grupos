<?php


namespace Usuario\Model;
use Zend\InputFilter\Factory as InputFactory;
use Zend\InputFilter\InputFilter;
use Zend\InputFilter\InputFilterAwareInterface;
use Zend\InputFilter\InputFilterInterface;


class Usuario implements InputFilterAwareInterface{
    
    public $in_id;
    public $va_nombre;
    public $va_email;  
    public $va_contrasena;
    public $verificar_contrasena;
    public $va_dni;
    public $va_foto;
    public $va_genero;
    public $va_descripcion;
    public $ta_ubigeo_in_id;
    
    protected $inputFilter;  
    
    public function exchangeArray($data){
            $this->in_id= (!empty($data['in_id'])) ? $data['in_id'] : null;
            $this->va_nombre= (!empty($data['va_nombre'])) ? $data['va_nombre'] : null;
            $this->va_email= (!empty($data['va_email'])) ? $data['va_email'] : null;
            $this->va_contrasena = (!empty($data['va_contrasena'])) ? $data['va_contrasena'] : null;
            $this->verificar_contrasena = (!empty($data['va_contrasena'])) ? $data['va_contrasena'] : null;
            $this->va_dni= (!empty($data['va_dni'])) ? $data['va_dni'] : null;
            $this->va_foto= (!empty($data['va_foto'])) ? $data['va_foto'] : 'foto-carnet.jpg';
            $this->va_genero= (!empty($data['va_genero'])) ? $data['va_genero'] : null;
            $this->va_descripcion= (!empty($data['va_descripcion'])) ? $data['va_descripcion'] : null;
            $this->ta_ubigeo_in_id= (!empty($data['ta_ubigeo_in_id'])) ? $data['ta_ubigeo_in_id'] : 1;
         }

    public function setInputFilter(InputFilterInterface $inputFilter) {
         throw new \Exception("Not used");
    }
    
      
    public function getArrayCopy()
    {
        return get_object_vars($this);
    }
    
     public function getInputFilter()
    {
         
         if (!$this->inputFilter) {
            $inputFilter = new InputFilter();
            $factory = new InputFactory();
         
            $inputFilter->add($factory->createInput(array(
                        'name' => 'in_id',
                        'required' => true,
                        'filters' => array(
                            array('name' => 'Int'),
                        ),
                    )));
            
            $inputFilter->add($factory->createInput(array(
                'name'     => 'va_nombre',
                'required' => true,
                'filters'  => array(
                    array('name' => 'StripTags'),
                    array('name' => 'StringTrim'),
                ),
                'validators' => array(
                    array(
                        'name'    => 'StringLength',
                        'options' => array(
                            'encoding' => 'UTF-8',
                            'min'      => 3,
                            'max'      => 100,
                        ),
                    ),
                ),
            )));
            
            $inputFilter->add($factory->createInput(array( 
                'name' => 'va_email', 
                'required' => true, 
                'filters' => array( 
                    array('name' => 'StripTags'), 
                    array('name' => 'StringTrim'), 
                ), 
                'validators' => array( 
                    array( 
                        'name' => 'EmailAddress', 
                        'options' => array( 
                            'encoding' => 'UTF-8', 
                            'min'      => 5, 
                            'max'      => 255, 
                            'messages' => array( 
                                \Zend\Validator\EmailAddress::INVALID_FORMAT => 'Formato de Email Inválido' 
                            ) 
                        ), 
                    ), 
                ), 
            ))); 
            
             $inputFilter->add($factory->createInput(array( 
                'name' => 'va_contrasena', 
                'required' => true, 
                'filters' => array( array('name' => 'StringTrim'), ), 
                'validators' => array( 
                    array( 
                        'name' => 'StringLength', 
                        'options' => array( 
//                             'encoding' => 'UTF-8', 
                            'min'      => 8, 
                            'max'      => 128, 
                        ), 
                    ), 
                ), 
            )));
             
            $inputFilter->add($factory->createInput(array( 
                'name' => 'verificar_contrasena', 
                'required' => true, 
                'filters' => array ( array('name' => 'StringTrim'), ), 
                'validators' => array( 
                    array( 
                        'name'    => 'StringLength', 
                        'options' => array( 'min' => 8 ), 
                    ), 
                    array( 
                        'name' => 'identical', 
                        'options' => array('token' => 'va_contrasena' ) 
                    ), 
                ), 
            )));
            
                $inputFilter->add($factory->createInput(array(
                        'name' => 'ta_ubigeo_in_id',
                        'required' => false
                    )));
             
//             $inputFilter->add($factory->createInput(array(
//                        'name' => 'va_dni',
//                        'required' => false,
//                        'filters' => array(
//                            array('name' => 'Int'),
//                        ),
//                    )));
             
            $inputFilter->add(
                $factory->createInput(array(
                    'name'     => 'va_foto',
                    'required' => false,
                     'validators' => array(
//                    array(
//                        'name'    => 'filemimetype',
//                      //  'options' =>  array('mimeType' => 'image/png,image/x-png,image/jpg,image/gif,image/jpeg'),
//                        'options' =>  array('mimeType' => 'image/jpg,image/jpeg'),
//                    ),
                    array(
                        'name'    => 'filesize',
                        'options' =>  array('max' => 204800),
                    ),
                  ),
               ))
            );
            
            $inputFilter->add($factory->createInput(array(
                'name'     => 'va_genero',
                'required' => false,
                'filters'  => array(
                    array('name' => 'StripTags'),
                    array('name' => 'StringTrim'),
                ),
                'validators' => array(
                    array(
                        'name'    => 'StringLength',
                        'options' => array(
                            'encoding' => 'UTF-8',
                            'min'      => 1,
                            'max'      => 200,
                        ),
                    ),
                ),
            )));
             
              $inputFilter->add($factory->createInput(array(
                'name'     => 'va_descripcion',
                'required' => false,
                'filters'  => array(
                    array('name' => 'StripTags'),
                    array('name' => 'StringTrim'),
                ),
                'validators' => array(
                    array(
                        'name'    => 'StringLength',
                        'options' => array(
                            'encoding' => 'UTF-8',
                            'min'      => 3,
                            'max'      => 200,
                        ),
                    ),
                ),
            )));
              
              
             $this->inputFilter = $inputFilter;
         }
        
         return $this->inputFilter;
         
    }
    
    
    public function getInputFilter2()
    {
         
         if (!$this->inputFilter) {
            $inputFilter = new InputFilter();
            $factory = new InputFactory();
         
            $inputFilter->add($factory->createInput(array(
                        'name' => 'in_id',
                        'required' => false,
                        'filters' => array(
                            array('name' => 'Int'),
                        ),
                    )));
            
            $inputFilter->add($factory->createInput(array(
                'name'     => 'va_nombre',
                'required' => true,
                'filters'  => array(
                    array('name' => 'StripTags'),
                    array('name' => 'StringTrim'),
                ),
                'validators' => array(
                    array(
                        'name'    => 'StringLength',
                        'options' => array(
                            'encoding' => 'UTF-8',
                            'min'      => 3,
                            'max'      => 100,
                        ),
                    ),
                ),
            )));
            
            $inputFilter->add($factory->createInput(array( 
                'name' => 'va_email', 
                'required' => true, 
                'filters' => array( 
                    array('name' => 'StripTags'), 
                    array('name' => 'StringTrim'), 
                ), 
                'validators' => array( 
                    array( 
                        'name' => 'EmailAddress', 
                        'options' => array( 
                            'encoding' => 'UTF-8', 
                            'min'      => 5, 
                            'max'      => 255, 
                            'messages' => array( 
                                \Zend\Validator\EmailAddress::INVALID_FORMAT => 'Formato de Email Inválido' 
                            ) 
                        ), 
                    ), 
                ), 
            ))); 
            
             $inputFilter->add($factory->createInput(array( 
                'name' => 'va_contrasena', 
                'required' => false, 
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
            )));
             
            $inputFilter->add($factory->createInput(array( 
                'name' => 'verificar_contrasena', 
                'required' => false, 
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
            )));
             
//             $inputFilter->add($factory->createInput(array(
//                        'name' => 'va_dni',
//                        'required' => false,
//                        'filters' => array(
//                            array('name' => 'Int'),
//                        ),
//                    )));
             
            $inputFilter->add(
                $factory->createInput(array(
                    'name'     => 'va_foto',
                    'required' => false,
                     'validators' => array(
//                    array(
//                        'name'    => 'filemimetype',
//                      //  'options' =>  array('mimeType' => 'image/png,image/x-png,image/jpg,image/gif,image/jpeg'),
//                        'options' =>  array('mimeType' => 'image/jpg,image/jpeg'),
//                    ),
                    array(
                        'name'    => 'filesize',
                        'options' =>  array('max' => 204800),
                    ),
                  ),
               ))
            );
            
            $inputFilter->add($factory->createInput(array(
                'name'     => 'va_genero',
                'required' => false,
                'filters'  => array(
                    array('name' => 'StripTags'),
                    array('name' => 'StringTrim'),
                ),
                'validators' => array(
                    array(
                        'name'    => 'StringLength',
                        'options' => array(
                            'encoding' => 'UTF-8',
                            'min'      => 1,
                            'max'      => 200,
                        ),
                    ),
                ),
            )));
             
              $inputFilter->add($factory->createInput(array(
                'name'     => 'va_descripcion',
                'required' => false,
                'filters'  => array(
                    array('name' => 'StripTags'),
                    array('name' => 'StringTrim'),
                ),
                'validators' => array(
                    array(
                        'name'    => 'StringLength',
                        'options' => array(
                            'encoding' => 'UTF-8',
                            'min'      => 3,
                            'max'      => 200,
                        ),
                    ),
                ),
            )));
              
                  $inputFilter->add($factory->createInput(array(
                        'name' => 'ta_ubigeo_in_id',
                        'required' => false
                   )));
              
              
             $this->inputFilter = $inputFilter;
         }
        
         return $this->inputFilter;
         
    }
    
    
}