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
function db_connect_mssql() {
	global $_CONFIG;
	if(!function_exists("mssql_connect")) { show_php_error(array("phperror"=>"mssql_connect not found","details"=>"Try to install php-mssql package")); return; }
	$_CONFIG["db"]["link"]=mssql_connect(getDefault("db/host").":".getDefault("db/port"),getDefault("db/user"),getDefault("db/pass"));
	if($_CONFIG["db"]["link"]===false) show_php_error(array("dberror"=>mssql_get_last_message()));
	if(!mssql_select_db(getDefault("db/name"),getDefault("db/link"))) show_php_error(array("dberror"=>mssql_get_last_message()));
	if(getDefault("db/link")) {
		db_query_mssql("SET NAMES 'UTF8'");
		db_query_mssql("SET FOREIGN_KEY_CHECKS=0");
		db_query_mssql("SET GROUP_CONCAT_MAX_LEN:=@@MAX_ALLOWED_PACKET");
	}
}

function db_query_mssql($query,$fetch="query") {
	$query=parse_query($query,"MSSQL");
	$result=array("total"=>0,"header"=>array(),"rows"=>array());
	if(!$query) return $result;
	// DO QUERY
	$stmt=mssql_query($query,getDefault("db/link"));
	if($stmt===false) show_php_error(array("dberror"=>mssql_get_last_message(),"query"=>$query));
	// DUMP RESULT TO MATRIX
	if(!is_bool($stmt) && mssql_num_fields($stmt)) {
		if($fetch=="auto") {
			$fetch=mssql_num_fields($stmt)>1?"query":"column";
		}
		if($fetch=="query") {
			while($row=mssql_fetch_assoc($stmt)) $result["rows"][]=$row;
			$result["total"]=count($result["rows"]);
			if($result["total"]>0) $result["header"]=array_keys($result["rows"][0]);
			mssql_free_result($stmt);
		}
		if($fetch=="column") {
			while($row=mssql_fetch_row($stmt)) $result["rows"][]=$row[0];
			$result["total"]=count($result["rows"]);
			$result["header"]=array("__a__");
			mssql_free_result($stmt);
		}
	}
	return $result;
}

function db_disconnect_mssql() {
	global $_CONFIG;
	mssql_close(getDefault("db/link"));
	$_CONFIG["db"]["link"]=null;
}
?>