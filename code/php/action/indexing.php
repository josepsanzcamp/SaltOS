<?php
/*
 ____        _ _    ___  ____
/ ___|  __ _| | |_ / _ \/ ___|
\___ \ / _` | | __| | | \___ \
 ___) | (_| | | |_| |_| |___) |
|____/ \__,_|_|\__|\___/|____/

SaltOS: Framework to develop Rich Internet Applications
Copyright (C) 2007-2019 by Josep Sanz Campderrós
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
if(!check_user()) action_denied();
if(getParam("action")=="indexing") {
	if(!eval_bool(getDefault("enableindexing"))) die();
	require_once("php/unoconv.php");
	require_once("php/getmail.php");
	// CHECK THE SEMAPHORE
	if(!semaphore_acquire(getParam("action"),getDefault("semaphoretimeout",100000))) die();
	// INDEXING FILES
	$query="SELECT id,id_aplicacion,id_registro,fichero_file,fichero_hash,retries FROM tbl_ficheros WHERE indexed=0 AND retries<3 LIMIT 1000";
	$result=db_query($query);
	$total=0;
	while($row=db_fetch_row($result)) {
		if(time_get_usage()>getDefault("server/percentstop")) break;
		// CHECK IF EXISTS
		$query=make_select_query("tbl_ficheros","id",make_where_query(array("id"=>$row["id"])));
		$exists=execute_query($query);
		if(!$exists) continue;
		// CONTINUE
		$query=make_update_query("tbl_ficheros",array(),make_where_query(array("id"=>$row["id"])),array("retries"=>"retries+1"));
		db_query($query);
		if($row["id_aplicacion"]==page2id("correo")) {
			$decoded=__getmail_getmime($row["id_registro"]);
			if(!$decoded) {
				show_php_error(array("phperror"=>"Email not found","details"=>sprintr($row),"file"=>getDefault("debug/warningfile","warning.log"),"die"=>false));
				$query=make_update_query("tbl_ficheros",array("retries"=>"3"),make_where_query(array("id"=>$row["id"])));
				db_query($query);
				continue;
			}
			$file=__getmail_getcid(__getmail_getnode("0",$decoded),$row["fichero_hash"]);
			if(!$file) {
				show_php_error(array("phperror"=>"Attachment not found","details"=>sprintr($row),"file"=>getDefault("debug/warningfile","warning.log"),"die"=>false));
				$QUERY=make_update_query("tbl_ficheros",array("retries"=>"3"),make_where_query(array("id"=>$row["id"])));
				db_query($query);
				continue;
			}
			$ext=strtolower(extension($file["cname"]));
			if(!$ext) $ext=strtolower(extension2($file["ctype"]));
			$input=get_cache_file($row["fichero_hash"],$ext);
			file_put_contents($input,$file["body"]);
		} else {
			$input=get_directory("dirs/filesdir").$row["fichero_file"];
		}
		$search=unoconv2txt($input);
		$query=make_update_query("tbl_ficheros",array("indexed"=>1,"search"=>$search),make_where_query(array("id"=>$row["id"])));
		db_query($query);
		make_indexing($row["id_aplicacion"],$row["id_registro"]);
		$total++;
	}
	db_free($result);
	// SEND RESPONSE
	if($total) javascript_alert($total.LANG("msgfilesindexed".min($total,2)));
	// INDEXING UNINDEXED CONTENTS
	$query=make_select_query("tbl_aplicaciones",array(
		"id",
		"tabla"
	),make_where_query(array(
		"tabla"=>array("!=","")
	)));
	$result=db_query($query);
	$total=0;
	while($row=db_fetch_row($result)) {
		if(time_get_usage()>getDefault("server/percentstop")) break;
		$id_aplicacion=$row["id"];
		$tabla=$row["tabla"];
		for(;;) {
			if(time_get_usage()>getDefault("server/percentstop")) break;
			// SEARCH IDS OF THE MAIN APPLICATION TABLE, THAT DOESN'T EXISTS ON THE INDEXING TABLE
			$query=make_select_query($tabla,"id",make_where_query(array(),"AND",array(
				"id NOT IN (".make_select_query("tbl_indexing","id_registro",make_where_query(array(
					"id_aplicacion"=>$id_aplicacion
				))).")"
			)),array(
				"limit"=>1000
			));
			$ids=execute_query_array($query);
			if(!count($ids)) break;
			make_indexing($id_aplicacion,$ids);
			$total+=count($ids);
		}
		for(;;) {
			if(time_get_usage()>getDefault("server/percentstop")) break;
			// SEARCH IDS OF THE INDEXING TABLE, THAT DOESN'T EXISTS ON THE MAIN APPLICATION TABLE
			$query=make_select_query("tbl_indexing","id_registro",make_where_query(array(
				"id_aplicacion"=>$id_aplicacion
			),"AND",array(
				"id_registro NOT IN (".make_select_query($tabla,"id").")"
			)),array(
				"limit"=>1000
			));
			$ids=execute_query_array($query);
			if(!count($ids)) break;
			make_indexing($id_aplicacion,$ids);
			$total+=count($ids);
		}
	}
	db_free($result);
	// CHECK INTEGRITY
	for(;;) {
		if(time_get_usage()>getDefault("server/percentstop")) break;
		// SEARCH FOR DUPLICATED ROWS IN INDEXING TABLE
		$query=make_select_query("tbl_indexing",array(
			"GROUP_CONCAT(id)"=>"ids",
			"id_aplicacion"=>"id_aplicacion",
			"id_registro"=>"id_registro",
			"COUNT(*)"=>"total"
		),"",array(
			"groupby"=>array("id_aplicacion","id_registro"),
			"having"=>"total>1",
			"limit"=>"1000"
		));
		$ids=execute_query_array($query);
		if(!count($ids)) break;
		foreach($ids as $key=>$val) {
			$val=explode(",",$val["ids"]);
			array_shift($val);
			$ids[$key]=implode(",",$val);
		}
		$temp=implode(",",$ids);
		$query=make_delete_query("tbl_indexing","id IN (${temp})");
		db_query($query);
		$total+=count($ids);
	}
	// CHECK INTEGRITY
	for(;;) {
		if(time_get_usage()>getDefault("server/percentstop")) break;
		// SEARCH IDS OF THE INDEXING TABLE THAT DOESN'T EXISTS IN THE APPLICATION TABLE
		$query=make_select_query("tbl_indexing","id",make_where_query(array(),"AND",array(
			"id_aplicacion NOT IN (".make_select_query("tbl_aplicaciones","id").")"
		)),array(
			"limit"=>1000
		));
		$ids=execute_query_array($query);
		if(!count($ids)) break;
		$temp=implode(",",$ids);
		$query=make_delete_query("tbl_indexing","id IN (${temp})");
		db_query($query);
		$total+=count($ids);
	}
	// SEND RESPONSE
	if($total) javascript_alert($total.LANG("msgregistersindexed".min($total,2)));
	// RELEASE SEMAPHORE
	semaphore_release(getParam("action"));
	javascript_headers();
	die();
}
?>