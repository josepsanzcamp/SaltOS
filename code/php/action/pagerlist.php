<?php
/*
 ____        _ _    ___  ____
/ ___|  __ _| | |_ / _ \/ ___|
\___ \ / _` | | __| | | \___ \
 ___) | (_| | | |_| |_| |___) |
|____/ \__,_|_|\__|\___/|____/

SaltOS: Framework to develop Rich Internet Applications
Copyright (C) 2007-2019 by Josep Sanz Campderrós
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
if(!check_user()) action_denied();
if(getParam("action")=="pagerlist") {
	require_once("php/listsim.php");
	$result=list_simulator($page,"count");
	if($result===null) action_denied();
	// PREPARE SOME VARS
	$currentpage=intval($result["offset"]/$result["limit"])+1;
	$totalpages=intval(($result["count"]-1)/$result["limit"])+1;
	$currentregini=min($result["offset"]+1,$result["count"]);
	$currentregend=min($result["offset"]+$result["limit"],$result["count"]);
	// PREPARE THE RESULT
	$_RESULT=array("rows"=>array());
	// PREPARE THE LIST OF PAGES
	$minpage=$currentpage-intval($result["limit"]/2);
	$maxpage=$currentpage+intval($result["limit"]/2);
	if($minpage<1) {
		$minpage=1;
		$maxpage=1+$result["limit"];
	} elseif($maxpage>$totalpages) {
		$minpage=$totalpages-$result["limit"];
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
	$_RESULT["info"]=LANG("paginaspc").$currentpage.LANG("spcdespc").$totalpages." (".LANG("regsfrom")." ".$currentregini.LANG("spcalspc").$currentregend.LANG("spcdespc").$result["count"].").";
	// PREPARE THE OUTPUT
	$_RESULT["rows"]=array_values($_RESULT["rows"]);
	$buffer=json_encode($_RESULT);
	// CONTINUE
	output_handler(array(
		"data"=>$buffer,
		"type"=>"application/json",
		"cache"=>false
	));
}
?>