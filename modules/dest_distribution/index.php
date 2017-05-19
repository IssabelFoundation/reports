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
  $Id: index.php,v 1.1.1.1 2007/07/06 21:31:56 gcarrillo Exp $ */

function _moduleContent(&$smarty, $module_name)
{
    include_once "libs/paloSantoGrid.class.php";
    include_once "libs/paloSantoDB.class.php";
    include_once "libs/paloSantoForm.class.php";
    include_once "libs/paloSantoConfig.class.php";
    include_once "libs/paloSantoCDR.class.php";
    require_once "libs/misc.lib.php";
    include_once "libs/paloSantoRate.class.php";
    include_once "libs/paloSantoTrunk.class.php";
    include_once "libs/paloSantoGraphImage.lib.php";

    //include module files
    include_once "modules/$module_name/configs/default.conf.php";

    load_language_module($module_name);

    //global variables
    global $arrConf;
    global $arrConfModule;
    $arrConf = array_merge($arrConf,$arrConfModule);

    //folder path for custom templates
    $base_dir=dirname($_SERVER['SCRIPT_FILENAME']);
    $templates_dir=(isset($arrConf['templates_dir']))?$arrConf['templates_dir']:'themes';
    $local_templates_dir="$base_dir/modules/$module_name/".$templates_dir.'/'.$arrConf['theme'];


    $MAX_DAYS=60;

    $arrData = array();
    $smarty->assign("menu","dest_distribution");

    $smarty->assign("Filter",_tr('Filter'));




    $arrFormElements = array("date_start"  => array("LABEL"                  => _tr("Start Date"),
                                                        "REQUIRED"               => "yes",
                                                        "INPUT_TYPE"             => "DATE",
                                                        "INPUT_EXTRA_PARAM"      => "",
                                                        "VALIDATION_TYPE"        => "ereg",
                                                        "VALIDATION_EXTRA_PARAM" => "^[[:digit:]]{1,2}[[:space:]]+[[:alnum:]]{3}[[:space:]]+[[:digit:]]{4}$"),
                                 "date_end"    => array("LABEL"                  => _tr("End Date"),
                                                        "REQUIRED"               => "yes",
                                                        "INPUT_TYPE"             => "DATE",
                                                        "INPUT_EXTRA_PARAM"      => "",
                                                        "VALIDATION_TYPE"        => "ereg",
                                                        "VALIDATION_EXTRA_PARAM" => "^[[:digit:]]{1,2}[[:space:]]+[[:alnum:]]{3}[[:space:]]+[[:digit:]]{4}$"),
                                  "criteria"  => array("LABEL"                  => _tr("Criteria"),
                                                        "REQUIRED"               => "yes",
                                                        "INPUT_TYPE"             => "SELECT",
                                                        "INPUT_EXTRA_PARAM"      => array(
                                                                 "minutes"         => _tr("Distribution by Time"),
                                                                                    "num_calls"         => _tr("Distribution by Number of Calls"),
                                                                                    "charge"     => _tr("Distribution by Cost")),
                                                        "VALIDATION_TYPE"        => "text",
                                                        "VALIDATION_EXTRA_PARAM" => ""),
                                 );

    $oFilterForm = new paloForm($smarty, $arrFormElements);

        // Por omision las fechas toman el sgte. valor (la fecha de hoy)
    $date_start = date("Y-m-d") . " 00:00:00";
    $date_end   = date("Y-m-d") . " 23:59:59";
    $value_criteria ="minutes";


    if(isset($_POST['filter'])) {
        if($oFilterForm->validateForm($_POST)) {
                // Exito, puedo procesar los datos ahora.
            $date_start = translateDate($_POST['date_start']) . " 00:00:00";
            $date_end   = translateDate($_POST['date_end']) . " 23:59:59";
        //valido que no exista diferencia mayor de 31 dias entre las fechas
            $inicio=strtotime($date_start);
            $fin=strtotime($date_end);
            $num_dias=($fin-$inicio)/86400;
            if ($num_dias>$MAX_DAYS){
                $_POST['date_start']=date("d M Y");
                $_POST['date_end']=date("d M Y");
                $date_start = date("Y-m-d"). " 00:00:00";
                $date_end   = date("Y-m-d"). " 23:59:59";
                $smarty->assign("mb_title", _tr("Validation Error"));
                $smarty->assign("mb_message", ""._tr('Date Range spans maximum number of days').":$MAX_DAYS");
            }
            $value_criteria = $_POST['criteria'];
            $arrFilterExtraVars = array("date_start" => $_POST['date_start'], "date_end" => $_POST['date_end'],"criteria"=>$_POST['criteria']);
        } else {
                // Error
            $smarty->assign("mb_title", _tr("Validation Error"));
            $arrErrores=$oFilterForm->arrErroresValidacion;
            $strErrorMsg = "<b>"._tr('The following fields contain errors').":</b><br>";
            foreach($arrErrores as $k=>$v) {
                    $strErrorMsg .= "$k, ";
            }
            $strErrorMsg .= "";
            $smarty->assign("mb_message", $strErrorMsg);
        }
        $htmlFilter = $contenidoModulo=$oFilterForm->fetchForm("$local_templates_dir/dest_dist_filter.tpl", "", $_POST);

    } else if(isset($_GET['date_start']) && isset($_GET['date_end'])) {
        //valido que no exista diferencia mayor de 31 dias entre las fechas
        $date_start = translateDate($_GET['date_start']) . " 00:00:00";
        $date_end   = translateDate($_GET['date_end']) . " 23:59:59";

        $inicio=strtotime($date_start);
        $fin=strtotime($date_end);
        $num_dias=($fin-$inicio)/86400;
        if ($num_dias>$MAX_DAYS){
            $_GET['date_start']=date("d M Y");
            $_GET['date_end']=date("d M Y");
            $date_start = date("Y-m-d"). " 00:00:00";
            $date_end   = date("Y-m-d"). " 23:59:59";
            $smarty->assign("mb_title", _tr("Validation Error"));
            $smarty->assign("mb_message", ""._tr('Date Range spans maximum number of days').":$MAX_DAYS");
        }

        $value_criteria = $_GET['criteria'];
        $arrFilterExtraVars = array("date_start" => $_GET['date_start'], "date_end" => $_GET['date_end'],"criteria"=>$_GET['criteria']);
        $htmlFilter = $contenidoModulo=$oFilterForm->fetchForm("$local_templates_dir/dest_dist_filter.tpl", "", $_GET);
    } else {
        $date_start = date("Y-m-d"). " 00:00:00";
        $date_end   = date("Y-m-d"). " 23:59:59";
        $htmlFilter = $contenidoModulo=$oFilterForm->fetchForm("$local_templates_dir/dest_dist_filter.tpl", "",
        array('date_start' => date("d M Y"), 'date_end' => date("d M Y"), 'criteria'=>'minutes'));
    }

    if (isset($_GET['action']) && $_GET['action'] == 'image') {
        ejecutarGrafico($value_criteria, $date_start, $date_end);
        return '';
    }

//obtener los datos a mostrar



    $type_graph=$value_criteria;

  //consulto cuales son los trunks de salida

    $data_graph = leerDatosGrafico($type_graph, $date_start, $date_end);
    $title_sumary = $data_graph['title_sumary'];

    //contruir la tabla de sumario
    $smarty->assign('URL_GRAPHIC', construirURL(array(
        'module'    =>  $module_name,
        'rawmode'   =>  'yes',
        'action'    =>  'image',
        'criteria'  =>  $value_criteria,
        'date_start'=>  date('d M Y', strtotime($date_start)),
        'date_end'  =>  date('d M Y', strtotime($date_end)),
    )));

    if (count($data_graph["values"])>0){
         $mostrarSumario=TRUE;
        $total_valores=array_sum($data_graph["values"]);
        $resultados=$data_graph["values"];
        foreach ($resultados as $pos => $valor){
             $results[]=array($data_graph['legend'][$pos],
                              number_format($valor,2),
                              number_format(($valor/$total_valores)*100,2)
                             );
        }
        if (count($results)>1)
        $results[]=array("<b>Total<b>",
                              "<b>".number_format($total_valores,2)."<b>",
                              "<b>".number_format(100,2)."<b>"
                             );

        $smarty->assign("Rate_Name", _tr("Rate Name"));
        $smarty->assign("Title_Criteria", $title_sumary);
        $smarty->assign("results", $results);
    }else
        $mostrarSumario=FALSE;
    $smarty->assign("mostrarSumario", $mostrarSumario);
    $smarty->assign("contentFilter", $htmlFilter);
    $smarty->assign("title", _tr('Destination Distribution'));
    $smarty->assign("icon","images/bardoc.png");
    return $smarty->fetch("file:$local_templates_dir/dest_distribution.tpl");
}

