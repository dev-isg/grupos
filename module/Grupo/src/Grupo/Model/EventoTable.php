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
                $selecttot->where(array('ta_grupo.va_nombre'=>$nombre));
            }
            $selecttot ->group('ta_grupo.in_id')->order('ta_grupo.in_id desc');
            $selectString = $sql->getSqlStringForSqlObject($selecttot);
            $resultSet = $adapter->query($selectString, $adapter::QUERY_MODE_EXECUTE);
            
            return $resultSet;
        
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
    
  public function guardarEvento(Evento $evento,$idgrupo=null,$imagen){
//       $fecha_esp = str_replace("/", "-", $evento->va_fecha);
//       $timestamp = strtotime($evento->va_fecha);
//         var_dump($timestamp);Exit;
      $data=array(
         'va_nombre'=>$evento->va_nombre,
         'va_descripcion'=>$evento->va_descripcion,
         'va_costo'=>$evento->va_costo,
          'va_fecha'=>$evento->va_fecha,
         'va_latitud'=>$evento->va_latitud,
          'va_longitud'=>$evento->va_longitud,
          'va_direccion'=>$evento->va_direccion,
          'va_referencia'=>$evento->va_referencia,
          'va_imagen'=>$imagen,
          'va_estado'=>$evento->va_estado,
          'va_max'=>$evento->va_max,
          'va_min'=>$evento->va_min,
          'va_duracion'=>$evento->va_duracion,
          'ta_usuario_in_id'=>$evento->ta_usuario_in_id,
//          'ta_ubigeo_in_id'=>$evento->ta_ubigeo_in_id,//distrito,//$convertir[0]['in_id']   
          'ta_grupo_in_id'=>$idgrupo//$evento->ta_grupo_in_id
      );
      
    
      $id = (int) $evento->in_id;
  
      foreach($data as $key=>$value){
          if(empty($value)){
              $data[$key]=0;
          }
      }
     
     
      if ($id == 0) {
          $this->tableGateway->insert($data);
          $idevento=$this->tableGateway->getLastInsertValue();
          return $idevento;
          
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
    
    
    /////////////////////////////////////////////EMPIEZA///////////////////////////////////////////////////
    
//     public function comprobarGrupo($iduser){
//         try{
//             $row=$this->getEventoUsuario($idevent, $iduser);
//         }catch (\Exception $e){
            
//         }
//         $adapter = $this->tableGateway->getAdapter();
//         $sql = new Sql($adapter);
//         $selecttot = $sql->select()
//         ->from('ta_usuario_has_ta_grupo')
//         ->join('ta_grupo', 'ta_usuario_has_ta_grupo.ta_grupo_in_id=ta_grupo.in_id', array(), 'LEFT')
//         ->join('ta_evento', 'ta_grupo.in_id=ta_evento.ta_grupo_in_id', array('id_grupo'=>'ta_grupo_in_id'), 'LEFT')
//         ->where(array(
//             'ta_usuario_has_ta_grupo.ta_usuario_in_id' => $iduser,
//             'ta_usuario_has_ta_grupo.va_estado'=>'activo',
//             'ta_evento.ta_grupo_in_id is not null'
//         ));
//         $selectString = $this->tableGateway->getSql()->getSqlStringForSqlObject($selecttot);
        
//         $adapter = $this->tableGateway->getAdapter();
//         $row = $adapter->query($selectString, $adapter::QUERY_MODE_EXECUTE);
//         if (! $row) {
//             throw new \Exception("No se encontro evento");
//         }
//        $row=$row->current();
//        $Grupoevent=$this->getEventoUsuario($idevent, $iduser);
//        if($row->id_grupo==$Grupoevent->idgrup){
//            return true;
//        }else{
//            $this->unirseGrupo($idgrup,$iduser)
//        }
        
//     }

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
     
        public function unirseGrupo($idgrup,$iduser){
            if($this->getGrupoUsuario($idgrup,$iduser)){
                return true;
//                 $consulta = $this->tableGateway->getSql()->update()->table('ta_usuario_has_ta_grupo')
//                 ->set(array('va_estado'=>'activo'))
//                 ->where(array('ta_usuario_in_id'=>$iduser,'ta_grupo_in_id'=>$idgrup));
    
            }else{
                $consulta = $this->tableGateway->getSql()->insert()->into('ta_usuario_has_ta_grupo')
                ->values(array('ta_usuario_in_id'=>$iduser,'ta_grupo_in_id'=>$idgrup,'va_estado'=>'activo','va_fecha'=>date('c')));
            }
            $selectString = $this->tableGateway->getSql()->getSqlStringForSqlObject($consulta);
            $adapter=$this->tableGateway->getAdapter();
            $row=$adapter->query($selectString, $adapter::QUERY_MODE_EXECUTE);
            if (!$row) {
                throw new \Exception("No se puede unir al grupo");
            }
            return true;
    
        }
////////////////////////////////////////////////////////////FIN////////////////////////////////////////////////
    public function getEventoUsuario($idevent, $iduser)
    {
        $adapter = $this->tableGateway->getAdapter();
        $sql = new Sql($adapter);
        $selecttot = $sql->select()
            ->from('ta_usuario_has_ta_evento')
            ->join('ta_evento', 'ta_usuario_has_ta_evento.ta_evento_in_id=ta_evento.in_id', 
                array('idgrup'=>'ta_grupo_in_id'), 'LEFT')
        // ->join('ta_grupo','ta_evento.ta_grupo_in_id=ta_grupo_in_id',array(),'LEFT')
            ->where(array(
                'ta_usuario_has_ta_evento.ta_evento_in_id' => $idevent,
                'ta_usuario_has_ta_evento.ta_usuario_in_id' => $iduser,
                'ta_evento.ta_grupo_in_id is not null'
                    ));
        $selectString = $this->tableGateway->getSql()->getSqlStringForSqlObject($selecttot);
        
        $adapter = $this->tableGateway->getAdapter();
        $row = $adapter->query($selectString, $adapter::QUERY_MODE_EXECUTE);
        if (! $row) {
            throw new \Exception("No se encontro evento");
         }
         return $row->current();
     }
     public function unirseEvento($idevent,$iduser){
         if($this->getEventoUsuario($idevent,$iduser)){
             $consulta = $this->tableGateway->getSql()->update()->table('ta_usuario_has_ta_evento')
             ->set(array('va_estado'=>'activo'))
             ->where(array('ta_usuario_in_id'=>$iduser,'ta_evento_in_id'=>$idevent));
             
         }else{   
             $evento=$this->getEvento($idevent);
             $idgrup=$evento->ta_grupo_in_id;
             $this->unirseGrupo($idgrup,$iduser);   
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
     
     public function retiraEvento($idevent,$iduser){
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
     
       public function listadoEvento()
    {
              $fecha = date("Y-m-d h:m:s"); 
         $adapter = $this->tableGateway->getAdapter();
            $sql = new Sql($adapter);
            $selecttot = $sql->select()
                    ->from('ta_evento')
          //             ->join('ta_comentario','ta_comentario.ta_evento_in_id=ta_evento.in_id', array('comentarios' => new \Zend\Db\Sql\Expression('COUNT(ta_comentario.in_id)')), 'left')            
      
          ->join('ta_grupo','ta_grupo.in_id=ta_evento.ta_grupo_in_id',array('categoria'=>'ta_categoria_in_id'),'left')
          ->join('ta_categoria','ta_grupo.ta_categoria_in_id=ta_categoria.in_id',array('nombre_categoria'=>'va_nombre','idcategoria'=>'in_id'),'left')
      //    ->join(array('c' => 'ta_comentario'), 'c.ta_evento_in_id=ta_evento.in_id', array('comentarios' => new \Zend\Db\Sql\Expression('COUNT(c.in_id)')), 'left')          
          ->where(array('ta_evento.va_estado'=>'activo','ta_evento.va_fecha>=?'=>$fecha))           
          ->order('in_id desc');  
        $selectString = $sql->getSqlStringForSqlObject($selecttot);
        $resultSet = $adapter->query($selectString, $adapter::QUERY_MODE_EXECUTE); 
        return $resultSet->buffer();
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
    {    $fecha = date("Y-m-d h:m:s");
         $adapter = $this->tableGateway->getAdapter();
            $sql = new Sql($adapter);
            $selecttot = $sql->select()
                    ->from('ta_evento')
          ->join('ta_grupo','ta_grupo.in_id=ta_evento.ta_grupo_in_id',array('categoria'=>'ta_categoria_in_id'),'left')
          ->join('ta_categoria','ta_grupo.ta_categoria_in_id=ta_categoria.in_id',array('nombre_categoria'=>'va_nombre'),'left')   
          ->where(array('ta_evento.va_nombre LIKE ?'=> '%'.$consulta.'%','ta_evento.va_estado'=>'activo','ta_evento.va_fecha>=?'=>$fecha)) 
          ->order('in_id desc');
            $selectString = $sql->getSqlStringForSqlObject($selecttot);
            $resultSet = $adapter->query($selectString, $adapter::QUERY_MODE_EXECUTE);  
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
    
    
    
     public function usuariosevento($id)
    {  
//         $id=(int)$id;
         $adapter = $this->tableGateway->getAdapter();
            $sql = new Sql($adapter);
            $select = $sql->select();
                 $select->from('ta_usuario_has_ta_evento')
              ->join('ta_usuario','ta_usuario.in_id=ta_usuario_has_ta_evento.ta_usuario_in_id',array('nombre_usuario'=>'va_nombre','imagen'=>'va_foto','descripcion_usuario'=>'va_descripcion'),'left')
          // ->join('ta_comentario','ta_comentario.ta_usuario_in_id=ta_usuario.in_id',array('descripcion'=>'va_descripcion','fecha_cometario'=>'va_fecha'),'left')           
             ->where(array('ta_usuario_has_ta_evento.ta_evento_in_id' => $id));
            $selectString = $sql->getSqlStringForSqlObject($select);
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
            ->where(array('ta_comentario.ta_evento_in_id' => $id));
            $selectString = $sql->getSqlStringForSqlObject($select);
            $resultSet = $adapter->query($selectString, $adapter::QUERY_MODE_EXECUTE);
            $resultSet->buffer();
//             var_dump($resultSet->toArray());Exit;
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
        $adapter->query($selectString, $adapter::QUERY_MODE_EXECUTE);
                
        
    }


 
      public function usuarioseventos($id)
    {
         $adapter = $this->tableGateway->getAdapter();
            $sql = new Sql($adapter);
            $selecttot = $sql->select()
          ->from('ta_usuario_has_ta_evento')      
          ->join('ta_evento','ta_evento.in_id=ta_usuario_has_ta_evento.ta_evento_in_id', array('nombre' =>'va_nombre','descripcion' =>'va_descripcion','imagen' =>'va_imagen','fecha' =>'va_fecha','id' =>'in_id'), 'left')         
          ->join('ta_grupo','ta_grupo.in_id=ta_evento.ta_grupo_in_id') 
          ->join('ta_categoria','ta_categoria.in_id=ta_grupo.ta_categoria_in_id', array('nombre_categoria' =>'va_nombre','idcategoria' =>'in_id'), 'left')              
         ->where(array('ta_usuario_has_ta_evento.ta_usuario_in_id'=>$id,'ta_evento.va_estado'=>'activo'));
            $selectString = $sql->getSqlStringForSqlObject($selecttot);
            $resultSet = $adapter->query($selectString, $adapter::QUERY_MODE_EXECUTE);  
            return $resultSet;
    }
    

    
    public function miseventos($id)
    {
         $adapter = $this->tableGateway->getAdapter();
            $sql = new Sql($adapter);
            $selecttot = $sql->select()
          ->from('ta_evento')      
          //->join('ta_evento','ta_evento.in_id=ta_usuario_has_ta_evento.ta_evento_in_id', array('nombre' =>'va_nombre','descripcion' =>'va_descripcion','imagen' =>'va_imagen','fecha' =>'va_fecha','id' =>'in_id'), 'left')         
          ->join('ta_grupo','ta_grupo.in_id=ta_evento.ta_grupo_in_id',array('monbregrupo' =>'va_nombre','idgrupo' =>'in_id','describe' =>'va_descripcion','imagen' =>'va_imagen'), 'left') 
          ->join('ta_categoria','ta_categoria.in_id=ta_grupo.ta_categoria_in_id', array('nombre_categoria' =>'va_nombre','idcategoria' =>'in_id'), 'left')              
         ->where(array('ta_evento.ta_usuario_in_id'=>$id));
            $selectString = $sql->getSqlStringForSqlObject($selecttot);
         //   var_dump($selectString);exit;
            $resultSet = $adapter->query($selectString, $adapter::QUERY_MODE_EXECUTE);  
            return $resultSet;
    }
    
    
    public function eventocategoria($id)
    {
            $fecha = date("Y-m-d h:m:s"); 
            $adapter = $this->tableGateway->getAdapter();
            $sql = new Sql($adapter);
            $selecttot = $sql->select()
                    ->from('ta_evento')
          ->join('ta_grupo','ta_grupo.in_id=ta_evento.ta_grupo_in_id',array('categoria'=>'ta_categoria_in_id'),'left')
          ->join('ta_categoria','ta_grupo.ta_categoria_in_id=ta_categoria.in_id',array('nombre_categoria'=>'va_nombre'),'left')
         ->where(array('ta_evento.va_estado'=>'activo','ta_evento.va_fecha>=?'=>$fecha,'ta_categoria.in_id'=>$id))           
          ->order('in_id desc');  
        $selectString = $sql->getSqlStringForSqlObject($selecttot);
        $resultSet = $adapter->query($selectString, $adapter::QUERY_MODE_EXECUTE); 
        return $resultSet->buffer();
    } 
     

}

    
