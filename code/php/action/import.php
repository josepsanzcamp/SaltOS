<?php
/*
 ____        _ _    ___  ____
/ ___|  __ _| | |_ / _ \/ ___|
\___ \ / _` | | __| | | \___ \
 ___) | (_| | | |_| |_| |___) |
|____/ \__,_|_|\__|\___/|____/

SaltOS: Framework to develop Rich Internet Applications
Copyright (C) 2007-2014 by Josep Sanz Campderrós
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
if(!check_user($page,"import")) action_denied();
if($page=="importaciones") {
	include("php/import.php");
	$id_importacion=abs(getParam("id"));
	$query="SELECT * FROM tbl_aplicaciones WHERE id=(SELECT id_aplicacion FROM tbl_importaciones WHERE id='${id_importacion}')";
	$row=execute_query($query);
	if($row===null) show_php_error(array("phperror"=>"Unknown aplicacion (id_importacion='${id_importacion}')"));
	$nodes=array();
	if($row["node0"]) $nodes[]=$row["node0"];
	if($row["node1"]) $nodes[]=$row["node1"];
	if(!count($nodes)) $nodes=null;
	$array=__import_importfile($id_importacion,$nodes);
	$array=__import_tree2array($array);
	$select=explode(",",implode(",",$nodes));
	$head=array_keys($array[0]);
	$buffer=__import_make_table(array("auto"=>true,"select"=>$select,"head"=>$head,"data"=>$array,"limit"=>20));
	output_buffer($buffer,"text/html");
	die();
}
if($page=="datacfg") {
	include("php/import.php");
	// GET THE FILE
	$ok=0;
	foreach($_FILES as $file) {
		if(isset($file["tmp_name"]) && $file["tmp_name"]!="" && file_exists($file["tmp_name"])) {
			$ok=1;
			break;
		} elseif(isset($file["name"]) && $file["name"]!="") {
			session_error(LANG("fileuploaderror").$file["name"]);
			javascript_history(-1);
			die();
		}
	}
	if(!$ok) {
		session_error(LANG("filenotfound","datacfg"));
		javascript_history(-1);
		die();
	}
	// DISABLE DB CACHE
	$oldcache=set_use_cache("false");
	// OPEN FILE
	$fp=gzopen($file["tmp_name"],"r");
	// IMPORT QUERYES
	$limit=1000000; // 1MB aprox.
	$data=gzread($fp,$limit*2);
	$len=strlen($data);
	$pos=0;
	while($pos<$len) {
		$count=__import_find_query($data,$pos);
		if($count) {
			$query=substr($data,$pos,$count);
			capture_next_error();
			db_query($query);
			$error=get_clear_error();
			if($error!="") {
				gzclose($fp);
				session_error(LANG("fileimporterror","datacfg").$file["name"]);
				javascript_history(-1);
				die();
			}
			$pos=$pos+$count+1;
		}
		if(($len-$pos<$limit || !$count) && $limit) {
			$temp=gzread($fp,$limit);
			if(strlen($temp)<$limit) $limit=0;
			$data=substr($data,$pos).$temp;
			unset($temp);
			$pos=0;
			$len=strlen($data);
		} elseif(!$count && !$limit) {
			break;
		}
	}
	gzclose($fp);
	// RESTORE DB CACHE
	set_use_cache($oldcache);
	// RETURN
	session_alert(LANG("filefoundok","datacfg").$file["name"]);
	javascript_history(-1);
	die();
}
?>