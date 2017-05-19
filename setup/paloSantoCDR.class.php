<?php
/* vim: set expandtab tabstop=4 softtabstop=4 shiftwidth=4:
  Codificación: UTF-8
  +----------------------------------------------------------------------+
  | Elastix version 0.5                                                  |
  | http://www.elastix.com                                               |
  +----------------------------------------------------------------------+
  | Copyright (c) 2006 Palosanto Solutions S. A.                         |
  +----------------------------------------------------------------------+
  | Cdla. Nueva Kennedy Calle E 222 y 9na. Este                          |
  | Telfs. 2283-268, 2294-440, 2284-356                                  |
  | Guayaquil - Ecuador                                                  |
  | http://www.palosanto.com                                             |
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
  | The Original Code is: Elastix Open Source.                           |
  | The Initial Developer of the Original Code is PaloSanto Solutions    |
  +----------------------------------------------------------------------+
  $Id: paloSantoCDR.class.php,v 1.1.1.1 2007/07/06 21:31:55 gcarrillo Exp $ */

class paloSantoCDR
{

    function paloSantoCDR(&$pDB)
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

    private function _construirWhereCDR($param)
    {
        $condSQL = array();
        $paramSQL = array();

        if (!is_array($param)) {
        	$this->errMsg = '(internal) invalid parameter array';
            return NULL;
        }
        if (!function_exists('_construirWhereCDR_notempty')) {
        	function _construirWhereCDR_notempty($x) { return ($x != ''); }
        }
        $param = array_filter($param, '_construirWhereCDR_notempty');

        // Fecha y hora de inicio y final del rango
        $sRegFecha = '/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/';
        if (isset($param['date_start'])) {
            if (preg_match($sRegFecha, $param['date_start'])) {
                $condSQL[] = 'calldate >= ?';
                $paramSQL[] = $param['date_start'];
            } else {
                $this->errMsg = '(internal) Invalid start date, must be yyyy-mm-dd hh:mm:ss';
            	return NULL;
            }
        }
        if (isset($param['date_end'])) {
            if (preg_match($sRegFecha, $param['date_end'])) {
                $condSQL[] = 'calldate <= ?';
                $paramSQL[] = $param['date_end'];
            } else {
                $this->errMsg = '(internal) Invalid end date, must be yyyy-mm-dd hh:mm:ss';
                return NULL;
            }
        }
        
        // Estado de la llamada
        if (isset($param['status']) && $param['status'] != 'ALL') {
            $condSQL[] = 'disposition = ?';
            $paramSQL[] = $param['status'];
        }

        // Extensión de fuente o destino
        if (isset($param['extension'])) {
            $condSQL[] = <<<SQL_COND_EXTENSION
(
       src = ?
    OR dst = ?
    OR SUBSTRING_INDEX(SUBSTRING_INDEX(channel,'-',1),'/',-1) = ?
    OR SUBSTRING_INDEX(SUBSTRING_INDEX(dstchannel,'-',1),'/',-1) = ?
)
SQL_COND_EXTENSION;
            array_push($paramSQL, $param['extension'], $param['extension'],
                $param['extension'], $param['extension']);
        }

        // Grupo de timbrado
        if (isset($param['ringgroup'])) {
        	$condSQL[] = 'grpnum = ?';
            $paramSQL[] = $param['ringgroup'];
        }
        
        // Dirección de la llamada
        if (isset($param['calltype']) && 
            in_array($param['calltype'], array('incoming', 'outgoing'))) {
            $sCampo = ($param['calltype'] == 'incoming') ? 'channel' : 'dstchannel';
            $listaTroncales = array();
            if (isset($param['troncales']) && is_array($param['troncales'])) {
                $listaTroncales = $param['troncales'];
            }

            // TODO: expandir troncales al estilo DAHDI/g0

            if (count($listaTroncales) > 0) {
                /* Se asume que la lista de troncales es válida, y que todo canal
                   empieza con la troncal correspondiente */
                if (!function_exists('_construirWhereCDR_troncal2like')) {
                    // Búsqueda por DAHDI/1 debe ser 'DAHDI/1-%'
                    function _construirWhereCDR_troncal2like($s) { return $s.'-%'; }
                }
                $paramSQL = array_merge($paramSQL, array_map('_construirWhereCDR_troncal2like', $listaTroncales));
                $condSQL[] = '('.implode(' OR ', array_fill(0, count($listaTroncales), "$sCampo LIKE ?")).')';                
            } else {
                /* Filtrar por todo lo que parezca troncal. Actualmente se recoge 
                   ZAP, DAHDI, y SIP/IAX2/H323 con un valor a la derecha que 
                   contenga al menos un caracter alfabético.
                   FIXME: no reconoce troncales enteramente numéricas que parecen teléfonos
                   FIXME: no reconoce troncales si tienen caracteres no alfanuméricos
                   FIXME: no reconoce troncales custom (¿cómo se las busca?)
                 */
                $sRegExpTroncal = '^(ZAP/.+|DAHDI/.+|(SIP|IAX|IAX2|H323)/([[:alnum:]]*[[:alpha:]][[:alnum:]]*))-';
                $condSQL[] = "$sCampo REGEXP '$sRegExpTroncal'";
            }
        }

        // field_name, field_pattern
        if (isset($param['field_name']) && isset($param['field_pattern'])) {            
            /* No se intenta interpretar field_pattern. Únicamente se construye
               la condición LIKE para que el campo correspondiente contenga como
               subcadena el valor de field_pattern. */
            $sCampo = $param['field_name'];
            $listaPat = array_filter(
                array_map('trim', 
                    is_array($param['field_pattern']) 
                        ? $param['field_pattern'] 
                        : explode(',', trim($param['field_pattern']))), 
                '_construirWhereCDR_notempty');

            if (!function_exists('_construirWhereCDR_troncal2like2')) {
                function _construirWhereCDR_troncal2like2($s) { return '%'.$s.'%'; }
            }
            $paramSQL = array_merge($paramSQL, array_map('_construirWhereCDR_troncal2like2', $listaPat));
            $fieldSQL = array_fill(0, count($listaPat), "$sCampo LIKE ?");
            
            /* Caso especial: si se especifica field_pattern=src|dst, también 
             * debe buscarse si el canal fuente o destino contiene el patrón
             * dentro de su especificación de canal. */
            if ($sCampo == 'src' || $sCampo == 'dst') {
            	if ($sCampo == 'src') $chanexpr = "SUBSTRING_INDEX(SUBSTRING_INDEX(channel,'-',1),'/',-1)";
                if ($sCampo == 'dst') $chanexpr = "SUBSTRING_INDEX(SUBSTRING_INDEX(dstchannel,'-',1),'/',-1)";
                $paramSQL = array_merge($paramSQL, array_map('_construirWhereCDR_troncal2like2', $listaPat));
                $fieldSQL = array_merge($fieldSQL, array_fill(0, count($listaPat), "$chanexpr LIKE ?"));
            }
            
            $condSQL[] = '('.implode(' OR ', $fieldSQL).')';
        }

        // Construir fragmento completo de sentencia SQL
        $where = array(implode(' AND ', $condSQL), $paramSQL);
        if ($where[0] != '') $where[0] = 'WHERE '.$where[0];
        return $where;
    }

