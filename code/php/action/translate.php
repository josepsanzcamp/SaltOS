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
if(!check_user()) action_denied();
if(getParam("action")=="translate") {
	require_once("php/translate.php");
	// CHECK COMMANDS
	if(!check_commands(getDefault("commands/apertium"),60) && !check_commands(getDefault("commands/aspell"),60)) action_denied();
	// GET PARAMETERS
	$text=getParam("text");
	$langs=getParam("langs");
	// SOME FUNCTIONS
	function __translate_get_options($filter="") {
		if(!is_array($filter)) $filter=explode(",",$filter);
		if($filter[0]=="") unset($filter[0]);
		$options=array();
		$langs=__translate_get_apertium_langs();
		if(count($langs)>0) {
			$options[]="<option value='' reverse=''>".LANG("translate","translate")."</option>";
			foreach($langs as $key=>$val) {
				$temp=explode("-",$val);
				if(!count($filter) || in_array($temp[0],$filter)) {
					$val2=implode("-",array(LANG($temp[0],"translate"),LANG($temp[1],"translate")));
					$val3=implode("-",array($temp[1],$temp[0]));
					$val3=in_array($val3,$langs)?$val3:"";
					$options[]="<option value='$val' reverse='$val3'>- $val2</option>";
				}
			}
		}
		$langs=__translate_get_aspell_langs();
		if(count($langs)>0) {
			$options[]="<option value='' reverse=''>".LANG("corrector","translate")."</option>";
			foreach($langs as $key=>$val) {
				if(!count($filter) || in_array($val,$filter)) {
					$val3=implode("-",array($val,$val));
					$val2=LANG($val,"translate");
					$options[]="<option value='$val3' reverse='$val3'>- $val2</option>";
				}
			}
		}
		$options=implode("\n",$options);
		return $options;
	}
	// CHECK FOR THE OPTIONS REQUEST
	if($langs=="auto") {
		$cache=get_cache_file(array("translate","auto",$text,__translate_get_apertium_langs(),__translate_get_aspell_langs()),getDefault("exts/textext",".txt"));
		//if(file_exists($cache)) unlink($cache);
		if(!file_exists($cache)) {
			$text=stripslashes($text);
			$langs=__translate_detect_aspell_langs($text);
			$options=__translate_get_options($langs);
			file_put_contents($cache,$options);
			chmod_protected($cache,0666);
		}
		output_file($cache);
	}
	// SECURITY CHECKS
	$error=0;
	$langs=strtolower($langs);
	for($i=0;$i<strlen($langs);$i++) {
		$ok=0;
		if($langs[$i]>="a" && $langs[$i]<="z") $ok=1;
		if($langs[$i]=="-") $ok=1;
		if(!$ok) $error=1;
	}
	$langs_array=explode("-",$langs);
	if(!$error && count($langs_array)!=2) $error=1;
	if(!$error && strlen($langs_array[0])!=2) $error=1;
	if(!$error && strlen($langs_array[1])!=2) $error=1;
	if($error) action_denied();
	// CONTINUE
	$cache=get_cache_file(array("translate",$langs,$text,__translate_get_apertium_langs(),__translate_get_aspell_langs()),getDefault("exts/textext",".txt"));
	//if(file_exists($cache)) unlink($cache);
	if(!file_exists($cache)) {
		$text=stripslashes($text);
		$text=__translate_aspell($text,$langs_array[0]);
		if($langs_array[0]!=$langs_array[1]) $text=__translate_apertium($text,$langs);
		file_put_contents($cache,$text);
		chmod_protected($cache,0666);
	}
	output_file($cache);
}
?>