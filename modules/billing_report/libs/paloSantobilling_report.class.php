<?php
  /* vim: set expandtab tabstop=4 softtabstop=4 shiftwidth=4:
  Codificación: UTF-8
  +----------------------------------------------------------------------+
  | Elastix version 2.0.0-12                                               |
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
  $Id: paloSantobilling_report2.class.php,v 1.1 2010-01-15 01:01:20 Eduardo Cueva ecueva@palosanto.com Exp $ */
require_once "libs/misc.lib.php";
class paloSantobilling_report {
    var $_DB;
    var $errMsg;

    function paloSantobilling_report(&$pDB)
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

    /*HERE YOUR FUNCTIONS*/
	
	 function obtainNumReport($filter_field, $filter_value, $start_date, $end_date, $pDB, $time, $disposition, $calltype, $arrConfig){
		  $pathLog = "/var/log/asterisk";
		  $where = "";
		  $where2 = "";
		  if($filter_field == 'src' && $filter_value!=""){
				$where .= " AND c.src = $filter_value ";
		  }
		  if($filter_field == 'dst' && $filter_value!=""){
				$where .= " AND c.dst like $filter_value ";
		  }
		  if($filter_field == 'duration' && $time>=0){
				$where .= " AND c.billsec = '$time' ";
		  }
		  if($filter_field == 'rate_applied' && $filter_value!=""){
				$where .= " AND r.name like $filter_value ";
		  }
		  if($filter_field == 'rate_value' && $filter_value!=""){
				$where .= " AND r.rate = $filter_value ";
		  }
          if($filter_field == 'accountcode' && $filter_value!=""){
                $where .= " AND c.accountcode = $filter_value ";
          }
		  if($filter_field == 'dstchannel' && $filter_value!=""){
				$where .= " AND c.dstchannel like $filter_value ";
		  }

                        $trunks = $this->getTrunksAll($arrConfig);

			if($calltype == "outgoing"){
				foreach($trunks as $key => $value){
					$where2 .= " OR c.dstchannel like '%".$value[1]."%' ";
				}
				$where .= "AND (c.dstchannel like '%DAHDI%' $where2) ";
		  }


		  $query="SELECT
						count(*)
					FROM
						cdr c
							LEFT OUTER JOIN rate r
								ON 
								c.calldate >= r.fecha_creacion AND
								substr(c.dst,'',length(r.prefix)) = r.prefix AND
								substr(c.dstchannel,'',length(r.trunk)) = r.trunk AND
                                                                r.prefix <> ''  
					WHERE c.disposition = '$disposition' AND 
							c.calldate >= '$start_date' AND 
							c.calldate <= '$end_date' 
							$where";

		$result = $pDB->genQuery("attach database '$pathLog/master.db' as master;",true);
        $result = $pDB->getFirstRowQuery($query);
        return $result[0];

	 }

