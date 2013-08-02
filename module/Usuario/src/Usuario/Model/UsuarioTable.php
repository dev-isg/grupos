<?php

namespace Usuario\Model;
use Zend\Db\TableGateway\AbstractTableGateway;
use Zend\Db\TableGateway\TableGateway;
use Zend\Db\Sql\Sql;
use Zend\Db\Sql\Select;
use Platos\Model\Platos;

class UsuarioTable{

    
    protected $tableGateway;
    
    public function __construct(TableGateway $tableGateway) {
        $this->tableGateway=$tableGateway;
    }
    
    public function fetchAll($id=null){

            $adapter = $this->tableGateway->getAdapter();
            $sql = new Sql($adapter);
            $selecttot = $sql->select()
                    ->from('ta_usuario')
                    ->join('ta_ubigeo','ta_usuario.ta_ubigeo_in_id=ta_ubigeo.in_id',array(),'left');

                   $selecttot ->group('ta_usuario.in_id')->order('ta_usuario.in_id desc');
            $selectString = $sql->getSqlStringForSqlObject($selecttot);
            $resultSet = $adapter->query($selectString, $adapter::QUERY_MODE_EXECUTE);

        return $resultSet;
    }
    
        public function getUsuario($id)
    {
        $id  = (int) $id;
        $row = $this->tableGateway->select(array('in_id' => $id));
        $row = $row->current();

        if (!$row) {
            throw new \Exception("Could not find row $id");
        }
        return $row;
    }
    
    public function guardarUsuario(Usuario $usuario){
//    public function guardarUsuario(Usuario $usuario,$notificacion=null){
      
      $data=array(
         'va_nombre'=>$usuario->va_nombre,
          'va_email'=>$usuario->va_email,
          'va_contraseña'=>$usuario->va_contraseña,
          'va_dni'=>$usuario->va_dni,
          'va_foto'=>$usuario->va_foto['name'],
          'va_genero'=>$usuario->va_genero,
         'va_descripcion'=>$usuario->va_descripcion,
          'ta_ubigeo_in_id'=>$usuario->ta_ubigeo_in_id,
      );
      $id = (int) $usuario->in_id;
  
      foreach($data as $key=>$value){
          if(empty($value)){
              $data[$key]=0;
          }
      }
     
//      if ($id == 0) {
//          $this->tableGateway->insert($data);
//          $idusuario=$this->tableGateway->getLastInsertValue();
//          
//             if($notificacion!=null){
//                foreach($notificacion as $key=>$value){
//                    $insert = $this->tableGateway->getSql()->insert()->into('ta_grupo_has_ta_notificacion')
//                           ->values(array('ta_grupo_in_id'=>$idgrupo,'ta_notificacion_in_id'=>$value));
//                   $selectString2 = $this->tableGateway->getSql()->getSqlStringForSqlObject($insert);
//                   $adapter=$this->tableGateway->getAdapter();
//                   $adapter->query($selectString2, $adapter::QUERY_MODE_EXECUTE);
//                }
//             }
//      }else {
//          
//            if ($this->getGrupo($id)) {
//                $this->tableGateway->update($data, array('in_id' => $id));
//                    if($notificacion!=null){
////                        print_r($notificacion);exit();
//                           $delete=$this->tableGateway->getSql()->delete()->from('ta_grupo_has_ta_notificacion')
//                                   ->where(array('ta_grupo_in_id'=>$id));
//                           $selectStringDelete = $this->tableGateway->getSql()->getSqlStringForSqlObject($delete);
//                           $adapter1=$this->tableGateway->getAdapter();
//                           $adapter1->query($selectStringDelete, $adapter1::QUERY_MODE_EXECUTE);
//                        foreach($notificacion as $key=>$value){
////                            $update = $this->tableGateway->getSql()->update()->table('ta_grupo_has_ta_notificacion')
////                                    ->set(array('ta_notificacion_in_id'=>$value))
////                                    ->where(array('ta_grupo_in_id'=>$id));  
//                          $update = $this->tableGateway->getSql()->insert()->into('ta_grupo_has_ta_notificacion')
//                           ->values(array('ta_grupo_in_id'=>$id,'ta_notificacion_in_id'=>$value));
//                          
//                           $selectStringUpdate = $this->tableGateway->getSql()->getSqlStringForSqlObject($update);
//                           $adapter2=$this->tableGateway->getAdapter();
//                           $adapter2->query($selectStringUpdate, $adapter2::QUERY_MODE_EXECUTE);
//                        }
//                    }
//                
//            } else {
//                throw new \Exception('no existe el usuario');
//            }
//        }
      
  }
    
    
    
}
