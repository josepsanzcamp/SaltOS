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

require_once("php/database/libsqlite.php");

class database_pdo_sqlite {
	private $link=null;

	function __construct($args) {
		if(!class_exists("PDO")) { show_php_error(array("phperror"=>"Class PDO not found","details"=>"Try to install php-pdo package")); return; }
		if(!file_exists($args["file"])) { show_php_error(array("phperror"=>"File '".$args["file"]."' not found")); return; }
		if(!is_writable($args["file"])) { show_php_error(array("phperror"=>"File '".$args["file"]."' not writable")); return; }
		try {
			$this->link=new PDO("sqlite:".$args["file"]);
		} catch(PDOException $e) {
			show_php_error(array("dberror"=>$e->getMessage()));
		}
		if($this->link) {
			$this->link->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);
			$this->link->setAttribute(PDO::ATTR_TIMEOUT,0);
			$this->db_query("PRAGMA cache_size=2000");
			$this->db_query("PRAGMA synchronous=OFF");
			$this->db_query("PRAGMA foreign_keys=OFF");
			if(!$this->__check("SELECT GROUP_CONCAT(1)")) $this->link->sqliteCreateAggregate("GROUP_CONCAT","__libsqlite_group_concat_step","__libsqlite_group_concat_finalize");
			if(!$this->__check("SELECT REPLACE(1,2,3)")) $this->link->sqliteCreateFunction("REPLACE","__libsqlite_replace");
			$this->link->sqliteCreateFunction("LPAD","__libsqlite_lpad");
			$this->link->sqliteCreateFunction("CONCAT","__libsqlite_concat");
			$this->link->sqliteCreateFunction("UNIX_TIMESTAMP","__libsqlite_unix_timestamp");
			$this->link->sqliteCreateFunction("YEAR","__libsqlite_year");
			$this->link->sqliteCreateFunction("MONTH","__libsqlite_month");
			$this->link->sqliteCreateFunction("WEEK","__libsqlite_week");
			$this->link->sqliteCreateFunction("TRUNCATE","__libsqlite_truncate");
			$this->link->sqliteCreateFunction("DAY","__libsqlite_day");
			$this->link->sqliteCreateFunction("DAYOFYEAR","__libsqlite_dayofyear");
			$this->link->sqliteCreateFunction("DAYOFWEEK","__libsqlite_dayofweek");
			$this->link->sqliteCreateFunction("HOUR","__libsqlite_hour");
			$this->link->sqliteCreateFunction("MINUTE","__libsqlite_minute");
			$this->link->sqliteCreateFunction("SECOND","__libsqlite_second");
			$this->link->sqliteCreateFunction("MD5","__libsqlite_md5");
			$this->link->sqliteCreateFunction("REPEAT","__libsqlite_repeat");
			$this->link->sqliteCreateFunction("FIND_IN_SET","__libsqlite_find_in_set");
			$this->link->sqliteCreateFunction("IF","__libsqlite_if");
			$this->link->sqliteCreateFunction("POW","__libsqlite_pow");
		}
	}

	function __check($query) {
		capture_next_error();
		$this->db_query($query);
		$error=get_clear_error();
		return !$error?true:false;
	}

	function db_query($query,$fetch="query") {
		$query=parse_query($query,"SQLITE");
		$result=array("total"=>0,"header"=>array(),"rows"=>array());
		if(!strlen(trim($query))) return $result;
		// TRICK TO DO THE STRIP SLASHES
		$pos=strpos($query,"\\");
		while($pos!==false) {
			$extra="";
			if($query[$pos+1]=="'") $extra="'";
			if($query[$pos+1]=="%") $extra="\\";
			$query=substr_replace($query,$extra,$pos,1);
			$pos=strpos($query,"\\",$pos+1);
		}
		// CONTINUE THE NORMAL OPERATION
		$timeout=getDefault("db/semaphoretimeout",10000000);
		if(semaphore_acquire(__FUNCTION__,$timeout)) {
			// DO QUERY
			while(1) {
				try {
					$stmt=$this->link->query($query);
					break;
				} catch(PDOException $e) {
					if($timeout<=0) {
						show_php_error(array("dberror"=>$e->getMessage(),"query"=>$query));
						break;
					} elseif(stripos($e->getMessage(),"database is locked")!==false) {
						$timeout-=__semaphore_usleep(rand(0,1000));
					} elseif(stripos($e->getMessage(),"database schema has changed")!==false) {
						$timeout-=__semaphore_usleep(rand(0,1000));
					} else {
						show_php_error(array("dberror"=>$e->getMessage(),"query"=>$query));
						break;
					}
				}
			}
			unset($query); // TRICK TO RELEASE MEMORY
			semaphore_release(__FUNCTION__);
			// DUMP RESULT TO MATRIX
			if(isset($stmt) && $stmt && $stmt->columnCount()>0) {
				if($fetch=="auto") {
					$fetch=$stmt->columnCount()>1?"query":"column";
				}
				if($fetch=="query") {
					$result["rows"]=$stmt->fetchAll(PDO::FETCH_ASSOC);
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
			show_php_error(array("phperror"=>"Could not acquire the semaphore","query"=>$query));
		}
		return $result;
	}

	function db_disconnect() {
		$this->link=null;
	}
}

?>