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
// bin_sqlite implementations
function db_connect_bin_sqlite() {
	global $_CONFIG;
	if(!check_commands(getDefault("commands/sqlite3"),60)) { db_error(array("dberror"=>"Command '".getDefault("commands/sqlite3")."' not found")); return; }
	if(!file_exists(getDefault("db/file"))) { db_error(array("dberror"=>"File '".getDefault("db/file")."' not found")); return; }
	if(!is_writable(getDefault("db/file"))) { db_error(array("dberror"=>"File '".getDefault("db/file")."' not writable")); return; }
	register_shutdown_function("__bin_sqlite_shutdown_handler");
}

function __bin_sqlite_shutdown_handler() {
	// TRICK FOR CREATE TEMPORARY TABLE
	global $_CONFIG;
	$drops=getDefault("db/drops");
	if(is_array($drops)) {
		foreach($drops as $drop) {
			$query="DROP TABLE $drop";
			db_query_bin_sqlite($query);
		}
	}
}

function __db_query_bin_sqlite_helper($query) {
	$result=array("total"=>0,"header"=>array(),"rows"=>array());
	if($query) {
		// TRICK FOR CREATE TEMPORARY TABLE
		$word1=strtok($query," ");
		$word2=strtok(" ");
		$word3=strtok(" ");
		$word4=strtok(" ");
		if(strtoupper("$word1 $word2 $word3")=="CREATE TEMPORARY TABLE") {
			$pos=stripos($query,"TEMPORARY");
			$query=substr_replace($query,"",$pos,9);
			global $_CONFIG;
			set_array($_CONFIG["db"]["drops"],"drop",$word4);
		}
		if(strtoupper("$word1 $word2")=="DROP TABLE") {
			global $_CONFIG;
			if(isset($_CONFIG["db"]["drops"]) && is_array($_CONFIG["db"]["drops"])) {
				$index=array_search($word3,$_CONFIG["db"]["drops"]);
				if($index!==false) unset($_CONFIG["db"]["drops"][$index]);
			}
		}
		// DEFINE FILES
		$stdin=get_temp_file(getDefault("exts/sqlext",".sql"));
		$stdout=get_temp_file(getDefault("exts/csvext",".csv"));
		// PREPARE QUERY
		$input=array();
		$input[]="PRAGMA cache_size=2000;";
		$input[]="PRAGMA synchronous=OFF;";
		$input[]="PRAGMA count_changes=OFF;";
		$input[]="PRAGMA foreign_keys=OFF;";
		$input[]=".header ON";
		$input[]=".mode csv";
		$input[]=".output $stdout";
		$input[]="$query;";
		$input=implode("\n",$input);
		file_put_contents($stdin,$input);
		$timeout=10000000;
		// EXECUTE QUERY WITH A GREAT TRICK!!!
		while($timeout>=0) {
			$error=ob_passthru(getDefault("commands/sqlite3")." ".str_replace(array("__DBFILE__","__INPUT__"),array(getDefault("db/file"),$stdin),getDefault("commands/__sqlite3__")));
			if(!$error) break;
			if($error && strpos($error,"database is locked")===false) break;
			$usleep=rand(0,1000);
			usleep($usleep);
			$timeout-=$usleep;
		}
		// PROCESS OUTPUT
		unlink($stdin);
		if($error) {
			if(file_exists($stdout)) unlink($stdout);
			db_error(array("query"=>$query,"dberror"=>$error));
		}
		// PREPARE PARAMETERS TO PARSE CSV
		$length=0; // DEFAULT VALUE
		$delimiter=","; // DEFAULT VALUE
		$enclosure='"'; // DEFAULT VALUE
		$escape='"'; // IMPORTANT: THIS IS THE NEEDED ESCAPE CHAR
		// GET OUTPUT
		if(file_exists($stdout)) {
			$fp=fopen($stdout,"r");
			capture_next_error();
			$row=fgetcsv($fp,$length,$delimiter,$enclosure,$escape);
			$error=get_clear_error();
			if($error) $row=fgetcsv($fp,$length,$delimiter,$enclosure);
			if(!feof($fp)) {
				$result["header"]=$row;
				if(!$error) $row=fgetcsv($fp,$length,$delimiter,$enclosure,$escape);
				if($error) $row=fgetcsv($fp,$length,$delimiter,$enclosure);
				while(!feof($fp)) {
					$result["rows"][]=array_combine($result["header"],$row);
					$result["total"]++;
					if(!$error) $row=fgetcsv($fp,$length,$delimiter,$enclosure,$escape);
					if($error) $row=fgetcsv($fp,$length,$delimiter,$enclosure);
				}
			}
			fclose($fp);
			unlink($stdout);
		}
	}
	return $result;
}

function db_query_bin_sqlite($query) {
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
		$result=__db_query_bin_sqlite_helper($query);
		semaphore_release($semaphore);
	} else {
		db_error(array("phperror"=>"Could not acquire the semaphore","query"=>$query));
	}
	return $result;
}

function db_disconnect_bin_sqlite() {
	// NOTHING TO DO
}

function db_error_bin_sqlite($array) {
	show_php_error($array);
}
?>