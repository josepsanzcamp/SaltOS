<?php
/*
 ____        _ _    ___  ____
/ ___|  __ _| | |_ / _ \/ ___|
\___ \ / _` | | __| | | \___ \
 ___) | (_| | | |_| |_| |___) |
|____/ \__,_|_|\__|\___/|____/

SaltOS: Framework to develop Rich Internet Applications
Copyright (C) 2007-2016 by Josep Sanz Campderrós
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
if(getParam("action")=="preview") {
	if(!check_commands(array(getDefault("commands/preview"),getDefault("commands/xserver")),60)) {
		output_handler(array(
			"file"=>"img/none.png",
			"cache"=>true
		));
		die();
	}
	$url=getParam("url");
	$scheme=parse_url($url,PHP_URL_SCHEME);
	if(!$scheme) $url="http://".$url;
	if(substr($url,-1,1)=="/") $url=substr($url,0,-1);
	$query=make_select_query("tbl_favoritos","id",make_where_query(array("url"=>$url)));
	if(!execute_query($query)) {
		output_handler(array(
			"file"=>"img/none.png",
			"cache"=>true
		));
		die();
	}
	$width=350;
	$height=200;
	$cache=get_cache_file(array($url,$width,$height),getDefault("exts/jpegext",".jpg"));
	if(!file_exists($cache)) {
		if(!semaphore_acquire(getParam("action"))) die();
		ob_passthru(getDefault("commands/xserver")." ".str_replace(array("__COMMAND__"),array(getDefault("commands/preview")." ".str_replace(array("__INPUT__","__OUTPUT__"),array($url,$cache),getDefault("commands/__preview__"))),getDefault("commands/__xserver__")));
		semaphore_release(getParam("action"));
		if(!file_exists($cache)) {
			output_handler(array(
				"file"=>"img/none.png",
				"cache"=>true
			));
			die();
		}
		// RESIZE
		$im1=imagecreatefromjpeg($cache);
		$im2=imagecreatetruecolor($width,$height);
		imagecopyresampled($im2,$im1,0,0,0,0,$width,$height,imagesx($im1),imagesy($im1));
		imagejpeg($im2,$cache);
		imagedestroy($im1);
		imagedestroy($im2);
		// CONTINUE
		chmod_protected($cache,0666);
	}
	output_handler(array(
		"file"=>$cache,
		"cache"=>true
	));
	die();
}
?>