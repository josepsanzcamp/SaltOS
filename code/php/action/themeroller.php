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
	// FUNCTIONS
	if(!defined("__THEMEROLLER_PHP__")) {
		define("__THEMEROLLER_PHP__",1);

		function __themeroller_calibrate($r,$g,$b) {
			return 0.299*$r+0.587*$g+0.114*$b;
		}

		function __themeroller_colorize($color,$rgb,$mult=1,$incr=0) {
			list($r,$g,$b)=__themeroller_components($color,true);
			$z=__themeroller_calibrate($r,$g,$b)*$mult+$incr;
			//~ echo "r=$r, g=$g, b=$b, z=$z\n";
			list($r,$g,$b)=__themeroller_components($rgb,true);
			$iter=0;
			for(;;) {
				$z2=__themeroller_calibrate($r,$g,$b);
				//~ echo "r=$r, g=$g, b=$b, z2=$z2\n";
				if(max($r,$g,$b)>1) { $r*=0.99; $g*=0.99; $b*=0.99; }
				elseif($iter>1000) break;
				elseif(abs($z2-$z)<0.01) break;
				elseif($z2<$z) { $r+=0.01; $g+=0.01; $b+=0.01; }
				elseif($z2>$z) { $r*=0.99; $g*=0.99; $b*=0.99; }
				$iter++;
			}
			//~ echo "iter=$iter\n";
			$z=sprintf("%02x%02x%02x",$r*255,$g*255,$b*255);
			return $z;
		}

		function __themeroller_csstrick($file,$csstrick) {
			foreach($csstrick as $key=>$val) if(strpos($file,$key)!==false) return explode(",",$val);
			show_php_error(array("phperror"=>"Mask '${file}' not found"));
		}

		function __themeroller_components($color,$normalize=false) {
			$len=strlen($color);
			if($len==3) { $size=1; $div=15; $mult=16; }
			elseif($len==6) { $size=2; $div=255; $mult=1; }
			else show_php_error(array("phperror"=>"Invalid color: '$color'"));
			$r=hexdec(substr($color,0*$size,$size));
			$g=hexdec(substr($color,1*$size,$size));
			$b=hexdec(substr($color,2*$size,$size));
			list($r,$g,$b)=$normalize?array($r/$div,$g/$div,$b/$div):array($r*$mult,$g*$mult,$b*$mult);
			return array($r,$g,$b);
		}
	}
	// CREATE IF NOT EXISTS
	$allbase=array_merge(array($xml[$type]["cssbase"]),glob_protected($xml[$type]["imgbase"]."*.png"));
	if(!cache_exists($cache,$allbase)) {
		if($mask) {
			$mask=explode(",",$mask);
			if(count($mask)!=3) action_denied();
			if(!file_exists($xml[$type]["imgbase"].$mask[0])) show_php_error(array("phperror"=>"Mask '${mask[0]}' not found"));
			if(!in_array(strlen($mask[1]),array(6,3))) action_denied();
			if(!in_array(strlen($mask[2]),array(6,3))) action_denied();
			$im=imagecreatefrompng($xml[$type]["imgbase"].$mask[0]);
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
			if(!defined("__CANCEL_DIE__")) output_file($cache);
		}
		if($over) {
			$over=explode(",",$over);
			if(count($over)!=4) action_denied();
			if(!file_exists($xml[$type]["imgbase"].$over[0])) show_php_error(array("phperror"=>"Over '${over[0]}' not found"));
			if(!in_array(strlen($over[1]),array(6,3))) action_denied();
			if(!is_numeric($over[2])) action_denied();
			if(!in_array(strlen($over[3]),array(6,3))) action_denied();
			$im=imagecreatefrompng($xml[$type]["imgbase"].$over[0]);
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
			if(!defined("__CANCEL_DIE__")) output_file($cache);
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
							if(eval_bool(getDefault("cache/useimginline"))) {
								if(!defined("__CANCEL_DIE__")) define("__CANCEL_DIE__",1);
								require_once("php/listsim.php");
								saltos_context($page,$action);
								setParam("mask","icons.png,${val2},${bgcolor}");
								ob_start();
								$oldcache=$cache;
								include(__FILE__);
								$cache=$oldcache;
								$data=base64_encode(ob_get_clean());
								saltos_context();
								$data="data:image/png;base64,${data}";
								$array["icons".substr($key2,$len)]="url(${data})";
							} else {
								$array["icons".substr($key2,$len)]="url(xml.php?action=themeroller&mask=icons.png,${val2},${bgcolor})";
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
							$bgImage=basename($bgImage[0]);
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
								$array["bgImgUrl".substr($key2,$len)]="url(xml.php?action=themeroller&over=${bgImage},${bgcolor},${bgimgopacity},${bgalpha})";
							}
							if(!isset($xml[$type]["csstrick"][$val2])) show_php_error(array("phperror"=>"csstrick '${val2}' not found"));
							$csstrick=explode(",",$xml[$type]["csstrick"][$val2]);
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
			output_file($cache);
		}
	} else {
		if(defined("__CANCEL_DIE__")) readfile($cache);
		if(!defined("__CANCEL_DIE__")) output_file($cache);
	}
}
?>