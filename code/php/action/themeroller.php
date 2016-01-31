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
if(getParam("action")=="themeroller") {
	// GET PARAMETERS
	$xml=xml2array("xml/themeroller.xml");
	$type=ismobile()?"mobile":"desktop";
	$theme=getParam("theme",getParam("amp;theme"));
	$theme=strtok($theme,"?");
	$mask=getParam("mask",getParam("amp;mask"));
	$mask=strtok($mask,"?");
	$over=getParam("over",getParam("amp;over"));
	$over=strtok($over,"?");
	// PREPARE CACHE FILENAME
	$temp=get_directory("dirs/cachedir");
	$hash=md5(serialize(array($xml,$type,$theme,$mask,$over,getDefault("cache/usecssminify"),getDefault("cache/useimginline"))));
	$count=0;
	if($theme) { $format="css"; $count++; }
	if($mask) { $format="png"; $count++; }
	if($over) { $format="png"; $count++; }
	if($count!=1) action_denied();
	$cache="$temp$hash.$format";
	// FOR DEBUG PURPOSES
	//if(file_exists($cache)) unlink($cache);
	//$_CONFIG["cache"]["useimginline"]="false";
	//$_CONFIG["cache"]["usecssminify"]="false";
	// CREATE IF NOT EXISTS
	require_once("php/libaction.php");
	$allbase=array_merge(array($xml[$type]["cssbase"]),glob_protected($xml[$type]["imgbase"]."*.png"));
	if(!cache_exists($cache,$allbase)) {
		if($mask) {
			$mask=explode(",",$mask);
			if(count($mask)!=3) action_denied();
			if(substr($mask[0],0,1)=="/") action_denied();
			if(strpos($mask[0],"..")!==false) action_denied();
			if(!file_exists($mask[0])) show_php_error(array("phperror"=>"Mask '${mask[0]}' not found"));
			if(!in_array(strlen($mask[1]),array(6,3))) action_denied();
			if(!in_array(strlen($mask[2]),array(6,3))) action_denied();
			$im=imagecreatefrompng($mask[0]);
			$sx=imagesx($im);
			$sy=imagesy($im);
			$im2=imagecreatetruecolor($sx,$sy);
			list($r,$g,$b)=__themeroller_components($mask[2]);
			$bg=imagecolorallocate($im2,$r,$g,$b);
			imagecolortransparent($im2,$bg);
			imagefilledrectangle($im2,0,0,$sx,$sy,$bg);
			list($r,$g,$b)=__themeroller_components($mask[1]);
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
			if(!defined("__CANCEL_DIE__")) output_handler(array(
				"file"=>$cache,
				"cache"=>true
			));
		}
		if($over) {
			$over=explode(",",$over);
			if(count($over)!=4) action_denied();
			if(substr($over[0],0,1)=="/") action_denied();
			if(strpos($over[0],"..")!==false) action_denied();
			if(!file_exists($over[0])) show_php_error(array("phperror"=>"Over '${over[0]}' not found"));
			if(!in_array(strlen($over[1]),array(6,3))) action_denied();
			if(!is_numeric($over[2])) action_denied();
			if(!in_array(strlen($over[3]),array(6,3))) action_denied();
			$im=imagecreatefrompng($over[0]);
			$sx=imagesx($im);
			$sy=imagesy($im);
			$im2=imagecreatetruecolor($sx,$sy);
			list($r,$g,$b)=__themeroller_components($over[3]);
			$bg=imagecolorallocate($im2,$r,$g,$b);
			imagefilledrectangle($im2,0,0,$sx,$sy,$bg);
			list($r,$g,$b)=__themeroller_components($over[1]);
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
			if(!defined("__CANCEL_DIE__")) output_handler(array(
				"file"=>$cache,
				"cache"=>true
			));
		}
		if($theme) {
			// QUERY STRING TO GENERATE THE THEME
			$buffer=file_get_contents($xml[$type]["cssbase"]);
			if(!isset($xml["themes"][$theme])) $theme=key($xml["themes"]);
			$rgb=$xml["themes"][$theme];
			if(!in_array(strlen($rgb),array(6,3))) show_php_error(array("phperror"=>"Invalid RGB color: '$rgb'"));
			if($type=="desktop") {
				// DEFINE THE ARRAY WITH ALL STYLE ITEMS
				$array=querystring2array($xml[$type]["querystring"]);
				// INVERT BG<=>FC COLORS IF NEEDED
				if(substr($theme,0,3)=="inv") {
					foreach(array("Content","Default","Hover","Active","Error") as $val) {
						list($array["bgColor$val"],$array["fc$val"])=array($array["fc$val"],$array["bgColor$val"]);
						list($array["borderColor$val"],$array["iconColor$val"])=array($array["iconColor$val"],$array["borderColor$val"]);
						$array["bgAlpha$val"]="000000";
					}
				}
				// ADD THE CSS BODY COLOR
				$bgalpha=isset($array["bgAlphaContent"])?$array["bgAlphaContent"]:"ffffff";
				$buffer="body { background: #${bgalpha}; }\n".$buffer;
				// MODIFY COLORS TO APPLY THEME
				foreach(array("bgColor","borderColor","fc","iconColor") as $val) {
					foreach(array("Header","Content","Default","Hover","Active") as $val2) {
						$array[$val.$val2]=__themeroller_colorize($array[$val.$val2],$rgb);
					}
				}
				// PREPARE SOME STRING THINGS
				foreach(array("iconColor") as $val) {
					$len=strlen($val);
					foreach($array as $key2=>$val2) {
						if(substr($key2,0,$len)==$val) {
							$bgcolor=$array["bgColor".substr($key2,$len)];
							$bgImage=$xml[$type]["imgbase"]."icons.png";
							if(eval_bool(getDefault("cache/useimginline"))) {
								if(!defined("__CANCEL_DIE__")) define("__CANCEL_DIE__",1);
								require_once("php/listsim.php");
								saltos_context($page,$action);
								setParam("mask","${bgImage},${val2},${bgcolor}");
								ob_start();
								$oldcache=$cache;
								include(__FILE__);
								$cache=$oldcache;
								$data=base64_encode(ob_get_clean());
								saltos_context();
								$data="data:image/png;base64,${data}";
								$array["icons".substr($key2,$len)]="url(${data})";
							} else {
								$array["icons".substr($key2,$len)]="url(?action=themeroller&mask=${bgImage},${val2},${bgcolor})";
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
							$bgalpha=isset($array["bgAlpha".substr($key2,$len)])?$array["bgAlpha".substr($key2,$len)]:"ffffff";
							$bgImage=glob_protected($xml[$type]["imgbase"]."*${val2}.png");
							if(!isset($bgImage[0])) show_php_error(array("phperror"=>"bgImage '${val2}' not found"));
							if(!file_exists($bgImage[0])) show_php_error(array("phperror"=>"bgImage '${bgImage[0]}' not found"));
							$bgImage=$bgImage[0];
							if(eval_bool(getDefault("cache/useimginline"))) {
								if(!defined("__CANCEL_DIE__")) define("__CANCEL_DIE__",1);
								require_once("php/listsim.php");
								saltos_context($page,$action);
								setParam("over","${bgImage},${bgcolor},${bgimgopacity},${bgalpha}");
								ob_start();
								$oldcache=$cache;
								include(__FILE__);
								$cache=$oldcache;
								$data=base64_encode(ob_get_clean());
								saltos_context();
								$data="data:image/png;base64,${data}";
								$array["bgImgUrl".substr($key2,$len)]="url(${data})";
							} else {
								$array["bgImgUrl".substr($key2,$len)]="url(?action=themeroller&over=${bgImage},${bgcolor},${bgimgopacity},${bgalpha})";
							}
							if(!isset($xml[$type]["csstrick"][$val2])) show_php_error(array("phperror"=>"csstrick '${val2}' not found"));
							$csstrick=explode(",",$xml[$type]["csstrick"][$val2]);
							$array["bg".substr($key2,$len)."XPos"]=$csstrick[0];
							$array["bg".substr($key2,$len)."YPos"]=$csstrick[1];
							$array["bg".substr($key2,$len)."Repeat"]=$csstrick[2];
						}
					}
				}
				// FOR THE JQUERY.UI.TOTOP PLUGIN
				$buffer.=file_get_contents($xml[$type]["csstotop"]);
				$bgImage=$xml[$type]["imgtotop"];
				$bgcolor=$array["bgColorHeader"];
				if(eval_bool(getDefault("cache/useimginline"))) {
					if(!defined("__CANCEL_DIE__")) define("__CANCEL_DIE__",1);
					require_once("php/listsim.php");
					saltos_context($page,$action);
					setParam("mask","${bgImage},ffffff,${bgcolor}");
					ob_start();
					$oldcache=$cache;
					include(__FILE__);
					$cache=$oldcache;
					$data=base64_encode(ob_get_clean());
					saltos_context();
					$data="data:image/png;base64,${data}";
					$array["UItoTop"]="url(${data})";
				} else {
					$array["UItoTop"]="url(?action=themeroller&mask=${bgImage},ffffff,${bgcolor})";
				}
				// CONTINUE
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
			}
			if($type=="mobile") {
				// DEFINE THE ARRAY WITH ALL STYLE ITEMS
				$array=array();
				$pos=strpos($buffer,"/*{");
				while($pos!==false) {
					$pos2=strpos($buffer,"}*/",$pos);
					if($pos2===false) break;
					$pos3=$pos-1;
					while($pos3>0 && in_array($buffer[$pos3],array(" ","\t"))) $pos3--;
					while($pos3>0 && !in_array($buffer[$pos3],array(" ","\t"))) $pos3--;
					$pos3++;
					$old=trim(substr($buffer,$pos3,$pos-$pos3));
					$key=trim(substr($buffer,$pos+3,$pos2-$pos-3));
					$array[$key]=$old;
					$pos=strpos($buffer,"/*{",$pos+1);
				}
				// INVERT BG<=>FC COLORS IF NEEDED
				if(substr($theme,0,3)=="inv") {
					foreach($array as $key=>$val) {
						if(substr($key,0,7)=="a-body-" && substr($val,0,1)=="#") {
							list($array[$key],$array["b-".substr($key,2)])=array($array["b-".substr($key,2)],$array[$key]);
						}
					}
				}
				// FORCE TO INVERT SOME COLORS
				$colors=array("a-bar-color","a-page-color","a-link-color","a-bup-color","a-bhover-color","a-bdown-color","a-active-color");
				foreach($colors as $val) {
					list($array[$val],$array["b-".substr($val,2)])=array($array["b-".substr($val,2)],$array[$val]);
				}
				// MODIFY COLORS TO APPLY THEME
				foreach($array as $key=>$val) {
					if(substr($key,0,2)=="a-" && !in_array($key,$colors) && substr($key,0,7)!="a-body-" && substr($val,0,1)=="#") {
						$array[$key]="#".__themeroller_colorize(substr($val,1),$rgb,0.7,0);
					}
				}
				// PREPARE SOME STRING THINGS
				foreach($array as $key=>$val) {
					if(substr($val,0,11)=="url(images/") {
						$array[$key]=str_replace("url(images/","url(".$xml[$type]["imgbase"],$val);
						if(eval_bool(getDefault("cache/useimginline"))) $array[$key]=inline_images($array[$key]);
					}
					$buffer=str_replace("url(images/","url(".$xml[$type]["imgbase"],$buffer);
					if(eval_bool(getDefault("cache/useimginline"))) $buffer=inline_images($buffer);
				}
			}
			// APPLY THE CHANGES TO THE CSS BASE
			$pos=strpos($buffer,"/*{");
			while($pos!==false) {
				$pos2=strpos($buffer,"}*/",$pos);
				if($pos2===false) break;
				$pos3=$pos-1;
				while($pos3>0 && in_array($buffer[$pos3],array(" ","\t"))) $pos3--;
				while($pos3>0 && !in_array($buffer[$pos3],array(" ","\t"))) $pos3--;
				$pos3++;
				$old=trim(substr($buffer,$pos3,$pos-$pos3));
				$key=trim(substr($buffer,$pos+3,$pos2-$pos-3));
				$val=isset($array[$key])?$array[$key]:$old;
				$buffer=substr_replace($buffer,$val,$pos3,$pos2-$pos3+3);
				$pos=strpos($buffer,"/*{",$pos+1);
			}
			// SAVE CACHE
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