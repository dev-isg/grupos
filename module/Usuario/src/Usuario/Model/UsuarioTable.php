<?php
namespace Usuario\Model;

use Zend\Db\TableGateway\AbstractTableGateway;
use Zend\Db\TableGateway\TableGateway;
use Zend\Db\Sql\Sql;
use Zend\Db\Sql\Select;

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
        $selecttot = $sql->select()->from('ta_usuario')
//                    ->join('ta_ubigeo','ta_usuario.ta_ubigeo_in_id=ta_ubigeo.in_id',array(),'left')
                           ;;

        ;
        
        ;
        
        ;
        
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
        $row = $row->current();
        
        if (! $row) {
            throw new \Exception("Could not find row $id");
        }
        return $row;
    }

    public function getUsuarioxEmail($email)
    {
        $row = $this->tableGateway->select(array(
            'va_email' => $email
        ));
        $row = $row->current();
        
        if (! $row) {
            throw new \Exception("Could not find row $email");
        }
        return $row;
    }

    public function generarPassword($correo)
    {
        $mail = $this->getUsuarioxEmail($correo);
        $expFormat = mktime(date("H"), date("i"), date("s"), date("m"), date("d") + 3, date("Y"));
        $expDate = date("Y-m-d H:i:s", $expFormat);
        $idgenerada = uniqid($mail->in_id . substr($mail->va_nombre, 0, 8) . substr($mail->va_email, 0, 8), 0);
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

    public function cambiarPassword($password, $iduser)
    {
        $data = array(
            'va_contrasena' => $password
        );
        $this->tableGateway->update($data, array(
            'in_id' => $iduser
        ));
        $this->eliminaPass($iduser);
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

    public function guardarUsuario(Usuario $usuario, $imagen)
    {
        // public function guardarUsuario(Usuario $usuario,$notificacion=null){
        $data = array(
            'va_nombre' => $usuario->va_nombre,
            'va_email' => $usuario->va_email,
            'va_contrasena' => sha1($usuario->va_contrasena),
            'va_dni' => $usuario->va_dni,
//            'va_foto' => $usuario->va_foto['name'],
            'va_foto' => $imagen,
            'va_genero' => $usuario->va_genero,
            'va_descripcion' => $usuario->va_descripcion
        // 'ta_ubigeo_in_id'=>$usuario->ta_ubigeo_in_id,
                );
//         print_r($imagen);
//            exit;
//         var_dump($data);
//         exit;
        $id = (int) $usuario->in_id;
        
        foreach($data as $key=>$value){
           if(empty($value)){
               $data[$key]=0;
           }
       }
        // var_dump($data);
        // exit();
        
        if ($id == 0) {
            $this->tableGateway->insert($data);
            // $idusuario=$this->tableGateway->getLastInsertValue();
        } else {
            
            if ($this->getUsuario($id)) {
                $this->tableGateway->update($data, array(
                    'in_id' => $id
                ));
            } else {
                throw new \Exception('no existe el usuario');
            }
        }
    }

    
        public function usuariosgrupos($id)
    {
         $adapter = $this->tableGateway->getAdapter();
            $sql = new Sql($adapter);
            $selecttot = $sql->select()
          ->from('ta_usuario_has_ta_grupo')
          ->join('ta_grupo','ta_grupo.in_id=ta_usuario_has_ta_grupo.ta_grupo_in_id', array('nombre' =>'va_nombre','descripcion' =>'va_descripcion','imagen' =>'va_imagen','fecha' =>'va_fecha','id' =>'in_id'), 'left')         
          ->join('ta_categoria','ta_categoria.in_id=ta_grupo.ta_categoria_in_id', array('nombre_categoria' =>'va_nombre','idcategoria' =>'in_id'), 'left')              
          ->where(array('ta_usuario_has_ta_grupo.ta_usuario_in_id'=>$id,'ta_grupo.va_estado'=>'activo'));
            $selectString = $sql->getSqlStringForSqlObject($selecttot);
            $resultSet = $adapter->query($selectString, $adapter::QUERY_MODE_EXECUTE);  
            return $resultSet;
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

//,'ta_usuario_has_ta_grupo.ta_usuario_in_id<>?'=>$id