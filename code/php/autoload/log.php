<?php
/*
 ____        _ _    ___  ____
/ ___|  __ _| | |_ / _ \/ ___|
\___ \ / _` | | __| | | \___ \
 ___) | (_| | | |_| |_| |___) |
|____/ \__,_|_|\__|\___/|____/

SaltOS: Framework to develop Rich Internet Applications
Copyright (C) 2007-2019 by Josep Sanz Campderrós
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

function __addlog_helper($a) {
	return current_datetime_decimals().": ".$a;
}

function checklog($hash,$file="") {
	$dir=get_directory("dirs/filesdir",getcwd_protected()."/files");
	if(file_exists($dir.$file)) {
		$buffer=file_get_contents($dir.$file);
		if(strpos($buffer,$hash)!==false) return 1;
	}
	return 0;
}

function addlog($msg,$file="") {
	if(!$file) $file=getDefault("debug/logfile","saltos.log");
	$dir=get_directory("dirs/filesdir",getcwd_protected()."/files");
	$maxfilesize=normalize_value(getDefault("debug/maxfilesize","1M"));
	if($maxfilesize>0 && file_exists($dir.$file) && filesize($dir.$file)>=$maxfilesize) {
		$next=1;
		while(file_exists($dir.$file.".".$next)) $next++;
		capture_next_error();
		rename($dir.$file,$dir.$file.".".$next);
		get_clear_error();
	}
	$msg=trim($msg);
	$msg=explode("\n",$msg);
	$msg=array_map("__addlog_helper",$msg);
	$msg=implode("\n",$msg)."\n";
	file_put_contents($dir.$file,$msg,FILE_APPEND);
	chmod_protected($dir.$file,0666);
}

function addtrace($array,$file) {
	if(!isset($array["backtrace"])) $array["backtrace"]=debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
	if(!isset($array["debug"])) $array["debug"]=session_backtrace();
	$msg_text=do_message_error($array,"text");
	addlog($msg_text,$file);
}

function debug_dump($die=true) {
	global $config;
	echo "<pre>"; echo "GET ".sprintr($_GET)."</pre>";
	echo "<pre>"; echo "POST ".sprintr($_POST)."</pre>";
	echo "<pre>"; echo "FILES ".sprintr($_FILES)."</pre>";
	echo "<pre>"; echo "CONFIG ".sprintr($config)."</pre>";
	if($die) die();
}

?>