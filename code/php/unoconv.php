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
function __unoconv_pre($array) {
	if(isset($array["input"])) {
		$input=$array["input"];
	} elseif(isset($array["data"]) && isset($array["ext"])) {
		$input=get_temp_file($array["ext"]);
		file_put_contents($input,$array["data"]);
	} else {
		show_php_error(array("phperror"=>"Call to unoconv without input"));
	}
	if(isset($array["output"])) {
		$output=$array["output"];
	} else {
		$output=get_temp_file(getDefault("exts/outputext",".out"));
	}
	return array($input,$output);
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
	list($input,$output)=__unoconv_pre($array);
	$ext=strtolower(extension($input));
	if($ext=="pdf") {
		copy($input,$output);
	} elseif(in_array($ext,__unoconv_list()) || saltos_content_type($input)=="text/plain") {
		if(check_commands(getDefault("commands/unoconv"),60)) ob_passthru(getDefault("commands/unoconv")." ".str_replace(array("__INPUT__","__OUTPUT__"),array($input,$output),getDefault("commands/__unoconv__")));
	}
	return __unoconv_post($array,$input,$output);
}

function unoconv2txt($array) {
	list($input,$output)=__unoconv_pre($array);
	$ext=strtolower(extension($input));
	if($ext=="txt" || saltos_content_type($input)=="text/plain") {
		copy($input,$output);
	} elseif($ext=="pdf") {
		if(check_commands(getDefault("commands/pdftotext"),60)) ob_passthru(getDefault("commands/pdftotext")." ".str_replace(array("__INPUT__","__OUTPUT__"),array($input,$output),getDefault("commands/__pdftotext__")));
	} elseif(in_array($ext,__unoconv_list()) && substr(saltos_content_type($input),0,5)!="image") {
		$temp=get_temp_file(getDefault("exts/pdfext",".pdf"));
		if(check_commands(getDefault("commands/unoconv"),60)) ob_passthru(getDefault("commands/unoconv")." ".str_replace(array("__INPUT__","__OUTPUT__"),array($input,$temp),getDefault("commands/__unoconv__")));
		if(file_exists($temp)) {
			if(check_commands(getDefault("commands/pdftotext"),60)) ob_passthru(getDefault("commands/pdftotext")." ".str_replace(array("__INPUT__","__OUTPUT__"),array($temp,$output),getDefault("commands/__pdftotext__")));
			unlink($temp);
		}
	}
	return __unoconv_post($array,$input,$output);
}
?>