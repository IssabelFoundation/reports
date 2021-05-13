<?php
/* vim: set expandtab tabstop=4 softtabstop=4 shiftwidth=4:
  Codificación: UTF-8
  +----------------------------------------------------------------------+
  | Issabel version 0.5                                                  |
  | http://www.issabel.org                                               |
  +----------------------------------------------------------------------+
  | Copyright (c) 2021 Issabel Foundation                                |
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
  $Id: index.php, Thu 13 May 2021 06:40:42 PM EDT, nicolas@issabel.com
*/

include_once "libs/paloSantoGraphImage.lib.php";

function _moduleContent(&$smarty, $module_name) {

    function getData($id, $getTime=0) {
        $chUsage = new paloSantoChannelUsage;
        $hours = array();
        $channels = array();
        $data = $chUsage->channelsUsage($id);
        $times = $data['DATA']['DAT_1']['VALUES'];
        foreach ($times as $index => $value) {
            //echo $index.' - '.$value.'</br>';
            //echo date('d-M H:m:s',$value). '</br>';
            array_push($hours,$index*1000);
            array_push($channels,$value);
        }
        if ($getTime==1) {
           return $hours;
        } else {
           return $channels;
        }
    }

    //include module files
    include_once "modules/$module_name/configs/default.conf.php";
    include_once "modules/$module_name/libs/paloSantoChannelUsage.class.php";

    load_language_module($module_name);

    //global variables
    global $arrConf;
    global $arrConfModule;
    $arrConf = array_merge($arrConf,$arrConfModule);

    //folder path for custom templates
    $base_dir=dirname($_SERVER['SCRIPT_FILENAME']);
    $templates_dir=(isset($arrConf['templates_dir']))?$arrConf['templates_dir']:'themes';
    $local_templates_dir="$base_dir/modules/$module_name/".$templates_dir.'/'.$arrConf['theme'];

    $smarty->assign("title",_tr("Channels Usage Report"));
    $smarty->assign("icon","modules/$module_name/images/reports_channel_usage.png");
    if (date_default_timezone_get()) {
       $timezone = date_default_timezone_get();
    }
    $chUsage = new paloSantoChannelUsage;
    //2 - total 3 - dahdi 4 - SIP 5 - IAX 6 - H323 7 - Local
    $arrHours = getData(2,1);
    $arrTotal = getData(2);
    $arrDahdi = getData(3);
    $arrSIP   = getData(4);
    $arrIAX   = getData(5);
    $arrH323  = getData(6);
    $arrLocal = getData(7);
    $smarty->assign("timezone", $timezone);
    $smarty->assign("hoursJSON", json_encode($arrHours));
    $smarty->assign("totalJSON", json_encode($arrTotal));
    $smarty->assign("dahdiJSON", json_encode($arrDahdi));
    $smarty->assign("sipJSON",   json_encode($arrSIP));
    $smarty->assign("iaxJSON",   json_encode($arrIAX));
    $smarty->assign("h323JSON",  json_encode($arrH323));
    $smarty->assign("localJSON", json_encode($arrLocal));
    return $smarty->fetch("$local_templates_dir/charts.tpl");
}
?>
