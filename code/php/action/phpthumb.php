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
if(getParam("action")=="phpthumb") {
	// FIND THE REAL FILE
	$src=getParam("src",getParam("amp;src"));
	if(!file_exists($src)) $src=getcwd()."/".getParam("src",getParam("amp;src"));
	if(!file_exists($src)) $src=get_directory("dirs/filesdir").getParam("src",getParam("amp;src"));
	if(!file_exists($src)) action_denied();
	// PARSE PARAMETERS
	$width=null;
	if(getParam("w",getParam("amp;w"))) {
		$width=intval(getParam("w",getParam("amp;w")));
		if($width<1 || $width>2000) action_denied();
	}
	$height=null;
	if(getParam("h",getParam("amp;h"))) {
		$height=intval(getParam("h",getParam("amp;h")));
		if($height<1 || $height>2000) action_denied();
	}
	// SECURITY CHECK
	$type=saltos_content_type($src);
	if(substr($type,0,5)!="image") action_denied();
	// CONTINUE
	$format=substr($type,6);
	if(getParam("f",getParam("amp;f"))) $format=getParam("f",getParam("amp;f"));
	// PREPARE CACHE FILENAME
	$temp=get_directory("dirs/cachedir");
	$hash=md5(serialize(array($src,$width,$height)));
	$cache="$temp$hash.$format";
	// FOR DEBUG PURPOSES
	//if(file_exists($cache)) unlink($cache);
	// CREATE IF NOT EXISTS
	if(!file_exists($cache)) {
		// LOAD IMAGE
		$im=imagecreatefromstring(file_get_contents($src));
		// CALCULATE SIZE
		if(!is_null($width) && !is_null($height)) {
			$width2=imagesx($im)*$height/imagesy($im);
			$height2=imagesy($im)*$width/imagesx($im);
			if($width2>$width) $height=$height2;
			if($height2>$height) $width=$width2;
		} elseif(is_null($width) && !is_null($height)) {
			$width=imagesx($im)*$height/imagesy($im);
		} elseif(!is_null($width) && is_null($height)) {
			$height=imagesy($im)*$width/imagesx($im);
		} elseif(is_null($width) && is_null($height)) {
			$width=imagesx($im);
			$height=imagesy($im);
		}
		// DO RESIZE
		$im2=imagecreatetruecolor($width,$height);
		$tr=imagecolortransparent($im);
		if($tr>=0) {
			$tr=imagecolorsforindex($im,$tr);
			$tr=imagecolorallocate($im2,$tr["red"],$tr["green"],$tr["blue"]);
			imagecolortransparent($im2,$tr);
			imagefilledrectangle($im2,0,0,$width,$height,$tr);
		} else {
			imagealphablending($im2,false);
			imagesavealpha($im2,true);
			$tr=imagecolorallocatealpha($im2,0,0,0,127);
			imagefilledrectangle($im2,0,0,$width,$height,$tr);
		}
		imagecopyresampled($im2,$im,0,0,0,0,$width,$height,imagesx($im),imagesy($im));
		imagedestroy($im);
		// WRITE
		if($format=="png") imagepng($im2,$cache);
		elseif($format=="jpeg") imagejpeg($im2,$cache);
		elseif($format=="gif") imagegif($im2,$cache);
		else show_php_error(array("phperror"=>"Unsupported format: format"));
		imagedestroy($im2);
		chmod_protected($cache,0666);
	}
	output_file($cache);
}
?>