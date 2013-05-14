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
function db_connect_pdo_mysql() {
	global $_CONFIG;
	if(!class_exists("PDO")) { db_error_pdo_mysql(array("phperror"=>"Class PDO not found","details"=>"Try to install php-mysqlnd package")); return; }
	try {
		$_CONFIG["db"]["link"]=new PDO("mysql:host=".getDefault("db/host").";port=".getDefault("db/port").";dbname=".getDefault("db/name"),getDefault("db/user"),getDefault("db/pass"));
	} catch(PDOException $e) {
		db_error_pdo_mysql(array("dberror"=>$e->getMessage()));
	}
	if(getDefault("db/link")) {
		getDefault("db/link")->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);
		db_query_pdo_mysql("SET NAMES 'UTF8'");
		db_query_pdo_mysql("SET FOREIGN_KEY_CHECKS=0");
		db_query_pdo_mysql("SET GROUP_CONCAT_MAX_LEN:=@@MAX_ALLOWED_PACKET");
	}
}

function __db_query_pdo_mysql_helper($query) {
	$result=array("total"=>0,"header"=>array(),"rows"=>array());
	if($query) {
		// DO QUERY
		try {
			$data=getDefault("db/link")->query($query);
		} catch(PDOException $e) {
			db_error_pdo_mysql(array("dberror"=>$e->getMessage(),"query"=>$query));
		}
		// DUMP RESULT TO MATRIX
		if($data && $data->columnCount()>0) {
			$result["rows"]=$data->fetchAll(PDO::FETCH_ASSOC);
			$result["total"]=count($result["rows"]);
			if($result["total"]>0) $result["header"]=array_keys($result["rows"][0]);
		}
	}
	return $result;
}

function db_query_pdo_mysql($query) {
	$query=parse_query($query,"MYSQL");
	return __db_query_pdo_mysql_helper($query);
}

function db_disconnect_pdo_mysql() {
	global $_CONFIG;
	$_CONFIG["db"]["link"]=null;
}

function db_error_pdo_mysql($array) {
	foreach($array as $key=>$val) $array[$key]=str_replace(array(getDefault("db/host"),getDefault("db/port"),getDefault("db/user"),getDefault("db/pass"),getDefault("db/name")),"...",$val);
	show_php_error($array);
}
?>