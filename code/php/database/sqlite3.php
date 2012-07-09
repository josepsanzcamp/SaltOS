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
// sqlite3 implementation
function db_connect_sqlite3() {
	global $_CONFIG;
	if(!class_exists("SQLite3")) { db_error(array("phperror"=>"Class SQLite3 not found")); return; }
	if(!file_exists(getDefault("db/file"))) { db_error(array("dberror"=>"File '".getDefault("db/file")."' not found")); return; }
	if(!is_writable(getDefault("db/file"))) { db_error(array("dberror"=>"File '".getDefault("db/file")."' not writable")); return; }
	try {
		$_CONFIG["db"]["link"]=new SQLite3(getDefault("db/file"));
		getDefault("db/link")->busyTimeout(60000);
	} catch(SQLite3Exception $e) {
		db_error(array("exception"=>$e->getMessage()));
	}
	if(getDefault("db/link")) {
		db_query_sqlite3("PRAGMA cache_size=2000");
		db_query_sqlite3("PRAGMA synchronous=OFF");
		db_query_sqlite3("PRAGMA count_changes=OFF");
		db_query_sqlite3("PRAGMA foreign_keys=OFF");
		// DETECT AND ADD GROUP_CONCAT
		$query="SELECT GROUP_CONCAT(id /*SQLITE , *//*MYSQL SEPARATOR */ ',') FROM (SELECT '1' id) test2";
		capture_next_error();
		db_query_sqlite3($query);
		$error=get_clear_error();
		if($error) getDefault("db/link")->createAggregate("GROUP_CONCAT","__sqlite3_group_concat_step","__sqlite3_group_concat_finalize",2);
	}
	register_shutdown_function("__sqlite3_shutdown_handler");
}

function __sqlite3_shutdown_handler() {
	$semaphore=getDefault("db/file").getDefault("exts/semext",".sem");
	semaphore_release($semaphore);
}

function __sqlite3_group_concat_step(&$context,$string,$separator) {
	if($context!="") $context.=$separator;
	$context.=$string;
	return $context;
}

function __sqlite3_group_concat_finalize(&$context) {
	return $context;
}

function __db_query_sqlite3_helper($query) {
	$result=array("total"=>0,"header"=>array(),"rows"=>array());
	if($query) {
		// DO QUERY
		try {
			$data=getDefault("db/link")->query($query);
			if($data===false) {
				$error=getDefault("db/link")->errorInfo();
				if(isset($error[2])) db_error(array("dberror"=>$error[2],"query"=>$query));
			}
		} catch(SQLite3Exception $e) {
			db_error(array("exception"=>$e->getMessage(),"query"=>$query));
		}
		// DUMP RESULT TO MATRIX
		if($data && $data->numColumns()) {
			$result["rows"]=array();
			while($temp=$data->fetchArray(SQLITE3_ASSOC)) $result["rows"][]=$temp;
			$result["total"]=count($result["rows"]);
			if($result["total"]>0) $result["header"]=array_keys($result["rows"][0]);
		}
	}
	return $result;
}

function db_query_sqlite3($query) {
	$query=parse_query($query,"SQLITE");
	// TRICK TO DO THE STRIP SLASHES
	$pos=strpos($query,"\\");
	while($pos!==false) {
		$extra=$query[$pos+1]=="'"?"'":"";
		$query=substr_replace($query,$extra,$pos,1);
		$pos=strpos($query,"\\",$pos+1);
	}
	// CONTINUE THE NORMAL OPERATION
	$semaphore=getDefault("db/file").getDefault("exts/semext",".sem");
	if(semaphore_acquire($semaphore,getDefault("db/semaphoretimeout",10000000))) {
		$result=__db_query_sqlite3_helper($query);
		semaphore_release($semaphore);
	} else {
		db_error(array("phperror"=>"Could not acquire the semaphore","query"=>$query));
	}
	return $result;
}

function db_disconnect_sqlite3() {
	global $_CONFIG;
	getDefault("db/link")->close();
	$_CONFIG["db"]["link"]=null;
}

function db_error_sqlite3($array) {
	show_php_error($array);
}
?>