function leerDatosGrafico($type_graph, $date_start, $date_end)
{
    global $arrConf;
    global $arrConfModule;

    $MAX_SLICES=10;

    $pConfig = new paloConfig("/etc", "amportal.conf", "=", "[[:space:]]*=[[:space:]]*");
    $arrConfig = $pConfig->leer_configuracion(false);

    $pDBSQLite = new paloDB($arrConfModule['dsn_conn_database_2']);
    if(!empty($pDBSQLite->errMsg)) {
        echo ""._tr('ERROR').": $pDBSQLite->errMsg <br>";
    }
    $pRate = new paloRate($pDBSQLite);
    if(!empty($pRate->errMsg)) {
        echo ""._tr('ERROR').": $pRate->errMsg <br>";
    }

    $pDBSet = new paloDB($arrConf['elastix_dsn']['settings']);
    $pDBTrunk = new paloDB($arrConfModule['dsn_conn_database_1']);
    $oTrunk    = new paloTrunk($pDBTrunk);
    $grupos = NULL;
    $troncales = $oTrunk->getExtendedTrunksBill($grupos, $arrConfig['ASTETCDIR']['valor'].'/chan_dahdi.conf');//ej array("DAHDI/1","DAHDI/2");

    $dsn     = $arrConfig['AMPDBENGINE']['valor'] . "://" . $arrConfig['AMPDBUSER']['valor'] . ":" . $arrConfig['AMPDBPASS']['valor'] . "@" .
               $arrConfig['AMPDBHOST']['valor'] . "/asteriskcdrdb";
    $pDB     = new paloDB($dsn);
    $oCDR    = new paloSantoCDR($pDB);
    $arrCDR  = $oCDR->obtenerCDRs("", 0, $date_start,$date_end, "", "","ANSWERED","outgoing",$troncales);

    $total =$arrCDR['NumRecords'][0];
    $num_calls=array();
    $minutos=array();
    $val_charge=array();
    $nombre_rate=array();
    $title_sumary = NULL;

    if ($total>0){
        foreach($arrCDR['Data'] as $cdr) {
            if (preg_match("/^DAHDI/([[:digit:]]+)/i",$cdr[4],$regs3)) $trunk='DAHDI/g'.$grupos[$regs3[1]];
            else $trunk=str_replace(strstr($cdr[4],'-'),'',$cdr[4]);
        //tengo que buscar la tarifa para el numero de telefono
            $numero=$cdr[2];
            $tarifa=array();
            $rate_name="";
            $charge=0;
            $bExito=$pRate->buscarTarifa($numero,$tarifa,$trunk);
            if (!count($tarifa)>0 && ($bExito)) $bExito=$pRate->buscarTarifa($numero,$tarifa,'None');
            if (!$bExito)
            {
                echo ""._tr('ERROR').": $pRate->errMsg <br>";
            }else
            {

             //verificar si tiene tarifa
                if (count($tarifa)>0)
                {
                    foreach ($tarifa as $id_tarifa=>$datos_tarifa)
                    {
                        $rate_name=$datos_tarifa['name'];
                        $id_rate=$datos_tarifa['id'];
                        $charge=(($cdr[8]/60)*$datos_tarifa['rate'])+$datos_tarifa['offset'];
                    }
                }else
                {
                    $rate_name=_tr("default");
                    $id_rate=0;
                //no tiene tarifa buscar tarifa por omision
                //por ahora para probar $1 el minuto
                    $rate=get_key_settings($pDBSet,"default_rate");
                    $rate_offset=get_key_settings($pDBSet,"default_rate_offset");
                    $charge=(($cdr[8]/60)*$rate)+$rate_offset;
                }
                $nombre_rate[$id_rate]=$rate_name;
                if (!isset($minutos[$id_rate])) $minutos[$id_rate]=0;
                if (!isset($num_calls[$id_rate])) $num_calls[$id_rate]=0;
                if (!isset($val_charge[$id_rate])) $val_charge[$id_rate]=0;
                $minutos[$id_rate]+=($cdr[8]/60);
                $num_calls[$id_rate]++;
                $val_charge[$id_rate]+=$charge;
            }
        }

    //ordenar los valores a mostrar
        arsort($num_calls);
        arsort($minutos);
        arsort($val_charge);

    //verificar que los valores no excedan el numero de slices del pie
//numero de llamadas

        if (count($num_calls)>$MAX_SLICES){
            $i=1;
            foreach($num_calls as $id_rate=>$valor)
            {

                if ($i>$MAX_SLICES-1){
                    if (!isset($valores_num_calls['otros'])) $valores_num_calls['otros']=0;
                    $valores_num_calls['otros']+=$valor;
                }
                else
                    $valores_num_calls[$id_rate]=$valor;
                $i++;
            }
        }else
            $valores_num_calls=$num_calls;

    //minutos
        if (count($minutos)>$MAX_SLICES){
            $i=1;
            foreach($minutos as $id_rate=>$valor)
            {
                if ($i>$MAX_SLICES-1){
                    if (!isset($valores_minutos['otros'])) $valores_minutos['otros']=0;
                    $valores_minutos['otros']+=$valor;
                }
                else
                    $valores_minutos[$id_rate]=$valor;
                $i++;
            }
        }else
            $valores_minutos=$minutos;


    //charge
        if (count($val_charge)>$MAX_SLICES){
            $i=1;
            foreach($val_charge as $id_rate=>$valor)
            {
                if ($i>$MAX_SLICES-1){
                    if (!isset($valores_charge['otros'])) $valores_charge['otros']=0;
                    $valores_charge['otros']+=$valor;
                }
                else
                    $valores_charge[$id_rate]=$valor;
                $i++;
            }
        }else
            $valores_charge=$val_charge;

        if ($type_graph=="minutes"){
            $titulo=_tr("Distribution by Time");
            $valores_grafico=$valores_minutos;
            $title_sumary=_tr("Minutes");
        }elseif ($type_graph=="charge"){
            $titulo=_tr("Distribution by Cost");
            $valores_grafico=$valores_charge;
            $title_sumary=_tr("Cost");
        }
        else{
            $titulo=_tr("Distribution by Number of Calls");
            $valores_grafico=$valores_num_calls;
            $title_sumary=_tr("Number of Calls");
        }

        //nombres de tarifas para leyenda
        foreach ($valores_grafico as $id=>$valor)
        {
            $nombres_tarifas[]=isset($nombre_rate[$id])?$nombre_rate[$id]:_tr("others");
        }

        $data=array_values($valores_grafico);
   }else
   {
        if ($type_graph=="minutes"){
            $titulo=_tr("Distribution by Time");
        }elseif ($type_graph=="charge"){
            $titulo=_tr("Distribution by Cost");
        }
        else{
            $titulo=_tr("Distribution by Number of Calls");
        }
        $nombres_tarifas=$data=array();
   }
//formar la estructura a pasar al pie

   $data_graph=array(
     "values"=>$data,
     "legend"=>$nombres_tarifas,
     "title"=>$titulo,
     "title_sumary"=>$title_sumary,
     );
    return $data_graph;
}

