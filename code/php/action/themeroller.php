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
if(getParam("action")=="themeroller") {
	// GET PARAMETERS
	$theme=getParam("theme",getParam("amp;theme"));
	$theme=strtok($theme,"?");
	$palette=xml2array("xml/themeroller.xml");
	$cssbase="lib/jquery/jquery-ui-1.9.2.css";
	$imgbase="lib/jquery/jquery-ui-1.9.2.images/";
	$mask=getParam("mask",getParam("amp;mask"));
	$mask=strtok($mask,"?");
	$over=getParam("over",getParam("amp;over"));
	$over=strtok($over,"?");
	// PREPARE CACHE FILENAME
	$temp=get_directory("dirs/cachedir");
	$hash=md5(serialize(array($theme,$palette,$cssbase,$imgbase,$mask,$over,getDefault("cache/usecssminify"),getDefault("cache/useimginline"))));
	if($theme) $format="css";
	if($mask) $format="png";
	if($over) $format="png";
	if(!isset($format)) action_denied();
	if($theme && $mask) action_denied();
	if($mask && $over) action_denied();
	if($over && $theme) action_denied();
	$cache="$temp$hash.$format";
	// FOR DEBUG PURPOSES
	//if(file_exists($cache)) unlink($cache);
	//$_CONFIG["cache"]["useimginline"]="false";
	//$_CONFIG["cache"]["usecssminify"]="false";
	// CREATE IF NOT EXISTS
	$allbase=array_merge(array($cssbase),glob($imgbase."*.png"));
	if(!cache_exists($cache,$allbase)) {
		if($mask) {
			$mask=explode(",",$mask);
			if(count($mask)!=2) action_denied();
			if(!file_exists($imgbase.$mask[0])) show_php_error(array("phperror"=>"Mask '${mask[0]}' not found"));
			if(strlen($mask[1])!=6) action_denied();
			$im=imagecreatefrompng($imgbase.$mask[0]);
			$sx=imagesx($im);
			$sy=imagesy($im);
			$im2=imagecreatetruecolor($sx,$sy);
			$bg=imagecolorallocate($im2,255,255,255);
			imagecolortransparent($im2,$bg);
			imagefilledrectangle($im2,0,0,$sx,$sy,$bg);
			$r=hexdec(substr($mask[1],0,2));
			$g=hexdec(substr($mask[1],2,2));
			$b=hexdec(substr($mask[1],4,2));
			$cache2=array();
			for($x=0;$x<$sx;$x++) {
				for($y=0;$y<$sy;$y++) {
					$z=imagecolorsforindex($im,imagecolorat($im,$x,$y));
					unset($z["alpha"]);
					$z=127-max(0,min(127,array_sum($z)/6));
					if(!isset($cache2[$z])) $cache2[$z]=imagecolorallocatealpha($im2,$r,$g,$b,$z);
					imagesetpixel($im2,$x,$y,$cache2[$z]);
				}
			}
			imagedestroy($im);
			imagepng($im2,$cache);
			imagedestroy($im2);
			chmod_protected($cache,0666);
			// DUMP THE DATA
			if(defined("__CANCEL_DIE__")) readfile($cache);
			if(!defined("__CANCEL_DIE__")) output_file($cache);
		}
		if($over) {
			$over=explode(",",$over);
			if(count($over)!=3) action_denied();
			if(!file_exists($imgbase.$over[0])) show_php_error(array("phperror"=>"Over '${over[0]}' not found"));
			if(strlen($over[1])!=6) action_denied();
			$im=imagecreatefrompng($imgbase.$over[0]);
			$sx=imagesx($im);
			$sy=imagesy($im);
			$im2=imagecreatetruecolor($sx,$sy);
			$bg=imagecolorallocate($im2,255,255,255);
			imagefilledrectangle($im2,0,0,$sx,$sy,$bg);
			$r=hexdec(substr($over[1],0,2));
			$g=hexdec(substr($over[1],2,2));
			$b=hexdec(substr($over[1],4,2));
			$cache2=array();
			for($x=0;$x<$sx;$x++) {
				for($y=0;$y<$sy;$y++) {
					$z=imagecolorsforindex($im,imagecolorat($im,$x,$y));
					$z=max(0,min(127,(127-$z["alpha"])*$over[2]/100));
					if(!isset($cache2[$z])) $cache2[$z]=imagecolorallocatealpha($im2,$r,$g,$b,$z);
					imagesetpixel($im2,$x,$y,$cache2[$z]);
				}
			}
			imagedestroy($im);
			imagepng($im2,$cache);
			imagedestroy($im2);
			chmod_protected($cache,0666);
			// DUMP THE DATA
			if(defined("__CANCEL_DIE__")) readfile($cache);
			if(!defined("__CANCEL_DIE__")) output_file($cache);
		}
		if($theme) {
			// FUNCTIONS
			function __themeroller_color($color,$rgb) {
				$array=array("r"=>substr($color,0,2),"g"=>substr($color,2,2),"b"=>substr($color,4,2));
				$result="";
				for($i=0;$i<3;$i++) $result.=$array[$rgb[$i]];
				return $result;
			}

			function __themeroller_csstrick($file,$csstrick) {
				foreach($csstrick as $key=>$val) if(strpos($file,$key)!==false) return explode(",",$val);
				show_php_error(array("phperror"=>"Mask '${file}' not found"));
			}
			// QUERY STRING TO GENERATE REDMOND THEME
			$buffer=file_get_contents($cssbase);
			$themeroller=$palette["themeroller"];
			$pos=strpos($themeroller,"#");
			$querystring=substr($themeroller,$pos+1);
			$array=querystring2array($querystring);
			// MODIFY COLORS TO APPLY THEME
			if(!isset($palette["themes"][$theme])) $theme=key($palette["themes"]);
			$rgb=$palette["themes"][$theme];
			foreach(array("bgColor","borderColor","fc","iconColor") as $val) {
				foreach(array("Header","Content","Default","Hover","Active") as $val2) {
					$array[$val.$val2]=__themeroller_color($array[$val.$val2],$rgb);
				}
			}
			// PREPARE SOME STRING THINGS
			foreach(array("iconColor") as $val) {
				$len=strlen($val);
				foreach($array as $key2=>$val2) {
					if(substr($key2,0,$len)==$val) {
						if(eval_bool(getDefault("cache/useimginline"))) {
							if(!defined("__CANCEL_DIE__")) define("__CANCEL_DIE__",1);
							require_once("php/listsim.php");
							saltos_context($page,$action);
							setParam("mask","icons.png,${val2}");
							ob_start();
							$oldcache=$cache;
							include(__FILE__);
							$cache=$oldcache;
							$data=base64_encode(ob_get_clean());
							saltos_context();
							$data="data:image/png;base64,${data}";
							$array["icons".substr($key2,$len)]="url(${data})";
						} else {
							$array["icons".substr($key2,$len)]="url(xml.php?action=themeroller&mask=icons.png,${val2})";
						}

					}
				}
			}
			foreach(array("bgTexture") as $val) {
				$len=strlen($val);
				foreach($array as $key2=>$val2) {
					if(substr($key2,0,$len)==$val) {
						$bgcolor=$array["bgColor".substr($key2,$len)];
						$bgimgopacity=$array["bgImgOpacity".substr($key2,$len)];
						if(eval_bool(getDefault("cache/useimginline"))) {
							if(!defined("__CANCEL_DIE__")) define("__CANCEL_DIE__",1);
							require_once("php/listsim.php");
							saltos_context($page,$action);
							setParam("over","${val2},${bgcolor},${bgimgopacity}");
							ob_start();
							$oldcache=$cache;
							include(__FILE__);
							$cache=$oldcache;
							$data=base64_encode(ob_get_clean());
							saltos_context();
							$data="data:image/png;base64,${data}";
							$array["bgImgUrl".substr($key2,$len)]="url(${data})";
						} else {
							$array["bgImgUrl".substr($key2,$len)]="url(xml.php?action=themeroller&over=${val2},${bgcolor},${bgimgopacity})";
						}
						$csstrick=__themeroller_csstrick($val2,$palette["csstrick"]);
						$array["bg".substr($key2,$len)."XPos"]=$csstrick[0];
						$array["bg".substr($key2,$len)."YPos"]=$csstrick[1];
						$array["bg".substr($key2,$len)."Repeat"]=$csstrick[2];
					}
				}
			}
			foreach(array("bgColor","borderColor","fc") as $val) {
				$len=strlen($val);
				foreach($array as $key2=>$val2) {
					if(substr($key2,0,$len)==$val) $array[$key2]="#".$val2;
				}
			}
			foreach(array("opacity") as $val) {
				$len=strlen($val);
				foreach($array as $key2=>$val2) {
					if(substr($key2,0,$len)==$val) $array[$key2]=($val2/100).";filter:Alpha(Opacity=".$val2.")";
				}
			}
			// APPLY THE CHANGES TO THE BASE CSS
			$pos=strpos($buffer,"/*{");
			while($pos!==false) {
				$pos2=strpos($buffer,"}*/");
				if($pos2===false) break;
				$pos3=$pos;
				while($pos3>0 && $buffer[$pos3]!=" ") $pos3--;
				$pos3++;
				$key=substr($buffer,$pos+3,$pos2-$pos-3);
				$val=isset($array[$key])?$array[$key]:"/*NOT FOUND*/";
				$buffer=substr_replace($buffer,$val,$pos3,$pos2-$pos3+3);
				$pos=strpos($buffer,"/*{");
			}
			// SAVE CACHE
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