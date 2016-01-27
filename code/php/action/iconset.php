<?php
declare(ticks=1000);
/*
 ____        _ _    ___  ____
/ ___|  __ _| | |_ / _ \/ ___|
\___ \ / _` | | __| | | \___ \
 ___) | (_| | | |_| |_| |___) |
|____/ \__,_|_|\__|\___/|____/

SaltOS: Framework to develop Rich Internet Applications
Copyright (C) 2007-2015 by Josep Sanz Campderrós
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
if(getParam("action")=="iconset") {
	// GET PARAMETERS
	$iconset=getParam("iconset",getParam("amp;iconset"));
	$iconset_array=getDefault("iconset/$iconset");
	if(!$iconset_array) action_denied();
	$format=getParam("format",getParam("amp;format"));
	$format=strtok($format,"?");
	if(!in_array($format,array("png","css"))) action_denied();
	// PREPARE CACHE FILENAME
	$temp=get_directory("dirs/cachedir");
	$hash=md5(serialize(array($iconset,$iconset_array,getDefault("cache/usecssminify"),getDefault("cache/useimginline"))));
	$cache="$temp$hash.$format";
	// FOR DEBUG PURPOSES
	//if(file_exists($cache)) unlink($cache);
	// CREATE IF NOT EXISTS
	if(!cache_exists($cache,$iconset_array)) {
		// PROCESS FORMATS
		if($format=="png") {
			// CREATE A BIG IMAGE
			$rows=intval((count($iconset_array)*2-1)/10)+1;
			$im=imagecreatetruecolor(160,$rows*16);
			imagealphablending($im,false);
			imagesavealpha($im,true);
			$index=0;
			foreach($iconset_array as $key=>$val) {
				$im2=imagecreatefrompng($val);
				$posx=intval($index%10)*16;
				$posy=intval($index/10)*16;
				imagecopy($im,$im2,$posx,$posy,0,0,16,16);
				$index++;
				$posx=intval($index%10)*16;
				$posy=intval($index/10)*16;
				if(function_exists("imagefilter")) imagefilter($im2,IMG_FILTER_GRAYSCALE);
				imagecopy($im,$im2,$posx,$posy,0,0,16,16);
				imagedestroy($im2);
				$index++;
			}
			if(isset($iconset_array["none"])) {
				$val=$iconset_array["none"];
				$im2=imagecreatefrompng($val);
				for($posx+=16;$posx<160;$posx+=16) {
					imagecopy($im,$im2,$posx,$posy,0,0,16,16);
				}
				imagedestroy($im2);
			}
			// SAVE AND DESTROY
			imagepng($im,$cache);
			imagedestroy($im);
			chmod_protected($cache,0666);
			// DUMP THE DATA
			if(defined("__CANCEL_DIE__")) readfile($cache);
			if(!defined("__CANCEL_DIE__")) output_handler(array(
				"file"=>$cache,
				"cache"=>true
			));
		}
		if($format=="css") {
			$buffer=array();
			if(eval_bool(getDefault("cache/useimginline"))) {
				define("__CANCEL_DIE__",1);
				setParam("format","png");
				ob_start();
				$oldcache=$cache;
				include(__FILE__);
				$cache=$oldcache;
				$data=base64_encode(ob_get_clean());
				$data="data:image/png;base64,${data}";
				$buffer[]=".saltos-icon { background-image: url(${data})!important; }";
			} else {
				$buffer[]=".saltos-icon { background-image: url(?action=iconset&format=png&iconset=${iconset})!important; }";
			}
			$index=0;
			foreach($iconset_array as $key=>$val) {
				$posx=intval($index%10)*16;
				$posy=intval($index/10)*16;
				$buffer[]=".saltos-icon-${key} { background-position: -${posx}px -${posy}px; }";
				$index++;
				$posx=intval($index%10)*16;
				$posy=intval($index/10)*16;
				$buffer[]=".ui-state-disabled .saltos-icon-${key} { background-position: -${posx}px -${posy}px; }";
				$buffer[]=".ui-state-disabled.saltos-icon-${key} { background-position: -${posx}px -${posy}px; }";
				$index++;
			}
			$buffer=implode("\n",$buffer);
			if(eval_bool(getDefault("cache/usecssminify"))) $buffer=minify_css($buffer);
			file_put_contents($cache,$buffer);
			chmod_protected($cache,0666);
			// DUMP THE DATA
			output_handler(array(
				"file"=>$cache,
				"cache"=>true
			));
		}
	} else {
		if(defined("__CANCEL_DIE__")) readfile($cache);
		if(!defined("__CANCEL_DIE__")) output_handler(array(
			"file"=>$cache,
			"cache"=>true
		));
	}
}
?>