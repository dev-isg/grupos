<?php
namespace Grupo\Model;
use Zend\Db\TableGateway\AbstractTableGateway;
use Zend\Db\TableGateway\TableGateway;
use Zend\Db\Sql\Sql;
use Zend\Db\Sql\Select;
use Platos\Model\Platos;

class GrupoTable{
    protected $tableGateway;
    
    public function __construct(TableGateway $tableGateway) {
        $this->tableGateway=$tableGateway;
    }
    
    public function fetchAll($id=null){
//        primera forma: tienes q poner los alias en el modelo sino no funciona
//        $sqlSelect = $this->tableGateway->getSql()->select()
//            ->join('ta_categoria','ta_grupo.ta_categoria_in_id=ta_categoria.in_id',array(),'left')
//            ->join('ta_usuario','ta_grupo.ta_usuario_in_id=ta_usuario.in_id',array('nombre_user'=>'va_nombre','va_email','va_dni','va_foto'),'left')
//            ->join(array('u'=>'ta_ubigeo'),'ta_ubigeo_in_id=u.in_id',array('va_pais','va_departamento','va_provincia','va_distrito'),'left')
//            ->group('ta_grupo.in_id')->order('ta_grupo.in_id desc');
//        
//       $resultSet = $this->tableGateway->selectWith($sqlSelect);

            $adapter = $this->tableGateway->getAdapter();
            $sql = new Sql($adapter);
            $selecttot = $sql->select()
                    ->from('ta_grupo')
                    ->join('ta_categoria','ta_grupo.ta_categoria_in_id=ta_categoria.in_id',array('nombre_categ'=>'va_nombre'),'left')
                    ->join('ta_usuario','ta_grupo.ta_usuario_in_id=ta_usuario.in_id',array('nombre_user'=>'va_nombre','va_email','va_dni','va_foto'),'left');
//                    ->join(array('u'=>'ta_ubigeo'),'ta_ubigeo_in_id=u.in_id',array('va_pais','va_departamento','va_provincia','va_distrito'),'left');
                    
//            if($id!=null){
//                $selecttot->join(array('gn'=>'ta_grupo_has_ta_notificacion'),'ta_grupo.in_id=gn.ta_grupo_in_id',array(),'left');
////                $selecttot->join(array('tn'=>'ta_notificacion'),'gn.ta_notificacion_in_id=tn.in_id',array('tipo_notificacion'=>'in_id'),'left');
//                $selecttot->where(array('ta_grupo.in_id'=>$id));
//            }
                   $selecttot ->group('ta_grupo.in_id')->order('ta_grupo.in_id desc');
            $selectString = $sql->getSqlStringForSqlObject($selecttot);
         //   var_dump($selectString);exit;
            $resultSet = $adapter->query($selectString, $adapter::QUERY_MODE_EXECUTE);

//            var_dump($resultSet->toArray());exit;
        return $resultSet;
    }
    
    
  
    
    
    public function buscarGrupo($nombre=null,$tipo=null){
            $adapter = $this->tableGateway->getAdapter();
            $sql = new Sql($adapter);
            $selecttot = $sql->select()
                    ->from('ta_grupo')
                    ->join('ta_categoria','ta_grupo.ta_categoria_in_id=ta_categoria.in_id',array('nombre_categ'=>'va_nombre'),'left')
                    ->join('ta_usuario','ta_grupo.ta_usuario_in_id=ta_usuario.in_id',array('nombre_user'=>'va_nombre','va_email','va_dni','va_foto'),'left');
            if($tipo!=null){
                $selecttot->where(array('ta_grupo.ta_categoria_in_id'=>$tipo));
            }
            if($nombre!=null){
                $selecttot->where(array('ta_grupo.va_nombre'=>$nombre));
                
            }
            $selecttot ->group('ta_grupo.in_id')->order('ta_grupo.in_id desc');
        
            $selectString = $sql->getSqlStringForSqlObject($selecttot);
           // var_dump($selectString);exit;
            $resultSet = $adapter->query($selectString, $adapter::QUERY_MODE_EXECUTE);
            
            return $resultSet;
        
    }
    public function getGrupo($id)
    {
        $id  = (int) $id;
        $row = $this->tableGateway->select(array('in_id' => $id));
        $row = $row->current();
//        $row = $this->fetchAll($id);

//var_dump($row->current());exit;
        if (!$row) {
            throw new \Exception("Could not find row $id");
        }
        return $row;
    }
    
