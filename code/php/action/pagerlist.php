<?php
/*
 ____        _ _    ___  ____
/ ___|  __ _| | |_ / _ \/ ___|
\___ \ / _` | | __| | | \___ \
 ___) | (_| | | |_| |_| |___) |
|____/ \__,_|_|\__|\___/|____/

SaltOS: Framework to develop Rich Internet Applications
Copyright (C) 2012 by Josep Sanz Campderrós
More information in http://www.saltos.net or info@saltos.net

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
if(!check_user()) action_denied();
if(getParam("action")=="pagerlist") {
	include("php/listsim.php");
	// LOAD THE APPLICATION FILE
	if(!file_exists("xml/$page.xml")) action_denied();
	$config=xml2array("xml/$page.xml");
	if(!isset($config["list"])) action_denied();
	// SAVE THE CURRENT CONTEXT
	saltos_context($page,"list");
	// RESTORE THE HISTORY DATA IF EXISTS
	$id_usuario=current_user();
	$id_aplicacion=page2id($page);
	load_history($id_usuario,$id_aplicacion);
	// MAKE THE LIST QUERY
	$config=$config["list"];
	$config=eval_attr($config);
	// GET THE NEEDED XML NODES
	$query0=$config["query"];
	$limit=$config["limit"];
	$offset=$config["offset"];
	// CHECK ORDER
	list($order,$array)=list_check_order($config["order"],$config["fields"]);
	// EXECUTE THE QUERY TO GET THE TOTAL ROWS
	$query="SELECT COUNT(*) FROM ($query0) __a__";
	$count=execute_query($query);
	// RESTORE THE SAVED CONTEXT
	saltos_context();
	// PREPARE SOME VARS
	$currentpage=intval($offset/$limit)+1;
	$totalpages=intval(($count-1)/$limit)+1;
	$currentregini=min($offset+1,$count);
	$currentregend=min($offset+$limit,$count);
	// PREPARE THE RESULT
	$_RESULT=array("rows"=>array());
	// PREPARE THE LIST OF PAGES
	$minpage=$currentpage-intval($limit/2);
	$maxpage=$currentpage+intval($limit/2);
	if($minpage<1) {
		$minpage=1;
		$maxpage=1+$limit;
	} elseif($maxpage>$totalpages) {
		$minpage=$totalpages-$limit;
		$maxpage=$totalpages;
	}
	$minpage=max($minpage,1);
	$maxpage=min($maxpage,$totalpages);
	if($minpage>1) set_array($_RESULT["rows"],"row",array("label"=>1,"value"=>1));
	for($i=$minpage;$i<=$maxpage;$i++) set_array($_RESULT["rows"],"row",array("label"=>$i,"value"=>$i));
	if($maxpage<$totalpages) set_array($_RESULT["rows"],"row",array("label"=>$totalpages,"value"=>$totalpages));
	// PREPARE VALUE VAR
	$_RESULT["value"]=$currentpage;
	// PREPARE PAGINATION VARS
	$_RESULT["first"]=($currentpage>1);
	$_RESULT["previous"]=($currentpage>1);
	$_RESULT["next"]=($currentpage<$totalpages);
	$_RESULT["last"]=($currentpage<$totalpages);
	// PREPARE LAST STRING
	$_RESULT["info"]=LANG("paginaspc").$currentpage.LANG("spcdespc").$totalpages." (".LANG("regsfrom")." ".$currentregini.LANG("spcalspc").$currentregend.LANG("spcdespc").$count.").";
	// PREPARE THE OUTPUT
	$buffer="<?xml version='1.0' encoding='UTF-8' ?>\n";
	$buffer.=array2xml($_RESULT);
	// CONTINUE
	output_buffer($buffer,"text/xml");
}
?>