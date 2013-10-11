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
	} catch(PDOException $e) {
		db_error_pdo_sqlite(array("dberror"=>$e->getMessage()));
	}
	if(getDefault("db/link")) {
		getDefault("db/link")->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);
		getDefault("db/link")->setAttribute(PDO::ATTR_TIMEOUT,0);
		db_query_pdo_sqlite("PRAGMA cache_size=2000");
		db_query_pdo_sqlite("PRAGMA synchronous=OFF");
		db_query_pdo_sqlite("PRAGMA foreign_keys=OFF");
		if(!__pdo_sqlite_check("SELECT GROUP_CONCAT(1)")) getDefault("db/link")->sqliteCreateAggregate("GROUP_CONCAT","__pdo_sqlite_group_concat_step","__pdo_sqlite_group_concat_finalize");
		if(!__pdo_sqlite_check("SELECT REPLACE(1,2,3)")) getDefault("db/link")->sqliteCreateFunction("REPLACE","__pdo_sqlite_replace");
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
}

function __pdo_sqlite_check($query) {
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

function __pdo_sqlite_replace($subject,$search,$replace) {
	return str_replace($search,$replace,$subject);
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

function db_query_pdo_sqlite($query,$fetch="query") {
	$query=parse_query($query,"SQLITE");
	$result=array("total"=>0,"header"=>array(),"rows"=>array());
	if(!$query) return $result;
	// TRICK TO DO THE STRIP SLASHES
	$pos=strpos($query,"\\");
	while($pos!==false) {
		$extra=$query[$pos+1]=="'"?"'":"";
		$query=substr_replace($query,$extra,$pos,1);
		$pos=strpos($query,"\\",$pos+1);
	}
	// CONTINUE THE NORMAL OPERATION
	$semaphore=getDefault("db/file").getDefault("exts/semext",".sem");
	$timeout=getDefault("db/semaphoretimeout",10000000);
	if(semaphore_acquire($semaphore,$timeout)) {
		// DO QUERY
		while(1) {
			try {
				$stmt=getDefault("db/link")->query($query);
				break;
			} catch(PDOException $e) {
				if($timeout<=0) {
					db_error_pdo_sqlite(array("dberror"=>$e->getMessage(),"query"=>$query));
					break;
				} elseif(stripos($e->getMessage(),"database is locked")!==false) {
					$timeout-=usleep_protected(rand(0,1000));
				} elseif(stripos($e->getMessage(),"database schema has changed")!==false) {
					$timeout-=usleep_protected(rand(0,1000));
				} else {
					db_error_pdo_sqlite(array("dberror"=>$e->getMessage(),"query"=>$query));
					break;
				}
			}
		}
		semaphore_release($semaphore);
		// DUMP RESULT TO MATRIX
		if(isset($stmt) && $stmt && $stmt->columnCount()>0) {
			if($fetch=="auto") {
				$fetch=$stmt->columnCount()>1?"query":"column";
			}
			if($fetch=="query") {
				$result["rows"]=$stmt->fetchAll(PDO::FETCH_ASSOC);
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
			if($fetch=="column") {
				$result["rows"]=$stmt->fetchAll(PDO::FETCH_COLUMN);
				$result["total"]=count($result["rows"]);
				$result["header"]=array("__a__");
			}
		}
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