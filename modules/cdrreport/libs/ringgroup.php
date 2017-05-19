<?php
/*
  vim: set expandtab tabstop=4 softtabstop=4 shiftwidth=4:
  Codificación: UTF-8
  +----------------------------------------------------------------------+
  | Copyright (c) 1997-2003 Palosanto Solutions S. A.                    |
  +----------------------------------------------------------------------+
  | Cdla. Nueva Kennedy Calle E 222 y 9na. Este                          |
  | Telfs. 2283-268, 2294-440, 2284-356                                  |
  | Guayaquil - Ecuador                                                  |
  +----------------------------------------------------------------------+
  | Este archivo fuente está sujeto a las políticas de licenciamiento    |
  | de Palosanto Solutions S. A. y no está disponible públicamente.      |
  | El acceso a este documento está restringido según lo estipulado      |
  | en los acuerdos de confidencialidad los cuales son parte de las      |
  | políticas internas de Palosanto Solutions S. A.                      |
  | Si Ud. está viendo este archivo y no tiene autorización explícita    |
  | de hacerlo, comuníquese con nosotros, podría estar infringiendo      |
  | la ley sin saberlo.                                                  |
  +----------------------------------------------------------------------+
  | Autores: Carlos Barcos <cbarcos@palosanto.com>                       |
  +----------------------------------------------------------------------+
  $Id: ringgroup.php,v 1.1 2007/01/09 23:49:36 alex Exp $
*/
class RingGroup {
    var $db=null;
    var $errMsg=null;

    function __construct($pDB){
      $this->db=$pDB;
      if (is_object($pDB)) {
        $this->db =& $pDB;
        $this->errMsg = $this->db->errMsg;
      }else{
        $dsn = (string)$pDB;
        $this->db = new paloDB($dsn);
        if (!$this->db->connStatus) {
          $this->errMsg = $this->db->errMsg;
        }
      }
    }
    
    function getRingGroup($id=null){
      $where="";
      $result=null;
      $data=array();
      $query   = "SELECT grpnum,grplist,description FROM ringgroups ";
      if(!is_null($id) && !empty($id) && is_numeric($id) ){
        $query .= "where grpnum=?";
        $result=$this->db->getFirstRowQuery($query, true, array($id));
        if(isset($result) && is_array($result) && count($result)>0){
          $data[$result['grpnum']]=$result['description'];
          return $data;
        }
      }else{
        $result=$this->db->fetchTable($query, true);
        if(isset($result) && is_array($result) && count($result)>0){
          foreach ($result as $row){
            $data[$row['grpnum']]=$row['grpnum'].' / '.$row['description'];
          }
          return $data;
        }
      }
      return $data;
    }
}
?>