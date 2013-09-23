<?php
namespace Usuario\Model;

use Zend\Db\TableGateway\AbstractTableGateway;
use Zend\Db\TableGateway\TableGateway;
use Zend\Db\Sql\Sql;
use Zend\Db\Sql\Select;
use Usuario\Controller\IndexController ;
class UsuarioTable
{

    protected $tableGateway;

    public function __construct(TableGateway $tableGateway)
    {
        $this->tableGateway = $tableGateway;
    }

    public function fetchAll($id = null)
    {
        $adapter = $this->tableGateway->getAdapter();
        $sql = new Sql($adapter);
        $selecttot = $sql->select()->from('ta_usuario');
//                    ->join('ta_ubigeo','ta_usuario.ta_ubigeo_in_id=ta_ubigeo.in_id',array(),'left')
        
        $selecttot->user('ta_usuario.in_id')->order('ta_usuario.in_id desc');
        $selectString = $sql->getSqlStringForSqlObject($selecttot);
        $resultSet = $adapter->query($selectString, $adapter::QUERY_MODE_EXECUTE);
        
        return $resultSet;
    }
   
    public function getUsuario($id)
    {
        $id = (int) $id;
        $row = $this->tableGateway->select(array(
            'in_id' => $id
        ));
        
        
        if (! $row) {
            throw new \Exception("Could not find row $id");
        }
        return $row->current();;
    }
    
     public function Distrito($id) {
        $adapter = $this->tableGateway->getAdapter();
        if($adapter){
        $sql = new Sql($adapter);
        $select = $sql->select()
                        ->columns(array('in_iddistrito', 'va_distrito'))
                        ->from('ta_ubigeo')
                        ->where(array('va_departamento' => 'LIMA', 'va_provincia' => 'LIMA'))->group('in_iddistrito');
        $selectString = $sql->getSqlStringForSqlObject($select);
        $results = $adapter->query($selectString, $adapter::QUERY_MODE_EXECUTE);
        $distrito = $results->toArray();
        
        $auxtipo = array();
        foreach ($distrito as $tipo) {
            $auxtipo[$tipo['in_iddistrito']] = $tipo['va_distrito'];
        }
       
        foreach($auxtipo as $key=>$value){
            if($key==$id){
            $auxdistrito=$auxtipo[$key];
            }
        }
        return $auxdistrito;
        }else{
            return;
        }
    }
    

    public function getUsuarioxEmail($email)
    {
        $row = $this->tableGateway->select(array(
            'va_email' => $email
        ));
        $resul = $row->current();
        
        if (! $resul) {
            throw new \Exception("Could not find row $email");
        }
        return $resul;
    }

    public function generarPassword($correo)
    {
        $mail = $this->getUsuarioxEmail($correo);
        $expFormat = mktime(date("H"), date("i"), date("s"), date("m"), date("d") + 3, date("Y"));
        $expDate = date("Y-m-d H:i:s", $expFormat);
        $idgenerada = sha1(uniqid($mail->in_id . substr($mail->va_nombre, 0, 8) . substr($mail->va_email, 0, 8).date("Y-m-d H:i:s"), 0));
        $data = array(
            'va_recupera_contrasena' => $idgenerada,
            'va_fecha_exp'=>$expDate
        );
        $this->tableGateway->update($data, array(
            'in_id' => $mail->in_id
        ));
        
        if (! $idgenerada) {
            throw new \Exception("No se puede generar password $idgenerada");
        }
        return $idgenerada;
    }

    public function cambiarPassword($password, $iduser) {
        $data = array(
            'va_contrasena' => sha1($password)
        );

        $actualiza = $this->tableGateway->getSql()->update()->table('ta_usuario')
                ->set($data)
                ->where(array('ta_usuario.in_id' => $iduser));
        $selectStringNotifca = $this->tableGateway->getSql()->getSqlStringForSqlObject($actualiza);
        $adapter1 = $this->tableGateway->getAdapter();
        $row = $adapter1->query($selectStringNotifca, $adapter1::QUERY_MODE_EXECUTE);

        if (!$row) {
            return false;
        }
        $this->eliminaPass($iduser);
        return true;
    }

