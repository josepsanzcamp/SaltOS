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
if(!check_user($page,"export")) action_denied();
if(getParam("action")=="export") {
	// DISABLE DB CACHE
	$oldcache=set_use_cache("false");
	// CONTINUE
	$noexports=defined("__FULL_DUMP__")?array():getDefault("db/noexports");
	$fext=getDefault("exts/gzipext",".gz");
	$name=encode_bad_chars("backup_saltos_".current_date()).$fext;
	$dbschema=xml2array("xml/dbschema.xml");
	$tables=array();
	foreach($dbschema["tables"] as $table) {
		if(!is_array($noexports) || !in_array($table["name"],$noexports)) $tables[]=$table["name"];
	}
	$file=get_temp_file($fext);
	$fp=gzopen($file,"w");
	foreach($tables as $table) {
		gzwrite($fp,"DELETE FROM `$table`;\n");
		$query="SELECT COUNT(*) count FROM $table";
		$count=execute_query($query);
		$limit=1000;
		$offset=0;
		while($offset<$count) {
			$query="SELECT * FROM $table LIMIT $offset,$limit";
			$offset=$offset+$limit;
			$result=db_query($query);
			$num=db_num_fields($result);
			$fields=array();
			for($i=0;$i<$num;$i++) {
				$fields[]="`".db_field_name($result,$i)."`";
			}
			$fields=implode(",",$fields);
			while($row=db_fetch_row($result)) {
				$values=array();
				foreach($row as $value) {
					//$value=str_replace("'","\\'",$value);
					$value=addslashes($value);
					$values[]="'".$value."'";
				}
				$values=implode(",",$values);
				gzwrite($fp,"INSERT INTO `$table`($fields) VALUES($values);\n");
			}
			db_free($result);
		}
	}
	gzclose($fp);
	// RESTORE DB CACHE
	set_use_cache($oldcache);
	// CONTINUE
	ob_start(); // NOT OB_HANDLER ALLOWED BECAUSE CONTENT IS COMPRESSED
	header_powered();
	header_expires(false);
	header("Content-Type: application/octet-stream");
	header("Content-Type: application/force-download",false);
	header("Content-Type: application/download",false);
	header("Content-Disposition: attachment; filename=\"$name\"");
	readfile($file);
	unlink($file);
	ob_end_flush();
	die();
}
?>