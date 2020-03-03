<?php
/*
 ____        _ _    ___  ____
/ ___|  __ _| | |_ / _ \/ ___|
\___ \ / _` | | __| | | \___ \
 ___) | (_| | | |_| |_| |___) |
|____/ \__,_|_|\__|\___/|____/

SaltOS: Framework to develop Rich Internet Applications
Copyright (C) 2007-2020 by Josep Sanz Campderrós
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

function load_style($style) {
	global $_CONFIG;
	if(!isset($_CONFIG["styles2"])) $_CONFIG["styles2"]=eval_attr(xml2array("xml/styles.xml"));
	foreach($_CONFIG["styles2"] as $style2) if($style2["value"]==$style) return true;
	return false;
}

function solve_style($style) {
	global $_CONFIG;
	$temp1=explode("/",$style);
	if(isset($temp1[1])) {
		foreach($_CONFIG["styles2"] as $style2) {
			$temp2=explode("/",$style2["value"]);
			if(isset($temp2[1])) {
				if($temp1[0]==$temp2[0] && $temp1[1]==$temp2[1]) return $style2["value"];
			}
		}
	}
	if(isset($temp1[2])) {
		foreach($_CONFIG["styles2"] as $style2) {
			$temp2=explode("/",$style2["value"]);
			if(isset($temp2[2])) {
				if($temp1[0]==$temp2[0] && $temp1[2]==$temp2[2]) return $style2["value"];
			}
		}
	}
	if(isset($temp1[0])) {
		foreach($_CONFIG["styles2"] as $style2) {
			$temp2=explode("/",$style2["value"]);
			if(isset($temp2[0])) {
				if($temp1[0]==$temp2[0]) return $style2["value"];
			}
		}
	}
	return $style;
}

function color_style($style) {
	global $_CONFIG;
	foreach($_CONFIG["styles2"] as $style2) if($style2["value"]==$style) return $style2["color"];
	return "";
}

function ICON($icon) {
	global $_CONFIG;
	if(!isset($_CONFIG["icons"])) $_CONFIG["icons"]=xml2array("xml/icons.xml");
	if(isset($_CONFIG["icons"][$icon])) return $_CONFIG["icons"][$icon];
	return $_CONFIG["icons"]["default"];
}

function detect_light_or_dark_from_style($style) {
	if(stripos($style,"light")!==false) return "light";
	if(stripos($style,"dark")!==false) return "dark";
	return "unknown";
}

function get_style() {
	$style=getDefault("style");
	$style=getCookie2("style",$style);
	$style=use_table_cookies("style","",$style);
	if(!load_style($style)) $style=getDefault("style");
	if(!load_style($style)) $style=solve_style($style);
	$style=getDefault("forcestyle",$style);
	return $style;
}

function get_css_files() {
	$result=array();
	// GET THE STYLES
	$style=get_style();
	$stylepre=getDefault("stylepre");
	$stylepost=getDefault("stylepost");
	if(load_style($style)) $result[]=$stylepre.$style.$stylepost;
	// TRICK FOR JSTREE
	$jstree=detect_light_or_dark_from_style($style);
	$jstreepre=getDefault("jstreepre");
	$jstreepost=getDefault("jstreepost");
	if(load_style($style)) $result[]=$jstreepre.$jstree.$jstreepost;
	// CONTINUE
	return $result;
}

?>