    public function consultarPassword($password)
    {
        $curDate = date("Y-m-d H:i:s");
        $row = $this->tableGateway->select(array(
            'va_recupera_contrasena' => $password,
//             'va_fecha_exp'=>$curDate
        ));
        $row = $row->current();
        
        if (! $row) {
            throw new \Exception("Could not find row $password");
        }
        return $row;
    }

    public function eliminaPass($iduser)
    {
        $data = array(
            'va_recupera_contrasena' => null
        );
        $this->tableGateway->update($data,array('in_id'=>$iduser));
    }

    public function guardarUsuario(Usuario $usuario, $imagen,$valor=null,$pass=null,$catg_ingresada=null)
    {
        // public function guardarUsuario(Usuario $usuario,$notificacion=null){
        $data = array(
            'va_nombre' => $usuario->va_nombre,
            'va_email' => $usuario->va_email,
            'va_contrasena' => sha1($usuario->va_contrasena),
            'va_foto' => $imagen,
            'va_genero' => $usuario->va_genero,
            'va_descripcion' => $usuario->va_descripcion,
            'va_verificacion' => $valor,
            'va_estado' =>'desactivo',
            'ta_ubigeo_in_id'=>$usuario->ta_ubigeo_in_id,
            'va_facebook' => $usuario->va_facebook,
            'va_twitter' => $usuario->va_twitter,
                );
        $id = (int) $usuario->in_id;
        
        foreach($data as $key=>$value){
           if(empty($value)){
               $data[$key]=0;
           }
       }

        if ($id == 0) {
            $data['va_fecha_ingreso'] = date("Y-m-d H:i:s");
            $this->tableGateway->insert($data);
            $iduser = $this->tableGateway->getLastInsertValue();
            $adapter = $this->tableGateway->getAdapter();
            $sql = new Sql($adapter);
            $notifica = $sql->select()->from('ta_notificacion');
            $selectStringNotifca = $this->tableGateway->getSql()->getSqlStringForSqlObject($notifica);
            $adapter1 = $this->tableGateway->getAdapter();
            $cantNotif = $adapter1->query($selectStringNotifca, $adapter1::QUERY_MODE_EXECUTE);
            $cant = array();
            foreach ($cantNotif as $arrnot) {
                $cant[$arrnot['in_id']] = $arrnot['va_nombre'];
            }
            foreach ($cant as $key => $value) {
                $notif = $this->tableGateway->getSql()->insert()
                        ->into('ta_notificacion_has_ta_usuario')
                        ->values(array(
                    'ta_notificacion_in_id' => $key,
                    'ta_usuario_in_id' => $iduser));
                $selectStringNotif = $this->tableGateway->getSql()->getSqlStringForSqlObject($notif);
                $adapter2 = $this->tableGateway->getAdapter();
                $adapter2->query($selectStringNotif, $adapter2::QUERY_MODE_EXECUTE);
            }
            //para las categorias
//            if ($catg_ingresada != null) {
//                $categ = array();
//                foreach ($catg_ingresada as $arrcateg) {
//                    $categ[$arrcateg['in_id']] = $arrcateg['va_nombre'];
//                }
//                if (count($categ > 0)) {
//                    foreach ($categ as $key => $value) {
//                        $notif = $this->tableGateway->getSql()->insert()
//                                ->into('ta_usuario_has_ta_categoria')
//                                ->values(array(
//                            'ta_categoria_in_id' => $key,
//                            'ta_usuario_in_id' => $iduser));
//                        $selectStringNotif = $this->tableGateway->getSql()->getSqlStringForSqlObject($notif);
//                        $adapter2 = $this->tableGateway->getAdapter();
//                        $adapter2->query($selectStringNotif, $adapter2::QUERY_MODE_EXECUTE);
//                    }
//                }
//            }
        } else {
            if ($this->getUsuario($id)) {
                 $this->updateCategoria($catg_ingresada, $id);
                if ($pass == '') {
                    $data['va_estado'] = 'activo';
                    $data['va_verificacion'] = '';
                    $this->tableGateway->update($data, array(
                        'in_id' => $id));
                } else {
                    $data['va_contrasena'] = $pass;
                    $data['va_verificacion'] = '';
                    $data['va_estado'] = 'activo';

                    $this->tableGateway->update($data, array(
                        'in_id' => $id));
                }
            } else {
                throw new \Exception('no existe el usuario');
            }
        }
    }
    
