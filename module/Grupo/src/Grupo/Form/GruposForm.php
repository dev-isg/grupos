<?php
namespace Grupo\Form;

use Zend\Form\Form;
use Zend\Db\Sql\Sql;
use Zend\Db\Adapter\AdapterInterface;



class GruposForm extends Form
{
    protected $dbAdapter;
    protected $idplato;
     public function __construct(AdapterInterface $dbAdapter)//$name = null
    {
        $this->setDbAdapter($dbAdapter);
//        $this->setId($id);
        parent::__construct('grupo');
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
            'name' => 'va_latitud',
            'type' => 'Hidden',
           'attributes' => array(               
                'id'   => 'va_latitud',         
            ),
        ));
        
          $this->add(array(
            'name' => 'va_longitud',
            'type' => 'Hidden',
           'attributes' => array(               
                'id'   => 'va_longitud',         
            ),
        ));
        
       
  
        $this->add(array(
            'name' => 'va_imagen',
            'type' => 'File',
              'attributes' => array(               
                'class' => '',
                'id'   => 'va_imagen',
                'placeholder'=>'Ingrese su página Web'
            ),
            'options' => array(
                'label' => 'Agregar Imagen : ',
            ),
        ));
        
          $this->add(array(
            'name' => 'va_descripcion',
            'type' => 'Textarea',
            'attributes' => array(               
                'class' => 'span11',
                'id'   => 'va_descripcion',
                'placeholder'=>'Ingrese descripción',
                'colls'=>40,
                'rows'=>4
            ),
            'options' => array(
                'label' => 'Descripción',
            ),
        ));
           
          
         $this->add(array(
            'name' => 'va_nombre',
            'type' => 'Text',
          
            'options' => array(
                'label' => 'Nombre del Grupo :',          
            ),
            'attributes' => array(               
                'class' => 'span11',
                'id'   => 'va_nombre',
                'placeholder'=>'Ingrese nombre del grupo'
            ),
        ));  
          
         
          $this->add(array(
            'name' => 'va_costo',
            'type' => 'Text',
            'attributes' => array(               
                'class' => 'span10',
                'id'   => 'de_precio',
                'placeholder'=>'Ingrese el costo'
            ),
            'options' => array(
                'label' => 'Costo',
            ),
        ));
          
        
        $this->add(array(
            'name' => 'va_direccion',
            'type' => 'Text',
            'attributes' => array(               
                'class' => 'span10',
                'id'   => 'va_direccion',
                'placeholder'=>'Ingrese direccion'
            ),
            'options' => array(
                'label' => 'Direccion',
            ),
        ));
        
         $this->add(array(
            'name' => 'va_referencia',
            'type' => 'Text',
            'attributes' => array(               
                'class' => 'span10',
                'id'   => 'va_referencia',
                'placeholder'=>'Ingrese direccion de referencia'
            ),
            'options' => array(
                'label' => 'Referencia',
            ),
        ));
          
          $this->add(array(
            'name' => 'va_dirigido',
            'type' => 'Text',
            'attributes' => array(               
                'class' => 'span10',
                'id'   => 'va_dirigido',
                'placeholder'=>'A quien?'
            ),
            'options' => array(
                'label' => 'Dirigido a :',
            ),
        ));
          
        $this->add(array(
            'name' => 'tipo_notificacion',
            'type' => 'MultiCheckbox',
             'attributes' => array(               
                'class' => 'checkbox inline',
                'id'   => 'en_destaque',
                 'placeholder'=>'Ingrese su destaque'
            ),
            'options' => array(
                   'label' => 'Notificaciones',
                  'value_options' => $this->tipoNotificacion(),
                )
        ));
          
               
        $this->add(array(
            'name' =>'ta_categoria_in_id',
            'type' => 'Select',  
            
             'attributes' => array(               
                'class' => 'span10',
                'id'   => 'ta_categoria_in_id'
            ),
           'options' => array('label' => 'Categoria del Grupo : ',
                     'value_options' => $this->tipoCategoria(),//array(1=>'banana'),
                     'empty_option'  => '--- Seleccionar ---'
             )
        ));
        
          $this->add(array(
            'name' => 'ta_ubigeo_in_id',//distrito
            'type' => 'Select',
             'attributes' => array(               
                'class' => 'span10',
                'id'   => 'ta_ubigeo_in_id'
            ),
           'options' => array(
                     'label' => 'Distrito',
                     'value_options' =>$this->Distrito(),                                               
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
                'value' => 'Guardar',
                'class' => 'btn btn-success',
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
       
        $this->dbAdapter =$this->getDbAdapter();
        $adapter = $this->dbAdapter;
        $sql = new Sql($adapter);
        $select = $sql->select()
                ->columns(array('in_iddistrito','va_distrito'))
            ->from('ta_ubigeo')
            ->where(array('va_departamento'=>'LIMA','va_provincia'=>'LIMA'))->group('in_iddistrito');
            $selectString = $sql->getSqlStringForSqlObject($select);
            $results = $adapter->query($selectString, $adapter::QUERY_MODE_EXECUTE);
            $distrito=$results->toArray();
            
        $auxtipo=array();
        foreach($distrito as $tipo){
            $auxtipo[$tipo['in_iddistrito']] = $tipo['va_distrito'];      
        }
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