  public function guardarGrupo(Grupo $grupo,$notificacion=null){
      
//      $pais=$grupo->pais;
//      $departamento=$grupo->departamento;
//      $provincia=$grupo->provincia;
//      $distrito=$grupo->distrito;
//        
//        $adapter = $this->tableGateway->getAdapter();
//        $sql = new Sql($adapter);
//        $idubigeo = $sql->select()->from('ta_ubigeo')
//                ->columns(array('in_id'))
//                ->where(array('in_idpais' => $pais, 'in_iddep' => $departamento, 'in_idprov' => $provincia, 'in_iddis' => $distrito));
//        $selectString0 = $this->tableGateway->getSql()->getSqlStringForSqlObject($idubigeo);
//
//        $result = $adapter->query($selectString0, $adapter::QUERY_MODE_EXECUTE);
//        $convertir = $result->toArray();
          
      $data=array(
         'va_nombre'=>$grupo->va_nombre,
         'va_descripcion'=>$grupo->va_descripcion,
//         'va_costo'=>$grupo->va_costo,
//         'va_latitud'=>$grupo->va_latitud,
//          'va_longitud'=>$grupo->va_longitud,
//          'va_direccion'=>$grupo->va_direccion,
//          'va_referencia'=>$grupo->va_referencia,
          'va_imagen'=>$grupo->va_imagen['name'],
          'va_estado'=>$grupo->va_estado,
//          'va_dirigido'=>$grupo->va_dirigido,
          'ta_usuario_in_id'=>$grupo->ta_usuario_in_id,
          'ta_categoria_in_id'=>$grupo->ta_categoria_in_id,
//          'ta_ubigeo_in_id'=>$grupo->ta_ubigeo_in_id//distrito,//$convertir[0]['in_id']            
      );
      $id = (int) $grupo->in_id;
  
      foreach($data as $key=>$value){
          if(empty($value)){
              $data[$key]=0;
          }
      }
     
      if ($id == 0) {
          $this->tableGateway->insert($data);
          $idgrupo=$this->tableGateway->getLastInsertValue();
          
//             if($notificacion!=null){
//                foreach($notificacion as $key=>$value){
//                    $insert = $this->tableGateway->getSql()->insert()->into('ta_grupo_has_ta_notificacion')
//                           ->values(array('ta_grupo_in_id'=>$idgrupo,'ta_notificacion_in_id'=>$value));
//                   $selectString2 = $this->tableGateway->getSql()->getSqlStringForSqlObject($insert);
//                   $adapter=$this->tableGateway->getAdapter();
//                   $adapter->query($selectString2, $adapter::QUERY_MODE_EXECUTE);
//                }
//             }
      }else {
          
            if ($this->getGrupo($id)) {
                $this->tableGateway->update($data, array('in_id' => $id));
                    if($notificacion!=null){
//                        print_r($notificacion);exit();
                           $delete=$this->tableGateway->getSql()->delete()->from('ta_grupo_has_ta_notificacion')
                                   ->where(array('ta_grupo_in_id'=>$id));
                           $selectStringDelete = $this->tableGateway->getSql()->getSqlStringForSqlObject($delete);
                           $adapter1=$this->tableGateway->getAdapter();
                           $adapter1->query($selectStringDelete, $adapter1::QUERY_MODE_EXECUTE);
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
                    }
                
            } else {
                throw new \Exception('no existe el usuario');
            }
        }
      
  }
  