    /*
     * Actualza las categorias en relacion con el usuario de la tabla ta_usuario_has_ta_categoria
     */
        public function updateCategoria($categoria, $id) {
//        if ($categoria != null) {
//            if (count($categoria) > 0) {
                if (count($this->getCategoriaxUsuario($id)->toArray()) > 0) {
                    $delete = $this->tableGateway->getSql()
                            ->delete()
                            ->from('ta_usuario_has_ta_categoria')
                            ->where(array(
                        'ta_usuario_in_id' => $id
                            ));
                    $selectStringDelete = $this->tableGateway->getSql()->getSqlStringForSqlObject($delete);
                    $adapter1 = $this->tableGateway->getAdapter();
                    $adapter1->query($selectStringDelete, $adapter1::QUERY_MODE_EXECUTE);

                    foreach ($categoria as $key => $value) {
                        $update = $this->tableGateway->getSql()
                                ->insert()
                                ->into('ta_usuario_has_ta_categoria')
                                ->values(array(
                            'ta_categoria_in_id' => $value,
                            'ta_usuario_in_id' => $id
                                ));

                        $selectStringUpdate = $this->tableGateway->getSql()->getSqlStringForSqlObject($update);
                        $adapter2 = $this->tableGateway->getAdapter();
                        $adapter2->query($selectStringUpdate, $adapter2::QUERY_MODE_EXECUTE);
                    }
                } else {
                      foreach ($categoria as $key => $value) {
                        $update = $this->tableGateway->getSql()
                                ->insert()
                                ->into('ta_usuario_has_ta_categoria')
                                ->values(array(
                            'ta_categoria_in_id' => $value,
                            'ta_usuario_in_id' => $id
                                ));

                        $selectStringUpdate = $this->tableGateway->getSql()->getSqlStringForSqlObject($update);
                        $adapter3 = $this->tableGateway->getAdapter();
                        $adapter3->query($selectStringUpdate, $adapter3::QUERY_MODE_EXECUTE);
                    }
                    
                }
//            }
//        }
    }
    /*
     * Obtiene las categorias del usuario
     */
         public function getCategoriaxUsuario($iduser){
         $adapter = $this->tableGateway->getAdapter();
         $sql = new Sql($adapter);
         $selecttot = $sql->select()
         ->from('ta_usuario_has_ta_categoria')
         ->join('ta_categoria','ta_categoria.in_id=ta_usuario_has_ta_categoria.ta_categoria_in_id',array('nombre_categ'=>'va_nombre'),'left')
         ->where(array('ta_usuario_has_ta_categoria.ta_usuario_in_id'=>$iduser));
         $selectString = $sql->getSqlStringForSqlObject($selecttot);
         $resultSet = $adapter->query($selectString, $adapter::QUERY_MODE_EXECUTE);
        return $resultSet;
    }
     /*
     * mienbros unidos al grupo al q pertenece otro usuario
     */
        public function UsuariosxGrupo($iduser=null) {
        $adapter = $this->tableGateway->getAdapter();

        $selectString ='SELECT DISTINCT(ta_usuario_has_ta_grupo.ta_usuario_in_id),
        ta_usuario_has_ta_grupo.va_estado,ta_usuario_has_ta_grupo.va_fecha,
        ta_usuario.va_nombre AS nombre_usuario, 
        ta_usuario.va_foto AS imagen, ta_usuario.va_descripcion AS descripcion_usuario 
        FROM ta_usuario_has_ta_grupo 
        LEFT JOIN ta_usuario ON ta_usuario.in_id=ta_usuario_has_ta_grupo.ta_usuario_in_id 
        WHERE ta_usuario_has_ta_grupo.ta_usuario_in_id != '.$iduser.' 
        AND ta_usuario_has_ta_grupo.va_estado = "activo"
        AND ta_usuario_has_ta_grupo.ta_grupo_in_id= ANY (
        SELECT ta_usuario_has_ta_grupo.ta_grupo_in_id 
        FROM ta_usuario_has_ta_grupo
        WHERE ta_usuario_has_ta_grupo.ta_usuario_in_id = '.$iduser.' 
        AND ta_usuario_has_ta_grupo.va_estado = "activo")
        GROUP BY ta_usuario_has_ta_grupo.ta_grupo_in_id LIMIT 4';

//        var_dump($selectString);Exit;
        $resultSet = $adapter->query($selectString, $adapter::QUERY_MODE_EXECUTE);
        return $resultSet;
    }
    /*
     * grupos a los que pertenece el id del usuario
     */
        public function UsuariosGrupo2($iduser=null) {
        $adapter = $this->tableGateway->getAdapter();
        $sql = new Sql($adapter);
        $select = $sql->select();
        $select->from('ta_usuario_has_ta_grupo')
                ->join('ta_usuario', 'ta_usuario.in_id=ta_usuario_has_ta_grupo.ta_usuario_in_id', 
                        array('nombre_usuario' => 'va_nombre', 'imagen' => 'va_foto', 
                            'descripcion_usuario' => 'va_descripcion'), 'left')
                ->join('ta_grupo','ta_grupo.in_id=ta_usuario_has_ta_grupo.ta_grupo_in_id',array(),'left')
                ->where(array(//'ta_usuario_has_ta_grupo.ta_grupo_in_id' => $id,
                    'ta_usuario_has_ta_grupo.ta_usuario_in_id'=>$iduser,
                    'ta_usuario_has_ta_grupo.va_estado' => 'activo'
                    ))->limit(4);

        $selectString = $sql->getSqlStringForSqlObject($select);
        var_dump();Exit;
        $resultSet = $adapter->query($selectString, $adapter::QUERY_MODE_EXECUTE);
        return $resultSet;
    }
    
