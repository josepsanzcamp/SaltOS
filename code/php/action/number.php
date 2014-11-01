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
if(getParam("action")=="number") {
	// GET PARAMETERS
	$bgcolor=getParam("bgcolor",getParam("amp;bgcolor","cd0a0a")); // DEFAULT FOR REDMOND
	$fgcolor=getParam("fgcolor",getParam("amp;fgcolor","fef1ec")); // DEFAULT FOR REDMOND
	$format=getParam("format",getParam("amp;format"));
	// SOME CHECKS
	if(!in_array($format,array("png","css"))) action_denied();
	// PREPARE CACHE FILENAME
	$temp=get_directory("dirs/cachedir");
	$hash=md5(serialize(array($bgcolor,$fgcolor,getDefault("cache/usecssminify"),getDefault("cache/useimginline"))));
	$cache="$temp$hash.$format";
	// FUNCTIONS
	if(!defined("__NUMBER_PHP__")) {
		define("__NUMBER_PHP__",1);
		function __number_color2dec($color,$component) {
			$offset=array("R"=>0,"G"=>2,"B"=>4);
			if(!isset($offset[$component])) show_php_error(array("phperror"=>"Unknown component"));
			return hexdec(substr($color,$offset[$component],2));
		}
		function __number_color2incr($color,$incr) {
			return min(max($color*$incr,0),255);
		}
		function __number_color2aprox($fgcolor,$bgcolor1,$bgcolor2) {
			$color=255;
			while($color==$fgcolor || $color==$bgcolor1 || $color==$bgcolor2) $color--;
			return $color;
		}
	}
	// FOR DEBUG PURPOSES
	//if(file_exists($cache)) unlink($cache);
	// CREATE IF NOT EXISTS
	if(!file_exists($cache)) {
		// PROCESS FORMATS
		if($format=="png") {
			// CALCULE THE COLORS VALUES
			$bgcolor_r=__number_color2dec($bgcolor,"R");
			$bgcolor_g=__number_color2dec($bgcolor,"G");
			$bgcolor_b=__number_color2dec($bgcolor,"B");
			$bgcolor_r1=__number_color2incr($bgcolor_r,1.1);
			$bgcolor_g1=__number_color2incr($bgcolor_g,1.1);
			$bgcolor_b1=__number_color2incr($bgcolor_b,1.1);
			$bgcolor_r2=__number_color2incr($bgcolor_r,0.9);
			$bgcolor_g2=__number_color2incr($bgcolor_g,0.9);
			$bgcolor_b2=__number_color2incr($bgcolor_b,0.9);
			$fgcolor_r=__number_color2dec($fgcolor,"R");
			$fgcolor_g=__number_color2dec($fgcolor,"G");
			$fgcolor_b=__number_color2dec($fgcolor,"B");
			$trcolor_r=__number_color2aprox($fgcolor_r,$bgcolor_r1,$bgcolor_r2);
			$trcolor_g=__number_color2aprox($fgcolor_g,$bgcolor_g1,$bgcolor_g2);
			$trcolor_b=__number_color2aprox($fgcolor_b,$bgcolor_b1,$bgcolor_b2);
			// CREATE A BIG IMAGE
			$im=imagecreatetruecolor(160,160);
			$trcolor=imagecolorallocate($im,$trcolor_r,$trcolor_g,$trcolor_b);
			imagecolortransparent($im,$trcolor);
			imagefilledrectangle($im,0,0,160,160,$trcolor);
			for($i=0;$i<100;$i++) {
				$im2=imagecreatetruecolor(160,160);
				$bgcolor2=imagecolorallocate($im2,$bgcolor_r1,$bgcolor_g1,$bgcolor_b1);
				$bgcolor3=imagecolorallocate($im2,$bgcolor_r2,$bgcolor_g2,$bgcolor_b2);
				$fgcolor2=imagecolorallocate($im2,$fgcolor_r,$fgcolor_g,$fgcolor_b);
				$trcolor=imagecolorallocate($im2,$trcolor_r,$trcolor_g,$trcolor_b);
				imagecolortransparent($im2,$trcolor);
				imagefilledrectangle($im2,0,0,160,160,$trcolor);
				if($i>0) {
					imagefilledarc($im2,80,80,120,120,180,0,$bgcolor2,null);
					imagefilledarc($im2,80,80,120,120,0,180,$bgcolor3,null);
					$font=getcwd()."/lib/fonts/DejaVuSans-Bold.ttf"; // GETCWD NEEDED BY HHVM
					$sf=60;
					while(1) {
						$bbox=imagettfbbox($sf,0,$font,$i);
						$sx=$bbox[4]-$bbox[0];
						$sy=$bbox[5]-$bbox[1];
						if($sx<120 && $sy<120) break;
						$sf--;
					}
					$px=80+$bbox[0]-$sx/2-5;
					$py=80+$bbox[1]-$sy/2;
					imagettftext($im2,$sf,0,$px-1,$py,$fgcolor2,$font,$i);
					imagettftext($im2,$sf,0,$px,$py,$fgcolor2,$font,$i);
					imagettftext($im2,$sf,0,$px+1,$py,$fgcolor2,$font,$i);
				}
				$posx=intval($i%10)*16;
				$posy=intval($i/10)*16;
				imagecopyresampled($im,$im2,$posx,$posy,0,0,16,16,160,160);
				imagedestroy($im2);
			}
			// SAVE AND DESTROY
			imagepng($im,$cache);
			imagedestroy($im);
			chmod_protected($cache,0666);
			// DUMP THE DATA
			if(defined("__CANCEL_DIE__")) readfile($cache);
			if(!defined("__CANCEL_DIE__")) output_file($cache);
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
				$buffer[]=".number-icon { background-image: url(${data})!important; }";
			} else {
				$buffer[]=".number-icon { background-image: url(?action=number&format=png&bgcolor=${bgcolor}&fgcolor=${fgcolor})!important; }";
			}
			$index=0;
			for($i=0;$i<100;$i++) {
				$posx=intval($i%10)*16;
				$posy=intval($i/10)*16;
				$buffer[]=".number-icon-${i} { background-position: -${posx}px -${posy}px; }";
				$index++;
			}
			$buffer=implode("\n",$buffer);
			if(eval_bool(getDefault("cache/usecssminify"))) $buffer=minify_css($buffer);
			file_put_contents($cache,$buffer);
			chmod_protected($cache,0666);
			// DUMP THE DATA
			output_file($cache);
		}
	} else {
		if(defined("__CANCEL_DIE__")) readfile($cache);
		if(!defined("__CANCEL_DIE__")) output_file($cache);
	}
}
?>