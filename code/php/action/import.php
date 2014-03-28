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
if(!check_user($page,"import")) action_denied();
if($page=="importaciones") {
	include("php/import.php");
	echo "TODO";
	die();
}
if($page=="datacfg") {
	// GET THE FILE
	$ok=0;
	foreach($_FILES as $file) {
		if(isset($file["tmp_name"]) && $file["tmp_name"]!="" && file_exists($file["tmp_name"])) {
			$ok=1;
			break;
		} elseif(isset($file["name"]) && $file["name"]!="") {
			session_error(LANG("fileuploaderror").$file["name"]);
			javascript_history(-1);
			die();
		}
	}
	if(!$ok) {
		session_error(LANG("filenotfound","datacfg"));
		javascript_history(-1);
		die();
	}
	// FUNCTIONS
	function __import_find_chars($data,$pos,$chars) {
		$result=array();
		$len=strlen($chars);
		for($i=0;$i<$len;$i++) {
			$temp=strpos($data,$chars[$i],$pos);
			if($temp!==false) $result[]=$temp;
		}
		return count($result)?min($result):false;
	}

	function __import_find_query($data,$pos) {
		$len=strlen($data);
		$parentesis=0;
		$parser=1;
		$exists=0;
		$pos2=__import_find_chars($data,$pos,"\\'();");
		while($pos2!==false) {
			if($data[$pos2]=="\\") $pos2++;
			elseif($data[$pos2]=="'") $parser=!$parser;
			elseif($data[$pos2]=="(" && $parser) $parentesis++;
			elseif($data[$pos2]==")" && $parser) $parentesis--;
			elseif($data[$pos2]==";" && $parser && !$parentesis) { $exists=1; break; }
			if($pos2+1>=$len) break;
			$pos2=__import_find_chars($data,$pos2+1,"\\'();");
		}
		if(!$parser || $parentesis || !$exists) return 0;
		return $pos2-$pos;
	}
	// DISABLE DB CACHE
	$oldcache=set_use_cache("false");
	// OPEN FILE
	$fp=gzopen($file["tmp_name"],"r");
	// IMPORT QUERYES
	$limit=1000000; // 1MB aprox.
	$data=gzread($fp,$limit*2);
	$len=strlen($data);
	$pos=0;
	while($pos<$len) {
		$count=__import_find_query($data,$pos);
		if($count) {
			$query=substr($data,$pos,$count);
			capture_next_error();
			db_query($query);
			$error=get_clear_error();
			if($error!="") {
				gzclose($fp);
				session_error(LANG("fileimporterror","datacfg").$file["name"]);
				javascript_history(-1);
				die();
			}
			$pos=$pos+$count+1;
		}
		if(($len-$pos<$limit || !$count) && $limit) {
			$temp=gzread($fp,$limit);
			if(strlen($temp)<$limit) $limit=0;
			$data=substr($data,$pos).$temp;
			unset($temp);
			$pos=0;
			$len=strlen($data);
		} elseif(!$count && !$limit) {
			break;
		}
	}
	gzclose($fp);
	// RESTORE DB CACHE
	set_use_cache($oldcache);
	// RETURN
	session_alert(LANG("filefoundok","datacfg").$file["name"]);
	javascript_history(-1);
	die();
}
?>