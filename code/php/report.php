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

function __report_begin($subject) {
	$message=__report_begin2($subject);
	$message.=__report_begin3($subject);
	return $message;
}

function __report_begin2($subject) {
	$message="<!DOCTYPE html>";
	$message.="<html>";
	$message.="<head>";
	$message.="<title>$subject</title>";
	$message.="</head>";
	$message.="<body bgcolor='".__report_config("bgbody")."'>";
	return $message;
}

function __report_begin3($subject) {
	$message="<table cellspacing='2px' cellpadding='2px' border='0px' width='".__report_config("big")."'>";
	$message.=__report_head($subject);
	return $message;
}

function __report_text($label,$value) {
	if($label!="") $label.=":";
	$message="<tr>";
	$message.="<td bgcolor='".__report_config("bglabel")."' valign='top' align='right' width='".__report_config("small")."' style='".__report_config("style")."'>";
	$message.="<font size='".__report_config("size")."' face='".__report_config("face")."' color='".__report_config("fglabel")."'><b>";
	$message.=$label;
	$message.="</b></font>";
	$message.="</td>";
	$message.="<td bgcolor='".__report_config("bgvalue")."' valign='top' align='left' width='".__report_config("medium")."' style='".__report_config("style")."'>";
	$message.="<font size='".__report_config("size")."' face='".__report_config("face")."' color='".__report_config("fgvalue")."'>";
	$message.=$value;
	$message.="</font>";
	$message.="</td>";
	$message.="</tr>";
	return $message;
}

function __report_textarea($label,$value,$repare=true) {
	if($label!="") $label.=":";
	$message="";
	if($label!="") {
		$message.="<tr>";
		$message.="<td bgcolor='".__report_config("bglabel")."' valign='top' align='center' width='".__report_config("big")."' colspan='2' style='".__report_config("style")."'>";
		$message.="<font size='".__report_config("size")."' face='".__report_config("face")."' color='".__report_config("fglabel")."'><b>";
		$message.=$label;
		$message.="</b></font>";
		$message.="</td>";
		$message.="</tr>";
	}
	if($repare) $value=str_replace("\n","<br/>",$value);
	$message.="<tr>";
	$message.="<td bgcolor='".__report_config("bgvalue")."' valign='top' align='left' width='".__report_config("big")."' colspan='2' height='100px' style='".__report_config("style")."'>";
	$message.="<font size='".__report_config("size")."' face='".__report_config("face")."' color='".__report_config("fgvalue")."'>";
	$message.=$value;
	$message.="</font>";
	$message.="</td>";
	$message.="</tr>";
	return $message;
}

function __report_mail($label,$value) {
	if($label!="") $label.=":";
	$message="<tr>";
	$message.="<td bgcolor='".__report_config("bglabel")."' valign='top' align='right' width='".__report_config("small")."' style='".__report_config("style")."'>";
	$message.="<font size='".__report_config("size")."' face='".__report_config("face")."' color='".__report_config("fglabel")."'><b>";
	$message.=$label;
	$message.="</b></font>";
	$message.="</td>";
	$message.="<td bgcolor='".__report_config("bgvalue")."' valign='top' align='left' width='".__report_config("medium")."' style='".__report_config("style")."'>";
	$message.="<font size='".__report_config("size")."' face='".__report_config("face")."' color='".__report_config("fgvalue")."'>";
	$message.="<a style='color:".__report_config("fgvalue")."' href='mailto: $value'>$value</a>";
	$message.="</font>";
	$message.="</td>";
	$message.="</tr>";
	return $message;
}

function __report_link($label,$value,$text) {
	if($label!="") $label.=":";
	$message="<tr>";
	$message.="<td bgcolor='".__report_config("bglabel")."' valign='top' align='right' width='".__report_config("small")."' style='".__report_config("style")."'>";
	$message.="<font size='".__report_config("size")."' face='".__report_config("face")."' color='".__report_config("fglabel")."'><b>";
	$message.=$label;
	$message.="</b></font>";
	$message.="</td>";
	if($text=="") $text=$value;
	$message.="<td bgcolor='".__report_config("bgvalue")."' valign='top' align='left' width='".__report_config("medium")."' style='".__report_config("style")."'>";
	$message.="<font size='".__report_config("size")."' face='".__report_config("face")."' color='".__report_config("fgvalue")."'>";
	$message.="<a style='color:".__report_config("fgvalue")."' href='$value'>$text</a>";
	$message.="</font>";
	$message.="</td>";
	$message.="</tr>";
	return $message;
}

function __report_end() {
	$message=__report_end3();
	$message.=__report_end2();
	return $message;
}

function __report_end2() {
	$message="</body>";
	$message.="</html>";
	return $message;
}

function __report_end3() {
	$message="</table>";
	return $message;
}

function __report_head($title) {
	$message="<tr>";
	$message.="<td bgcolor='".__report_config("bgtitle")."' valign='top' align='center' width='".__report_config("big")."' colspan='2' style='".__report_config("style")."'>";
	$message.="<font size='".__report_config("size")."' face='".__report_config("face")."' color='".__report_config("fgtitle")."'><b>";
	$message.=$title;
	$message.="</b></font>";
	$message.="</td>";
	$message.="</tr>";
	return $message;
}

function __report_separator() {
	$message="<tr>";
	$message.="<td width='".__report_config("big")."' colspan='2' style='".__report_config("style")."'>";
	$message.="<font size='".__report_config("size")."' face='".__report_config("face")."'>";
	$message.="&nbsp;";
	$message.="</font>";
	$message.="</td>";
	$message.="</tr>";
	return $message;
}

function __report_config($arg) {
	static $colors=array(
		"bgbody"=>"#fcfdfd",
		"bgtitle"=>"#5c9ccc",
		"fgtitle"=>"#ffffff",
		"bglabel"=>"#d0e5f5",
		"fglabel"=>"#1d5987",
		"bgvalue"=>"#dfeffc",
		"fgvalue"=>"#2e6e9e",
		"size"=>"1",
		"face"=>"Helvetica,Arial,sans-serif",
		"big"=>"600px",
		"medium"=>"400px",
		"small"=>"200px",
		"style"=>"padding:3px 5px"
	);
	if(is_array($arg)) {
		foreach($arg as $key=>$val) $colors[$key]=$val;
		return;
	}
	if(!isset($colors[$arg])) return "";
	return $colors[$arg];
}

?>