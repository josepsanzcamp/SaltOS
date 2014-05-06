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
function __unoconv_pre($array) {
	if(isset($array["input"])) {
		$input=$array["input"];
	} elseif(isset($array["data"]) && isset($array["ext"])) {
		$input=get_temp_file($array["ext"]);
		file_put_contents($input,$array["data"]);
	} else {
		show_php_error(array("phperror"=>"Call to unoconv without valid input"));
	}
	if(isset($array["output"])) {
		$output=$array["output"];
	} else {
		$output=get_temp_file(getDefault("exts/outputext",".out"));
	}
	$type=saltos_content_type($input);
	$ext=strtolower(extension($input));
	$type0=strtok($type,"/");
	return array($input,$output,$type,$ext,$type0);
}

function __unoconv_post($array,$input,$output) {
	if(!isset($array["input"])) {
		unlink($input);
	}
	if(!isset($array["output"]) && file_exists($output)) {
		$result=file_get_contents($output);
		unlink($output);
	} else {
		$result="";
	}
	return $result;
}

function __unoconv_list() {
	if(!check_commands(getDefault("commands/unoconv"),60)) return array();
	$abouts=ob_passthru(getDefault("commands/unoconv")." ".getDefault("commands/__unoconv_about__"),60);
	$abouts=explode("\n",$abouts);
	$exts=array();
	foreach($abouts as $about) {
		$pos1=strpos($about,"[");
		$pos2=strpos($about,"]");
		if($pos1!==false && $pos2!==false) {
			$ext=substr($about,$pos1+1,$pos2-$pos1-1);
			if($ext[0]==".") $ext=substr($ext,1);
			if(!in_array($ext,$exts)) $exts[]=$ext;
		}
	}
	return $exts;
}

function unoconv2pdf($array) {
	list($input,$output,$type,$ext,$type0)=__unoconv_pre($array);
	if($type=="application/pdf") {
		copy($input,$output);
	} elseif((in_array($ext,__unoconv_list()) && !in_array($type,array("audio","video"))) || in_array($type0,array("text","message"))) {
		if(check_commands(getDefault("commands/unoconv"),60)) {
			ob_passthru(getDefault("commands/unoconv")." ".str_replace(array("__INPUT__","__OUTPUT__"),array($input,$output),getDefault("commands/__unoconv__")));
		}
	}
	return __unoconv_post($array,$input,$output);
}

function unoconv2txt($array) {
	list($input,$output,$type,$ext,$type0)=__unoconv_pre($array);
	if($type=="text/plain") {
		copy($input,$output);
	} elseif($type=="text/html") {
		file_put_contents($output,html2text(file_get_contents($input)));
	} elseif($type=="application/pdf") {
		if(check_commands(getDefault("commands/pdftotext"),60)) {
			ob_passthru(getDefault("commands/pdftotext")." ".str_replace(array("__INPUT__","__OUTPUT__"),array($input,$output),getDefault("commands/__pdftotext__")));
		}
	} elseif((in_array($ext,__unoconv_list()) && !in_array($type0,array("image","audio","video"))) || in_array($type0,array("text","message"))) {
		if(check_commands(array(getDefault("commands/unoconv"),getDefault("commands/pdftotext")),60)) {
			$temp=get_temp_file(getDefault("exts/pdfext",".pdf"));
			ob_passthru(getDefault("commands/unoconv")." ".str_replace(array("__INPUT__","__OUTPUT__"),array($input,$temp),getDefault("commands/__unoconv__")));
			if(file_exists($temp)) {
				ob_passthru(getDefault("commands/pdftotext")." ".str_replace(array("__INPUT__","__OUTPUT__"),array($temp,$output),getDefault("commands/__pdftotext__")));
				unlink($temp);
			}
		}
	}
	if(file_exists($output)) {
		$temp=file_get_contents($output);
		$temp=getutf8($temp);
		file_put_contents($output,$temp);
	}
	return __unoconv_post($array,$input,$output);
}

function __unoconv_img2ocr($file) {
	if(!check_commands(getDefault("commands/tesseract"),60)) return "";
	$box=str_replace(getDefault("exts/tiffext",".tif"),getDefault("exts/boxext",".box"),$file);
	$tmp=str_replace(getDefault("exts/tiffext",".tif"),"",$file);
	if(!file_exists($box)) ob_passthru(getDefault("commands/tesseract")." ".str_replace(array("__INPUT__","__OUTPUT__"),array($file,$tmp),getDefault("commands/__tesseract__")));
	$txt=str_replace(getDefault("exts/tiffext",".tif"),getDefault("exts/textext",".txt"),$file);
	if(!file_exists($txt)) __unoconv_box2txt($box,$txt);
	return $txt;
}

