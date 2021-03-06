<?php

namespace Usuario\Form;

use Zend\Form\Form;
use Zend\Db\Sql\Sql;
use Zend\Db\Adapter\AdapterInterface;
use Zend\ServiceManager\ServiceManager;
use Zend\ServiceManager\ServiceManagerAwareInterface;

class UsuarioForm extends Form {

    protected $dbAdapter;

    public function __construct($name = null, AdapterInterface $dbAdapter = null) {
        if ($dbAdapter != null) {
            $this->setDbAdapter($dbAdapter);
        }
        parent::__construct('usuario');
        $this->setAttribute('method', 'post');
        $this->setAttribute('endtype', 'multipart/form-data');


        $this->add(array(
            'name' => 'in_id',
            'type' => 'Hidden',
            'attributes' => array(
                'id' => 'in_id',
            ),
        ));

        $this->add(array(
            'name' => 'va_nombre',
            'type' => 'Text',
            'attributes' => array(
                'class' => 'span10',
                'placeholder' => 'Ingrese el nombre de usario…'
            ),
        ));

        $this->add(array(
            'name' => 'va_email',
            'type' => 'Text',
            'attributes' => array(
                'class' => 'span10',
                'placeholder' => 'Ingrese el mail... '
            ),
        ));
        
         $this->add(array(
            'name' => 'va_facebook',
            'type' => 'Text',
            'attributes' => array(
                'class' => 'span10',
                'placeholder' => 'Ingrese la url de su facebook'
            ),
        ));
                
         $this->add(array(
            'name' => 'va_twitter',
            'type' => 'Text',
            'attributes' => array(
                'class' => 'span10',
                'placeholder' => 'Ingrese la url de su twitter'
            ),
        ));

//        $this->add(array(
//            'name' => 'va_dni',
//            'type' => 'Text',
//            'attributes' => array(
//                'placeholder'=>'Ingrese el dni... '
//            ),
//        )); 

        $this->add(array(
            'name' => 'va_genero',
            'type' => 'Select',
//            'attributes' => array(               
//                'placeholder'=>'Ingrese el dni... '
//            ),
            'options' => array(
                'value_options' => array(
                    'masculino' => 'masculino', 'femenino' => 'femenino'
                ),
                'empty_option' => '--- Seleccionar ---',
            ),
        ));

        $this->add(array(
            'name' => 'va_foto',
            'type' => 'File',
            'attributes' => array(
                'placeholder' => 'Ingrese el mail... '
            ),
        ));


        $this->add(array(
            'name' => 'va_descripcion',
            'type' => 'Textarea',
            'attributes' => array(
                'placeholder' => 'Ingrese su descripción... ',
                'cols' => 80,
                'rows' => 3
            ),
        ));

        $this->add(array(
            'name' => 'va_contrasena',
            'type' => 'Password',
//            'options' => array(
//                'label' => 'Password:',          
//            ),
            'attributes' => array(
                'id' => 'va_contrasena',
                'class' => 'span10',
                'placeholder' => 'Ingrese la contraseña…'
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
                'placeholder' => 'Confirme la contraseña…'
            ),
        ));

//        $this->add(array(
//            'name' => 'ta_ubigeo_in_id', //distrito
//            'type' => 'Select',
//            'attributes' => array(
//                'class' => 'span10',
//                'id' => 'ta_ubigeo_in_id'
//            ),
//             'options' =>array(                                    
//                'empty_option' => '--- Seleccionar ---',
//            )
//           
//        ));
$this->add(array(
            'name' => 'va_pais',
            'type' => 'Select',
             'attributes' => array(               
                'class' => 'span10',
                'id'   => 'pais'
            ),
           'options' =>array(
                'value_options' =>$this->Provincia(),                                               
                'empty_option' => '--- Seleccionar ---',
            )
        ));

        $this->add(array(
            'type' => 'Select',
            'attributes' => array(
                'multiple' => 'multiple',
                'class'=>'select2-offscreen',
                'id'=>'e1',
                'style'=>'width:97.3%;',
                'tabindex'=>-1
            ),
            'name' => 'select2',
            'options' => array(
                'value_options' => $this->tipoCategoria()
            ),
        ));



        $this->add(array(
            'name' => 'submit',
            'type' => 'Submit',
            'attributes' => array(
                'value' => 'Ingresar',
                'class' => 'btn btn-info btn-large',//'btn btn-primary'
//                'id' => 'submitbutton',
            ),
        ));
    }
    
   public function Provincia() {
        $this->dbAdapter = $this->getDbAdapter();
        $adapter = $this->dbAdapter;
    //    if($adapter){
        $sql = new Sql($adapter);
        $select = $sql->select()
                        ->columns(array('Code', 'Name'))
                        ->from('country')
                        ->order('Name ASC');
        $selectString = $sql->getSqlStringForSqlObject($select);
        $results = $adapter->query($selectString, $adapter::QUERY_MODE_EXECUTE);
        $pais= $results->toArray();  
        $auxtipo = array();
        foreach ($pais as $tipo) {
            $auxtipo[$tipo['Code']] = $tipo['Name'];
        }
        return $auxtipo;

    }
 public function tipoCategoria() {
        $this->dbAdapter = $this->getDbAdapter();
        $adapter = $this->dbAdapter;
        if ($adapter) {
            $sql = new Sql($adapter);
            $select = $sql->select()
                    ->from('ta_categoria');
            $selectString = $sql->getSqlStringForSqlObject($select);
            $categoria = $adapter->query($selectString, $adapter::QUERY_MODE_EXECUTE);
            $auxcateg = array();
            foreach ($categoria as $catego) {
                $auxcateg[$catego['in_id']] = $catego['va_nombre'];
            }
            return $auxcateg;
        } else {
            return;
        }
    }



    public function setDbAdapter(AdapterInterface $dbAdapter) {
        $this->dbAdapter = $dbAdapter;

        return $this;
    }

    public function getDbAdapter() {
        return $this->dbAdapter;
    }

}

