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
if(getParam("action")=="cache") {
	// FUNCTIONS
	function __cache_resolve_path($buffer,$file) {
		// RESOLVE FULL PATH FOR ALL BACKGROUNDS IMAGES
		$dirname_file=dirname($file)."/";
		$pos=strpos($buffer,"url(");
		while($pos!==false) {
			$pos2=strpos($buffer,")",$pos+4);
			$img=substr($buffer,$pos+4,$pos2-$pos-4);
			if(in_array(substr($img,0,1),array("'",'"'))) $img=substr($img,1);
			if(in_array(substr($img,-1,1),array("'",'"'))) $img=substr($img,0,-1);
			$newimg=semi_realpath($dirname_file.$img);
			if(file_exists($newimg)) {
				$buffer=substr_replace($buffer,$newimg,$pos+4,$pos2-$pos-4);
				$pos2=$pos2-strlen($img)+strlen($newimg);
			}
			$pos=strpos($buffer,"url(",$pos2+1);
		}
		return $buffer;
	}
	// CONTINUE
	$files=trim(getParam("files",getParam("amp;files")));
	if(substr($files,-1,1)==",") $files=substr($files,0,-1);
	$usecssminify=eval_bool(getDefault("cache/usecssminify"));
	$usejsminify=eval_bool(getDefault("cache/usejsminify"));
	$useimginline=eval_bool(getDefault("cache/useimginline"));
	$cache=get_cache_file(array("cache",$usecssminify,$usejsminify,$useimginline,$files),strtolower(extension($files)));
	$files=explode(",",$files);
	foreach($files as $key=>$val) $files[$key]=trim($val);
	//if(file_exists($cache)) unlink($cache);
	if(!cache_exists($cache,$files)) {
		if(file_exists($cache)) unlink_protected($cache);
		$precache=get_cache_file($cache);
		if(file_exists($precache)) unlink_protected($precache);
		file_put_contents($precache,"");
		chmod_protected($precache,0666);
		foreach($files as $file) {
			if(file_exists($file)) {
				$buffer="";
				$subcache=get_cache_file(array("subcache",$usecssminify,$usejsminify,$useimginline,$file),strtolower(extension($file)));
				//if(file_exists($subcache)) unlink($subcache);
				if(!cache_exists($subcache,$file)) {
					if(file_exists($subcache)) unlink_protected($subcache);
					$type=saltos_content_type($file);
					$isminified=strpos($file,".min.");
					if($type=="text/css") {
						$buffer=file_get_contents($file);
						if($buffer) {
							$buffer=__cache_resolve_path($buffer,$file);
							if($useimginline) $buffer=inline_images($buffer);
							if($usecssminify && !$isminified) $buffer=minify_css($buffer);
							file_put_contents($subcache,$buffer);
							chmod_protected($subcache,0666);
						}
					} elseif($type=="application/x-javascript") {
						$buffer=file_get_contents($file);
						if($buffer) {
							if($usejsminify && !$isminified) $buffer=minify_js($buffer);
							if(substr(trim($buffer),-1,1)!=";") $buffer.=";";
							file_put_contents($subcache,$buffer);
							chmod_protected($subcache,0666);
						}
					}
				} else {
					$buffer=file_get_contents($subcache);
				}
				if($buffer) {
					file_put_contents($precache,$buffer,FILE_APPEND);
					chmod_protected($precache,0666);
				}
			}
		}
		rename($precache,$cache);
		chmod_protected($cache,0666);
	}
	output_file($cache);
}
?>