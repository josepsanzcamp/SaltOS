<?php
/*
 ____        _ _    ___  ____
/ ___|  __ _| | |_ / _ \/ ___|
\___ \ / _` | | __| | | \___ \
 ___) | (_| | | |_| |_| |___) |
|____/ \__,_|_|\__|\___/|____/

SaltOS: Framework to develop Rich Internet Applications
Copyright (C) 2007-2020 by Josep Sanz Campderrós
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
if(getParam("action")=="score") {
	// GET PARAMETERS
	$pass=getParam("pass");
	$format=getParam("format");
	if(!in_array($format,array("png","json"))) action_denied();
	$width=intval(getParam("width",60));
	$height=intval(getParam("width",16));
	$size=intval(getParam("font",8));
	$minscore=intval(getDefault("security/minscore"));
	// PREPARE CACHE FILENAME
	$temp=get_directory("dirs/cachedir");
	$hash=md5(serialize(array($pass,$format,$width,$height,$size,$minscore)));
	$cache="$temp$hash.$format";
	// FOR DEBUG PURPOSES
	//if(file_exists($cache)) unlink($cache);
	// CREATE IF NOT EXISTS
	if(!file_exists($cache)) {
		// PROCESS FORMATS
		if($format=="png") {
			$im=imagecreatetruecolor($width,$height);
			$score=password_strength($pass);
			$incr=($score*512/100)/$width;
			$posx=0;
			for($i=0;$i<=255;$i=$i+$incr) {
				if($posx>$width) break;
				$color=imagecolorallocate($im,255,$i,0);
				imageline($im,$posx,0,$posx,$height,$color);
				$posx++;
			}
			for($i=255;$i>=0;$i=$i-$incr) {
				if($posx>$width) break;
				$color=imagecolorallocate($im,$i,255,0);
				imageline($im,$posx,0,$posx,$height,$color);
				$posx++;
			}
			$font=getcwd()."/lib/fonts/DejaVuSans.ttf";
			$bbox=imagettfbbox($size,0,$font,$score."%");
			$sx=$bbox[4]-$bbox[0];
			$sy=$bbox[5]-$bbox[1];
			$color=imagecolorallocate($im,0,0,0);
			imagettftext($im,$size,0,$width/2-$sx/2,$height/2-$sy/2,$color,$font,$score."%");
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
		if($format=="json") {
			define("__CANCEL_DIE__",1);
			setParam("format","png");
			ob_start();
			$oldcache=$cache;
			include(__FILE__);
			$cache=$oldcache;
			$data=base64_encode(ob_get_clean());
			$data="data:image/png;base64,${data}";
			$valid=($score>=$minscore)?1:0;
			$_RESULT=array("image"=>$data,"score"=>$score."%","valid"=>$valid);
			$buffer=json_encode($_RESULT);
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