    /**
     * Procedimiento para listar los CDRs desde la tabla asterisk.cdr con varios
     * filtrados aplicados.
     *
     * @param   mixed   $param  Lista de parámetros de filtrado:
     *  date_start      Fecha y hora minima de la llamada, en formato 
     *                  yyyy-mm-dd hh:mm:ss. Si se omite, se lista desde la 
     *                  primera llamada.
     *  date_end        Fecha y hora máxima de la llamada, en formato 
     *                  yyyy-mm-dd hh:mm:ss. Si se omite, se lista hasta la 
     *                  última llamada.
     *  status          Estado de la llamada, guardado en el campo 'disposition'.
     *                  Si se especifica, puede ser uno de los valores siguientes:
     *                  ANSWERED, NO ANSWER, BUSY, FAILED
     *  calltype        Tipo de llamada. Se puede indicar "incoming" o "outgoing".
     *  troncales       Arreglo de troncales por el cual se debe filtrar las
     *                  llamadas según el valor almacenado en la columna 'channel'
     *                  o 'dstchannel', para calltype de tipo "incoming" o 
     *                  "outgoing", respectivamente. Se ignora si se omite un
     *                  valor para calltype.
     *  extension       Número de extensión para el cual filtrar los números. 
     *                  Este valor filtra por los campos 'src' y 'dst'.
     *  field_name
     *  field_pattern   Campo y subcadena para buscar dentro de los registros.
     *                  El valor de field_pattern puede ser un arreglo, o un
     *                  valor separado por comas, y buscará múltiples patrones.
     * @param   mixed   $limit  Máximo número de CDRs a leer, o NULL para todos
     * @param   mixed   $offset Inicio de lista de CDRs, si se especifica $limit
     *
     * @return  mixed   Estructura con los siguientes campos:
     *  total   integer     Número total de CDRs disponibles con los filtrados
     *  cdrs    mixed       Lista de los cdrs. Se devuelven los siguientes campos
     *                      en el orden en que se listan a continuación:
     *                      calldate, src, dst, channel, dstchannel, disposition, 
     *                      uniqueid, duration, billsec, accountcode
     */
    function listarCDRs($param,$limit = NULL, $offset = 0)
    {
        $resultado = array();
        list($sWhere, $paramSQL) = $this->_construirWhereCDR($param);
        if (is_null($sWhere)) return NULL;
        // Cuenta del total de registros recuperados
        $sPeticionSQL = 
            'SELECT COUNT(*) FROM cdr '.
            'LEFT JOIN asterisk.ringgroups '.
                'ON asteriskcdrdb.cdr.dst = asterisk.ringgroups.grpnum '.
            $sWhere;
        $r = $this->_DB->getFirstRowQuery($sPeticionSQL, FALSE, $paramSQL);
        if (!is_array($r)) {
            $this->errMsg = '(internal) Failed to count CDRs - '.$this->_DB->errMsg;
            return NULL;
        }
        //TODO: ESTO DEBERIA SER QUITADO EN UN FUTURO
        $resultado['total'] = $r[0];
        
        $resultado['cdrs'] = array();
        if ($resultado['total'] <= 0) return $resultado;

        // Los datos de los registros, respetando limit y offset
        $sPeticionSQL = 
            'SELECT calldate, src, dst, channel, dstchannel, disposition, '.
                'uniqueid, duration, billsec, accountcode, grpnum, description '.
            'FROM cdr '.
            'LEFT JOIN asterisk.ringgroups '.
                'ON asteriskcdrdb.cdr.dst = asterisk.ringgroups.grpnum '.
            $sWhere.
            ' ORDER BY calldate DESC';
        if (!empty($limit)) {
            $sPeticionSQL .= " LIMIT ? OFFSET ?";
            array_push($paramSQL, $limit, $offset);
        }
        $resultado['cdrs'] = $this->_DB->fetchTable($sPeticionSQL, FALSE, $paramSQL);
        if (!is_array($resultado['cdrs'])) {
            $this->errMsg = '(internal) Failed to fetch CDRs - '.$this->_DB->errMsg;
            return NULL;
        }
        return $resultado;
    }

