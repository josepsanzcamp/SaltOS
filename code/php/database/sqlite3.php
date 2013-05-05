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
function db_connect_sqlite3() {
	global $_CONFIG;
	if(!class_exists("SQLite3")) { db_error_sqlite3(array("phperror"=>"Class SQLite3 not found","details"=>"Try to install php-pdo package")); return; }
	if(!file_exists(getDefault("db/file"))) { db_error_sqlite3(array("dberror"=>"File '".getDefault("db/file")."' not found")); return; }
	if(!is_writable(getDefault("db/file"))) { db_error_sqlite3(array("dberror"=>"File '".getDefault("db/file")."' not writable")); return; }
	try {
		$_CONFIG["db"]["link"]=new SQLite3(getDefault("db/file"));
	} catch(SQLite3Exception $e) {
		db_error_sqlite3(array("exception"=>$e->getMessage()));
	}
	if(getDefault("db/link")) {
		getDefault("db/link")->busyTimeout(0);
		db_query_sqlite3("PRAGMA cache_size=2000");
		db_query_sqlite3("PRAGMA synchronous=OFF");
		db_query_sqlite3("PRAGMA foreign_keys=OFF");
		if(!__sqlite3_group_concat_check()) getDefault("db/link")->createAggregate("GROUP_CONCAT","__sqlite3_group_concat_step","__sqlite3_group_concat_finalize");
		getDefault("db/link")->createFunction("LPAD","__sqlite3_lpad");
		getDefault("db/link")->createFunction("CONCAT","__sqlite3_concat");
		getDefault("db/link")->createFunction("UNIX_TIMESTAMP","__sqlite3_unix_timestamp");
		getDefault("db/link")->createFunction("YEAR","__sqlite3_year");
		getDefault("db/link")->createFunction("MONTH","__sqlite3_month");
		getDefault("db/link")->createFunction("WEEK","__sqlite3_week");
		getDefault("db/link")->createFunction("TRUNCATE","__sqlite3_truncate");
		getDefault("db/link")->createFunction("DAY","__sqlite3_day");
		getDefault("db/link")->createFunction("DAYOFYEAR","__sqlite3_dayofyear");
		getDefault("db/link")->createFunction("DAYOFWEEK","__sqlite3_dayofweek");
		getDefault("db/link")->createFunction("HOUR","__sqlite3_hour");
		getDefault("db/link")->createFunction("MINUTE","__sqlite3_minute");
		getDefault("db/link")->createFunction("SECOND","__sqlite3_second");
		getDefault("db/link")->createFunction("MD5","__sqlite3_MD5");
	}
	register_shutdown_function("__sqlite3_shutdown_handler");
}

function __sqlite3_shutdown_handler() {
	$semaphore=getDefault("db/file").getDefault("exts/semext",".sem");
	semaphore_release($semaphore);
}

function __sqlite3_group_concat_check() {
	$query="SELECT GROUP_CONCAT(1)";
	capture_next_error();
	db_query_sqlite3($query);
	$error=get_clear_error();
	return !$error?true:false;
}

function __sqlite3_group_concat_step($context,$rows,$string,$separator=",") {
	if($context!="") $context.=$separator;
	$context.=$string;
	return $context;
}

function __sqlite3_group_concat_finalize($context,$rows) {
	return $context;
}

function __sqlite3_lpad($input,$length,$char) {
	return str_pad($input,$length,$char,STR_PAD_LEFT);
}

function __sqlite3_concat() {
	$array=func_get_args();
	return implode("",$array);
}

function __sqlite3_unix_timestamp($date) {
	return strtotime($date);
}

function __sqlite3_year($date) {
	return date("Y",strtotime($date));
}

function __sqlite3_month($date) {
	return date("m",strtotime($date));
}

function __sqlite3_week($date,$mode) {
	$mode=$mode*86400;
	return date("W",strtotime($date)+$mode);
}

function __sqlite3_truncate($n,$d) {
	$d=pow(10,$d);
	return intval($n*$d)/$d;
}

function __sqlite3_day($date) {
	return intval(date("d",strtotime($date)));
}

function __sqlite3_dayofyear($date) {
	return date("z",strtotime($date))+1;
}

function __sqlite3_dayofweek($date) {
	return date("w",strtotime($date))+1;
}

function __sqlite3_hour($date) {
	return intval(date("H",strtotime($date)));
}

function __sqlite3_minute($date) {
	return intval(date("i",strtotime($date)));
}

function __sqlite3_second($date) {
	return intval(date("s",strtotime($date)));
}

function __sqlite3_md5($temp) {
	return md5($temp);
}

function __db_query_sqlite3_helper($query) {
	$result=array("total"=>0,"header"=>array(),"rows"=>array());
	if($query) {
		// DO QUERY
		try {
			$data=getDefault("db/link")->query($query);
			if($data===false) {
				$error=getDefault("db/link")->errorInfo();
				if(isset($error[2])) db_error_sqlite3(array("dberror"=>$error[2],"query"=>$query));
			}
		} catch(SQLite3Exception $e) {
			db_error_sqlite3(array("exception"=>$e->getMessage(),"query"=>$query));
		}
		// DUMP RESULT TO MATRIX
		if($data && $data->numColumns()) {
			while($row=$data->fetchArray(SQLITE3_ASSOC)) $result["rows"][]=$row;
			$continue=false;
			foreach($result["rows"] as $key=>$val) {
				foreach($val as $key2=>$val2) {
					if($key2[0]=="`" && substr($key2,-1,1)=="`") {
						unset($result["rows"][$key][$key2]);
						$result["rows"][$key][substr($key2,1,-1)]=$val2;
						$continue=true;
					}
				}
				if(!$continue) break;
			}
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
		db_error_sqlite3(array("phperror"=>"Could not acquire the semaphore","query"=>$query));
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