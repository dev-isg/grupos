<?php
namespace Grupo\Form;

use Zend\Form\Form;
use Zend\Db\Sql\Sql;
use Zend\Db\Adapter\AdapterInterface;



class EventoForm extends Form
{
    protected $dbAdapter;
    protected $idplato;
     public function __construct(AdapterInterface $dbAdapter)//$name = null
    {
        $this->setDbAdapter($dbAdapter);
//        $this->setId($id);
        parent::__construct('evento');
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
            'name' => 'ta_usuario_in_id',
            'type' => 'Hidden',
           'attributes' => array(               
                'id'   => 'Ta_usuario_in_id',         
            ),
        )); 
              
       $this->add(array(
            'name' => 'ta_grupo_in_id',
            'type' => 'Hidden',
           'attributes' => array(               
                'id'   => 'ta_grupo_in_id',         
            ),
        ));
        
        $this->add(array(
            'name' => 'va_latitud',
            'type' => 'Hidden',
           'attributes' => array(               
                'id'   => 'mapLocationLat',         
            ),
        ));
        
          $this->add(array(
            'name' => 'va_longitud',
            'type' => 'Hidden',
           'attributes' => array(               
                'id'   => 'mapLocationLon',         
            ),
        ));
        
       
  
        $this->add(array(
            'name' => 'va_imagen',
            'type' => 'File',
              'attributes' => array(               
                'class' => '',
                'id'   => 'va_imagen',
                'placeholder'=>'Ingrese su imagen'
            )
        ));
        
          $this->add(array(
            'name' => 'va_descripcion',
            'type' => 'Textarea',
            'attributes' => array(               
                'class' => 'ckeditor',
                'id'   => 'editor1',
                'colls'=>80,
                'rows'=>10
            ),

        ));
           
          
         $this->add(array(
            'name' => 'va_nombre',
            'type' => 'Text',
          
            'options' => array(
                'label' => 'Nombre del evento:',          
            ),
            'attributes' => array(               
                'class' => 'span12',
                'id'   => 'va_nombre',
                'placeholder'=>'Ingrese el nombre del evento…'
            ),
        ));  
          
         
          $this->add(array(
            'name' => 'va_costo',
            'type' => 'Text',
            'attributes' => array(               
                'class' => 'span6',
                'id'   => 'inputCosto',
                'placeholder'=>'Costo...'
            )
        ));
          
                    $this->add(array(
            'name' => 'va_duracion',
            'type' => 'Text',
            'attributes' => array(               
                'class' => 'span6',
                'id' => 'va_duracion',
                'placeholder'=>'Cuanto tiempo...'
            )
        ));
                    
          $this->add(array(
            'name' => 'va_min',
            'type' => 'Text',
            'attributes' => array(               
                'class' => 'span5',
                'id'   => 'inputIcon1',
            )
        ));
          $this->add(array(
            'name' => 'va_max',
            'type' => 'Text',
            'attributes' => array(               
                'class' => 'span5',
                'id'   => 'inputIcon2',
            )
        ));
          
        
        $this->add(array(
            'name' => 'va_direccion',
            'type' => 'Text',
            'attributes' => array(               
                'class' => 'input-block-level addresspicker_map',
                'id'   => 'address',
                'placeholder'=>'Ingrese direccion',
                'maxlength'=>150
            )
        ));
        
         $this->add(array(
            'name' => 'va_referencia',
            'type' => 'Text',
            'attributes' => array(               
                'class' => 'input-block-level',
                'id'   => 'addressReference',
                'placeholder'=>'Ingrese direccion de referencia',
                'maxlength'=>250
            )
        ));
         
           $this->add(array(
            'name' => 'va_fecha',
            'type' => 'Text',
            'attributes' => array(
                'size'=>16,
                'readonly' => 'true',
                'class' => 'span10',
                'id'=>'fechaId'
            ),
        ));
          
//          $this->add(array(
//            'name' => 'va_dirigido',
//            'type' => 'Text',
//            'attributes' => array(               
//                'class' => 'span10',
//                'id'   => 'va_dirigido',
//                'placeholder'=>'A quien?'
//            ),
//            'options' => array(
//                'label' => 'Dirigido a :',
//            ),
//        ));
          
          
               
//        $this->add(array(
//            'name' =>'ta_categoria_in_id',
//            'type' => 'Select',  
//            
//             'attributes' => array(               
//                'class' => 'span10',
//                'id'   => 'ta_categoria_in_id'
//            ),
//           'options' => array('label' => 'Categoria del Grupo : ',
//                     'value_options' => $this->tipoCategoria(),//array(1=>'banana'),
//                     'empty_option'  => '--- Seleccionar ---'
//             )
//        ));
        
          $this->add(array(
            'name' => 'ta_ubigeo_in_id',//distrito
            'type' => 'Select',
             'attributes' => array(               
                'class' => 'input-block-level',
                'id'   => 'cityId'
            ),
           'options' => array(
                     'value_options' =>$this->Distrito(),                                               
//                    'empty_option'  => '--- Seleccionar ---',
                   
             )
        ));
          
           $this->add(array(
            'name' => 'va_tipo',
            'type' => 'Select',
             'attributes' => array(               
                'class' => 'input-block-level',
                'id'   => 'cityId'
            ),
           'options' => array(
                     'value_options' =>array(1=>'Pública',2=>'Privada'),                                               
                    'empty_option'  => '--- Seleccionar ---',
                   
             )
        ));
            
