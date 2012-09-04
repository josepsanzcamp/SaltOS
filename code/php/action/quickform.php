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
if(getParam("action")=="quickform") {
	include_once("php/listsim.php");
	$pagesim=$page;
	if(getParam("id_folder")) {
		$pagesim="folders";
		lastfolder(getParam("id_folder"));
	}
	if(getParam("is_fichero")) {
		$pagesim="ficheros";
	}
	if(getParam("is_buscador")) {
		$pagesim="buscador";
	}
	$ids=list_simulator($pagesim,"array");
	if(is_null($ids)) action_denied();
	// FIND IF ID EXISTS IN THE LIST
	$id_abs=abs($id);
	$index=-1;
	foreach($ids as $key=>$val) {
		$action_id=intval($val);
		if($action_id==$id_abs) {
			$index=$key;
			break;
		}
	}
	if($index<0) {
		foreach($ids as $key=>$val) {
			$temp=explode("_",$val);
			if(count($temp)!=3) break;
			$action_page=$temp[1];
			$action_id=intval($temp[2]);
			if($action_page==$page && $action_id==$id_abs) {
				$index=$key;
				break;
			}
		}
	}
	if($index<0) {
		$ids=array($id_abs);
		$index=0;
	}
	$count=count($ids);
	// PREPARE THE LIST OF REGISTERS
	$minindex=$index-intval($limit/2);
	$maxindex=$index+intval($limit/2);
	if($minindex<0) {
		$minindex=0;
		$maxindex=$limit;
	} elseif($maxindex>=$count) {
		$minindex=$count-$limit-1;
		$maxindex=$count-1;
	}
	$minindex=max($minindex,0);
	$maxindex=min($maxindex,$count-1);
	// RETRIEVE THE ACTION_TITLE
	$ids2=array();
	if($minindex>0) $ids2[]=$ids[0];
	for($i=$minindex;$i<=$maxindex;$i++) $ids2[]=$ids[$i];
	if($maxindex<$count-1) $ids2[]=$ids[$count-1];
	$ids3=is_numeric($ids2[0])?$ids2:explode("|","'".implode("'|'",$ids2)."'");
	$titles=list_simulator($pagesim,$ids3);
	// PREPARE THE RESULT
	$count2=1;
	$_RESULT=array("rows"=>array());
	foreach($ids2 as $key=>$value) {
		$label=isset($titles[$key])?$titles[$key]:$value;
		$row=array("label"=>$label,"value"=>$value);
		$row["__ROW_NUMBER__"]=$count2++;
		set_array($_RESULT["rows"],"row",$row);
	}
	// PREPARE THE VALUE
	$_RESULT["value"]=$ids[$index];
	// PREPARE THE DISABLED BUTTONS
	$_RESULT["first"]=($index>0);
	$_RESULT["previous"]=($index>0);
	$_RESULT["next"]=($index<$count-1);
	$_RESULT["last"]=($index<$count-1);
	// PREPARE THE OUTPUT
	$buffer="<?xml version='1.0' encoding='UTF-8' ?>\n";
	$buffer.=array2xml($_RESULT);
	// TRICK TO MARK AS READED THE EMAILS AND FEEDS
	if($page=="correo") {
		// MARCAR CORREO COMO LEIDO SI ES EL PROPIETARIO
		$query="UPDATE tbl_correo SET state_new='0' WHERE id=(SELECT id_registro FROM tbl_registros_i WHERE id_aplicacion='".page2id("correo")."' AND id_registro='$id_abs' AND id_usuario='".current_user()."')";
		db_query($query);
	}
	if($page=="feeds") {
		// MARCAR FEED COMO LEIDO SI ES EL PROPIETARIO
		$query="UPDATE tbl_feeds SET state_new='0' WHERE id=(SELECT id_registro FROM tbl_registros_i WHERE id_aplicacion='".page2id("feeds")."' AND id_registro='$id_abs' AND id_usuario='".current_user()."')";
		db_query($query);
	}
	// CONTINUE
	output_buffer($buffer,"text/xml");
}
?>