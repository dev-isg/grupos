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
 
            $idgenerada = uniqid($mail->in_id.substr($mail->va_nombre,0,8).substr($mail->va_email,0,8),0);
        if (!$idgenerada) {
            throw new \Exception("No se puede generar password $idgenerada");
        }
        return $idgenerada;
    }

    public function guardarUsuario(Usuario $usuario)
    {
        // public function guardarUsuario(Usuario $usuario,$notificacion=null){
        $data = array(
            'va_nombre' => $usuario->va_nombre,
            'va_email' => $usuario->va_email,
            'va_contrasena' => sha1($usuario->va_contrasena),
            'va_dni' => $usuario->va_dni,
            'va_foto' => $usuario->va_foto['name'],
            'va_genero' => $usuario->va_genero,
            'va_descripcion' => $usuario->va_descripcion
        // 'ta_ubigeo_in_id'=>$usuario->ta_ubigeo_in_id,
                );
        // var_dump($data);
        // exit;
        $id = (int) $usuario->in_id;
        
        // foreach($data as $key=>$value){
        // if(empty($value)){
        // $data[$key]=0;
        // }
        // }
        //
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
}
