<?php
  /* vim: set expandtab tabstop=4 softtabstop=4 shiftwidth=4:
  Codificación: UTF-8
  +----------------------------------------------------------------------+
  | Issabel version 1.1-4                                               |
  | http://www.issabel.org                                               |
  +----------------------------------------------------------------------+
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
  $Id: default.conf.php,v 1.1 2008-06-13 04:06:20 Alex Villacís Lasso Exp $ */

function _moduleContent(&$smarty, $module_name)
{
    //include issabel framework
    include_once "libs/paloSantoGrid.class.php";
    include_once "libs/paloSantoForm.class.php";

    //include module files
    include_once "modules/$module_name/configs/default.conf.php";
    include_once "modules/$module_name/libs/paloSantoAsteriskLogs.class.php";
    include_once "modules/$module_name/libs/LogParser_Full.class.php";

    // incluir el archivo de idioma de acuerdo al que este seleccionado
    // si el archivo de idioma no existe incluir el idioma por defecto
    
    load_language_module($module_name);

    //global variables
    global $arrConf;
    global $arrConfModule;
    $arrConf = array_merge($arrConf,$arrConfModule);

    //folder path for custom templates
    $base_dir=dirname($_SERVER['SCRIPT_FILENAME']);
    $templates_dir=(isset($arrConf['templates_dir']))?$arrConf['templates_dir']:'themes';
    $local_templates_dir="$base_dir/modules/$module_name/".$templates_dir.'/'.$arrConf['theme'];

    $accion = getAction();

    $content = "";
    switch($accion)
    {
        default:
            $content = report_AsteriskLogs($smarty, $module_name, $local_templates_dir);
            break;
    }

    return $content;
}