            /*
     * grupos a los que pertenece el id del usuario
     */
        public function UsuariosGrupo($iduser=null) {
        $adapter = $this->tableGateway->getAdapter();
        $sql = new Sql($adapter);
        $select = $sql->select();
        $select->from('ta_usuario_has_ta_grupo')
//                ->join('ta_usuario', 'ta_usuario.in_id=ta_usuario_has_ta_grupo.ta_usuario_in_id', 
//                        array('nombre_usuario' => 'va_nombre', 'imagen' => 'va_foto', 
//                            'descripcion_usuario' => 'va_descripcion'), 'left')
                ->join('ta_grupo','ta_grupo.in_id=ta_usuario_has_ta_grupo.ta_grupo_in_id',
                        array('nombre_grupo' => 'va_nombre', 'imagen' => 'va_imagen', 
                            'descripcion_usuario' => 'va_descripcion'),'left')
                ->where(array(//'ta_usuario_has_ta_grupo.ta_grupo_in_id' => $id,
                    'ta_usuario_has_ta_grupo.ta_usuario_in_id'=>$iduser,
                    'ta_usuario_has_ta_grupo.va_estado' => 'activo'
                    ))->limit(4);

        $selectString = $sql->getSqlStringForSqlObject($select);
//        var_dump($selectString);Exit;
        $resultSet = $adapter->query($selectString, $adapter::QUERY_MODE_EXECUTE);
        return $resultSet;
    }
        public function usuariosgrupos($id)
    {
         $adapter = $this->tableGateway->getAdapter();
            $sql = new Sql($adapter);
            $selecttot = $sql->select()
          ->from('ta_usuario_has_ta_grupo')
          ->join('ta_grupo','ta_grupo.in_id=ta_usuario_has_ta_grupo.ta_grupo_in_id', array('nombre' =>'va_nombre','descripcion' =>'va_descripcion','imagen' =>'va_imagen','fecha' =>'va_fecha','id' =>'in_id'), 'left')         
          ->join('ta_categoria','ta_categoria.in_id=ta_grupo.ta_categoria_in_id', array('nombre_categoria' =>'va_nombre','idcategoria' =>'in_id'), 'left')              
          ->where(array('ta_usuario_has_ta_grupo.ta_usuario_in_id'=>$id,'ta_usuario_has_ta_grupo.va_estado'=>'activo','ta_grupo.va_estado'=>'activo'))
          ->where('ta_grupo.ta_usuario_in_id !='.$id)
           ->order('ta_usuario_has_ta_grupo.va_fecha DESC');
            $selectString = $sql->getSqlStringForSqlObject($selecttot);
            $resultSet = $adapter->query($selectString, $adapter::QUERY_MODE_EXECUTE);  
            return $resultSet->buffer();
    }
         public function categoriasunicas($id)
    {
         $adapter = $this->tableGateway->getAdapter();
            $sql = new Sql($adapter);
            $selecttot = $sql->select()
          ->from('ta_usuario_has_ta_grupo')
          ->join('ta_grupo','ta_grupo.in_id=ta_usuario_has_ta_grupo.ta_grupo_in_id', array('nombre' =>'va_nombre','descripcion' =>'va_descripcion','imagen' =>'va_imagen','fecha' =>'va_fecha','id' =>'in_id'), 'left')         
          ->join('ta_categoria','ta_categoria.in_id=ta_grupo.ta_categoria_in_id', array('nombre_categoria' => 'va_nombre','idcategoria' =>'in_id'), 'left')              
          ->where(array('ta_usuario_has_ta_grupo.ta_usuario_in_id'=>$id))
           ->group('ta_categoria.va_nombre');         
            $selectString = $sql->getSqlStringForSqlObject($selecttot);
            $resultSet = $adapter->query($selectString, $adapter::QUERY_MODE_EXECUTE);  
            return $resultSet;
    }
    