//            $this->add(array(
//            'name' => 'provincia',
//            'type' => 'Select',
//             'attributes' => array(               
//                'class' => 'span10',
//                'id'   => 'provincia'
//            ),
//           'options' => array(
//                     'label' => 'Provincia',
//                     'value_options' => array(
//                          '' => 'selecccione :'                                                
//                     ),
//               'disable_inarray_validator' => true
//             )
//        ));
//        
//            $this->add(array(
//            'name' => 'departamento',
//            'type' => 'Select',
//             'attributes' => array(               
//                'class' => 'span10',
//                'id'   => 'departamento'
//            ),
//           'options' => array(
//                     'label' => 'Departamento',
//                     'value_options' => array(
//                          '' => 'selecccione :',
//                                          
//                     ),
//               'disable_inarray_validator' => true
//             )
//        ));
//                        
//               $this->add(array(
//            'name' => 'pais',
//            'type' => 'Select',
//             'attributes' => array(               
//                'class' => 'span10',
//                'id'   => 'pais'
//            ),
//           'options' => array(
//                     'label' => 'Pais',
//                     'value_options' => array(
//                          '' => 'selecccione :',
//                             '1' => 'Peru'                  
//                     ),
//               'disable_inarray_validator' => true
//             )
//        ));

        $this->add(array(
            'name' => 'submit',
            'type' => 'Submit',
            'attributes' => array(
                'value' => 'Crear evento',
                'class' => 'btn btn-info btn-large',
                'id' => 'submitbutton',
            ),
        ));
        
        
        
        

    }

    
   public function tipoCategoria()
   {   
           
//        $idcateg=$this->getId();
        $this->dbAdapter =$this->getDbAdapter();
        $adapter = $this->dbAdapter;
        $sql = new Sql($adapter);
        $select = $sql->select()
            ->from('ta_categoria'); 
//            ->where(array('ta_categoria.in_id'=>$idcateg));
            $selectString = $sql->getSqlStringForSqlObject($select);
            $results = $adapter->query($selectString, $adapter::QUERY_MODE_EXECUTE);
            $tipocateg=$results->toArray();
        $auxtipo=array();
        foreach($tipocateg as $tipo){
            $auxtipo[$tipo['in_id']] = $tipo['va_nombre'];      
        }
            return $auxtipo;
            
     }
     
    public function tipoNotificacion()
        {   
        $this->dbAdapter =$this->getDbAdapter();
        $adapter = $this->dbAdapter;
        $sql = new Sql($adapter);
        $select = $sql->select()
            ->from('ta_notificacion'); 
            $selectString = $sql->getSqlStringForSqlObject($select);
            $results = $adapter->query($selectString, $adapter::QUERY_MODE_EXECUTE);
            $tiponotif=$results->toArray();

        $auxtipo=array();
        foreach($tiponotif as $tipo){
            $auxtipo[$tipo['in_id']] = $tipo['va_nombre'];      
        }
            return $auxtipo;
            
     }
     
     public function Distrito()
        {   
       
//        $this->dbAdapter =$this->getDbAdapter();
//        $adapter = $this->dbAdapter;
//        $sql = new Sql($adapter);
//        $select = $sql->select()
//                ->columns(array('in_iddistrito','va_distrito'))
//            ->from('ta_ubigeo')
//            ->where(array('va_departamento'=>'LIMA','va_provincia'=>'LIMA'))->group('in_iddistrito');
//            $selectString = $sql->getSqlStringForSqlObject($select);
//            $results = $adapter->query($selectString, $adapter::QUERY_MODE_EXECUTE);
//            $distrito=$results->toArray();
            
        $auxtipo=array('PER-LIM'=>'Perú');
//        foreach($distrito as $tipo){
//            $auxtipo[$tipo['in_iddistrito']] = $tipo['va_distrito'];      
//        }
            return $auxtipo;
            
     }
     
         public function setDbAdapter(AdapterInterface $dbAdapter)
    {
        $this->dbAdapter = $dbAdapter;

        return $this;
    }

    public function getDbAdapter()
    {
        return $this->dbAdapter;
    }
    
    public function setId($id){
        $this->idplato=$id;
        return $this;
    }
    
        public function getId()
    {
        return $this->idplato;
    }
}

