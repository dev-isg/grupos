<?php

namespace Grupo\Model;
use Zend\InputFilter\Factory as InputFactory;
use Zend\InputFilter\InputFilter;
use Zend\InputFilter\InputFilterAwareInterface;
use Zend\InputFilter\InputFilterInterface;

class Evento implements InputFilterAwareInterface
{
    public $in_id;
    public $va_nombre;
    public $va_descripcion;
    public $va_costo;
    public $va_latitud;
    public $va_longitud;
    public $va_direccion;
    public $va_referencia;
    public $va_imagen;
    public $va_fecha;

    public $va_estado;
    public $va_max;
    public $va_min;
    public $va_duracion;
    //foraneas
    public $ta_usuario_in_id;
//    public $ta_categoria_in_id;   
    public $ta_ubigeo_in_id;
    public $ta_grupo_in_id;
    
//    public $pais;
//    public $departamento;
//    public $provincia;
//    public $distrito;
    
    
   
    protected $inputFilter;   
    
    public function exchangeArray($data){
            $this->in_id= (!empty($data['in_id'])) ? $data['in_id'] : null;
            $this->va_nombre= (!empty($data['va_nombre'])) ? $data['va_nombre'] : null;
            $this->va_descripcion= (!empty($data['va_descripcion'])) ? $data['va_descripcion'] : null;
            $this->va_costo= (!empty($data['va_costo'])) ? $data['va_costo'] : null;
            $this->va_latitud= (!empty($data['va_latitud'])) ? $data['va_latitud'] : null;
            $this->va_longitud= (!empty($data['va_longitud'])) ? $data['va_longitud'] : null;
            $this->va_direccion= (!empty($data['va_direccion'])) ? $data['va_direccion'] : null;
            $this->va_referencia= (!empty($data['va_referencia'])) ? $data['va_referencia'] : null;
            $this->va_imagen= (!empty($data['va_imagen'])) ? $data['va_imagen'] : null;
            $this->va_fecha= (!empty($data['va_fecha'])) ? $data['va_fecha'] : null;
 
            $this->va_estado= (!empty($data['va_estado'])) ? $data['va_estado'] : 1;
            $this->va_max= (!empty($data['va_max'])) ? $data['va_max'] : null;
            $this->va_min= (!empty($data['va_min'])) ? $data['va_min'] : null;
            $this->va_duracion=(!empty($data['va_duracion'])) ? $data['va_duracion'] : null;
            
            $this->ta_usuario_in_id= (!empty($data['ta_usuario_in_id'])) ? $data['ta_usuario_in_id'] : 1;
//            $this->ta_categoria_in_id= (!empty($data['ta_categoria_in_id'])) ? $data['ta_categoria_in_id'] : null;
            $this->ta_ubigeo_in_id= (!empty($data['ta_ubigeo_in_id'])) ? $data['ta_ubigeo_in_id'] : null;
            $this->ta_grupo_in_id=(!empty($data['ta_grupo_in_id'])) ? $data['ta_grupo_in_id'] : 2;

//            $this->pais = (!empty($data['pais'])) ? $data['pais'] : null;
//            $this->departamento = (!empty($data['departamento'])) ? $data['departamento'] : null;
//            $this->provincia = (!empty($data['provincia'])) ? $data['provincia'] : null;
//            $this->distrito = (!empty($data['distrito'])) ? $data['distrito'] : null;
            


         }
    
    public function setInputFilter(InputFilterInterface $inputFilter)
    {
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
//            
//            $inputFilter->add($factory->createInput(array(
//                        'name' => 'ta_grupo_in_id',
//                        'required' => true,
//                        'filters' => array(
//                            array('name' => 'Int'),
//                        ),
//                    )));    
            
            $inputFilter->add($factory->createInput(array(
                'name' => 'va_latitud',
                'required' => true,
                'filters' => array(
                    array('name' => 'Int'),
                ),
            )));
            $inputFilter->add($factory->createInput(array(
                'name' => 'va_longitud',
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
                'name'     => 'va_descripcion',
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
                            'max'      => 200,
                        ),
                    ),
                ),
            )));
            
            $inputFilter->add($factory->createInput(array(
                'name'     => 'va_costo',
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
                            'max'      => 100,
                        ),
                    ),
                ),
            )));
            
              $inputFilter->add($factory->createInput(array(
                'name'     => 'va_direccion',
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
                            'max'      => 100,
                        ),
                    ),
                ),
            )));
              
            $inputFilter->add($factory->createInput(array(
                'name'     => 'va_referencia',
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
                            'max'      => 100,
                        ),
                    ),
                ),
            )));
            
//
//            
////              $inputFilter->add($factory->createInput(array(
////                'name'     => 'ta_categoria_in_id',
////                'required' => true,
////                'filters'  => array(
////                    array('name' => 'StripTags'),
////                    array('name' => 'StringTrim'),
////                ),
////            )));
//              
                $inputFilter->add($factory->createInput(array(
                'name'     => 'ta_ubigeo_in_id',//distrito
                'required' => true,
                'filters'  => array(
                    array('name' => 'StripTags'),
                    array('name' => 'StringTrim'),
                ),
            )));
            
//           $inputFilter->add(
//                $factory->createInput(array(
//                    'name'     => 'va_imagen',
//                    'required' => false,
//                     'validators' => array(
//                    array(
//                        'name'    => 'filemimetype',
//                      //  'options' =>  array('mimeType' => 'image/png,image/x-png,image/jpg,image/gif,image/jpeg'),
//                        'options' =>  array('mimeType' => 'image/jpg,image/jpeg'),
//                    ),
//                    array(
//                        'name'    => 'filesize',
//                        'options' =>  array('max' => 204800),
//                    ),
//                  ),
//               ))
//            );
                $this->inputFilter = $inputFilter;
        }
        return $this->inputFilter;
    }
    
    
}
