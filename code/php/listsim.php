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
function list_simulator($newpage,$ids="string") {
	global $page;
	global $action;
	global $_LANG;
	// STORE OLD INFORMATION
	$oldpage=isset($page)?$page:null;
	$oldaction=isset($action)?$action:null;
	$oldlang=$_LANG["default"];
	// LOAD THE APPLICATION FILE
	if(!file_exists("xml/$newpage.xml")) return null;
	$config=xml2array("xml/$newpage.xml");
	if(!isset($config["list"])) return null;
	// BACKUP THE GET AND POST DATA TO RESTORE WHEN FINISH
	$_OLDGET=$_GET;
	$_GET=array();
	$_OLDPOST=$_POST;
	$_POST=array();
	// UPDATE THE PAGE
	$page=$newpage;
	setParam("page",$page);
	// UPDATE THE ACTION
	$action="list";
	setParam("action",$action);
	// DISABLE DB CACHE
	$oldcache=set_use_cache("false");
	// RESTORE THE HISTORY DATA IF EXISTS
	$id_usuario=current_user();
	$id_aplicacion=page2id($page);
	$query="SELECT querystring FROM tbl_history WHERE `id_usuario`='$id_usuario' AND `id_aplicacion`='$id_aplicacion'";
	$result=db_query($query);
	$numrows=db_num_rows($result);
	$row=db_fetch_row($result);
	db_free($result);
	if($numrows) {
		$items=querystring2array(base64_decode($row["querystring"]));
		if(isset($items["id_folder"])) unset($items["id_folder"]);
		if(isset($items["is_fichero"])) unset($items["is_fichero"]);
		if(isset($items["is_buscador"])) unset($items["is_buscador"]);
		$_POST=array_merge($_POST,$items);
	}
	// MAKE THE LIST QUERY
	$_LANG["default"]="$page,menu,common";
	$config=$config[$action];
	$config=eval_attr($config);
	$query0=$config["query"];
	$order=$config["order"];
	// PREPARE THE ORDER HELPER
	$fields=array();
	foreach($config["fields"] as $val) {
		if(isset($val["name"])) $fields[]=$val["name"];
		if(isset($val["order"])) $fields[]=$val["order"];
		if(isset($val["orderasc"])) $fields[]=$val["orderasc"];
		if(isset($val["orderdesc"])) $fields[]=$val["orderdesc"];
	}
	// PREPARE THE ORDER ARRAY
	$array_order=explode(",",$order);
	foreach($array_order as $key=>$val) {
		$val=encode_words($val);
		$val=explode(" ",$val,2);
		if(!in_array($val[0],$fields)) $val[0]="id";
		if(!isset($val[1])) $val[1]="desc";
		$val[1]=strtolower($val[1]);
		if(!in_array($val[1],array("asc","desc"))) $val[1]="desc";
		$array_order[$key]=$val;
	}
	// CONVERT THE ARRAY ORDER TO STRING
	foreach($array_order as $key=>$val) $array_order[$key]=implode(" ",$val);
	if(!count(array_intersect($array_order,array("id asc","id desc")))) $array_order[]="id desc";
	$order=implode(",",$array_order);
	// DETECT DB ENGINE
	$dbtype=db_type();
	if(in_array($dbtype,array("SQLITE","MARIADB"))) {
		if($ids=="string" || $ids=="array") {
			// LIST OF TEMPORARY FIELDS TO RETRIEVE
			$fields=explode(",",$order);
			foreach($fields as $key=>$val) {
				$val=explode(" ",$val);
				$fields[$key]=$val[0];
			}
			if(!in_array("action_id",$fields)) array_push($fields,"action_id");
			$fields=implode(",",$fields);
			// CONTINUE
			$query="SELECT action_id FROM (SELECT $fields FROM ($query0) __a__) __b__ ORDER BY $order";
			$result=execute_query($query);
			if(!$result) $result=array();
			if(!is_array($result)) $result=array($result);
			if($ids=="string") $result=count($result)?implode(",",$result):"0";
		} else {
			// LIST OF TEMPORARY FIELDS TO RETRIEVE
			$fields=explode(",",$order);
			foreach($fields as $key=>$val) {
				$val=explode(" ",$val);
				$fields[$key]=$val[0];
			}
			if(!in_array("action_title",$fields)) array_push($fields,"action_title");
			$fields=implode(",",$fields);
			// CONTINUE
			$ids=check_ids($ids);
			$query="SELECT action_title FROM (SELECT $fields FROM ($query0) __a__ WHERE action_id IN ($ids)) __b__ ORDER BY $order";
			$result=execute_query($query);
			if(!$result) $result=array();
			if(!is_array($result)) $result=array($result);
		}
	} elseif(in_array($dbtype,array("MYSQL"))) {
		static $tbl_hash1=null;
		// CHECK IF tbl_hash1 EXISTS
		if(is_null($tbl_hash1)) {
			// LIST OF TEMPORARY FIELDS TO RETRIEVE
			$fields=explode(",",$order);
			foreach($fields as $key=>$val) {
				$val=explode(" ",$val);
				$fields[$key]=$val[0];
			}
			if(!in_array("action_id",$fields)) array_push($fields,"action_id");
			if(!in_array("action_title",$fields)) array_push($fields,"action_title");
			$fields=implode(",",$fields);
			// CREATE THE TEMPORARY TABLES (HELPERS)
			$tbl_hash1="tbl_".get_unique_id_md5();
			$query="CREATE TEMPORARY TABLE $tbl_hash1 AS SELECT $fields FROM ($query0) $tbl_hash1";
			db_query($query);
		}
		// CONTINUE
		if($ids=="string" || $ids=="array") {
			$query="SELECT action_id FROM $tbl_hash1 ORDER BY $order";
			$result=execute_query($query);
			if(!$result) $result=array();
			if(!is_array($result)) $result=array($result);
			if($ids=="string") $result=count($result)?implode(",",$result):"0";
		} else {
			$ids=check_ids($ids);
			$query="SELECT action_title FROM $tbl_hash1 WHERE action_id IN ($ids) ORDER BY $order";
			$result=execute_query($query);
			if(!$result) $result=array();
			if(!is_array($result)) $result=array($result);
		}
	} else {
		show_php_error(array("phperror"=>"Unknown dbtype '$dbtype'"));
	}
	// RESTORE DB CACHE
	set_use_cache($oldcache);
	// RESTORE THE ORIGINAL GET AND POST
	$_GET=$_OLDGET;
	unset($_OLDGET);
	$_POST=$_OLDPOST;
	unset($_OLDPOST);
	// REVERT TO THE OLD VARIABLES
	$page=$oldpage;
	$action=$oldaction;
	$_LANG["default"]=$oldlang;
	setParam("page",$page);
	setParam("action",$action);
	// RETURN THE EXPECTED RESULT
	return $result;
}
?>