    /**
     * Procedimiento para contar los CDRs desde la tabla asterisk.cdr con varios
     * filtrados aplicados. Véase listarCDRs para los parámetros conocidos.
     *
     * @param   mixed   $param  Lista de parámetros de filtrado.
     * 
     * @return  mixed   NULL en caso de error, o número de CDRs del filtrado
     */
    function contarCDRs($param)
    {
        list($sWhere, $paramSQL) = $this->_construirWhereCDR($param);
        if (is_null($sWhere)) return NULL;

        // Cuenta del total de registros recuperados
        $sPeticionSQL = 
            'SELECT COUNT(*) FROM cdr '.
            'LEFT JOIN asterisk.ringgroups '.
                'ON asteriskcdrdb.cdr.dst = asterisk.ringgroups.grpnum '.
            $sWhere;
        $r = $this->_DB->getFirstRowQuery($sPeticionSQL, FALSE, $paramSQL);
        if (!is_array($r)) {
            $this->errMsg = '(internal) Failed to count CDRs - '.$this->_DB->errMsg;
            return NULL;
        }
        return $r[0];
    }

    // Función de compatibilidad para código antiguo
    function getNumCDR($date_start="", $date_end="", $field_name="", $field_pattern="",$status="ALL",$calltype="",$troncales=NULL, $extension="")
    {
        $param = $this->getParam($date_start,$date_end,$field_name,$field_pattern,$status,$calltype,$troncales,$extension);
        return $this->contarCDRs($param);
    }
    