     public function eliminarGrupo($id, $estado) {
        $data = array(
            'va_estado' => $estado,
        );
        $this->tableGateway->update($data, array('in_id' => $id));
     }
     

     
     public function getNotifiaciones($id){
          $adapter = $this->tableGateway->getAdapter();
            $sql = new Sql($adapter);
            $selecttot = $sql->select()
                    ->from('ta_grupo_has_ta_notificacion')
                    ->where(array('ta_grupo_in_id'=>$id));
            $selectString = $sql->getSqlStringForSqlObject($selecttot);
            $resultSet = $adapter->query($selectString, $adapter::QUERY_MODE_EXECUTE);

        return $resultSet;
         
     }
     
     public function unirseGrupo($idgrup,$iduser){
           $insert = $this->tableGateway->getSql()->insert()->into('ta_usuario_has_ta_grupo')
                   ->values(array('ta_usuario_in_id'=>$iduser,'ta_grupo_in_id'=>$idgrup,'va_estado'=>'activo'));
           $selectString = $this->tableGateway->getSql()->getSqlStringForSqlObject($insert);
           $adapter=$this->tableGateway->getAdapter();
           $row=$adapter->query($selectString, $adapter::QUERY_MODE_EXECUTE);
          if (!$row) {
            throw new \Exception("No se puede unir al grupo");
            }
          return true;

     }
     
     public function retiraGrupo($idgrup,$iduser){
            $update = $this->tableGateway->getSql()->update()->table('ta_usuario_has_ta_grupo')
                    ->set(array('va_estado'=>'desactivo'))
                    ->where(array('ta_usuario_in_id'=>$iduser,'ta_grupo_in_id'=>$idgrup));  
           $selectStringUpdate = $this->tableGateway->getSql()->getSqlStringForSqlObject($update);
           $adapter=$this->tableGateway->getAdapter();
           $row=$adapter->query($selectStringUpdate, $adapter::QUERY_MODE_EXECUTE);  
            if (!$row) {
            throw new \Exception("No se puede retirar al grupo");
            }
          return true;
     }
     
     public function usuarioxGrupo($iduser=null){
            $adapter = $this->tableGateway->getAdapter();
            $sql = new Sql($adapter);
            $selecttot = $sql->select()
                    ->from('ta_usuario_has_ta_grupo')
                    ->join(array('tg'=>'ta_grupo'),'tg.in_id=ta_usuario_has_ta_grupo.ta_grupo_in_id',array('nom_grup'=>'va_nombre'),'LEFT')
                    ->where(array('ta_usuario_has_ta_grupo.va_estado'=>'activo'));
         if($iduser!=null){
             $selecttot->where(array('ta_usuario_has_ta_grupo.ta_usuario_in_id'=>$iduser));
             
         }
           // $selecttot ->order('ta_grupo.in_id desc');
            $selectString = $sql->getSqlStringForSqlObject($selecttot);
            $resultSet = $adapter->query($selectString, $adapter::QUERY_MODE_EXECUTE);
            
         if (!$resultSet) {
            throw new \Exception("No se puede encontrar el/los grupo(s)");
        }
            return $resultSet->toArray();
         
     }
     
      public function tipoCategoria()
   {   
        $adapter = $this->tableGateway->getAdapter();
        $sql = new Sql($adapter);
        $select = $sql->select()
            ->from('ta_categoria'); 
            $selectString = $sql->getSqlStringForSqlObject($select);
            $results = $adapter->query($selectString, $adapter::QUERY_MODE_EXECUTE);
//            $tipocateg=$results->toArray();
//        $auxtipo=array();
//        foreach($tipocateg as $tipo){
//            $auxtipo[$tipo['in_id']] = $tipo['va_nombre'];      
//        }
            return $results;
            
     }
}
