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

function CONFIG_LOADED() {
	global $_CONFIG;
	return isset($_CONFIG);
}

function CONFIG($key) {
	$row=array();
	$query="SELECT valor FROM tbl_configuracion WHERE clave='${key}'";
	capture_next_error();
	$config=execute_query($query);
	$error=get_clear_error();
	if($error=="" && $config!==null) {
		$row=array($key=>$config);
	} else {
		$row=getDefault("configs");
	}
	if(!isset($row[$key])) return null;
	return $row[$key];
}

function setConfig($key,$val) {
	$query="SELECT valor FROM tbl_configuracion WHERE clave='${key}'";
	$config=execute_query($query);
	if($config===null) {
		$query=make_insert_query("tbl_configuracion",array(
			"clave"=>$key,
			"valor"=>$val
		));
		db_query($query);
	} else {
		$query=make_update_query("tbl_configuracion",array(
			"valor"=>$val
		),make_where_query(array(
			"clave"=>$key
		)));
		db_query($query);
	}
}

function make_select_config($keys) {
	$keys=explode(",",$keys);
	$subquery=array("(SELECT '0' id) id");
	foreach($keys as $key) {
		$key=trim($key);
		$query="SELECT valor '$key' FROM tbl_configuracion WHERE clave='$key'";
		$subquery[]="($query) $key";
		$config=execute_query($query);
		if($config===null) {
			$val=CONFIG($key);
			$query=make_insert_query("tbl_configuracion",array(
				"clave"=>$key,
				"valor"=>$val
			));
			db_query($query);
		}
	}
	$subquery=implode(",",$subquery);
	$query="SELECT $subquery";
	return $query;
}

function preeval_update_config($clave) {
	$query="\"UPDATE tbl_configuracion SET valor='\".addslashes(getParam(\"$clave\")).\"' WHERE clave='$clave'\"";
	return $query;
}

function getDefault($key,$default="") {
	global $_CONFIG;
	$key=explode("/",$key);
	$count=count($key);
	$config=$_CONFIG;
	if($count==1 && isset($config["default"][$key[0]])) {
		$config=$config["default"][$key[0]];
		$count=0;
	}
	while($count) {
		$key2=array_shift($key);
		if(!isset($config[$key2])) return $default;
		$config=$config[$key2];
		$count--;
	}
	if($config==="") return $default;
	return $config;
}

?>