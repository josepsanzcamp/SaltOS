<?php
/*
 ____        _ _    ___  ____
/ ___|  __ _| | |_ / _ \/ ___|
\___ \ / _` | | __| | | \___ \
 ___) | (_| | | |_| |_| |___) |
|____/ \__,_|_|\__|\___/|____/

SaltOS: Framework to develop Rich Internet Applications
Copyright (C) 2007-2021 by Josep Sanz Campderrós
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

function db_connect($args=null) {
	global $_CONFIG;
	if($args===null) $config=getDefault("db");
	if($args!==null) $config=$args;
	$php="php/database/".$config["type"].".php";
	if(!file_exists($php)) show_php_error(array("phperror"=>"Database type '".$config["type"]."' not found"));
	require_once($php);
	$driver="database_".$config["type"];
	$obj=new $driver($config);
	if($args===null) $_CONFIG["db"]["obj"]=$obj;
	if($args!==null) return $obj;
}

function db_query($query,$fetch="query") {
	if(method_exists(getDefault("db/obj"),"db_query")) {
		$debug=eval_bool(getDefault("debug/slowquerydebug"));
		if($debug) {
			$curtime=microtime(true);
		}
		$result=getDefault("db/obj")->db_query($query,$fetch);
		if($debug) {
			$curtime=microtime(true)-$curtime;
			$maxtime=getDefault("debug/slowquerytime");
			if($curtime>$maxtime) {
				addtrace(array(
					"dbwarning"=>"Slow query requires $curtime seconds",
					"query"=>$query,
				),getDefault("debug/dbwarningfile","dbwarning.log"));
			}
		}
	} else {
		show_php_error(array("phperror"=>"Unknown database connector"));
		$result=array("total"=>0,"header"=>array(),"rows"=>array());
	}
	return $result;
}

function db_disconnect() {
	getDefault("db/obj")->db_disconnect();
}

// shared functions
function db_fetch_row(&$result) {
	if(!isset($result["__array_reverse__"])) {
		$result["rows"]=array_reverse($result["rows"]);
		$result["__array_reverse__"]=1;
	}
	return array_pop($result["rows"]);
}

function db_fetch_all(&$result) {
	return $result["rows"];
}

function db_num_rows($result) {
	return $result["total"];
}

function db_free(&$result) {
	$result=array("total"=>0,"header"=>array(),"rows"=>array());
}

function db_num_fields($result) {
	return count($result["header"]);
}

function db_field_name($result,$index) {
	if(!isset($result["header"][$index])) show_php_error(array("phperror"=>"Unknown field name at position ${index}"));
	return $result["header"][$index];
}

// extra functions
function db_query_protected($query,$fetch="query") {
	capture_next_error();
	$result=db_query($query,$fetch);
	get_clear_error();
	return $result;
}
?>