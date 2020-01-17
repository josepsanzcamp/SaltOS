<?php
/*
 ____        _ _    ___  ____
/ ___|  __ _| | |_ / _ \/ ___|
\___ \ / _` | | __| | | \___ \
 ___) | (_| | | |_| |_| |___) |
|____/ \__,_|_|\__|\___/|____/

SaltOS: Framework to develop Rich Internet Applications
Copyright (C) 2007-2020 by Josep Sanz Campderrós
More information in http://www.saltos.org or info@saltos.org

This program is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/

// TO DO COMPATIBLE WITH OLD LINKS
//~ if(getServer("HTTP_REFERER")=="" && getServer("QUERY_STRING")!="") {
	//~ $url=get_base()."#".getServer("QUERY_STRING");
	//~ _javascript_location($url);
	//~ die();
//~ }

// GET ACTIONS
$action=getParam("action");
if(file_exists("php/action/_${action}.php")) include("php/action/_${action}.php");
if(file_exists("php/action/${action}.php")) include("php/action/${action}.php");

// CONTINUE
$array=eval_attr(xml2array("xml/bootstrap.xml"));

// SOME CHECKS
if(!is_array($array["metas"])) $array["metas"]=array();
if(!is_array($array["css"])) $array["css"]=array();
if(!is_array($array["js"])) $array["css"]=array();

// GET DATA
$template=trim($array["template"]);
$rev=getDefault("info/revision");

// COMPUTE METAS
$metas=array();
foreach($array["metas"] as $key=>$val) {
	$key=limpiar_key($key);
	if($key=="meta") $metas[]="<meta ".$val.">";
	if($key=="icon") $metas[]="<link href='${val}?r=${rev}' rel='icon'>";
	if($key=="title") $metas[]="<title>${val}</title>";
}
$metas=implode("\n",$metas);
$template=str_replace("__METAS__",$metas,$template);

// COMPUTE CSS
$css=array();
foreach($array["css"] as $key=>$val) {
	$key=limpiar_key($key);
	if($key=="include") $css[]="<link href='${val}?r=${rev}' rel='stylesheet'>";
	if($key=="inline") $css[]="<style>${val}</style>";
}
$css=implode("\n",$css);
$template=str_replace("__CSS__",$css,$template);

// COMPUTE JS
$js=array();
foreach($array["js"] as $key=>$val) {
	$key=limpiar_key($key);
	if($key=="include") $js[]="<script src='${val}?r=${rev}'></script>";
	if($key=="inline") $js[]="<script>${val}</script>";
}
$js=implode("\n",$js);
$template=str_replace("__JS__",$js,$template);

// OUTPUT TEMPLATE
output_handler(array(
	"data"=>$template,
	"type"=>"text/html",
	"cache"=>false
));
?>