    /**
     * Procedimiento para borrar los CDRs en la tabla asterisk.cdr que coincidan
     * con los filtros indicados.
     * @param   mixed   $param  Lista de parámetros de filtrado. Véase listarCDRs
     *                          para los parámetros permitidos.
     *
     * @return  bool    VERDADERO en caso de éxito, FALSO en caso de error.
     */
    function borrarCDRs($param)
    {
        list($sWhere, $paramSQL) = $this->_construirWhereCDR($param);
        if (is_null($sWhere)) return NULL;

        // Borrado de los registros seleccionados
        $sPeticionSQL = 
            'DELETE cdr FROM cdr '.
            'LEFT JOIN asterisk.ringgroups '.
                'ON asteriskcdrdb.cdr.dst = asterisk.ringgroups.grpnum '.
            $sWhere;
        $r = $this->_DB->genQuery($sPeticionSQL, $paramSQL);
        if (!$r) {
            $this->errMsg = '(internal) Failed to delete CDRs - '.$this->_DB->errMsg;
        }
        return $r;
    }
    
    /* Procedimiento que ayuda a empaquetar los parámetros de las funciones 
     * viejas para compatibilidad */
    private function getParam($date_start="", $date_end="", $field_name="", $field_pattern="",$status="ALL",$calltype="",$troncales=NULL, $extension="")
    {
        $param = array();
        if (!empty($date_start)) $param['date_start'] = $date_start;
        if (!empty($date_end)) $param['date_end'] = $date_end;
        if (!empty($field_name)) $param['field_name'] = $field_name;
        if (!empty($field_pattern)) $param['field_pattern'] = $field_pattern;
        if (!empty($status) && $status != 'ALL') $param['status'] = $status;
        if (!empty($calltype)) $param['calltype'] = $calltype;
        if (!empty($troncales)) $param['troncales'] = $troncales;
        if (!empty($extension)) $param['extension'] = $extension;
        return $param;
    }

    // Función de compatibilidad para código antiguo
    function obtenerCDRs($limit, $offset, $date_start="", $date_end="", $field_name="", $field_pattern="",$status="ALL",$calltype="",$troncales=NULL, $extension="")
    {
        $param = $this->getParam($date_start, $date_end, $field_name, $field_pattern,$status,$calltype,$troncales, $extension);
        $r = $this->listarCDRs($param, $limit, $offset);
        return is_array($r) 
            ? array(
                'NumRecords'    =>  array($r['total']),
                'Data'          =>  $r['cdrs'],
                )
            : NULL;
    }

    // Función de compatibilidad para código antiguo
    function Delete_All_CDRs($date_start="", $date_end="", $field_name="", $field_pattern="",$status="ALL",$calltype="",$troncales=NULL, $extension="")
    {
        $param = $this->getParam($date_start, $date_end, $field_name, $field_pattern,$status,$calltype,$troncales, $extension);
        return $this->borrarCDRs($param);
    }
}
?>