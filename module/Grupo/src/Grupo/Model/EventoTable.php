<?php
namespace Grupo\Model;
use Zend\Db\TableGateway\AbstractTableGateway;
use Zend\Db\TableGateway\TableGateway;
use Zend\Db\Sql\Sql;
use Zend\Db\Sql\Select;
use Platos\Model\Platos;

class EventoTable{
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
                $selecttot->where(array('ta_grupo.va_nombre'=>$nombre,'ta_grupo.va_nombre LIKE ?'=> '%'.$nombre.'%',));
            }
            $selecttot ->group('ta_grupo.in_id')->order('ta_grupo.in_id desc');
            $selectString = $sql->getSqlStringForSqlObject($selecttot);
            $resultSet = $adapter->query($selectString, $adapter::QUERY_MODE_EXECUTE);

            return $resultSet->buffer();
        
    }
    public function getEvento($id)
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
    
  public function guardarEvento(Evento $evento,$idgrupo=null,$imagen,$iduser=null){
      $fecha_esp=preg_replace('/\s+/',' ', $evento->va_fecha);
      $fecha_esp = str_replace("-", " ", $fecha_esp);
      $fecha=date('Y-m-d H:i:s', strtotime($fecha_esp));

      $data=array(
         'va_nombre'=>$evento->va_nombre,
         'va_descripcion'=>  htmlentities($evento->va_descripcion),
         'va_costo'=>$evento->va_costo,
          'va_fecha'=>$fecha,//$evento->va_fecha,
         'va_latitud'=>$evento->va_latitud,
          'va_longitud'=>$evento->va_longitud,
          'va_direccion'=>$evento->va_direccion,
          'va_referencia'=>$evento->va_referencia,
          'va_imagen'=>$imagen,
          'va_estado'=>$evento->va_estado,
          'va_max'=>$evento->va_max,
          'va_min'=>$evento->va_min,
          'va_duracion'=>$evento->va_duracion,
          'ta_usuario_in_id'=>$iduser=($iduser!=null)?$iduser:$evento->ta_usuario_in_id,
//          'ta_ubigeo_in_id'=>$evento->ta_ubigeo_in_id,//distrito,//$convertir[0]['in_id']   
          'ta_grupo_in_id'=>$idgrupo=($idgrupo!=null)?$idgrupo:$evento->ta_grupo_in_id,//$evento->ta_grupo_in_id
           'va_tipo'=>$evento->va_tipo
          );
//    var_dump($evento->va_descripcion);Exit;
      $id = (int) $evento->in_id;
  
//      foreach($data as $key=>$value){
//          if(empty($value)){
//              $data[$key]=0;
//          }
//      }
     
      if ($id == 0) {
          $data['va_fecha_ingreso']= date("Y-m-d H:i:s");
          $this->tableGateway->insert($data);
          $idevento=$this->tableGateway->getLastInsertValue();
          if($this->unirseEvento($idevento,$iduser)){
              return $idevento;
          }  
         
          
      }else {
            if ($this->getEvento($id)) {
                $this->tableGateway->update($data, array('in_id' => $id));       
            } else {
                throw new \Exception('no existe el evento');
            }
        }
      
  }
  
     public function eliminarGrupo($id, $estado) {
        $data = array(
            'va_estado' => $estado
        );
        $this->tableGateway->update($data, array(
            'in_id' => $id
        ));
    }
    
    
    
    
         public function usuarioGrupoxEvento($idevento){
             $adapter = $this->tableGateway->getAdapter();
            $sql = new Sql($adapter);
             $selecttot=    $sql->select()->columns(array('va_fecha'))
                        ->from('ta_usuario_has_ta_grupo')
                        ->join('ta_usuario','ta_usuario.in_id=ta_usuario_has_ta_grupo.ta_usuario_in_id',array('idusuario'=>'in_id','nombre_usuario'=>'va_nombre','imagen'=>'va_foto','descripcion_usuario'=>'va_descripcion'),'left')        
                        ->join('ta_grupo','ta_grupo.in_id=ta_usuario_has_ta_grupo.ta_grupo_in_id',array(),'left')
                       ->join('ta_evento','ta_evento.ta_grupo_in_id=ta_grupo.in_id',array(),'left')
                   ->where(array(
                       'ta_evento.in_id'=>$idevento,
                       'ta_usuario_has_ta_grupo.va_estado'=>'activo',
                       ));

//            $selecttot = $sql->select()
//                    ->columns(array('va_fecha','va_estado'))
//                    ->from('ta_usuario_has_ta_grupo')
//                    ->join('ta_usuario','ta_usuario.in_id=ta_usuario_has_ta_grupo.ta_usuario_in_id',array('nombre_usuario'=>'va_nombre','imagen'=>'va_foto','descripcion_usuario'=>'va_descripcion'),'left')
//                   
//                    ->where(array('ta_usuario_has_ta_grupo.ta_grupo_in_id' => $idgrupo,
//                        'ta_usuario_has_ta_grupo.ta_usuario_in_id' => $iduser));
            $selectString = $sql->getSqlStringForSqlObject($selecttot);   
            $resultSet = $adapter->query($selectString, $adapter::QUERY_MODE_EXECUTE);
        return $resultSet;
         
     }
    

        public function getGrupoUsuario($idgrupo,$iduser){
            $adapter = $this->tableGateway->getAdapter();
            $sql = new Sql($adapter);
            $selecttot = $sql->select()
            ->from('ta_usuario_has_ta_grupo')
            ->where(array('ta_grupo_in_id'=>$idgrupo,'ta_usuario_in_id'=>$iduser));
            $selectString = $this->tableGateway->getSql()->getSqlStringForSqlObject($selecttot);
            $adapter=$this->tableGateway->getAdapter();
            $row=$adapter->query($selectString, $adapter::QUERY_MODE_EXECUTE);
            if (!$row) {
                throw new \Exception("No se encontro grupo");
            }
            return $row->current();
        }
     /*
      * unirse al grupo
      * 
      */
        public function unirseGrupo($idgrup,$iduser){

            $usuario=$this->getGrupoUsuario($idgrup,$iduser);
            if($usuario){
                $estado=($usuario->va_estado!='activo')?'pendiente':'activo';
                 $consulta = $this->tableGateway->getSql()->update()->table('ta_usuario_has_ta_grupo')
                 ->set(array('va_estado'=>$estado))//'pendiente' activo
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
                return false;
            }
            return true;
    
        }
        /*
         * retira del grupo
         */
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

