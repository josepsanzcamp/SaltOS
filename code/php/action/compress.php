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
if(getParam("action")=="compress") {
	$id_aplicacion=page2id($page);
	if(!$id_aplicacion) show_php_error(array("phperror"=>"Unknown page"));
	$id_registro=abs($id);
	if(!$id_registro) {
		setParam("id","session");
		$info=array(LANG("temporalfiles")." ".current_datetime());
	} else {
		include("php/listsim.php");
		$info=list_simulator($page,$id_registro);
		if(!isset($info[0])) show_php_error(array("phperror"=>"Content not found"));
		if(substr($info[0],-3,3)=="...") $info[0]=substr($info[0],0,-3);
	}
	$info[0]=encode_bad_chars($info[0]," ");
	$info[0]=intelligence_cut($info[0],50,"");
	$info[0]=encode_bad_chars($info[0]);
	$cids=getParam("cid");
	if(!$cids) show_php_error(array("phperror"=>"Unknown files"));
	$cids=explode(",",$cids);
	$files=array();
	$todelete=array();
	$action="download";
	setParam("action",$action);
	define("__CANCEL_DIE__",1);
	foreach($cids as $cid) {
		setParam("cid",$cid);
		ob_start();
		include("php/action/download.php");
		$data=ob_get_clean();
		if(!file_exists($file)) {
			file_put_contents($file,$data);
			$todelete[]=$file;
		}
		$files[]=array("file"=>$file,"name"=>$name);
	}
	require_once("lib/phpclasses/archive/archive.php");
	$format=getParam("format");
	switch($format) {
		case "zip":
			$archive=new zip_file($info[0].".zip");
			$type="application/zip";
			break;
		case "tar":
			$archive=new tar_file($info[0].".tar");
			$type="application/x-tar";
			break;
		case "gzip":
			$archive=new gzip_file($info[0].".tgz");
			$type="application/x-gzip";
			break;
		case "bzip":
			$archive=new bzip_file($info[0].".tbz");
			$type="application/x-bzip2";
			break;
		default:
			show_php_error(array("phperror"=>"Unknown format"));
	}
	$archive->set_options(array("inmemory"=>1,"storepaths"=>0,"prepend"=>$info[0],"followlinks"=>1));
	foreach($files as $index=>$temp) {
		$archive->add_files($temp["file"]);
		$archive->files[$index]["name2"]=dirname($archive->files[0]["name2"])."/".$temp["name"];
	}
	$archive->create_archive();
	ob_start();
	$archive->download_file();
	$buffer=ob_get_clean();
	output_handler(array(
		"data"=>$buffer,
		"type"=>$type,
		"cache"=>false,
		"die"=>false
	));
	foreach($todelete as $delete) unlink($delete);
	die();
}

?>