function ejecutarGrafico($value_criteria, $date_start, $date_end)
{
    $data_graph = leerDatosGrafico($value_criteria, $date_start, $date_end);

    if (count($data_graph["values"])>0){
    // Create the Pie Graph.
        $graph = new PieGraph(630, 220,"auto");
        $graph->SetMarginColor('#fafafa');
        $graph->SetFrame(true,'#999999');

        $graph->legend->SetFillColor("#fafafa");
        $graph->legend->SetColor("#444444", "#999999");
        $graph->legend->SetShadow('gray@0.6',4);

    // Set A title for the plot
        $graph->title->Set(utf8_decode($data_graph["title"]));
        $graph->title->SetColor("#444444");
        $graph->legend->Pos(0.1,0.2);

    // Create 3D pie plot
        $p1 = new PiePlot3d($data_graph["values"]);
        $p1->SetCenter(0.4);
        $p1->SetSize(100);

    // Adjust projection angle
        $p1->SetAngle(60);

    // Adjsut angle for first slice
        $p1->SetStartAngle(45);

    // Display the slice values
        $p1->value->SetColor("black");

    // Add colored edges to the 3D pie
    // NOTE: You can't have exploded slices with edges!
        $p1->SetEdge("black");

        $p1->SetLegends($data_graph["legend"]);
        $graph->Add($p1);
        $graph->Stroke();
    }else{
	$graph = new CanvasGraph(630,220,"auto");
	$title = new Text(utf8_decode($data_graph["title"]));
	$title->ParagraphAlign('center');
	$title->SetFont(FF_FONT2,FS_BOLD);
	$title->SetMargin(3);
	$title->SetAlign('center');
	$title->Center(0,630,110);
	$graph->AddText($title);

	$t1 = new Text(utf8_decode(_tr("No records found")));
	$t1->SetBox("white","black",true);
	$t1->ParagraphAlign("center");
	$t1->SetColor("black");

	$graph->AddText($t1);
	$graph->img->SetColor('navy');
	$graph->img->SetTextAlign('center','bottom');
	$graph->img->Rectangle(0,0,629,219);
	$graph->Stroke();
	/*
       //no hay datos - por ahora muestro una imagen en blanco con mensaje no records found
        header('Content-type: image/png');
        $titulo=utf8_decode($data_graph["title"]);
        $im = imagecreate(630, 220);
        $background_color = imagecolorallocate($im, 255, 255, 255);
        $text_color = imagecolorallocate($im, 233, 14, 91);
        imagestring($im, 10, 5, 5, $titulo. "  -  No records found", $text_color);
        imagepng($im);
        imagedestroy($im);*/
    }
}

?>