    public function grupossimilares($idcategoria,$id =null)
    {
         $adapter = $this->tableGateway->getAdapter();
            $sql = new Sql($adapter);
            $selecttot = $sql->select()
          ->from('ta_grupo')
          ->join('ta_categoria','ta_categoria.in_id=ta_grupo.ta_categoria_in_id', array('nombre_categoria_similar' =>'va_nombre'), 'left')
       //  ->join('ta_usuario_has_ta_grupo','ta_usuario_has_ta_grupo.ta_grupo_in_id=ta_grupo.in_id')                 
           ->where(array('ta_grupo.ta_categoria_in_id'=>$idcategoria,'ta_grupo.in_id<>?'=>$id,'ta_grupo.va_estado'=>'activo'));
            $selectString = $sql->getSqlStringForSqlObject($selecttot);
            $resultSet = $adapter->query($selectString, $adapter::QUERY_MODE_EXECUTE);  
            return $resultSet;
    }
    
     public function updateNotificacion($notificacion,$id){
         $adapter = $this->tableGateway->getAdapter();
         $sql = new Sql($adapter);
         if($notificacion != null){
                foreach($notificacion as $key=>$value){
                   $update = $this->tableGateway->getSql()->update()->table('ta_usuario_has_ta_notificacion')
                      ->join('ta_grupo','ta_grupo.in_id=ta_grupo_has_ta_notificacion.ta_grupo_in_id',array(),'left')
                      ->set(array('ta_grupo_has_ta_notificacion.ta_notificacion_in_id'=>$value))
                      ->where(array('ta_grupo.ta_usuario_in_id'=>$id));
                   $selectString = $sql->getSqlStringForSqlObject($update);
                   $adapter->query($selectString, $adapter::QUERY_MODE_EXECUTE);
//                    var_dump($selectString);Exit;
                }
         }
     }
     
     
      public function usuario($token)
    {
        $adapter = $this->tableGateway->getAdapter();
        $sql = new Sql($adapter);
        $selecttot = $sql->select()->from('ta_usuario')
                ->where(array('va_verificacion'=>$token,'va_estado'=>'desactivo'));
        $selectString = $sql->getSqlStringForSqlObject($selecttot);
        $resultSet = $adapter->query($selectString, $adapter::QUERY_MODE_EXECUTE);
        return $resultSet->toArray();
    }
      public function usuariocorreo($idface)
    {
        $adapter = $this->tableGateway->getAdapter();
        $sql = new Sql($adapter);
        $selecttot = $sql->select()->from('ta_usuario')
                ->where(array('id_facebook'=>$idface));
        $selectString = $sql->getSqlStringForSqlObject($selecttot);
        $resultSet = $adapter->query($selectString, $adapter::QUERY_MODE_EXECUTE);
        return $resultSet->toArray();
    }
        public function verificaCorreo($correo)
    {
        $adapter = $this->tableGateway->getAdapter();
        $sql = new Sql($adapter);
        $selecttot = $sql->select()->from('ta_usuario')
                ->where(array('va_email'=>$correo));
        $selectString = $sql->getSqlStringForSqlObject($selecttot);
        $resultSet = $adapter->query($selectString, $adapter::QUERY_MODE_EXECUTE);
        return $resultSet->current();
    }
    
