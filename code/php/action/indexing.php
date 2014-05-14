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
if(!check_user()) action_denied();
if(getParam("action")=="indexing") {
	#if(!eval_bool(getDefault("enableindexing"))) return;
	require_once("php/unoconv.php");
	require_once("php/getmail.php");
	// CHECK THE SEMAPHORE
	$semaphore=get_cache_file(array(getParam("action"),current_user()),getDefault("exts/semext",".sem"));
	if(!semaphore_acquire($semaphore,getDefault("semaphoretimeout",100000))) {
		javascript_error(LANG("msgerrorsemaphore").getParam("action"));
		die();
	}
	// INDEXING FILES
	$query="SELECT * FROM tbl_ficheros WHERE id_usuario='".current_user()."' AND indexed='0' ORDER BY id ASC LIMIT 1000";
	$result=db_query($query);
	$total=0;
	while($row=db_fetch_row($result)) {
		if($row["id_aplicacion"]==page2id("correo")) {
			$decoded=__getmail_getmime($row["id_registro"]);
			if(!$decoded) show_php_error(array("phperror"=>"Email not found","details"=>sprintr($row)));
			$file=__getmail_getcid(__getmail_getnode("0",$decoded),$row["fichero_file"]);
			if(!$file) {
				$retry=0;
				$files=__getmail_getfiles(__getmail_getnode("0",$decoded));
				foreach($files as $key=>$val) {
					$test1=$row["fichero_file"]==md5(md5($val["body"]).md5($val["cid"]).md5($val["cname"]).md5($val["ctype"]).md5($val["csize"]));
					$test2=$row["fichero_file"]==md5(serialize(array($val["body"],$val["cid"],$val["cname"],$val["ctype"],$val["csize"])));
					if($test1 || $test2) {
						$query="UPDATE tbl_ficheros SET fichero_file='${val["chash"]}' WHERE id='${row["id"]}'";
						db_query($query);
						$row["fichero_file"]=$val["chash"];
						$file=__getmail_getcid(__getmail_getnode("0",$decoded),$row["fichero_file"]);
						$retry=1;
						break;
					}
				}
				if(!$retry) continue;
			}
			if(!$file) show_php_error(array("phperror"=>"Attachment not found","details"=>sprintr($row)));
			$ext=strtolower(extension($file["cname"]));
			if(!$ext) $ext=strtolower(extension2($file["ctype"]));
			$input=get_cache_file($row["fichero_file"],$ext);
			file_put_contents($input,$file["body"]);
		} else {
			$input=get_directory("dirs/filesdir").$row["fichero_file"];
		}
		$search=addslashes(encode_search(unoconv2txt($input)," "));
		$query="UPDATE tbl_ficheros SET indexed='1',search='${search}' WHERE id='${row["id"]}'";
		db_query($query);
		$total++;
	}
	db_free($result);
	// SEND RESPONSE
	if($total) javascript_alert($total.LANG("msgtotalindexed".min($total,2)));
	// RELEASE SEMAPHORE
	semaphore_release($semaphore);
	javascript_headers();
	die();
}
?>