	 function obtainReport($limit, $offset, $filter_field, $filter_value, $start_date, $end_date, $pDB, $time, $disposition, $calltype, $arrConfig){

		  $pathLog = "/var/log/asterisk";
		  $where = "";
		  $where2 = "";
		  if($filter_field == 'src' && $filter_value!=""){
				$where .= " AND c.src = $filter_value ";
		  }
		  if($filter_field == 'dst' && $filter_value!=""){
				$where .= " AND c.dst like $filter_value ";
		  }
		  if($filter_field == 'duration' && $time>=0){
				$where .= " AND c.billsec = '$time' ";
		  }
		  if($filter_field == 'rate_applied' && $filter_value!=""){
				$where .= " AND r.name like $filter_value "; ///// arreglar
		  }
		  if($filter_field == 'rate_value' && $filter_value!=""){
				$where .= " AND r.rate = $filter_value "; ///// arreglar
		  }
          if($filter_field == 'accountcode' && $filter_value!=""){
                $where .= " AND c.accountcode = $filter_value ";
          }
		  if($filter_field == 'dstchannel' && $filter_value!=""){
				$where .= " AND c.dstchannel like $filter_value ";
		  }
		  $trunks = $this->getTrunksAll($arrConfig);

		  if($calltype == "outgoing"){
				foreach($trunks as $key => $value){
					$where2 .= " OR c.dstchannel like '%".$value[1]."%' ";
				}
				$where .= "AND (c.dstchannel like '%DAHDI%' $where2) ";
		  }

		  $query = "SELECT
                        c.AcctId AS cid,
                        r.id AS rid,
						c.calldate AS Date,
						r.name AS Rate_applied,
						r.rate AS Rate_value,
						r.rate_offset AS Offset,
						c.src AS Src,
						c.dst AS Destination,
						c.dstchannel AS Dst_channel,
						c.billsec AS duration,
                        r.hided_digits AS digits,
                        c.accountcode AS accountcode
					FROM
						cdr c
							LEFT OUTER JOIN 
                        rate r
							ON 
								c.calldate >= r.fecha_creacion AND
								substr(c.dst,'',length(r.prefix)) = r.prefix AND
								substr(c.dstchannel,'',length(r.trunk)) = r.trunk AND
                                                                r.prefix <> '' 
					WHERE 
                        c.disposition = '$disposition' AND 
						c.calldate >= '$start_date' AND 
						c.calldate <= '$end_date' 
						$where
					LIMIT $limit OFFSET $offset";
		$result = $pDB->genQuery("attach database '$pathLog/master.db' as master;",true);
        $result = $pDB->fetchTable($query,true);
        return $result;
	 }

	 function getTrunks($pDB){
		  $query = "select trunk from rate where trunk <> '';"; // not default rate
		  $result = $pDB->fetchTable($query,true);
		  return $result;
	 }

	 function getTrunksAll($arrConfig){
		  include_once "libs/paloSantoTrunk.class.php";

		  $pDB = new paloDB("sqlite3:////var/www/db/trunk.db");
	          $oTrunk     = new paloTrunk($pDB);
		  $arrTrunks=array();
		  $result = "";
		  $dsn = $arrConfig['AMPDBENGINE']['valor'] . "://" . $arrConfig['AMPDBUSER']['valor'] . ":" . $arrConfig['AMPDBPASS']['valor'] . "@" . $arrConfig['AMPDBHOST']['valor'] . "/asterisk";
		  $pDB = new paloDB($dsn);
		  $arrTrunks = getTrunks($pDB);

		  if (is_array($arrTrunks)) {
			  foreach ($arrTrunks as $tupla) {
				  if (substr($tupla[1], 0, 3) != 'DAHDI' || $tupla[1]{4} == 'g')
					  $t[] = $tupla;
			  }
			  $arrTrunks = $t;
		  }
		  return $arrTrunks;
	 }

	 function getDefaultRate($pDB){
		  $query = "select rate, rate_offset from rate where name='Default';";
		  $result = $pDB->fetchTable($query,true);
		  return $result[0];
	 }

    function getbilling_reportById($id)
    {
        $data = array($id);
        $query = "SELECT * FROM table WHERE id=?";

        $result=$this->_DB->getFirstRowQuery($query,true,$data);

        if($result==FALSE){
            $this->errMsg = $this->_DB->errMsg;
            return null;
        }
        return $result;
    }
	
    function getRates($pDB)
    {
        $query = "SELECT * FROM rate";

        $result = $pDB->fetchTable($query,true);

        if($result==FALSE){
            $this->errMsg = $this->_DB->errMsg;
            return null;
        }
        return $result;
    }

	 function Sec2HHMMSS($sec)
	{
		$HH = '00'; $MM = '00'; $SS = '00';
	
		if($sec >= 3600){ 
			$HH = (int)($sec/3600);
			$sec = $sec%3600; 
			if( $HH < 10 ) $HH = "0$HH";
		}
	
		if( $sec >= 60 ){ 
			$MM = (int)($sec/60);
			$sec = $sec%60;
			if( $MM < 10 ) $MM = "0$MM";
		}
	
		$SS = $sec;
		if( $SS < 10 ) $SS = "0$SS";
	
		return $HH."h ".$MM."m ".$SS."s";
	}
}
?>
