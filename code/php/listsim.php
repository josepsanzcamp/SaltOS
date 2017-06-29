<?php
/*
 ____        _ _    ___  ____
/ ___|  __ _| | |_ / _ \/ ___|
\___ \ / _` | | __| | | \___ \
 ___) | (_| | | |_| |_| |___) |
|____/ \__,_|_|\__|\___/|____/

SaltOS: Framework to develop Rich Internet Applications
Copyright (C) 2007-2017 by Josep Sanz Campderrós
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
function list_simulator($newpage,$ids="string") {
	global $page;
	global $action;
	global $_LANG;
	// LOAD THE APPLICATION FILE
	if(!file_exists("xml/$newpage.xml")) return null;
	$config=xml2array("xml/$newpage.xml");
	if(!isset($config["list"])) return null;
	// SAVE THE CURRENT CONTEXT
	saltos_context($newpage,"list");
	// RESTORE THE HISTORY DATA IF EXISTS
	$id_usuario=current_user();
	$id_aplicacion=page2id($page);
	load_history($id_usuario,$id_aplicacion);
	// MAKE THE LIST QUERY
	$config=$config[$action];
	$config=eval_attr($config);
	// GET THE NEEDED XML NODES
	$query0=$config["query"];
	$limit=$config["limit"];
	$offset=$config["offset"];
	$extra=isset($config["limit2"])?"LIMIT ".$config["limit2"]:"";
	// CHECK ORDER
	list($order,$array)=list_check_order($config["order"],$config["fields"]);
	// EXECUTE THE QUERY TO GET THE REQUESTED DATA
	if($ids=="count") {
		$query="SELECT COUNT(*) FROM ($query0 $extra) __a__";
		$count=execute_query($query);
		$result=array("count"=>$count,"limit"=>$limit,"offset"=>$offset);
	} elseif($ids=="string" || $ids=="array") {
		$query="SELECT action_id FROM ($query0 $extra) __a__ ORDER BY $order";
		$result=execute_query_array($query);
		if($ids=="string") $result=count($result)?implode(",",$result):"0";
	} else {
		$ids=check_ids($ids);
		$query="SELECT action_title FROM ($query0 $extra) __a__ WHERE action_id IN ($ids) ORDER BY $order";
		$result=execute_query_array($query);
	}
	// RESTORE THE SAVED CONTEXT
	saltos_context();
	// RETURN THE EXPECTED RESULT
	return $result;
}

function saltos_context($newpage="",$newaction="") {
	global $page;
	global $action;
	global $_LANG;
	static $oldget=array();
	static $oldpost=array();
	static $oldpage="";
	static $oldaction="";
	static $oldlang="";
	if($newpage && $newaction && !$oldpage && !$oldaction) {
		// SAVE CONTEXT
		$oldget=$_GET;
		$_GET=array();
		$oldpost=$_POST;
		$_POST=array();
		$oldpage=isset($page)?$page:null;
		$page=$newpage;
		setParam("page",$page);
		$oldaction=isset($action)?$action:null;
		$action=$newaction;
		setParam("action",$action);
		$oldlang=$_LANG["default"];
		$_LANG["default"]="$page,menu,common";
	} elseif(!$newpage && !$newaction && $oldpage && $oldaction) {
		// RESTORE CONTEXT
		$_GET=$oldget;
		$oldget=array();
		$_POST=$oldpost;
		$oldpost=array();
		$page=$oldpage;
		$oldpage="";
		$action=$oldaction;
		$oldaction="";
		$_LANG["default"]=$oldlang;
		$oldlang="";
	} else {
		show_php_error(array(
			"phperror"=>"saltos_context internal error",
			"details"=>sprintr(array("newpage"=>$newpage,"newaction"=>$newaction,"oldpage"=>$oldpage,"oldaction"=>$oldaction))
		));
	}
}

function list_check_order($order,$fields2) {
	// PREPARE THE ORDER HELPER
	$fields=array();
	foreach($fields2 as $val) {
		if(isset($val["name"])) $fields[]=$val["name"];
		if(isset($val["order"])) $fields[]=$val["order"];
		if(isset($val["orderasc"])) $fields[]=$val["orderasc"];
		if(isset($val["orderdesc"])) $fields[]=$val["orderdesc"];
	}
	// PREPARE THE ORDER ARRAY
	$array=explode(",",$order);
	foreach($array as $key=>$val) {
		$val=prepare_words($val);
		$val=explode(" ",$val,2);
		if(!in_array($val[0],$fields)) $val[0]="id";
		if(!isset($val[1])) $val[1]="desc";
		$val[1]=strtolower($val[1]);
		if(!in_array($val[1],array("asc","desc"))) $val[1]="desc";
		$array[$key]=$val;
	}
	// CONVERT THE ARRAY ORDER TO STRING
	$order=$array;
	foreach($order as $key=>$val) $order[$key]=implode(" ",$val);
	if(!count(array_intersect($order,array("id asc","id desc")))) $order[]="id desc";
	$order=implode(",",$order);
	// RETURN AS STRING AND AS TREE
	return array($order,$array);
}
?>