//     public function retiraGrupo($idgrup,$iduser){
//            if($this->getGrupoUsuario($idgrup,$iduser)){
//                 $consulta = $this->tableGateway->getSql()->update()->table('ta_usuario_has_ta_grupo')
//                 ->set(array('va_estado'=>'pendiente'))
//                 ->where(array('ta_usuario_in_id'=>$iduser,'ta_grupo_in_id'=>$idgrup));
//    
//            }else{
//                $consulta = $this->tableGateway->getSql()->insert()->into('ta_usuario_has_ta_grupo')
//                ->values(array('ta_usuario_in_id'=>$iduser,'ta_grupo_in_id'=>$idgrup,'va_estado'=>'pendiente','va_fecha'=>date('c')));
//            }
//            $selectString = $this->tableGateway->getSql()->getSqlStringForSqlObject($consulta);
//            $adapter=$this->tableGateway->getAdapter();
//            $row=$adapter->query($selectString, $adapter::QUERY_MODE_EXECUTE);
//            if (!$row) {
//                throw new \Exception("No se puede unir al grupo");
//            }
//            return true;
//    
//        }
////////////////////////////////////////////////////////////FIN////////////////////////////////////////////////
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

        $adapter = $this->tableGateway->getAdapter();
        $row = $adapter->query($selectString, $adapter::QUERY_MODE_EXECUTE);
        if (!$row) {
            throw new \Exception("No se encontro evento");
        }
        return $row->current();
    }
    
   public function getEventosxUsuario($iduser) {
        $adapter = $this->tableGateway->getAdapter();
        $sql = new Sql($adapter);
        $selecttot = $sql->select()
                ->from('ta_usuario_has_ta_evento')
                ->join('ta_evento', 'ta_usuario_has_ta_evento.ta_evento_in_id=ta_evento.in_id', array('idgrup' => 'ta_grupo_in_id','tipo'=>'va_tipo'), 'LEFT')
                ->where(array(
//            'ta_usuario_has_ta_evento.va_estado' => 'activo',
            'ta_usuario_has_ta_evento.ta_usuario_in_id' => $iduser,
            'ta_evento.ta_grupo_in_id is not null','ta_evento.va_tipo'=>'privado'
                ));
        $selectString = $this->tableGateway->getSql()->getSqlStringForSqlObject($selecttot);
        $row = $adapter->query($selectString, $adapter::QUERY_MODE_EXECUTE);
        if (!$row) {
            throw new \Exception("No se encontro evento");
        }
        return $row->current();
    }
     public function unirseEvento($idevent,$iduser){
             $evento=$this->getEvento($idevent);
             $idgrup=$evento->ta_grupo_in_id;
         if($this->getEventoUsuario($idevent,$iduser)){
             $this->unirseGrupo($idgrup,$iduser);
             $consulta = $this->tableGateway->getSql()->update()->table('ta_usuario_has_ta_evento')
             ->set(array('va_estado'=>'activo','va_fecha'=>date('c')))//activo
             ->where(array('ta_usuario_in_id'=>$iduser,'ta_evento_in_id'=>$idevent));
             
         }else{   
             //agrege al grupo cuando se une al evento
             //comprueba estado de grupo
             $grupo=$this->usuarioGrupo($idgrup,$iduser)->current();

             $estado=($grupo->va_estado=='activo')?'activo':'desactivo';
             $this->unirseGrupo($idgrup,$iduser);   
             $consulta = $this->tableGateway->getSql()->insert()->into('ta_usuario_has_ta_evento')
             ->values(array('ta_usuario_in_id'=>$iduser,'ta_evento_in_id'=>$idevent,'va_estado'=>$estado,'va_fecha'=>date('c')));//desactivo activo
         
             
         }

           $selectString = $this->tableGateway->getSql()->getSqlStringForSqlObject($consulta);

           $adapter=$this->tableGateway->getAdapter(); 
           $row=$adapter->query($selectString, $adapter::QUERY_MODE_EXECUTE);
          if (!$row) {
            throw new \Exception("No se puede unir al evento");
            }
          return true;

     }
     
     public function retiraEvento($idevent,$iduser){
//            $idgrup=$this->getEvento($idevent)->ta_grupo_in_id;
//            $this->retiraGrupo($idgrup, $iduser);
            $update = $this->tableGateway->getSql()->update()->table('ta_usuario_has_ta_evento')
                    ->set(array('va_estado'=>'desactivo'))
                    ->where(array('ta_usuario_in_id'=>$iduser,'ta_evento_in_id'=>$idevent));  
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
     
    public function listadoEvento($iduser=null,$idtipo=null) {
        $fecha = date("Y-m-d h:m:s");
        $adapter = $this->tableGateway->getAdapter();
        $sql = new Sql($adapter);
        $selecttot = $sql->select()
                ->from('ta_evento')
                ->join('ta_comentario', 'ta_comentario.ta_evento_in_id=ta_evento.in_id', array('comentarios' => new \Zend\Db\Sql\Expression('COUNT(ta_comentario.in_id)')), 'left')
                ->join('ta_grupo', 'ta_grupo.in_id=ta_evento.ta_grupo_in_id', array('categoria' => 'ta_categoria_in_id'), 'left')

                ->join('ta_categoria', 'ta_grupo.ta_categoria_in_id=ta_categoria.in_id', array('nombre_categoria' => 'va_nombre', 'idcategoria' => 'in_id'), 'left')
                 ->where(array('ta_evento.va_estado' => 'activo', 'ta_evento.va_fecha>=?' => $fecha, 
                'ta_evento.va_tipo!=?' => 'privado'));
            if($idtipo!=null){
                    $selecttot->where(array('ta_categoria.in_id'=>$idtipo));
                 }
                 $selecttot->group('in_id');
//        if($iduser != null){
//            if($this->getEventosxUsuario($iduser)){
//            $selecttot->join('ta_usuario_has_ta_evento','ta_usuario_has_ta_evento.ta_evento_in_id=ta_evento.in_id',array(),'left')
//            ->where(array('ta_evento.va_estado' => 'activo', 'ta_evento.va_fecha>=?' => $fecha,
//                'ta_usuario_has_ta_evento.ta_usuario_in_id'=>$iduser,'ta_evento.va_tipo' => 'privado'));  
//            }else{
//                $selecttot->where(array('ta_evento.va_estado' => 'activo', 'ta_evento.va_fecha>=?' => $fecha, 'ta_evento.va_tipo!=?' => 'privado',
//                    'ta_usuario_has_ta_evento.ta_usuario_in_id'=>$iduser));
//            }
//        }else{
//            $selecttot->where(array('ta_evento.va_estado' => 'activo', 'ta_evento.va_fecha>=?' => $fecha, 'ta_evento.va_tipo!=?' => 'privado')); 
//        }
        
        if($iduser != null){
         $selectpriva = $sql->select()
                ->from('ta_evento')
                 ->join('ta_comentario', 'ta_comentario.ta_evento_in_id=ta_evento.in_id', array('comentarios' => new \Zend\Db\Sql\Expression('COUNT(ta_comentario.in_id)')), 'left')
                ->join('ta_grupo', 'ta_grupo.in_id=ta_evento.ta_grupo_in_id', array('categoria' => 'ta_categoria_in_id'), 'left')
                ->join('ta_categoria', 'ta_grupo.ta_categoria_in_id=ta_categoria.in_id', array('nombre_categoria' => 'va_nombre', 'idcategoria' => 'in_id'), 'left')              
            ->join('ta_usuario_has_ta_evento','ta_usuario_has_ta_evento.ta_evento_in_id=ta_evento.in_id',array(),'left')
            ->where(array('ta_evento.va_estado' => 'activo', 'ta_evento.va_fecha>=?' => $fecha,
                'ta_usuario_has_ta_evento.ta_usuario_in_id'=>$iduser,'ta_evento.va_tipo' => 'privado'));
                 if($idtipo!=null){
                    $selectpriva->where(array('ta_categoria.in_id'=>$idtipo));
                 }
                $selectpriva ->group('in_id');
         $selecttot->combine($selectpriva);
        }
//        else{
//            $selecttot->where(array('ta_evento.va_estado' => 'activo', 'ta_evento.va_fecha>=?' => $fecha, 
//                'ta_evento.va_tipo!=?' => 'privado'))->group('in_id');//->order('in_id desc'); 
//        }
//        $selecttot->group('in_id')->order('in_id desc'); 
        $selectString = $sql->getSqlStringForSqlObject($selecttot);
        $resultSet = $adapter->query($selectString, $adapter::QUERY_MODE_EXECUTE);//.' ORDER BY in_id DESC'
        $resultSet1=$resultSet->buffer();
        
        return $resultSet1;
    }

    public function listadocategoriasEvento($categoria)
    {
         $adapter = $this->tableGateway->getAdapter();
            $sql = new Sql($adapter);
            $selecttot = $sql->select()
                    ->from('ta_evento')
          ->join('ta_grupo','ta_grupo.in_id=ta_evento.ta_grupo_in_id',array('categoria'=>'ta_categoria_in_id'),'left')
          ->join('ta_categoria','ta_grupo.ta_categoria_in_id=ta_categoria.in_id',array('nombre_categoria'=>'va_nombre'),'left')
          ->where(array('ta_grupo.ta_categoria_in_id'=>$categoria))
          ->order('in_id desc');
            $selectString = $sql->getSqlStringForSqlObject($selecttot);
            $resultSet = $adapter->query($selectString, $adapter::QUERY_MODE_EXECUTE);           
            return $resultSet;
    }
      public function listado2Evento($consulta)
    {   $fecha = date("Y-m-d h:m:s");
         $adapter = $this->tableGateway->getAdapter();
            $sql = new Sql($adapter);
            $selecttot = $sql->select()
                    ->from('ta_evento')
          ->join('ta_comentario','ta_comentario.ta_evento_in_id=ta_evento.in_id', array('comentarios' => new \Zend\Db\Sql\Expression('COUNT(ta_comentario.in_id)')), 'left')                         
          ->join('ta_grupo','ta_grupo.in_id=ta_evento.ta_grupo_in_id',array('categoria'=>'ta_categoria_in_id'),'left')
          ->join('ta_categoria','ta_grupo.ta_categoria_in_id=ta_categoria.in_id',array('nombre_categoria'=>'va_nombre','idcategoria'=>'in_id'),'left')   
          ->where(array('ta_evento.va_nombre LIKE ?'=> '%'.$consulta.'%','ta_evento.va_estado'=>'activo','ta_evento.va_tipo'=>'publico','ta_evento.va_fecha>=?'=>$fecha)) 
         ->order('in_id desc')->group('in_id');
            $selectString = $sql->getSqlStringForSqlObject($selecttot);
//            var_dump($selectString);exit;
            $resultSet = $adapter->query($selectString, $adapter::QUERY_MODE_EXECUTE);
//             var_dump(count($resultSet));exit;
         if (!$resultSet) {
            throw new \Exception("No se puede encontrar el/los grupo(s)");
        }
            return $resultSet->buffer();
    }
     public function Evento($id)
    {
         $adapter = $this->tableGateway->getAdapter();
            $sql = new Sql($adapter);
            $selecttot = $sql->select()
                    ->from('ta_evento')
          ->join('ta_grupo','ta_grupo.in_id=ta_evento.ta_grupo_in_id',array('categoria'=>'ta_categoria_in_id','descripcion'=>'va_descripcion','fecha_creacion'=>'va_fecha','imagen'=>'va_imagen','nombre_grupo'=>'va_nombre','id_grupo'=>'in_id'),'left')
          ->join('ta_categoria','ta_grupo.ta_categoria_in_id=ta_categoria.in_id',array('nombre_categoria'=>'va_nombre'),'left')
          ->join('ta_usuario','ta_grupo.ta_usuario_in_id=ta_usuario.in_id',array('nombre_user'=>'va_nombre','va_email','va_dni','va_foto'),'left')
          ->join('ta_usuario_has_ta_evento','ta_usuario_has_ta_evento.ta_evento_in_id=ta_evento.in_id', array('cantidad' => new \Zend\Db\Sql\Expression('COUNT(ta_usuario_has_ta_evento.ta_evento_in_id)')), 'left')         
          ->where(array('ta_evento.in_id'=>$id));
            $selectString = $sql->getSqlStringForSqlObject($selecttot);
            $resultSet = $adapter->query($selectString, $adapter::QUERY_MODE_EXECUTE);           
            return $resultSet->toArray();
    }
    
    
    
    public function grupoid($id)
    {
         $adapter = $this->tableGateway->getAdapter();
            $sql = new Sql($adapter);
            $selecttot = $sql->select()
                    ->from('ta_grupo')
          ->join('ta_usuario_has_ta_grupo','ta_usuario_has_ta_grupo.ta_grupo_in_id=ta_grupo.in_id', array('cantidad' => new \Zend\Db\Sql\Expression('COUNT(ta_usuario_has_ta_grupo.ta_grupo_in_id)')), 'left')         
          ->where(array('ta_grupo.in_id'=>$id));
            $selectString = $sql->getSqlStringForSqlObject($selecttot);
            $resultSet = $adapter->query($selectString, $adapter::QUERY_MODE_EXECUTE);           
            return $resultSet->toArray();
    }
    
    public function eventosfuturos($id)
    { 
      $fecha = date("Y-m-d h:m:s");  
         $adapter = $this->tableGateway->getAdapter();
            $sql = new Sql($adapter);
            $select = $sql->select();
                 $select->from('ta_evento')
          ->columns(array('eventosfuturos' => new \Zend\Db\Sql\Expression('COUNT(in_id)')))
                 ->where(array('ta_grupo_in_id' => $id,'va_fecha>=?'=>$fecha,'va_estado'=>'activo'));
            $selectString = $sql->getSqlStringForSqlObject($select);
            $resultSet = $adapter->query($selectString, $adapter::QUERY_MODE_EXECUTE);
        return $resultSet->toArray();
    }
    public function eventospasados($id)
    { 
      $fecha = date("Y-m-d h:m:s");  
         $adapter = $this->tableGateway->getAdapter();
            $sql = new Sql($adapter);
            $select = $sql->select();
                 $select->from('ta_evento')
          ->columns(array('eventospasados' => new \Zend\Db\Sql\Expression('COUNT(in_id)')))
                 ->where(array('ta_grupo_in_id' => $id,'va_fecha<?'=>$fecha,'va_estado'=>'activo'));
            $selectString = $sql->getSqlStringForSqlObject($select);
            $resultSet = $adapter->query($selectString, $adapter::QUERY_MODE_EXECUTE);
        return $resultSet->toArray();
    }
    
    
    
     public function usuariosevento($id,$iduser=null,$estado=null)
    {  
         $adapter = $this->tableGateway->getAdapter();
            $sql = new Sql($adapter);
            $select = $sql->select();
             if($iduser!=null){
                   $select->columns(array('va_fecha'));
             }
              $select->from('ta_usuario_has_ta_evento')
              ->join('ta_usuario','ta_usuario.in_id=ta_usuario_has_ta_evento.ta_usuario_in_id',array('id_usuario'=>'in_id','nombre_usuario'=>'va_nombre','imagen'=>'va_foto','descripcion_usuario'=>'va_descripcion'),'left');        
             
             if($iduser!=null){
                   $select->where(array('ta_usuario_has_ta_evento.ta_evento_in_id' => $id,
                        //'ta_usuario_has_ta_evento.ta_usuario_in_id' => $iduser,
                       'ta_usuario_has_ta_evento.va_estado'=>$estado))->order('ta_usuario_has_ta_evento.va_fecha DESC');//'activo'
             }else{
                  $select->where(array('ta_usuario_has_ta_evento.ta_evento_in_id' => $id,
                                        'ta_usuario_has_ta_evento.va_estado'=>'activo'))->order('ta_usuario_has_ta_evento.va_fecha DESC');
             }                                                                          
            $selectString = $sql->getSqlStringForSqlObject($select);
//            var_dump($selectString);Exit;
            $resultSet = $adapter->query($selectString, $adapter::QUERY_MODE_EXECUTE);
        return $resultSet;
    }
    
    
  public function UsuariosxEvento($id,$iduser)
    {  
         $adapter = $this->tableGateway->getAdapter();
            $sql = new Sql($adapter);
            $select = $sql->select();
             if($iduser!=null){
                   $select->columns(array('va_fecha'));
             }
              $select->from('ta_usuario_has_ta_evento')
              ->join('ta_usuario','ta_usuario.in_id=ta_usuario_has_ta_evento.ta_usuario_in_id',array('id_usuario'=>'in_id','nombre_usuario'=>'va_nombre','imagen'=>'va_foto','descripcion_usuario'=>'va_descripcion'),'left');        

                   $select->where(array('ta_usuario_has_ta_evento.ta_evento_in_id' => $id,
                        'ta_usuario_has_ta_evento.ta_usuario_in_id' => $iduser));
//                       'ta_usuario_has_ta_evento.va_estado !=?'=>'desactivo'));//'activo'
                                                                            
            $selectString = $sql->getSqlStringForSqlObject($select);
//            var_dump($selectString);Exit;
            $resultSet = $adapter->query($selectString, $adapter::QUERY_MODE_EXECUTE);
        return $resultSet;
    }
    
     public function usuarioGrupo($idgrupo,$iduser=null){
            $adapter = $this->tableGateway->getAdapter();
            $sql = new Sql($adapter);
            $selecttot = $sql->select()
                    ->columns(array('va_fecha','va_estado'))
                    ->from('ta_usuario_has_ta_grupo')
                    ->join('ta_usuario','ta_usuario.in_id=ta_usuario_has_ta_grupo.ta_usuario_in_id',array('nombre_usuario'=>'va_nombre','imagen'=>'va_foto','descripcion_usuario'=>'va_descripcion'),'left')
                   
                    ->where(array('ta_usuario_has_ta_grupo.ta_grupo_in_id' => $idgrupo,
                        'ta_usuario_has_ta_grupo.ta_usuario_in_id' => $iduser));
            $selectString = $sql->getSqlStringForSqlObject($selecttot);
            $resultSet = $adapter->query($selectString, $adapter::QUERY_MODE_EXECUTE);
        return $resultSet;
         
     }
    
    public function eventoxUsuario($idevent)
    {
        $adapter = $this->tableGateway->getAdapter();
        $sql = new Sql($adapter);
        $selecttot = $sql->select()
        ->from('ta_usuario')
        ->join('ta_evento','ta_usuario.in_id=ta_evento.ta_usuario_in_id',array('*'), 'left')
        ->where(array('ta_evento.in_id'=>$idevent));
        $selectString = $sql->getSqlStringForSqlObject($selecttot);
        $resultSet = $adapter->query($selectString, $adapter::QUERY_MODE_EXECUTE);
        return $resultSet;
    }
    
    public function compruebarUsuarioxEvento($iduser,$idevent){
        $adapter = $this->tableGateway->getAdapter();
        $sql = new Sql($adapter);
        $selecttot = $sql->select()
        ->from('ta_usuario_has_ta_evento')
        ->where(array('ta_usuario_in_id'=>$iduser,'ta_evento_in_id'=>$idevent));
        $selectString = $sql->getSqlStringForSqlObject($selecttot);
        $resultSet = $adapter->query($selectString, $adapter::QUERY_MODE_EXECUTE);
        
        return $resultSet->current();
    
    }
    
    
      public function comentariosevento($id)
    {  
         $adapter = $this->tableGateway->getAdapter();
            $sql = new Sql($adapter);
            $select = $sql->select();
                 $select->from('ta_comentario')
              ->join('ta_usuario','ta_usuario.in_id=ta_comentario.ta_usuario_in_id',array('nombre_usuario'=>'va_nombre','imagen'=>'va_foto','descripcion_usuario'=>'va_descripcion'),'left')
            ->where(array('ta_comentario.ta_evento_in_id' => $id))
            ->order('ta_comentario.in_id DESC');
            $selectString = $sql->getSqlStringForSqlObject($select);
            $resultSet = $adapter->query($selectString, $adapter::QUERY_MODE_EXECUTE);
            $resultSet->buffer();
        return $resultSet;
    }

    
    public function guardarComentario($data,$iduser,$idevento){
        $values=array(
            'va_descripcion'=>$data['va_descripcion'],
            'en_estado'=>1,
            'va_fecha'=>date('c'),
            'ta_usuario_in_id'=>$iduser,
            'ta_evento_in_id'=>$idevento
        );
        
        $adapter = $this->tableGateway->getAdapter();
        $sql = new Sql($adapter);
        $insert=$sql->insert()->into('ta_comentario')->values($values);
        $selectString = $sql->getSqlStringForSqlObject($insert);
        $row=$adapter->query($selectString, $adapter::QUERY_MODE_EXECUTE);

        if (!$row) {
            throw new \Exception("No se encontro evento");
            return false;
           
        }      
         return true;
    }


 
      public function usuarioseventos($id) {
        $adapter = $this->tableGateway->getAdapter();
        $sql = new Sql($adapter);
        $selecttot = $sql->select()
                ->from('ta_usuario_has_ta_evento')
                ->join('ta_evento', 'ta_evento.in_id=ta_usuario_has_ta_evento.ta_evento_in_id', array('nombre' => 'va_nombre', 'descripcion' => 'va_descripcion', 'imagen' => 'va_imagen', 'fecha' => 'va_fecha', 'id' => 'in_id'), 'left')
                ->join('ta_comentario', 'ta_comentario.ta_evento_in_id=ta_evento.in_id', array('comentarios' => new \Zend\Db\Sql\Expression('COUNT(ta_comentario.in_id)')), 'left')
                ->join('ta_grupo', 'ta_grupo.in_id=ta_evento.ta_grupo_in_id', array(), 'left')
                ->join('ta_usuario_has_ta_grupo', 
                       'ta_usuario_has_ta_grupo.ta_grupo_in_id=ta_grupo.in_id', 
                        array(), 'left')
                ->join('ta_categoria', 'ta_categoria.in_id=ta_grupo.ta_categoria_in_id', array('nombre_categoria' => 'va_nombre', 'idcategoria' => 'in_id'), 'left')
                ->where(array(
                    'ta_usuario_has_ta_grupo.va_estado' => 'activo',
                    'ta_usuario_has_ta_evento.va_estado' => 'activo',
                    'ta_grupo.va_estado' => 'activo','ta_usuario_has_ta_evento.va_estado' => 'activo', 
                    'ta_evento.va_estado' => 'activo', 'ta_usuario_has_ta_evento.ta_usuario_in_id' => $id
                ))->where('ta_evento.ta_usuario_in_id !=' . $id)
                ->group('ta_evento.in_id')
                ->order('ta_usuario_has_ta_evento.va_fecha DESC');
        $selectString = $sql->getSqlStringForSqlObject($selecttot);

        $resultSet = $adapter->query($selectString, $adapter::QUERY_MODE_EXECUTE);
        return $resultSet->buffer();
    }

    public function miseventos($id) {
        $adapter = $this->tableGateway->getAdapter();
        $sql = new Sql($adapter);
        $selecttot = $sql->select()
                ->from('ta_evento')
                ->join('ta_comentario', 'ta_comentario.ta_evento_in_id=ta_evento.in_id', array('comentarios' => new \Zend\Db\Sql\Expression('COUNT(ta_comentario.in_id)')), 'left')
                ->join('ta_grupo', 'ta_grupo.in_id=ta_evento.ta_grupo_in_id', array('monbregrupo' => 'va_nombre', 'idgrupo' => 'in_id', 'describe' => 'va_descripcion', 'imagen' => 'va_imagen'), 'left')
                ->join('ta_categoria', 'ta_categoria.in_id=ta_grupo.ta_categoria_in_id', array('nombre_categoria' => 'va_nombre', 'idcategoria' => 'in_id'), 'left')
                ->where(array('ta_evento.ta_usuario_in_id' => $id, 'ta_evento.va_estado' => 'activo'))
                ->order('ta_evento.va_fecha_ingreso DESC')
                ->group('in_id');
        $selectString = $sql->getSqlStringForSqlObject($selecttot);
        $resultSet = $adapter->query($selectString, $adapter::QUERY_MODE_EXECUTE);
        return $resultSet->buffer();
    }
    
    
    public function eventocategoria($id)
    {
            $fecha = date("Y-m-d h:m:s"); 
            $adapter = $this->tableGateway->getAdapter();
            $sql = new Sql($adapter);
            $selecttot = $sql->select()
                    ->from('ta_evento')
   ->join('ta_comentario','ta_comentario.ta_evento_in_id=ta_evento.in_id', array('comentarios' => new \Zend\Db\Sql\Expression('COUNT(ta_comentario.in_id)')), 'left')                         
            
          ->join('ta_grupo','ta_grupo.in_id=ta_evento.ta_grupo_in_id',array('categoria'=>'ta_categoria_in_id'),'left')
          ->join('ta_categoria','ta_grupo.ta_categoria_in_id=ta_categoria.in_id',array('nombre_categoria'=>'va_nombre','idcategoria'=>'in_id'),'left')
         ->where(array('ta_evento.va_estado'=>'activo','ta_evento.va_fecha>=?'=>$fecha,'ta_categoria.in_id'=>$id,
             'ta_evento.va_tipo!=?' => 'privado'))           
          ->order('in_id desc')
                    ->group('in_id');  
        $selectString = $sql->getSqlStringForSqlObject($selecttot);
        $resultSet = $adapter->query($selectString, $adapter::QUERY_MODE_EXECUTE); 
        return $resultSet->buffer();
    }
    
    
     

}

    
