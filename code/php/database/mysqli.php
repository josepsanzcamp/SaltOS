<?php
/*
 ____        _ _    ___  ____
/ ___|  __ _| | |_ / _ \/ ___|
\___ \ / _` | | __| | | \___ \
 ___) | (_| | | |_| |_| |___) |
|____/ \__,_|_|\__|\___/|____/

SaltOS: Framework to develop Rich Internet Applications
Copyright (C) 2013 by Josep Sanz Campderrós
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
function db_connect_mysqli() {
	global $_CONFIG;
	if(!function_exists("mysqli_connect")) { db_error_mysqli(array("phperror"=>"mysqli_connect not found","details"=>"Try to install php-mysqlnd package")); return; }
	$_CONFIG["db"]["link"]=mysqli_connect(getDefault("db/host"),getDefault("db/user"),getDefault("db/pass"),getDefault("db/name"),getDefault("db/port")) or db_error_mysqli(array("dberror"=>mysqli_error(getDefault("db/link"))));
	if(getDefault("db/link")) {
		db_query_mysqli("SET NAMES 'UTF8'");
		db_query_mysqli("SET FOREIGN_KEY_CHECKS=0");
		db_query_mysqli("SET GROUP_CONCAT_MAX_LEN:=@@MAX_ALLOWED_PACKET");
	}
}

function db_query_mysqli($query,$extra="query") {
	$query=parse_query($query,"MYSQL");
	$result=array("total"=>0,"header"=>array(),"rows"=>array());
	if(!$query) return $result;
	// DO QUERY
	$stmt=mysqli_query(getDefault("db/link"),$query) or db_error_mysqli(array("dberror"=>mysqli_error(getDefault("db/link")),"query"=>$query));
	// DUMP RESULT TO MATRIX
	if(!is_bool($stmt) && mysqli_num_fields($stmt)>0) {
		if($extra=="auto") {
			$extra=mysqli_num_fields($stmt)>1?"query":"column";
		}
		if($extra=="query") {
			while($row=mysqli_fetch_assoc($stmt)) $result["rows"][]=$row;
			$result["total"]=count($result["rows"]);
			if($result["total"]>0) $result["header"]=array_keys($result["rows"][0]);
			mysqli_free_result($stmt);
		}
		if($extra=="column") {
			while($row=mysqli_fetch_row($stmt)) $result["rows"][]=$row[0];
			$result["total"]=count($result["rows"]);
			$result["header"]=array("__a__");
			mysqli_free_result($stmt);
		}
	}
	return $result;
}

function db_disconnect_mysqli() {
	global $_CONFIG;
	mysqli_close(getDefault("db/link"));
	$_CONFIG["db"]["link"]=null;
}

function db_error_mysqli($array) {
	foreach($array as $key=>$val) $array[$key]=str_replace(array(getDefault("db/host"),getDefault("db/port"),getDefault("db/user"),getDefault("db/pass"),getDefault("db/name")),"...",$val);
	show_php_error($array);
}
?>