<?php
/*
 ____        _ _    ___  ____
/ ___|  __ _| | |_ / _ \/ ___|
\___ \ / _` | | __| | | \___ \
 ___) | (_| | | |_| |_| |___) |
|____/ \__,_|_|\__|\___/|____/

SaltOS: Framework to develop Rich Internet Applications
Copyright (C) 2007-2021 by Josep Sanz Campderrós
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

function javascript_headers() {
	if(!headers_sent()) {
		header("Content-Type: text/html");
	}
}

function javascript_location($url,$cond="") {
	$temp="";
	$temp.="if(typeof(parent.opencontent)=='function') parent.opencontent('${url}');";
	$temp.="else if(typeof(opencontent)=='function') opencontent('${url}');";
	$temp.="else window.location.href='${url}';";
	javascript_template($temp,$cond);
}

function javascript_history($go,$cond="") {
	$temp="";
	if(is_numeric($go)) {
		if($go) {
			$temp.="if(typeof(parent.history)=='object') parent.history.go(${go});";
			$temp.="else if(typeof(history)=='object') history.go(${go});";
		} else {
			$temp.="if(typeof(parent.addcontent)=='function') parent.addcontent('reload');";
			$temp.="else if(typeof(addcontent)=='function') addcontent('reload');";
			$temp.="else window.location.reload();";
		}
	} else {
		$temp.="if(typeof(parent.addcontent)=='function') parent.addcontent('${go}');";
		$temp.="else if(typeof(addcontent)=='function') addcontent('${go}');";
	}
	javascript_template($temp,$cond);
}

function javascript_location_base($args) {
	javascript_location(get_base().$args);
}

function javascript_location_page($page) {
	if($page!="") $page="?page=${page}";
	javascript_location_base($page);
}

function javascript_alert($message,$cond="") {
	static $cache=array();
	$hash=md5(serialize(array($message,$cond)));
	if(!isset($cache[$hash])) {
		$cache[$hash]=1;
		$title=addslashes(LANG("alert"));
		$message=addslashes($message);
		$class="ui-state-highlight";
		$temp="";
		$temp.="if(typeof(parent.notice)=='function') parent.notice('${title}','${message}',false,'${class}');";
		$temp.="else if(typeof(notice)=='function') notice('${title}','${message}',false,'${class}');";
		javascript_template($temp,$cond);
	}
}

function javascript_error($message,$cond="") {
	static $cache=array();
	$hash=md5(serialize(array($message,$cond)));
	if(!isset($cache[$hash])) {
		$cache[$hash]=1;
		$title=addslashes(LANG("error"));
		$message=addslashes($message);
		$class="ui-state-error";
		$temp="";
		$temp.="if(typeof(parent.notice)=='function') parent.notice('${title}','${message}',false,'${class}');";
		$temp.="else if(typeof(notice)=='function') notice('${title}','${message}',false,'${class}');";
		javascript_template($temp,$cond);
	}
}

function javascript_unloading($cond="") {
	$temp="";
	$temp.="if(typeof(parent.unloadingcontent)=='function') parent.unloadingcontent();";
	$temp.="else if(typeof(unloadingcontent)=='function') unloadingcontent();";
	javascript_settimeout($temp,100,$cond);
}

function javascript_settimeout($temp,$timeout,$cond="") {
	$temp="setTimeout(function() { ${temp} },${timeout});";
	javascript_template($temp,$cond);
}

function javascript_template($temp,$cond="") {
	javascript_headers();
	if($cond) $temp="if($cond) { ${temp} }";
	echo "<script type='text/javascript'>";
	echo $temp;
	echo "</script>";
}

function _javascript_location($url,$cond="") {
	javascript_template("window.location.href='${url}';",$cond);
}

function _javascript_history($go,$cond="") {
	if($go) javascript_template("history.go(${go})",$cond);
	else javascript_template("addcontent('reload')",$cond);
}

function _javascript_opencontent($url,$cond="") {
	javascript_template("saltos.opencontent('${url}');",$cond);
}

function _javascript_addcontent($url,$cond="") {
	javascript_template("saltos.addcontent('${url}');",$cond);
}

?>