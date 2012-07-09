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
if(!check_user()) action_denied();
if(getParam("action")=="download") {
	$id_aplicacion=page2id($page);
	if(!$id_aplicacion) show_php_error(array("phperror"=>"Unknown page"));
	$id_registro=(getParam("id")=="session")?getParam("id"):abs($id);
	if(!$id_registro) show_php_error(array("phperror"=>"Unknown content"));
	$cid=getParam("cid");
	if(!$cid) show_php_error(array("phperror"=>"Unknown file"));
	if($page=="correo") {
		if($id_registro=="session") {
			sess_init();
			$session=$_SESSION["correo"];
			sess_close();
			if(!isset($session["files"][$cid])) show_php_error(array("phperror"=>"Session not found"));
			$result=$session["files"][$cid];
			$file=$result["file"];
			$name=$result["name"];
			$type=$result["mime"];
			$size=$result["size"];
		} else {
			include_once("php/getmail.php");
			$decoded=__getmail_getmime($id_registro);
			if(!$decoded) show_php_error(array("phperror"=>"Email not found"));
			if(strlen($cid)!=32) {
				$query="SELECT fichero_file FROM tbl_ficheros WHERE id='${cid}' AND id_aplicacion='${id_aplicacion}' AND id_registro='${id_registro}'";
				$cid=execute_query($query);
				if(!$cid) show_php_error(array("phperror"=>"Unknown file"));
			}
			$result=__getmail_getcid(__getmail_getnode("0",$decoded),$cid);
			if(!$result) show_php_error(array("phperror"=>"Attachment not found"));
			$ext=pathinfo($result["cname"],PATHINFO_EXTENSION);
			if(!$ext) $ext=substr($result["ctype"],strrpos($result["ctype"],"/")+1);
			$file=get_temp_file($ext);
			file_put_contents($file,$result["body"]);
			$name=$result["cname"];
			$type=$result["ctype"];
			$size=$result["csize"];
		}
	} else {
		$query="SELECT * FROM tbl_ficheros WHERE id='${cid}' AND id_aplicacion='${id_aplicacion}' AND id_registro='${id_registro}'";
		$result=execute_query($query);
		if(!$result) show_php_error(array("phperror"=>"File not found"));
		$file=get_directory("dirs/filesdir").$result["fichero_file"];
		if(!file_exists($file)) show_php_error(array("phperror"=>"Local file not found"));
		$name=$result["fichero"];
		$type=$result["fichero_type"];
		$size=$result["fichero_size"];
	}
	if(!defined("__CANCEL_HEADER__")) {
		$hash=md5(file_get_contents($file));
		header_etag($hash);
		$gzipext=getDefault("exts/gzipext",".gz");
		$isgzip=(substr($name,-strlen($gzipext))==$gzipext);
		ob_start($isgzip?"":getDefault("obhandler"));
		header_powered();
		header_expires(false);
		header("Content-Disposition: attachment; filename=\"${name}\"");
		header("Content-Type: ${type}");
		readfile($file);
		ob_end_flush();
	} else {
		readfile($file);
	}
	if($page=="correo" && $id_registro!="session") unlink($file);
	if(!defined("__CANCEL_DIE__")) die();
}
?>