function __unoconv_pdf2ocr($pdf) {
	if(!check_commands(array(getDefault("commands/pdfimages"),getDefault("commands/convert")),60)) return "";
	$result=array();
	// EXTRACT ALL IMAGES FROM PDF
	$root=get_directory("dirs/cachedir").md5_file($pdf);
	$pbm="${root}-000.pbm";
	if(!file_exists($pbm)) ob_passthru(getDefault("commands/pdfimages")." ".str_replace(array("__INPUT__","__OUTPUT__"),array($pdf,$root),getDefault("commands/__pdfimages__")));
	// CONVERT ALL IMAGES TO TIFF
	$files=glob("${root}-*.pbm");
	foreach($files as $file) {
		$tif=str_replace(getDefault("exts/pbmext",".pbm"),getDefault("exts/tiffext",".tif"),$file);
		if(!file_exists($tif)) {
			if(!isset($width) && !isset($height)) {
				// GET SIZES OF THE PDF
				$size=ob_passthru(getDefault("commands/convert")." ".str_replace(array("__INPUT__"),array($pdf),getDefault("commands/__convert_getsize__")));
				list($width,$height)=explode(" ",$size);
				$zoom=2.77; // EQUIVALENT TO 300DPI APROX
				$width=intval($width*$zoom);
				$height=intval($height*$zoom);
			}
			// CONTINUE
			ob_passthru(getDefault("commands/convert")." ".str_replace(array("__INPUT__","__WIDTH__","__HEIGHT__","__OUTPUT__"),array($file,$width,$height,$tif),getDefault("commands/__convert_resize__")));
		}
	}
	// EXTRACT ALL TEXT FROM TIFF
	$files=glob("${root}-*.tif");
	foreach($files as $file) {
		$txt=__unoconv_img2ocr($file);
		$result[]=file_get_contents($txt);
	}
	$result=implode("\n\n",$result);
	return $result;
}

function __unoconv_box2txt($box,$txt) {
	$lines=file($box,FILE_IGNORE_NEW_LINES);
	$result=array("");
	if(isset($lines[0])) {
		// COMPUTE SOME SIZE VARS
		$temp=explode(" ",$lines[0]);
		$pos1=array(($temp[3]+$temp[1])/2,($temp[4]+$temp[2])/2,$temp[3]-$temp[1],$temp[4]-$temp[2]);
		$index=0;
		$defx=array(0);
		$minx=($temp[3]+$temp[1])/2;
		$maxx=($temp[3]+$temp[1])/2;
		foreach($lines as $line) {
			$temp=explode(" ",$line);
			$pos2=array(($temp[3]+$temp[1])/2,($temp[4]+$temp[2])/2,$temp[3]-$temp[1],$temp[4]-$temp[2]);
			$incrx=$pos2[0]-$pos1[0];
			$incry=$pos2[1]-$pos1[1];
			if($incry>max($pos1[3],$pos2[3]) || $incrx<0) {
				$index++;
				$defx[$index]=0;
			}
			$defx[$index]++;
			$minx=min($minx,($temp[3]+$temp[1])/2);
			$maxx=max($maxx,($temp[3]+$temp[1])/2);
			$pos1=$pos2;
		}
		$defx=($maxx-$minx)/max($defx);
		// MAKE THE TEXT RESULT
		$temp=explode(" ",$lines[0]);
		$pos1=array(($temp[3]+$temp[1])/2,($temp[4]+$temp[2])/2,$temp[3]-$temp[1],$temp[4]-$temp[2]);
		$index=0;
		foreach($lines as $line) {
			$temp=explode(" ",$line);
			$pos2=array(($temp[3]+$temp[1])/2,($temp[4]+$temp[2])/2,$temp[3]-$temp[1],$temp[4]-$temp[2]);
			$incrx=$pos2[0]-$pos1[0];
			$incry=$pos2[1]-$pos1[1];
			if($incry>max($pos1[3],$pos2[3]) || $incrx<0) {
				$index++;
				$result[$index]="";
				$spaces=intval(($pos2[0]-$minx)/$defx);
			} else {
				$spaces=intval(($incrx-min($pos1[2],$pos2[2]))/$defx);
			}
			if($spaces>0) $result[$index].=str_repeat(" ",$spaces);
			$result[$index].=$temp[0];
			$pos1=$pos2;
		}
	}
	$result=implode("\n",$result);
	file_put_contents($txt,$result);
}
?>