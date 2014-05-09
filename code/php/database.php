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
// abstract functions
function db_connect() {
	$php="php/database/".getDefault("db/type").".php";
	if(!file_exists($php)) show_php_error(array("phperror"=>"Type '".getDefault("db/type")."' not found"));
	require_once($php);
	$func=__FUNCTION__."_".getDefault("db/type");
	return $func();
}

function db_query($query,$fetch="query") {
	static $stack=array();
	$query=trim($query);
	// CHECK CACHE
	$hash=md5(serialize(array($query,$fetch)));
	$usecache=eval_bool(get_use_cache($query));
	if($usecache && isset($stack[$hash])) return $stack[$hash];
	// DO QUERY
	$func=__FUNCTION__."_".getDefault("db/type");
	$result=$func($query,$fetch);
	// AND RETURN
	if($usecache) $stack[$hash]=$result;
	return $result;
}

function db_disconnect() {
	$func=__FUNCTION__."_".getDefault("db/type");
	return $func();
}

function db_error($array) {
	$func=__FUNCTION__."_".getDefault("db/type");
	return $func($array);
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
	if(!isset($result["header"][$index])) db_error(array("phperror"=>"Unknown field name at position ${index}"));
	return $result["header"][$index];
}
?>