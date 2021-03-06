<?php
/* vim: set expandtab tabstop=4 softtabstop=4 shiftwidth=4:
  Codificación: UTF-8
  +----------------------------------------------------------------------+
  | Issabel version 4.0                                                  |
  | http://www.issabel.org                                               |
  +----------------------------------------------------------------------+
  | Copyright (c) 2021 Issabel Foundation                                |
  | Copyright (c) 2006 Palosanto Solutions S. A.                         |
  +----------------------------------------------------------------------+
  | The contents of this file are subject to the General Public License  |
  | (GPL) Version 2 (the "License"); you may not use this file except in |
  | compliance with the License. You may obtain a copy of the License at |
  | http://www.opensource.org/licenses/gpl-license.php                   |
  |                                                                      |
  | Software distributed under the License is distributed on an "AS IS"  |
  | basis, WITHOUT WARRANTY OF ANY KIND, either express or implied. See  |
  | the License for the specific language governing rights and           |
  | limitations under the License.                                       |
  +----------------------------------------------------------------------+
  | The Initial Developer of the Original Code is PaloSanto Solutions    |
  +----------------------------------------------------------------------+
  $Id: paloSantoQueue.class.php, Thu 20 May 2021 08:13:21 AM EDT, nicolas@issabel.com
*/

if (isset($arrConf['basePath'])) {
    include_once($arrConf['basePath'] . "/libs/paloSantoDB.class.php");
} else {
    include_once("libs/paloSantoDB.class.php");
}

class paloQueue {

    var $_DB; // instancia de la clase paloDB
    var $errMsg;

    function __construct(&$pDB)
    {
        // Se recibe como parámetro una referencia a una conexión paloDB
        if (is_object($pDB)) {
            $this->_DB =& $pDB;
            $this->errMsg = $this->_DB->errMsg;
        } else {
            $dsn = (string)$pDB;
            $this->_DB = new paloDB($dsn);

            if (!$this->_DB->connStatus) {
                $this->errMsg = $this->_DB->errMsg;
                // debo llenar alguna variable de error
            } else {
                // debo llenar alguna variable de error
            }
        }
    }

    /**
    * Procedimiento para obtener el listado de los trunks existentes. 
    *
    * @return array    Listado de trunks en el siguiente formato, o FALSE en caso de error:
    *  array(
    *      array(variable, valor),
    *      ...
    *  )
    */
    function getQueue($id = null)
    {
        $arr_result = FALSE;
    
        $this->errMsg = "";

        $where_id = "";

        $arr_result =& $this->_DB->fetchTable("SHOW TABLES LIKE 'queues_config'");
        if (!is_array($arr_result)) {
            $this->errMsg = $this->_DB->errMsg;
            $arr_result = FALSE;
        } else {
            if (count($arr_result) > 0) {
                // Tratar para esquema de base de datos de version 2.4.x
                if (!is_null($id)) {
                    $where_id = " and extension='$id'";
                }
                $sPeticionSQL = "SELECT DISTINCT(extension), CONCAT(extension, ' ', descr) FROM queues_config WHERE 1=1 ".$where_id;
                $arr_result =& $this->_DB->fetchTable($sPeticionSQL);
                if (!is_array($arr_result)) {
                    $this->errMsg = $this->_DB->errMsg;
                    $arr_result = FALSE;
                }
            } else {
                // Tratar para esquema de base de datos de version 2.3.1
                if (!is_null($id)) {
                    $where_id = " and queues.id='$id'";
                }
                $sPeticionSQL = "SELECT distinct(queues.id), CONCAT(queues.id,' ',extensions.descr)
                         FROM queues, extensions
                         WHERE queues.id=extensions.extension and extensions.application='Queue' ".$where_id;

                $arr_result =& $this->_DB->fetchTable($sPeticionSQL);
                if (!is_array($arr_result)) {
                    $this->errMsg = $this->_DB->errMsg;
                    $arr_result = FALSE;
                }
            }
        }
        return $arr_result;
    }
}
?>