    public function usuario1($correo)
    {
        $adapter = $this->tableGateway->getAdapter();
        $sql = new Sql($adapter);
        $selecttot = $sql->select()->from('ta_usuario')
                ->where(array('va_email'=>$correo));
        $selectString = $sql->getSqlStringForSqlObject($selecttot);
        $resultSet = $adapter->query($selectString, $adapter::QUERY_MODE_EXECUTE);
        return $resultSet->toArray();
    }
     public function cambiarestado($id)
    {
        $adapter = $this->tableGateway->getAdapter();
        $sql = new Sql($adapter);
        $selecttot = $sql->update('ta_usuario')
                ->set(array('va_verificacion'=>'','va_estado'=>'activo'))
                ->where(array('in_id'=>$id));
        $selectString = $sql->getSqlStringForSqlObject($selecttot);
                   $adapter->query($selectString, $adapter::QUERY_MODE_EXECUTE);
    }
    
    public function idfacebook($id,$idfacebook,$logout)
    {
        $adapter = $this->tableGateway->getAdapter();
        $sql = new Sql($adapter);
        $selecttot = $sql->update('ta_usuario')
                ->set(array('id_facebook'=>$idfacebook,'va_logout'=>$logout))
                ->where(array('in_id'=>$id));
        $selectString = $sql->getSqlStringForSqlObject($selecttot);
                   $adapter->query($selectString, $adapter::QUERY_MODE_EXECUTE);
    }
public function idfacebook2($id,$logout)
    {
        $adapter = $this->tableGateway->getAdapter();
        $sql = new Sql($adapter);
        $selecttot = $sql->update('ta_usuario')
                ->set(array('va_logout'=>$logout))
                ->where(array('in_id'=>$id));
        $selectString = $sql->getSqlStringForSqlObject($selecttot);
                   $adapter->query($selectString, $adapter::QUERY_MODE_EXECUTE);
    }
    
     public function insertarusuariofacebbok($nombre,$email,$idfacebook,$foto,$logout,$genero)
    {   $contrasena = sha1($idfacebook) ;
        $adapter = $this->tableGateway->getAdapter();
        $sql = new Sql($adapter);
        $selecttot = $sql->insert()
                ->into('ta_usuario')
                ->values(array('va_nombre'=>$nombre,'va_email'=>$email,'id_facebook'=>$idfacebook,
                    'va_estado'=>'activo','va_contrasena'=>$contrasena,'va_foto'=>$foto
                   ,'va_logout'=>$logout,'va_genero'=>$genero));
        $selectString = $sql->getSqlStringForSqlObject($selecttot);
      $adapter->query($selectString, $adapter::QUERY_MODE_EXECUTE);
    }

     public function getPais()
    {
        $adapter = $this->tableGateway->getAdapter();
        $sql = new Sql($adapter);
        $select = $sql->select()
                        ->columns(array('Code', 'Name'))
                        ->from('country');
        $selectString = $sql->getSqlStringForSqlObject($select);
        $results = $adapter->query($selectString, $adapter::QUERY_MODE_EXECUTE);
       return $results->toArray();
    }
     public function getCiudad($ciudad)
    {
        $adapter = $this->tableGateway->getAdapter();
        $sql = new Sql($adapter);
        $select = $sql->select()
                        ->columns(array('ID', 'Name'))
                        ->from('city')
         ->where(array('CountryCode' =>$ciudad ))->group('ID');
        $selectString = $sql->getSqlStringForSqlObject($select);
        $results = $adapter->query($selectString, $adapter::QUERY_MODE_EXECUTE);
       return $results->toArray();
    
    }
     public function getCiudadPeru()
    {
        $adapter = $this->tableGateway->getAdapter();
        $sql = new Sql($adapter);
        $select = $sql->select()
                        ->columns(array('ID'=>'in_id', 'Name'=>'va_provincia'))
                        ->from('ta_ubigeo')
         ->where(array('in_idpais' =>1))->group('va_provincia');
        $selectString = $sql->getSqlStringForSqlObject($select);
        $results = $adapter->query($selectString, $adapter::QUERY_MODE_EXECUTE);
       return $results->toArray();
    
    }
}

//,'ta_usuario_has_ta_grupo.ta_usuario_in_id<>?'=>$id