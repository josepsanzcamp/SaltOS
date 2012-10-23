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
if(!check_user($page,"import")) action_denied();
if(getParam("action")=="import") {
	// GET THE FILE
	$ok=0;
	foreach($_FILES as $file) {
		if(isset($file["tmp_name"]) && $file["tmp_name"]!="" && file_exists($file["tmp_name"])) {
			$file=$file["tmp_name"];
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
	function __import_find_query($data,$pos) {
		$len=strlen($data);
		$parentesis=0;
		$parser=1;
		$count=$pos;
		$letra_old="";
		while($count<$len) {
			$letra=$data[$count];
			if($letra=="'" && $letra_old!="\\") $parser=!$parser;
			elseif($letra=="(" && $parser) $parentesis++;
			elseif($letra==")" && $parser) $parentesis--;
			elseif($letra==";" && $parser && $parentesis==0) break;
			$letra_old=$letra;
			$count++;
		}
		return $count-$pos;
	}
	// DISABLE DB CACHE
	$oldcache=set_use_cache("false");
	// OPEN FILE
	$fp=gzopen($file,"r");
	// IMPORT QUERYES
	$limit=1000000; // 1MB aprox.
	$data=gzread($fp,$limit*2);
	$len=strlen($data);
	$pos=0;
	while($pos<$len) {
		$count=__import_find_query($data,$pos);
		$query=substr($data,$pos,$count);
		db_query($query);
		$pos=$pos+$count+1;
		if($len-$pos<$limit && $limit>0) {
			$temp=gzread($fp,$limit);
			if(strlen($temp)<$limit) $limit=0;
			$data=substr($data,$pos).$temp;
			unset($temp);
			$pos=0;
			$len=strlen($data);
		}
	}
	gzclose($fp);
	// RESTORE DB CACHE
	set_use_cache($oldcache);
	// RETURN
	session_alert(LANG("filefoundok","datacfg"));
	javascript_history(-1);
	die();
}
?>