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
    
    public function fetchAll($id = null) {
        $adapter = $this->tableGateway->getAdapter();
        $selectString = 'select ta_grupo.*,ta_categoria.va_nombre as nombre_categoria,
        ta_categoria.in_id as idcategoria    
        from ta_grupo left join ta_categoria on 
         ta_grupo.ta_categoria_in_id=ta_categoria.in_id   
        where ta_grupo.va_estado=1 order by 
        (select ta_evento.va_fecha_ingreso from ta_evento 
        where ta_grupo_in_id=ta_grupo.in_id  order by ta_evento.in_id DESC LIMIT 1) DESC';
        $resultSet = $adapter->query($selectString, $adapter::QUERY_MODE_EXECUTE);
        return $resultSet->buffer();
    }

    public function buscarGrupo($nombre=null,$tipo=null){
            $adapter = $this->tableGateway->getAdapter();
            $sql = new Sql($adapter);
            $selecttot = $sql->select()
                    ->from('ta_grupo')
                    ->join('ta_categoria','ta_grupo.ta_categoria_in_id=ta_categoria.in_id',array('nombre_categoria'=>'va_nombre','idcategoria'=>'in_id'),'left')
                    ->join('ta_usuario','ta_grupo.ta_usuario_in_id=ta_usuario.in_id',array('nombre_user'=>'va_nombre','va_email','va_dni','va_foto'),'left');
//                    ->join('ta_evento','ta_grupo.in_id=ta_evento.ta_grupo_in_id',array(),'left');
            if($tipo!=null){
                $selecttot->where(array('ta_grupo.va_estado'=>'activo','ta_grupo.ta_categoria_in_id'=>$tipo));
            }
            if($nombre!=null){
                $selecttot->where(array('ta_grupo.va_nombre LIKE ?'=>'%'.$nombre.'%','ta_grupo.va_estado'=>'activo'));
                        // ->where(array('ta_grupo.va_estado'=>'activo'));
                
            }
//            $selecttot ->order('ta_evento.va_fecha_ingreso desc');//->group('ta_grupo.in_id')->order('ta_grupo.in_id desc');
             $selecttot ->order('ta_grupo.in_id DESC');
            $selectString = $sql->getSqlStringForSqlObject($selecttot);
         
            $resultSet =  $adapter->query($selectString, $adapter::QUERY_MODE_EXECUTE);
          if (!$resultSet) {
            throw new \Exception("Could not find row");
        }
            
            return $resultSet->buffer();//->buffer();
        
    }
    /*
     * Obtiene la inforamacion de un grupo
     */
    public function getGrupo($id)
    {
        $id  = (int) $id;
        $row = $this->tableGateway->select(array('in_id' => $id));
        $row = $row->current();
        if (!$row) {
            throw new \Exception("Could not find row $id");
        }
        return $row;
    }
    
    public function grupoxUsuario($idgrupo)
    {
        $adapter = $this->tableGateway->getAdapter();
        $sql = new Sql($adapter);
        $selecttot = $sql->select()
        ->columns(array('nombre_usuario'=>'va_nombre','va_email'))
        ->from('ta_usuario')
        ->join('ta_grupo','ta_usuario.in_id=ta_grupo.ta_usuario_in_id',array('*'), 'left')
        ->where(array('ta_grupo.in_id'=>$idgrupo));
        $selectString = $sql->getSqlStringForSqlObject($selecttot);
        $resultSet = $adapter->query($selectString, $adapter::QUERY_MODE_EXECUTE);
        return $resultSet;
    }
    
    public function usuariosUnidosGrupo($idgrupo)
    {
        $adapter = $this->tableGateway->getAdapter();
        $sql = new Sql($adapter);
        $selecttot = $sql->select()
        ->from('ta_usuario_has_ta_grupo')
        ->join('ta_usuario','ta_usuario.in_id=ta_usuario_has_ta_grupo.ta_usuario_in_id',array('*'), 'left')
        ->where(array('ta_usuario_has_ta_grupo.ta_grupo_in_id'=>$idgrupo));
        $selectString = $sql->getSqlStringForSqlObject($selecttot);
        $resultSet = $adapter->query($selectString, $adapter::QUERY_MODE_EXECUTE);
        return $resultSet;
    }
    
    
  public function guardarGrupo(Grupo $grupo,$notificacion=null,$iduser=null,$imagen){

          
      $data=array(
         'va_nombre'=>$grupo->va_nombre,
         'va_descripcion'=>$grupo->va_descripcion,
//         'va_costo'=>$grupo->va_costo,
//         'va_latitud'=>$grupo->va_latitud,
//          'va_longitud'=>$grupo->va_longitud,
//          'va_direccion'=>$grupo->va_direccion,
//          'va_referencia'=>$grupo->va_referencia,
          'va_imagen'=>$imagen,
          'va_estado'=>$grupo->va_estado,
//          'va_dirigido'=>$grupo->va_dirigido,
          'ta_usuario_in_id'=>$iduser,//$idusuario['in_id'],//$grupo->ta_usuario_in_id,
          'ta_categoria_in_id'=>$grupo->ta_categoria_in_id,
//          'ta_ubigeo_in_id'=>$grupo->ta_ubigeo_in_id//distrito,//$convertir[0]['in_id']            
      );
      //var_dump($imagen);exit;
      $id = (int) $grupo->in_id;
  
      foreach($data as $key=>$value){
          if(empty($value)){
              $data[$key]=0;
          }
      }
     
      if ($id == 0) {
          $data['va_fecha']=date('c');
          $this->tableGateway->insert($data);
          $idgrupo=$this->tableGateway->getLastInsertValue();
          if($this->aprobarUsuario($idgrupo,$iduser, 'activo')){// $this->unirseGrupo($idgrupo,$iduser)
              return $idgrupo;
          }        
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
//                     if($notificacion!=null){
//                            $delete=$this->tableGateway->getSql()->delete()->from('ta_grupo_has_ta_notificacion')
//                                    ->where(array('ta_grupo_in_id'=>$id));
//                            $selectStringDelete = $this->tableGateway->getSql()->getSqlStringForSqlObject($delete);
//                            $adapter1=$this->tableGateway->getAdapter();
//                            $adapter1->query($selectStringDelete, $adapter1::QUERY_MODE_EXECUTE);
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
//                     }
                
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
     
     public function getNotifiacionesxUsuario($iduser){
         $adapter = $this->tableGateway->getAdapter();
         $sql = new Sql($adapter);
         $selecttot = $sql->select()
//          ->from('ta_grupo_has_ta_notificacion')
         ->from('ta_notificacion_has_ta_usuario')
         ->join('ta_usuario','ta_usuario.in_id=ta_notificacion_has_ta_usuario.ta_usuario_in_id',array(),'left')
         ->join('ta_notificacion','ta_notificacion.in_id=ta_notificacion_has_ta_usuario.ta_notificacion_in_id',array('va_nombre'),'left')
         ->where(array('ta_usuario.in_id'=>$iduser));
//          ->join('ta_grupo','ta_grupo.in_id=ta_grupo_has_ta_notificacion.ta_grupo_in_id',array(),'left')
//          ->where(array('ta_grupo.ta_usuario_in_id'=>$iduser));
         $selectString = $sql->getSqlStringForSqlObject($selecttot);
         $resultSet = $adapter->query($selectString, $adapter::QUERY_MODE_EXECUTE);
        
        return $resultSet;
    }

    public function updateNotificacion($notificacion, $id)
    {
        $adapter = $this->tableGateway->getAdapter();
        $sql = new Sql($adapter);
        
        if ($notificacion != null) {
            $delete = $this->tableGateway->getSql()
                ->delete()
                ->from('ta_notificacion_has_ta_usuario')
                ->where(array(
                'ta_usuario_in_id' => $id
            ));
            $selectStringDelete = $this->tableGateway->getSql()->getSqlStringForSqlObject($delete);
            $adapter1 = $this->tableGateway->getAdapter();
            $adapter1->query($selectStringDelete, $adapter1::QUERY_MODE_EXECUTE);
            foreach ($notificacion as $key => $value) {
                $update = $this->tableGateway->getSql()
                    ->insert()
                    ->into('ta_notificacion_has_ta_usuario')
                    ->values(array(
                    'ta_notificacion_in_id' => $value,
                    'ta_usuario_in_id' => $id
                    
                ));
                
                $selectStringUpdate = $this->tableGateway->getSql()->getSqlStringForSqlObject($update);
                $adapter2 = $this->tableGateway->getAdapter();
                $adapter2->query($selectStringUpdate, $adapter2::QUERY_MODE_EXECUTE);
             }
           }else{
                    $delete = $this->tableGateway->getSql()
                        ->delete()
                        ->from('ta_notificacion_has_ta_usuario')
                        ->where(array(
                        'ta_usuario_in_id' => $id
                    ));
                    $selectStringDelete = $this->tableGateway->getSql()->getSqlStringForSqlObject($delete);
                    $adapter1 = $this->tableGateway->getAdapter();
                    $adapter1->query($selectStringDelete, $adapter1::QUERY_MODE_EXECUTE);
             
         }    
     }
     
   public function getGrupoUsuarioDetalle($idgrupo,$iduser){
         $adapter = $this->tableGateway->getAdapter();
         $sql = new Sql($adapter);
         $selecttot = $sql->select()
         ->from('ta_usuario_has_ta_grupo')
         ->where(array('ta_grupo_in_id'=>$idgrupo,'ta_usuario_in_id'=>$iduser,'va_estado'=>'activo'));//
         $selectString = $this->tableGateway->getSql()->getSqlStringForSqlObject($selecttot);

         $adapter=$this->tableGateway->getAdapter();
         $row=$adapter->query($selectString, $adapter::QUERY_MODE_EXECUTE);
         if (!$row) {
             throw new \Exception("No se encontro evento");
         }
         return $row->current();
     }
     /*
      * Obtiene datos del grupo y del usuario al que se unio
      */
     public function getGrupoUsuario($idgrupo,$iduser){
         $adapter = $this->tableGateway->getAdapter();
         $sql = new Sql($adapter);
         $selecttot = $sql->select()
         ->from('ta_usuario_has_ta_grupo')
         ->where(array('ta_grupo_in_id'=>$idgrupo,'ta_usuario_in_id'=>$iduser));//,'va_estado'=>'activo'
         $selectString = $this->tableGateway->getSql()->getSqlStringForSqlObject($selecttot);

         $adapter=$this->tableGateway->getAdapter();
         $row=$adapter->query($selectString, $adapter::QUERY_MODE_EXECUTE);
         if (!$row) {
             throw new \Exception("No se encontro evento");
         }
         return $row->current();
     }
     /*
      * Cambia de estado a los usuarios pendientes de confirmacion
      * @return bolean
      */
     
    public function aprobarUsuario($idgrupo, $idusuario, $aprobar) {
        if ($this->getGrupoUsuario($idgrupo, $idusuario)) {
            $consulta = $this->tableGateway->getSql()->update()->table('ta_usuario_has_ta_grupo')
                    ->set(array('va_estado' => $aprobar))//, 'va_aceptado' => $aprobar
                    ->where(array('ta_usuario_in_id' => $idusuario, 'ta_grupo_in_id' => $idgrupo));
        }else{
           $consulta = $this->tableGateway->getSql()->insert()->into('ta_usuario_has_ta_grupo')
                   ->values(array('ta_usuario_in_id'=>$idusuario,'ta_grupo_in_id'=>$idgrupo,'va_estado'=>$aprobar, 
                      'va_fecha'=>date('c')));
         }
            $selectString = $this->tableGateway->getSql()->getSqlStringForSqlObject($consulta);
            $adapter = $this->tableGateway->getAdapter();
            $row = $adapter->query($selectString, $adapter::QUERY_MODE_EXECUTE);
            if (!$row) {
                throw new \Exception("No se puede unir/dejar al grupo");
            }
            
       $eventos = $this->totalEventosxGrupo($idgrupo, $idusuario);//eventosgrupo($idgrup, $iduser,'privado')
//       var_dump($eventos->toArray());Exit;
       if($eventos){
        foreach ($eventos as $evento) {
            if ($eventos->count() > 0) {
                if ($evento->va_estado == 'activo') {
                    $updatevent = $this->tableGateway->getSql()->update()->table('ta_usuario_has_ta_evento')
                            ->set(array('va_estado' => 'activo'))
                            ->where(array('ta_usuario_in_id' => $idusuario, 'ta_evento_in_id' => $evento->in_id));
                    $selectStringUpdate = $this->tableGateway->getSql()->getSqlStringForSqlObject($updatevent);
                    $adapter = $this->tableGateway->getAdapter();

                    $rowevent = $adapter->query($selectStringUpdate, $adapter::QUERY_MODE_EXECUTE);
                    if (!$rowevent) {
                        throw new \Exception("No se puede retirar de los eventos");
                    }
                }
            }
        }
       }
            return true;
    }
     
     public function unirseGrupo($idgrup,$iduser){
         if($this->getGrupoUsuario($idgrup,$iduser)){
            $consulta = $this->tableGateway->getSql()->update()->table('ta_usuario_has_ta_grupo')
                    ->set(array('va_estado'=>'pendiente'))//activo
                    ->where(array('ta_usuario_in_id'=>$iduser,'ta_grupo_in_id'=>$idgrup)); 

         }else{
           $consulta = $this->tableGateway->getSql()->insert()->into('ta_usuario_has_ta_grupo')
                   ->values(array('ta_usuario_in_id'=>$iduser,'ta_grupo_in_id'=>$idgrup,'va_estado'=>'pendiente','va_fecha'=>date('c')));
         }
           $selectString = $this->tableGateway->getSql()->getSqlStringForSqlObject($consulta);
           $adapter=$this->tableGateway->getAdapter();
           $row=$adapter->query($selectString, $adapter::QUERY_MODE_EXECUTE);
          if (!$row) {
            throw new \Exception("No se puede unir al grupo");
            }
          return true;

     }
     
     
     public function unirseEvento($idevent,$iduser){

         if($this->getEventoUsuario($idevent,$iduser)){
             $consulta = $this->tableGateway->getSql()->update()->table('ta_usuario_has_ta_evento')
             ->set(array('va_estado'=>'activo','va_fecha'=>date('c')))
             ->where(array('ta_usuario_in_id'=>$iduser,'ta_evento_in_id'=>$idevent));
             
         }else{   
             //agrege al grupo cuando se une al evento  
             $consulta = $this->tableGateway->getSql()->insert()->into('ta_usuario_has_ta_evento')
             ->values(array('ta_usuario_in_id'=>$iduser,'ta_evento_in_id'=>$idevent,'va_estado'=>'activo','va_fecha'=>date('c')));
         }

           $selectString = $this->tableGateway->getSql()->getSqlStringForSqlObject($consulta);
           $adapter=$this->tableGateway->getAdapter(); 
           $row=$adapter->query($selectString, $adapter::QUERY_MODE_EXECUTE);
          if (!$row) {
            throw new \Exception("No se puede unir al evento");
            }
          return true;

     }
     
     public function getEventoUsuario($idevent, $iduser) {
        $adapter = $this->tableGateway->getAdapter();
        $sql = new Sql($adapter);
        $selecttot = $sql->select()
                ->from('ta_usuario_has_ta_evento')
                ->join('ta_evento', 'ta_usuario_has_ta_evento.ta_evento_in_id=ta_evento.in_id', array('idgrup' => 'ta_grupo_in_id','tipo'=>'va_tipo'), 'LEFT')
                ->where(array(
            'ta_usuario_has_ta_evento.ta_evento_in_id' => $idevent,
            'ta_usuario_has_ta_evento.ta_usuario_in_id' => $iduser,
            'ta_evento.ta_grupo_in_id is not null'
                ));
        $selectString = $this->tableGateway->getSql()->getSqlStringForSqlObject($selecttot);
        $row = $adapter->query($selectString, $adapter::QUERY_MODE_EXECUTE);
        if (!$row) {
            throw new \Exception("No se encontro evento");
        }
        return $row->current();
    }
     
     public function retiraGrupo($idgrup, $iduser) {
        $update = $this->tableGateway->getSql()->update()->table('ta_usuario_has_ta_grupo')
                ->set(array('va_estado' => 'desactivo'))
                ->where(array('ta_usuario_in_id' => $iduser, 'ta_grupo_in_id' => $idgrup));
        $selectStringUpdate = $this->tableGateway->getSql()->getSqlStringForSqlObject($update);
        $adapter = $this->tableGateway->getAdapter();
        $row = $adapter->query($selectStringUpdate, $adapter::QUERY_MODE_EXECUTE);
        
        $eventos = $this->totalEventosxGrupo($idgrup, $iduser);//eventosgrupo($idgrup, $iduser,'privado')
        foreach ($eventos as $evento) {
            if ($eventos->count() > 0) {
                if ($evento->va_estado == 'activo') {
                    $updatevent = $this->tableGateway->getSql()->update()->table('ta_usuario_has_ta_evento')
                            ->set(array('va_estado' => 'desactivo'))
                            ->where(array('ta_usuario_in_id' => $iduser, 'ta_evento_in_id' => $evento->in_id));
                    $selectStringUpdate = $this->tableGateway->getSql()->getSqlStringForSqlObject($updatevent);
                    $adapter = $this->tableGateway->getAdapter();

                    $rowevent = $adapter->query($selectStringUpdate, $adapter::QUERY_MODE_EXECUTE);
                    if (!$rowevent) {
                        throw new \Exception("No se puede retirar de los eventos");
                    }
                }
            }
        }

        if (!$row) {
            throw new \Exception("No se puede retirar al grupo");
        }
        return true;
    }
     /*
      * Obtiene los eventos de los grupos
      */
        public function getEventoGrupo($idgrupo,$iduser){
         $adapter = $this->tableGateway->getAdapter();
         $sql = new Sql($adapter);
         $selecttot = $sql->select()
         ->from('ta_usuario_has_ta_evento')
         ->join('ta_evento','ta_usuario_has_ta_evento.ta_evento_in_id=ta_evento.in_id',array(),'left')        
         ->where(array('ta_evento.ta_grupo_in_id'=>$idgrupo,'ta_usuario_has_ta_evento.ta_usuario_in_id'=>$iduser,
             'va_estado'=>'activo'));
         $selectString = $this->tableGateway->getSql()->getSqlStringForSqlObject($selecttot);
         $row=$adapter->query($selectString, $adapter::QUERY_MODE_EXECUTE);
         if (!$row) {
             throw new \Exception("No se encontro evento");
         }
         return $row->current();
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
            return $results;
            
     }
     public function usuariosgrupodetalle($id,$iduser=null)
    {  
         $adapter = $this->tableGateway->getAdapter();
            $sql = new Sql($adapter);
            $select = $sql->select();
              if($iduser!=null){
                   $select->columns(array('va_fecha'));
             }
                 $select->from('ta_usuario_has_ta_grupo')
                        ->join('ta_usuario','ta_usuario.in_id=ta_usuario_has_ta_grupo.ta_usuario_in_id',array('nombre_usuario'=>'va_nombre','imagen'=>'va_foto','descripcion_usuario'=>'va_descripcion'),'left');        
             if($iduser!=null){
                   $select->where(array('ta_usuario_has_ta_grupo.ta_grupo_in_id' => $id,
                        'ta_usuario_has_ta_grupo.ta_usuario_in_id' => $iduser,
                       'ta_usuario_has_ta_grupo.va_estado'=>'activo',
                       ));//'ta_usuario_has_ta_grupo.va_aceptado'=>'si'
             }else{
                   $select->where(array('ta_usuario_has_ta_grupo.ta_grupo_in_id' => $id,
                       'ta_usuario_has_ta_grupo.va_estado'=>'activo'))->order('ta_usuario_has_ta_grupo.va_fecha DESC');
             }

            $selectString = $sql->getSqlStringForSqlObject($select);
            $resultSet = $adapter->query($selectString, $adapter::QUERY_MODE_EXECUTE);
        return $resultSet;
    }
    /*
     * Obtiene informacion del usuario segun su id y el estado que tiene
     */
    
    public function estadoUsuariosxGrupo($id,$estado,$iduser=null) {
        $adapter = $this->tableGateway->getAdapter();
        $sql = new Sql($adapter);
        $select = $sql->select();
        $select->from('ta_usuario_has_ta_grupo')
                ->join('ta_usuario', 'ta_usuario.in_id=ta_usuario_has_ta_grupo.ta_usuario_in_id', 
                        array('nombre_usuario' => 'va_nombre', 'imagen' => 'va_foto', 
                            'descripcion_usuario' => 'va_descripcion'), 'left')
                ->join('ta_grupo','ta_grupo.in_id=ta_usuario_has_ta_grupo.ta_grupo_in_id',array(),'left')
                ->where(array('ta_usuario_has_ta_grupo.ta_grupo_in_id' => $id,
                    'ta_usuario_has_ta_grupo.ta_usuario_in_id !=?'=>$iduser,
                    'ta_usuario_has_ta_grupo.va_estado' => $estado//'activo', 'ta_usuario_has_ta_grupo.va_aceptado' =>$estado
                    ));

        $selectString = $sql->getSqlStringForSqlObject($select);
        $resultSet = $adapter->query($selectString, $adapter::QUERY_MODE_EXECUTE);
        return $resultSet;
    }
     
       public function usuariosgrupo($id,$iduser=null)
    {  
         $adapter = $this->tableGateway->getAdapter();
            $sql = new Sql($adapter);
            $select = $sql->select();
              if($iduser!=null){
                   $select->columns(array('va_fecha'));
             }
                 $select->from('ta_usuario_has_ta_grupo')
                        ->join('ta_usuario','ta_usuario.in_id=ta_usuario_has_ta_grupo.ta_usuario_in_id',array('nombre_usuario'=>'va_nombre','imagen'=>'va_foto','descripcion_usuario'=>'va_descripcion'),'left');        
             if($iduser!=null){
                   $select->where(array('ta_usuario_has_ta_grupo.ta_grupo_in_id' => $id,
                        'ta_usuario_has_ta_grupo.ta_usuario_in_id' => $iduser));
             }else{
                   $select->where(array('ta_usuario_has_ta_grupo.ta_grupo_in_id' => $id,
                       'ta_usuario_has_ta_grupo.va_estado'=>'activo'))->order('ta_usuario_has_ta_grupo.va_fecha DESC');
             }

            $selectString = $sql->getSqlStringForSqlObject($select);
            $resultSet = $adapter->query($selectString, $adapter::QUERY_MODE_EXECUTE);
        return $resultSet;
    }

    


    
    public function compruebarUsuarioxGrupo($iduser,$idgrupo){
        
        $adapter = $this->tableGateway->getAdapter();
        $sql = new Sql($adapter);
        $selecttot = $sql->select()
        ->from('ta_usuario_has_ta_grupo')
        ->where(array('ta_usuario_in_id'=>$iduser,'ta_grupo_in_id'=>$idgrupo));
        $selectString = $sql->getSqlStringForSqlObject($selecttot);
        $resultSet = $adapter->query($selectString, $adapter::QUERY_MODE_EXECUTE);
        return $resultSet->current();
    
    }
    
//    public function getEventoxUsuario($iduser){
//        $adapter = $this->tableGateway->getAdapter();
//        $sql = new Sql($adapter);
//        $select = $sql->select();
//        $select->from('ta_evento')->where(''=>$iduser);
//        $selectString = $sql->getSqlStringForSqlObject($select);
//        $resultSet = $adapter->query($selectString, $adapter::QUERY_MODE_EXECUTE);
//        return $resultSet;      
//    }
    
       public function totalEventosxGrupo($id, $iduser,$estado='activo') {
        $adapter = $this->tableGateway->getAdapter();
        $sql = new Sql($adapter);
        $select = $sql->select();
        $select->from('ta_evento')
                ->join('ta_usuario_has_ta_evento', 'ta_usuario_has_ta_evento.ta_evento_in_id=ta_evento.in_id', 
                array('miembros' => new \Zend\Db\Sql\Expression('COUNT(ta_usuario_has_ta_evento.ta_usuario_in_id)')), 'left');

            if ($this->getGrupoUsuarioDetalle($id, $iduser)) {
                $select->where(array('ta_evento.ta_grupo_in_id' => $id, 'ta_evento.va_estado' => $estado));
            } else {
                $select->where(array('ta_evento.va_estado' => 'activo','ta_evento.ta_grupo_in_id' => $id,
                    'ta_usuario_has_ta_evento.ta_usuario_in_id'=>$iduser));
            }
   
        $select->group('in_id')->order('va_fecha desc');
        $selectString = $sql->getSqlStringForSqlObject($select);
//        var_dump($selectString);
        $resultSet = $adapter->query($selectString, $adapter::QUERY_MODE_EXECUTE);
        return $resultSet->buffer();
    }
    
      public function eventosgrupo($id, $iduser = null, $tipo = 'publico') {
        //$fecha = date("Y-m-d h:m:s"); 
        $adapter = $this->tableGateway->getAdapter();
        $sql = new Sql($adapter);
        $select = $sql->select();
        $select->from('ta_evento')
                ->join('ta_usuario_has_ta_evento', 'ta_usuario_has_ta_evento.ta_evento_in_id=ta_evento.in_id', 
                array('miembros' => new \Zend\Db\Sql\Expression('COUNT(ta_usuario_has_ta_evento.ta_usuario_in_id)')), 'left');

        if ($iduser != null) {
            if ($this->getGrupoUsuarioDetalle($id, $iduser)) {
                $select->where(array('ta_evento.ta_grupo_in_id' => $id, 'ta_evento.va_estado' => 'activo'));
//            $select->where(array('ta_evento.va_tipo'=>'privado'));
            } else {
                $select->where(array('ta_evento.ta_grupo_in_id' => $id, 'ta_evento.va_tipo' => $tipo));
            }
        } else {
            $select->where(array('ta_evento.ta_grupo_in_id' => $id, 'ta_evento.va_tipo' => $tipo));
        }
        $select->group('in_id')->order('va_fecha desc');
        $selectString = $sql->getSqlStringForSqlObject($select);
        $resultSet = $adapter->query($selectString, $adapter::QUERY_MODE_EXECUTE);
//        var_dump($resultSet->toArray());Exit;
        return $resultSet->buffer();
    }

    public function misgrupos($id)
    {
         $adapter = $this->tableGateway->getAdapter();
            $sql = new Sql($adapter);
            $selecttot = $sql->select()
          ->from('ta_grupo')      
      //   ->join('ta_grupo','ta_grupo.in_id=ta_evento.ta_grupo_in_id',array('monbregrupo' =>'va_nombre','idgrupo' =>'in_id','describe' =>'va_descripcion','imagen' =>'va_imagen'), 'left') 
          ->join('ta_categoria','ta_categoria.in_id=ta_grupo.ta_categoria_in_id', array('nombre_categoria' =>'va_nombre','idcategoria' =>'in_id'), 'left')              
          ->where(array('ta_grupo.ta_usuario_in_id'=>$id,'ta_grupo.va_estado'=>'activo'))
          ->order('ta_grupo.va_fecha DESC');
            $selectString = $sql->getSqlStringForSqlObject($selecttot);
            $resultSet = $adapter->query($selectString, $adapter::QUERY_MODE_EXECUTE);  
            return $resultSet->buffer();
    }
     
}

