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
function db_connect_mysql() {
	global $_CONFIG;
	if(!function_exists("mysql_pconnect")) { db_error_mysql(array("phperror"=>"mysql_pconnect not found")); return; }
	$_CONFIG["db"]["link"]=mysql_pconnect(getDefault("db/host").":".getDefault("db/port"),getDefault("db/user"),getDefault("db/pass")) or db_error_mysql(array("dberror"=>"mysql_pconnect()"));
	mysql_select_db(getDefault("db/name"),getDefault("db/link")) or db_error_mysql(array("dberror"=>"mysql_select_db()"));
	if(getDefault("db/link")) {
		db_query_mysql("SET NAMES 'UTF8'");
		db_query_mysql("SET FOREIGN_KEY_CHECKS=0");
		db_query_mysql("SET GROUP_CONCAT_MAX_LEN:=@@MAX_ALLOWED_PACKET");
	}
}

function __db_query_mysql_helper($query) {
	$result=array("total"=>0,"header"=>array(),"rows"=>array());
	if($query) {
		// DO QUERY
		$data=mysql_query($query,getDefault("db/link")) or db_error_mysql(array("dberror"=>mysql_error(getDefault("db/link")),"query"=>$query));
		// DUMP RESULT TO MATRIX
		if(!is_bool($data) && mysql_num_fields($data)) {
			while($row=mysql_fetch_assoc($data)) $result["rows"][]=$row;
			$result["total"]=count($result["rows"]);
			if($result["total"]>0) $result["header"]=array_keys($result["rows"][0]);
			mysql_free_result($data);
		}
	}
	return $result;
}

function db_query_mysql($query) {
	$query=parse_query($query,"MYSQL");
	return __db_query_mysql_helper($query);
}

function db_disconnect_mysql() {
	global $_CONFIG;
	mysql_close(getDefault("db/link"));
	$_CONFIG["db"]["link"]=null;
}

function db_error_mysql($array) {
	foreach($array as $key=>$val) $array[$key]=str_replace(array(getDefault("db/host"),getDefault("db/port"),getDefault("db/user"),getDefault("db/pass"),getDefault("db/name")),"...",$val);
	show_php_error($array);
}

function db_type_mysql() {
	$query="SHOW VARIABLES WHERE Value LIKE '%MariaDB%'";
	$result=db_query($query);
	$numrows=db_num_rows($result);
	db_free($result);
	return $numrows?"MARIADB":"MYSQL";
}
?>