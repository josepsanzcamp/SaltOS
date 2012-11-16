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
if(getParam("action")=="phpthumb") {
	// FIND THE REAL FILE
	$src=getParam("src",getParam("amp;src"));
	if(!file_exists($src)) $src=getcwd()."/".getParam("src",getParam("amp;src"));
	if(!file_exists($src)) $src=get_directory("dirs/filesdir").getParam("src",getParam("amp;src"));
	if(!file_exists($src)) action_denied();
	// BEGIN THE PHPTHUMB WRAPPER
	if(!isset($_SERVER["HTTP_REFERER"])) $_SERVER["HTTP_REFERER"]="";
	$_SERVER["PHP_SELF"]=dirname(getServer("SCRIPT_NAME"))."/lib/phpthumb/phpThumb.php";
	require_once("lib/phpthumb/phpthumb.class.php");
	$phpThumb = new phpThumb();
	$phpThumb->src=realpath($src);
	$phpThumb->config_temp_directory=get_directory("dirs/cachedir");
	$phpThumb->config_cache_directory=get_directory("dirs/cachedir");
	$phpThumb->cache_maxage=getDefault("cache/cachegctimeout");
	$phpThumb->cache_maxsize=10*1024*1024;
	$phpThumb->cache_maxfiles=200;
	$phpThumb->config_cache_force_passthru=false;
	if(getParam("w",getParam("amp;w"))) {
		$phpThumb->w=intval(getParam("w",getParam("amp;w")));
		if($phpThumb->w>2000) action_denied();
	}
	if(getParam("h",getParam("amp;h"))) {
		$phpThumb->h=intval(getParam("h",getParam("amp;h")));
		if($phpThumb->h>2000) action_denied();
	}
	if(getParam("far",getParam("amp;far"))) $phpThumb->far=intval(getParam("far",getParam("amp;far")));
	if(getParam("bg",getParam("amp;bg"))) $phpThumb->bg=getParam("bg",getParam("amp;bg"));
	// SECURITY CHECK
	$type=saltos_content_type($phpThumb->src);
	if(substr($type,0,5)!="image") action_denied();
	// CONTINUE
	$format=substr($type,6);
	if(getParam("f",getParam("amp;f"))) $format=getParam("f",getParam("amp;f"));
	$phpThumb->config_output_format=$format;
	$phpThumb->q=100;
	$phpThumb->config_allow_src_above_docroot=true;
	$phpThumb->fltr[]="usm|80|0.5|3";
	$phpThumb->SetCacheFilename();
	$cache=$phpThumb->cache_filename;
	$cache=dirname($cache)."/".md5(basename($cache)).".".extension($cache);
	if(!file_exists($cache)) {
		if(!$phpThumb->GenerateThumbnail()) action_denied();
		$phpThumb->RenderToFile($cache);
		chmod_protected($cache,0666);
	}
	output_file($cache);
}
?>