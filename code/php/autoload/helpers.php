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

function get_filtered_field($field) {
	if(substr($field,0,4)=="tel:") {
		$temp=explode(":",$field,2);
		$field=$temp[1];
	}
	if(substr($field,0,7)=="mailto:") {
		$temp=explode(":",$field,2);
		$field=$temp[1];
	}
	if(substr($field,0,5)=="href:") {
		$temp=explode(":",$field,2);
		$field=$temp[1];
	}
	if(substr($field,0,5)=="link:") {
		$temp=explode(":",$field,3);
		$field=$temp[2];
	}
	return $field;
}

function check_filter($array) {
	foreach($array as $key=>$val) {
		if(getParam($key)!=$val) {
			return true;
		}
	}
	return false;
}

function add_css_page(&$result,$page) {
	$file="css/${page}.css";
	if(!file_exists($file)) $file=$page;
	if(file_exists($file)) {
		$exists=0;
		if(isset($result["styles"])) {
			if(in_array($file,$result["styles"])) $exists=1;
			foreach($result["styles"] as $array) if(is_array($array)) if(in_array($file,$array)) $exists=1;
		}
		if(!$exists) {
			if(!eval_bool(getDefault("cache/usecsscache"))) set_array($result["styles"],"include",$file);
			else set_array($result["styles"],"cache",array("include"=>$file));
		}
	}
}

function add_js_page(&$result,$page) {
	$file="js/${page}.js";
	if(!file_exists($file)) $file=$page;
	if(file_exists($file)) {
		$exists=0;
		if(isset($result["javascript"])) {
			if(in_array($file,$result["javascript"])) $exists=1;
			foreach($result["javascript"] as $array) if(is_array($array)) if(in_array($file,$array)) $exists=1;
		}
		if(!$exists) {
			if(!eval_bool(getDefault("cache/usejscache"))) set_array($result["javascript"],"include",$file);
			else set_array($result["javascript"],"cache",array("include"=>$file));
		}
	}
}

function add_css_js_page(&$result,$page) {
	add_css_page($result,$page);
	add_js_page($result,$page);
}

function check_ids() {
	$value=array();
	foreach(func_get_args() as $arg) {
		$arg=is_array($arg)?$arg:explode(",",$arg);
		$value=array_merge($value,$arg);
	}
	foreach($value as $key=>$val) {
		if(substr_count($val,"_")==2) {
			if($val[0]=="'" && substr($val,-1,1)=="'") $val=substr($val,1,-1);
			$val=explode("_",$val);
			$val[1]=id2page(page2id($val[1]));
			$val[2]=abs($val[2]);
			$value[$key]="'".implode("_",$val)."'";
		} else {
			$value[$key]=abs($val);
		}
	}
	$value=count($value)?implode(",",$value):"0";
	return $value;
}

?>