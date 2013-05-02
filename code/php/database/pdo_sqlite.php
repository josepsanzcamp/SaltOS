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
function db_connect_pdo_sqlite() {
	global $_CONFIG;
	if(!class_exists("PDO")) { db_error_pdo_sqlite(array("phperror"=>"Class PDO not found","details"=>"Try to install php-pdo package")); return; }
	if(!file_exists(getDefault("db/file"))) { db_error_pdo_sqlite(array("dberror"=>"File '".getDefault("db/file")."' not found")); return; }
	if(!is_writable(getDefault("db/file"))) { db_error_pdo_sqlite(array("dberror"=>"File '".getDefault("db/file")."' not writable")); return; }
	try {
		$_CONFIG["db"]["link"]=new PDO("sqlite:".getDefault("db/file"));
		getDefault("db/link")->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_SILENT);
	} catch(PDOException $e) {
		db_error_pdo_sqlite(array("exception"=>$e->getMessage()));
	}
	if(getDefault("db/link")) {
		db_query_pdo_sqlite("PRAGMA cache_size=2000");
		db_query_pdo_sqlite("PRAGMA synchronous=OFF");
		db_query_pdo_sqlite("PRAGMA count_changes=OFF");
		db_query_pdo_sqlite("PRAGMA temp_store=MEMORY");
		db_query_pdo_sqlite("PRAGMA foreign_keys=OFF");
		if(!__pdo_sqlite_group_concat_check()) getDefault("db/link")->sqliteCreateAggregate("GROUP_CONCAT","__pdo_sqlite_group_concat_step","__pdo_sqlite_group_concat_finalize");
		getDefault("db/link")->sqliteCreateFunction("LPAD","__pdo_sqlite_lpad");
		getDefault("db/link")->sqliteCreateFunction("CONCAT","__pdo_sqlite_concat");
		getDefault("db/link")->sqliteCreateFunction("UNIX_TIMESTAMP","__pdo_sqlite_unix_timestamp");
		getDefault("db/link")->sqliteCreateFunction("YEAR","__pdo_sqlite_year");
		getDefault("db/link")->sqliteCreateFunction("MONTH","__pdo_sqlite_month");
		getDefault("db/link")->sqliteCreateFunction("WEEK","__pdo_sqlite_week");
		getDefault("db/link")->sqliteCreateFunction("TRUNCATE","__pdo_sqlite_truncate");
		getDefault("db/link")->sqliteCreateFunction("DAY","__pdo_sqlite_day");
		getDefault("db/link")->sqliteCreateFunction("DAYOFYEAR","__pdo_sqlite_dayofyear");
		getDefault("db/link")->sqliteCreateFunction("DAYOFWEEK","__pdo_sqlite_dayofweek");
		getDefault("db/link")->sqliteCreateFunction("HOUR","__pdo_sqlite_hour");
		getDefault("db/link")->sqliteCreateFunction("MINUTE","__pdo_sqlite_minute");
		getDefault("db/link")->sqliteCreateFunction("SECOND","__pdo_sqlite_second");
		getDefault("db/link")->sqliteCreateFunction("MD5","__pdo_sqlite_md5");
	}
	register_shutdown_function("__pdo_sqlite_shutdown_handler");
}

function __pdo_sqlite_shutdown_handler() {
	$semaphore=getDefault("db/file").getDefault("exts/semext",".sem");
	semaphore_release($semaphore);
}

function __pdo_sqlite_group_concat_check() {
	$query="SELECT GROUP_CONCAT(1)";
	capture_next_error();
	db_query_pdo_sqlite($query);
	$error=get_clear_error();
	return !$error?true:false;
}

function __pdo_sqlite_group_concat_step($context,$rows,$string,$separator=",") {
	if($context!="") $context.=$separator;
	$context.=$string;
	return $context;
}

function __pdo_sqlite_group_concat_finalize($context,$rows) {
	return $context;
}

function __pdo_sqlite_lpad($input,$length,$char) {
	return str_pad($input,$length,$char,STR_PAD_LEFT);
}

function __pdo_sqlite_concat() {
	$array=func_get_args();
	return implode("",$array);
}

function __pdo_sqlite_unix_timestamp($date) {
	return strtotime($date);
}

function __pdo_sqlite_year($date) {
	return intval(date("Y",strtotime($date)));
}

function __pdo_sqlite_month($date) {
	return intval(date("m",strtotime($date)));
}

function __pdo_sqlite_week($date,$mode) {
	$mode=$mode*86400;
	return date("W",strtotime($date)+$mode);
}

function __pdo_sqlite_truncate($n,$d) {
	$d=pow(10,$d);
	return intval($n*$d)/$d;
}

function __pdo_sqlite_day($date) {
	return intval(date("d",strtotime($date)));
}

function __pdo_sqlite_dayofyear($date) {
	return date("z",strtotime($date))+1;
}

function __pdo_sqlite_dayofweek($date) {
	return date("w",strtotime($date))+1;
}

function __pdo_sqlite_hour($date) {
	return intval(date("H",strtotime($date)));
}

function __pdo_sqlite_minute($date) {
	return intval(date("i",strtotime($date)));
}

function __pdo_sqlite_second($date) {
	return intval(date("s",strtotime($date)));
}

function __pdo_sqlite_md5($temp) {
	return md5($temp);
}

function __db_query_pdo_sqlite_helper($query) {
	$result=array("total"=>0,"header"=>array(),"rows"=>array());
	if($query) {
		// DO QUERY
		try {
			$data=getDefault("db/link")->query($query);
			if($data===false) {
				$error=getDefault("db/link")->errorInfo();
				if(isset($error[2])) db_error_pdo_sqlite(array("dberror"=>$error[2],"query"=>$query));
			}
		} catch(PDOException $e) {
			db_error_pdo_sqlite(array("exception"=>$e->getMessage(),"query"=>$query));
		}
		// DUMP RESULT TO MATRIX
		if($data && $data->columnCount()>0) {
			$result["rows"]=$data->fetchAll(PDO::FETCH_ASSOC);
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

function db_query_pdo_sqlite($query) {
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
		$result=__db_query_pdo_sqlite_helper($query);
		semaphore_release($semaphore);
	} else {
		db_error_pdo_sqlite(array("phperror"=>"Could not acquire the semaphore","query"=>$query));
	}
	return $result;
}

function db_disconnect_pdo_sqlite() {
	global $_CONFIG;
	$_CONFIG["db"]["link"]=null;
}

function db_error_pdo_sqlite($array) {
	show_php_error($array);
}
?>