function report_AsteriskLogs($smarty, $module_name, $local_templates_dir)
{
    $arrFormElements = array(
            "filter"            => array(   "LABEL"                  => _tr("Date"),
                                            "REQUIRED"               => "no",
                                            "INPUT_TYPE"             => "SELECT",
                                            "INPUT_EXTRA_PARAM"      => NULL,
                                            "VALIDATION_TYPE"        => "ereg",
                                            "VALIDATION_EXTRA_PARAM" => '^[[:digit:]]{4}-[[:digit:]]{2}-[[:digit:]]{2}$'),

            "busqueda"          => array(
                                            "LABEL"                  => _tr('Search string'),
                                            "REQUIRED"               => "no",
                                            "INPUT_TYPE"             => "TEXT",
                                            'VALIDATION_TYPE'           =>  'text',
                                            'INPUT_EXTRA_PARAM'         =>  '',
                                            'VALIDATION_EXTRA_PARAM'    =>  '',
            ),
            "offset"            => array(   "LABEL"                  => _tr("offset"),
                                            "REQUIRED"               => "no",
                                            "INPUT_TYPE"             => "HIDDEN",
                                            "INPUT_EXTRA_PARAM"      => NULL,
                                            "VALIDATION_TYPE"        => "ereg",
                                            "VALIDATION_EXTRA_PARAM" => '^[[:digit:]]+$'),

            /* Variables requeridas para seguir la pista de la última búsqueda */
            "ultima_busqueda"          => array(
                                            "LABEL"                  => _tr('Search string'),
                                            "REQUIRED"               => "no",
                                            "INPUT_TYPE"             => "HIDDEN",
                                            'VALIDATION_TYPE'           =>  'text',
                                            'INPUT_EXTRA_PARAM'         =>  '',
                                            'VALIDATION_EXTRA_PARAM'    =>  '',),
            "ultimo_offset"            => array(   "LABEL"                  => _tr("offset"),
                                            "REQUIRED"               => "no",
                                            "INPUT_TYPE"             => "HIDDEN",
                                            "INPUT_EXTRA_PARAM"      => NULL,
                                            "VALIDATION_TYPE"        => "ereg",
                                            "VALIDATION_EXTRA_PARAM" => '^[[:digit:]]+$'),
                                );

    $smarty->assign("SHOW", _tr("Show"));
    $smarty->assign("SEARCHNEXT", _tr('Search'));
    $field_pattern = getParameter("filter");
    $busqueda = getParameter('busqueda');
    if (is_null($busqueda) || trim($busqueda) == '') $busqueda = '';

    /* Última búsqueda, si existe */
    $sUltimaBusqueda = getParameter('ultima_busqueda');
    $iUltimoOffset = getParameter('ultimo_offset');
    if (is_null($sUltimaBusqueda) || $sUltimaBusqueda == '' ||
        is_null($iUltimoOffset) || !preg_match('/^[[:digit:]]+$/', $iUltimoOffset)) {
        $sUltimaBusqueda = NULL;
        $iUltimoOffset = NULL;
    }

    $pAsteriskLogs = new paloSantoAsteriskLogs();

    $listaFechas = $pAsteriskLogs->astLog->listarFechas();

    if (!preg_match("/".$arrFormElements['filter']['VALIDATION_EXTRA_PARAM']."/", $field_pattern))
        $field_pattern = $listaFechas[count($listaFechas) - 1];
    $_POST['filter'] = $field_pattern;
    $total_datos = $pAsteriskLogs->ObtainNumAsteriskLogs($field_pattern);
    $comboFechas = array();
    foreach ($listaFechas as $f) $comboFechas[$f] = $f;

    $arrFormElements['filter']['INPUT_EXTRA_PARAM'] = $comboFechas;
    $oFilterForm = new paloForm($smarty, $arrFormElements);

    if ($busqueda != '') $_POST['busqueda'] = $busqueda;

    $oGrid  = new paloSantoGrid($smarty);

    $iNumLineasPorPagina = 40;
    $iEstimadoBytesPagina = $iNumLineasPorPagina * 128;

    $iOffsetVerdadero = getParameter('offset');
    if (is_null($iOffsetVerdadero) || !preg_match("/^[[:digit:]]+$/", $iOffsetVerdadero)) {
        $iOffsetVerdadero = 0;        
    }
    $totalBytes = $total_datos[0];
    if ($iOffsetVerdadero >= $totalBytes) $iOffsetVerdadero = 0;

    if (isset($_GET['filter']) && isset($_POST['filter']) && $_GET['filter'] != $_POST['filter'])
        $iOffsetVerdadero = 0;

    /* Para ubicarse en la página, se obtiene la región 5 páginas estimadas hacia delante y 
       5 páginas estimadas hacia atrás desde el offset indicado.
     */
    $inicioRango = $iOffsetVerdadero - 5 * $iEstimadoBytesPagina;
    if ($inicioRango < 0) $inicioRango = 0;
    $arrResult =$pAsteriskLogs->ObtainAsteriskLogs(10 * $iEstimadoBytesPagina, $inicioRango, $field_pattern);
    
    /* Localizar la línea del offset verdadero, así como los offsets de las páginas previa y siguiente */
    for ($iPos = 0; $iPos < count($arrResult); $iPos++) {
        if ($arrResult[$iPos]['offset'] >= $iOffsetVerdadero) break;
    }
    $iPosPrevio = $iPos - $iNumLineasPorPagina;
    $iPosSiguiente = $iPos + $iNumLineasPorPagina;
    if ($iPosPrevio < 0) $iPosPrevio = 0;
    if ($iPosSiguiente > count($arrResult) - 1) $iPosSiguiente = count($arrResult) - 1;
    $iOffsetPrevio = $arrResult[$iPosPrevio]['offset'];
    $iOffsetSiguiente = $arrResult[$iPosSiguiente]['offset'];

    $limit = $iNumLineasPorPagina;
    $total = (int)($totalBytes / 128);


    $oGrid->setLimit($limit);
    $oGrid->setTotal($total);


    $offset = $iOffsetVerdadero;
    $nav = getParameter('nav');
    if ($nav) switch ($nav) {
    case 'start':
        $offset = 0;
        break;
    case 'end':
        /* Caso especial: se debe tomar la última sección del log */
        $inicioRango = $totalBytes - 5 * $iEstimadoBytesPagina;
        if ($inicioRango < 0) $inicioRango = 0;
        $arrResult =$pAsteriskLogs->ObtainAsteriskLogs(10 * $iEstimadoBytesPagina, $inicioRango, $field_pattern);
        if (count($arrResult) <= $iNumLineasPorPagina)
            $offset = $arrResult[0]['offset'];
        else $offset = $arrResult[count($arrResult) - $iNumLineasPorPagina]['offset'];
        break;
    case 'next':
        $offset = $iOffsetSiguiente;
        break;
    case 'previous':
        $offset = $iOffsetPrevio;
        break;
    case 'bypage':
        $numPage = ($limit==0)?0:ceil($total / $limit);

        $page  = getParameter("page");
        if(preg_match("/[0-9]+/",$page)==0)// no es un número
            $page = 1;

        if( $page > $numPage) // se está solicitando una pagina mayor a las que existen
            $page = $numPage;

        $start = ( ( ($page - 1) * $limit ) + 1 ) - $limit;

        if($start + $limit <= 1){
            $offset = 0;
            breaK;
        }

        $inicioBusqueda = ($page * $iEstimadoBytesPagina) - ($iEstimadoBytesPagina);
        $arrResult =$pAsteriskLogs->ObtainAsteriskLogs(10 * $iEstimadoBytesPagina, $inicioBusqueda, $field_pattern);
                $offset = $arrResult[0]['offset'];

        $oGrid->setOffsetValue($offset);
        break;
    }


    // Buscar la cadena de texto indicada, y modificar offset si se encuentra
    if (isset($_POST['searchnext'])  && $busqueda != '') {
        $pAsteriskLogs->astLog->posicionarMensaje($field_pattern, $offset);
        $posBusqueda = $pAsteriskLogs->astLog->buscarTextoMensaje($busqueda);
        if (!is_null($posBusqueda)) {
            $offset = $posBusqueda[1];
            $smarty->assign('SEARCHNEXT', _tr('Search next'));
            $_POST['ultima_busqueda'] = $busqueda;
            $_POST['ultimo_offset'] = $offset;
            
            // Si el offset anterior indicado es idéntico al offset recién encontrado
            // y la cadena de búsqueda es también idéntica, se asume que se ha
            // pedido una búsqueda de la siguiente ocurrencia.
            if (!is_null($sUltimaBusqueda) && !is_null($iUltimoOffset) && 
                $offset == $iUltimoOffset && $sUltimaBusqueda == $busqueda) {
                $pAsteriskLogs->astLog->posicionarMensaje($field_pattern, $offset);
                $pAsteriskLogs->astLog->siguienteMensaje(); // Sólo para ignorar primera ocurrencia
                $posBusqueda = $pAsteriskLogs->astLog->buscarTextoMensaje($busqueda);
                if (!is_null($posBusqueda)) {
                    $offset = $posBusqueda[1];
                    $_POST['ultimo_offset'] = $offset;
                }
            }
        } else {
        }
    }

    $url = array(
        'menu'              =>  $module_name,
        'filter'            =>  $field_pattern,
        'offset'            =>  $offset,
        'busqueda'          =>  $busqueda,
        'ultima_busqueda'   =>  (isset($_POST['ultima_busqueda']) ? $_POST['ultima_busqueda'] : ''),
        'ultimo_offset'     =>  (isset($_POST['ultimo_offset']) ? $_POST['ultimo_offset'] : ''),
    );
    //Fin Paginacion

    $arrResult =$pAsteriskLogs->ObtainAsteriskLogs(10 * $iEstimadoBytesPagina, $offset, $field_pattern,
        (($busqueda != '') ? $busqueda : NULL));
    $arrResult = array_slice($arrResult, 0, $iNumLineasPorPagina);
    $posLog = $pAsteriskLogs->astLog->obtenerPosicionMensaje();
    $arrData = null;
    if(is_array($arrResult) && $totalBytes>0){
        foreach($arrResult as $key => $value){
            //$partes = preg_split("/\[/",$value['tipo']);
            $l     = $value['tipo'];
            $linea = $value['fecha'].' '.$value['tipo'].' '.$value['origen'].' '.$value['linea'];
            switch (true) {
                case strpos($l, 'INFO'):
                    $linea = '<span class="beige">' . $linea . '</span>';
                    break;
                case strpos($linea, 'WARNING'):
                    $linea = '<span class="orange">' . $linea . '</span>';
                    break;
                case strpos($linea, 'DEBUG'):
                    $linea = '<span class="green">' . $linea . '</span>';
                    break;
                case strpos($linea, 'UPDATE'):
                case strpos($linea, 'NOTICE'):
                    $linea = '<span class="cyan">' . $linea . '</span>';
                    break;
                case strpos($linea, 'FATAL'):
                case strpos($linea, 'CRITICAL'):
                case strpos($linea, 'ERROR'):
                    $linea = '<span class="red">' . $linea . '</span>';
                    break;
                default:
                    $linea = $linea;
                    break;
            }

            $arrTmp[0] = $linea;
            $arrData[] = $arrTmp;
        }
    }
    
    $_POST['offset'] = $offset;

    //$defaultEnd=end($comboFechas);
    $oGrid->addFilterControl(_tr("Filter applied: ")._tr("Date")." = ".$_POST['filter'], $_POST, array('filter' => $listaFechas[count($listaFechas) - 1]),true);
    $oGrid->addFilterControl(_tr("Filter applied: ")._tr('Search string')." = ".$busqueda, $_POST, array('busqueda' => ""));

    $htmlFilter = $oFilterForm->fetchForm("$local_templates_dir/filter.tpl", "", $_POST);
    $oGrid->showFilter(trim($htmlFilter));
    $oGrid->setTplFile("modules/$module_name/themes/default/loglist.tpl"); 

    $arrGrid = array("title"    => _tr("Asterisk Logs"),
                    "url"      => $url,
                    "icon"     => "/modules/$module_name/images/reports_asterisk_logs.png",
                    "width"    => "99%",
                    "start"    => ($totalBytes==0) ? 0 : 1 + (int)($offset / 128),
                    "end"      => (int)($offset / 128) + $iNumLineasPorPagina,
                    "total"    => (int)($totalBytes / 128),
                    "columns"  => array(0 => array("name"      => _tr('Date'),
                                                "property1" => ""),

                                        1 => array("name"      => _tr('Type'),
                                                "property1" => ""),
                                        2 => array("name"      => _tr('Source'),
                                                "property1" => ""),
                                        3 => array("name"      => _tr('Message'),
                                                "property1" => "")
                                    )
                );

    $contenidoModulo = $oGrid->fetchGrid($arrGrid, $arrData);

    /*$current_page=getParameter("page");
    print($current_page);
    $contenidoModulo .= "<script type='text/javascript'>
        var offset = ".$offset.";
        var limit = ".$limit.";
        var current_page = ".$current_page.";
                    alert(current_page);
            var start = current_page * limit;
            page = Math.floor(start / limit);
        $('#pageup').val(page);
        $('#pagedown').val(page);
    </script>";*/
    return $contenidoModulo;
}

function getAction()
{
    if(getParameter("show")) //Get parameter by POST (submit)
        return "show";
    else if(getParameter("new"))
        return "new";
    else if(getParameter("action")=="show") //Get parameter by GET (command pattern, links)
        return "show";
    else
        return "report";
}
?>
