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
if(getParam("action")=="favicon") {
	// GET PARAMETERS
	$bgcolor=getParam("bgcolor",getParam("amp;bgcolor","cd0a0a")); // DEFAULT FOR REDMOND
	$fgcolor=getParam("fgcolor",getParam("amp;fgcolor","fef1ec")); // DEFAULT FOR REDMOND
	$format=getParam("format",getParam("amp;format"));
	// SOME CHECKS
	if(!in_array($format,array("animation","alternate"))) action_denied();
	// PREPARE CACHE FILENAME
	$temp=get_directory("dirs/cachedir");
	$hash=md5(serialize(array($bgcolor,$fgcolor,$format)));
	$cache="$temp$hash.png";
	//if(file_exists($cache)) unlink($cache);
	// FUNCTIONS
	function __number_color2dec($color,$component) {
		$offset=array("R"=>0,"G"=>2,"B"=>4);
		if(!isset($offset[$component])) show_php_error(array("phperror"=>"Unknown component"));
		return hexdec(substr($color,$offset[$component],2));
	}
	function __number_color2aprox($fgcolor,$bgcolor) {
		$fgcolor=intval($fgcolor/85);
		$bgcolor=intval($bgcolor/85);
		$color=3;
		if($fgcolor==$color) $color--;
		if($bgcolor==$color) $color--;
		if($fgcolor==$color) $color--;
		$color=$color*85;
		return $color;
	}
	// CREATE IF NOT EXISTS
	if(!file_exists($cache)) {
		// LOAD DEFAULT FAVICON
		$im3=imagecreatefrompng("img/favicon.png");
		$trcolor=imagecolorat($im3,0,0);
		imagecolortransparent($im3,$trcolor);
		if(function_exists("imagefilter")) imagefilter($im3,IMG_FILTER_GRAYSCALE);
		// PROCESS FORMATS
		if($format=="animation") {
			// CREATE A BIG IMAGE
			$im=imagecreatetruecolor(6400,32);
			$trcolor=imagecolorallocate($im,__number_color2aprox(__number_color2dec($fgcolor,"R"),__number_color2dec($bgcolor,"R")),__number_color2aprox(__number_color2dec($fgcolor,"G"),__number_color2dec($bgcolor,"G")),__number_color2aprox(__number_color2dec($fgcolor,"B"),__number_color2dec($bgcolor,"B")));
			imagecolortransparent($im,$trcolor);
			imagefilledrectangle($im,0,0,6400,32,$trcolor);
			for($i=0;$i<100;$i++) {
				$im2=imagecreatetruecolor(160,160);
				$bgcolor2=imagecolorallocate($im2,__number_color2dec($bgcolor,"R"),__number_color2dec($bgcolor,"G"),__number_color2dec($bgcolor,"B"));
				$fgcolor2=imagecolorallocate($im2,__number_color2dec($fgcolor,"R"),__number_color2dec($fgcolor,"G"),__number_color2dec($fgcolor,"B"));
				$trcolor=imagecolorallocate($im2,__number_color2aprox(__number_color2dec($fgcolor,"R"),__number_color2dec($bgcolor,"R")),__number_color2aprox(__number_color2dec($fgcolor,"G"),__number_color2dec($bgcolor,"G")),__number_color2aprox(__number_color2dec($fgcolor,"B"),__number_color2dec($bgcolor,"B")));
				imagecolortransparent($im2,$trcolor);
				imagefilledrectangle($im2,0,0,160,160,$trcolor);
				if($i>0) {
					imagefilledarc($im2,100,100,120,120,0,0,$bgcolor2,null);
					$font="lib/fonts/DejaVuSans-Bold.ttf";
					$sf=60;
					while(1) {
						$bbox=imagettfbbox($sf,0,$font,$i);
						$sx=$bbox[4]-$bbox[0];
						$sy=$bbox[5]-$bbox[1];
						if($sx<120 && $sy<120) break;
						$sf--;
					}
					$px=100+$bbox[0]-$sx/2-5;
					$py=100+$bbox[1]-$sy/2;
					imagettftext($im2,$sf,0,$px-1,$py,$fgcolor2,$font,$i);
					imagettftext($im2,$sf,0,$px,$py,$fgcolor2,$font,$i);
					imagettftext($im2,$sf,0,$px+1,$py,$fgcolor2,$font,$i);
				}
				imagecopyresampled($im,$im3,$i*64,0,0,0,32,32,32,32);
				imagecopyresampled($im,$im3,$i*64+32,0,31,0,32,32,-32,32);
				imagecopyresized($im,$im2,$i*64,0,0,0,32,32,160,160);
				imagecopyresized($im,$im2,$i*64+32,0,0,0,32,32,160,160);
				imagedestroy($im2);
			}
			// SAVE AND DESTROY
			imagepng($im,$cache);
			imagedestroy($im);
			chmod_protected($cache,0666);
		}
		if($format=="alternate") {
			imagepng($im3,$cache);
			chmod_protected($cache,0666);
		}
		// DESTROY DEFAULT FAVICON
		imagedestroy($im3);
	}
	// DUMP THE DATA
	output_file($cache);
}
?>